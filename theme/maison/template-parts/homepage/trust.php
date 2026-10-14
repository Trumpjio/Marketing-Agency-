<?php
/**
 * Homepage — Trust marquee (continuous horizontal scroll of client logos).
 *
 * @package Maison
 */
$label = maison_field( 'trust_label', 'Trusted by businesses that compound' );
$logos = maison_field( 'trust_logos', array() );
if ( ! is_array( $logos ) || empty( $logos ) ) {
	$logos = array(
		array( 'name' => 'Lumière & Co' ),
		array( 'name' => 'Northbrook' ),
		array( 'name' => 'Atelier Nine' ),
		array( 'name' => 'Vireo' ),
		array( 'name' => 'Rowan Health' ),
		array( 'name' => 'Halden' ),
		array( 'name' => 'Marchetti' ),
		array( 'name' => 'Studio Oak' ),
	);
}

// Duplicate the logo list for seamless CSS marquee loop.
$track = array_merge( $logos, $logos );
?>
<section class="trust">
	<div class="trust__label-wrap">
		<span class="trust__label"><?php echo esc_html( $label ); ?></span>
	</div>
	<div class="marquee" aria-hidden="true">
		<div class="marquee__track">
			<?php foreach ( $track as $logo ) :
				$name  = $logo['name'] ?? '';
				$image = $logo['image'] ?? null;
				if ( is_array( $image ) && ! empty( $image['url'] ) ) : ?>
					<span class="marquee__item"><img src="<?php echo esc_url( $image['url'] ); ?>" alt="" loading="lazy"></span>
				<?php else : ?>
					<span class="marquee__item"><?php echo esc_html( $name ); ?></span>
				<?php endif;
			endforeach; ?>
		</div>
	</div>
</section>
