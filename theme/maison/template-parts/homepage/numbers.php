<?php
/**
 * Homepage — By the numbers (big editorial stat row).
 *
 * @package Maison
 */
$label = maison_field( 'numbers_label', 'By the numbers' );
$title = maison_field( 'numbers_title', 'Twelve years. One method. Measured in revenue.' );
$intro = maison_field( 'numbers_intro', 'A quiet track record, built one patient engagement at a time. These are the numbers that decide whether a partnership continues.' );
$stats = maison_field( 'numbers_stats', array(
	array( 'value' => '+312%', 'caption' => 'Average pipeline growth across engagements, year one' ),
	array( 'value' => '$2.4B', 'caption' => 'Revenue attributed to campaigns we have run' ),
	array( 'value' => '94%',   'caption' => 'Of partnerships renewed into a second year' ),
	array( 'value' => '47',    'caption' => 'Ambitious businesses partnered to date' ),
) );
?>
<section class="stats">
	<div class="container">
		<div class="stats__head">
			<div class="reveal">
				<div class="stats__label"><?php echo esc_html( $label ); ?></div>
				<h2 class="stats__title"><?php echo maison_safe_headline( $title ); ?></h2>
			</div>
			<p class="stats__intro reveal"><?php echo esc_html( $intro ); ?></p>
		</div>

		<?php if ( is_array( $stats ) && ! empty( $stats ) ) : ?>
			<div class="stats__grid">
				<?php foreach ( $stats as $s ) : ?>
					<div class="stats__item reveal">
						<div class="stats__value"><?php echo maison_safe_headline( $s['value'] ?? '' ); ?></div>
						<div class="stats__caption"><?php echo esc_html( $s['caption'] ?? '' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
