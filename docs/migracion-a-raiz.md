# Mover el sitio de `/wp/` a la raíz del dominio

Objetivo: que el portal abra en `https://noticiasbarlovento.com` en lugar de
`https://noticiasbarlovento.com/wp/`.

Procedimiento para ejecutar a mano en cPanel y wp-admin. **Nada de esto lo hace
el despliegue automático**: los archivos de la raíz del dominio están fuera de
la carpeta del plugin, así que la Action no los toca.

## Punto de partida (verificado el 9/8/2026)

Hoy `noticiasbarlovento.com` **no sirve ningún sitio**: no hay `index.php` ni
`index.html` en la raíz —el del sitio viejo está renombrado a `index.html_`—
así que Apache devuelve un listado de directorios público.

Eso tiene dos consecuencias:

- La migración es de bajo riesgo: no reemplazamos un sitio que funciona,
  llenamos un vacío.
- El listado abierto es un problema de seguridad por sí mismo. Cualquiera ve la
  estructura del servidor. Se cierra en el paso 3.

## Método elegido y por qué

WordPress soporta oficialmente esta configuración: **el core se queda donde
está** (`public_html/wp/`) y solo cambian las URLs públicas.

La alternativa —mover los ~2.000 archivos de WordPress a la raíz— no aporta
nada y multiplica las formas de romper el sitio. No la usamos.

Dos consecuencias que conviene entender antes de empezar:

- **Las imágenes no se rompen.** `wp-content` sigue viviendo dentro de `/wp/`,
  y WordPress arma la URL de los archivos subidos a partir de ahí. Las
  direcciones `https://noticiasbarlovento.com/wp/wp-content/uploads/…` siguen
  siendo válidas. **No hace falta ningún buscar-y-reemplazar en la base de
  datos**, que es la parte peligrosa de este tipo de migraciones.
- **La ruta del despliegue no cambia.** El plugin sigue en
  `public_html/wp/wp-content/plugins/noticiasbarlovento-core/`.

Lo que sí cambia son los enlaces de las notas: pasan de
`noticiasbarlovento.com/wp/mi-nota` a `noticiasbarlovento.com/mi-nota`. Las
direcciones viejas siguen funcionando porque WordPress redirige a la canónica.

## Orden de los pasos

Los archivos van **antes** que el cambio de URLs. Con el `index.php` puesto y
las URLs todavía en `/wp`, la raíz simplemente redirige a `/wp/`: el sitio no
se cae en ningún momento. Al revés —URLs primero— la portada queda rota hasta
que terminás de copiar archivos.

## Paso 1: respaldo

No es opcional. Los pasos siguientes escriben archivos en la raíz y modifican
la base de datos.

- **Backuply** (ya instalado): respaldo completo, archivos + base de datos.
- Además, cPanel → Asistente de respaldo → **descargarlo a tu máquina**. Un
  respaldo que vive en el mismo servidor no sirve si el problema es el servidor.

Verificá que terminó y que el archivo pesa lo que debería antes de seguir.

## Paso 2: archivar el sitio estático viejo

> **Antes de mover nada, saber esto.** El sitio viejo se hizo con Sitejet
> Builder, y sigue vinculado al dominio en cPanel → Sitejet Builder. La única
> acción que ofrece esa pantalla es "Continue Editing": no se puede desvincular
> ni borrar. **No entrar a ese editor**: publicar desde ahí recrea todas estas
> carpetas y sobrescribe el `index.php` y el `.htaccess` de la raíz.

Apache sirve carpetas y archivos reales **antes** de pasarle el pedido a
WordPress. Si existe la carpeta `contacto/` y además una página de WordPress con
el slug `contacto`, gana la carpeta vieja y la página queda inalcanzable.

Crear `public_html/_sitio-viejo/` y mover ahí dentro:

**Carpetas**

```
blog                          images
blog-single-page-layout       jobs-1-item
bundles                       js
cobertura                     legal-notice
contacto                      modules-1-item
css                           privacy
equipo                        real-estate-single-page-layout
g                             subpage
                              webcard
```

**Archivos**

```
index.html_
api.php
error_log
sitemap.xml
```

`sitemap.xml` es el que más importa de los cuatro, y el más fácil de pasar por
alto: es el mapa del sitio estático viejo. SiteSEO va a generar el sitemap de
WordPress en esa misma dirección, y como Apache prefiere el archivo real, Google
seguiría leyendo el mapa de un sitio que ya no existe. No rompe nada visible
—se descubre meses después, cuando el posicionamiento no levanta.

### Lo que NO se mueve

| Qué | Por qué |
|---|---|
| `wp/` | Es WordPress |
| `cgi-bin/` | Lo administra cPanel |
| `php.ini` y `.user.ini` | Configuración de PHP del hosting |
| `.well-known/` | Con esta carpeta Let's Encrypt renueva el certificado SSL |
| `.htaccess` | Se edita en el paso 3, no se mueve ni se reemplaza |
| `noticiasbarlovento.com/` | Es la raíz de la cuenta FTP vieja. Se limpia aparte, cuando se elimine esa cuenta |
| `classicpress/` | Pendiente de identificar. No choca con ningún slug, así que no frena la migración |

## Paso 3: los archivos de la raíz

### 3.1 El `.htaccess` de la raíz: se edita, no se reemplaza

Ya existe uno en `public_html`, y contiene **solo** el bloque que genera cPanel
para fijar la versión de PHP. No tiene redirecciones ni nada del sitio viejo.

Ese bloque es el que le dice a Apache que la raíz del dominio corre con PHP 8.3.
**No se toca y no se reemplaza**: si se pisa con el `.htaccess` de `/wp/`, el
`index.php` de la raíz podría quedar servido por otra versión de PHP, o
descargarse como texto plano en lugar de ejecutarse.

Entonces: **del `/wp/` se copia únicamente `index.php`**, y al `.htaccess` que ya
está en la raíz se le agregan nuestras reglas debajo del bloque de cPanel.

### 3.2 Copiar `index.php`

**Copiar** (no mover) `public_html/wp/index.php` a `public_html/`.

El original dentro de `/wp/` tiene que quedar donde está. Si movés en lugar de
copiar, el sitio se cae.

Para ver los archivos que empiezan con punto hay que activar *Settings → Show
Hidden Files* en el Administrador de archivos.

### 3.3 Editar el `index.php` de la raíz

Abrir `public_html/index.php`. La última línea dice:

```php
require __DIR__ . '/wp-blog-header.php';
```

Cambiarla por:

```php
require __DIR__ . '/wp/wp-blog-header.php';
```

Es el único cambio del archivo. Ese `/wp/` de más es lo que le dice a la raíz
dónde está WordPress.

### 3.4 Dejar el `.htaccess` de la raíz así

El bloque de cPanel va primero y **tal cual está**. Nuestras reglas van debajo.

```apache
# php -- BEGIN cPanel-generated handler, do not edit
# Set the "ea-php83" package as the default "PHP" programming language.
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php83___lsphp .php .php8 .phtml
</IfModule>
# php -- END cPanel-generated handler, do not edit

# Sin index.php, Apache publicaba el listado completo de la raiz.
Options -Indexes

# Servir index.php antes que cualquier index.html que quede dando vueltas.
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

Al terminar este paso, la raíz ya carga WordPress y redirige a `/wp/`. El sitio
sigue funcionando igual que antes: todavía no cambió ninguna URL.

## Paso 4: cambiar las direcciones en WordPress

**wp-admin → Ajustes → Generales**:

| Campo | Valor nuevo |
|---|---|
| Dirección de WordPress (URL) | `https://noticiasbarlovento.com/wp` |
| Dirección del sitio (URL) | `https://noticiasbarlovento.com` |

Los dos **sin barra al final**. Guardar.

Este es el momento del cambio. `wp-admin` sigue accesible en
`https://noticiasbarlovento.com/wp/wp-admin`.

## Paso 5: regenerar enlaces permanentes

**wp-admin → Ajustes → Enlaces permanentes** → entrar y darle **Guardar
cambios** sin modificar nada. Eso obliga a WordPress a reescribir sus reglas con
la ruta nueva.

## Paso 6: purgar cachés

- **SpeedyCache**: purgar todo. Si no, vas a ver páginas viejas y creer que algo
  falló.
- Probar en una ventana de incógnito, para descartar el caché del navegador.

## Verificación

- [ ] `https://noticiasbarlovento.com` abre la portada de WordPress
- [ ] Ya **no** aparece el listado de directorios en ninguna carpeta
- [ ] Una nota individual abre bien y su URL no lleva `/wp/`
- [ ] Las imágenes de las notas se ven
- [ ] `https://noticiasbarlovento.com/wp/wp-admin` entra al panel
- [ ] El candado de SSL aparece sin advertencias
- [ ] Categorías, menú y búsqueda funcionan
- [ ] Una URL vieja `…/wp/alguna-nota` redirige a la nueva

## Después de la migración

- **SiteSEO**: regenerar el sitemap y verificar que las URLs ya no tengan `/wp/`.
- **Google Search Console**: enviar el sitemap nuevo. La redirección canónica se
  encarga del resto; el índice tarda semanas en actualizarse.
- **Certificado SSL**: resolver la alerta de Let's Encrypt pendiente en cPanel.
  Ahora la raíz del dominio es la puerta de entrada del sitio.
- **Cuenta FTP vieja**: eliminar `admin@noticiasbarlovento.com` y después borrar
  `public_html/noticiasbarlovento.com/`, que es su raíz y contiene los archivos
  de un despliegue fallido.
- **`_sitio-viejo/`**: cuando el sitio nuevo lleve unas semanas estable, se puede
  borrar. Mientras tanto no molesta.

## Si algo sale mal

Es reversible sin restaurar el respaldo:

1. Borrar `public_html/index.php` y `public_html/.htaccess` (los de la raíz; los
   de `/wp/` no se tocan).
2. Volver los dos campos de **Ajustes → Generales** a
   `https://noticiasbarlovento.com/wp`.
3. Sacar lo que haga falta de `_sitio-viejo/`.

**Si quedaste afuera de wp-admin** —pasa si una de las direcciones quedó mal
escrita— se arregla por FTP. Editar `wp-config.php` y agregar estas dos líneas
**antes** de `/* That's all, stop editing! */`:

```php
define( 'WP_HOME', 'https://noticiasbarlovento.com' );
define( 'WP_SITEURL', 'https://noticiasbarlovento.com/wp' );
```

Esas constantes le ganan a lo que esté guardado en la base de datos y devuelven
el acceso al panel enseguida.

`wp-config.php` **no se versiona en este repositorio**: se edita directo en el
servidor.
