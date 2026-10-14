<?php
/**
 * Case Study archive.
 *
 * @package Maison
 */
get_header(); ?>

<section class="hero" style="padding-bottom:var(--space-6);">
	<div class="container">
		<div class="hero__eyebrow reveal"><?php esc_html_e( 'Selected Work', 'maison' ); ?></div>
		<h1 class="hero__title serif reveal" style="font-size:clamp(2.5rem,7vw,5rem);"><?php esc_html_e( 'Work worth remembering.', 'maison' ); ?></h1>
	</div>
</section>

<section style="padding-top:0;">
	<div class="container">
		<?php
		if ( have_posts() ) :
			$index = 0;
			while ( have_posts() ) : the_post(); $index++;
				$reverse = ( $index % 2 === 0 ) ? ' case--reverse' : '';
				$client  = maison_field( 'client', get_the_title() );
				$sub     = maison_field( 'subtitle', get_the_title() );
				$summary = maison_field( 'summary', get_the_excerpt() );
				$duration = maison_field( 'duration' );
				$kpis    = maison_field( 'kpis', array() );
				$thumb   = get_the_post_thumbnail_url( null, 'maison-card' );
				$industries = get_the_terms( get_the_ID(), 'industry' );
				?>
				<article class="case<?php echo esc_attr( $reverse ); ?> reveal">
					<a href="<?php the_permalink(); ?>" class="case__visual" style="text-decoration:none;color:inherit;<?php if ( $thumb ) echo 'background-image:url(' . esc_url( $thumb ) . ');background-size:cover;background-position:center;'; ?>">
						<div class="case__visual-number"><?php echo esc_html( sprintf( '%02d', $index ) ); ?></div>
						<div class="case__visual-mark"><?php echo esc_html( $client ); ?></div>
					</a>
					<div>
						<div class="case__meta">
							<?php
							if ( $industries && ! is_wp_error( $industries ) ) echo '<span>' . esc_html( $industries[0]->name ) . '</span>';
							if ( $duration ) echo '<span>' . esc_html( $duration ) . '</span>';
							?>
						</div>
						<h2 class="case__title serif"><a href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none;"><?php echo esc_html( $sub ); ?></a></h2>
						<p class="case__desc"><?php echo esc_html( $summary ); ?></p>
						<?php if ( is_array( $kpis ) && ! empty( $kpis ) ) : ?>
							<div class="case__kpis">
								<?php foreach ( $kpis as $kpi ) : ?>
									<div><div class="case__kpi-value serif"><?php echo esc_html( $kpi['value'] ?? '' ); ?></div><div class="case__kpi-label"><?php echo esc_html( $kpi['label'] ?? '' ); ?></div></div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<a href="<?php the_permalink(); ?>" class="btn-text"><?php esc_html_e( 'View case study', 'maison' ); ?><?php echo maison_arrow_svg(); ?></a>
					</div>
				</article>
			<?php endwhile;

			the_posts_pagination( array( 'prev_text' => __( '← Prev', 'maison' ), 'next_text' => __( 'Next →', 'maison' ) ) );
		else : ?>
			<p class="section-intro"><?php esc_html_e( 'Case studies coming soon.', 'maison' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/homepage/cta' ); ?>

<?php get_footer(); ?>
