<?php
/**
 * The default page template. Elementor overrides this when you build with
 * Elementor Canvas / Elementor Full-Width template options.
 *
 * @package OrcaPulse
 */

get_header();
?>

<main id="main" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="page-<?php the_ID(); ?>" <?php post_class( 'orcapulse-page' ); ?>>
			<?php the_content(); ?>
		</article>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
