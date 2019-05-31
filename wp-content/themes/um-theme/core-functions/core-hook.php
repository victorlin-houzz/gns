<?php
/**
 * Theme Hooks
 *
 * @package     um-theme
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

global $defaults;
$defaults = um_theme_option_defaults();

/**
 * Header
 *
 * @see  um_theme_header_skip_links()
 * @see  um_theme_header_custom_background()
 * @see  um_theme_core_header()
 * @see  um_theme_header_topbar()
 * @see  um_theme_header_bottombar()
 * @see  um_add_bottom_menu_clicker()
 */
add_action( 'um_theme_header', 'um_theme_header_skip_links', 10 );
add_action( 'um_theme_header', 'um_theme_header_custom_background', 20 );
add_action( 'um_theme_header', 'um_theme_core_header', 30 );
add_action( 'um_theme_before_header', 'um_theme_header_topbar' );
add_action( 'um_theme_after_header', 'um_theme_header_bottombar' );
add_filter( 'wp_nav_menu_items', 'um_add_bottom_menu_clicker', 10, 2 );
add_action( 'after_setup_theme', 'um_theme_custom_header_setup' );

/**
 * Pages
 *
 * @see  um_theme_output_page_comments()
 */
add_action( 'um_theme_page', 'um_theme_output_page_header', 10 );
add_action( 'um_theme_page', 'um_theme_output_page_content', 20 );
add_action( 'um_theme_after_page_content', 'um_theme_output_page_comments', 10 );

/**
 * Loop
 *
 * @see  um_theme_pagination()
 */
add_action( 'um_theme_loop_after', 'um_theme_pagination' );

/**
 * Single Article
 *
 * @see  um_theme_single_post_header()
 * @see  um_theme_single_post_featured_image()
 * @see  um_theme_single_post_content()
 * @see  um_theme_single_post_additional_content()
 */
add_action( 'um_theme_single_post', 'um_theme_single_post_header', 10 );
add_action( 'init', 'um_theme_single_post_featured_image' );
add_action( 'um_theme_single_post', 'um_theme_single_post_content', 30 );
add_action( 'um_theme_single_post_bottom', 'um_theme_single_post_additional_content', 10 );
add_filter( 'wp_get_attachment_image_attributes','um_theme_add_post_featured_image_alt_text', 10, 2 );
/**
 * Customizer
 *
 * @see  um_theme_customize_preview_js()
 * @see  um_theme_customize_register()
 * @see  um_theme_native_customizer_style()
 */
add_action( 'customize_preview_init', 'um_theme_customize_preview_js' );
add_action( 'customize_register', 'um_theme_customize_register' );
add_action( 'customize_controls_enqueue_scripts', 'um_theme_native_customizer_style' );
add_action( 'wp_enqueue_scripts', 'um_theme_customizer_head_styles', 1000 );


// Initialize UM theme.
add_action( 'after_setup_theme', 'um_theme_setup' );

// Remove unncessary functions.
add_action( 'after_setup_theme', 'um_theme_performance_enhancer' );

// Theme updater.
add_action( 'after_setup_theme', 'um_theme_update_theme_license' );

// Register widgets.
add_action( 'widgets_init', 'um_theme_widgets_init' );

// Resource hints for Google Fonts.
add_filter( 'wp_resource_hints', 'um_theme_resource_hints', 10, 2 );

// Add class to next post div.
add_filter( 'next_posts_link_attributes', 'um_theme_next_page_add_class' );

// Add class to previous post div.
add_filter( 'previous_posts_link_attributes', 'um_theme_previous_page_add_class' );

// Add class to body html element.
add_filter( 'body_class', 'um_theme_body_classes' );

// Remove widgets dashboard.
add_action( 'wp_dashboard_setup', 'um_theme_admin_remove_dashboard_widgets' );

// Remove Welcome Panel from Dashboard.
remove_action( 'welcome_panel', 'wp_welcome_panel' );

// Enqueue Theme javaScript files.
add_action( 'wp_enqueue_scripts', 'um_theme_scripts' );

// Change Default Comment Login link to UM Login Page.
add_filter( 'comment_form_defaults', 'um_theme_change_login_link' );

// Change the Leave a Reply title.
add_filter( 'comment_form_defaults', 'um_theme_comment_reply_title' );


// Remove version number from url.
add_filter( 'script_loader_src', 'um_theme_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', 'um_theme_remove_script_version', 15, 1 );

// Output JavaScript Code from Customizer.
add_action( 'wp_head', 'um_theme_output_javascript_code' );
add_action( 'wp_head', 'um_theme_output_head_code' );
add_action( 'um_theme_before_site', 'um_theme_output_header_code' );
add_action( 'wp_footer', 'um_theme_output_footer_code' );

add_action( 'create_category', 'um_theme_has_active_categories_reset' );
add_action( 'edit_category',   'um_theme_has_active_categories_reset' );
add_action( 'delete_category', 'um_theme_has_active_categories_reset' );
add_action( 'save_post',       'um_theme_has_active_categories_reset' );

// Content Archive.
add_action( 'um_theme_content_archive_header', 'um_theme_archive_content_title', 10 );
add_action( 'um_theme_content_archive_header', 'um_theme_archive_content_description', 12 );

// Sticky Sidebar.
add_action( 'um_theme_before_sidebar', 'um_theme_sticky_sidebar_opening' );
add_action( 'um_theme_after_sidebar', 'um_theme_sticky_sidebar_closing' );

// Add rel="nofollow" and remove rel="tag".
add_filter( 'wp_tag_cloud', 'um_theme_modify_tag_rel' );
add_filter( 'the_tags', 'um_theme_modify_tag_rel' );

// Add rel="nofollow" and remove rel="category".
add_filter( 'wp_list_categories', 'um_theme_modify_category_rel' );
add_filter( 'the_category', 'um_theme_modify_category_rel' );

/**
 * Footer
 *
 * @see  um_theme_footer_widgets()
 * @see  um_theme_footer_bottom_content()
 * @see  um_theme_scroll_to_top()
 */
add_action( 'um_theme_footer', 'um_theme_footer_widgets', 10 );
add_action( 'um_theme_footer', 'um_theme_footer_bottom_content', 20 );
add_action( 'wp_footer', 'um_theme_scroll_to_top' );
