<?php
/**
 *
 * Template name: No Sidebar
 *
 * @package um-theme
 */
global $defaults;
get_header();?>

<div id="main" class="site-main">
<?php do_action( 'um_theme_before_page_content' );?>
<?php while ( have_posts() ) : the_post(); ?>

<!-- Page header -->
<header class="boot-col-md-12 page__header entry-header">
	<div class="single page-meta">
		<h1 class="entry-title">
			<?php the_title();?>
		</h1>
	</div>

</header>

<div class="boot-col-md-12 page-content">
<div class="website-canvas">
<div class="boot-row">

	<main id="primary" class="content-area single-page__content boot-col-md-12" tabindex="-1">
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="container-card-content">
			<div class="entry-content">
				<?php the_content(); ?>
				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'um-theme' ),
						'after'  => '</div>',
					) );
				?>
			</div>
		</div>
		</div>
		<?php
		if ( $defaults['um_theme_show_site_comments'] === true ) {
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		}
		?>
	</main>

	<?php endwhile; ?>
</div>
</div>
</div>
</div>

<?php
get_footer();
