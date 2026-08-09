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
		NB_CORE_VERSION
	);

	/*
	 * El JS propio esta desactivado por defecto: el sitio todavia no lo
	 * necesita y cada archivo extra cuesta carga. Descomentar cuando haga falta.
	 */
	/*
	wp_enqueue_script(
		'nb-core-site',
		NB_CORE_URL . 'assets/js/site.js',
		array(),
		NB_CORE_VERSION,
		true
	);
	*/
}
add_action( 'wp_enqueue_scripts', 'nb_core_encolar_assets', 99 );
