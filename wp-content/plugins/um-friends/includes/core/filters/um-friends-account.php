<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add Notifications tab to account page
 *
 * @param array $tabs
 * @return array
 */
function um_friends_account_notification_tab( $tabs ) {

	if ( empty( $tabs[400]['notifications'] ) ) {
		$tabs[400]['notifications'] = array(
			'icon'          => 'um-faicon-envelope',
			'title'         => __( 'Notifications', 'um-friends' ),
			'submit_title'  => __( 'Update Notifications', 'um-friends' ),
		);
	}

	return $tabs;
}
add_filter( 'um_account_page_default_tabs_hook', 'um_friends_account_notification_tab', 10, 1 );


/**
 * Show friends notifications in account tab
 *
 * @param $output
 * @param $shortcode_args
 * @return string
 */
function um_friends_account_tab( $output, $shortcode_args ) {
	if ( isset( $shortcode_args['_enable_new_friend'] ) && 0 == $shortcode_args['_enable_new_friend'] ) {
		return $output;
	}

	$_enable_new_friend = UM()->Friends_API()->api()->enabled_email( get_current_user_id() );

	$fields['_enable_new_friend'] = array(
		'meta_key' => '_enable_new_friend'
	);
	$fields = apply_filters('um_account_secure_fields', $fields, 'notifications' );

	ob_start(); ?>

	<div class="um-field-area">
		<label class="um-field-checkbox <?php if ( ! empty( $_enable_new_friend ) ) { ?>active<?php } ?>">
			<input type="checkbox" name="_enable_new_friend" value="1" <?php checked( ! empty( $_enable_new_friend ) ) ?> />
			<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-<?php if ( ! empty( $_enable_new_friend ) ) { ?>outline<?php } else { ?>outline-blank<?php } ?>"></i></span>
			<span class="um-field-checkbox-option"><?php _e( 'I have got a new friend', 'um-friends' ); ?></span>
		</label>

		<div class="um-clear"></div>

	</div>

	<?php $output .= ob_get_clean();

	return $output;
}
add_filter( 'um_account_content_hook_notifications', 'um_friends_account_tab', 50, 2 );


/**
 * @param $array
 *
 * @return mixed
 */
function um_friends_notes_privacy( $array ) {
	$array['friends'] = __( 'Friends only', 'um-friends' );
	return $array;
}
add_filter( 'um_user_notes_privacy_dropdown_values', 'um_friends_notes_privacy', 10, 1 );
add_filter( 'um_user_bookmarks_privacy_dropdown_values', 'um_friends_notes_privacy', 10, 1 );


/**
 * @param $can_view
 * @param $privacy
 * @param $user_id
 *
 * @return bool
 */
function um_user_notes_custom_privacy( $can_view, $privacy, $user_id ) {
	if ( $privacy == 'friends' && $user_id ) {
		return UM()->Friends_API()->api()->is_friend( $user_id, um_profile_id() );
	}

	return $can_view;
}
add_filter( 'um_user_notes_custom_privacy', 'um_friends_notes_privacy', 10, 3 );


/**
 * @param $can_view
 * @param $privacy
 * @param $user_id
 *
 * @return bool
 */
function um_friends_bookmarks_privacy( $can_view, $privacy, $user_id ) {
	if ( $privacy == 'friends' && $user_id ) {
		return UM()->Friends_API()->api()->is_friend( $user_id, um_profile_id() );
	}

	return $can_view;
}
add_filter( 'um_user_bookmarks_custom_privacy', 'um_friends_bookmarks_privacy', 10, 3 );