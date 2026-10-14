<?php
/**
 * Blog / Insights index — used when a static front page is set
 * and a page is assigned as the "Posts page" (Settings → Reading).
 *
 * @package Maison
 */

get_header(); ?>

<?php
$blog_page_id = (int) get_option( 'page_for_posts' );
$eyebrow = $blog_page_id ? maison_field( 'blog_eyebrow', 'Insights', $blog_page_id ) : 'Insights';
$title   = $blog_page_id ? maison_field( 'blog_title', 'Writing, worth your time.', $blog_page_id ) : 'Writing, worth your time.';
$sub     = $blog_page_id ? maison_field( 'blog_sub', 'Field notes on positioning, acquisition, and the craft of growth — written when we have something new to say, not on a content calendar.', $blog_page_id ) : 'Field notes on positioning, acquisition, and the craft of growth — written when we have something new to say, not on a content calendar.';
?>

<section class="hero" style="padding-bottom:var(--space-6);">
	<div class="container">
		<div class="hero__eyebrow reveal"><?php echo esc_html( $eyebrow ); ?></div>
		<h1 class="hero__title serif reveal" style="font-size:clamp(2.5rem,7vw,5rem);"><?php echo maison_safe_headline( $title ); ?></h1>
		<p class="hero__sub reveal"><?php echo esc_html( $sub ); ?></p>
	</div>
</section>

<section style="padding-top:0;">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:var(--space-5);border-top:1px solid var(--color-divider);padding-top:var(--space-5);">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="reveal">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" style="display:block;aspect-ratio:4/3;overflow:hidden;background:var(--color-surface);border:1px solid var(--color-divider);margin-bottom:var(--space-3);">
								<?php the_post_thumbnail( 'maison-card', array( 'style' => 'width:100%;height:100%;object-fit:cover;', 'loading' => 'lazy' ) ); ?>
							</a>
						<?php endif; ?>
						<div class="case__meta"><span><?php echo esc_html( get_the_date() ); ?></span><span><?php echo esc_html( get_the_author() ); ?></span></div>
						<h2 class="serif" style="font-size:1.5rem;margin-bottom:var(--space-2);line-height:1.2;letter-spacing:-0.01em;"><a href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none;"><?php the_title(); ?></a></h2>
						<p style="color:var(--color-ink-soft);line-height:1.6;margin-bottom:var(--space-2);"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<a class="btn-text" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read', 'maison' ); ?><?php echo maison_arrow_svg(); ?></a>
					</article>
				<?php endwhile; ?>
			</div>

			<nav style="margin-top:var(--space-6);text-align:center;">
				<?php the_posts_pagination( array( 'prev_text' => __( '← Previous', 'maison' ), 'next_text' => __( 'Next →', 'maison' ) ) ); ?>
			</nav>
		<?php else : ?>
			<p class="section-intro reveal"><?php esc_html_e( 'Writing coming soon.', 'maison' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/homepage/cta' ); ?>

<?php get_footer(); ?>
