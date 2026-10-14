<?php
/**
 * Homepage — Selected Work (case studies).
 *
 * @package Maison
 */

$cases = maison_homepage_case_studies( 3 );
?>
<section id="work" style="position:relative;overflow:hidden;">
	<div class="bg-numeral" style="left:-3vw;top:4vw;">01</div>
	<div class="container" style="position:relative;z-index:1;">
		<div class="reveal">
			<div class="section-label"><?php esc_html_e( 'Selected Work', 'maison' ); ?></div>
			<h2 class="section-title serif"><?php esc_html_e( 'Businesses that chose depth over noise.', 'maison' ); ?></h2>
		</div>

		<div style="margin-top: var(--space-7)">
			<?php
			$index = 0;
			if ( $cases->have_posts() ) :
				while ( $cases->have_posts() ) : $cases->the_post(); $index++;
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
						<div class="case__visual" <?php if ( $thumb ) echo 'style="background-image:url(' . esc_url( $thumb ) . ');background-size:cover;background-position:center;"'; ?>>
							<div class="case__visual-number"><?php echo esc_html( sprintf( '%02d', $index ) ); ?></div>
							<div class="case__visual-mark"><?php echo esc_html( $client ); ?></div>
						</div>
						<div>
							<div class="case__meta">
								<?php
								if ( $industries && ! is_wp_error( $industries ) ) {
									echo '<span>' . esc_html( $industries[0]->name ) . '</span>';
								}
								if ( $duration ) {
									echo '<span>' . esc_html( $duration ) . '</span>';
								}
								?>
							</div>
							<h3 class="case__title serif"><?php echo esc_html( $sub ); ?></h3>
							<p class="case__desc"><?php echo esc_html( $summary ); ?></p>

							<?php if ( is_array( $kpis ) && ! empty( $kpis ) ) : ?>
								<div class="case__kpis">
									<?php foreach ( $kpis as $kpi ) : ?>
										<div>
											<div class="case__kpi-value serif"><?php echo esc_html( $kpi['value'] ?? '' ); ?></div>
											<div class="case__kpi-label"><?php echo esc_html( $kpi['label'] ?? '' ); ?></div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<a href="<?php the_permalink(); ?>" class="btn-text"><?php esc_html_e( 'View case study', 'maison' ); ?><?php echo maison_arrow_svg(); ?></a>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata();
			else :
				// Fallback demo content when no case studies have been added yet.
				$demo = array(
					array( 'client' => 'Lumière & Co', 'industry' => 'Premium E-Commerce', 'duration' => '18-Month Engagement', 'title' => 'From quiet brand to category authority.', 'desc' => 'A heritage leather maison with exceptional product and invisible demand. We rebuilt the brand story, re-architected acquisition, and ran a disciplined content and SEO program.', 'kpis' => array( array( 'value' => '4.2×', 'label' => 'Revenue, YoY' ), array( 'value' => '63%', 'label' => 'Lower CAC' ), array( 'value' => '#1', 'label' => 'Category SOV' ) ) ),
					array( 'client' => 'Rowan Health', 'industry' => 'B2B · Healthtech', 'duration' => 'Pipeline Program', 'title' => 'A $14M pipeline — built in nine months.', 'desc' => 'Rowan had the product; what they lacked was signal, position, and a repeatable system for enterprise demand.', 'kpis' => array( array( 'value' => '$14M', 'label' => 'Qualified pipeline' ), array( 'value' => '38%', 'label' => 'Reply rate' ), array( 'value' => '6', 'label' => 'Enterprise logos' ) ) ),
					array( 'client' => 'Atelier Nine', 'industry' => 'Hospitality', 'duration' => 'Brand & Digital', 'title' => 'A hotel brand built to be unmistakable.', 'desc' => 'New property. No category. We shaped the identity, built the booking experience, and launched with a signature content campaign.', 'kpis' => array( array( 'value' => '92%', 'label' => 'Year-1 occupancy' ), array( 'value' => '+47%', 'label' => 'ADR vs. forecast' ), array( 'value' => '11M', 'label' => 'Earned reach' ) ) ),
				);
				foreach ( $demo as $i => $d ) :
					$reverse = ( $i % 2 === 1 ) ? ' case--reverse' : '';
					?>
					<article class="case<?php echo esc_attr( $reverse ); ?> reveal">
						<div class="case__visual">
							<div class="case__visual-number"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></div>
							<div class="case__visual-mark"><?php echo esc_html( $d['client'] ); ?></div>
						</div>
						<div>
							<div class="case__meta"><span><?php echo esc_html( $d['industry'] ); ?></span><span><?php echo esc_html( $d['duration'] ); ?></span></div>
							<h3 class="case__title serif"><?php echo esc_html( $d['title'] ); ?></h3>
							<p class="case__desc"><?php echo esc_html( $d['desc'] ); ?></p>
							<div class="case__kpis">
								<?php foreach ( $d['kpis'] as $k ) : ?>
									<div><div class="case__kpi-value serif"><?php echo esc_html( $k['value'] ); ?></div><div class="case__kpi-label"><?php echo esc_html( $k['label'] ); ?></div></div>
								<?php endforeach; ?>
							</div>
						</div>
					</article>
				<?php endforeach;
			endif;
			?>
		</div>
	</div>
</section>
