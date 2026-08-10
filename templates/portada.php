<?php
/**
 * Portada editorial de Noticias Barlovento.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$destacados = nb_core_portada_obtener_destacados( 5 );
$principal  = ! empty( $destacados ) ? array_shift( $destacados ) : null;
$usados     = array();

if ( $principal instanceof WP_Post ) {
	$usados[] = $principal->ID;
}

foreach ( $destacados as $destacado ) {
	$usados[] = $destacado->ID;
}

$ultimas = nb_core_portada_obtener_posts(
	array(
		'posts_per_page' => 6,
		'post__not_in'   => $usados,
	)
);
$usados  = array_merge( $usados, wp_list_pluck( $ultimas, 'ID' ) );

$secciones_principales = array( 'Barlovento', 'Regional', 'Nacional' );
$secciones_servicio    = array( 'Cultura', 'Deporte', 'Salud', 'Turismo' );
?>

<main id="primary" class="nb-portada" role="main">
	<div class="nb-portada__contenedor">
		<?php if ( $principal instanceof WP_Post ) : ?>
			<section class="nb-portada-apertura" aria-labelledby="nb-portada-destacadas">
				<h1 id="nb-portada-destacadas" class="screen-reader-text">Noticias destacadas</h1>

				<div class="nb-portada-apertura__principal">
					<?php nb_core_portada_render_tarjeta( $principal, 'principal', true ); ?>
				</div>

				<?php if ( ! empty( $destacados ) ) : ?>
					<div class="nb-portada-apertura__secundarias">
						<?php foreach ( $destacados as $destacado ) : ?>
							<?php nb_core_portada_render_tarjeta( $destacado, 'secundaria', false ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $ultimas ) ) : ?>
			<section class="nb-portada-seccion nb-portada-ultimas">
				<?php nb_core_portada_render_cabecera_seccion( 'Lo último' ); ?>
				<ol class="nb-portada-ultimas__lista">
					<?php foreach ( $ultimas as $ultima ) : ?>
						<?php $categoria_ultima = nb_core_portada_categoria_post( $ultima->ID ); ?>
						<li>
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $ultima ) ); ?>"><?php echo esc_html( get_the_date( 'H:i', $ultima ) ); ?></time>
							<div>
								<?php if ( $categoria_ultima ) : ?>
									<span class="nb-portada-ultimas__categoria"><?php echo esc_html( $categoria_ultima->name ); ?></span>
								<?php endif; ?>
								<a href="<?php echo esc_url( get_permalink( $ultima ) ); ?>"><?php echo esc_html( get_the_title( $ultima ) ); ?></a>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			</section>
		<?php endif; ?>

		<?php foreach ( $secciones_principales as $nombre_seccion ) : ?>
			<?php
			$categoria = nb_core_portada_categoria( $nombre_seccion );
			$posts     = nb_core_portada_posts_categoria( $nombre_seccion, 4, $usados );
			?>
			<?php if ( $categoria && ! empty( $posts ) ) : ?>
				<section class="nb-portada-seccion nb-portada-seccion--<?php echo esc_attr( sanitize_title( $nombre_seccion ) ); ?>">
					<?php nb_core_portada_render_cabecera_seccion( $nombre_seccion, $categoria ); ?>
					<div class="nb-portada-seccion__grid nb-portada-seccion__grid--principal">
						<?php foreach ( $posts as $indice => $post_seccion ) : ?>
							<?php nb_core_portada_render_tarjeta( $post_seccion, 0 === $indice ? 'categoria-destacada' : 'compacta', 0 === $indice ); ?>
						<?php endforeach; ?>
					</div>
				</section>
				<?php $usados = array_merge( $usados, wp_list_pluck( $posts, 'ID' ) ); ?>
			<?php endif; ?>
		<?php endforeach; ?>

		<div class="nb-portada-servicios">
			<?php foreach ( $secciones_servicio as $nombre_seccion ) : ?>
				<?php
				$categoria = nb_core_portada_categoria( $nombre_seccion );
				$posts     = nb_core_portada_posts_categoria( $nombre_seccion, 3, $usados );
				?>
				<?php if ( $categoria && ! empty( $posts ) ) : ?>
					<section class="nb-portada-seccion nb-portada-seccion--mini">
						<?php nb_core_portada_render_cabecera_seccion( $nombre_seccion, $categoria ); ?>
						<div class="nb-portada-seccion__grid nb-portada-seccion__grid--mini">
							<?php foreach ( $posts as $post_seccion ) : ?>
								<?php nb_core_portada_render_tarjeta( $post_seccion, 'mini', false ); ?>
							<?php endforeach; ?>
						</div>
					</section>
					<?php $usados = array_merge( $usados, wp_list_pluck( $posts, 'ID' ) ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</main>

<?php
get_footer();
