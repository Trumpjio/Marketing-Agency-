<?php
/**
 * Light SEO defaults.
 *
 * NOTE: Install Rank Math (or Yoast) for full control. This file provides
 * sensible fallbacks for OpenGraph, Twitter cards, and robots meta if no SEO
 * plugin is active. If Rank Math/Yoast is detected, we step out of the way.
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function maison_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

add_action( 'wp_head', 'maison_meta_tags', 1 );
function maison_meta_tags() {
	if ( maison_seo_plugin_active() ) {
		return;
	}

	$title = wp_get_document_title();
	$desc  = maison_meta_description();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
	$image = maison_meta_image();

	echo "\n";
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";

	// OpenGraph
	echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( maison_brand_name() ) . '">' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	// Twitter
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	}
}

function maison_meta_description() {
	if ( is_singular() ) {
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_strip_all_tags( $excerpt );
		}
	}
	return get_bloginfo( 'description' );
}

function maison_meta_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		return get_the_post_thumbnail_url( null, 'full' );
	}
	$logo = maison_option( 'org_logo' );
	if ( ! empty( $logo['url'] ) ) {
		return $logo['url'];
	}
	return '';
}
