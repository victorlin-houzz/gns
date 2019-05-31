<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	/***
	***	@creates options in Role page
	***/
add_filter( 'um_admin_role_metaboxes', 'um_activity_add_role_metabox', 10, 1 );
function um_activity_add_role_metabox( $roles_metaboxes ) {

    $roles_metaboxes[] = array(
        'id'        => "um-admin-form-activity{" . um_activity_path . "}",
        'title'     => __('Social Activity','um-activity'),
        'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
        'screen'    => 'um_role_meta',
        'context'   => 'normal',
        'priority'  => 'default'
    );

    return $roles_metaboxes;
}

	
	/***
	***	@Clear a wall post report
	***/
	add_action('um_admin_do_action__wall_report', 'um_admin_do_action__wall_report');
	function um_admin_do_action__wall_report( $action ){
		if ( !is_admin() || !current_user_can( 'edit_posts' ) ) die();
		
		if ( !isset($_REQUEST['post_id']) || !is_numeric( $_REQUEST['post_id'] ) ) die();

		if ( ! UM()->Activity_API()->api()->reported( $_REQUEST['post_id'] ) ) die();
		
		$post_id = (int) $_REQUEST['post_id'];
		
		delete_post_meta( $post_id, '_reported' );
		delete_post_meta( $post_id, '_reported_by' );
		
		$count = (int)get_option('um_activity_flagged');
		if ( $count < 1 ) $count = 1;
		update_option( 'um_activity_flagged', absint( $count - 1 ) );
		
		exit( wp_redirect( admin_url( 'edit.php?post_type=um_activity' ) ) );

	}