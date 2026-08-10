<?php
/**
 * Plantilla editorial para una noticia individual.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="primary" class="nb-noticia" role="main">
	<?php while ( have_posts() ) : ?>
		<?php
		the_post();

		$post_id          = get_the_ID();
		$categorias       = get_the_category( $post_id );
		$categoria        = ! empty( $categorias ) ? $categorias[0] : null;
		$tipo             = nb_core_noticia_etiqueta_tipo( $post_id );
		$localidad        = nb_core_noticia_meta( $post_id, 'localidad' );
		$credito_foto     = nb_core_noticia_meta( $post_id, 'credito_foto' );
		$fuente_visible   = nb_core_noticia_fuente_visible( $post_id );
		$relacionadas     = nb_core_noticia_relacionadas( $post_id, 3 );
		$url_contacto     = nb_core_noticia_url_contacto();
		$url_actual       = get_permalink( $post_id );
		$titulo_compartir = get_the_title( $post_id );
		$publicado        = get_post_time( 'U', true, $post_id );
		$modificado       = get_post_modified_time( 'U', true, $post_id );
		$mostrar_cambio   = $modificado > ( $publicado + 300 );
		$imagen_id        = get_post_thumbnail_id( $post_id );
		$pie_imagen       = $imagen_id ? wp_get_attachment_caption( $imagen_id ) : '';
		?>

		<article <?php post_class( 'nb-noticia__articulo' ); ?>>
			<header class="nb-noticia__cabecera">
				<nav class="nb-noticia__migas" aria-label="Ruta de navegacion">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a>
					<?php if ( $categoria instanceof WP_Term ) : ?>
						<span aria-hidden="true">/</span>
						<a href="<?php echo esc_url( get_category_link( $categoria ) ); ?>"><?php echo esc_html( $categoria->name ); ?></a>
					<?php endif; ?>
				</nav>

				<div class="nb-noticia__etiquetas">
					<span class="nb-noticia__tipo"><?php echo esc_html( $tipo ); ?></span>
					<?php if ( '' !== $localidad ) : ?>
						<span class="nb-noticia__localidad"><?php echo esc_html( $localidad ); ?></span>
					<?php endif; ?>
				</div>

				<h1 class="nb-noticia__titulo"><?php the_title(); ?></h1>

				<?php if ( has_excerpt( $post_id ) ) : ?>
					<p class="nb-noticia__bajada"><?php echo esc_html( get_the_excerpt( $post_id ) ); ?></p>
				<?php endif; ?>

				<div class="nb-noticia__firma">
					<span>
						Por <a href="<?php echo esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( get_the_author() ); ?></a>
					</span>
					<span aria-hidden="true">·</span>
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
						<?php echo esc_html( get_the_date( get_option( 'date_format' ) . ' · ' . get_option( 'time_format' ) ) ); ?>
					</time>
					<?php if ( $mostrar_cambio ) : ?>
						<span class="nb-noticia__actualizada">
							Actualizado <?php echo esc_html( get_the_modified_date( get_option( 'date_format' ) . ' · ' . get_option( 'time_format' ) ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<figure class="nb-noticia__principal">
					<?php
					echo get_the_post_thumbnail(
						$post_id,
						'full',
						array(
							'loading'       => 'eager',
							'decoding'      => 'async',
							'fetchpriority' => 'high',
						)
					);
					?>
					<?php if ( '' !== $pie_imagen || '' !== $credito_foto ) : ?>
						<figcaption>
							<?php if ( '' !== $pie_imagen ) : ?>
								<span><?php echo esc_html( $pie_imagen ); ?></span>
							<?php endif; ?>
							<?php if ( '' !== $credito_foto ) : ?>
								<span class="nb-noticia__credito">Foto: <?php echo esc_html( $credito_foto ); ?></span>
							<?php endif; ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

			<div class="nb-noticia__cuerpo">
				<?php the_content(); ?>
				<?php
				wp_link_pages(
					array(
						'before' => '<nav class="nb-noticia__paginas">',
						'after'  => '</nav>',
					)
				);
				?>
			</div>

			<footer class="nb-noticia__pie-editorial">
				<div class="nb-noticia__fuente">
					<strong>Fuente</strong>
					<span><?php echo esc_html( $fuente_visible ); ?></span>
				</div>

				<?php if ( ! empty( $categorias ) ) : ?>
					<div class="nb-noticia__categorias" aria-label="Categorias">
						<?php foreach ( $categorias as $categoria_item ) : ?>
							<a href="<?php echo esc_url( get_category_link( $categoria_item ) ); ?>"><?php echo esc_html( $categoria_item->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</footer>

			<section class="nb-noticia__acciones" aria-labelledby="nb-compartir-titulo">
				<h2 id="nb-compartir-titulo">Compartir esta noticia</h2>
				<div class="nb-noticia__compartir">
					<a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( $titulo_compartir . ' ' . $url_actual ) ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
					<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url_actual ) ); ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
					<a href="<?php echo esc_url( 'https://t.me/share/url?url=' . rawurlencode( $url_actual ) . '&text=' . rawurlencode( $titulo_compartir ) ); ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
				</div>
				<?php if ( '' !== $url_contacto ) : ?>
					<a class="nb-noticia__correccion" href="<?php echo esc_url( $url_contacto ); ?>">Solicitar una correccion</a>
				<?php endif; ?>
			</section>

			<?php if ( ! empty( $relacionadas ) ) : ?>
				<section class="nb-noticia__relacionadas" aria-labelledby="nb-relacionadas-titulo">
					<header class="nb-noticia__seccion-cabecera">
						<h2 id="nb-relacionadas-titulo">Noticias relacionadas</h2>
					</header>
					<div class="nb-noticia__relacionadas-grid">
						<?php foreach ( $relacionadas as $relacionada ) : ?>
							<article class="nb-noticia-relacionada">
								<?php if ( has_post_thumbnail( $relacionada ) ) : ?>
									<a class="nb-noticia-relacionada__imagen" href="<?php echo esc_url( get_permalink( $relacionada ) ); ?>" tabindex="-1" aria-hidden="true">
										<?php echo get_the_post_thumbnail( $relacionada, 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
									</a>
								<?php endif; ?>
								<h3><a href="<?php echo esc_url( get_permalink( $relacionada ) ); ?>"><?php echo esc_html( get_the_title( $relacionada ) ); ?></a></h3>
								<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $relacionada ) ); ?>"><?php echo esc_html( get_the_date( get_option( 'date_format' ), $relacionada ) ); ?></time>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<section class="nb-noticia__comentarios">
					<?php comments_template(); ?>
				</section>
			<?php endif; ?>
		</article>
	<?php endwhile; ?>
</main>

<?php
get_footer();
