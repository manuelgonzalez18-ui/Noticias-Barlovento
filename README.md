# Noticias Barlovento Core

Plugin con las personalizaciones propias de [noticiasbarlovento.com](https://noticiasbarlovento.com/wp/).

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

Restringirlo a esa carpeta limita el daño si las credenciales se filtran.
Si se usa un usuario restringido, en `.github/workflows/deploy.yml` hay que
cambiar `server-dir` por `./`.

> **Ojo con la cuenta que ya existe.** La cuenta `admin@noticiasbarlovento.com`
> que figura en cPanel tiene como raíz `/home/…/admin`, que no es la carpeta del
> plugin. O se crea un usuario nuevo apuntado al plugin (y entonces
> `server-dir: ./`), o se usa una cuenta con acceso a `public_html` y se deja el
> `server-dir` completo que trae el workflow. Las dos opciones funcionan; lo que
> no funciona es mezclarlas.

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

### 4. El despliegue ya está activo

La corrida en seco se hizo y la ruta quedó verificada, así que `dry-run` está
en `false`: **cada push a `main` sube al servidor**.

Si en algún momento hace falta volver a probar sin escribir —por ejemplo al
cambiar `server-dir`— poner `dry-run: true` en `.github/workflows/deploy.yml`,
hacer push, leer el log en la pestaña Actions y después volver a `false`.

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
