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

$publicidad_archivos = array(
	'abrahany' => dirname( __DIR__ ) . '/assets/images/abrahany-studio-nails.webp.txt',
	'kenia'    => dirname( __DIR__ ) . '/assets/images/kenia-rangel-diseno-grafico.webp.txt',
);
$publicidad_imagenes = array();

foreach ( $publicidad_archivos as $publicidad_clave => $publicidad_archivo ) {
	$publicidad_imagenes[ $publicidad_clave ] = '';

	if ( is_readable( $publicidad_archivo ) ) {
		$publicidad_base64 = trim( (string) file_get_contents( $publicidad_archivo ) );

		if ( '' !== $publicidad_base64 ) {
			$publicidad_imagenes[ $publicidad_clave ] = 'data:image/webp;base64,' . $publicidad_base64;
		}
	}
}

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

		<?php if ( ! empty( $publicidad_imagenes['abrahany'] ) || ! empty( $publicidad_imagenes['kenia'] ) ) : ?>
			<style>
				.nb-portada-publicidad__grid {
					display: grid;
					grid-template-columns: repeat( 2, minmax( 0, 1fr ) );
					gap: clamp( 0.85rem, 2vw, 1.25rem );
				}

				.nb-portada-publicidad__anuncio {
					display: block;
					overflow: hidden;
					border: 1px solid rgba( 13, 36, 75, 0.14 );
					border-radius: calc( var( --nb-radio ) + 4px );
					background: #ffffff;
					box-shadow: 0 14px 36px rgba( 15, 35, 65, 0.09 );
					transition: transform 180ms ease, box-shadow 180ms ease;
				}

				.nb-portada-publicidad__anuncio:hover,
				.nb-portada-publicidad__anuncio:focus-visible {
					transform: translateY( -2px );
					box-shadow: 0 18px 44px rgba( 15, 35, 65, 0.15 );
				}

				.nb-portada-publicidad__anuncio img {
					display: block;
					width: 100%;
					height: auto;
					aspect-ratio: 3 / 2;
					object-fit: cover;
				}

				@media ( max-width: 700px ) {
					.nb-portada-publicidad__grid {
						grid-template-columns: 1fr;
					}
				}

				@media ( prefers-reduced-motion: reduce ) {
					.nb-portada-publicidad__anuncio {
						transition: none;
					}
					.nb-portada-publicidad__anuncio:hover,
					.nb-portada-publicidad__anuncio:focus-visible {
						transform: none;
					}
				}
			</style>

			<aside class="nb-portada-publicidad" aria-label="Publicidad">
				<div class="nb-portada-publicidad__grid">
					<?php if ( ! empty( $publicidad_imagenes['abrahany'] ) ) : ?>
						<a
							class="nb-portada-publicidad__anuncio"
							href="https://wa.me/qr/LRVX44AB262ZP1"
							target="_blank"
							rel="noopener noreferrer sponsored"
							aria-label="Contactar a Abrahany Studio Nails por WhatsApp"
						>
							<img
								src="<?php echo esc_attr( $publicidad_imagenes['abrahany'] ); ?>"
								alt="Abrahany Studio Nails: servicios de uñas, cejas y pestañas"
								width="360"
								height="240"
								loading="lazy"
								decoding="async"
							>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $publicidad_imagenes['kenia'] ) ) : ?>
						<a
							class="nb-portada-publicidad__anuncio"
							href="https://wa.me/message/AODEYZT5B7ZWK1"
							target="_blank"
							rel="noopener noreferrer sponsored"
							aria-label="Contactar a Kenia Rangel, diseñadora gráfica, por WhatsApp"
						>
							<img
								src="<?php echo esc_attr( $publicidad_imagenes['kenia'] ); ?>"
								alt="Kenia Rangel, diseñadora gráfica: banners, tarjetas, flyers, logotipos y material POP"
								width="360"
								height="240"
								loading="lazy"
								decoding="async"
							>
						</a>
					<?php endif; ?>
				</div>
			</aside>
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
