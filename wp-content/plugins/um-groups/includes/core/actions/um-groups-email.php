<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Email notification for approved group membership
 * @param  integer $user_id  
 * @param  integer $group_id 
 */
function um_groups_after_member_changed_status__approved( $user_id, $group_id ) {
	um_fetch_user( $user_id  );

	$member_address = um_user('user_email');
	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );
		
	UM()->mail()->send(
		$member_address,
		'groups_approve_member',
		array(
			'plain_text' => 1,
			'path' => um_groups_path . 'templates/email/',
			'tags' => array(
				'{group_name}',
				'{group_url}',
			),
			'tags_replace' => array(
				$group_name,
				$group_url,
			)
		)
	);
}
add_action( 'um_groups_after_member_changed_status__approved', 'um_groups_after_member_changed_status__approved', 10, 2 );


/**
 * Email notification for join request to a group
 *
 * @param  integer $user_id  
 * @param  integer $group_id 
 */
function um_groups_after_member_changed_status__pending_admin_review( $user_id, $group_id ) {
	global $wpdb;
	
	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );
	$groups_request_tab_url = add_query_arg( 'tab','requests', $group_url );
	$moderators = UM()->Groups()->member()->get_moderators( $group_id );

	um_fetch_user( $user_id );
	$member_name = um_user('display_name');
	$profile_link = um_user_profile_url( $user_id );
    
	foreach( $moderators as $key => $mod ){

		// moderator
		um_fetch_user( $mod->uid );
		$moderator_name = um_user('display_name');
		$moderator_address = um_user('user_email');
      
		UM()->mail()->send(
			$moderator_address,
			'groups_join_request',
			array(
				'plain_text' => 1,
				'path' => um_groups_path . 'templates/email/',
				'tags' => array(
					'{moderator_name}',
					'{member_name}',
					'{group_name}',
					'{group_url}',
					'{groups_request_tab_url}',
					'{profile_link}'
				),
				'tags_replace' => array(
					$moderator_name,
					$member_name,
					$group_name,
					$group_url,
					$groups_request_tab_url,
					$profile_link
				)
			)
		);
	}


}
add_action('um_groups_after_member_changed_status__pending_admin_review','um_groups_after_member_changed_status__pending_admin_review', 10, 2 );


/**
 * Email notification for join request to a group
 * @param  integer $user_id  
 * @param  integer $group_id 
 */
function um_groups_after_member_changed_status__pending_member_review( $user_id, $group_id ) {
    
	um_fetch_user( $user_id  );

	$member_address = um_user('user_email');
	$group_invitation_guest_name = um_user('display_name');

	$group_name = get_the_title( $group_id );
	$group_url = get_the_permalink( $group_id );
	$group_invitation_host_id = get_the_author_id( $group_id );

	um_fetch_user( $group_invitation_host_id );
	$group_invitation_host_name = um_user('display_name');

	UM()->mail()->send(
		$member_address,
		'groups_invite_member',
		array(
			'plain_text' => 1,
			'path' => um_groups_path . 'templates/email/',
			'tags' => array(
				'{group_name}',
				'{group_url}',
				'{group_invitation_guest_name}',
				'{group_invitation_host_name}'
			),
			'tags_replace' => array(
				$group_name,
				$group_url,
				$group_invitation_guest_name,
				$group_invitation_host_name
			)
		)
	);

}
add_action( 'um_groups_after_member_changed_status__pending_member_review','um_groups_after_member_changed_status__pending_member_review', 10, 2 );