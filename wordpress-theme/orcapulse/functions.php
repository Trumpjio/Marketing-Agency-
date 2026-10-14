<?php
/**
 * OrcaPulse theme functions.
 *
 * @package OrcaPulse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ORCAPULSE_VERSION', '1.0.0' );

/**
 * Theme setup.
 */
function orcapulse_setup() {
	// Title tag managed by WP.
	add_theme_support( 'title-tag' );
	// Featured images.
	add_theme_support( 'post-thumbnails' );
	// HTML5 markup.
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );
	// Automatic feed links.
	add_theme_support( 'automatic-feed-links' );
	// Custom logo.
	add_theme_support( 'custom-logo', array(
		'height'      => 48,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	// Responsive embeds.
	add_theme_support( 'responsive-embeds' );
	// Align-wide (Gutenberg).
	add_theme_support( 'align-wide' );
	// Editor styles.
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/glass.css' );

	// Register nav menus — these are what Elementor's Nav Menu widget reads.
	register_nav_menus( array(
		'primary'         => __( 'Primary (Mobile Panel)', 'orcapulse' ),
		'services'        => __( 'Services Menu', 'orcapulse' ),
		'industries'      => __( 'Industries Menu', 'orcapulse' ),
		'locations-gcc'   => __( 'Locations — GCC', 'orcapulse' ),
		'locations-global'=> __( 'Locations — Global', 'orcapulse' ),
		'studio'          => __( 'Studio (Work, Insights, About)', 'orcapulse' ),
		'footer-services' => __( 'Footer — Services', 'orcapulse' ),
		'footer-industries' => __( 'Footer — Industries', 'orcapulse' ),
		'footer-studio'   => __( 'Footer — Studio', 'orcapulse' ),
		'footer-legal'    => __( 'Footer — Legal', 'orcapulse' ),
	) );
}
add_action( 'after_setup_theme', 'orcapulse_setup' );

/**
 * Enqueue theme styles and scripts.
 */
function orcapulse_enqueue_assets() {
	// Google Fonts — Inter + Manrope.
	wp_enqueue_style(
		'orcapulse-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap',
		array(),
		null
	);
	// Full design system — loaded directly so it works even if style.css @import fails.
	wp_enqueue_style(
		'orcapulse-glass',
		get_template_directory_uri() . '/assets/glass.css',
		array( 'orcapulse-fonts' ),
		ORCAPULSE_VERSION
	);
	// Theme style.css (WordPress required — must be enqueued even if minimal).
	wp_enqueue_style(
		'orcapulse-style',
		get_stylesheet_uri(),
		array( 'orcapulse-glass' ),
		ORCAPULSE_VERSION
	);
	// Universal site script (sticky nav, mobile menu toggle, reveals).
	wp_enqueue_script(
		'orcapulse-main',
		get_template_directory_uri() . '/assets/main.js',
		array(),
		ORCAPULSE_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'orcapulse_enqueue_assets' );

/**
 * Also enqueue on Elementor preview iframe (so edits look right).
 */
add_action( 'elementor/preview/enqueue_styles', 'orcapulse_enqueue_assets' );
add_action( 'elementor/editor/after_enqueue_styles', 'orcapulse_enqueue_assets' );

/**
 * Elementor compatibility — declares support so Elementor stops warning
 * about unsupported themes and uses the body_class and content hooks.
 */
function orcapulse_elementor_support() {
	add_theme_support( 'elementor' );
	add_theme_support( 'elementor-pro' );
	add_post_type_support( 'page', 'elementor' );
	add_post_type_support( 'post', 'elementor' );
}
add_action( 'after_setup_theme', 'orcapulse_elementor_support' );

/**
 * Register Elementor locations (header, footer, single, archive).
 * Lets Elementor Pro Theme Builder own the layout for those regions.
 */
function orcapulse_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'orcapulse_register_elementor_locations' );

/**
 * Clean up the <head> — remove things we never use.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

/**
 * Add OpenGraph + Twitter card meta to every page.
 */
function orcapulse_social_meta() {
	if ( is_singular() ) {
		$title = wp_get_document_title();
		$desc  = get_the_excerpt() ?: get_bloginfo( 'description' );
		$url   = get_permalink();
		$image = get_the_post_thumbnail_url( null, 'full' );

		echo '<meta property="og:type" content="' . ( is_single() ? 'article' : 'website' ) . '" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";
		if ( $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n";
		}
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'orcapulse_social_meta', 5 );

/**
 * Register widget areas (footer).
 */
function orcapulse_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer Column 1', 'orcapulse' ),
		'id'            => 'footer-1',
		'description'   => __( 'Appears in the footer.', 'orcapulse' ),
		'before_widget' => '<div class="footer__col">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="footer__title">',
		'after_title'   => '</div>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer Column 2', 'orcapulse' ),
		'id'            => 'footer-2',
		'description'   => __( 'Appears in the footer.', 'orcapulse' ),
		'before_widget' => '<div class="footer__col">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="footer__title">',
		'after_title'   => '</div>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer Column 3', 'orcapulse' ),
		'id'            => 'footer-3',
		'description'   => __( 'Appears in the footer.', 'orcapulse' ),
		'before_widget' => '<div class="footer__col">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="footer__title">',
		'after_title'   => '</div>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer Column 4', 'orcapulse' ),
		'id'            => 'footer-4',
		'description'   => __( 'Appears in the footer.', 'orcapulse' ),
		'before_widget' => '<div class="footer__col">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="footer__title">',
		'after_title'   => '</div>',
	) );
}
add_action( 'widgets_init', 'orcapulse_widgets_init' );

/**
 * Helper: fallback nav for register_nav_menu that returns a plain <ul> of pages.
 */
function orcapulse_nav_fallback() {
	wp_list_pages( array(
		'title_li' => '',
		'depth'    => 1,
	) );
}
