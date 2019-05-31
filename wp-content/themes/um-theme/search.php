<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package um-theme
 */
get_header(); ?>
	<main id="primary" class="content-area" tabindex="-1">
		<section id="main" class="site-main">
		<div class="website-canvas">
		<div class="boot-row">
		<div id="primary" class="content-area single-page__content <?php um_determine_single_content_width();?>">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<!-- Title for search results containing search term -->
				<h1 class="page-title">
					<?php
					/* translators: %s: search query. */
					printf( esc_html__( 'Search Results for: %s', 'um-theme' ), '<span class="search-term">' . get_search_query() . '</span>' );
					?>
				</h1>
			</header><!-- .page-header -->

			<ol class="boot-row search-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<li class="boot-col-sm-12">
					<article id="post-<?php the_ID();?>" <?php post_class();?>>
						<header class="entry-header">
							<h3 class="entry-title"><a href="<?php the_permalink();?>"><?php the_title();?></a></h3>
						</header>

						<?php um_published_on();?>
						<span class="meta"><?php esc_html_e( 'by', 'um-theme' );?></span>
						<?php um_post_author();?>
						<div class="featured-image"><?php the_post_thumbnail( 'um-theme-thumb' );?></div>
						<div class="excerpt"><?php the_excerpt();?></div>
					</article>
					</li>
				<?php endwhile; ?>
			</ol>

			<?php
				um_theme_pagination();
		 else :
				get_template_part( 'template-parts/content', 'none' );
		 endif;
		?>
		</div>
		<?php get_sidebar( 'sidebar-page' ); ?>
		</div>
		</div>
		</section>
	</main>
<?php get_footer(); ?>
