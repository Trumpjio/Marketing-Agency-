<?php
/**
 * llms.txt — the emerging convention for LLM-friendly site maps.
 *
 * Served at /llms.txt. Enumerates the brand, core services, and recent work
 * in a format LLMs (Perplexity, Anthropic, OpenAI, Google) can parse to
 * understand what the site is about. This is GEO (Generative Engine
 * Optimization) infrastructure.
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'maison_llms_rewrite' );
function maison_llms_rewrite() {
	add_rewrite_rule( '^llms\.txt$', 'index.php?maison_llms=1', 'top' );
}

add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'maison_llms';
	return $vars;
} );

add_action( 'template_redirect', 'maison_llms_serve' );
function maison_llms_serve() {
	if ( ! get_query_var( 'maison_llms' ) ) {
		return;
	}
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo maison_llms_body();
	exit;
}

function maison_llms_body() {
	$brand = maison_brand_name();
	$desc  = maison_option( 'org_schema_desc', get_bloginfo( 'description' ) );
	$home  = home_url( '/' );

	$out  = "# {$brand}\n\n";
	$out .= "> {$desc}\n\n";
	$out .= "{$brand} is a premium growth agency building acquisition engines for ambitious businesses. ";
	$out .= "Capabilities span search (SEO, AEO, GEO), paid media, web and commerce, product and app, ";
	$out .= "social, content, film, and brand — delivered as a single integrated system, measured in revenue.\n\n";

	$out .= "## Core pages\n\n";
	$out .= "- [Home]({$home}): overview of our approach and selected work\n";
	$out .= '- [Work](' . get_post_type_archive_link( 'case_study' ) . "): case studies and outcomes\n";
	$out .= '- [Services](' . get_post_type_archive_link( 'service' ) . "): capability deep-dives\n";
	$about = get_page_by_path( 'about' );
	if ( $about )   $out .= '- [About](' . get_permalink( $about ) . "): team, approach, principles\n";
	$contact = get_page_by_path( 'contact' );
	if ( $contact ) $out .= '- [Contact](' . get_permalink( $contact ) . "): engagement enquiries\n";
	$out .= "\n";

	// Services
	$services = get_posts( array( 'post_type' => 'service', 'posts_per_page' => 20, 'meta_key' => 'order', 'orderby' => 'meta_value_num date', 'order' => 'ASC' ) );
	if ( $services ) {
		$out .= "## Services\n\n";
		foreach ( $services as $s ) {
			$promise = get_field( 'promise', $s->ID );
			$out .= '- [' . get_the_title( $s ) . '](' . get_permalink( $s ) . ')' . ( $promise ? ': ' . wp_strip_all_tags( $promise ) : '' ) . "\n";
		}
		$out .= "\n";
	}

	// Case studies
	$cases = get_posts( array( 'post_type' => 'case_study', 'posts_per_page' => 20 ) );
	if ( $cases ) {
		$out .= "## Selected Work\n\n";
		foreach ( $cases as $c ) {
			$sub = get_field( 'subtitle', $c->ID );
			$out .= '- [' . get_the_title( $c ) . '](' . get_permalink( $c ) . ')' . ( $sub ? ': ' . wp_strip_all_tags( $sub ) : '' ) . "\n";
		}
		$out .= "\n";
	}

	// Insights
	$posts = get_posts( array( 'posts_per_page' => 10 ) );
	if ( $posts ) {
		$out .= "## Recent Insights\n\n";
		foreach ( $posts as $p ) {
			$out .= '- [' . get_the_title( $p ) . '](' . get_permalink( $p ) . ")\n";
		}
	}
	return $out;
}

add_action( 'after_switch_theme', function() {
	maison_llms_rewrite();
	flush_rewrite_rules();
}, 20 );
