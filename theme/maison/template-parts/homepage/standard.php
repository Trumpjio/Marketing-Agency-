<?php
/**
 * Homepage — The Standard (commitments / non-negotiables).
 *
 * @package Maison
 */
$label = maison_field( 'standard_label', 'The Standard' );
$title = maison_field( 'standard_title', 'Five commitments we never break.' );
$intro = maison_field( 'standard_intro', 'The agency industry runs on vague promises. These are the ones we have put in writing, in every contract we have ever signed.' );
$items = maison_field( 'standard_items', array(
	array( 'title' => 'Senior people, every day.',              'desc' => 'The person who sells you the engagement is the person running it. No account-manager layer, no juniors learning on your budget.' ),
	array( 'title' => 'A flat fee. Known in advance.',          'desc' => 'Quarterly retainer, scoped before you sign. No hourly billing, no surprise invoices, no scope-of-work gymnastics.' ),
	array( 'title' => 'Honest reporting, every month.',          'desc' => 'Pipeline, LTV and gross-margin numbers — reported against the goals we agreed on in week one, not reshuffled to look flattering.' ),
	array( 'title' => 'A real exit at any point.',               'desc' => 'Thirty-day notice and your data, access and work product handed back clean. If we are not earning the retainer, you should walk.' ),
	array( 'title' => 'Discretion, by default.',                 'desc' => 'We publish case studies only with written approval. The work speaks for the client first, for us second.' ),
) );
?>
<section class="standard">
	<div class="bg-numeral" style="right:-5vw;top:5vw;">05</div>
	<div class="container" style="position:relative;z-index:1;">
		<div class="standard__head">
			<div class="reveal">
				<div class="section-label"><?php echo esc_html( $label ); ?></div>
				<h2 class="section-title serif"><?php echo esc_html( $title ); ?></h2>
			</div>
			<p class="section-intro reveal"><?php echo esc_html( $intro ); ?></p>
		</div>

		<?php if ( is_array( $items ) && ! empty( $items ) ) : ?>
			<div class="standard__list">
				<?php foreach ( $items as $i => $item ) : ?>
					<div class="standard__row reveal">
						<div class="standard__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></div>
						<h3 class="standard__title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
						<p class="standard__desc"><?php echo esc_html( $item['desc'] ?? '' ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
