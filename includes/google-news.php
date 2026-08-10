<?php
/**
 * Integracion tecnica con Google News y metadatos de noticias.
 *
 * @package NoticiasBarloventoCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL publica del sitemap exclusivo para Google News.
 *
 * @return string
 */
function nb_core_google_news_sitemap_url() {
	return home_url( '/news-sitemap.xml' );
}

/**
 * Indica si una noticia debe quedar fuera de Google News.
 *
 * La entrada sigue siendo indexable por Google Search. Se excluye de News si
 * el editor la marco como republicada integramente o si es contenido
 * patrocinado.
 *
 * @param int $post_id ID de la entrada.
 * @return bool
 */
function nb_core_google_news_excluir_post( $post_id ) {
	$republicado = (string) get_post_meta( $post_id, '_nb_republicado_google_news', true );
	$tipo        = (string) get_post_meta( $post_id, '_nb_tipo_contenido', true );

	return '1' === $republicado || 'patrocinado' === $tipo;
}

/**
 * Devuelve las noticias publicadas durante las ultimas 48 horas.
 *
 * Google recomienda que el sitemap de News solo conserve articulos de los
 * ultimos dos dias. El limite de 1000 tambien coincide con la especificacion
 * del sitemap de noticias.
 *
 * @return WP_Post[]
 */
function nb_core_google_news_posts_recientes() {
	$entradas = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 1000,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => false,
			'no_found_rows'       => true,
			'date_query'          => array(
				array(
					'column'    => 'post_date_gmt',
					'after'     => gmdate( 'Y-m-d H:i:s', time() - ( 2 * DAY_IN_SECONDS ) ),
					'inclusive' => true,
				),
			),
		)
	);

	return array_values(
		array_filter(
			$entradas,
			function ( $entrada ) {
				return $entrada instanceof WP_Post && ! nb_core_google_news_excluir_post( $entrada->ID );
			}
		)
	);
}

/**
 * Convierte el idioma de WordPress a un codigo valido para Google News.
 *
 * @return string
 */
function nb_core_google_news_idioma() {
	$idioma = strtolower( (string) get_bloginfo( 'language' ) );
	$partes = preg_split( '/[-_]/', $idioma );
	$codigo = ! empty( $partes[0] ) ? $partes[0] : 'es';

	return preg_match( '/^[a-z]{2,3}$/', $codigo ) ? $codigo : 'es';
}

/**
 * Escapa texto para un documento XML.
 *
 * @param string $texto Texto original.
 * @return string
 */
function nb_core_google_news_xml( $texto ) {
	if ( function_exists( 'esc_xml' ) ) {
		return esc_xml( $texto );
	}

	return htmlspecialchars( $texto, ENT_QUOTES | ENT_XML1, 'UTF-8' );
}

/**
 * Imprime el sitemap de Google News.
 */
function nb_core_google_news_render_sitemap() {
	$entradas    = nb_core_google_news_posts_recientes();
	$publicacion = get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'Noticias Barlovento';
	$idioma      = nb_core_google_news_idioma();

	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex, follow', true );

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";

	foreach ( $entradas as $entrada ) {
		$url      = get_permalink( $entrada );
		$titulo   = wp_strip_all_tags( get_the_title( $entrada ) );
		$fecha    = get_post_time( DATE_W3C, true, $entrada );

		if ( ! $url || ! $titulo || ! $fecha ) {
			continue;
		}

		echo "  <url>\n";
		echo '    <loc>' . nb_core_google_news_xml( $url ) . "</loc>\n";
		echo "    <news:news>\n";
		echo "      <news:publication>\n";
		echo '        <news:name>' . nb_core_google_news_xml( $publicacion ) . "</news:name>\n";
		echo '        <news:language>' . nb_core_google_news_xml( $idioma ) . "</news:language>\n";
		echo "      </news:publication>\n";
		echo '      <news:publication_date>' . nb_core_google_news_xml( $fecha ) . "</news:publication_date>\n";
		echo '      <news:title>' . nb_core_google_news_xml( $titulo ) . "</news:title>\n";
		echo "    </news:news>\n";
		echo "  </url>\n";
	}

	echo "</urlset>\n";
	exit;
}

/**
 * Intercepta /news-sitemap.xml sin depender de reglas de reescritura.
 *
 * @param WP $wp Instancia principal de WordPress.
 */
function nb_core_google_news_interceptar_sitemap( $wp ) {
	$solicitud = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : '';

	if ( 'news-sitemap.xml' === $solicitud ) {
		nb_core_google_news_render_sitemap();
	}
}
add_action( 'parse_request', 'nb_core_google_news_interceptar_sitemap', 1 );

/**
 * Publica el sitemap de News y acceso explicito para Googlebot-News en robots.
 *
 * @param string $salida Contenido de robots.txt.
 * @param bool   $publico Si el sitio permite indexacion.
 * @return string
 */
function nb_core_google_news_robots_txt( $salida, $publico ) {
	if ( ! $publico ) {
		return $salida;
	}

	$salida = rtrim( $salida ) . "\n";

	if ( false === stripos( $salida, 'User-agent: Googlebot-News' ) ) {
		$salida .= "\nUser-agent: Googlebot-News\nAllow: /\n";
	}

	$sitemap = nb_core_google_news_sitemap_url();
	if ( false === strpos( $salida, $sitemap ) ) {
		$salida .= "\nSitemap: " . $sitemap . "\n";
	}

	return $salida;
}
add_filter( 'robots_txt', 'nb_core_google_news_robots_txt', 20, 2 );

/**
 * Permite a Google usar vistas previas de imagen grandes.
 *
 * @param array $robots Directivas calculadas por WordPress.
 * @return array
 */
function nb_core_google_news_robots_meta( $robots ) {
	if ( '1' === (string) get_option( 'blog_public' ) ) {
		$robots['max-image-preview'] = 'large';
	}

	return $robots;
}
add_filter( 'wp_robots', 'nb_core_google_news_robots_meta', 20 );

/**
 * Excluye solo de Google News las piezas republicadas/patrocinadas.
 */
function nb_core_google_news_meta_especifica() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( $post_id && nb_core_google_news_excluir_post( $post_id ) ) {
		echo '<meta name="Googlebot-News" content="noindex, follow">' . "\n";
	}
}
add_action( 'wp_head', 'nb_core_google_news_meta_especifica', 2 );

/**
 * URL del logo institucional para datos estructurados.
 *
 * @return string
 */
function nb_core_google_news_logo_url() {
	$icono = get_site_icon_url( 512 );
	if ( $icono ) {
		return $icono;
	}

	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $logo ) {
			return $logo;
		}
	}

	return '';
}

/**
 * Devuelve la descripcion de una noticia para schema.org.
 *
 * @param int $post_id ID de la entrada.
 * @return string
 */
function nb_core_google_news_descripcion( $post_id ) {
	if ( has_excerpt( $post_id ) ) {
		return wp_strip_all_tags( get_the_excerpt( $post_id ) );
	}

	$contenido = (string) get_post_field( 'post_content', $post_id );
	$contenido = wp_strip_all_tags( strip_shortcodes( $contenido ) );

	return wp_trim_words( $contenido, 35, '…' );
}

/**
 * Construye la entidad editorial principal.
 *
 * @return array<string,mixed>
 */
function nb_core_google_news_organizacion_schema() {
	$organizacion = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'Noticias Barlovento',
		'url'   => home_url( '/' ),
	);

	$logo = nb_core_google_news_logo_url();
	if ( $logo ) {
		$organizacion['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => $logo,
		);
	}

	if ( function_exists( 'nb_core_contacto_datos' ) ) {
		$contacto = nb_core_contacto_datos();
		if ( ! empty( $contacto['email'] ) ) {
			$organizacion['email'] = $contacto['email'];
		}
	}

	if ( function_exists( 'nb_core_contacto_redes' ) ) {
		$redes = array();
		foreach ( nb_core_contacto_redes() as $red ) {
			if ( ! empty( $red['url'] ) ) {
				$redes[] = $red['url'];
			}
		}
		if ( $redes ) {
			$organizacion['sameAs'] = array_values( array_unique( $redes ) );
		}
	}

	$politica = get_page_by_path( 'politica-editorial' );
	if ( $politica instanceof WP_Post && 'publish' === $politica->post_status ) {
		$organizacion['publishingPrinciples'] = get_permalink( $politica );
	}

	return $organizacion;
}

/**
 * Construye el autor de la noticia para schema.org.
 *
 * @param int $post_id ID de la entrada.
 * @return array<string,mixed>
 */
function nb_core_google_news_autor_schema( $post_id ) {
	$autor_id     = (int) get_post_field( 'post_author', $post_id );
	$nombre       = trim( (string) get_the_author_meta( 'display_name', $autor_id ) );
	$url_autor    = $autor_id ? get_author_posts_url( $autor_id ) : '';
	$es_redaccion = '' === $nombre || false !== stripos( $nombre, 'Noticias Barlovento' );

	if ( $es_redaccion ) {
		return array(
			'@type' => 'Organization',
			'name'  => 'Noticias Barlovento',
			'url'   => home_url( '/' ),
		);
	}

	$autor = array(
		'@type' => 'Person',
		'name'  => $nombre,
		'url'   => $url_autor,
	);

	$cargo = trim( (string) get_user_meta( $autor_id, '_nb_cargo_editorial', true ) );
	if ( $cargo ) {
		$autor['jobTitle'] = $cargo;
	}

	$web = esc_url_raw( (string) get_the_author_meta( 'user_url', $autor_id ) );
	if ( $web ) {
		$autor['sameAs'] = array( $web );
	}

	return $autor;
}

/**
 * Imprime Organization y NewsArticle en JSON-LD.
 */
function nb_core_google_news_schema() {
	if ( is_admin() || is_feed() || is_robots() ) {
		return;
	}

	$grafo = array( nb_core_google_news_organizacion_schema() );

	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return;
		}

		$articulo = array(
			'@type'               => 'NewsArticle',
			'@id'                 => get_permalink( $post_id ) . '#newsarticle',
			'headline'            => wp_strip_all_tags( get_the_title( $post_id ) ),
			'description'         => nb_core_google_news_descripcion( $post_id ),
			'datePublished'       => get_post_time( DATE_W3C, false, $post_id ),
			'dateModified'        => get_post_modified_time( DATE_W3C, false, $post_id ),
			'inLanguage'          => get_bloginfo( 'language' ),
			'isAccessibleForFree' => true,
			'mainEntityOfPage'    => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $post_id ),
			),
			'author'              => array( nb_core_google_news_autor_schema( $post_id ) ),
			'publisher'           => array( '@id' => home_url( '/#organization' ) ),
		);

		$imagen_id = get_post_thumbnail_id( $post_id );
		if ( $imagen_id ) {
			$imagen = wp_get_attachment_image_src( $imagen_id, 'full' );
			if ( is_array( $imagen ) && ! empty( $imagen[0] ) ) {
				$articulo['image'] = array( $imagen[0] );
			}
		}

		$categorias = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $categorias ) && $categorias ) {
			$articulo['articleSection'] = array_values( $categorias );
		}

		$etiquetas = wp_get_post_terms( $post_id, 'post_tag', array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $etiquetas ) && $etiquetas ) {
			$articulo['keywords'] = array_values( $etiquetas );
		}

		$localidad = trim( (string) get_post_meta( $post_id, '_nb_localidad', true ) );
		if ( $localidad ) {
			$articulo['contentLocation'] = array(
				'@type' => 'Place',
				'name'  => $localidad,
			);
		}

		$grafo[] = $articulo;
	}

	$datos = array(
		'@context' => 'https://schema.org',
		'@graph'   => $grafo,
	);

	echo '<script type="application/ld+json" id="nb-core-google-news-schema">';
	echo wp_json_encode( $datos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	echo '</script>' . "\n";
}
add_action( 'wp_head', 'nb_core_google_news_schema', 30 );

/**
 * Pantalla de control para el administrador.
 */
function nb_core_google_news_admin_menu() {
	add_management_page(
		'Google News',
		'Google News',
		'manage_options',
		'nb-google-news',
		'nb_core_google_news_admin_render'
	);
}
add_action( 'admin_menu', 'nb_core_google_news_admin_menu' );

/**
 * Renderiza el estado tecnico y los pasos que dependen de Search Console.
 */
function nb_core_google_news_admin_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$entradas = nb_core_google_news_posts_recientes();
	$publico  = '1' === (string) get_option( 'blog_public' );
	?>
	<div class="wrap">
		<h1>Google News — Noticias Barlovento</h1>
		<p>La integracion tecnica del sitio se gestiona desde Noticias Barlovento Core.</p>

		<table class="widefat striped" style="max-width:900px;">
			<tbody>
				<tr><th>Indexacion del sitio</th><td><?php echo $publico ? 'Habilitada' : '<strong>DESHABILITADA</strong>'; ?></td></tr>
				<tr><th>Sitemap de News</th><td><a href="<?php echo esc_url( nb_core_google_news_sitemap_url() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( nb_core_google_news_sitemap_url() ); ?></a></td></tr>
				<tr><th>Noticias elegibles en las ultimas 48 h</th><td><?php echo esc_html( (string) count( $entradas ) ); ?></td></tr>
				<tr><th>Datos estructurados</th><td>Organization + NewsArticle activos</td></tr>
				<tr><th>Googlebot-News</th><td>Permitido en robots.txt; republicados/patrocinados se excluyen de News individualmente</td></tr>
			</tbody>
		</table>

		<h2>Pasos externos</h2>
		<ol>
			<li>Verificar <strong>noticiasbarlovento.com</strong> como propiedad de dominio en Google Search Console.</li>
			<li>Enviar <code><?php echo esc_html( nb_core_google_news_sitemap_url() ); ?></code> en Sitemaps.</li>
			<li>Inspeccionar una noticia reciente y solicitar indexacion cuando la migracion sea reciente.</li>
			<li>Revisar Rendimiento → Google News para confirmar impresiones y clics cuando Google empiece a mostrar contenido.</li>
		</ol>
	</div>
	<?php
}
