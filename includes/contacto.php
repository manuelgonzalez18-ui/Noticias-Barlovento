<?php
/**
 * Contacto y redes oficiales de Noticias Barlovento.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Devuelve las redes oficiales del medio.
 *
 * @return array<int,array{name:string,short:string,url:string}>
 */
function nb_core_contacto_redes() {
	return array(
		array(
			'name'  => 'Facebook',
			'short' => 'f',
			'url'   => 'https://www.facebook.com/share/17uDFSzjTh/',
		),
		array(
			'name'  => 'Instagram',
			'short' => 'IG',
			'url'   => 'https://www.instagram.com/noticiasbarlovento?igsh=MTBmZ3ZvcmRoNmJkdg==',
		),
		array(
			'name'  => 'Telegram',
			'short' => 'TG',
			'url'   => 'https://t.me/NoticiasBarlovento',
		),
		array(
			'name'  => 'X',
			'short' => 'X',
			'url'   => 'https://x.com/Noti_Barlovent0',
		),
		array(
			'name'  => 'YouTube',
			'short' => 'YT',
			'url'   => 'https://youtube.com/@noticiasbarlovento2511?si=aQYhxRKofxg-LoBp',
		),
	);
}

/**
 * Devuelve los canales directos de contacto.
 *
 * @return array<string,mixed>
 */
function nb_core_contacto_datos() {
	return array(
		'whatsapp' => array(
			array(
				'label' => '+57 313 4408030',
				'url'   => 'https://wa.me/573134408030',
			),
			array(
				'label' => '0412-381-8081',
				'url'   => 'https://wa.me/584123818081',
			),
		),
		'email'  => 'noticiasbarlovento@gmail.com',
		'social' => nb_core_contacto_redes(),
	);
}

/**
 * Agrega un acceso a Contacto al menu Principal sin modificar la base de datos.
 *
 * @param string   $items HTML de los elementos del menu.
 * @param stdClass $args  Argumentos de wp_nav_menu().
 * @return string
 */
function nb_core_contacto_agregar_menu( $items, $args ) {
	if ( false !== strpos( $items, 'nb-menu-contacto' ) ) {
		return $items;
	}

	$es_principal = false;
	$principal    = wp_get_nav_menu_object( 'Principal' );

	if ( ! empty( $args->menu ) ) {
		$menu_actual = wp_get_nav_menu_object( $args->menu );
		$es_principal = $menu_actual && $principal && (int) $menu_actual->term_id === (int) $principal->term_id;
	}

	if ( ! $es_principal && ! empty( $args->theme_location ) && $principal ) {
		$ubicaciones = get_nav_menu_locations();
		$es_principal = isset( $ubicaciones[ $args->theme_location ] ) && (int) $ubicaciones[ $args->theme_location ] === (int) $principal->term_id;
	}

	/* Respaldo para temas que no informan menu/theme_location en el argumento. */
	if ( ! $es_principal ) {
		$texto = wp_strip_all_tags( $items );
		$es_principal = false !== stripos( $texto, 'Quiénes somos' ) && false !== stripos( $texto, 'Inicio' );
	}

	if ( ! $es_principal ) {
		return $items;
	}

	return $items . sprintf(
		'<li class="menu-item nb-menu-contacto"><a href="%1$s">%2$s</a></li>',
		esc_url( home_url( '/#contacto' ) ),
		esc_html__( 'Contacto', 'noticiasbarlovento-core' )
	);
}
add_filter( 'wp_nav_menu_items', 'nb_core_contacto_agregar_menu', 20, 2 );

/**
 * Renderiza la sección de contacto al final del sitio.
 */
function nb_core_contacto_renderizar() {
	$datos = nb_core_contacto_datos();
	?>
	<section id="contacto" class="nb-contacto" aria-labelledby="nb-contacto-titulo">
		<div class="nb-contacto__interior">
			<header class="nb-contacto__cabecera">
				<p class="nb-contacto__eyebrow">Noticias Barlovento</p>
				<h2 id="nb-contacto-titulo">Contacto y redes</h2>
				<p>Comunícate con nuestra redacción o síguenos en nuestros canales oficiales.</p>
			</header>

			<div class="nb-contacto__grid">
				<div class="nb-contacto__panel">
					<h3>WhatsApp</h3>
					<div class="nb-contacto__acciones">
						<?php foreach ( $datos['whatsapp'] as $whatsapp ) : ?>
							<a class="nb-contacto__boton nb-contacto__boton--whatsapp" href="<?php echo esc_url( $whatsapp['url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<span aria-hidden="true">WA</span>
								<?php echo esc_html( $whatsapp['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>

					<h3>Correo electrónico</h3>
					<a class="nb-contacto__correo" href="mailto:<?php echo esc_attr( $datos['email'] ); ?>"><?php echo esc_html( $datos['email'] ); ?></a>
				</div>

				<div class="nb-contacto__panel">
					<h3>Redes oficiales</h3>
					<div class="nb-contacto__redes" aria-label="Redes sociales oficiales">
						<?php foreach ( $datos['social'] as $red ) : ?>
							<a href="<?php echo esc_url( $red['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $red['name'] ); ?>">
								<span class="nb-contacto__red-icono" aria-hidden="true"><?php echo esc_html( $red['short'] ); ?></span>
								<span><?php echo esc_html( $red['name'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
}
add_action( 'wp_footer', 'nb_core_contacto_renderizar', 5 );
