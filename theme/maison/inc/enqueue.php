<?php
/**
 * Enqueue scripts and styles.
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'maison_enqueue_assets' );
function maison_enqueue_assets() {
	// Fonts — Fraunces (display serif) + Inter (body).
	wp_enqueue_style(
		'maison-google-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;1,9..144,300;1,9..144,400&family=Inter:wght@300;400;500;600&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'maison-main',
		MAISON_URI . '/style.css',
		array( 'maison-google-fonts' ),
		MAISON_VERSION
	);

	wp_enqueue_script(
		'maison-main',
		MAISON_URI . '/assets/js/main.js',
		array(),
		MAISON_VERSION,
		true
	);
}

add_filter( 'style_loader_tag', 'maison_preconnect_fonts', 10, 2 );
function maison_preconnect_fonts( $html, $handle ) {
	if ( 'maison-google-fonts' === $handle ) {
		$preconnect  = '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		$preconnect .= '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
		return $preconnect . $html;
	}
	return $html;
}

add_filter( 'script_loader_tag', 'maison_defer_scripts', 10, 2 );
function maison_defer_scripts( $tag, $handle ) {
	$defer = array( 'maison-main' );
	if ( in_array( $handle, $defer, true ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}
