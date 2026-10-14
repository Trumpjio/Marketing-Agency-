<?php
/**
 * Homepage template.
 *
 * If the user builds the homepage with Elementor Pro Theme Builder, this is
 * overridden. Otherwise, if a static front page is set, it renders that page's
 * content through page.php. If not, we fall through to index.php.
 *
 * @package OrcaPulse
 */

if ( 'page' === get_option( 'show_on_front' ) ) {
	// Static front page assigned — render through page.php.
	include get_template_directory() . '/page.php';
	return;
}

// Else fall through to the posts listing.
include get_template_directory() . '/index.php';
