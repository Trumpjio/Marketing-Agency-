<?php
/**
 * Default page template.
 *
 * @package Maison
 */
get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
	<section>
		<div class="container">
			<header class="reveal" style="margin-bottom: var(--space-5)">
				<h1 class="section-title serif"><?php the_title(); ?></h1>
			</header>
			<div class="reveal" style="max-width:66ch;color:var(--color-ink-soft);line-height:1.7;">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
<?php endwhile; ?>

<?php get_footer(); ?>
