<?php
/**
 * The footer template.
 *
 * @package OrcaPulse
 */
?>
	<footer class="footer">
		<div class="container footer__inner">
			<div class="footer__brand">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				} else {
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand">ORCA<span>PULSE</span></a>
					<?php
				}
				?>
				<p class="footer__tag"><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'A specialist growth partner for ambitious businesses. Rooted in the GCC, working globally.', 'orcapulse' ) ); ?></p>
			</div>
			<div class="footer__cols">
				<?php if ( has_nav_menu( 'footer-services' ) || is_active_sidebar( 'footer-1' ) ) : ?>
				<div class="footer__col">
					<div class="footer__title"><?php esc_html_e( 'Services', 'orcapulse' ); ?></div>
					<?php
					if ( has_nav_menu( 'footer-services' ) ) {
						wp_nav_menu( array( 'theme_location' => 'footer-services', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) );
					} else {
						dynamic_sidebar( 'footer-1' );
					}
					?>
				</div>
				<?php endif; ?>

				<?php if ( has_nav_menu( 'footer-industries' ) || is_active_sidebar( 'footer-2' ) ) : ?>
				<div class="footer__col">
					<div class="footer__title"><?php esc_html_e( 'Industries', 'orcapulse' ); ?></div>
					<?php
					if ( has_nav_menu( 'footer-industries' ) ) {
						wp_nav_menu( array( 'theme_location' => 'footer-industries', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) );
					} else {
						dynamic_sidebar( 'footer-2' );
					}
					?>
				</div>
				<?php endif; ?>

				<?php if ( has_nav_menu( 'footer-studio' ) || is_active_sidebar( 'footer-3' ) ) : ?>
				<div class="footer__col">
					<div class="footer__title"><?php esc_html_e( 'Studio', 'orcapulse' ); ?></div>
					<?php
					if ( has_nav_menu( 'footer-studio' ) ) {
						wp_nav_menu( array( 'theme_location' => 'footer-studio', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) );
					} else {
						dynamic_sidebar( 'footer-3' );
					}
					?>
				</div>
				<?php endif; ?>

				<?php if ( has_nav_menu( 'footer-legal' ) || is_active_sidebar( 'footer-4' ) ) : ?>
				<div class="footer__col">
					<div class="footer__title"><?php esc_html_e( 'Legal', 'orcapulse' ); ?></div>
					<?php
					if ( has_nav_menu( 'footer-legal' ) ) {
						wp_nav_menu( array( 'theme_location' => 'footer-legal', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false ) );
					} else {
						dynamic_sidebar( 'footer-4' );
					}
					?>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="container footer__bottom">
			<small>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'orcapulse' ); ?></small>
		</div>
	</footer>
</div><!-- .page -->

<?php wp_footer(); ?>
</body>
</html>
