/**
 * customizer.js
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */
(function ($) {

	var style = $( '.site-title a' ),
		accent_color_background = $( '.post-meta__tag__item a' ),
		accent_color_text 		= $( '.single.post-meta,.single.post-meta a,.comment-reply-link,.comment__author,.post-meta__author,.comment-edit-link' ),
		menu_color 				= $( '.outer-section-profile-container .inner-section-profile-container a,.um-header-avatar-name a,#bs-navbar-profile a,#bs-navbar-primary a,#bs-navbar-primary' ),
		button_color_background = $( '.comment-form input[type=submit],.site-search form input[type=submit]' ),
		button_color_text 		= $( '.comment-form input[type=submit]' );

	// Site title
	wp.customize('blogname', function (value) {
		value.bind( function (to) {
			$( '.site-title a' ).text( to );
		} );
	} );

	// Site Description
	wp.customize( 'blogdescription', function (value) {
		value.bind(function(to) {
			$( '.site-description' ).text( to );
		} );
	} );

	/*--------------------------------------------------------------
	## Color
	--------------------------------------------------------------*/

	// Header text color.
	wp.customize('header_textcolor', function (value) {
		value.bind( function(to){
			if ( 'blank' === to ) {
				$( '.site-title a, .site-description' ).css( {
					'clip': 'rect(1px, 1px, 1px, 1px)',
					'position': 'absolute'
				} );
			} else {
				$( '.site-title a, .site-description' ).css( {
					'clip': 'auto',
					'position': 'relative'
				} );
				$( '.site-title a, .site-description' ).css( {
					'color': to
				} );
			}
		} );
	} );

	// Header Background Color
	wp.customize( 'customization[header_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-header' ).css( 'background-color', newval );
		} );
	} );

	// Header Selected Menu Text Color
	wp.customize( 'customization[selected_menu_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '#bs-navbar-primary .current-menu-item a' ).css( 'color', newval );
		} );
	} );

	// Header Selected Menu Background Color
	wp.customize( 'customization[selected_menu_bg_color]', function( value ) {
		value.bind( function( newval ) {
			$( '#bs-navbar-primary .current-menu-item a' ).css( 'background-color', newval );
		} );
	} );

	// Widget Background Color
	wp.customize( 'customization[widgets_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( '#secondary .widget' ).css( 'background-color', newval );
		} );
	} );


	// Footer Background Color
	wp.customize( 'customization[footer_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-footer-layout-text' ).css( 'background-color', newval );
		} );
	} );

	// Footer Text Color
	wp.customize( 'customization[footer_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-footer-layout-text' ).css( 'color', newval );
		} );
	} );

	// Footer Link Color
	wp.customize( 'customization[footer_link_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-footer-layout-text a' ).css( 'color', newval );
		} );
	} );

	// Footer Link Hover Color
	wp.customize( 'customization[footer_link_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-footer-layout-text a:hover' ).css( 'color', newval );
		} );
	} );

	// Footer Menu Color
	wp.customize( 'customization[footer_menu_color]', function( value ) {
		value.bind( function( newval ) {
			$( '#bs-navbar-footer a,#bs-navbar-footer li' ).css( 'color', newval );
		} );
	} );

	// Footer Widget Background
	wp.customize( 'customization[um_theme_footer_widget_bg_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-footer-layout-sidebar' ).css( 'background-color', newval );
		} );
	} );

	// Site Body Text Color
	wp.customize( 'customization[body_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).css( 'color', newval );
		} );
	} );

	// Menu Text Color
	wp.customize( 'customization[menu_text_color]', function( value ) {
		value.bind( function( newval ) {
			menu_color.css( 'color', newval );
		} );
	} );

	// Other Color
	wp.customize( 'customization[um_theme_primary_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.scrollToTop' ).css( 'color', newval );
		} );
	} );

	// Website Meta Color Background
	wp.customize( 'customization[um_theme_website_meta_color]', function( value ) {
		value.bind( function( newval ) {
			accent_color_background.css( 'background-color', newval );
		} );
	} );

	// Website Meta Color
	wp.customize( 'customization[um_theme_website_meta_color]', function( value ) {
		value.bind( function( newval ) {
			accent_color_text.css( 'color', newval );
		} );
	} );

	// Button Background Color
	wp.customize( 'customization[button_background_color]', function( value ) {
		value.bind( function( newval ) {
			button_color_background.css( 'background-color', newval );
		} );
	} );

	// Button Text Color
	wp.customize( 'customization[button_text_color]', function( value ) {
		value.bind( function( newval ) {
			button_color_text.css( 'color', newval );
		} );
	} );
	// Link Text Color
	wp.customize( 'customization[link_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'a' ).css( 'color', newval );
		} );
	} );
	// Link Hover Color
	wp.customize( 'customization[link_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'a:hover' ).css( 'color', newval );
		} );
	} );

	// Topbar Background Color
	wp.customize( 'customization[header_topbar_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-top-bar' ).css( 'background-color', newval );
		} );
	} );

	// Topbar Text Color
	wp.customize( 'customization[header_topbar_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-header-topbar-text' ).css( 'color', newval );
		} );
	} );

	// Topbar Menu Color & Top Bar Link
	wp.customize( 'customization[header_topbar_menu_color]', function( value ) {
		value.bind( function( newval ) {
			$( '#header-top li a,.topbar-container a, #bs-navbar-topbar a, #bs-navbar-topbar' ).css( 'color', newval );
		} );
	} );

	// Topbar Menu Hover Color & Top Bar Link Hover Color
	wp.customize( 'customization[header_topbar_link_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.topbar-container a:hover, #bs-navbar-topbar a:hover' ).css( 'color', newval );
		} );
	} );

	// Social Icon Color
	wp.customize( 'customization[social_accounts_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-theme-social-link a' ).css( 'color', newval );
		} );
	} );

	// Social Icon Hover Color
	wp.customize( 'customization[social_accounts_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-theme-social-link a:hover' ).css( 'color', newval );
		} );
	} );

	// Bottom Bar Background Color
	wp.customize( 'customization[header_bottombar_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-bottom-bar' ).css( 'background-color', newval );
		} );
	} );

	// Bottom Bar Menu Color
	wp.customize( 'customization[header_bottombar_menu_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-bottom-bar, #bs-navbar-bottombar a, #bs-navbar-bottombar li' ).css( 'color', newval );
		} );
	} );

	// Bottom Bar Text Color
	wp.customize( 'customization[header_bottombar_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-header-bottom-text' ).css( 'color', newval );
		} );
	} );

	// Private Message Color
	wp.customize( 'customization[header_private_message_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-one-profile .um-notification-m' ).css( 'color', newval );
		} );
	} );

	// Private Message Hover Color
	wp.customize( 'customization[header_private_message_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-one-profile .um-notification-m a:hover' ).css( 'color', newval );
		} );
	} );

	// Notification Color
	wp.customize( 'customization[header_notification_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-one-profile .um-notification-b' ).css( 'color', newval );
		} );
	} );


	// Notification Hover Color
	wp.customize( 'customization[header_notification_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-one-profile .um-notification-b a:hover' ).css( 'color', newval );
		} );
	} );

	// Heading Text Color
	wp.customize( 'customization[title_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( 'h1,h2,h3,h4,h5,h6' ).css( 'color', newval );
		} );
	} );


	// Header Search Box Color
	wp.customize( 'customization[header_search_background_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-search #search-form-input' ).css( 'background-color', newval );
		} );
	} );

	// Header Search Text Color
	wp.customize( 'customization[header_search_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-search #search-form-input' ).css( 'color', newval );
		} );
	} );


	// Notification Bubble Background Color
	wp.customize( 'customization[header_notification_bubble_bg_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-notification-live-count' ).css( 'background-color', newval );
		} );
	} );

	// Notification Bubble Color
	wp.customize( 'customization[header_notification_bubble_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-notification-live-count' ).css( 'color', newval );
		} );
	} );

	// On Click Icon Color
	wp.customize( 'customization[header_bottombar_onclick_icon_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.bottom-t-m-ico' ).css( 'color', newval );
		} );
	} );

	// On Click Icon Hover Color
	wp.customize( 'customization[header_bottombar_onclick_icon_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.bottom-t-m-ico:hover' ).css( 'color', newval );
		} );
	} );

	// Profile Navigation Bar Color
	wp.customize( 'customization[um_theme_template_profile_nav_bar_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-profile-nav' ).css( 'background-color', newval );
		} );
	} );

	// Profile Navigation Menu Color
	wp.customize( 'customization[um_theme_template_profile_nav_menu_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-profile-nav-item a' ).css( 'color', newval );
		} );
	} );

	// Profile Navigation Menu Hover Color
	wp.customize( 'customization[um_theme_template_profile_nav_menu_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-profile-nav-item a:hover' ).css( 'color', newval );
		} );
	} );


	// Profile Content Area Color
	wp.customize( 'customization[um_theme_template_profile_content_area_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-profile .um-header' ).css( 'background-color', newval );
		} );
	} );

	// Profile Single Container Color
	wp.customize( 'customization[um_theme_template_profile_single_container_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-theme-profile-single-content-container' ).css( 'background-color', newval );
		} );
	} );

	// Profile Field Label Border Color
	wp.customize( 'customization[um_theme_template_profile_field_label_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-profile.um-viewing .um-field-label' ).css( 'border-color', newval );
		} );
	} );

	// Profile Sidebar Container Color
	wp.customize( 'customization[um_theme_template_profile_sidebar_container_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-theme-profile-single-sidebar-container' ).css( 'background-color', newval );
		} );
	} );


	// Footer Widget Color
	wp.customize( 'customization[um_theme_footer_widget_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.footer-sidebar-column-one,.footer-sidebar-column-two,.footer-sidebar-column-three,.footer-sidebar-column-four' ).css( 'color', newval );
		} );
	} );

	// Footer Widget Link Color
	wp.customize( 'customization[um_theme_footer_widget_link_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.footer-sidebar-column-one a,.footer-sidebar-column-two a,.footer-sidebar-column-three a,.footer-sidebar-column-four a' ).css( 'color', newval );
		} );
	} );

	// Footer Widget Link Hover Color
	wp.customize( 'customization[um_theme_footer_widget_link_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.footer-sidebar-column-one a:hover,.footer-sidebar-column-two a:hover,.footer-sidebar-column-three a:hover,.footer-sidebar-column-four a:hover' ).css( 'color', newval );
		} );
	} );


	// Box Background Color
	wp.customize( 'customization[um_theme_member_directory_box_bg_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-member' ).css( 'background-color', newval );
		} );
	} );

	// Box Text Color
	wp.customize( 'customization[um_theme_member_directory_box_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-member-name a' ).css( 'color', newval );
		} );
	} );


	// Search Placeholder Text Color
	wp.customize( 'customization[header_search_placeholder_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-inner .search-form ::placeholder' ).css( 'color', newval );
		} );
	} );


	// Content Background Color
	wp.customize( 'customization[um_customizer_post_content_bg_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.site-content' ).css( 'background-color', newval );
		} );
	} );

	// Post background Color
	wp.customize( 'customization[um_theme_blog_post_bg_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.template-blog .blog-post-container' ).css( 'background-color', newval );
		} );
	} );

	// Post Content Color
	wp.customize( 'customization[um_theme_blog_post_content_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.template-blog .excerpt,.template-blog .excerpt-list,.template-blog .blog-post-container' ).css( 'color', newval );
		} );
	} );

	// Post Title Color
	wp.customize( 'customization[um_theme_blog_post_title_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.template-blog .entry-title a' ).css( 'color', newval );
		} );
	} );

	// Header Buttton 1 Color
	wp.customize( 'customization[header_login_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-button-1' ).css( 'color', newval );
		} );
	} );


	// Header Buttton 1 Background Color
	wp.customize( 'customization[header_login_button_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-button-1' ).css( 'background-color', newval );
		} );
	} );

	// Header Buttton 2 Color
	wp.customize( 'customization[header_register_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-button-2' ).css( 'color', newval );
		} );
	} );

	// Header Buttton 2 Background Color
	wp.customize( 'customization[header_log_button_two_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.header-button-2' ).css( 'background-color', newval );
		} );
	} );

	// Article Box Color
	wp.customize( 'customization[um_post_single_box_bg]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-article-content' ).css( 'background-color', newval );
		} );
	} );

	// Article Box Text Color
	wp.customize( 'customization[um_post_single_box_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.single-article-content' ).css( 'color', newval );
		} );
	} );


	// Message Button Text Color
	wp.customize( 'customization[um_theme_ext_pm_message_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-message-item.left_m .um-message-item-content' ).css( 'color', newval );
		} );
	} );

	// Message Button Color
	wp.customize( 'customization[um_theme_ext_pm_message_bg_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-message-item.left_m .um-message-item-content' ).css( 'background-color', newval );
		} );
	} );


	// Message Button Text Color
	wp.customize( 'customization[um_theme_ext_pm_message_button_text_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-message-send' ).css( 'color', newval );
		} );
	} );

	// Message Button Color
	wp.customize( 'customization[um_theme_ext_pm_message_button_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-message-send' ).css( 'background-color', newval );
		} );
	} );

	/*--------------------------------------------------------------
	## Font Size
	--------------------------------------------------------------*/

	// Body Font Size
	wp.customize( 'customization[um_theme_body_font_size]', function( value ) {
		value.bind( function( newval ) {
			$( 'body' ).css( 'font-size', newval );
		} );
	} );

	// Menu Font Size
	wp.customize( 'customization[um_theme_menu_font_size]', function( value ) {
		value.bind( function( newval ) {
			$( '.menu-item a,.page-numbers a,.page-numbers span' ).css( 'font-size', newval );
		} );
	} );

	// Title Alignment Widget
	wp.customize( 'customization[um_theme_widget_title_alignment]', function( value ) {
		value.bind( function( newval ) {
			$( '.widget-title' ).css( 'text-align', newval );
		} );
	} );

	/*--------------------------------------------------------------
	## Layout & Positioning
	--------------------------------------------------------------*/

	 // Site Width
     wp.customize( 'customization[um_theme_canvas_width]', function( value ) {
        value.bind( function( to ) {
            $( '.website-canvas' ).css( 'max-width', newval );
        } );
    });

	// Menu Position
	wp.customize( 'customization[um_theme_menu_position]', function( value ) {
		value.bind( function( newval ) {
			$( '.nav-menu' ).css( 'text-align', newval );
		} );
	} );

	/*--------------------------------------------------------------
	## WooCommerce
	--------------------------------------------------------------*/
	// WooCommerce Price Color
	wp.customize( 'customization[um_theme_woocommerce_price_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.woocommerce div.product p.price,.woocommerce div.product span.price,.woocommerce ul.products li.product .price' ).css( 'color', newval );
		} );
	} );


	// WooCommerce Product Title Color
	wp.customize( 'customization[um_theme_woocommerce_product_title_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.woocommerce div.product .product_title,.woocommerce-loop-product__title' ).css( 'color', newval );
		} );
	} );

	// Add To Cart Button
	wp.customize( 'customization[um_theme_woocommerce_add_cart_button_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.woocommerce #respond input#submit.alt,.woocommerce a.button.alt,.woocommerce button.button.alt,.woocommerce input.button.alt,.woocommerce #respond input#submit,.woocommerce a.button,.woocommerce button.button,.woocommerce input.button' ).css('background-color', newval );
		} );
	} );

	// Add To Cart Button Text
	wp.customize( 'customization[um_theme_woocommerce_add_cart_button_text]', function( value ) {
		value.bind( function( newval ) {
			$( '.woocommerce #respond input#submit.alt,.woocommerce a.button.alt,.woocommerce button.button.alt,.woocommerce input.button.alt,.woocommerce #respond input#submit,.woocommerce a.button,.woocommerce button.button,.woocommerce input.button' ).css('color', newval );
		} );
	} );

	// Sale Badge
	wp.customize( 'customization[um_theme_woocommerce_sale_badge_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.woocommerce span.onsale' ).css( 'background-color', newval );
		} );
	} );

	// Sale Badge Text
	wp.customize( 'customization[um_theme_woocommerce_sale_badge_text]', function( value ) {
		value.bind( function( newval ) {
			$( '.woocommerce span.onsale' ).css( 'color', newval );
		} );
	} );

	// Friend Requests Icon Color
	wp.customize( 'customization[header_friend_req_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-friend-tick' ).css( 'color', newval );
		} );
	} );

	// Friend Requests Icon Hover Color
	wp.customize( 'customization[header_friend_req_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-friend-tick:hover' ).css( 'color', newval );
		} );
	} );

	// Notification Icon Color
	wp.customize( 'customization[header_notification_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-notification-b,.um-notification-ico' ).css( 'color', newval );
		} );
	} );

	// Notification Icon Hover Color
	wp.customize( 'customization[header_notification_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-notification-ico:hover' ).css( 'color', newval );
		} );
	} );

	// Message Icon Color
	wp.customize( 'customization[header_private_message_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-msg-tik-ico' ).css( 'color', newval );
		} );
	} );

	// Message Icon Hover Color
	wp.customize( 'customization[header_private_message_hover_color]', function( value ) {
		value.bind( function( newval ) {
			$( '.um-msg-tik-ico:hover' ).css( 'color', newval );
		} );
	} );

} )( jQuery );