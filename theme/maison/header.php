<?php
/**
 * Header template.
 *
 * @package Maison
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="container">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<?php echo esc_html( maison_brand_name() ); ?><span>.</span>
			<?php endif; ?>
		</a>

		<nav class="nav" aria-label="<?php esc_attr_e( 'Primary', 'maison' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => '',
					'items_wrap'     => '%3$s',
					'depth'          => 1,
				) );
			} else {
				echo '<a href="' . esc_url( get_post_type_archive_link( 'case_study' ) ?: home_url( '/#work' ) ) . '">' . esc_html__( 'Work', 'maison' ) . '</a>';
				echo '<a href="' . esc_url( home_url( '/#approach' ) ) . '">' . esc_html__( 'Approach', 'maison' ) . '</a>';
				echo '<a href="' . esc_url( get_post_type_archive_link( 'service' ) ?: home_url( '/#capabilities' ) ) . '">' . esc_html__( 'Capabilities', 'maison' ) . '</a>';
				echo '<a href="' . esc_url( home_url( '/#contact' ) ) . '">' . esc_html__( 'Contact', 'maison' ) . '</a>';
			}
			?>
		</nav>

		<a href="<?php echo esc_url( maison_primary_cta_url() ); ?>" class="btn btn-primary">
			<?php esc_html_e( 'Book a Call', 'maison' ); ?>
			<?php echo maison_arrow_svg(); ?>
		</a>
	</div>
</header>

<main id="top">
