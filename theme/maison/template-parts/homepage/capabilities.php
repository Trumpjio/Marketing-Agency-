<?php
/**
 * Homepage — Capabilities grid.
 *
 * Pulls from `service` CPT. Falls back to defaults so homepage renders
 * before any services are added.
 *
 * @package Maison
 */

$label = maison_field( 'cap_label', 'Capabilities' );
$title = maison_field( 'cap_title', 'Full-stack growth capability, under one roof.' );
$intro = maison_field( 'cap_intro', 'Not a menu of services — a single team that moves across every lever your growth needs. You brief the outcome; we assemble the craft behind it.' );

$services = maison_services( 8 );
$items = array();
if ( $services->have_posts() ) {
	while ( $services->have_posts() ) {
		$services->the_post();
		$items[] = array(
			'title'   => get_the_title(),
			'summary' => maison_field( 'summary', get_the_excerpt() ),
			'url'     => get_permalink(),
		);
	}
	wp_reset_postdata();
} else {
	foreach ( maison_default_capabilities() as $d ) {
		$items[] = array( 'title' => $d['title'], 'summary' => $d['summary'], 'url' => '' );
	}
}
?>
<section id="capabilities">
	<div class="container">
		<div class="capabilities__head">
			<div class="reveal">
				<div class="section-label"><?php echo esc_html( $label ); ?></div>
				<h2 class="section-title serif"><?php echo esc_html( $title ); ?></h2>
			</div>
			<p class="section-intro reveal"><?php echo esc_html( $intro ); ?></p>
		</div>

		<div class="capabilities__grid">
			<?php foreach ( $items as $i => $cap ) : ?>
				<?php $tag = $cap['url'] ? 'a' : 'div'; ?>
				<<?php echo $tag; ?> class="capability reveal"<?php if ( $cap['url'] ) echo ' href="' . esc_url( $cap['url'] ) . '" style="color:inherit;text-decoration:none;"'; ?>>
					<div class="capability__num"><?php echo esc_html( sprintf( '%02d —', $i + 1 ) ); ?></div>
					<h3 class="capability__title serif"><?php echo esc_html( $cap['title'] ); ?></h3>
					<p class="capability__desc"><?php echo esc_html( $cap['summary'] ); ?></p>
				</<?php echo $tag; ?>>
			<?php endforeach; ?>
		</div>
	</div>
</section>
