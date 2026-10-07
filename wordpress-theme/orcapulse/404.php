<?php
/**
 * 404 template.
 *
 * @package OrcaPulse
 */

get_header();
?>

<main id="main" class="site-main">
	<section class="section hero">
		<div class="container" style="text-align:center;">
			<h1 class="reveal"><?php esc_html_e( "Page not found.", 'orcapulse' ); ?></h1>
			<p class="hero__sub reveal"><?php esc_html_e( "The page you were looking for doesn't exist. Try one of these instead.", 'orcapulse' ); ?></p>
			<div class="hero__actions reveal">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary"><?php esc_html_e( 'Go home', 'orcapulse' ); ?></a>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
