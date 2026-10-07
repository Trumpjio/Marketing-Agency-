<?php
/**
 * Single blog post template.
 *
 * @package OrcaPulse
 */

get_header();
?>

<main id="main" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<section class="section hero">
				<div class="container">
					<nav class="breadcrumb reveal" aria-label="Breadcrumb">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'orcapulse' ); ?></a>
						<span>›</span>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/insights/' ) ); ?>"><?php esc_html_e( 'Insights', 'orcapulse' ); ?></a>
					</nav>
					<h1 class="reveal"><?php the_title(); ?></h1>
					<?php if ( get_the_excerpt() ) : ?>
						<p class="hero__sub reveal"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
			</section>

			<?php if ( has_post_thumbnail() ) : ?>
				<section class="section">
					<div class="container">
						<div class="article-visual reveal" style="aspect-ratio:16/9;overflow:hidden;border-radius:var(--r-lg);">
							<?php the_post_thumbnail( 'full', array( 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<section class="section">
				<div class="container">
					<div class="prose reveal">
						<?php the_content(); ?>
					</div>
				</div>
			</section>

			<section class="section">
				<div class="container">
					<div class="cta-block reveal">
						<h2><?php esc_html_e( 'Thinking about this for your business?', 'orcapulse' ); ?></h2>
						<p><?php esc_html_e( "A 30-minute call. No pitch deck. We'll tell you honestly whether we can help.", 'orcapulse' ); ?></p>
						<a href="<?php echo esc_url( get_theme_mod( 'orcapulse_cta_url', 'https://calendly.com/orcapulse/intro-call' ) ); ?>" target="_blank" rel="noopener" class="btn btn--primary"><?php esc_html_e( 'Book a strategy call', 'orcapulse' ); ?></a>
					</div>
				</div>
			</section>
		</article>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
