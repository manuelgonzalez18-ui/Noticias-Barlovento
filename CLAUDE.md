# Contexto del proyecto — Noticias Barlovento

Portal de noticias del Municipio Brión, Estado Miranda, Venezuela.
Sitio en producción: https://noticiasbarlovento.com/wp/

## Arquitectura del sitio

WordPress alojado en cPanel (hosting compartido). **No hay entorno de staging**:
lo que se despliega va directo a producción.

Cadena de temas — importante entenderla antes de tocar nada:

```
NewsExo 8.6          (tema padre, de ThemeArile)
  └── News Gallery 1.6   (tema hijo, ACTIVO)
```

News Gallery ya es un tema hijo, y WordPress no admite "temas nietos".
Por eso **todas las personalizaciones van en el plugin de este repo**, nunca
en un tema hijo nuevo ni editando archivos de los temas.

## Qué es este repositorio

El plugin `noticiasbarlovento-core`. Es el único código propio del sitio.
El repo se despliega completo a:

```
public_html/wp/wp-content/plugins/noticiasbarlovento-core/
```

### Estructura

- `noticiasbarlovento-core.php` — cabecera del plugin, constantes, carga de módulos
- `includes/assets.php` — encolado de CSS y JS
- `includes/customizations.php` — hooks y filtros del sitio
- `assets/css/site.css` — estilos propios (se cargan con prioridad 99, después del tema)
- `assets/js/site.js` — scripts propios (desactivado por defecto)
- `.github/workflows/deploy.yml` — despliegue por FTPS al hacer push a `main`

## Convenciones de código

- Prefijo `nb_core_` en todas las funciones y `nb-core-` en los handles de assets.
- Constantes ya definidas: `NB_CORE_VERSION`, `NB_CORE_FILE`, `NB_CORE_PATH`, `NB_CORE_URL`.
- Estilo de WordPress Coding Standards: tabs para indentar, espacios dentro de paréntesis.
- Todo archivo PHP arranca con el guard `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- Para agregar un módulo nuevo: crear el archivo en `includes/` y añadir su nombre
  (sin extensión) al array de `nb_core_cargar_modulos()`.
- Al cambiar CSS o JS, subir `NB_CORE_VERSION` o el navegador sirve la versión cacheada.
- Comentarios y textos de cara al usuario en español.

## Restricciones

- **Nunca** editar archivos dentro de `wp-content/themes/newsexo/` ni de
  `wp-content/themes/news-gallery/`. Se pierden en cada actualización.
- **Nunca** borrar el tema NewsExo aunque figure como inactivo: es el padre del
  tema activo y el sitio se cae sin él.
- No versionar credenciales, `wp-config.php`, ni volcados de base de datos.
- El despliegue es directo a producción: verificar la sintaxis antes de hacer push.

## Deuda técnica conocida

- NewsExo 8.6 está marcado como vulnerable en WP Toolkit. Pendiente evaluar si
  hay versión parcheada o si toca migrar de tema.
- **Sitejet Builder sigue vinculado a `noticiasbarlovento.com`.** El sitio
  estático viejo se hizo con esa herramienta y en cPanel → Sitejet Builder
  aparece con la única acción "Continue Editing": no hay forma de desvincularlo
  ni borrarlo desde ahí. **No entrar a ese editor.** Sitejet no republica solo,
  pero si alguien publica desde ahí sobrescribe `public_html` con el sitio
  estático y se lleva puestos el `index.php` y el `.htaccess` de la raíz, o sea
  deshace la migración.

- El sitio se está moviendo de `/wp/` a la raíz del dominio. El procedimiento
  está en [docs/migracion-a-raiz.md](docs/migracion-a-raiz.md) y se ejecuta a
  mano en cPanel, no por el despliegue. Hecho hasta ahora: el `.htaccess` y el
  `index.php` de la raíz ya están puestos, y `noticiasbarlovento.com` sirve
  WordPress. Falta archivar el sitio estático viejo y cambiar las dos URLs en
  Ajustes → Generales.
- Título y descripción del sitio siguen en los valores por defecto de WordPress
  ("untitled site" / "My WordPress Blog").
- Alerta de Let's Encrypt en cPanel: revisar estado del certificado SSL.

## Plugins ya instalados en el sitio

SiteSEO, SpeedyCache, Imunify Security, CookieAdmin, Pagelayer, PopularFX,
Backuply, FormLayer, GoSMTP. Antes de escribir una funcionalidad nueva, verificar
si alguno de estos ya la cubre.
