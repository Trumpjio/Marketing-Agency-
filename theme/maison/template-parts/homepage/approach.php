<?php
/**
 * Homepage — Approach.
 *
 * @package Maison
 */
$label   = maison_field( 'approach_label', 'The Approach' );
$title   = maison_field( 'approach_title', 'One partner. One engine. Compounding returns.' );
$intro   = maison_field( 'approach_intro', "Most agencies ship tactics. We build growth as a system — one that gets smarter, cheaper and more defensible with every quarter it runs. Three principles decide what we accept and how we deliver." );
$pillars = maison_field( 'approach_pillars', maison_default_pillars() );
?>
<section id="approach" class="approach">
	<div class="container">
		<div class="reveal" style="max-width: 56rem">
			<div class="section-label"><?php echo esc_html( $label ); ?></div>
			<h2 class="section-title serif"><?php echo maison_safe_headline( $title ); ?></h2>
			<p class="section-intro"><?php echo esc_html( $intro ); ?></p>
		</div>

		<?php if ( is_array( $pillars ) && ! empty( $pillars ) ) : ?>
			<div class="approach__grid">
				<?php foreach ( $pillars as $i => $p ) : ?>
					<div class="pillar reveal">
						<div class="pillar__num"><?php echo esc_html( sprintf( '%02d —', $i + 1 ) ); ?></div>
						<h3 class="pillar__title"><?php echo esc_html( $p['title'] ?? '' ); ?></h3>
						<p class="pillar__desc"><?php echo esc_html( $p['desc'] ?? '' ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
