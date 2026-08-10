<?php
/**
 * Presentacion y metadatos editoriales para noticias individuales.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Indica si la plantilla editorial debe usarse para la vista actual.
 *
 * @return bool
 */
function nb_core_noticia_esta_activa() {
	return is_singular( 'post' );
}

/**
 * Sustituye la plantilla individual del tema por la del plugin.
 *
 * @param string $template Plantilla resuelta por WordPress.
 * @return string
 */
function nb_core_noticia_template( $template ) {
	if ( ! nb_core_noticia_esta_activa() ) {
		return $template;
	}

	$propia = NB_CORE_PATH . 'templates/single-noticia.php';

	return is_readable( $propia ) ? $propia : $template;
}
add_filter( 'template_include', 'nb_core_noticia_template', 98 );

/**
 * Agrega una clase especifica al body de las noticias.
 *
 * @param array $clases Clases existentes.
 * @return array
 */
function nb_core_noticia_clase_body( $clases ) {
	if ( nb_core_noticia_esta_activa() ) {
		$clases[] = 'nb-noticia-activa';
	}

	return $clases;
}
add_filter( 'body_class', 'nb_core_noticia_clase_body' );

/**
 * Tipos editoriales permitidos.
 *
 * @return array
 */
function nb_core_noticia_tipos() {
	return array(
		'redaccion'   => 'Redaccion propia',
		'nota-prensa' => 'Nota de prensa',
		'agencia'     => 'Agencia',
		'colaborador' => 'Colaborador',
		'patrocinado' => 'Contenido patrocinado',
	);
}

/**
 * Registra el panel editorial dentro del editor de entradas.
 */
function nb_core_noticia_registrar_metabox() {
	add_meta_box(
		'nb-core-noticia-editorial',
		'Datos editoriales',
		'nb_core_noticia_render_metabox',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'nb_core_noticia_registrar_metabox' );

/**
 * Renderiza los campos editoriales.
 *
 * @param WP_Post $post Entrada actual.
 */
function nb_core_noticia_render_metabox( $post ) {
	$tipo             = get_post_meta( $post->ID, '_nb_tipo_contenido', true );
	$fuente           = get_post_meta( $post->ID, '_nb_fuente', true );
	$localidad        = get_post_meta( $post->ID, '_nb_localidad', true );
	$credito_foto     = get_post_meta( $post->ID, '_nb_credito_foto', true );
	$url_original     = get_post_meta( $post->ID, '_nb_url_fuente_original', true );
	$nota_correccion  = get_post_meta( $post->ID, '_nb_nota_correccion', true );
	$republicado      = '1' === (string) get_post_meta( $post->ID, '_nb_republicado_google_news', true );
	$tipos            = nb_core_noticia_tipos();

	if ( ! isset( $tipos[ $tipo ] ) ) {
		$tipo = 'redaccion';
	}

	wp_nonce_field( 'nb_core_guardar_noticia_editorial', 'nb_core_noticia_nonce' );
	?>
	<p>
		<label for="nb_tipo_contenido"><strong>Tipo de contenido</strong></label><br>
		<select id="nb_tipo_contenido" name="nb_tipo_contenido" style="width:100%;">
			<?php foreach ( $tipos as $clave => $etiqueta ) : ?>
				<option value="<?php echo esc_attr( $clave ); ?>" <?php selected( $tipo, $clave ); ?>><?php echo esc_html( $etiqueta ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="nb_fuente"><strong>Fuente u organizacion</strong></label><br>
		<input id="nb_fuente" name="nb_fuente" type="text" value="<?php echo esc_attr( $fuente ); ?>" style="width:100%;" placeholder="Ej. Alcaldia de Brion">
	</p>
	<p>
		<label for="nb_url_fuente_original"><strong>URL de la fuente original</strong></label><br>
		<input id="nb_url_fuente_original" name="nb_url_fuente_original" type="url" value="<?php echo esc_attr( $url_original ); ?>" style="width:100%;" placeholder="https://...">
	</p>
	<p>
		<label for="nb_localidad"><strong>Localidad</strong></label><br>
		<input id="nb_localidad" name="nb_localidad" type="text" value="<?php echo esc_attr( $localidad ); ?>" style="width:100%;" placeholder="Ej. Higuerote, Brion">
	</p>
	<p>
		<label for="nb_credito_foto"><strong>Credito de foto</strong></label><br>
		<input id="nb_credito_foto" name="nb_credito_foto" type="text" value="<?php echo esc_attr( $credito_foto ); ?>" style="width:100%;" placeholder="Ej. Noticias Barlovento">
	</p>
	<p>
		<label>
			<input type="checkbox" name="nb_republicado_google_news" value="1" <?php checked( $republicado ); ?>>
			<strong>Republicado íntegramente de otra fuente</strong>
		</label>
		<br><span style="color:#646970;">Lo mantiene en Google Search, pero lo excluye de Google News y del sitemap de noticias.</span>
	</p>
	<p>
		<label for="nb_nota_correccion"><strong>Nota de correccion o actualizacion</strong></label><br>
		<textarea id="nb_nota_correccion" name="nb_nota_correccion" rows="4" style="width:100%;" placeholder="Ej. Corrección: se actualizó el nombre de la institución..."><?php echo esc_textarea( $nota_correccion ); ?></textarea>
	</p>
	<p style="margin-bottom:0;color:#646970;">Los campos son opcionales. Para Google News conviene identificar autor, fuente, localidad y cualquier republicación o patrocinio.</p>
	<?php
}

/**
 * Guarda los campos editoriales con validacion de nonce y permisos.
 *
 * @param int $post_id ID de entrada.
 */
function nb_core_noticia_guardar_metadatos( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['nb_core_noticia_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nb_core_noticia_nonce'] ) ), 'nb_core_guardar_noticia_editorial' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$tipos = nb_core_noticia_tipos();
	$tipo  = isset( $_POST['nb_tipo_contenido'] ) ? sanitize_key( wp_unslash( $_POST['nb_tipo_contenido'] ) ) : 'redaccion';

	if ( ! isset( $tipos[ $tipo ] ) ) {
		$tipo = 'redaccion';
	}

	update_post_meta( $post_id, '_nb_tipo_contenido', $tipo );

	$campos = array(
		'_nb_fuente'       => 'nb_fuente',
		'_nb_localidad'    => 'nb_localidad',
		'_nb_credito_foto' => 'nb_credito_foto',
	);

	foreach ( $campos as $meta_key => $campo ) {
		$valor = isset( $_POST[ $campo ] ) ? sanitize_text_field( wp_unslash( $_POST[ $campo ] ) ) : '';

		if ( '' === $valor ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $valor );
		}
	}

	$url_original = isset( $_POST['nb_url_fuente_original'] ) ? esc_url_raw( wp_unslash( $_POST['nb_url_fuente_original'] ) ) : '';
	if ( '' === $url_original ) {
		delete_post_meta( $post_id, '_nb_url_fuente_original' );
	} else {
		update_post_meta( $post_id, '_nb_url_fuente_original', $url_original );
	}

	$nota_correccion = isset( $_POST['nb_nota_correccion'] ) ? sanitize_textarea_field( wp_unslash( $_POST['nb_nota_correccion'] ) ) : '';
	if ( '' === $nota_correccion ) {
		delete_post_meta( $post_id, '_nb_nota_correccion' );
	} else {
		update_post_meta( $post_id, '_nb_nota_correccion', $nota_correccion );
	}

	if ( isset( $_POST['nb_republicado_google_news'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['nb_republicado_google_news'] ) ) ) {
		update_post_meta( $post_id, '_nb_republicado_google_news', '1' );
	} else {
		delete_post_meta( $post_id, '_nb_republicado_google_news' );
	}
}
add_action( 'save_post_post', 'nb_core_noticia_guardar_metadatos' );

/**
 * Devuelve un metadato editorial ya sanitizado para mostrar.
 *
 * @param int    $post_id ID de entrada.
 * @param string $clave Clave sin prefijo.
 * @return string
 */
function nb_core_noticia_meta( $post_id, $clave ) {
	$permitidas = array( 'tipo_contenido', 'fuente', 'localidad', 'credito_foto', 'url_fuente_original', 'nota_correccion' );

	if ( ! in_array( $clave, $permitidas, true ) ) {
		return '';
	}

	$valor = (string) get_post_meta( $post_id, '_nb_' . $clave, true );

	if ( 'url_fuente_original' === $clave ) {
		return esc_url_raw( $valor );
	}

	return sanitize_text_field( $valor );
}

/**
 * Devuelve la etiqueta editorial que se muestra al lector.
 *
 * @param int $post_id ID de entrada.
 * @return string
 */
function nb_core_noticia_etiqueta_tipo( $post_id ) {
	$tipos = nb_core_noticia_tipos();
	$tipo  = nb_core_noticia_meta( $post_id, 'tipo_contenido' );

	if ( '' === $tipo || ! isset( $tipos[ $tipo ] ) ) {
		$tipo = 'redaccion';
	}

	return $tipos[ $tipo ];
}

/**
 * Construye la linea de fuente para el pie editorial.
 *
 * @param int $post_id ID de entrada.
 * @return string
 */
function nb_core_noticia_fuente_visible( $post_id ) {
	$tipo   = nb_core_noticia_etiqueta_tipo( $post_id );
	$fuente = nb_core_noticia_meta( $post_id, 'fuente' );

	if ( '' !== $fuente ) {
		return $tipo . ' — ' . $fuente;
	}

	if ( 'Redaccion propia' === $tipo ) {
		return 'Redaccion Noticias Barlovento';
	}

	return $tipo;
}

/**
 * Obtiene noticias relacionadas por categorias compartidas.
 *
 * @param int $post_id ID de entrada.
 * @param int $cantidad Cantidad maxima.
 * @return WP_Post[]
 */
function nb_core_noticia_relacionadas( $post_id, $cantidad = 3 ) {
	$categorias = wp_get_post_categories( $post_id );

	if ( empty( $categorias ) ) {
		return array();
	}

	return get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) $cantidad ),
			'post__not_in'        => array( $post_id ),
			'category__in'        => $categorias,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}

/**
 * Busca una pagina de contacto existente para ofrecer correcciones.
 *
 * @return string URL de contacto.
 */
function nb_core_noticia_url_contacto() {
	foreach ( array( 'contacto', 'contactenos', 'contact' ) as $slug ) {
		$pagina = get_page_by_path( $slug );

		if ( $pagina instanceof WP_Post && 'publish' === $pagina->post_status ) {
			return get_permalink( $pagina );
		}
	}

	return home_url( '/#contacto' );
}
