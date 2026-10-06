<?php
/**
 * Template Name: About
 *
 * @package Maison
 */

get_header(); ?>

<?php
$eyebrow         = maison_field( 'about_eyebrow', 'About' );
$title           = maison_field( 'about_title', 'A different kind of<br>agency.' );
$sub             = maison_field( 'about_sub', 'We built the agency we wished existed when we were on the client side — small, senior, outcome-led, and ruthlessly honest about what actually moves the number.' );
$story_title     = maison_field( 'story_title', 'How we got here.' );
$story_body      = maison_field( 'story_body' );
$principles_title = maison_field( 'principles_title', 'What we believe.' );
$principles      = maison_field( 'principles', array(
	array( 'title' => 'Clarity beats cleverness.',           'desc' => 'The best campaign is the one your mother could explain. We write for comprehension, then make it beautiful — never the other way around.' ),
	array( 'title' => 'Positioning before promotion.',       'desc' => "If we can't sharpen what you're selling, no amount of media spend will fix it. Every engagement starts with the thing worth saying." ),
	array( 'title' => 'Senior people, every day.',           'desc' => 'The person who sells you the engagement is the person running it. No account-manager layer, no juniors learning on your budget.' ),
	array( 'title' => 'Measured in revenue, not reach.',     'desc' => 'Impressions make reports look busy. Pipeline, LTV and gross margin decide whether we keep the account.' ),
	array( 'title' => 'Build to compound.',                  'desc' => 'Every quarter should leave you with a cheaper CAC, a deeper moat, or an asset you did not have before. If it does not, we did not do our job.' ),
) );
$team_title      = maison_field( 'team_title', 'The people.' );
$team            = maison_field( 'team', array() );
?>

<section class="hero">
	<div class="container">
		<div class="hero__eyebrow reveal"><?php echo esc_html( $eyebrow ); ?></div>
		<h1 class="hero__title serif reveal"><?php echo maison_safe_headline( $title ); ?></h1>
		<p class="hero__sub reveal"><?php echo esc_html( $sub ); ?></p>
	</div>
</section>

<?php if ( $story_body ) : ?>
	<section style="padding-top:0;">
		<div class="container" style="max-width:860px;">
			<div class="reveal" style="margin-bottom:var(--space-5);">
				<div class="section-label"><?php esc_html_e( 'Our story', 'maison' ); ?></div>
				<h2 class="section-title serif"><?php echo esc_html( $story_title ); ?></h2>
			</div>
			<div class="reveal" style="color:var(--color-ink-soft);font-size:1.125rem;line-height:1.8;">
				<?php echo wp_kses_post( $story_body ); ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( is_array( $principles ) && ! empty( $principles ) ) : ?>
	<section class="approach">
		<div class="container">
			<div class="reveal" style="max-width:56rem;">
				<div class="section-label"><?php esc_html_e( 'Principles', 'maison' ); ?></div>
				<h2 class="section-title serif"><?php echo esc_html( $principles_title ); ?></h2>
			</div>
			<div class="approach__grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
				<?php foreach ( $principles as $i => $p ) : ?>
					<div class="pillar reveal">
						<div class="pillar__num"><?php echo esc_html( sprintf( '%02d —', $i + 1 ) ); ?></div>
						<h3 class="pillar__title"><?php echo esc_html( $p['title'] ?? '' ); ?></h3>
						<p class="pillar__desc"><?php echo esc_html( $p['desc'] ?? '' ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( is_array( $team ) && ! empty( $team ) ) : ?>
	<section>
		<div class="container">
			<div class="reveal" style="margin-bottom:var(--space-5);">
				<div class="section-label"><?php esc_html_e( 'Team', 'maison' ); ?></div>
				<h2 class="section-title serif"><?php echo esc_html( $team_title ); ?></h2>
			</div>
			<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:var(--space-5);">
				<?php foreach ( $team as $member ) : ?>
					<article class="reveal">
						<?php $photo = $member['photo'] ?? null;
						if ( is_array( $photo ) && ! empty( $photo['url'] ) ) : ?>
							<figure style="aspect-ratio:4/5;overflow:hidden;background:var(--color-surface);border:1px solid var(--color-divider);margin-bottom:var(--space-3);">
								<img src="<?php echo esc_url( $photo['url'] ); ?>" alt="<?php echo esc_attr( $member['name'] ?? '' ); ?>" style="width:100%;height:100%;object-fit:cover;" loading="lazy">
							</figure>
						<?php else : ?>
							<div style="aspect-ratio:4/5;background:var(--color-surface);border:1px solid var(--color-divider);margin-bottom:var(--space-3);"></div>
						<?php endif; ?>
						<h3 class="serif" style="font-size:1.25rem;margin-bottom:0.25rem;"><?php echo esc_html( $member['name'] ?? '' ); ?></h3>
						<div style="font-size:0.875rem;letter-spacing:0.1em;text-transform:uppercase;color:var(--color-muted);margin-bottom:var(--space-2);"><?php echo esc_html( $member['role'] ?? '' ); ?></div>
						<p style="color:var(--color-ink-soft);font-size:0.9375rem;line-height:1.6;"><?php echo esc_html( $member['bio'] ?? '' ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/homepage/cta' ); ?>

<?php get_footer(); ?>
