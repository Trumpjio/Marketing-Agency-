<?php
/**
 * Single Case Study.
 *
 * @package Maison
 */
get_header(); ?>

<?php while ( have_posts() ) : the_post();
	$client    = maison_field( 'client' );
	$sub       = maison_field( 'subtitle' );
	$duration  = maison_field( 'duration' );
	$summary   = maison_field( 'summary' );
	$challenge = maison_field( 'challenge' );
	$approach  = maison_field( 'approach' );
	$outcome   = maison_field( 'outcome' );
	$kpis      = maison_field( 'kpis', array() );
	$industries = get_the_terms( get_the_ID(), 'industry' );
	?>

	<section class="hero" style="padding-bottom:var(--space-6);">
		<div class="container">
			<div class="hero__eyebrow reveal">
				<?php
				$meta_bits = array();
				if ( $industries && ! is_wp_error( $industries ) ) $meta_bits[] = $industries[0]->name;
				if ( $duration ) $meta_bits[] = $duration;
				echo esc_html( implode( ' · ', $meta_bits ) );
				?>
			</div>
			<h1 class="hero__title serif reveal" style="font-size:clamp(2rem,6vw,4rem);"><?php echo esc_html( $sub ?: get_the_title() ); ?></h1>
			<?php if ( $client ) : ?>
				<p class="hero__sub reveal" style="font-family:var(--font-display);font-size:1.5rem;color:var(--color-accent);"><?php echo esc_html( $client ); ?></p>
			<?php endif; ?>
			<?php if ( $summary ) : ?>
				<p class="hero__sub reveal"><?php echo esc_html( $summary ); ?></p>
			<?php endif; ?>

			<?php if ( is_array( $kpis ) && ! empty( $kpis ) ) : ?>
				<div class="hero__meta reveal">
					<?php foreach ( $kpis as $kpi ) : ?>
						<div class="hero__meta-item">
							<div class="hero__meta-value serif" style="color:var(--color-accent);"><?php echo esc_html( $kpi['value'] ?? '' ); ?></div>
							<div class="hero__meta-label"><?php echo esc_html( $kpi['label'] ?? '' ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( has_post_thumbnail() ) : ?>
		<section style="padding-top:0;padding-bottom:var(--space-7);">
			<div class="container">
				<figure class="reveal">
					<?php the_post_thumbnail( 'maison-case-hero', array( 'style' => 'width:100%;height:auto;' ) ); ?>
				</figure>
			</div>
		</section>
	<?php endif; ?>

	<section>
		<div class="container" style="max-width:860px;">
			<?php if ( $challenge ) : ?>
				<div class="reveal" style="margin-bottom:var(--space-6);">
					<div class="section-label"><?php esc_html_e( 'Challenge', 'maison' ); ?></div>
					<div style="color:var(--color-ink-soft);font-size:1.125rem;line-height:1.8;"><?php echo wp_kses_post( $challenge ); ?></div>
				</div>
			<?php endif; ?>
			<?php if ( $approach ) : ?>
				<div class="reveal" style="margin-bottom:var(--space-6);">
					<div class="section-label"><?php esc_html_e( 'Approach', 'maison' ); ?></div>
					<div style="color:var(--color-ink-soft);font-size:1.125rem;line-height:1.8;"><?php echo wp_kses_post( $approach ); ?></div>
				</div>
			<?php endif; ?>
			<?php if ( $outcome ) : ?>
				<div class="reveal" style="margin-bottom:var(--space-6);">
					<div class="section-label"><?php esc_html_e( 'Outcome', 'maison' ); ?></div>
					<div style="color:var(--color-ink-soft);font-size:1.125rem;line-height:1.8;"><?php echo wp_kses_post( $outcome ); ?></div>
				</div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="reveal" style="color:var(--color-ink-soft);font-size:1.125rem;line-height:1.8;"><?php the_content(); ?></div>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/homepage/cta' ); ?>
<?php endwhile; ?>

<?php get_footer(); ?>
