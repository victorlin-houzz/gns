<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Change Group Title
 *
 * @param $title
 *
 * @return string
 */
function um_groups_change_title( $title ) {
	$screen = get_current_screen();
 
	if  ( 'um_groups' == $screen->post_type ) {
		$title = __('Enter group name...','um-groups');
	}
 
	return $title;
}
add_filter( 'enter_title_here', 'um_groups_change_title', 10, 1 );


/**
 * Add table columns
 *
 * @param $columns
 *
 * @return array
 */
function um_groups_posts_columns( $columns ) {
	unset( $columns['title'] );

	$columns['group_name'] = __('Name', 'um-groups');

	$new_columns = array(
		'members' => __('Members', 'um-groups'),
		'privacy' => __('Privacy', 'um-groups'),
		'author'  => __('Creator', 'um-groups'),
	);

	$new_columns['date'] = $columns['date'];
	unset( $columns['date'] );

	return array_merge($columns, $new_columns);
}
add_filter( 'manage_um_groups_posts_columns', 'um_groups_posts_columns' );


/**
 * Add group avatar in `Title` Column
 *
 * @param $column_name
 * @param $post_id
 */
function um_groups_table_content( $column_name, $post_id ) {
	if ($column_name == 'group_name') {
		echo UM()->Groups()->api()->get_group_image( $post_id );
		the_title();
	}
}
add_action( 'manage_um_groups_posts_custom_column', 'um_groups_table_content', 10, 2 );