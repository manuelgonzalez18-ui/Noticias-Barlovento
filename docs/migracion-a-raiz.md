# Mover el sitio de `/wp/` a la raíz del dominio

Objetivo: que el portal abra en `https://noticiasbarlovento.com` en lugar de
`https://noticiasbarlovento.com/wp/`.

Este documento es un procedimiento para ejecutar a mano en cPanel y wp-admin.
**Nada de esto lo hace el despliegue automático**: los archivos de la raíz del
dominio están fuera de la carpeta del plugin, así que la Action no los toca.

> Los archivos de este documento están en bloques de código para copiar y pegar.
> `docs/` está excluido del despliegue justamente para que nada de acá termine
> subido por error a la carpeta del plugin.

## Método elegido y por qué

WordPress soporta oficialmente esta configuración: **el core se queda donde
está** (`public_html/wp/`) y solo cambian las URLs públicas. Se conoce como
*"Giving WordPress its own directory"*.

La alternativa —mover los ~2.000 archivos de WordPress a la raíz— no aporta
nada y multiplica las formas de romper el sitio. No la usamos.

Dos consecuencias importantes de este método, que conviene entender antes de
empezar:

- **Las imágenes no se rompen.** `wp-content` sigue viviendo dentro de `/wp/`,
  y WordPress arma la URL de los archivos subidos a partir de ahí. Las
  direcciones tipo `https://noticiasbarlovento.com/wp/wp-content/uploads/…`
  siguen siendo válidas. **No hace falta ningún buscar-y-reemplazar en la base
  de datos**, que es la parte peligrosa de este tipo de migraciones.
- **La ruta del despliegue no cambia.** El plugin sigue en
  `public_html/wp/wp-content/plugins/noticiasbarlovento-core/`, así que el
  `server-dir` del workflow queda igual.

Lo que sí cambia son los enlaces de las notas: pasan de
`noticiasbarlovento.com/wp/mi-nota` a `noticiasbarlovento.com/mi-nota`.
Las direcciones viejas siguen funcionando porque WordPress redirige solo a la
dirección canónica.

## Antes de empezar

### 1. Respaldo completo

No es opcional. El paso 3 escribe en la base de datos y el paso 4 agrega
archivos a la raíz.

- **Backuply** (ya instalado en el sitio): respaldo completo, archivos + base
  de datos.
- Además, en cPanel → Asistente de respaldo → descargar el respaldo completo a
  tu máquina. Un respaldo que vive solo en el mismo servidor no sirve si el
  problema es el servidor.

Verificá que el respaldo terminó y que el archivo pesa lo que debería antes de
seguir.

### 2. Decidir qué pasa con el sitio estático viejo

En la raíz del dominio hay un sitio viejo, con estas carpetas:
`blog`, `contacto`, `equipo`, `cobertura`, `css`.

**Esto hay que resolverlo sí o sí antes de la migración**, por dos razones
concretas:

1. Si en la raíz hay un `index.html`, Apache probablemente lo sirva **antes**
   que el `index.php` de WordPress, y la portada seguiría mostrando el sitio
   viejo.
2. Apache sirve carpetas reales antes de pasarle el pedido a WordPress. Si
   existe la carpeta `contacto/` y además una página de WordPress con el slug
   `contacto`, **gana la carpeta vieja** y la página nueva queda inalcanzable.

La forma más segura de resolverlo es archivar, no borrar:

```
public_html/index.html      ->  public_html/_sitio-viejo/index.html
public_html/blog/           ->  public_html/_sitio-viejo/blog/
public_html/contacto/       ->  public_html/_sitio-viejo/contacto/
public_html/equipo/         ->  public_html/_sitio-viejo/equipo/
public_html/cobertura/      ->  public_html/_sitio-viejo/cobertura/
public_html/css/            ->  public_html/_sitio-viejo/css/
```

Desde el Administrador de archivos de cPanel: crear la carpeta `_sitio-viejo`
y mover todo ahí dentro. Queda accesible si hace falta rescatar un texto o una
foto, y deja de interferir. Una vez que el sitio nuevo esté andando y estable,
se puede borrar.

**Ojo:** no muevas la carpeta `wp/`, ni `cgi-bin`, ni ningún `.well-known`
(ese último lo usa Let's Encrypt para renovar el certificado SSL).

## El procedimiento

### Paso 3: cambiar las direcciones en WordPress

En **wp-admin → Ajustes → Generales**:

| Campo | Valor nuevo |
|---|---|
| Dirección de WordPress (URL) | `https://noticiasbarlovento.com/wp` |
| Dirección del sitio (URL) | `https://noticiasbarlovento.com` |

Los dos **sin barra al final**. Guardar.

Apenas guardes, la portada va a dar error hasta que termines el paso 4. Es
normal y dura lo que tardes en copiar dos archivos. Hacelo en un horario de
poco tráfico, no un domingo a la noche.

`wp-admin` sigue siendo accesible en `https://noticiasbarlovento.com/wp/wp-admin`
durante todo el proceso.

### Paso 4: copiar `index.php` y `.htaccess` a la raíz

**Copiar** (no mover) estos dos archivos de `public_html/wp/` a `public_html/`:

- `index.php`
- `.htaccess`

En el Administrador de archivos de cPanel hay que activar *Configuración →
Mostrar archivos ocultos* para ver el `.htaccess`.

Los originales dentro de `/wp/` tienen que quedar donde están. Si movés en
lugar de copiar, el sitio se cae.

### Paso 5: editar el `index.php` de la raíz

Abrí `public_html/index.php` y buscá la última línea, que dice:

```php
require __DIR__ . '/wp-blog-header.php';
```

Cambiala por:

```php
require __DIR__ . '/wp/wp-blog-header.php';
```

Es el único cambio en ese archivo. Ese `/wp/` de más es lo que le dice a la
raíz dónde está WordPress.

### Paso 6: dejar el `.htaccess` de la raíz así

Contenido completo de `public_html/.htaccess`:

```apache
# Servir index.php antes que cualquier index.html que haya quedado dando vueltas.
DirectoryIndex index.php index.html

# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

La línea `DirectoryIndex` es la que evita que un `index.html` olvidado en la
raíz le gane a WordPress.

### Paso 7: regenerar los enlaces permanentes

En **wp-admin → Ajustes → Enlaces permanentes**, entrar y darle **Guardar
cambios** sin modificar nada. Eso obliga a WordPress a reescribir sus reglas de
reescritura con la ruta nueva.

### Paso 8: purgar cachés

- **SpeedyCache**: purgar todo el caché. Si no, vas a estar viendo páginas
  viejas y creyendo que algo falló.
- Probá también en una ventana de incógnito, para descartar el caché de tu
  propio navegador.

## Verificación

Revisá esta lista antes de cantar victoria:

- [ ] `https://noticiasbarlovento.com` abre la portada de WordPress
- [ ] `https://noticiasbarlovento.com/wp/` sigue abriendo o redirige, sin error
- [ ] Una nota individual abre bien y su URL ya **no** lleva `/wp/`
- [ ] Las imágenes de las notas se ven (siguen apuntando a `/wp/wp-content/uploads/`)
- [ ] `https://noticiasbarlovento.com/wp/wp-admin` entra al panel
- [ ] El candado de SSL aparece en la barra del navegador, sin advertencias
- [ ] Las categorías y el menú del tema navegan bien
- [ ] La búsqueda del sitio funciona
- [ ] Una URL vieja tipo `noticiasbarlovento.com/wp/alguna-nota` redirige a la nueva

## Después de la migración

- **SiteSEO**: regenerar el sitemap y verificar que las URLs que lista ya no
  tengan `/wp/`.
- **Google Search Console**: enviar el sitemap nuevo. La redirección canónica
  de WordPress se encarga del resto; Google va a tardar semanas en actualizar
  el índice, es normal.
- **Certificado SSL**: en CLAUDE.md figura una alerta pendiente de Let's
  Encrypt. Conviene resolverla antes o justo después de este cambio, porque
  ahora la raíz del dominio pasa a ser la puerta de entrada del sitio.

## Si algo sale mal

El cambio es reversible y no hay que restaurar el respaldo para volver atrás:

1. Borrar `public_html/index.php` y `public_html/.htaccess` (los de la raíz;
   los de `/wp/` no se tocan).
2. Volver los dos campos de **Ajustes → Generales** a
   `https://noticiasbarlovento.com/wp`.
3. Sacar el sitio viejo de `_sitio-viejo/` si lo querés de vuelta en la raíz.

**Si quedaste afuera de wp-admin** —pasa si una de las dos direcciones quedó
mal escrita—, no se arregla desde el navegador, pero sí por FTP. Editá
`wp-config.php` y agregá estas dos líneas **antes** de la línea que dice
`/* That's all, stop editing! */`:

```php
define( 'WP_HOME', 'https://noticiasbarlovento.com' );
define( 'WP_SITEURL', 'https://noticiasbarlovento.com/wp' );
```

Esas constantes le ganan a lo que esté guardado en la base de datos y te
devuelven el acceso al panel enseguida. Corregí lo que haga falta y después
podés sacarlas, o dejarlas: también sirven como forma permanente de fijar las
direcciones.

Recordá que `wp-config.php` **no se versiona en este repositorio**: se edita
directo en el servidor.
