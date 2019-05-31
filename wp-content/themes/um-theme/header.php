<?php
global $defaults;
$defaults = um_theme_option_defaults();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="referrer" content="no-referrer-when-downgrade" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php
	if ( function_exists( 'wp_body_open' ) ) {
		wp_body_open();
	}
	?>

<?php do_action( 'um_theme_before_site' ); ?>

<div id="page" class="hfeed site website-width">

<?php do_action( 'um_theme_before_header' );?>


	<header id="masthead" class="custom-header site-header <?php um_theme_output_header_sticky_class();?>" role="banner">

		<?php
		// Elementor `header` location
		if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
			/**
			 * Functions hooked into um_theme_header action
			 *
			 * @hooked um_theme_header_skip_links          - 10
			 * @hooked um_theme_header_custom_background   - 20
			 * @hooked um_theme_core_header                - 30
			 */
			 do_action( 'um_theme_header' );
		}
		?>

	</header>
	<?php do_action( 'um_theme_after_header' ); ?>

<div class="boot-row">
	<div class="boot-col-md-12">

	<?php do_action( 'um_theme_before_content' ); ?>

	<div id="content" class="site-content">
		<?php do_action( 'um_theme_content_top' );
