<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend core fields
 *
 * @param $fields
 *
 * @return mixed
 */
function um_mycred_add_field( $fields ) {

	$fields['mycred_default'] = array(
		'title' => __('myCRED Balance','um-mycred'),
		'metakey' => 'mycred_default',
		'type' => 'text',
		'label' => __('myCRED Balance','um-mycred'),
		'required' => 0,
		'public' => 1,
		'editable' => 0,
		'icon' => 'um-faicon-trophy',
	);

	$fields['mycred_progress'] = array(
		'title' => __('myCRED Progress','um-mycred'),
		'metakey' => 'mycred_progress',
		'type' => 'text',
		'label' => __('myCRED Progress','um-mycred'),
		'required' => 0,
		'public' => 1,
		'editable' => 0,
		'edit_forbidden' => 1,
		'show_anyway' => true,
		'custom' => true,
	);

	$fields['mycred_badges'] = array(
		'title' => __('myCRED Badges','um-mycred'),
		'metakey' => 'mycred_badges',
		'type' => 'text',
		'label' => __('myCRED Badges','um-mycred'),
		'required' => 0,
		'public' => 1,
		'editable' => 0,
		'edit_forbidden' => 1,
		'show_anyway' => true,
		'custom' => true,
	);

	$fields['mycred_rank'] = array(
		'title' => __('myCRED Rank','um-mycred'),
		'metakey' => 'mycred_rank',
		'type' => 'select',
		'label' => __('myCRED Rank','um-mycred'),
		'required' => 0,
		'public' => 1,
		'editable' => 0,
		'edit_forbidden' => 1,
		'show_anyway' => true,
		'custom' => true,
		'options' => array()
	);

	return $fields;
}
add_filter( "um_predefined_fields_hook", 'um_mycred_add_field', 10 );


/**
 * Number format for points
 *
 * @param $value
 * @param $data
 *
 * @return mixed|null|string
 */
function um_mycred_points_value( $value, $data ) {
	return UM()->myCRED_API()->get_points( um_user('ID') );
}
add_filter( 'um_profile_field_filter_hook__mycred_default', 'um_mycred_points_value', 99, 2 );


/**
 * Show user rank
 *
 * @param $value
 * @param $data
 *
 * @return null
 */
function um_mycred_show_rank_field( $value, $data ) {
	if ( ! function_exists( 'mycred_get_users_rank' ) ) {
		return null;
	}
	$user_id = um_is_core_page('user') ? um_profile_id() : um_user('ID');
	$rank = mycred_get_users_rank( $user_id );

	if ( is_object( $rank ) ) {
		return $rank->title;
	}

	return $value;
}
add_filter( 'um_profile_field_filter_hook__mycred_rank', 'um_mycred_show_rank_field', 99, 2 );


/**
 * Show user balance
 *
 * @param $value
 * @param $data
 *
 * @return string
 */
function um_mycred_show_badges_field( $value, $data ) {
	return UM()->myCRED_API()->show_badges( um_profile_id() );
}
add_filter( 'um_profile_field_filter_hook__mycred_badges', 'um_mycred_show_badges_field', 99, 2 );


/**
 * Show user balance
 *
 * @param $is_custom
 * @param $key
 * @param $user_id
 *
 * @return string
 */
function um_mycred_get_field_progress( $is_custom, $key, $user_id ) {
	if ( 'mycred_badges' !== $key ) {
		return $is_custom;
	}

	$users_badges = mycred_get_users_badges( $user_id );
	if ( ! empty( $users_badges ) ) {
		$is_custom = true;
	}

	return $is_custom;
}
add_filter( 'um_profile_completeness_get_field_progress', 'um_mycred_get_field_progress', 99, 3 );


/**
 * Show user progress
 *
 * @param $value
 * @param $data
 *
 * @return null|string
 */
function um_mycred_show_progress_field( $value, $data ) {
	if ( ! function_exists( 'mycred_get_users_rank' ) ) {
		return null;
	}

	wp_enqueue_script( 'um_mycred' );
	wp_enqueue_style( 'um_mycred' );

	$user_id = um_profile_id();

	$rank = mycred_get_users_rank( $user_id );
	if ( is_object( $rank ) ) {
		$progress = '<span class="um-mycred-progress um-tip-n" title="'. $rank->title . ' ' . (int) UM()->myCRED_API()->get_rank_progress( $user_id ) . '%"><span class="um-mycred-progress-done" style="" data-pct="'.UM()->myCRED_API()->get_rank_progress( $user_id ).'"></span></span>';
	}

	return $progress;
}
add_filter( 'um_profile_field_filter_hook__mycred_progress', 'um_mycred_show_progress_field', 99, 2 );


/**
 * @param $tags
 *
 * @return array
 */
function um_mycred_allowed_user_tags( $tags ) {
	$tags[] = '{mycred_balance}';
	return $tags;
}
add_filter( 'um_allowed_user_tags_patterns', 'um_mycred_allowed_user_tags', 10, 1 );


/**
 * @param $value
 * @param $user_id
 *
 * @return mixed|null|string
 */
function um_profile_tag_hook__mycred_balance( $value, $user_id ) {
	return UM()->myCRED_API()->get_points( $user_id );
}
add_filter( 'um_profile_tag_hook__mycred_balance', 'um_profile_tag_hook__mycred_balance', 10, 2 );


/**
 * @param $options
 *
 * @return mixed
 */
function um_mycred_members_directory_sort_dropdown_options( $options ) {
	$options['most_mycred_points'] = __( 'Most Points', 'um-mycred' );
	$options['least_mycred_points'] = __( 'Least Points', 'um-mycred' );

	return $options;
}
add_filter( 'um_members_directory_sort_dropdown_options', 'um_mycred_members_directory_sort_dropdown_options', 10, 1 );