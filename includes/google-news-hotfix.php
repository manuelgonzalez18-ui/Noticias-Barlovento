<?php
/**
 * Hotfix robusto para servir news-sitemap.xml en instalaciones donde
 * WordPress vive en un subdirectorio y la ruta publica esta en la raiz.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detecta la ruta publica directamente desde REQUEST_URI.
 *
 * @return bool
 */
function nb_core_google_news_es_peticion_sitemap_robusta() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$ruta = (string) parse_url( $uri, PHP_URL_PATH );
	$ruta = '/' . ltrim( $ruta, '/' );

	return '/news-sitemap.xml' === $ruta || '/google-news-sitemap.xml' === $ruta;
}

/**
 * Renderiza el XML sin depender de parse_request ni de rewrite rules.
 */
function nb_core_google_news_render_sitemap_robusto() {
	if ( ! function_exists( 'nb_core_google_news_posts_recientes' ) || ! function_exists( 'nb_core_google_news_xml' ) ) {
		return;
	}

	$entradas    = nb_core_google_news_posts_recientes();
	$publicacion = get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'Noticias Barlovento';
	$idioma      = function_exists( 'nb_core_google_news_idioma' ) ? nb_core_google_news_idioma() : 'es';

	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: application/xml; charset=UTF-8', true );
	header( 'Cache-Control: public, max-age=300, must-revalidate', true );
	header( 'X-Content-Type-Options: nosniff', true );

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

	foreach ( $entradas as $entrada ) {
		$url    = get_permalink( $entrada );
		$titulo = wp_strip_all_tags( get_the_title( $entrada ) );
		$fecha  = get_post_time( DATE_W3C, true, $entrada );

		if ( ! $url || ! $titulo || ! $fecha ) {
			continue;
		}

		echo "  <url>\n";
		echo '    <loc>' . nb_core_google_news_xml( $url ) . "</loc>\n";
		echo "    <news:news>\n";
		echo "      <news:publication>\n";
		echo '        <news:name>' . nb_core_google_news_xml( $publicacion ) . "</news:name>\n";
		echo '        <news:language>' . nb_core_google_news_xml( $idioma ) . "</news:language>\n";
		echo "      </news:publication>\n";
		echo '      <news:publication_date>' . nb_core_google_news_xml( $fecha ) . "</news:publication_date>\n";
		echo '      <news:title>' . nb_core_google_news_xml( $titulo ) . "</news:title>\n";
		echo "    </news:news>\n";
		echo "  </url>\n";
	}

	echo "</urlset>\n";
	exit;
}

/**
 * Se ejecuta antes de que WordPress resuelva la consulta principal.
 */
function nb_core_google_news_interceptar_request_uri() {
	if ( nb_core_google_news_es_peticion_sitemap_robusta() ) {
		nb_core_google_news_render_sitemap_robusto();
	}
}
add_action( 'init', 'nb_core_google_news_interceptar_request_uri', 0 );
