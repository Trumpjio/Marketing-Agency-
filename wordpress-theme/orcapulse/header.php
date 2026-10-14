<?php
/**
 * The header template.
 *
 * @package OrcaPulse
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="page">
	<div class="orb orb-blue" style="top: 220px; left: -280px;"></div>
	<div class="orb orb-cyan" style="top: 1200px; right: -240px;"></div>
	<div class="orb orb-purple" style="top: 2200px; left: -260px;"></div>

	<header class="nav" id="nav">
		<div class="container nav__inner">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand">ORCA<span>PULSE</span></a>
				<?php
			}
			?>
			<button class="nav__mobile-trigger" aria-expanded="false" aria-controls="mobile-menu" id="mobile-toggle">
				<span class="burger"><span></span><span></span><span></span></span>
				<?php esc_html_e( 'Menu', 'orcapulse' ); ?>
			</button>
		</div>
		<div class="nav__mobile-panel" id="mobile-menu">
			<?php if ( has_nav_menu( 'services' ) ) : ?>
			<div class="nav__mobile-group">
				<div class="nav__mobile-group-title"><?php esc_html_e( 'Services', 'orcapulse' ); ?></div>
				<?php wp_nav_menu( array( 'theme_location' => 'services', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) ); ?>
			</div>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'industries' ) ) : ?>
			<div class="nav__mobile-group">
				<div class="nav__mobile-group-title"><?php esc_html_e( 'Industries', 'orcapulse' ); ?></div>
				<?php wp_nav_menu( array( 'theme_location' => 'industries', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) ); ?>
			</div>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'locations-gcc' ) ) : ?>
			<div class="nav__mobile-group">
				<div class="nav__mobile-group-title"><?php esc_html_e( 'Locations — GCC', 'orcapulse' ); ?></div>
				<?php wp_nav_menu( array( 'theme_location' => 'locations-gcc', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) ); ?>
			</div>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'locations-global' ) ) : ?>
			<div class="nav__mobile-group">
				<div class="nav__mobile-group-title"><?php esc_html_e( 'Locations — Global', 'orcapulse' ); ?></div>
				<?php wp_nav_menu( array( 'theme_location' => 'locations-global', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) ); ?>
			</div>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'studio' ) ) : ?>
			<div class="nav__mobile-group">
				<div class="nav__mobile-group-title"><?php esc_html_e( 'Studio', 'orcapulse' ); ?></div>
				<?php wp_nav_menu( array( 'theme_location' => 'studio', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) ); ?>
			</div>
			<?php endif; ?>

			<?php
			$cta_url = get_theme_mod( 'orcapulse_cta_url', 'https://calendly.com/orcapulse/intro-call' );
			$cta_label = get_theme_mod( 'orcapulse_cta_label', __( 'Book a Call', 'orcapulse' ) );
			?>
			<a href="<?php echo esc_url( $cta_url ); ?>" target="_blank" rel="noopener" class="btn btn--primary"><?php echo esc_html( $cta_label ); ?></a>
		</div>
	</header>
