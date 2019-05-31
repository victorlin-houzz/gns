<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Customize filter query
 *
 * @param $query_args
 * @param $args
 *
 * @return mixed
 */
function um_profile_completeness_search_filter( $query_args, $args ) {
	extract( $args );

	if ( isset( $has_completed_profile ) && $has_completed_profile == 1 && isset( $has_completed_profile_pct ) && $has_completed_profile_pct > 0 ) {
		$query_args['meta_query'][] = array(
			'key' => '_completed',
			'value' => $has_completed_profile_pct,
			'compare' => '>=',
			'type' =>'NUMERIC'
		);
	}

	return $query_args;
}
add_filter( 'um_prepare_user_query_args', 'um_profile_completeness_search_filter', 120, 2 );


/**
 * Admin options for directory filtering
 *
 * @param $fields
 *
 * @return array
 */
function um_profile_completeness_admin_directory( $fields ) {
	$additional_fields = array(
		array(
			'id'		    => '_um_has_completed_profile',
			'type'		    => 'checkbox',
			'label'		    => __( 'Only show members who have completed their profile', 'um-profile-completeness' ),
			'value'		    => UM()->query()->get_meta_value( '_um_has_completed_profile', null, 'na' ),
		),
		array(
			'id'		    => '_um_has_completed_profile_pct',
			'type'		    => 'text',
			'label'		    => __( 'Required completeness (%)','um-profile-completeness' ),
			'value'		    => UM()->query()->get_meta_value('_um_has_completed_profile_pct', null, 'na' ),
			'conditional'	=> array( '_um_has_completed_profile', '=', '1' ),
			'size'			=> 'small'
		)
	);

	return array_merge( $fields, $additional_fields );
}
add_filter( 'um_admin_extend_directory_options_general', 'um_profile_completeness_admin_directory' );