<?php
/**
 * Homepage — Hero.
 *
 * @package Maison
 */
$eyebrow = maison_field( 'hero_eyebrow', 'Est. 2026 · Partner, not vendor' );
$title   = maison_field( 'hero_title', 'Growth, <em>engineered</em> for ambitious businesses.' );
$sub     = maison_field( 'hero_sub', "We build the demand engine — strategy, media, product, and brand — for companies that have outgrown generalist agencies. One house. One standard. Measured in revenue, not impressions." );
$cta_p_l = maison_field( 'hero_cta_primary_label', 'Book a Strategy Call' );
$cta_p_u = maison_field( 'hero_cta_primary_url', '#contact' );
$cta_s_l = maison_field( 'hero_cta_secondary_label', 'See Selected Work' );
$cta_s_u = maison_field( 'hero_cta_secondary_url', '#work' );
$meta    = maison_field( 'hero_meta', maison_default_hero_meta() );
?>
<section class="hero">
	<div class="hero__watermark" aria-hidden="true"><?php echo esc_html( maison_brand_name() ); ?></div>
	<div class="container">
		<div class="hero__eyebrow reveal"><?php echo esc_html( $eyebrow ); ?></div>
		<h1 class="hero__title serif reveal"><?php echo maison_safe_headline( $title ); ?></h1>
		<p class="hero__sub reveal"><?php echo maison_safe_headline( $sub ); ?></p>
		<div class="hero__cta reveal">
			<a href="<?php echo esc_url( $cta_p_u ); ?>" class="btn btn-primary"><?php echo esc_html( $cta_p_l ); ?><?php echo maison_arrow_svg(); ?></a>
			<a href="<?php echo esc_url( $cta_s_u ); ?>" class="btn btn-ghost"><?php echo esc_html( $cta_s_l ); ?></a>
		</div>

		<?php if ( is_array( $meta ) && ! empty( $meta ) ) : ?>
			<div class="hero__meta reveal">
				<?php foreach ( $meta as $m ) : ?>
					<div class="hero__meta-item">
						<div class="hero__meta-value serif"><?php echo esc_html( $m['value'] ?? '' ); ?></div>
						<div class="hero__meta-label"><?php echo esc_html( $m['label'] ?? '' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
