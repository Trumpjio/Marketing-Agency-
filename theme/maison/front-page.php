<?php
/**
 * Front page template.
 *
 * @package Maison
 */

get_header(); ?>

<?php get_template_part( 'template-parts/homepage/hero' ); ?>
<?php get_template_part( 'template-parts/homepage/trust' ); ?>
<?php get_template_part( 'template-parts/homepage/work' ); ?>
<?php get_template_part( 'template-parts/homepage/approach' ); ?>
<?php get_template_part( 'template-parts/homepage/capabilities' ); ?>
<?php get_template_part( 'template-parts/homepage/quote' ); ?>
<?php get_template_part( 'template-parts/homepage/cta' ); ?>

<?php get_footer(); ?>
