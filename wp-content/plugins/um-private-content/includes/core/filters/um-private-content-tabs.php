<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/***
 ***	@adds a main tab to display forum activity in profile
 ***/
add_filter( 'um_profile_tabs', 'um_private_content_add_tab', 1000, 1 );
function um_private_content_add_tab( $tabs ) {

    $tab_title = UM()->options()->get('tab_private_content_title');
    $tab_title = ! empty( $tab_title ) ? $tab_title : __( 'Private Content', 'um-private-content' );

    $tab_icon = UM()->options()->get('tab_private_content_icon');
    $tab_icon = ! empty( $tab_icon ) ? $tab_icon : 'um-faicon-eye-slash';

    $tabs['private_content'] = array(
        'name' => $tab_title,
        'icon' => $tab_icon,
        'custom' => true
    );

    return $tabs;

}

/***
 ***	@add tabs based on user
 ***/
add_filter( 'um_user_profile_tabs', 'um_private_content_user_add_tab', 1000, 1 );
function um_private_content_user_add_tab( $tabs ) {
	if ( ( um_is_core_page( 'user' ) && ! um_is_myprofile() ) || ! UM()->options()->get( 'show_private_content_on_profile' ) ) {
		unset( $tabs['private_content'] );
		return $tabs;
    }

    //um_fetch_user( get_current_user_id() );

    $private_post_id = get_user_meta( um_user( 'ID' ), '_um_private_content_post_id', true );

    $post = get_post( $private_post_id );

    if ( empty( $post ) || empty( $post->post_content ) ) {
		unset( $tabs['private_content'] );
    }

    return $tabs;
}


/***
 ***	@default reviews tab
 ***/
add_action( 'um_profile_content_private_content', 'um_profile_content_private_content' );
function um_profile_content_private_content( $args ) {

    //um_fetch_user( get_current_user_id() );

    $private_post_id = get_user_meta( um_user( 'ID' ), '_um_private_content_post_id', true );

    $post = get_post( $private_post_id );
    if ( ! empty( $post ) ) {
        setup_postdata( $post );
        the_content();
        wp_reset_postdata();
    }
}