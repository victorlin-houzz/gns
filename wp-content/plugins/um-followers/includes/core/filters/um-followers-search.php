<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Adding sort directories by followers
 *
 * @param $query_args
 * @param $sortby
 *
 * @return mixed
 */
function um_followers_sortby_followed( $query_args, $sortby ) {
	if ( $sortby != 'most_followed' && $sortby != 'least_followed' ) return $query_args;

	$query_args['orderby'] = 'followers';
	$query_args['order']   = $sortby == 'most_followed' ? 'DESC' : 'ASC';

	return $query_args;
}
add_filter( 'um_modify_sortby_parameter', 'um_followers_sortby_followed', 100, 2 );


/**
 * Adding sort directories by followers
 *
 * @param $query
 *
 * @return mixed
 */
function um_wp_user_filter_by_followers( $query ) {
	global $wpdb;

	$users_table     = $wpdb->users;
	$followers_table = UM()->Followers_API()->api()->table_name;

	if ( isset( $query->query_vars['orderby'] ) && 'followers' == $query->query_vars['orderby'] ) {
		$order = isset( $query->query_vars['order'] ) ? $query->query_vars['order'] : 'DESC';
		$query->query_orderby = sprintf(
			'ORDER BY (SELECT COUNT(*) FROM `%s` WHERE `%s`.ID = `%s`.`user_id1`) %s',
			$followers_table, $users_table, $followers_table, $order );
	}

	return $query;
}
add_filter( 'pre_user_query', 'um_wp_user_filter_by_followers', 100 );