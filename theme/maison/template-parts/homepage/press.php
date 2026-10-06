<?php
/**
 * Homepage — Press / "As featured in" strip.
 *
 * @package Maison
 */
$label   = maison_field( 'press_label', 'As featured in' );
$outlets = maison_field( 'press_outlets', array() );
if ( ! is_array( $outlets ) || empty( $outlets ) ) {
	$outlets = array(
		array( 'name' => 'Fast Company' ),
		array( 'name' => 'Forbes' ),
		array( 'name' => 'AdAge' ),
		array( 'name' => 'The Drum' ),
		array( 'name' => 'Monocle' ),
	);
}
?>
<section class="press">
	<div class="container">
		<div class="press__inner">
			<div class="press__label"><?php echo esc_html( $label ); ?></div>
			<div class="press__list">
				<?php foreach ( $outlets as $o ) :
					$name = $o['name'] ?? '';
					$url  = $o['url'] ?? '';
					if ( $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" class="press__item" rel="noopener" target="_blank"><?php echo esc_html( $name ); ?></a>
					<?php else : ?>
						<span class="press__item"><?php echo esc_html( $name ); ?></span>
					<?php endif;
				endforeach; ?>
			</div>
		</div>
	</div>
</section>
