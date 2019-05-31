<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Adding default order on directory
 *
 * @param array $query_args
 * @param $sortby
 *
 * @return array
 */
function um_reviews_sortby_top_rated( $query_args, $sortby ) {
	if ( $sortby != 'top_rated' )
		return $query_args;

	unset( $query_args['orderby'] );
	unset( $query_args['order'] );

	$query_args['meta_key'] = '_reviews_avg';
	$query_args['orderby'] = 'meta_value';
	$query_args['order'] = 'DESC';

	return $query_args;
}
add_filter( 'um_modify_sortby_parameter', 'um_reviews_sortby_top_rated', 100, 2 );


/**
 * Filter by user rating on frontend
 *
 * @param $query_args
 * @param $args
 *
 * @return mixed
 */
function um_reviews_filter_by_rating( $query_args, $args ) {
	if ( is_user_logged_in() ) {
		if ( ! UM()->Reviews_API()->api()->get_global_tab_privacy() ) {
			return $query_args;
		}

		if ( ! UM()->Reviews_API()->api()->get_role_tab_privacy() ) {
			return $query_args;
		}
	}

	if ( isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ) {

		foreach ( $query_args['meta_query'] as $k => $v ) {

			if ( isset( $v ) && is_array( $v ) ) {
				foreach ( $v as $review_value ) {
					if ( isset( $review_value['key'] ) && $review_value['key'] == 'filter_rating' ) {

						unset( $query_args['meta_query'][ $k ] );

						$val = $_GET['filter_rating'];
						$search = array( $val, $val + 0.95 );

						$query_args['meta_query'][] = array(
							'key' => '_reviews_avg',
							'value' => $search,
							'compare' => 'BETWEEN',
							//'type' => 'DECIMAL'
							'type' => 'NUMERIC'
						);
						break;

					}
				}
			}

		}

	}

	return $query_args;
}
add_filter( 'um_prepare_user_query_args', 'um_reviews_filter_by_rating', 200, 2 );


/**
 * Custom search filter
 *
 * @param $attrs
 *
 * @return mixed
 */
function um_custom_search_field_filter_rating( $attrs ) {
	if ( is_user_logged_in() ) {
		if ( ! UM()->Reviews_API()->api()->get_global_tab_privacy() ) {
			return $attrs;
		}

		if ( ! UM()->Reviews_API()->api()->get_role_tab_privacy() ) {
			return $attrs;
		}
	}

	$attrs['label'] = __( 'User Rating', 'um-reviews' );
	$attrs['options'] = array(
		5 => __( '5 Stars', 'um-reviews' ),
		4 => __( '4 Stars', 'um-reviews' ),
		3 => __( '3 Stars', 'um-reviews' ),
		2 => __( '2 Stars', 'um-reviews' ),
		1 => __( '1 Star', 'um-reviews '),
		0 => __( 'Any rating', 'um-reviews' )
	);
	$attrs['custom'] = true;

	return $attrs;
}
add_filter( 'um_custom_search_field_filter_rating', 'um_custom_search_field_filter_rating', 10, 1 );


/**
 * Extend search fields
 *
 * @param array $fields
 *
 * @return array
 */
function um_admin_custom_search_filter_rating( $fields ) {

	$fields['filter_rating'] = array(
		'title' => __( 'Filter by user rating', 'um-reviews' )
	);

	return $fields;
}
add_filter( 'um_admin_custom_search_filters', 'um_admin_custom_search_filter_rating', 10, 1 );


/**
 * @param $attrs
 *
 * @return mixed
 */
function um_review_frontend_member_search_filters( $attrs ) {

	if ( is_user_logged_in() ) {
		if( ! UM()->Reviews_API()->api()->get_global_tab_privacy() || ! UM()->Reviews_API()->api()->get_role_tab_privacy() ) {
			$key = array_search( 'filter_rating', $attrs );
			if( $key !== false ) {
				unset( $attrs[ $key ] );
			}
		}
	}

	return $attrs;
}
add_filter( 'um_frontend_member_search_filters', 'um_review_frontend_member_search_filters', 10 ,1 );