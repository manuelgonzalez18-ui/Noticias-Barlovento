<?php
/**
 * Endpoint de respaldo para el sitemap de Google News cuando Apache bloquea
 * rutas .xml antes de que WordPress pueda procesarlas.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Devuelve la URL de respaldo del sitemap.
 *
 * @return string
 */
function nb_core_google_news_fallback_url() {
	return add_query_arg( 'nb_news_sitemap', '1', home_url( '/' ) );
}

/**
 * Sirve el sitemap desde una URL con query string que siempre entra por
 * index.php, evitando depender de reglas Apache para archivos .xml.
 */
function nb_core_google_news_fallback_servir() {
	$solicitado = isset( $_GET['nb_news_sitemap'] ) ? sanitize_text_field( wp_unslash( $_GET['nb_news_sitemap'] ) ) : '';

	if ( '1' !== $solicitado ) {
		return;
	}

	if ( function_exists( 'nb_core_google_news_render_sitemap' ) ) {
		nb_core_google_news_render_sitemap();
	}

	status_header( 503 );
	header( 'Content-Type: text/plain; charset=UTF-8' );
	echo 'Sitemap temporalmente no disponible.';
	exit;
}
add_action( 'init', 'nb_core_google_news_fallback_servir', 0 );

/**
 * Sustituye en robots.txt la URL .xml que Apache no enruta por la URL de
 * respaldo comprobable desde la raiz publica del sitio.
 *
 * @param string $salida Contenido de robots.txt.
 * @param bool   $publico Si WordPress permite indexacion.
 * @return string
 */
function nb_core_google_news_fallback_robots( $salida, $publico ) {
	if ( ! $publico ) {
		return $salida;
	}

	$rota     = home_url( '/news-sitemap.xml' );
	$respaldo = nb_core_google_news_fallback_url();

	$lineas = preg_split( '/\r\n|\r|\n/', (string) $salida );
	$limpias = array();

	foreach ( $lineas as $linea ) {
		if ( false !== stripos( $linea, 'Sitemap:' ) && false !== strpos( $linea, $rota ) ) {
			continue;
		}
		$limpias[] = $linea;
	}

	$salida = rtrim( implode( "\n", $limpias ) ) . "\n";

	if ( false === strpos( $salida, $respaldo ) ) {
		$salida .= "\nSitemap: " . $respaldo . "\n";
	}

	return $salida;
}
add_filter( 'robots_txt', 'nb_core_google_news_fallback_robots', 100, 2 );
