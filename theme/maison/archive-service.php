<?php
/**
 * Service archive.
 *
 * @package Maison
 */
get_header(); ?>

<section class="hero" style="padding-bottom:var(--space-6);">
	<div class="container">
		<div class="hero__eyebrow reveal"><?php esc_html_e( 'Capabilities', 'maison' ); ?></div>
		<h1 class="hero__title serif reveal" style="font-size:clamp(2.5rem,7vw,5rem);"><?php esc_html_e( 'One house. Every lever.', 'maison' ); ?></h1>
		<p class="hero__sub reveal"><?php esc_html_e( 'A single team that moves across every surface your growth needs — not a menu of services.', 'maison' ); ?></p>
	</div>
</section>

<section style="padding-top:0;">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="capabilities__grid">
				<?php $i = 0; while ( have_posts() ) : the_post(); $i++; ?>
					<a class="capability reveal" href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none;">
						<div class="capability__num"><?php echo esc_html( sprintf( '%02d —', $i ) ); ?></div>
						<h2 class="capability__title serif"><?php the_title(); ?></h2>
						<p class="capability__desc"><?php echo esc_html( maison_field( 'summary', get_the_excerpt() ) ); ?></p>
					</a>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<p class="section-intro"><?php esc_html_e( 'Services coming soon.', 'maison' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/homepage/cta' ); ?>

<?php get_footer(); ?>
