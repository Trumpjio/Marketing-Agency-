<?php
/**
 * JSON-LD structured data for SEO / AEO / GEO.
 *
 * Emits Organization + WebSite on every page; adds page-type-specific
 * schema (Article, Service, FAQPage, BreadcrumbList, CollectionPage).
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'maison_schema', 20 );
function maison_schema() {
	$graph = array();

	$graph[] = maison_schema_organization();
	$graph[] = maison_schema_website();
	$graph[] = maison_schema_breadcrumbs();

	if ( is_singular( 'case_study' ) ) {
		$graph[] = maison_schema_case_study();
	} elseif ( is_singular( 'service' ) ) {
		$graph[] = maison_schema_service();
		$faq = maison_schema_faq();
		if ( $faq ) {
			$graph[] = $faq;
		}
	} elseif ( is_singular( 'post' ) ) {
		$graph[] = maison_schema_article();
	} elseif ( is_post_type_archive( 'case_study' ) || is_post_type_archive( 'service' ) ) {
		$graph[] = maison_schema_collection();
	}

	$graph = array_filter( $graph );
	if ( empty( $graph ) ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $graph ),
	);

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

function maison_schema_organization() {
	$logo = maison_option( 'org_logo' );
	$org  = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => maison_brand_name(),
		'url'   => home_url( '/' ),
	);
	if ( ! empty( $logo['url'] ) ) {
		$org['logo'] = array(
			'@type'  => 'ImageObject',
			'url'    => $logo['url'],
			'width'  => isset( $logo['width'] ) ? (int) $logo['width'] : 400,
			'height' => isset( $logo['height'] ) ? (int) $logo['height'] : 400,
		);
	}
	$desc = maison_option( 'org_schema_desc' );
	if ( $desc ) {
		$org['description'] = $desc;
	}
	$email = maison_option( 'contact_email' );
	$phone = maison_option( 'contact_phone' );
	if ( $email || $phone ) {
		$cp = array( '@type' => 'ContactPoint', 'contactType' => 'customer service' );
		if ( $email ) $cp['email'] = $email;
		if ( $phone ) $cp['telephone'] = $phone;
		$org['contactPoint'] = $cp;
	}
	$social = maison_option( 'social_links', array() );
	if ( is_array( $social ) && ! empty( $social ) ) {
		$org['sameAs'] = array_values( array_filter( array_map( function( $s ) { return $s['url'] ?? ''; }, $social ) ) );
	}
	return $org;
}

function maison_schema_website() {
	return array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => maison_brand_name(),
		'description'     => get_bloginfo( 'description' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'inLanguage'      => get_bloginfo( 'language' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

function maison_schema_breadcrumbs() {
	$items = array();
	$items[] = array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) );
	if ( is_singular( 'case_study' ) ) {
		$items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Work', 'item' => get_post_type_archive_link( 'case_study' ) );
		$items[] = array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title(), 'item' => get_permalink() );
	} elseif ( is_singular( 'service' ) ) {
		$items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => get_post_type_archive_link( 'service' ) );
		$items[] = array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title(), 'item' => get_permalink() );
	} elseif ( is_singular() && ! is_front_page() ) {
		$items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink() );
	}
	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

function maison_schema_case_study() {
	$client = maison_field( 'client' );
	return array(
		'@type'           => 'CreativeWork',
		'@id'             => get_permalink() . '#case',
		'name'            => get_the_title(),
		'headline'        => maison_field( 'subtitle', get_the_title() ),
		'description'     => maison_field( 'summary', get_the_excerpt() ),
		'url'             => get_permalink(),
		'datePublished'   => get_the_date( 'c' ),
		'dateModified'    => get_the_modified_date( 'c' ),
		'author'          => array( '@id' => home_url( '/#organization' ) ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'image'           => get_the_post_thumbnail_url( null, 'maison-case-hero' ),
		'about'           => $client ?: null,
		'inLanguage'      => get_bloginfo( 'language' ),
	);
}

function maison_schema_service() {
	return array(
		'@type'       => 'Service',
		'@id'         => get_permalink() . '#service',
		'name'        => get_the_title(),
		'description' => maison_field( 'promise', get_the_excerpt() ),
		'provider'    => array( '@id' => home_url( '/#organization' ) ),
		'areaServed'  => 'Worldwide',
		'url'         => get_permalink(),
	);
}

function maison_schema_faq() {
	$faqs = maison_field( 'faqs', array() );
	if ( ! is_array( $faqs ) || empty( $faqs ) ) {
		return null;
	}
	$entities = array();
	foreach ( $faqs as $faq ) {
		if ( empty( $faq['question'] ) || empty( $faq['answer'] ) ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $faq['question'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $faq['answer'] ),
			),
		);
	}
	if ( empty( $entities ) ) {
		return null;
	}
	return array(
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
}

function maison_schema_article() {
	return array(
		'@type'           => 'Article',
		'@id'             => get_permalink() . '#article',
		'headline'        => get_the_title(),
		'description'     => get_the_excerpt(),
		'datePublished'   => get_the_date( 'c' ),
		'dateModified'    => get_the_modified_date( 'c' ),
		'author'          => array(
			'@type' => 'Person',
			'name'  => get_the_author(),
			'url'   => get_author_posts_url( get_the_author_meta( 'ID' ) ),
		),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'image'           => get_the_post_thumbnail_url( null, 'full' ),
		'mainEntityOfPage' => get_permalink(),
	);
}

function maison_schema_collection() {
	return array(
		'@type'       => 'CollectionPage',
		'name'        => post_type_archive_title( '', false ),
		'url'         => get_post_type_archive_link( get_query_var( 'post_type' ) ?: 'post' ),
		'isPartOf'    => array( '@id' => home_url( '/#website' ) ),
		'inLanguage'  => get_bloginfo( 'language' ),
	);
}
