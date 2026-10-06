<?php
/**
 * Template Name: Contact
 *
 * @package Maison
 */

get_header(); ?>

<?php
$eyebrow     = maison_field( 'contact_eyebrow', 'Begin' );
$title       = maison_field( 'contact_title', "Let's build the<br><em>next</em> stage." );
$sub         = maison_field( 'contact_sub', 'Thirty minutes, no pitch — a frank conversation about where your growth is leaking and what it would take to seal it. We take on a limited number of partnerships each quarter.' );
$shortcode   = maison_field( 'contact_form_shortcode' );
$steps_title = maison_field( 'steps_title', 'What happens next.' );
$steps       = maison_field( 'steps', array(
	array( 'title' => 'A short reply.',       'desc' => 'Within one business day, you hear back from a partner — never an inbox robot or an SDR.' ),
	array( 'title' => 'A 30-minute call.',    'desc' => 'We ask about the business, the number you need to move, and what you have tried. No deck, no demo.' ),
	array( 'title' => 'A written proposal.',  'desc' => 'If it is a fit, you receive a scoped engagement with specific deliverables, a timeline, and a flat quarterly fee.' ),
) );

$email  = maison_option( 'contact_email', 'hello@maison.studio' );
$phone  = maison_option( 'contact_phone' );
$studio = maison_option( 'studio_meta', 'By appointment' );
?>

<section class="hero">
	<div class="container">
		<div class="hero__eyebrow reveal"><?php echo esc_html( $eyebrow ); ?></div>
		<h1 class="hero__title serif reveal"><?php echo maison_safe_headline( $title ); ?></h1>
		<p class="hero__sub reveal"><?php echo esc_html( $sub ); ?></p>
	</div>
</section>

<section style="padding-top:0;">
	<div class="container">
		<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:var(--space-6);align-items:start;" class="contact__grid">
			<div class="reveal">
				<div class="section-label"><?php esc_html_e( 'Enquiry', 'maison' ); ?></div>
				<h2 class="section-title serif" style="font-size:2rem;max-width:16ch;"><?php esc_html_e( 'Tell us about the business.', 'maison' ); ?></h2>

				<?php if ( $shortcode ) : ?>
					<div style="margin-top:var(--space-4);"><?php echo do_shortcode( $shortcode ); ?></div>
				<?php else : ?>
					<form action="#" method="post" class="contact-form" style="margin-top:var(--space-4);">
						<?php wp_nonce_field( 'maison_contact', 'maison_contact_nonce' ); ?>
						<div class="form-row">
							<label class="form-label" for="cf-name"><?php esc_html_e( 'Your name', 'maison' ); ?></label>
							<input id="cf-name" type="text" name="name" required class="form-input">
						</div>
						<div class="form-row">
							<label class="form-label" for="cf-email"><?php esc_html_e( 'Email', 'maison' ); ?></label>
							<input id="cf-email" type="email" name="email" required class="form-input">
						</div>
						<div class="form-row">
							<label class="form-label" for="cf-company"><?php esc_html_e( 'Company', 'maison' ); ?></label>
							<input id="cf-company" type="text" name="company" class="form-input">
						</div>
						<div class="form-row">
							<label class="form-label" for="cf-budget"><?php esc_html_e( 'Engagement budget', 'maison' ); ?></label>
							<select id="cf-budget" name="budget" class="form-input">
								<option value=""><?php esc_html_e( 'Select a range', 'maison' ); ?></option>
								<option value="under-25"><?php esc_html_e( 'Under $25K / quarter', 'maison' ); ?></option>
								<option value="25-50"><?php esc_html_e( '$25K – $50K / quarter', 'maison' ); ?></option>
								<option value="50-100"><?php esc_html_e( '$50K – $100K / quarter', 'maison' ); ?></option>
								<option value="100-plus"><?php esc_html_e( '$100K+ / quarter', 'maison' ); ?></option>
							</select>
						</div>
						<div class="form-row">
							<label class="form-label" for="cf-message"><?php esc_html_e( 'What are you trying to move?', 'maison' ); ?></label>
							<textarea id="cf-message" name="message" rows="5" required class="form-input"></textarea>
						</div>
						<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Send enquiry', 'maison' ); ?><?php echo maison_arrow_svg(); ?></button>
						<p style="font-size:0.75rem;color:var(--color-muted);margin-top:var(--space-3);letter-spacing:0.05em;">
							<?php esc_html_e( 'This form is a placeholder — install Fluent Forms (or similar), paste the shortcode in the Contact page ACF field, and it replaces this.', 'maison' ); ?>
						</p>
					</form>
				<?php endif; ?>
			</div>

			<aside class="reveal">
				<div style="padding:var(--space-4);background:var(--color-surface);border:1px solid var(--color-divider);">
					<div class="section-label"><?php esc_html_e( 'Direct', 'maison' ); ?></div>
					<?php if ( $email ) : ?>
						<div style="margin-bottom:var(--space-3);">
							<div style="font-size:0.75rem;letter-spacing:0.22em;text-transform:uppercase;color:var(--color-muted);margin-bottom:0.25rem;"><?php esc_html_e( 'Partnerships', 'maison' ); ?></div>
							<a href="mailto:<?php echo esc_attr( $email ); ?>" style="font-family:var(--font-display);font-size:1.125rem;color:var(--color-ink);"><?php echo esc_html( $email ); ?></a>
						</div>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<div style="margin-bottom:var(--space-3);">
							<div style="font-size:0.75rem;letter-spacing:0.22em;text-transform:uppercase;color:var(--color-muted);margin-bottom:0.25rem;"><?php esc_html_e( 'Call', 'maison' ); ?></div>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" style="font-family:var(--font-display);font-size:1.125rem;color:var(--color-ink);"><?php echo esc_html( $phone ); ?></a>
						</div>
					<?php endif; ?>
					<?php if ( $studio ) : ?>
						<div>
							<div style="font-size:0.75rem;letter-spacing:0.22em;text-transform:uppercase;color:var(--color-muted);margin-bottom:0.25rem;"><?php esc_html_e( 'Studio', 'maison' ); ?></div>
							<div style="font-family:var(--font-display);font-size:1.125rem;color:var(--color-ink);"><?php echo esc_html( $studio ); ?></div>
						</div>
					<?php endif; ?>
				</div>
			</aside>
		</div>
	</div>
</section>

<?php if ( is_array( $steps ) && ! empty( $steps ) ) : ?>
	<section class="approach">
		<div class="container">
			<div class="reveal" style="max-width:56rem;">
				<div class="section-label"><?php esc_html_e( 'Process', 'maison' ); ?></div>
				<h2 class="section-title serif"><?php echo esc_html( $steps_title ); ?></h2>
			</div>
			<div class="approach__grid">
				<?php foreach ( $steps as $i => $s ) : ?>
					<div class="pillar reveal">
						<div class="pillar__num"><?php echo esc_html( sprintf( '%02d —', $i + 1 ) ); ?></div>
						<h3 class="pillar__title"><?php echo esc_html( $s['title'] ?? '' ); ?></h3>
						<p class="pillar__desc"><?php echo esc_html( $s['desc'] ?? '' ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php get_footer(); ?>
