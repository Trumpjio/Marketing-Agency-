<?php
/**
 * Footer template.
 *
 * @package Maison
 */
?>
</main>

<footer class="site-footer">
	<div class="container site-footer__inner">
		<div class="site-footer__brand"><?php echo esc_html( maison_brand_name() ); ?><span>.</span></div>

		<?php
		$social = maison_option( 'social_links', array() );
		if ( is_array( $social ) && ! empty( $social ) ) :
			?>
			<div class="site-footer__social">
				<?php foreach ( $social as $s ) :
					if ( empty( $s['url'] ) ) continue; ?>
					<a href="<?php echo esc_url( $s['url'] ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $s['label'] ?? '' ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="site-footer__meta">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( maison_brand_name() ); ?> &middot; <?php esc_html_e( 'All rights reserved', 'maison' ); ?></div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
