<?php
/**
 * Estructura inicial del sitio: identidad, secciones, pagina institucional y menu.
 *
 * Corre una sola vez y deja constancia en la opcion 'nb_core_estructura'.
 * Cada paso comprueba antes si la pieza ya existe, asi que nunca pisa nada:
 * lo que se edite despues desde wp-admin manda siempre.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ejecuta el arranque si no se hizo todavia.
 *
 * Va en admin_init y no en init: escribe en la base de datos, y no tiene
 * sentido que eso ocurra en una visita anonima al sitio.
 */
function nb_core_estructura_inicial() {
	if ( get_option( 'nb_core_estructura' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	nb_core_estructura_identidad();
	nb_core_estructura_categorias();
	nb_core_estructura_pagina_institucional();
	nb_core_estructura_menu();

	update_option( 'nb_core_estructura', '1' );
}
add_action( 'admin_init', 'nb_core_estructura_inicial' );

/**
 * Pone titulo y descripcion del sitio, solo si siguen en los valores por
 * defecto de WordPress. Si alguien ya los escribio, no se tocan.
 */
function nb_core_estructura_identidad() {
	$por_defecto = array(
		'untitled site',
		'my wordpress blog',
		'just another wordpress site',
		'sitio sin titulo',
		'otro sitio realizado con wordpress',
	);

	$titulo = strtolower( trim( get_option( 'blogname' ) ) );
	if ( '' === $titulo || in_array( $titulo, $por_defecto, true ) ) {
		update_option( 'blogname', 'Noticias Barlovento' );
	}

	$descripcion = strtolower( trim( get_option( 'blogdescription' ) ) );
	if ( '' === $descripcion || in_array( $descripcion, $por_defecto, true ) ) {
		update_option(
			'blogdescription',
			'Periodismo comunitario e institucional desde el corazón de Barlovento'
		);
	}
}

/**
 * Devuelve las secciones del portal, en el orden en que van en el menu.
 *
 * @return array
 */
function nb_core_estructura_secciones() {
	return array(
		'Nacional',
		'Regional',
		'Barlovento',
		'Cultura',
		'Deporte',
		'Salud',
		'Turismo',
	);
}

/**
 * Crea las categorias que falten.
 */
function nb_core_estructura_categorias() {
	foreach ( nb_core_estructura_secciones() as $nombre ) {
		if ( ! term_exists( $nombre, 'category' ) ) {
			wp_insert_term( $nombre, 'category' );
		}
	}
}

/**
 * Crea la pagina "Quiénes somos" si no existe.
 *
 * El contenido va en formato de bloques para que se pueda editar comodo
 * desde el editor, no como un bloque HTML suelto.
 *
 * @return int|null ID de la pagina, o null si no se pudo crear.
 */
function nb_core_estructura_pagina_institucional() {
	$existente = get_page_by_path( 'quienes-somos' );

	if ( $existente ) {
		return $existente->ID;
	}

	$parrafos = array(
		'En Noticias Barlovento celebramos una década de labor periodística evolucionando hacia nuestro dominio propio. Somos un portal digital venezolano enfocado en la cobertura comunitaria, institucional y social del eje barloventeño, trascendiendo al estado Miranda y toda Venezuela.',
		'Ofrecemos información veraz sobre proyectos de desarrollo, comunicados oficiales e iniciativas públicas, combinada con reportajes, transmisiones en vivo y una sólida plataforma de atención directa para los reportes e incidencias de las comunidades.',
	);

	$contenido  = nb_core_estructura_bloque_titulo( 2, 'NOTICIAS BARLOVENTO: 10 años siendo la voz de nuestra región' );
	$contenido .= nb_core_estructura_bloque_titulo( 3, '10 años informando a Barlovento y Miranda' );

	foreach ( $parrafos as $parrafo ) {
		$contenido .= nb_core_estructura_bloque_parrafo( $parrafo );
	}

	$contenido .= nb_core_estructura_bloque_titulo( 3, 'Nuestra misión' );
	$contenido .= nb_core_estructura_bloque_parrafo( 'Noticias Barlovento es un medio de comunicación digital de vocación social, orientado a liderar la información y promover el desarrollo integral del eje barloventeño y el estado Miranda. Somos un espacio y una voz para la construcción de un periodismo ciudadano, donde las comunidades, la gestión pública y el poder popular son los protagonistas.' );

	$contenido .= nb_core_estructura_bloque_titulo( 3, 'Nuestra visión' );
	$contenido .= nb_core_estructura_bloque_parrafo( 'Ser el principal portal y multiplataforma noticiosa de servicio público con alcance regional y nacional, que produce y divulga contenido informativo, comunitario e institucional para una base de lectores amplia y leal; manteniendo siempre una visión integradora y solidaria de los pueblos de Barlovento y Venezuela.' );

	$id = wp_insert_post(
		array(
			'post_title'   => 'Quiénes somos',
			'post_name'    => 'quienes-somos',
			'post_content' => $contenido,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		)
	);

	return is_wp_error( $id ) ? null : $id;
}

/**
 * Arma un bloque de titulo del editor.
 *
 * @param int    $nivel Nivel del encabezado (2 a 6).
 * @param string $texto Texto del titulo.
 * @return string
 */
function nb_core_estructura_bloque_titulo( $nivel, $texto ) {
	return sprintf(
		"<!-- wp:heading {\"level\":%1\$d} -->\n<h%1\$d>%2\$s</h%1\$d>\n<!-- /wp:heading -->\n\n",
		(int) $nivel,
		esc_html( $texto )
	);
}

/**
 * Arma un bloque de parrafo del editor.
 *
 * @param string $texto Texto del parrafo.
 * @return string
 */
function nb_core_estructura_bloque_parrafo( $texto ) {
	return sprintf(
		"<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->\n\n",
		esc_html( $texto )
	);
}

/**
 * Crea el menu principal y lo asigna a la ubicacion del tema.
 *
 * Si ya existe un menu llamado "Principal", no se toca.
 */
function nb_core_estructura_menu() {
	if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	}

	$menu = wp_get_nav_menu_object( 'Principal' );

	if ( $menu ) {
		return;
	}

	$menu_id = wp_create_nav_menu( 'Principal' );

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	// Inicio: enlace relativo, para que siga funcionando cuando el sitio
	// termine de mudarse de /wp/ a la raiz del dominio.
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'  => 'Inicio',
			'menu-item-url'    => '/',
			'menu-item-status' => 'publish',
			'menu-item-type'   => 'custom',
		)
	);

	foreach ( nb_core_estructura_secciones() as $nombre ) {
		$termino = get_term_by( 'name', $nombre, 'category' );

		if ( ! $termino ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $nombre,
				'menu-item-object'    => 'category',
				'menu-item-object-id' => $termino->term_id,
				'menu-item-type'      => 'taxonomy',
				'menu-item-status'    => 'publish',
			)
		);
	}

	$pagina = get_page_by_path( 'quienes-somos' );

	if ( $pagina ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => 'Quiénes somos',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $pagina->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	nb_core_estructura_asignar_ubicacion( $menu_id );
}

/**
 * Asigna el menu a la ubicacion principal del tema.
 *
 * No sabemos como llama NewsExo a sus ubicaciones, asi que se busca la que
 * suene a menu principal y, si ninguna coincide, se usa la primera. Solo se
 * ocupan ubicaciones que esten libres.
 *
 * @param int $menu_id ID del menu recien creado.
 */
function nb_core_estructura_asignar_ubicacion( $menu_id ) {
	$ubicaciones = get_registered_nav_menus();

	if ( empty( $ubicaciones ) ) {
		return;
	}

	$asignadas = get_theme_mod( 'nav_menu_locations', array() );
	$elegida   = '';

	foreach ( array_keys( $ubicaciones ) as $clave ) {
		if ( preg_match( '/primary|main|principal|header|top/i', $clave ) ) {
			$elegida = $clave;
			break;
		}
	}

	if ( '' === $elegida ) {
		$claves  = array_keys( $ubicaciones );
		$elegida = reset( $claves );
	}

	if ( ! empty( $asignadas[ $elegida ] ) ) {
		return;
	}

	$asignadas[ $elegida ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $asignadas );
}
