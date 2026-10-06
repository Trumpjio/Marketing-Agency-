<?php
/**
 * Homepage — Testimonial.
 *
 * @package Maison
 */
$text = maison_field( 'quote_text', "They stopped feeling like an agency after month two. They started feeling like <em>the team</em>." );
$name = maison_field( 'quote_name', 'Isla Mendez' );
$role = maison_field( 'quote_role', 'Founder & CEO, Northbrook' );
?>
<section class="quote">
	<div class="container">
		<div class="reveal">
			<div class="quote__mark">&ldquo;</div>
			<p class="quote__text"><?php echo maison_safe_headline( $text ); ?></p>
			<div class="quote__attribution">
				<span class="quote__name serif"><?php echo esc_html( $name ); ?></span>
				<span><?php echo esc_html( $role ); ?></span>
			</div>
		</div>
	</div>
</section>
