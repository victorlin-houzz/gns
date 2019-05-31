<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Creates options in Role page
 *
 * @param $roles_metaboxes
 *
 * @return array
 */
function um_mycred_add_role_metabox( $roles_metaboxes ) {

	$roles_metaboxes[] = array(
		'id'        => "um-admin-form-mycred{" . um_mycred_path . "}",
		'title'     => __('myCRED','um-mycred'),
		'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
		'screen'    => 'um_role_meta',
		'context'   => 'normal',
		'priority'  => 'default'
	);

	return $roles_metaboxes;
}
add_filter( 'um_admin_role_metaboxes', 'um_mycred_add_role_metabox', 10, 1 );


/**
 * Sort by highest rated
 *
 * @param $options
 *
 * @return mixed
 */
function um_mycred_sort_user_option( $options ) {
	$options['most_mycred_points'] = __( 'Most MyCRED Points', 'um-mycred' );
	$options['least_mycred_points'] = __( 'Least MyCRED Points', 'um-mycred' );

	return $options;
}
add_filter( 'um_admin_directory_sort_users_select', 'um_mycred_sort_user_option', 10, 1 );
