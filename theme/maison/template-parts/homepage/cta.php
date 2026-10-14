<?php
/**
 * Homepage — Final CTA.
 *
 * @package Maison
 */
$title       = maison_field( 'cta_title', "If you're ready to grow, we're ready to build." );
$sub         = maison_field( 'cta_sub', 'We take on a limited number of partnerships each quarter. Thirty minutes, no pitch — a frank conversation about where your growth is leaking and what it would take to seal it.' );
$button      = maison_field( 'cta_button_label', 'Book a Strategy Call' );
$button_url  = maison_field( 'cta_button_url', maison_primary_cta_url() );
$email       = maison_option( 'contact_email', 'hello@maison.studio' );
$phone       = maison_option( 'contact_phone', '+1 (000) 000 0000' );
$studio      = maison_option( 'studio_meta', 'By appointment' );
?>
<section id="contact" class="cta">
	<div class="container">
		<div class="cta__inner">
			<div class="reveal">
				<div class="section-label"><?php esc_html_e( 'Begin', 'maison' ); ?></div>
				<h2 class="cta__title serif"><?php echo maison_safe_headline( $title ); ?></h2>
				<p class="cta__sub"><?php echo esc_html( $sub ); ?></p>
				<a href="<?php echo esc_url( $button_url ); ?>" class="btn btn-primary"><?php echo esc_html( $button ); ?><?php echo maison_arrow_svg(); ?></a>
			</div>
			<div class="cta__right reveal">
				<?php if ( $email ) : ?>
					<div class="cta__contact-item">
						<div class="cta__contact-label"><?php esc_html_e( 'Partnerships', 'maison' ); ?></div>
						<div class="cta__contact-value"><a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:inherit;text-decoration:none;"><?php echo esc_html( $email ); ?></a></div>
					</div>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<div class="cta__contact-item">
						<div class="cta__contact-label"><?php esc_html_e( 'Direct', 'maison' ); ?></div>
						<div class="cta__contact-value"><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" style="color:inherit;text-decoration:none;"><?php echo esc_html( $phone ); ?></a></div>
					</div>
				<?php endif; ?>
				<?php if ( $studio ) : ?>
					<div class="cta__contact-item">
						<div class="cta__contact-label"><?php esc_html_e( 'Studio', 'maison' ); ?></div>
						<div class="cta__contact-value"><?php echo esc_html( $studio ); ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
