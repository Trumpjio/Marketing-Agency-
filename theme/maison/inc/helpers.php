<?php
/**
 * Template helpers.
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Safe ACF getter with fallback. */
function maison_field( $key, $default = '', $post_id = false ) {
	if ( function_exists( 'get_field' ) ) {
		$val = get_field( $key, $post_id );
		if ( ! empty( $val ) ) {
			return $val;
		}
	}
	return $default;
}

/** Options-page ACF getter. */
function maison_option( $key, $default = '' ) {
	return maison_field( $key, $default, 'option' );
}

/** Brand name used in header/footer/schema. */
function maison_brand_name() {
	$name = maison_option( 'brand_name', get_bloginfo( 'name' ) );
	return $name ? $name : 'Maison';
}

/** Allow <em> and <br> in headlines, nothing else. */
function maison_safe_headline( $text ) {
	return wp_kses( $text, array( 'em' => array(), 'br' => array(), 'span' => array( 'class' => true ) ) );
}

/** Render an SVG arrow icon used by buttons. */
function maison_arrow_svg() {
	return '<svg class="arrow" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9 1l4 4-4 4M13 5H1" stroke="currentColor" stroke-width="1.3" stroke-linecap="square"/></svg>';
}

/** Primary CTA target URL (uses homepage CTA URL, falls back to contact page or #contact). */
function maison_primary_cta_url() {
	$url = maison_field( 'hero_cta_primary_url', '', get_option( 'page_on_front' ) );
	if ( $url ) {
		return $url;
	}
	$contact = get_page_by_path( 'contact' );
	if ( $contact ) {
		return get_permalink( $contact );
	}
	return home_url( '/#contact' );
}

/** Pull the N featured case studies for homepage. */
function maison_homepage_case_studies( $count = 3 ) {
	$args = array(
		'post_type'      => 'case_study',
		'posts_per_page' => $count,
		'meta_key'       => 'homepage_order',
		'orderby'        => 'meta_value_num date',
		'order'          => 'ASC',
		'meta_query'     => array(
			'relation' => 'OR',
			array( 'key' => 'featured_home', 'value' => '1', 'compare' => '=' ),
			array( 'key' => 'featured_home', 'compare' => 'NOT EXISTS' ),
		),
	);
	return new WP_Query( $args );
}

/** Pull services, ordered. */
function maison_services( $count = 8 ) {
	return new WP_Query( array(
		'post_type'      => 'service',
		'posts_per_page' => $count,
		'meta_key'       => 'order',
		'orderby'        => 'meta_value_num date',
		'order'          => 'ASC',
	) );
}

/** Fallback hero metrics shown when none are set in ACF. */
function maison_default_hero_meta() {
	return array(
		array( 'value' => '+312%',  'label' => 'Avg. pipeline growth' ),
		array( 'value' => '47',     'label' => 'Ambitious partners' ),
		array( 'value' => '12 yrs', 'label' => 'Compounded craft' ),
		array( 'value' => '1',      'label' => 'Team, end to end' ),
	);
}

/** Fallback approach pillars. */
function maison_default_pillars() {
	return array(
		array( 'title' => 'Positioning before promotion.',       'desc' => "We don't amplify noise. Every engagement begins by sharpening the thing worth saying — so every dollar after travels further." ),
		array( 'title' => 'A system, not a stack of tactics.',   'desc' => 'Acquisition, conversion, retention and brand built as one integrated engine — because disconnected tactics are how budgets quietly leak.' ),
		array( 'title' => 'Measured in revenue.',                'desc' => 'Impressions are vanity. Pipeline, LTV and gross margin are the only scoreboard that matters — and the one we report on, every month.' ),
	);
}

/** Fallback default capabilities when CMS is empty. */
function maison_default_capabilities() {
	return array(
		array( 'title' => 'Search & Discovery',    'summary' => 'SEO, AEO and GEO — ranking in search engines, answer engines and AI-generated results.' ),
		array( 'title' => 'Paid Media',            'summary' => 'Performance across Google, Meta, LinkedIn, TikTok, programmatic and emerging platforms — managed as one portfolio.' ),
		array( 'title' => 'Web & Commerce',        'summary' => 'Marketing sites, SaaS platforms, e-commerce, membership and booking systems — built to convert, built to last.' ),
		array( 'title' => 'Product & App',         'summary' => 'iOS, Android and cross-platform applications — from scoping to shipped product, with the growth loop baked in.' ),
		array( 'title' => 'Social Presence',       'summary' => 'Strategy, calendar and operations across every platform the audience actually lives on — anchored to narrative.' ),
		array( 'title' => 'Content & Editorial',   'summary' => 'The long-form, the briefings, the owned media — the compounding asset that pays off for years, not days.' ),
		array( 'title' => 'Film & Motion',         'summary' => 'Production, editing and motion design that reads premium at thumb-scroll speed and billboard scale alike.' ),
		array( 'title' => 'Brand & Design',        'summary' => 'Identity, art direction and the system that keeps every surface feeling made by the same hand.' ),
	);
}
