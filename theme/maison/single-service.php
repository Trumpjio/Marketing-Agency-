<?php
/**
 * Single Service.
 *
 * @package Maison
 */
get_header(); ?>

<?php while ( have_posts() ) : the_post();
	$promise      = maison_field( 'promise' );
	$deliverables = maison_field( 'deliverables', array() );
	$process      = maison_field( 'process', array() );
	$faqs         = maison_field( 'faqs', array() );
	?>

	<section class="hero" style="padding-bottom:var(--space-6);">
		<div class="container">
			<div class="hero__eyebrow reveal"><?php esc_html_e( 'Capability', 'maison' ); ?></div>
			<h1 class="hero__title serif reveal" style="font-size:clamp(2rem,6vw,4rem);"><?php the_title(); ?></h1>
			<?php if ( $promise ) : ?>
				<p class="hero__sub reveal"><?php echo esc_html( $promise ); ?></p>
			<?php endif; ?>
			<div class="hero__cta reveal">
				<a href="<?php echo esc_url( maison_primary_cta_url() ); ?>" class="btn btn-primary"><?php esc_html_e( 'Book a Strategy Call', 'maison' ); ?><?php echo maison_arrow_svg(); ?></a>
			</div>
		</div>
	</section>

	<?php if ( get_the_content() ) : ?>
		<section>
			<div class="container" style="max-width:860px;">
				<div class="reveal" style="color:var(--color-ink-soft);font-size:1.125rem;line-height:1.8;"><?php the_content(); ?></div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( is_array( $deliverables ) && ! empty( $deliverables ) ) : ?>
		<section>
			<div class="container">
				<div class="reveal" style="margin-bottom:var(--space-5);">
					<div class="section-label"><?php esc_html_e( 'What we deliver', 'maison' ); ?></div>
					<h2 class="section-title serif"><?php esc_html_e( 'Everything, considered.', 'maison' ); ?></h2>
				</div>
				<div class="capabilities__grid">
					<?php foreach ( $deliverables as $i => $d ) : ?>
						<div class="capability reveal">
							<div class="capability__num"><?php echo esc_html( sprintf( '%02d —', $i + 1 ) ); ?></div>
							<h3 class="capability__title serif" style="font-size:1.125rem;"><?php echo esc_html( $d['item'] ?? '' ); ?></h3>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( is_array( $process ) && ! empty( $process ) ) : ?>
		<section class="approach">
			<div class="container">
				<div class="reveal" style="max-width:56rem;">
					<div class="section-label"><?php esc_html_e( 'Process', 'maison' ); ?></div>
					<h2 class="section-title serif"><?php esc_html_e( 'How we work.', 'maison' ); ?></h2>
				</div>
				<div class="approach__grid">
					<?php foreach ( $process as $i => $step ) : ?>
						<div class="pillar reveal">
							<div class="pillar__num"><?php echo esc_html( sprintf( '%02d —', $i + 1 ) ); ?></div>
							<h3 class="pillar__title"><?php echo esc_html( $step['title'] ?? '' ); ?></h3>
							<p class="pillar__desc"><?php echo esc_html( $step['desc'] ?? '' ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( is_array( $faqs ) && ! empty( $faqs ) ) : ?>
		<section>
			<div class="container" style="max-width:860px;">
				<div class="reveal" style="margin-bottom:var(--space-5);">
					<div class="section-label"><?php esc_html_e( 'FAQ', 'maison' ); ?></div>
					<h2 class="section-title serif"><?php esc_html_e( 'Questions, answered.', 'maison' ); ?></h2>
				</div>
				<div style="border-top:1px solid var(--color-divider);">
					<?php foreach ( $faqs as $faq ) : ?>
						<details class="reveal" style="padding:var(--space-3) 0;border-bottom:1px solid var(--color-divider);">
							<summary style="font-family:var(--font-display);font-size:1.25rem;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:var(--space-3);">
								<span><?php echo esc_html( $faq['question'] ?? '' ); ?></span>
								<span style="color:var(--color-accent);font-size:1.5rem;">+</span>
							</summary>
							<div style="padding-top:var(--space-2);color:var(--color-ink-soft);line-height:1.7;max-width:66ch;"><?php echo wp_kses_post( wpautop( $faq['answer'] ?? '' ) ); ?></div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/homepage/cta' ); ?>
<?php endwhile; ?>

<?php get_footer(); ?>
