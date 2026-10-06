<?php
/**
 * Homepage — Trust strip.
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
	);
}
?>
<section class="trust">
	<div class="container trust__inner">
		<div class="trust__label"><?php echo esc_html( $label ); ?></div>
		<div class="trust__logos">
			<?php foreach ( $logos as $logo ) :
				$name  = $logo['name'] ?? '';
				$image = $logo['image'] ?? null;
				if ( is_array( $image ) && ! empty( $image['url'] ) ) : ?>
					<img class="trust__logo" src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" width="140" height="42">
				<?php else : ?>
					<span class="trust__logo"><?php echo esc_html( $name ); ?></span>
				<?php endif;
			endforeach; ?>
		</div>
	</div>
</section>
