<?php
/**
 * Hooks y filtros propios del sitio.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Agrega una clase al <body> para poder apuntar estilos propios sin depender
 * de los selectores internos del tema, que cambian entre versiones.
 *
 * @param array $clases Clases que WordPress ya calculo.
 * @return array
 */
function nb_core_clase_body( $clases ) {
	$clases[] = 'nb-core';

	return $clases;
}
add_filter( 'body_class', 'nb_core_clase_body' );

/**
 * Oculta la version de WordPress del <head> y de los feeds.
 *
 * @return string
 */
function nb_core_ocultar_version() {
	return '';
}
add_filter( 'the_generator', 'nb_core_ocultar_version' );
remove_action( 'wp_head', 'wp_generator' );
