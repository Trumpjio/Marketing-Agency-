<?php
/**
 * Fallback template. Lists the latest posts.
 *
 * @package OrcaPulse
 */

get_header();
?>

<main id="main" class="site-main">
	<section class="section hero">
		<div class="container">
			<h1 class="reveal"><?php single_post_title(); ?></h1>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<?php if ( have_posts() ) : ?>
				<div class="grid grid--3">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<a class="glass reveal" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="article-visual" style="margin-bottom:var(--s-3);aspect-ratio:4/3;overflow:hidden;border-radius:var(--r-md);">
									<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?>
								</div>
							<?php endif; ?>
							<h3 class="case__title" style="font-size:1.125rem;margin-bottom:var(--s-1);"><?php the_title(); ?></h3>
							<p class="cap__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
						</a>
						<?php
					endwhile;
					?>
				</div>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No posts yet.', 'orcapulse' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php get_footer(); ?>
