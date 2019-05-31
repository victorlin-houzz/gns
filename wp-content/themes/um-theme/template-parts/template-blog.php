<?php
/**
 *
 * Template name: Blog Posts
 *
 * @package um-theme
 */
global $defaults;

get_header();?>

<main id="primary" class="content-area" tabindex="-1">
<div id="main" class="site-main">
<?php
	// Homepage pagination.
	if ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
		$paged = get_query_var( 'page' );
	} else {
		$paged = 1;
	}

		$args = array(
			'post_type' 	=> 'post',
			'post_status' 	=> 'publish',
			'orderby' 		=> 'date',
			'order' 		=> 'DESC',
			'paged' 		=> $paged,
		);

		if ( has_filter( 'um_theme_template_blog_args' ) ) {
			$args = apply_filters( 'um_theme_template_blog_args', $args );
		}

		$wp_query = null;
		$wp_query = new WP_Query();
		$wp_query->query( $args );

		if ( $wp_query->have_posts() ) : ?>

			<header class="page__header entry-header">
			<div class="single page-meta">
				<h1 class="entry-title">
					<?php the_title();?>
				</h1>
			</div>
			</header>

			<div class="website-canvas">
			<div class="boot-row">
				<div class="template-blog <?php um_determine_single_content_width();?> <?php um_theme_determine_sidebar_position();?>">
				<div class="boot-row">
				<?php
				/* Start the Loop */
				while ( have_posts() ) : the_post(); ?>
						<?php if ( $defaults['um_theme_blog_posts_layout'] === 1 ) : ?>

							<?php if ( $wp_query->current_post === 0 ) : ?>
								<article id="post-<?php the_ID();?>" <?php post_class( 'boot-col-sm-6' );?>>
								<div class="blog-post-container blog-post-one boot-text-center equal-height">
									<div class="blog-post-image">
										<a href="<?php the_permalink();?>">
										<?php the_post_thumbnail( 'um-theme-thumb' );?>
										</a>
									</div>
									<div class="blog-post-title entry-header boot-text-center">
										<h3 class="entry-title">
											<a href="<?php the_permalink();?>"><?php the_title();?></a>
										</h3>
									</div>
									<?php um_theme_category(); ?>
									<div class="entry-excerpt"><?php the_excerpt();?></div>
									<p class="more-link-wrap">
										<a href="<?php the_permalink();?>"><?php esc_html_e( 'Continue Reading', 'um-theme' );?></a>
									</p>
								</div>
								</article>
							<?php elseif ( $wp_query->current_post === 1 ) : ?>
								<article id="post-<?php the_ID();?>" <?php post_class( 'boot-col-sm-6' );?>>
								<div class="blog-post-container blog-post-one boot-text-center equal-height">
									<div class="blog-post-image">
										<a href="<?php the_permalink();?>">
										<?php the_post_thumbnail( 'um-theme-thumb' );?>
										</a>
									</div>
									<div class="blog-post-title entry-header boot-text-center">
										<h3 class="entry-title">
											<a href="<?php the_permalink();?>"><?php the_title();?></a>
										</h3>
									</div>
									<?php um_theme_category(); ?>
									<div class="entry-excerpt"><?php the_excerpt();?></div>
									<p class="more-link-wrap">
										<a href="<?php the_permalink();?>"><?php esc_html_e( 'Continue Reading', 'um-theme' );?></a>
									</p>
								</div>
								</article>
							<?php else : ?>
								<article id="post-<?php the_ID();?>" <?php post_class( 'boot-col-sm-4' );?>>
								<div class="blog-post-container blog-post-one-alt boot-text-center equal-height">
									<div class="blog-post-image">
										<a href="<?php the_permalink();?>">
										<?php the_post_thumbnail( 'um-theme-thumb' );?>
										</a>
									</div>
									<div class="blog-post-title entry-header boot-text-center">
										<h4 class="entry-title">
											<a href="<?php the_permalink();?>"><?php the_title();?></a>
										</h4>
									</div>
								</div>
								</article>
							<?php endif; ?>

						<?php else : ?>

							<article id="post-<?php the_ID();?>" <?php post_class( 'boot-col-sm-12' );?>>
							<header class="entry-header">
								<h3 class="entry-title">
									<a href="<?php the_permalink();?>"><?php the_title();?></a>
								</h3>
							</header>

								<?php um_published_on();?>
								<span class="meta"><?php esc_html_e( 'by', 'um-theme' );?></span>
								<?php um_post_author();?>
								- <span class="meta">
									<a href="<?php comments_link(); ?>">
										<?php comments_number( 'Leave a comment', '1 Response', '% Responses' ); ?>
									</a>
								</span>

								<div class="featured-image">
									<?php the_post_thumbnail( 'um-theme-thumb' );?>
								</div>
								<div class="excerpt">
									<?php the_excerpt();?>
								</div>
							</article>

						<?php endif;?>

					<?php
					endwhile;
					um_theme_pagination();
					else :
						get_template_part( 'template-parts/content', 'none' );
					endif;
					wp_reset_postdata();
				?>
				</div>
				</div>
				<?php get_sidebar( 'sidebar-page' );?>
			</div>
			</div>

	</div><!-- #primary -->
</main><!-- #main -->
<?php
get_footer();
