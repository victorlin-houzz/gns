<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package um-theme
 */

get_header(); ?>
<div class="website-canvas">
<main id="primary" class="content-area" tabindex="-1">
	<div id="main" class="site-main">
	<section class="error-404 not-found website-canvas">
		<div class="container-card-content">
			<?php
			// Elementor `404` location.
			if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) { ?>
				<header class="page-header">
					<h1 class="page-title">
						<?php apply_filters( 'um_theme_404_title', esc_html_e( 'Oops! That page can&rsquo;t be found.', 'um-theme' ) ); // WPCS: XSS OK. ?>
					</h1>
				</header><!-- .page-header -->

				<div class="page-content">
					<p><?php apply_filters( 'um_theme_404_text', esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'um-theme' ) );?></p>
					<?php get_search_form(); ?>
				</div>
			<?php } ?>
		</div>
	</section>
	</div>
</main>
</div>
<?php get_footer(); ?>
