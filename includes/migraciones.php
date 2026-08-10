<?php
/**
 * Migraciones versionadas del plugin.
 *
 * Se ejecutan una sola vez por version y permiten aplicar cambios de estructura
 * sin depender de reactivar el plugin ni de una visita al panel de WordPress.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ejecuta las migraciones pendientes.
 */
function nb_core_ejecutar_migraciones() {
	if ( defined( 'NB_CORE_TRANSPARENCIA_VERSION' ) &&
		NB_CORE_TRANSPARENCIA_VERSION !== (string) get_option( 'nb_core_transparencia_version' ) &&
		function_exists( 'nb_core_transparencia_asegurar_pagina' ) ) {

		nb_core_transparencia_asegurar_pagina(
			'politica-editorial',
			'Política editorial',
			nb_core_transparencia_contenido_politica()
		);

		nb_core_transparencia_asegurar_pagina(
			'correcciones-y-aclaratorias',
			'Correcciones y aclaratorias',
			nb_core_transparencia_contenido_correcciones()
		);

		nb_core_transparencia_asegurar_pagina(
			'equipo',
			'Equipo',
			'<!-- wp:paragraph --><p>Conoce a las personas y perfiles que publican información en Noticias Barlovento.</p><!-- /wp:paragraph -->[nb_equipo]'
		);

		update_option( 'nb_core_transparencia_version', NB_CORE_TRANSPARENCIA_VERSION );
	}

	update_option( 'nb_core_db_version', NB_CORE_VERSION );
}
add_action( 'init', 'nb_core_ejecutar_migraciones', 5 );
