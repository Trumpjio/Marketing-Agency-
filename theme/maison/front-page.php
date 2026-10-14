<?php
/**
 * Front page template.
 *
 * Section order chosen for a conversion funnel:
 *   Hero → Trust marquee → Press → Work → Numbers → Approach →
 *   Standard → Capabilities → Quote → CTA
 *
 * Ornaments divide chapters without screaming for attention.
 *
 * @package Maison
 */

get_header(); ?>

<?php get_template_part( 'template-parts/homepage/hero' ); ?>
<?php get_template_part( 'template-parts/homepage/trust' ); ?>
<?php get_template_part( 'template-parts/homepage/press' ); ?>

<?php maison_ornament(); ?>
<?php get_template_part( 'template-parts/homepage/work' ); ?>

<?php get_template_part( 'template-parts/homepage/numbers' ); ?>

<?php maison_ornament(); ?>
<?php get_template_part( 'template-parts/homepage/approach' ); ?>

<?php get_template_part( 'template-parts/homepage/standard' ); ?>

<?php maison_ornament(); ?>
<?php get_template_part( 'template-parts/homepage/capabilities' ); ?>

<?php get_template_part( 'template-parts/homepage/quote' ); ?>

<?php maison_ornament(); ?>
<?php get_template_part( 'template-parts/homepage/cta' ); ?>

<?php get_footer(); ?>
