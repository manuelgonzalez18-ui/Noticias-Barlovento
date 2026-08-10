# Noticias Barlovento Core

Plugin con las personalizaciones propias de [noticiasbarlovento.com](https://noticiasbarlovento.com).

Repositorio: <https://github.com/manuelgonzalez18-ui/Noticias-Barlovento>

## Puesta en marcha

### 1. El repositorio ya está conectado

El código vive en GitHub y se despliega solo al hacer merge a `main`.
Para trabajar desde otra máquina:

```bash
git clone https://github.com/manuelgonzalez18-ui/Noticias-Barlovento.git
cd Noticias-Barlovento
```

### 2. Crear el usuario FTP en cPanel

En cPanel → Cuentas FTP, crear un usuario cuyo directorio raíz sea exactamente:

```
public_html/wp/wp-content/plugins/noticiasbarlovento-core
```

Restringirlo a esa carpeta limita el daño si las credenciales se filtran: esas
credenciales viven en un secret de GitHub, y desde ahí no pueden tocar
`wp-config.php` ni el resto del hosting.

Como el usuario aterriza directamente en la carpeta del plugin, el workflow usa
`server-dir: ./`.

> **No sirve la cuenta `admin@noticiasbarlovento.com` que ya existía.** Su raíz
> es `/home/…/admin`, que no es la carpeta del plugin. Con esa cuenta el primer
> despliegue creó el árbol `public_html/wp/wp-content/plugins/…` *dentro* de esa
> jaula: los archivos subieron bien, pero a un lugar donde WordPress no mira, y
> el plugin no aparecía en el panel. Si quedó esa carpeta colgada dentro de
> `admin/`, se puede borrar sin problema.

Datos del servidor, según cPanel:

| Dato | Valor |
|---|---|
| Servidor FTP | `ftp.noticiasbarlovento.com` |
| Puerto (FTP y FTPS explícito) | `21` |

### 3. Cargar los secrets en GitHub

Settings → Secrets and variables → Actions → New repository secret:

| Secret | Valor |
|---|---|
| `FTP_SERVER` | host FTP indicado por cPanel |
| `FTP_USERNAME` | usuario creado en el paso anterior |
| `FTP_PASSWORD` | su contraseña |

Las credenciales van solo ahí: nunca dentro de un archivo del repo.

### 4. Corrida en seco

Con `dry-run: true`, la Action se conecta y deja en el log qué archivos subiría
y a dónde, sin escribir nada. Es la forma de verificar la ruta antes de tocar
producción, y conviene repetirla cada vez que se cambie `server-dir` o la
cuenta FTP.

Cuando el log muestre la ruta correcta, volver a `dry-run: false`: a partir de
ahí cada push a `main` despliega de verdad.

### 5. Activar el plugin

Tras el primer despliegue exitoso: wp-admin → Plugins → activar
"Noticias Barlovento Core".

Para comprobar que el CSS propio está cargando, inspeccionar el `<body>` en el
sitio: debe llevar la clase `nb-core`.

## Flujo de trabajo

```bash
git checkout -b nombre-del-cambio
# editar
git commit -am "Descripción del cambio"
git push origin nombre-del-cambio
# abrir PR, revisar, mergear a main -> se despliega solo
```

Trabajar sobre ramas y no directo en `main` da margen para revisar antes de que
el cambio llegue a producción, ya que no hay entorno de staging.

## Notas

Ver [CLAUDE.md](CLAUDE.md) para el contexto completo de la arquitectura del sitio
y las restricciones importantes.

Para pasar el sitio de `noticiasbarlovento.com/wp/` a `noticiasbarlovento.com`,
el procedimiento paso a paso está en
[docs/migracion-a-raiz.md](docs/migracion-a-raiz.md). Se ejecuta a mano en cPanel
y wp-admin: son archivos de la raíz del dominio, fuera del alcance de este
despliegue.
