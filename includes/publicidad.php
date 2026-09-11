<?php
/**
 * Gestion de espacios publicitarios del portal.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Devuelve la configuracion disponible de espacios publicitarios.
 *
 * @return array
 */
function nb_core_publicidad_espacios() {
	return array(
		'superior' => array(
			'etiqueta'    => 'Banner superior',
			'descripcion' => 'Formato recomendado: 970x90 o 728x90. Se adapta al ancho disponible.',
		),
		'lateral_1' => array(
			'etiqueta'    => 'Lateral 300x250',
			'descripcion' => 'Rectangulo mediano en la columna derecha de la noticia.',
		),
		'lateral_2' => array(
			'etiqueta'    => 'Lateral 300x600',
			'descripcion' => 'Formato vertical para campañas con mayor visibilidad.',
		),
		'contenido' => array(
			'etiqueta'    => 'Dentro de la noticia',
			'descripcion' => 'Banner horizontal insertado despues del tercer parrafo.',
		),
		'inferior' => array(
			'etiqueta'    => 'Banner inferior',
			'descripcion' => 'Banner horizontal antes de las noticias relacionadas.',
		),
	);
}

/**
 * Registra la opcion que almacena los codigos de publicidad.
 */
function nb_core_publicidad_registrar_ajustes() {
	register_setting(
		'nb_core_publicidad',
		'nb_core_publicidad',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'nb_core_publicidad_sanitizar_ajustes',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'nb_core_publicidad_registrar_ajustes' );

/**
 * Sanitiza la configuracion publicitaria.
 *
 * Los administradores con unfiltered_html pueden pegar etiquetas de redes
 * publicitarias. Para el resto de usuarios se aplica wp_kses_post().
 *
 * @param mixed $valor Valor recibido.
 * @return array
 */
function nb_core_publicidad_sanitizar_ajustes( $valor ) {
	$limpio   = array();
	$espacios = nb_core_publicidad_espacios();
	$valor    = is_array( $valor ) ? $valor : array();

	foreach ( $espacios as $clave => $configuracion ) {
		$codigo = isset( $valor[ $clave ] ) ? wp_unslash( $valor[ $clave ] ) : '';

		if ( current_user_can( 'unfiltered_html' ) ) {
			$limpio[ $clave ] = $codigo;
		} else {
			$limpio[ $clave ] = wp_kses_post( $codigo );
		}
	}

	return $limpio;
}

/**
 * Agrega la pagina de configuracion en Ajustes.
 */
function nb_core_publicidad_menu() {
	add_options_page(
		'Publicidad',
		'Publicidad',
		'manage_options',
		'nb-core-publicidad',
		'nb_core_publicidad_pagina_ajustes'
	);
}
add_action( 'admin_menu', 'nb_core_publicidad_menu' );

/**
 * Renderiza la pagina de configuracion.
 */
function nb_core_publicidad_pagina_ajustes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$valores  = get_option( 'nb_core_publicidad', array() );
	$espacios = nb_core_publicidad_espacios();
	?>
	<div class="wrap">
		<h1>Publicidad</h1>
		<p>Configura los banners que se muestran en las noticias. Puedes pegar HTML de una creatividad, un enlace con imagen o el codigo de tu proveedor publicitario.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'nb_core_publicidad' ); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<?php foreach ( $espacios as $clave => $configuracion ) : ?>
						<tr>
							<th scope="row">
								<label for="nb-publicidad-<?php echo esc_attr( $clave ); ?>"><?php echo esc_html( $configuracion['etiqueta'] ); ?></label>
							</th>
							<td>
								<textarea id="nb-publicidad-<?php echo esc_attr( $clave ); ?>" name="nb_core_publicidad[<?php echo esc_attr( $clave ); ?>]" rows="6" class="large-text code"><?php echo esc_textarea( isset( $valores[ $clave ] ) ? $valores[ $clave ] : '' ); ?></textarea>
								<p class="description"><?php echo esc_html( $configuracion['descripcion'] ); ?></p>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( 'Guardar publicidad' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Devuelve el HTML de un espacio publicitario.
 *
 * @param string $espacio Clave del espacio.
 * @param string $clase   Clase adicional opcional.
 * @return string
 */
function nb_core_publicidad_html( $espacio, $clase = '' ) {
	$espacios = nb_core_publicidad_espacios();

	if ( ! isset( $espacios[ $espacio ] ) ) {
		return '';
	}

	$valores = get_option( 'nb_core_publicidad', array() );
	$codigo  = isset( $valores[ $espacio ] ) ? trim( (string) $valores[ $espacio ] ) : '';
	$clases  = trim( 'nb-publicidad nb-publicidad--' . sanitize_html_class( $espacio ) . ' ' . $clase );

	ob_start();
	?>
	<aside class="<?php echo esc_attr( $clases ); ?>" aria-label="Publicidad">
		<span class="nb-publicidad__etiqueta">Publicidad</span>
		<div class="nb-publicidad__contenido">
			<?php if ( '' !== $codigo ) : ?>
				<?php echo $codigo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contenido administrado por usuarios con permisos. ?>
			<?php else : ?>
				<div class="nb-publicidad__vacio">
					<strong>Espacio publicitario disponible</strong>
					<span><?php echo esc_html( $espacios[ $espacio ]['descripcion'] ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	</aside>
	<?php

	return (string) ob_get_clean();
}

/**
 * Imprime un espacio publicitario.
 *
 * @param string $espacio Clave del espacio.
 * @param string $clase   Clase adicional opcional.
 */
function nb_core_publicidad_render( $espacio, $clase = '' ) {
	echo nb_core_publicidad_html( $espacio, $clase ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Inserta el espacio publicitario interno despues del tercer parrafo.
 *
 * @param string $contenido Contenido ya filtrado de la entrada.
 * @return string
 */
function nb_core_publicidad_insertar_en_contenido( $contenido ) {
	if ( is_admin() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $contenido;
	}

	$publicidad = nb_core_publicidad_html( 'contenido', 'nb-publicidad--en-contenido' );

	if ( '' === $publicidad ) {
		return $contenido;
	}

	$partes = preg_split( '/(<\/p>)/i', $contenido, -1, PREG_SPLIT_DELIM_CAPTURE );

	if ( ! is_array( $partes ) || count( $partes ) < 6 ) {
		return $contenido . $publicidad;
	}

	$resultado       = '';
	$parrafos_cerrados = 0;
	$insertado       = false;

	foreach ( $partes as $parte ) {
		$resultado .= $parte;

		if ( preg_match( '/^<\/p>$/i', $parte ) ) {
			$parrafos_cerrados++;

			if ( 3 === $parrafos_cerrados ) {
				$resultado .= $publicidad;
				$insertado = true;
			}
		}
	}

	return $insertado ? $resultado : $contenido . $publicidad;
}
add_filter( 'the_content', 'nb_core_publicidad_insertar_en_contenido', 35 );
