<?php
/**
 * ACF field groups — registered in code so the theme is portable.
 *
 * Requires: Advanced Custom Fields Pro (for Repeater, Flexible Content, Options Page).
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'maison_register_options_page' );
function maison_register_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page( array(
		'page_title'  => 'Maison — Site Settings',
		'menu_title'  => 'Site Settings',
		'menu_slug'   => 'maison-settings',
		'capability'  => 'manage_options',
		'icon_url'    => 'dashicons-admin-generic',
		'position'    => 2,
		'redirect'    => false,
	) );
}

add_action( 'acf/init', 'maison_register_fields' );
function maison_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	/* ----------------------------------------------------------
	 *  SITE SETTINGS (Options Page)
	 * ---------------------------------------------------------- */
	acf_add_local_field_group( array(
		'key'      => 'group_site_settings',
		'title'    => 'Brand & Contact',
		'fields'   => array(
			array( 'key' => 'field_brand_name', 'label' => 'Brand name', 'name' => 'brand_name', 'type' => 'text', 'default_value' => 'Maison' ),
			array( 'key' => 'field_brand_tagline', 'label' => 'Short tagline', 'name' => 'brand_tagline', 'type' => 'text' ),
			array( 'key' => 'field_contact_email', 'label' => 'Contact email', 'name' => 'contact_email', 'type' => 'email' ),
			array( 'key' => 'field_contact_phone', 'label' => 'Contact phone', 'name' => 'contact_phone', 'type' => 'text' ),
			array( 'key' => 'field_studio_meta', 'label' => 'Studio line (e.g. "By appointment")', 'name' => 'studio_meta', 'type' => 'text' ),
			array( 'key' => 'field_org_schema_desc', 'label' => 'Organization description (for schema)', 'name' => 'org_schema_desc', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_org_logo', 'label' => 'Organization logo (schema)', 'name' => 'org_logo', 'type' => 'image', 'return_format' => 'array' ),
			array( 'key' => 'field_social_tab', 'label' => 'Social links', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_social_links', 'label' => 'Social URLs', 'name' => 'social_links', 'type' => 'repeater', 'layout' => 'table', 'sub_fields' => array(
				array( 'key' => 'field_social_label', 'label' => 'Platform', 'name' => 'label', 'type' => 'text' ),
				array( 'key' => 'field_social_url', 'label' => 'URL', 'name' => 'url', 'type' => 'url' ),
			) ),
		),
		'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'maison-settings' ) ) ),
	) );

	/* ----------------------------------------------------------
	 *  HOMEPAGE (front-page)
	 * ---------------------------------------------------------- */
	acf_add_local_field_group( array(
		'key'      => 'group_homepage',
		'title'    => 'Homepage',
		'fields'   => array(
			// Hero
			array( 'key' => 'field_hp_hero_tab', 'label' => 'Hero', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_hero_eyebrow', 'label' => 'Eyebrow', 'name' => 'hero_eyebrow', 'type' => 'text', 'default_value' => 'Est. 2026 · Partner, not vendor' ),
			array( 'key' => 'field_hp_hero_title', 'label' => 'Headline (wrap emphasis in <em>…</em>)', 'name' => 'hero_title', 'type' => 'textarea', 'rows' => 2, 'new_lines' => 'br' ),
			array( 'key' => 'field_hp_hero_sub', 'label' => 'Sub-headline', 'name' => 'hero_sub', 'type' => 'textarea', 'rows' => 3, 'new_lines' => 'br' ),
			array( 'key' => 'field_hp_hero_cta_primary', 'label' => 'Primary CTA label', 'name' => 'hero_cta_primary_label', 'type' => 'text', 'default_value' => 'Book a Strategy Call' ),
			array( 'key' => 'field_hp_hero_cta_primary_url', 'label' => 'Primary CTA URL', 'name' => 'hero_cta_primary_url', 'type' => 'url', 'default_value' => '#contact' ),
			array( 'key' => 'field_hp_hero_cta_secondary', 'label' => 'Secondary CTA label', 'name' => 'hero_cta_secondary_label', 'type' => 'text', 'default_value' => 'See Selected Work' ),
			array( 'key' => 'field_hp_hero_cta_secondary_url', 'label' => 'Secondary CTA URL', 'name' => 'hero_cta_secondary_url', 'type' => 'url', 'default_value' => '#work' ),
			array( 'key' => 'field_hp_hero_meta', 'label' => 'Hero metrics (4)', 'name' => 'hero_meta', 'type' => 'repeater', 'min' => 0, 'max' => 4, 'layout' => 'table', 'sub_fields' => array(
				array( 'key' => 'field_hp_hero_meta_value', 'label' => 'Value', 'name' => 'value', 'type' => 'text' ),
				array( 'key' => 'field_hp_hero_meta_label', 'label' => 'Label', 'name' => 'label', 'type' => 'text' ),
			) ),

			// Trust
			array( 'key' => 'field_hp_trust_tab', 'label' => 'Trust', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_trust_label', 'label' => 'Trust label', 'name' => 'trust_label', 'type' => 'text', 'default_value' => 'Trusted by businesses that compound' ),
			array( 'key' => 'field_hp_trust_logos', 'label' => 'Logos', 'name' => 'trust_logos', 'type' => 'repeater', 'layout' => 'block', 'sub_fields' => array(
				array( 'key' => 'field_hp_trust_logo_name', 'label' => 'Client name (text or alt)', 'name' => 'name', 'type' => 'text' ),
				array( 'key' => 'field_hp_trust_logo_image', 'label' => 'Logo image (optional)', 'name' => 'image', 'type' => 'image', 'return_format' => 'array' ),
			) ),

			// Approach
			array( 'key' => 'field_hp_approach_tab', 'label' => 'Approach', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_approach_label', 'label' => 'Section label', 'name' => 'approach_label', 'type' => 'text', 'default_value' => 'The Approach' ),
			array( 'key' => 'field_hp_approach_title', 'label' => 'Title', 'name' => 'approach_title', 'type' => 'textarea', 'rows' => 2, 'new_lines' => 'br' ),
			array( 'key' => 'field_hp_approach_intro', 'label' => 'Intro', 'name' => 'approach_intro', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_hp_approach_pillars', 'label' => 'Pillars (3)', 'name' => 'approach_pillars', 'type' => 'repeater', 'min' => 0, 'max' => 3, 'layout' => 'block', 'sub_fields' => array(
				array( 'key' => 'field_hp_pillar_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_hp_pillar_desc', 'label' => 'Description', 'name' => 'desc', 'type' => 'textarea', 'rows' => 3 ),
			) ),

			// Capabilities
			array( 'key' => 'field_hp_cap_tab', 'label' => 'Capabilities', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_cap_label', 'label' => 'Section label', 'name' => 'cap_label', 'type' => 'text', 'default_value' => 'Capabilities' ),
			array( 'key' => 'field_hp_cap_title', 'label' => 'Title', 'name' => 'cap_title', 'type' => 'text', 'default_value' => 'Full-stack growth capability, under one roof.' ),
			array( 'key' => 'field_hp_cap_intro', 'label' => 'Intro', 'name' => 'cap_intro', 'type' => 'textarea', 'rows' => 3 ),

			// Press
			array( 'key' => 'field_hp_press_tab', 'label' => 'Press', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_press_label', 'label' => 'Press label', 'name' => 'press_label', 'type' => 'text', 'default_value' => 'As featured in' ),
			array( 'key' => 'field_hp_press_outlets', 'label' => 'Outlets', 'name' => 'press_outlets', 'type' => 'repeater', 'layout' => 'table', 'sub_fields' => array(
				array( 'key' => 'field_hp_press_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text' ),
				array( 'key' => 'field_hp_press_url',  'label' => 'URL (optional)', 'name' => 'url', 'type' => 'url' ),
			) ),

			// By the numbers
			array( 'key' => 'field_hp_numbers_tab', 'label' => 'By the numbers', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_numbers_label', 'label' => 'Section label', 'name' => 'numbers_label', 'type' => 'text', 'default_value' => 'By the numbers' ),
			array( 'key' => 'field_hp_numbers_title', 'label' => 'Title (wrap emphasis in <em>…</em>)', 'name' => 'numbers_title', 'type' => 'textarea', 'rows' => 2, 'new_lines' => 'br' ),
			array( 'key' => 'field_hp_numbers_intro', 'label' => 'Intro', 'name' => 'numbers_intro', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_hp_numbers_stats', 'label' => 'Stats (4 recommended)', 'name' => 'numbers_stats', 'type' => 'repeater', 'min' => 0, 'max' => 4, 'layout' => 'block', 'sub_fields' => array(
				array( 'key' => 'field_hp_numbers_value',   'label' => 'Value', 'name' => 'value', 'type' => 'text' ),
				array( 'key' => 'field_hp_numbers_caption', 'label' => 'Caption', 'name' => 'caption', 'type' => 'textarea', 'rows' => 2 ),
			) ),

			// The Standard
			array( 'key' => 'field_hp_std_tab', 'label' => 'The Standard', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_std_label', 'label' => 'Section label', 'name' => 'standard_label', 'type' => 'text', 'default_value' => 'The Standard' ),
			array( 'key' => 'field_hp_std_title', 'label' => 'Title', 'name' => 'standard_title', 'type' => 'text', 'default_value' => 'Five commitments we never break.' ),
			array( 'key' => 'field_hp_std_intro', 'label' => 'Intro', 'name' => 'standard_intro', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_hp_std_items', 'label' => 'Commitments', 'name' => 'standard_items', 'type' => 'repeater', 'layout' => 'block', 'sub_fields' => array(
				array( 'key' => 'field_hp_std_item_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_hp_std_item_desc',  'label' => 'Description', 'name' => 'desc', 'type' => 'textarea', 'rows' => 3 ),
			) ),

			// Testimonial
			array( 'key' => 'field_hp_quote_tab', 'label' => 'Testimonial', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_quote_text', 'label' => 'Quote text (wrap emphasis in <em>…</em>)', 'name' => 'quote_text', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_hp_quote_name', 'label' => 'Name', 'name' => 'quote_name', 'type' => 'text' ),
			array( 'key' => 'field_hp_quote_role', 'label' => 'Role / Company', 'name' => 'quote_role', 'type' => 'text' ),

			// CTA
			array( 'key' => 'field_hp_cta_tab', 'label' => 'Final CTA', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hp_cta_title', 'label' => 'Title', 'name' => 'cta_title', 'type' => 'textarea', 'rows' => 2, 'new_lines' => 'br', 'default_value' => "If you're ready to grow, we're ready to build." ),
			array( 'key' => 'field_hp_cta_sub', 'label' => 'Sub', 'name' => 'cta_sub', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_hp_cta_button', 'label' => 'Button label', 'name' => 'cta_button_label', 'type' => 'text', 'default_value' => 'Book a Strategy Call' ),
			array( 'key' => 'field_hp_cta_url', 'label' => 'Button URL', 'name' => 'cta_button_url', 'type' => 'url' ),
		),
		'location' => array(
			array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ),
		),
	) );

	/* ----------------------------------------------------------
	 *  CASE STUDY
	 * ---------------------------------------------------------- */
	acf_add_local_field_group( array(
		'key'      => 'group_case_study',
		'title'    => 'Case Study Details',
		'fields'   => array(
			array( 'key' => 'field_cs_client', 'label' => 'Client name', 'name' => 'client', 'type' => 'text' ),
			array( 'key' => 'field_cs_subtitle', 'label' => 'Headline (outcome, not service)', 'name' => 'subtitle', 'type' => 'text' ),
			array( 'key' => 'field_cs_duration', 'label' => 'Engagement duration', 'name' => 'duration', 'type' => 'text' ),
			array( 'key' => 'field_cs_summary', 'label' => 'Summary (homepage/listing card)', 'name' => 'summary', 'type' => 'textarea', 'rows' => 4 ),
			array( 'key' => 'field_cs_kpis', 'label' => 'KPIs (up to 3)', 'name' => 'kpis', 'type' => 'repeater', 'min' => 0, 'max' => 3, 'layout' => 'table', 'sub_fields' => array(
				array( 'key' => 'field_cs_kpi_value', 'label' => 'Value', 'name' => 'value', 'type' => 'text' ),
				array( 'key' => 'field_cs_kpi_label', 'label' => 'Label', 'name' => 'label', 'type' => 'text' ),
			) ),
			array( 'key' => 'field_cs_body_tab', 'label' => 'Case body', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_cs_challenge', 'label' => 'Challenge', 'name' => 'challenge', 'type' => 'wysiwyg', 'media_upload' => 0 ),
			array( 'key' => 'field_cs_approach', 'label' => 'Approach', 'name' => 'approach', 'type' => 'wysiwyg', 'media_upload' => 0 ),
			array( 'key' => 'field_cs_outcome', 'label' => 'Outcome', 'name' => 'outcome', 'type' => 'wysiwyg', 'media_upload' => 0 ),
			array( 'key' => 'field_cs_featured_home', 'label' => 'Feature on homepage?', 'name' => 'featured_home', 'type' => 'true_false', 'ui' => 1 ),
			array( 'key' => 'field_cs_homepage_order', 'label' => 'Homepage order (lower = earlier)', 'name' => 'homepage_order', 'type' => 'number', 'default_value' => 10 ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'case_study' ) ) ),
	) );

	/* ----------------------------------------------------------
	 *  SERVICE
	 * ---------------------------------------------------------- */
	acf_add_local_field_group( array(
		'key'      => 'group_service',
		'title'    => 'Service Details',
		'fields'   => array(
			array( 'key' => 'field_sv_promise', 'label' => 'The promise (one sentence)', 'name' => 'promise', 'type' => 'text' ),
			array( 'key' => 'field_sv_summary', 'label' => 'Summary (for homepage capability card)', 'name' => 'summary', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_sv_capabilities', 'label' => 'What we deliver', 'name' => 'deliverables', 'type' => 'repeater', 'layout' => 'table', 'sub_fields' => array(
				array( 'key' => 'field_sv_deliverable', 'label' => 'Deliverable', 'name' => 'item', 'type' => 'text' ),
			) ),
			array( 'key' => 'field_sv_process_tab', 'label' => 'Process', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_sv_process', 'label' => 'Process steps', 'name' => 'process', 'type' => 'repeater', 'layout' => 'block', 'sub_fields' => array(
				array( 'key' => 'field_sv_step_title', 'label' => 'Step title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_sv_step_desc', 'label' => 'Step description', 'name' => 'desc', 'type' => 'textarea', 'rows' => 3 ),
			) ),
			array( 'key' => 'field_sv_faq_tab', 'label' => 'FAQ (AEO/GEO)', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_sv_faqs', 'label' => 'FAQs', 'name' => 'faqs', 'type' => 'repeater', 'layout' => 'block', 'instructions' => 'FAQs are serialized into FAQPage schema automatically — essential for Google AI Overviews, Perplexity, ChatGPT Search.', 'sub_fields' => array(
				array( 'key' => 'field_sv_q', 'label' => 'Question', 'name' => 'question', 'type' => 'text' ),
				array( 'key' => 'field_sv_a', 'label' => 'Answer', 'name' => 'answer', 'type' => 'textarea', 'rows' => 4 ),
			) ),
			array( 'key' => 'field_sv_order', 'label' => 'Homepage order (lower = earlier)', 'name' => 'order', 'type' => 'number', 'default_value' => 10 ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'service' ) ) ),
	) );

	/* ----------------------------------------------------------
	 *  TESTIMONIAL
	 * ---------------------------------------------------------- */
	acf_add_local_field_group( array(
		'key'      => 'group_testimonial',
		'title'    => 'Testimonial Details',
		'fields'   => array(
			array( 'key' => 'field_ts_quote', 'label' => 'Quote (wrap emphasis in <em>…</em>)', 'name' => 'quote', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_ts_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text' ),
			array( 'key' => 'field_ts_role', 'label' => 'Role / Company', 'name' => 'role', 'type' => 'text' ),
		),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'testimonial' ) ) ),
	) );

	/* ----------------------------------------------------------
	 *  ABOUT PAGE (template-about.php)
	 * ---------------------------------------------------------- */
	acf_add_local_field_group( array(
		'key'      => 'group_about',
		'title'    => 'About Page',
		'fields'   => array(
			array( 'key' => 'field_ab_eyebrow', 'label' => 'Eyebrow', 'name' => 'about_eyebrow', 'type' => 'text', 'default_value' => 'About' ),
			array( 'key' => 'field_ab_title', 'label' => 'Headline (wrap emphasis in <em>…</em>)', 'name' => 'about_title', 'type' => 'textarea', 'rows' => 2, 'new_lines' => 'br' ),
			array( 'key' => 'field_ab_sub', 'label' => 'Sub-headline', 'name' => 'about_sub', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_ab_story_tab', 'label' => 'Story', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_ab_story_title', 'label' => 'Story section title', 'name' => 'story_title', 'type' => 'text', 'default_value' => 'A different kind of agency.' ),
			array( 'key' => 'field_ab_story_body', 'label' => 'Story body', 'name' => 'story_body', 'type' => 'wysiwyg', 'media_upload' => 0 ),
			array( 'key' => 'field_ab_principles_tab', 'label' => 'Principles', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_ab_principles_title', 'label' => 'Principles section title', 'name' => 'principles_title', 'type' => 'text', 'default_value' => 'What we believe.' ),
			array( 'key' => 'field_ab_principles', 'label' => 'Principles', 'name' => 'principles', 'type' => 'repeater', 'layout' => 'block', 'sub_fields' => array(
				array( 'key' => 'field_ab_principle_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_ab_principle_desc', 'label' => 'Description', 'name' => 'desc', 'type' => 'textarea', 'rows' => 3 ),
			) ),
			array( 'key' => 'field_ab_team_tab', 'label' => 'Team', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_ab_team_title', 'label' => 'Team section title', 'name' => 'team_title', 'type' => 'text', 'default_value' => 'The people.' ),
			array( 'key' => 'field_ab_team', 'label' => 'Team members', 'name' => 'team', 'type' => 'repeater', 'layout' => 'block', 'sub_fields' => array(
				array( 'key' => 'field_ab_tm_name', 'label' => 'Name', 'name' => 'name', 'type' => 'text' ),
				array( 'key' => 'field_ab_tm_role', 'label' => 'Role', 'name' => 'role', 'type' => 'text' ),
				array( 'key' => 'field_ab_tm_bio', 'label' => 'Bio', 'name' => 'bio', 'type' => 'textarea', 'rows' => 3 ),
				array( 'key' => 'field_ab_tm_photo', 'label' => 'Photo', 'name' => 'photo', 'type' => 'image', 'return_format' => 'array' ),
			) ),
		),
		'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'templates/template-about.php' ) ) ),
	) );

	/* ----------------------------------------------------------
	 *  CONTACT PAGE (template-contact.php)
	 * ---------------------------------------------------------- */
	acf_add_local_field_group( array(
		'key'      => 'group_contact',
		'title'    => 'Contact Page',
		'fields'   => array(
			array( 'key' => 'field_ct_eyebrow', 'label' => 'Eyebrow', 'name' => 'contact_eyebrow', 'type' => 'text', 'default_value' => 'Begin' ),
			array( 'key' => 'field_ct_title', 'label' => 'Headline (wrap emphasis in <em>…</em>)', 'name' => 'contact_title', 'type' => 'textarea', 'rows' => 2, 'new_lines' => 'br' ),
			array( 'key' => 'field_ct_sub', 'label' => 'Sub-headline', 'name' => 'contact_sub', 'type' => 'textarea', 'rows' => 3 ),
			array( 'key' => 'field_ct_form_shortcode', 'label' => 'Form shortcode (Fluent Forms / Gravity / etc.)', 'name' => 'contact_form_shortcode', 'type' => 'text', 'instructions' => 'Paste a form shortcode here. If empty, a native HTML form is rendered.' ),
			array( 'key' => 'field_ct_steps_tab', 'label' => 'What happens next', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_ct_steps_title', 'label' => 'Section title', 'name' => 'steps_title', 'type' => 'text', 'default_value' => 'What happens next.' ),
			array( 'key' => 'field_ct_steps', 'label' => 'Steps', 'name' => 'steps', 'type' => 'repeater', 'layout' => 'block', 'sub_fields' => array(
				array( 'key' => 'field_ct_step_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text' ),
				array( 'key' => 'field_ct_step_desc', 'label' => 'Description', 'name' => 'desc', 'type' => 'textarea', 'rows' => 3 ),
			) ),
		),
		'location' => array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'templates/template-contact.php' ) ) ),
	) );

	/* ----------------------------------------------------------
	 *  BLOG PAGE (home.php target — WP posts page)
	 * ---------------------------------------------------------- */
	acf_add_local_field_group( array(
		'key'      => 'group_blog',
		'title'    => 'Blog / Insights Page',
		'fields'   => array(
			array( 'key' => 'field_bl_eyebrow', 'label' => 'Eyebrow', 'name' => 'blog_eyebrow', 'type' => 'text', 'default_value' => 'Insights' ),
			array( 'key' => 'field_bl_title', 'label' => 'Headline (wrap emphasis in <em>…</em>)', 'name' => 'blog_title', 'type' => 'textarea', 'rows' => 2, 'new_lines' => 'br' ),
			array( 'key' => 'field_bl_sub', 'label' => 'Sub-headline', 'name' => 'blog_sub', 'type' => 'textarea', 'rows' => 3 ),
		),
		'location' => array( array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'posts_page' ) ) ),
	) );
}
