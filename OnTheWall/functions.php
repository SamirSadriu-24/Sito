<?php
/**
 * OnTheWall — functions and definitions.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ONTHEWALL_VERSION' ) ) {
	define( 'ONTHEWALL_VERSION', '1.0.0' );
}

/**
 * Setup del tema.
 */
function onthewall_setup() {
	load_theme_textdomain( 'onthewall', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Formato delle immagini della griglia "The Wall".
	add_image_size( 'onthewall-square', 640, 640, true );

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Navigazione principale', 'onthewall' ),
		)
	);
}
add_action( 'after_setup_theme', 'onthewall_setup' );

/**
 * Restituisce la versione di un asset del tema basata sul mtime del file.
 *
 * @param string $relative_path Percorso relativo alla root del tema.
 * @return string
 */
function onthewall_asset_version( $relative_path ) {
	$file = get_template_directory() . $relative_path;

	return file_exists( $file ) ? (string) filemtime( $file ) : ONTHEWALL_VERSION;
}

/**
 * Accoda fogli di stile e script.
 */
function onthewall_enqueue_assets() {
	$theme_uri = get_template_directory_uri();

	// Google Fonts: Anton 400, Inter 400/500/700, JetBrains Mono 400/700.
	wp_enqueue_style(
		'onthewall-fonts',
		'https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;700&family=JetBrains+Mono:wght@400;700&display=swap',
		array(),
		null
	);

	// Foglio di stile principale del design.
	wp_enqueue_style(
		'onthewall-main',
		$theme_uri . '/assets/css/main.css',
		array( 'onthewall-fonts' ),
		onthewall_asset_version( '/assets/css/main.css' )
	);

	// Utility del tema (sostituiscono gli attributi style="" della preview).
	wp_enqueue_style(
		'onthewall-theme',
		$theme_uri . '/assets/css/theme.css',
		array( 'onthewall-main' ),
		onthewall_asset_version( '/assets/css/theme.css' )
	);

	// style.css (header del tema) accodato per convenzione WordPress.
	wp_enqueue_style(
		'onthewall-style',
		get_stylesheet_uri(),
		array( 'onthewall-theme' ),
		ONTHEWALL_VERSION
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'onthewall_enqueue_assets' );

/**
 * Preconnect ai domini dei font, come nella preview approvata.
 *
 * @param array  $urls          URL da stampare.
 * @param string $relation_type Tipo di relazione.
 * @return array
 */
function onthewall_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && wp_style_is( 'onthewall-fonts', 'enqueued' ) ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'onthewall_resource_hints', 10, 2 );

require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/class-onthewall-nav-walker.php';
