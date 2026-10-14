<?php
/**
 * Fallback template.
 *
 * @package Maison
 */
get_header(); ?>

<section>
	<div class="container">
		<header class="reveal" style="margin-bottom: var(--space-6)">
			<div class="section-label"><?php single_post_title(); ?></div>
			<h1 class="section-title serif"><?php esc_html_e( 'Insights', 'maison' ); ?></h1>
		</header>

		<?php if ( have_posts() ) : ?>
			<div style="display:grid;gap:var(--space-5);">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="reveal" style="padding-top:var(--space-3);border-top:1px solid var(--color-divider);">
						<div class="case__meta"><span><?php echo esc_html( get_the_date() ); ?></span><span><?php echo esc_html( get_the_author() ); ?></span></div>
						<h2 class="case__title serif"><a href="<?php the_permalink(); ?>" style="color:inherit"><?php the_title(); ?></a></h2>
						<p class="case__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<a class="btn-text" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read', 'maison' ); ?><?php echo maison_arrow_svg(); ?></a>
					</article>
				<?php endwhile; ?>
			</div>
			<nav style="margin-top:var(--space-6)"><?php the_posts_pagination(); ?></nav>
		<?php else : ?>
			<p class="section-intro"><?php esc_html_e( 'Nothing published yet.', 'maison' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
