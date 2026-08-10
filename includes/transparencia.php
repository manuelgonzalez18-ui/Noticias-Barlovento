<?php
/**
 * Transparencia editorial: paginas institucionales y perfiles de autores.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Version de la estructura de transparencia. */
define( 'NB_CORE_TRANSPARENCIA_VERSION', '1.0.0' );

/**
 * Crea una pagina institucional si no existe.
 *
 * Nunca sobrescribe una pagina ya creada o editada por la redaccion.
 *
 * @param string $slug      Slug de la pagina.
 * @param string $titulo    Titulo publico.
 * @param string $contenido Contenido de bloques/shortcodes.
 * @return int|null
 */
function nb_core_transparencia_asegurar_pagina( $slug, $titulo, $contenido ) {
	$existente = get_page_by_path( $slug );
	if ( $existente instanceof WP_Post ) {
		return $existente->ID;
	}

	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $titulo,
			'post_content' => $contenido,
		)
	);

	return is_wp_error( $id ) ? null : (int) $id;
}

/**
 * Texto base de la politica editorial.
 *
 * @return string
 */
function nb_core_transparencia_contenido_politica() {
	return '<!-- wp:paragraph --><p>Noticias Barlovento es un medio digital dedicado a la información comunitaria, regional, institucional y de servicio público, con énfasis en Barlovento y el estado Miranda.</p><!-- /wp:paragraph -->' .
		'<!-- wp:heading {"level":2} --><h2>Principios editoriales</h2><!-- /wp:heading -->' .
		'<!-- wp:list --><ul><li><strong>Exactitud:</strong> procuramos verificar datos, fechas, nombres y contexto antes de publicar.</li><li><strong>Autoría y fuentes:</strong> cada noticia identifica autor, fecha y, cuando corresponde, la fuente u organización que suministró la información.</li><li><strong>Diferenciación editorial:</strong> distinguimos redacción propia, notas de prensa, contenidos de agencia, colaboraciones y contenido patrocinado.</li><li><strong>Correcciones:</strong> corregimos errores de forma transparente y mostramos la fecha de actualización cuando una nota cambia de manera sustancial.</li><li><strong>Independencia:</strong> la publicidad y los acuerdos comerciales se mantienen identificados y separados de las decisiones editoriales.</li><li><strong>Servicio a la comunidad:</strong> priorizamos información útil, verificable y relevante para los habitantes de Barlovento, Miranda y Venezuela.</li></ul><!-- /wp:list -->' .
		'<!-- wp:heading {"level":2} --><h2>Notas de prensa y contenido republicado</h2><!-- /wp:heading -->' .
		'<!-- wp:paragraph --><p>Cuando una información proviene de una institución, agencia u otra fuente, se identifica en el artículo. El contenido republicado íntegramente puede excluirse de Google News para ayudar a los buscadores a reconocer la fuente original.</p><!-- /wp:paragraph -->' .
		'<!-- wp:heading {"level":2} --><h2>Publicidad y patrocinios</h2><!-- /wp:heading -->' .
		'<!-- wp:paragraph --><p>La publicidad se identifica visualmente como publicidad. Cualquier pieza editorial patrocinada debe indicar de forma visible su naturaleza comercial y no se presenta como periodismo independiente.</p><!-- /wp:paragraph -->' .
		'<!-- wp:heading {"level":2} --><h2>Contacto editorial</h2><!-- /wp:heading -->' .
		'<!-- wp:paragraph --><p>Los lectores pueden enviar información, observaciones o solicitudes de corrección mediante los canales oficiales publicados en la sección de contacto de Noticias Barlovento.</p><!-- /wp:paragraph -->';
}

/**
 * Texto base de la politica de correcciones.
 *
 * @return string
 */
function nb_core_transparencia_contenido_correcciones() {
	return '<!-- wp:paragraph --><p>Noticias Barlovento procura corregir con rapidez los errores verificables que puedan afectar la comprensión de una noticia.</p><!-- /wp:paragraph -->' .
		'<!-- wp:heading {"level":2} --><h2>Cómo solicitar una corrección</h2><!-- /wp:heading -->' .
		'<!-- wp:paragraph --><p>Envía el enlace de la noticia, el dato que consideras incorrecto y, cuando sea posible, una fuente o documento de respaldo a <a href="mailto:noticiasbarlovento@gmail.com">noticiasbarlovento@gmail.com</a> o mediante nuestros canales oficiales de WhatsApp.</p><!-- /wp:paragraph -->' .
		'<!-- wp:heading {"level":2} --><h2>Cómo corregimos</h2><!-- /wp:heading -->' .
		'<!-- wp:list --><ul><li>Verificamos el señalamiento y consultamos la fuente correspondiente.</li><li>Si procede, actualizamos el artículo sin alterar artificialmente su fecha original de publicación.</li><li>Cuando la corrección es sustancial, la noticia muestra una nota de corrección o actualización y la fecha de modificación.</li><li>Las aclaratorias que no cambian el sentido principal pueden incorporarse directamente en el texto.</li></ul><!-- /wp:list -->' .
		'<!-- wp:paragraph --><p>La corrección de un error no implica necesariamente la eliminación de una noticia cuando existe interés público en conservar el registro de lo ocurrido.</p><!-- /wp:paragraph -->';
}

/**
 * Crea las paginas de transparencia una sola vez por version.
 */
function nb_core_transparencia_instalar_paginas() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( NB_CORE_TRANSPARENCIA_VERSION === (string) get_option( 'nb_core_transparencia_version' ) ) {
		return;
	}

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
add_action( 'admin_init', 'nb_core_transparencia_instalar_paginas' );

/**
 * Devuelve los enlaces institucionales visibles en el pie.
 *
 * @return array<int,array{label:string,url:string}>
 */
function nb_core_transparencia_enlaces() {
	$paginas = array(
		'quienes-somos'               => 'Quiénes somos',
		'equipo'                      => 'Equipo',
		'politica-editorial'          => 'Política editorial',
		'correcciones-y-aclaratorias' => 'Correcciones y aclaratorias',
	);
	$enlaces = array();

	foreach ( $paginas as $slug => $etiqueta ) {
		$pagina = get_page_by_path( $slug );
		if ( $pagina instanceof WP_Post && 'publish' === $pagina->post_status ) {
			$enlaces[] = array(
				'label' => $etiqueta,
				'url'   => get_permalink( $pagina ),
			);
		}
	}

	return $enlaces;
}

/**
 * Lista dinamicamente los autores con publicaciones.
 *
 * @return string
 */
function nb_core_transparencia_shortcode_equipo() {
	$usuarios = get_users(
		array(
			'orderby' => 'display_name',
			'order'   => 'ASC',
		)
	);
	$autores = array();

	foreach ( $usuarios as $usuario ) {
		if ( ! $usuario instanceof WP_User ) {
			continue;
		}

		$cantidad = (int) count_user_posts( $usuario->ID, 'post', true );
		if ( $cantidad < 1 ) {
			continue;
		}

		$autores[] = array(
			'id'          => $usuario->ID,
			'nombre'      => $usuario->display_name,
			'cargo'       => trim( (string) get_user_meta( $usuario->ID, '_nb_cargo_editorial', true ) ),
			'descripcion' => trim( (string) get_user_meta( $usuario->ID, 'description', true ) ),
			'url'         => get_author_posts_url( $usuario->ID ),
			'cantidad'    => $cantidad,
		);
	}

	if ( ! $autores ) {
		return '<p>El equipo editorial se está actualizando.</p>';
	}

	ob_start();
	?>
	<div class="nb-equipo">
		<?php foreach ( $autores as $autor ) : ?>
			<article class="nb-equipo__autor">
				<h2><a rel="author" href="<?php echo esc_url( $autor['url'] ); ?>"><?php echo esc_html( $autor['nombre'] ); ?></a></h2>
				<?php if ( $autor['cargo'] ) : ?><p class="nb-equipo__cargo"><?php echo esc_html( $autor['cargo'] ); ?></p><?php endif; ?>
				<?php if ( $autor['descripcion'] ) : ?><p><?php echo esc_html( $autor['descripcion'] ); ?></p><?php endif; ?>
				<p class="nb-equipo__publicaciones"><?php echo esc_html( sprintf( '%d publicaciones', $autor['cantidad'] ) ); ?></p>
			</article>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'nb_equipo', 'nb_core_transparencia_shortcode_equipo' );

/**
 * Campo adicional para identificar la funcion profesional del autor.
 *
 * @param WP_User $usuario Usuario que se edita.
 */
function nb_core_transparencia_campo_usuario( $usuario ) {
	if ( ! $usuario instanceof WP_User ) {
		return;
	}
	?>
	<h2>Noticias Barlovento</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="nb_cargo_editorial">Cargo o función editorial</label></th>
			<td>
				<input type="text" class="regular-text" id="nb_cargo_editorial" name="nb_cargo_editorial" value="<?php echo esc_attr( get_user_meta( $usuario->ID, '_nb_cargo_editorial', true ) ); ?>" placeholder="Ej. Periodista / Editor / Corresponsal">
				<p class="description">Se muestra en la página Equipo y puede incorporarse a los datos estructurados del autor.</p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'nb_core_transparencia_campo_usuario' );
add_action( 'edit_user_profile', 'nb_core_transparencia_campo_usuario' );

/**
 * Guarda el cargo editorial del perfil.
 *
 * @param int $user_id ID del usuario.
 */
function nb_core_transparencia_guardar_usuario( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	$cargo = isset( $_POST['nb_cargo_editorial'] ) ? sanitize_text_field( wp_unslash( $_POST['nb_cargo_editorial'] ) ) : '';
	if ( '' === $cargo ) {
		delete_user_meta( $user_id, '_nb_cargo_editorial' );
	} else {
		update_user_meta( $user_id, '_nb_cargo_editorial', $cargo );
	}
}
add_action( 'personal_options_update', 'nb_core_transparencia_guardar_usuario' );
add_action( 'edit_user_profile_update', 'nb_core_transparencia_guardar_usuario' );
