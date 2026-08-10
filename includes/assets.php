<?php
/**
 * Encolado de CSS y JS propios.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Devuelve una version de cache basada en la fecha real del archivo.
 *
 * Esto evita tener que subir manualmente NB_CORE_VERSION cada vez que cambia
 * CSS o JS, y mantiene un fallback estable si el archivo no es legible.
 *
 * @param string $ruta_relativa Ruta relativa dentro del plugin.
 * @return string
 */
function nb_core_version_asset( $ruta_relativa ) {
	$archivo = NB_CORE_PATH . ltrim( $ruta_relativa, '/' );

	if ( is_readable( $archivo ) ) {
		$modificado = filemtime( $archivo );

		if ( false !== $modificado ) {
			return (string) $modificado;
		}
	}

	return NB_CORE_VERSION;
}

/**
 * Devuelve las publicaciones que alimentan el ticker del sitio.
 *
 * El ticker no depende de la categoria configurada en NewsExo: toma siempre
 * las entradas publicadas mas recientes del sitio.
 *
 * @return array<int,array{title:string,url:string}>
 */
function nb_core_datos_ticker() {
	$entradas = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 8,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => false,
			'no_found_rows'       => true,
		)
	);

	$items = array();

	foreach ( $entradas as $entrada ) {
		$titulo = get_the_title( $entrada );
		$url    = get_permalink( $entrada );

		if ( ! $titulo || ! $url ) {
			continue;
		}

		$items[] = array(
			'title' => wp_strip_all_tags( $titulo ),
			'url'   => esc_url_raw( $url ),
		);
	}

	return $items;
}

/**
 * Encola los assets del sitio.
 *
 * La prioridad 99 asegura que el CSS propio se cargue despues del tema
 * News Gallery y pueda sobrescribir sus estilos sin recurrir a !important.
 */
function nb_core_encolar_assets() {
	wp_enqueue_style(
		'nb-core-site',
		NB_CORE_URL . 'assets/css/site.css',
		array(),
		nb_core_version_asset( 'assets/css/site.css' )
	);

	wp_enqueue_style(
		'nb-core-header',
		NB_CORE_URL . 'assets/css/header.css',
		array( 'nb-core-site' ),
		nb_core_version_asset( 'assets/css/header.css' )
	);

	wp_enqueue_script(
		'nb-core-header',
		NB_CORE_URL . 'assets/js/header.js',
		array(),
		nb_core_version_asset( 'assets/js/header.js' ),
		true
	);

	/*
	 * NewsExo muestra la franja de titulares tambien en categorias y archivos.
	 * Por eso el ticker debe cargarse globalmente y no solo en la portada.
	 */
	wp_enqueue_style(
		'nb-core-ticker',
		NB_CORE_URL . 'assets/css/ticker.css',
		array( 'nb-core-site' ),
		nb_core_version_asset( 'assets/css/ticker.css' )
	);

	wp_enqueue_script(
		'nb-core-ticker',
		NB_CORE_URL . 'assets/js/ticker.js',
		array(),
		nb_core_version_asset( 'assets/js/ticker.js' ),
		true
	);

	wp_localize_script(
		'nb-core-ticker',
		'nbTickerData',
		array(
			'items' => nb_core_datos_ticker(),
		)
	);

	if ( function_exists( 'nb_core_portada_esta_activa' ) && nb_core_portada_esta_activa() ) {
		wp_enqueue_style(
			'nb-core-portada',
			NB_CORE_URL . 'assets/css/portada.css',
			array( 'nb-core-site' ),
			nb_core_version_asset( 'assets/css/portada.css' )
		);

		/*
		 * El CSS general estiliza los <article> del blog generico. La portada
		 * tiene su propio sistema de tarjetas y debe partir de una caja limpia.
		 */
		wp_add_inline_style(
			'nb-core-portada',
			'body.nb-portada-activa .nb-portada article{margin:0;padding:0;background:transparent;border:0;}'
		);
	}

	if ( function_exists( 'nb_core_noticia_esta_activa' ) && nb_core_noticia_esta_activa() ) {
		wp_enqueue_style(
			'nb-core-noticia',
			NB_CORE_URL . 'assets/css/noticia.css',
			array( 'nb-core-site' ),
			nb_core_version_asset( 'assets/css/noticia.css' )
		);

		/* La noticia propia no debe heredar la caja generica del single del tema. */
		wp_add_inline_style(
			'nb-core-noticia',
			'body.nb-core.single.nb-noticia-activa .nb-noticia article.nb-noticia__articulo{background:transparent;border-radius:0;}'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'nb_core_encolar_assets', 99 );
