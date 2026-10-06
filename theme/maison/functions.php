<?php
/**
 * Maison theme entry point.
 *
 * Loads modular inc/ files. Keep this file thin — logic lives in inc/.
 *
 * @package Maison
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAISON_VERSION', '1.0.0' );
define( 'MAISON_DIR', get_template_directory() );
define( 'MAISON_URI', get_template_directory_uri() );

require_once MAISON_DIR . '/inc/setup.php';
require_once MAISON_DIR . '/inc/enqueue.php';
require_once MAISON_DIR . '/inc/post-types.php';
require_once MAISON_DIR . '/inc/acf-fields.php';
require_once MAISON_DIR . '/inc/schema.php';
require_once MAISON_DIR . '/inc/seo.php';
require_once MAISON_DIR . '/inc/llms.php';
require_once MAISON_DIR . '/inc/helpers.php';
require_once MAISON_DIR . '/inc/seeder.php';
