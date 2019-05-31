<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extends core fields
 *
 * @param array $fields
 *
 * @return array
 */
function um_online_add_fields( $fields ) {

	$fields['_hide_online_status'] = array(
		'title' => __( 'Show my online status?', 'um-online' ),
		'metakey' => '_hide_online_status',
		'type' => 'radio',
		'label' => __( 'Show my online status?', 'um-online' ),
		'help' => __( 'Do you want other people to see that you are online?', 'um-online' ),
		'required' => 0,
		'public' => 1,
		'editable' => 1,
		'default' => 'no',
		'options' => array( 'no' => __( 'No', 'um-online' ), 'yes' => __( 'Yes', 'um-online' ) ),
		'account_only' => true,
	);

	$fields = apply_filters( 'um_account_secure_fields', $fields, '_hide_online_status' );

	$fields['online_status'] = array(
		'title'             => __( 'Online Status', 'um-online' ),
		'metakey'           => 'online_status',
		'type'              => 'text',
		'label'             => __( 'Online Status', 'um-online' ),
		'edit_forbidden'    => 1,
		'show_anyway'       => true,
		'custom'            => true,
	);

	return $fields;
}
add_filter( 'um_predefined_fields_hook', 'um_online_add_fields', 100 );


/**
 * Shows the online field in account page
 *
 * @param string $args
 * @param array $shortcode_args
 *
 * @return string
 */
function um_activity_account_online_fields( $args, $shortcode_args ) {
	$args = $args . ',_hide_online_status';
	return $args;
}
add_filter( 'um_account_tab_privacy_fields', 'um_activity_account_online_fields', 10, 2 );


/**
 * Shows the online status
 *
 * @param $value
 * @param $data
 *
 * @return string
 */
function um_online_show_status( $value, $data ) {
	if ( UM()->Online_API()->is_online( um_user('ID') ) ) {
		$output = '<span class="um-online-status online">' . __( 'online', 'um-online' ) . '</span>';
	} else {
		$output = '<span class="um-online-status offline">' . __( 'offline', 'um-online' ) . '</span>';
	}

	return $output;
}
add_filter( 'um_profile_field_filter_hook__online_status', 'um_online_show_status', 99, 2 );