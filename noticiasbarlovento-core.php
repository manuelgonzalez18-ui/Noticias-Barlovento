<?php
/**
 * Plugin Name:       Noticias Barlovento Core
 * Plugin URI:        https://noticiasbarlovento.com/
 * Description:       Personalizaciones propias de Noticias Barlovento. Todo el codigo del sitio vive aqui, nunca en los temas.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Noticias Barlovento
 * License:           GPL-2.0-or-later
 * Text Domain:       noticiasbarlovento-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version del plugin.
 *
 * Se usa como version de respaldo para recursos cuyo mtime no se pueda leer.
 */
define( 'NB_CORE_VERSION', '1.2.0' );

/** Ruta absoluta al archivo principal del plugin. */
define( 'NB_CORE_FILE', __FILE__ );

/** Ruta absoluta a la carpeta del plugin, con barra final. */
define( 'NB_CORE_PATH', plugin_dir_path( NB_CORE_FILE ) );

/** URL publica de la carpeta del plugin, con barra final. */
define( 'NB_CORE_URL', plugin_dir_url( NB_CORE_FILE ) );

/**
 * Carga los modulos del plugin.
 *
 * Para agregar uno nuevo: crear el archivo en includes/ y sumar su nombre
 * (sin extension) al array.
 */
function nb_core_cargar_modulos() {
	$modulos = array(
		'assets',
		'customizations',
		'estructura-inicial',
	);

	foreach ( $modulos as $modulo ) {
		$archivo = NB_CORE_PATH . 'includes/' . $modulo . '.php';

		if ( is_readable( $archivo ) ) {
			require_once $archivo;
		}
	}
}
nb_core_cargar_modulos();
