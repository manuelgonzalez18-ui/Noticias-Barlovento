# Google News — Noticias Barlovento

Esta integración vive en `noticiasbarlovento-core` y está pensada para mantener al sitio elegible para Google News sin depender de Publisher Center.

## Implementado por el plugin

- `NewsArticle` + `Organization` en JSON-LD para las noticias.
- Autor con URL propia; si la firma es `Noticias Barlovento`, se declara como organización y no como una persona ficticia.
- `max-image-preview:large` en las directivas de robots.
- Sitemap específico en `https://noticiasbarlovento.com/news-sitemap.xml`.
- El sitemap conserva únicamente noticias publicadas durante las últimas 48 horas.
- `Googlebot-News` queda permitido en el `robots.txt` virtual de WordPress.
- Contenido marcado como republicado íntegramente o patrocinado se excluye de Google News sin sacarlo de Google Search.
- Páginas de `Política editorial`, `Correcciones y aclaratorias` y `Equipo`.
- Enlaces de transparencia editorial visibles en el pie.
- Campo de `Cargo o función editorial` en los perfiles de usuario.
- Campo de nota de corrección/actualización en cada noticia.

## Flujo al publicar una noticia

1. Usar un titular descriptivo y una imagen destacada relevante.
2. Elegir el tipo editorial correcto: Redacción propia, Nota de prensa, Agencia, Colaborador o Contenido patrocinado.
3. Completar fuente, localidad y crédito de foto cuando corresponda.
4. Si el texto reproduce íntegramente una publicación de terceros, marcar `Republicado íntegramente de otra fuente` y añadir la URL original.
5. Si se corrige un error sustancial, completar `Nota de corrección o actualización`.
6. Evitar cambiar la fecha original solo para volver a hacer parecer reciente una noticia antigua.

## Search Console — paso externo obligatorio

Esto no se puede automatizar desde el plugin porque requiere una cuenta de Google y control del dominio/DNS.

1. Verificar `noticiasbarlovento.com` como propiedad de dominio en Google Search Console.
2. En **Sitemaps**, enviar `https://noticiasbarlovento.com/news-sitemap.xml`.
3. Usar **Inspección de URL** sobre una noticia reciente y comprobar que Google puede rastrearla.
4. Validar una noticia en Rich Results Test para comprobar `NewsArticle`.
5. Cuando Google empiece a mostrar el medio, revisar **Rendimiento → Google News**.

## Publisher Center

Desde 2025 Google News genera las publicaciones automáticamente. No existe un alta manual del medio que garantice inclusión; la elegibilidad depende de la calidad, rastreabilidad, transparencia y políticas del contenido.
