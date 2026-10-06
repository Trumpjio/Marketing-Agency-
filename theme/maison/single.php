<?php
/**
 * Single post (insight).
 *
 * @package Maison
 */
get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
	<section>
		<div class="container" style="max-width:800px;">
			<header class="reveal" style="margin-bottom: var(--space-5)">
				<div class="case__meta"><span><?php echo esc_html( get_the_date() ); ?></span><span><?php echo esc_html( get_the_author() ); ?></span></div>
				<h1 class="section-title serif" style="max-width:28ch;"><?php the_title(); ?></h1>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="reveal" style="margin-bottom:var(--space-5);">
					<?php the_post_thumbnail( 'full', array( 'style' => 'width:100%;height:auto;' ) ); ?>
				</figure>
			<?php endif; ?>
			<div class="reveal" style="color:var(--color-ink-soft);line-height:1.8;font-size:1.125rem;">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
<?php endwhile; ?>

<?php get_footer(); ?>
