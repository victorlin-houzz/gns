<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @param array $tabs
 *
 * @return array
 */
function um_groups_add_tabs( $tabs ) {
	$tabs['groups_list'] = array(
		'name'     => __( 'Groups', 'um-groups' ),
		'icon'     => 'um-faicon-users',
	);

	$enabled_tab = UM()->options()->get( 'profile_tab_groups_list' );

	if ( ! $enabled_tab && ! is_admin() ) {
		unset( $tabs['groups_list'] );
	}

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_groups_add_tabs', 2000, 1 );