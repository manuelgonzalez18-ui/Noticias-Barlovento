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

	/*
	 * El JS propio sigue desactivado por defecto: la primera fase del rediseno
	 * se resuelve con PHP y CSS para no agregar coste de ejecucion al navegador.
	 */
	/*
	wp_enqueue_script(
		'nb-core-site',
		NB_CORE_URL . 'assets/js/site.js',
		array(),
		nb_core_version_asset( 'assets/js/site.js' ),
		true
	);
	*/
}
add_action( 'wp_enqueue_scripts', 'nb_core_encolar_assets', 99 );
