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

$publicidad_svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" role="img" aria-labelledby="title desc">
  <title id="title">Pescados RS</title>
  <desc id="desc">Publicidad de Pescados RS, el pescado más fresco.</desc>
  <defs>
    <linearGradient id="cielo" x1="0" x2="0" y1="0" y2="1">
      <stop offset="0%" stop-color="#dff1ff"/>
      <stop offset="100%" stop-color="#f8fcff"/>
    </linearGradient>
    <linearGradient id="mar" x1="0" x2="0" y1="0" y2="1">
      <stop offset="0%" stop-color="#7cc4e8"/>
      <stop offset="100%" stop-color="#2d77b2"/>
    </linearGradient>
    <linearGradient id="arena" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="#f9f3e7"/>
      <stop offset="100%" stop-color="#eef3fb"/>
    </linearGradient>
  </defs>
  <rect width="1000" height="1000" rx="28" fill="url(#cielo)"/>
  <rect y="610" width="1000" height="390" fill="url(#mar)"/>
  <circle cx="810" cy="120" r="68" fill="#e0a723" opacity="0.95"/>
  <path d="M0 620 C120 590 260 610 380 640 C530 678 690 672 1000 620 L1000 735 L0 735 Z" fill="#0d4d86" opacity="0.28"/>
  <path d="M0 660 C160 620 260 640 420 680 C600 722 760 716 1000 650 L1000 770 L0 770 Z" fill="#ffffff" opacity="0.15"/>
  <g opacity="0.85" fill="#0d4d86">
    <path d="M118 128c26-20 58-21 91-2-21 0-38 10-51 28-13-10-26-19-40-26z"/>
    <path d="M186 145c18-14 41-15 64-1-15 0-27 8-36 20-9-7-18-13-28-19z"/>
  </g>
  <rect x="58" y="62" width="884" height="876" rx="28" fill="url(#arena)" opacity="0.94"/>
  <g transform="translate(140 90)">
    <ellipse cx="360" cy="78" rx="160" ry="55" fill="none" stroke="#0d4d86" stroke-width="14"/>
    <circle cx="455" cy="66" r="7" fill="#0d4d86"/>
    <polygon points="194,78 138,48 138,108" fill="#0d4d86"/>
    <path d="M230 82c45 28 90 31 144 21" fill="none" stroke="#0d4d86" stroke-width="12" stroke-linecap="round"/>
    <path d="M236 55c40-17 76-17 116-8" fill="none" stroke="#e0a723" stroke-width="10" stroke-linecap="round"/>
  </g>
  <text x="500" y="250" text-anchor="middle" font-size="110" font-weight="800" font-family="Georgia, 'Times New Roman', serif" fill="#113a74">Pescados <tspan fill="#d6a019">RS</tspan></text>
  <rect x="150" y="288" width="700" height="112" rx="56" fill="#113a74"/>
  <text x="500" y="364" text-anchor="middle" font-size="72" font-style="italic" font-family="Brush Script MT, Segoe Script, cursive" fill="#ffffff">El pescado más fresco</text>
  <rect x="90" y="430" width="820" height="64" rx="32" fill="#154d88" opacity="0.95"/>
  <text x="500" y="472" text-anchor="middle" font-size="28" font-weight="700" font-family="Arial, Helvetica, sans-serif" fill="#ffffff">Lebranche • Róbalo • Curvina • Cojinúa • Pargo • Medregal • Camarones</text>
  <text x="500" y="545" text-anchor="middle" font-size="35" font-weight="700" font-family="Arial, Helvetica, sans-serif" fill="#123b73">Pescados y mariscos frescos en Tacarigua de la Laguna.</text>
  <text x="500" y="590" text-anchor="middle" font-size="35" font-weight="700" font-family="Arial, Helvetica, sans-serif" fill="#123b73">También mezcla para paellas.</text>
  <g transform="translate(56 700)">
    <rect x="0" y="0" width="290" height="140" rx="24" fill="#0d4d86"/>
    <circle cx="54" cy="72" r="22" fill="#e0a723"/>
    <path d="M54 49v43" stroke="#ffffff" stroke-width="8" stroke-linecap="round"/>
    <path d="M34 92c17-11 39-11 56 0" fill="none" stroke="#ffffff" stroke-width="8" stroke-linecap="round"/>
    <text x="106" y="68" font-size="28" font-family="Brush Script MT, Segoe Script, cursive" fill="#ffffff">Sabor del</text>
    <text x="106" y="108" font-size="36" font-weight="700" font-family="Brush Script MT, Segoe Script, cursive" fill="#ffd26a">litoral venezolano</text>
  </g>
  <g transform="translate(735 685)">
    <rect x="0" y="0" width="185" height="165" rx="18" fill="#f6ecd9" stroke="#d4c09a" stroke-width="4"/>
    <text x="92" y="58" text-anchor="middle" font-size="26" font-family="Brush Script MT, Segoe Script, cursive" fill="#113a74">Del mar</text>
    <text x="92" y="92" text-anchor="middle" font-size="26" font-family="Brush Script MT, Segoe Script, cursive" fill="#113a74">a tu mesa</text>
    <path d="M48 120c22-12 66-12 88 0" fill="none" stroke="#113a74" stroke-width="5" stroke-linecap="round"/>
  </g>
  <g transform="translate(120 720)">
    <ellipse cx="120" cy="96" rx="114" ry="58" fill="#cfd8dc"/>
    <polygon points="22,96 -24,64 -24,128" fill="#b6c3cb"/>
    <circle cx="188" cy="84" r="8" fill="#1b2d3f"/>
    <path d="M52 116c48 26 114 26 168-2" fill="none" stroke="#8fa3ad" stroke-width="9" stroke-linecap="round"/>
  </g>
  <g transform="translate(328 760)">
    <ellipse cx="100" cy="80" rx="96" ry="48" fill="#d8a37e"/>
    <polygon points="10,80 -24,52 -24,108" fill="#c88357"/>
    <circle cx="150" cy="70" r="7" fill="#1b2d3f"/>
    <path d="M40 92c42 18 92 18 136-2" fill="none" stroke="#b66f45" stroke-width="8" stroke-linecap="round"/>
  </g>
  <g transform="translate(542 770)">
    <path d="M12 75c25-42 80-36 100 0-18 10-30 18-46 44-17-21-31-32-54-44z" fill="#f18554"/>
    <path d="M44 57c8-12 27-14 38 0" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
    <path d="M7 91c34 12 74 12 118 0" fill="none" stroke="#d96a37" stroke-width="6" stroke-linecap="round"/>
  </g>
  <g transform="translate(718 792)">
    <circle cx="24" cy="24" r="22" fill="#f2d564" opacity="0.96"/>
    <path d="M10 24h28" stroke="#ffffff" stroke-width="4" opacity="0.9"/>
    <path d="M24 10v28" stroke="#ffffff" stroke-width="4" opacity="0.9"/>
  </g>
</svg>
SVG;

$publicidad_imagen = 'data:image/svg+xml;utf8,' . rawurlencode( $publicidad_svg );

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

		<aside class="nb-portada-publicidad" aria-label="Publicidad">
			<a
				class="nb-portada-publicidad__tarjeta"
				href="https://wa.me/qr/CJJQQ7PKHVBLO1"
				target="_blank"
				rel="noopener noreferrer sponsored"
				aria-label="Contactar a Pescados RS por WhatsApp"
			>
				<span class="nb-portada-publicidad__media">
					<img
						class="nb-portada-publicidad__imagen"
						src="<?php echo esc_attr( $publicidad_imagen ); ?>"
						alt="Pescados RS en Tacarigua de la Laguna: pescado fresco, camarones y mezcla para paellas"
						width="1000"
						height="1000"
						loading="lazy"
						decoding="async"
						style="aspect-ratio: 1 / 1;"
					>
				</span>
				<span class="nb-portada-publicidad__contenido">
					<span class="nb-portada-publicidad__etiqueta">Publicidad</span>
					<strong class="nb-portada-publicidad__titulo">Pescados RS</strong>
					<span class="nb-portada-publicidad__bajada">El pescado más fresco</span>
					<span class="nb-portada-publicidad__niveles">Lebranche · Róbalo · Curvina · Cojinúa · Pargo · Medregal · Camarones</span>
					<span class="nb-portada-publicidad__texto">Pescados y mariscos frescos en Tacarigua de la Laguna. También mezcla para paellas.</span>
					<span class="nb-portada-publicidad__cta">Contactar por WhatsApp <span aria-hidden="true">→</span></span>
				</span>
			</a>
		</aside>

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
