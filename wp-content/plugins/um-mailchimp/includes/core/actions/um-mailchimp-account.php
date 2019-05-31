<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Call subscription process on registration when status set to 'approved'
 * */
function um_mailchimp_after_user_status_is_changed( $status ) {
	if( $status != 'approved' ) return;
	$user_id = um_user('ID');
	$user_lists = get_user_meta( $user_id, 'um-mailchimp', true );

	if( !is_array( $user_lists ) ) return;
	UM()->Mailchimp_API()->api()->user_id = $user_id;
	foreach( $user_lists as $list_id=>$value ) {
		$list = UM()->Mailchimp_API()->api()->get_list_by_mailchimp_id( $list_id );
		UM()->Mailchimp_API()->api()->update( $list['id'], $list['merge_vars'] );
	}
	delete_user_meta( $user_id, 'um-mailchimp' );
}
add_action('um_after_user_status_is_changed', 'um_mailchimp_after_user_status_is_changed' );

/**
 * Call update subscriber information on profile update
 */
function um_mailchimp_user_after_updating_profile( $user_id, $old_user_data ) {
	um_fetch_user( $user_id );
	$user_lists = UM()->Mailchimp_API()->api()->get_user_lists( $user_id );
	UM()->Mailchimp_API()->api()->user_id = $user_id;
	foreach( $user_lists as $list ) {
		UM()->Mailchimp_API()->api()->update( $list['id'], $list['merge_vars'] );
	}


}
add_action('profile_update', 'um_mailchimp_user_after_updating_profile', 10, 2 );

/**
 * This action will be executed when user self register from frontend. We use this hook instead of 'user_register'
 * because we need subscribe only approved users.
 *
 * @param $user_id
 */
function um_mailchimp_after_user_role_is_updated( $user_id ){
	if( is_wp_error( $user_id ) ) return;
	$user_lists = UM()->Mailchimp_API()->api()->get_user_lists( $user_id );
	UM()->Mailchimp_API()->api()->user_id = $user_id;
	foreach( $user_lists as $list ) {
		UM()->Mailchimp_API()->api()->update( $list['id'], $list['merge_vars'] );
	}

}
add_action('um_after_user_role_is_updated','um_mailchimp_after_user_role_is_updated' );

/**
 * This action will be executed when someone registers user from wp-admin area. It subscribes created user to all lists
 * with option 'auto_register'
 *
 * @param $user_id
 */
function um_mailchimp_user_register( $user_id ) {
	if( !is_admin() || defined('DOING_AJAX') ) return;
	$lists = UM()->Mailchimp_API()->api()->get_all_lists();
	UM()->Mailchimp_API()->api()->user_id = $user_id;
	foreach( $lists as $list ) {
		if( !$list['auto_register'] ) continue;
		UM()->Mailchimp_API()->api()->subscribe( $list['id'], $list['merge_vars'] );
	}
}
add_action('user_register','um_mailchimp_user_register' );

	
	/***
	***	@hook in account update to subscribe/unsubscribe users
	***/
add_action('um_post_account_update', 'um_mailchimp_account_update');
function um_mailchimp_account_update() {
	$user_id = um_user('ID');

	if( um_user('account_status') != 'approved' ) return;

	$user_lists = UM()->Mailchimp_API()->api()->get_user_lists( $user_id );

	if( UM()->options()->get('account_tab_notifications') ) {
		$new_lists = isset ( $_POST['um-mailchimp'] ) ? array_keys( $_POST['um-mailchimp'] ) : array();
		$old_lists = array_keys( $user_lists );
		$need_subscribe = array_diff( $new_lists, $old_lists );
		$need_unsubscribe = array_diff( $old_lists, $new_lists );

		foreach( $need_subscribe as $list_key ) {
			$list = UM()->Mailchimp_API()->api()->get_list_by_mailchimp_id( $list_key );
			UM()->Mailchimp_API()->api()->subscribe( $list['id'], $list['merge_vars'] );
		}

		foreach( $need_unsubscribe as $list_key ) {
			UM()->Mailchimp_API()->api()->unsubscribe( $list_key );
		}

		$user_lists = array_keys( UM()->Mailchimp_API()->api()->get_user_lists( $user_id ) );
		$user_lists = array_diff( $user_lists, $need_subscribe, $need_unsubscribe );
	}

	foreach( $user_lists as $list ) {
		UM()->Mailchimp_API()->api()->update( $list['id'], $list['merge_vars'] );
	}
}


/**
 * Delete user from Mailchimp lists on delete process
 * @param $user_id
 */
function um_unsubscribe_user( $user_id ) {
	$user_lists = get_user_meta( $user_id, '_mylists', true );
	foreach ( $user_lists as $list_id=>$val ) {
		UM()->Mailchimp_API()->api()->user_id = $user_id;
		UM()->Mailchimp_API()->api()->unsubscribe( $list_id );
	}
}
add_action( 'um_delete_user', 'um_unsubscribe_user' );