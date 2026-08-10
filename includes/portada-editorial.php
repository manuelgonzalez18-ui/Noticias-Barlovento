<?php
/**
 * Portada editorial propia de Noticias Barlovento.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Indica si debe usarse la portada editorial del plugin.
 *
 * Se limita a la primera pagina del blog para no interferir con archivos,
 * categorias ni una futura pagina estatica configurada como portada.
 *
 * @return bool
 */
function nb_core_portada_esta_activa() {
	return is_home() && ! is_paged();
}

/**
 * Sustituye la plantilla generica del tema por la portada del plugin.
 *
 * @param string $template Plantilla elegida por WordPress.
 * @return string
 */
function nb_core_portada_template( $template ) {
	if ( ! nb_core_portada_esta_activa() ) {
		return $template;
	}

	$propia = NB_CORE_PATH . 'templates/portada.php';

	return is_readable( $propia ) ? $propia : $template;
}
add_filter( 'template_include', 'nb_core_portada_template', 99 );

/**
 * Agrega una clase especifica al body cuando la portada propia esta activa.
 *
 * @param array $clases Clases existentes.
 * @return array
 */
function nb_core_portada_clase_body( $clases ) {
	if ( nb_core_portada_esta_activa() ) {
		$clases[] = 'nb-portada-activa';
	}

	return $clases;
}
add_filter( 'body_class', 'nb_core_portada_clase_body' );

/**
 * Obtiene entradas publicadas con valores seguros por defecto.
 *
 * @param array $args Argumentos para get_posts().
 * @return WP_Post[]
 */
function nb_core_portada_obtener_posts( $args = array() ) {
	$defaults = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 4,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	return get_posts( wp_parse_args( $args, $defaults ) );
}

/**
 * Arma la apertura priorizando entradas fijadas y completando con las ultimas.
 *
 * @param int $cantidad Cantidad de noticias.
 * @return WP_Post[]
 */
function nb_core_portada_obtener_destacados( $cantidad = 5 ) {
	$cantidad = max( 1, (int) $cantidad );
	$posts    = array();
	$usados   = array();
	$fijados  = array_filter( array_map( 'intval', (array) get_option( 'sticky_posts', array() ) ) );

	if ( ! empty( $fijados ) ) {
		$posts = nb_core_portada_obtener_posts(
			array(
				'post__in'       => $fijados,
				'posts_per_page' => $cantidad,
			)
		);
		$usados = wp_list_pluck( $posts, 'ID' );
	}

	$faltan = $cantidad - count( $posts );

	if ( $faltan > 0 ) {
		$relleno = nb_core_portada_obtener_posts(
			array(
				'posts_per_page' => $faltan,
				'post__not_in'   => array_merge( $usados, $fijados ),
			)
		);
		$posts   = array_merge( $posts, $relleno );
	}

	return $posts;
}

/**
 * Busca una categoria por su nombre visible.
 *
 * @param string $nombre Nombre de categoria.
 * @return WP_Term|null
 */
function nb_core_portada_categoria( $nombre ) {
	$termino = get_term_by( 'name', $nombre, 'category' );

	return $termino instanceof WP_Term ? $termino : null;
}

/**
 * Obtiene noticias de una categoria existente.
 *
 * @param string $nombre Nombre de categoria.
 * @param int    $cantidad Cantidad de entradas.
 * @param int[]  $excluir IDs a excluir.
 * @return WP_Post[]
 */
function nb_core_portada_posts_categoria( $nombre, $cantidad = 4, $excluir = array() ) {
	$categoria = nb_core_portada_categoria( $nombre );

	if ( ! $categoria ) {
		return array();
	}

	return nb_core_portada_obtener_posts(
		array(
			'cat'            => $categoria->term_id,
			'posts_per_page' => max( 1, (int) $cantidad ),
			'post__not_in'   => array_filter( array_map( 'intval', (array) $excluir ) ),
		)
	);
}

/**
 * Devuelve la categoria principal de una entrada.
 *
 * @param int $post_id ID de entrada.
 * @return WP_Term|null
 */
function nb_core_portada_categoria_post( $post_id ) {
	$categorias = get_the_category( $post_id );

	return ! empty( $categorias ) ? $categorias[0] : null;
}

/**
 * Crea un resumen limpio para tarjetas de portada.
 *
 * @param int $post_id ID de entrada.
 * @param int $palabras Longitud maxima.
 * @return string
 */
function nb_core_portada_resumen( $post_id, $palabras = 28 ) {
	$extracto = get_post_field( 'post_excerpt', $post_id );

	if ( '' === trim( $extracto ) ) {
		$extracto = get_post_field( 'post_content', $post_id );
	}

	$extracto = wp_strip_all_tags( strip_shortcodes( $extracto ) );

	return wp_trim_words( $extracto, max( 8, (int) $palabras ), '…' );
}

/**
 * Renderiza una tarjeta reutilizable de noticia.
 *
 * @param WP_Post $post Entrada.
 * @param string  $variante principal|secundaria|compacta|categoria.
 * @param bool    $mostrar_resumen Si muestra resumen.
 * @return void
 */
function nb_core_portada_render_tarjeta( $post, $variante = 'categoria', $mostrar_resumen = false ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$categoria = nb_core_portada_categoria_post( $post->ID );
	$clase     = sanitize_html_class( $variante );
	?>
	<article class="nb-portada-tarjeta nb-portada-tarjeta--<?php echo esc_attr( $clase ); ?>">
		<?php if ( has_post_thumbnail( $post ) ) : ?>
			<a class="nb-portada-tarjeta__imagen" href="<?php echo esc_url( get_permalink( $post ) ); ?>" tabindex="-1" aria-hidden="true">
				<?php echo get_the_post_thumbnail( $post, 'large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
			</a>
		<?php endif; ?>

		<div class="nb-portada-tarjeta__contenido">
			<?php if ( $categoria ) : ?>
				<a class="nb-portada-etiqueta" href="<?php echo esc_url( get_category_link( $categoria ) ); ?>">
					<?php echo esc_html( $categoria->name ); ?>
				</a>
			<?php endif; ?>

			<h2 class="nb-portada-tarjeta__titulo">
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
			</h2>

			<p class="nb-portada-tarjeta__meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>"><?php echo esc_html( get_the_date( 'j M Y', $post ) ); ?></time>
			</p>

			<?php if ( $mostrar_resumen ) : ?>
				<p class="nb-portada-tarjeta__resumen"><?php echo esc_html( nb_core_portada_resumen( $post->ID ) ); ?></p>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Renderiza la cabecera de una seccion editorial.
 *
 * @param string       $titulo Titulo visible.
 * @param WP_Term|null $categoria Categoria opcional.
 * @return void
 */
function nb_core_portada_render_cabecera_seccion( $titulo, $categoria = null ) {
	?>
	<header class="nb-portada-seccion__cabecera">
		<h2><?php echo esc_html( $titulo ); ?></h2>
		<?php if ( $categoria instanceof WP_Term ) : ?>
			<a href="<?php echo esc_url( get_category_link( $categoria ) ); ?>">Ver más</a>
		<?php endif; ?>
	</header>
	<?php
}
