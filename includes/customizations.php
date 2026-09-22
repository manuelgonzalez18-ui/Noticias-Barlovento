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

/**
 * Usa dos de los espacios publicitarios reservados de las noticias para las
 * creatividades locales cuando esos espacios no fueron configurados desde
 * Ajustes > Publicidad. Una configuracion manual siempre tiene prioridad.
 *
 * @param mixed $valor Configuracion guardada de publicidad.
 * @return array
 */
function nb_core_publicidad_local_predeterminada( $valor ) {
	$valor = is_array( $valor ) ? $valor : array();

	if ( empty( $valor['lateral_1'] ) ) {
		$archivo_abrahany = dirname( __DIR__ ) . '/assets/images/abrahany-studio-nails.webp.txt';

		if ( is_readable( $archivo_abrahany ) ) {
			$base64_abrahany = trim( (string) file_get_contents( $archivo_abrahany ) );

			if ( '' !== $base64_abrahany ) {
				$valor['lateral_1'] = sprintf(
					'<a class="nb-anuncio-local" href="%1$s" target="_blank" rel="noopener noreferrer sponsored" aria-label="Contactar a Abrahany Studio Nails por WhatsApp"><img src="data:image/webp;base64,%2$s" alt="Abrahany Studio Nails: servicios de uñas, cejas y pestañas" width="800" height="533" loading="lazy" decoding="async" style="display:block;width:100%%;height:auto;object-fit:contain;"></a>',
					esc_url( 'https://wa.me/qr/LRVX44AB262ZP1' ),
					$base64_abrahany
				);
			}
		}
	}

	if ( empty( $valor['lateral_2'] ) ) {
		$imagen_kenia = plugin_dir_url( dirname( __DIR__ ) . '/noticiasbarlovento-core.php' ) . 'assets/images/kenia-rangel-diseno-grafico-300.webp';
		$valor['lateral_2'] = sprintf(
			'<a class="nb-anuncio-local" href="%1$s" target="_blank" rel="noopener noreferrer sponsored" aria-label="Contactar a Kenia Rangel, diseñadora gráfica, por WhatsApp"><img src="%2$s" alt="Kenia Rangel, diseñadora gráfica: banners, tarjetas, flyers, logotipos y material POP" width="300" height="200" loading="lazy" decoding="async" style="display:block;width:100%%;height:auto;object-fit:contain;"></a>',
			esc_url( 'https://wa.me/message/AODEYZT5B7ZWK1' ),
			esc_url( $imagen_kenia )
		);
	}

	return $valor;
}
add_filter( 'option_nb_core_publicidad', 'nb_core_publicidad_local_predeterminada' );
