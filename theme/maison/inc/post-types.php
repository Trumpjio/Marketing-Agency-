<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'maison_register_post_types' );
function maison_register_post_types() {
	register_post_type( 'case_study', array(
		'labels' => array(
			'name'               => __( 'Case Studies', 'maison' ),
			'singular_name'      => __( 'Case Study', 'maison' ),
			'menu_name'          => __( 'Case Studies', 'maison' ),
			'add_new_item'       => __( 'Add New Case Study', 'maison' ),
			'edit_item'          => __( 'Edit Case Study', 'maison' ),
			'view_item'          => __( 'View Case Study', 'maison' ),
			'search_items'       => __( 'Search Case Studies', 'maison' ),
		),
		'public'       => true,
		'has_archive'  => 'work',
		'rewrite'      => array( 'slug' => 'work', 'with_front' => false ),
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position'=> 5,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ),
	) );

	register_post_type( 'service', array(
		'labels' => array(
			'name'               => __( 'Services', 'maison' ),
			'singular_name'      => __( 'Service', 'maison' ),
			'menu_name'          => __( 'Services', 'maison' ),
			'add_new_item'       => __( 'Add New Service', 'maison' ),
			'edit_item'          => __( 'Edit Service', 'maison' ),
			'view_item'          => __( 'View Service', 'maison' ),
			'search_items'       => __( 'Search Services', 'maison' ),
		),
		'public'       => true,
		'has_archive'  => 'services',
		'rewrite'      => array( 'slug' => 'services', 'with_front' => false ),
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-screenoptions',
		'menu_position'=> 6,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
	) );

	register_post_type( 'testimonial', array(
		'labels' => array(
			'name'          => __( 'Testimonials', 'maison' ),
			'singular_name' => __( 'Testimonial', 'maison' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-format-quote',
		'menu_position'=> 7,
		'supports'     => array( 'title', 'editor' ),
	) );

	register_taxonomy( 'industry', array( 'case_study' ), array(
		'labels' => array(
			'name'          => __( 'Industries', 'maison' ),
			'singular_name' => __( 'Industry', 'maison' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'industry' ),
	) );

	register_taxonomy( 'capability', array( 'case_study', 'service' ), array(
		'labels' => array(
			'name'          => __( 'Capabilities', 'maison' ),
			'singular_name' => __( 'Capability', 'maison' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'capability' ),
	) );
}

add_action( 'after_switch_theme', 'maison_flush_rewrites' );
function maison_flush_rewrites() {
	maison_register_post_types();
	flush_rewrite_rules();
}
