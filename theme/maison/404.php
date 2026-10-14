<?php
/**
 * 404 template.
 *
 * @package Maison
 */
get_header(); ?>

<section class="hero">
	<div class="container" style="text-align:center;">
		<div class="hero__eyebrow reveal" style="justify-content:center;">404</div>
		<h1 class="hero__title serif reveal" style="margin-left:auto;margin-right:auto;"><?php esc_html_e( 'Nothing here.', 'maison' ); ?></h1>
		<p class="hero__sub reveal" style="margin-left:auto;margin-right:auto;"><?php esc_html_e( 'The page you were looking for has moved, or never existed.', 'maison' ); ?></p>
		<div class="hero__cta reveal" style="justify-content:center;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Return home', 'maison' ); ?><?php echo maison_arrow_svg(); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
