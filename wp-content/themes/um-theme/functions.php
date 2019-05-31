<?php
/**
 * UM Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package um-theme
 * @author  Ultimate Member
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU Public License
 */

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */

/**
 * Define Constants
 */
define( 'UM_THEME_VERSION', '1.12' );
define( 'UM_THEME_DIR', get_template_directory() . '/' );
define( 'UM_THEME_URI', get_template_directory_uri() . '/' );

if ( ! function_exists( 'um_theme_setup' ) ) {
	function um_theme_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on UM Theme, use a find and replace
		 * to change 'um-theme' to the name of your theme in all the template files
		 */
		load_theme_textdomain( 'um-theme' );

		/*
	     * Add default posts and comments RSS feed links to head.
	     */
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'um-theme-thumb', 820, 400, array( 'center', 'center' ) );
		set_post_thumbnail_size( 'um-theme-thumb', 820, 400 );

		/*
		 * Enable support responsive embedded content
		 * See: https://wordpress.org/gutenberg/handbook/extensibility/theme-support/#responsive-embedded-content
		 */
		add_theme_support( 'responsive-embeds' );

		// Add support for default block styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Adds support for editor color palette.
		add_theme_support( 'editor-color-palette', array(
		    array(
		        'name' 	=> __( 'sky blue', 'um-theme' ),
		        'slug' 	=> 'strong-magenta',
		        'color' => '#6596ff',
		    ),
		    array(
		        'name' 	=> __( 'light grayish magenta', 'um-theme' ),
		        'slug' 	=> 'light-grayish-magenta',
		        'color' => '#333333',
		    ),
		    array(
		        'name' 	=> __( 'very light gray', 'um-theme' ),
		        'slug' 	=> 'very-light-gray',
		        'color' => '#eeeeee',
		    ),
		    array(
		        'name' 	=> __( 'very dark gray', 'um-theme' ),
		        'slug' 	=> 'very-dark-gray',
		        'color' => '#444444',
		    ),
		) );

		/**
		 * Register custom Custom Navigation Menus.
		 * This theme uses wp_nav_menu() in the following locations.
		 *
		 * @link  https://developer.wordpress.org/reference/functions/register_nav_menus/
		 * @since 1.0.0
		 */
		register_nav_menus(
			/**
			 * Filter registered nav menus.
			 *
			 * @since 1.0.0
			 *
			 * @var array
			 */
			(array) apply_filters( 'um_theme_nav_menus',
				array(
					'header-top' 		=> esc_html__( 'Top Bar Menu', 'um-theme' ),
					'primary' 			=> esc_html__( 'Primary Menu', 'um-theme' ),
					'header-bottom' 	=> esc_html__( 'Bottom Bar Menu', 'um-theme' ),
					'profile-menu' 		=> esc_html__( 'User Header Menu', 'um-theme' ),
					'footer' 			=> esc_html__( 'Footer Menu', 'um-theme' ),
				)
			)
		);

		// Add theme support for Custom Logo.
		add_theme_support( 'custom-logo' );

		/*
		 * Switch default core yorkup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

		add_theme_support( 'widget-customizer' );

	    /*
	     * Enable support for Customizer Selective Refresh.
	     * See: https://make.wordpress.org/core/2016/02/16/selective-refresh-in-the-customizer/
	     */
		add_theme_support( 'customize-selective-refresh-widgets' );

		add_theme_support( 'custom-background', apply_filters( 'um_custom_background_args', array(
			'default-color' => '#f6f9fc',
			'default-image' => '',
		) ) );

		// Set the default content width.
		$GLOBALS['content_width'] = apply_filters( 'um_content_width', 640 );

		add_editor_style();
	}
}

/**
 * Enqueue scripts and styles.
 */
if ( ! function_exists( 'um_theme_scripts' ) ) {
	function um_theme_scripts() {

		// Enqueue Helpers Libraries.
		wp_enqueue_style( 'third-party-libraries', UM_THEME_URI . 'inc/css/libraries.min.css' );

		// Enqueue Theme Stylesheet.
		wp_enqueue_style( 'um-stylesheet', get_stylesheet_uri(), false, UM_THEME_VERSION , 'all' );

		// Enqueue Google Fonts
        wp_enqueue_style( 'um-theme-fonts', um_theme_fonts_url(), array(), UM_THEME_VERSION, 'all' );

        // Enqueue Theme JavaScript.
	  	wp_enqueue_script( 'um-js', UM_THEME_URI . 'inc/js/um-theme-app-min.js', array( 'jquery' ), UM_THEME_VERSION, true );

	   // Load the html5 shiv.
		wp_enqueue_script( 'um-html5', UM_THEME_URI . 'inc/js/html5.js', array(), '3.7.3' );
		wp_script_add_data( 'um-html5', 'conditional', 'lt IE 9' );

		// Enqueue Comments Script
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// RTL Support
		if ( is_rtl() ) {
			wp_enqueue_style( 'um-rtl-stylesheet', UM_THEME_URI . 'inc/css/rtl.css' );
		}

		// Enqueue bbPress stylesheet if active.
		if ( class_exists( 'bbPress' ) ) {
			wp_enqueue_style( 'um-bbpress', UM_THEME_URI . 'inc/css/um-theme-bbpress.css' );
		}
	}
}

/**
 * Multilingual Support
 *
 * @since 1.0.4
 */
require_once UM_THEME_DIR . 'core-functions/core-multilingual.php';

/**
 * Load Customizer Settings & Defaults
 *
 * @since 0.50
 */
require_once UM_THEME_DIR . 'inc/customizer.php';
require_once UM_THEME_DIR . 'core-functions/core-default.php';
require_once UM_THEME_DIR . 'core-functions/core-customizer-utility.php';
require_once UM_THEME_DIR . 'core-functions/core-customizer-css.php';
require_once UM_THEME_DIR . 'core-functions/core-widgets.php';
/**
 * Load Hooks
 *
 * @since 0.50
 */
require_once UM_THEME_DIR . 'core-functions/core-hook.php';

/**
 * Load Helper Fuctions
 *
 * @since 0.50
 */
require_once UM_THEME_DIR . 'core-functions/core-helpers.php';

/**
 * Load custom Menu Walker
 *
 * @since 0.50
 */
require_once UM_THEME_DIR . 'core-functions/core-menu-walker.php';

/**
 * Load Sidebar Components
 *
 * @since 0.50
 */
require_once UM_THEME_DIR . 'core-functions/core-sidebar.php';

/**
 * Load Header Components
 *
 * @since 0.50
 */
require_once UM_THEME_DIR . 'core-functions/core-header.php';
require_once UM_THEME_DIR . 'inc/custom-header.php';

/**
 * Load Comment Components
 *
 * @since 0.50
 */
require_once UM_THEME_DIR . 'core-functions/core-comment.php';

/**
 * Load WooCommerce compatibility file.
 *
 * @since 0.50
 */

if ( class_exists( 'WooCommerce' ) ) {
	require_once UM_THEME_DIR . 'core-functions/core-woocommerce.php';
}


/**
 * Load Ultimate Member compatibility file.
 *
 * @since 0.50
 */

if ( class_exists( 'UM' ) ) {
	require_once UM_THEME_DIR . 'core-functions/core-ultimate-member.php';
}

/**
 * Load bbPress compatibility file.
 *
 * @since 0.50
 */

if ( class_exists( 'bbPress' ) ) {
	require_once UM_THEME_DIR . 'core-functions/core-bbpress.php';
}

/**
 * Load Elementor Page Builder compatibility file.
 *
 * @since 0.50
 */

if ( did_action( 'elementor/loaded' ) ) {
	require_once UM_THEME_DIR . 'core-functions/core-elementor.php';
}

/**
 * Load Beaver Builder Themer compatibility file.
 *
 * @since 0.50
 */

if ( ! class_exists( 'FLThemeBuilderLoader' ) || ! class_exists( 'FLThemeBuilderLayoutData' ) ) {
	require_once UM_THEME_DIR . 'core-functions/core-beaver-themer.php';
}


/**
 * Load Liftr LMS compatibility file.
 *
 * @since 0.50
 */
if ( class_exists( 'LifterLMS' ) ) {
	require_once UM_THEME_DIR . 'core-functions/core-lifter-lms.php';
}

/**
 * Remote theme update.
 *
 * @since 0.50
 */
if ( ! function_exists( 'um_theme_update_theme_license' ) ) {
	function um_theme_update_theme_license() {
		if ( ! class_exists( 'EDD_Theme_Updater' ) ) {
			require_once UM_THEME_DIR . 'updater/theme-updater.php';
		}
	}
}

/**
 * Load Liftr WP Job Manager Compatibility
 *
 * @since 1.12
 */
if ( class_exists( 'WP_Job_Manager' ) ) {
	require_once UM_THEME_DIR . 'core-functions/core-job-manager.php';
}