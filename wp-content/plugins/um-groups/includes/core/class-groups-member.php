<?php 
namespace um_ext\um_groups\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Groups_Member
 * @package um_ext\um_groups\core
 */
class Groups_Member{


	/**
	 * @var
	 */
	var $member;


	/**
	 * Groups_Member constructor.
	 */
	function __construct() {

	}

	/**
	 * Set member's current group
	 * @param integer $group_id 
	 */
	function set_group( $group_id, $user_id = null ) {
		global $wpdb;

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;
		
		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
			FROM {$table_name} 
			WHERE group_id = %d AND 
			      user_id1 = %d",
			$group_id,
			$user_id
		) );
	}


	/**
	 * Get member role
	 * @return string
	 */
	function get_role() {
		if ( isset( $this->member->role ) ) {
			return $this->member->role;
		}

		return '';
	}


	/**
	 * Get member status
	 * @return string
	 */
	function get_status() {
		if ( isset( $this->member->status ) ) {
			return $this->member->status;
		}

		return '';
	}


	/**
	 * Set member status to approve
	 *
	 */
	function approve() {
		UM()->check_ajax_nonce();

		if ( empty( $_REQUEST['group_id'] ) || empty( $_REQUEST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid data', 'um-groups' ) );
		}

		$group_id = $_REQUEST['group_id'];
		$user_id = $_REQUEST['user_id'];
		$role = 'member';
		$status = 'approved';

		if ( ! is_user_logged_in() || ( ! UM()->Groups()->api()->can_approve_requests( $group_id ) && ! um_groups_admin_all_access() ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-groups' ) );
		}

		global $wpdb;
		$table_name = UM()->Groups()->setup()->db_groups_table;
		/*$this->member = $wpdb->get_row(
			$wpdb->prepare(
				"UPDATE {$table_name} 
				SET status = %s, 
				    role = %s 
				WHERE group_id = %d AND 
				      user_id1 = %d",
				$status,
				$role,
				$group_id,
				$user_id
			)
		);*/

		$wpdb->update(
			$table_name,
			array(
				'status'    => $status,
				'role'      => $role,
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s',
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
			FROM {$table_name} 
			WHERE group_id = %d AND 
			      user_id1 = %d",
			$group_id,
			$user_id
		) );

		do_action("um_groups_after_member_changed_status", $user_id, $group_id, $status );
		do_action("um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, $role, false );

		do_action("um_groups_after_member_approve", $user_id, $group_id, $status );

		wp_send_json_success();
	}


	/**
	 * Set member status to reject
	 * @return json
	 */
	function reject( $group_id = null, $user_id = null ) {
		UM()->check_ajax_nonce();

		global $wpdb;

		if( is_null( $group_id ) ){
			$group_id = $_REQUEST['group_id'];
		}

		if( is_null( $user_id ) ){
			$user_id = $_REQUEST['user_id'];
		}
		
		if( ! is_user_logged_in() || ! UM()->Groups()->api()->can_approve_requests( $group_id ) && ! um_groups_admin_all_access() ){
			return wp_die(__('Invalid request','um-groups') );
		}
		
		$table_name = UM()->Groups()->setup()->db_groups_table;
		$status = 'rejected';

		/*$this->member = $wpdb->get_row(
			$wpdb->prepare(
					"UPDATE {$table_name} SET status = %s WHERE group_id = %d AND user_id1 = %d ", 
					$status,
					$group_id, 
					$user_id 
			)
		);*/

		$wpdb->update(
			$table_name,
			array(
				'status'    => $status,
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
			FROM {$table_name} 
			WHERE group_id = %d AND 
			      user_id1 = %d",
			$group_id,
			$user_id
		) );

		do_action("um_groups_after_member_changed_status", $user_id, $group_id, $status );
		do_action("um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, $role, false );
   
		return wp_send_json( array('status' => true ) );
	}


	/**
	 * Confirm group invite
	 * @return json
	 */
	function confirm_invitation( $group_id = null, $user_id = null ) {
		
		global $wpdb;

		if( is_null( $group_id ) ){
			$group_id = $_REQUEST['group_id'];
		}

		if( is_null( $user_id ) ){
			$user_id = $_REQUEST['user_id'];
		}
		
		if( ! is_user_logged_in() ){
			return wp_die(__('Invalid request','um-groups') );
		}
		
		$table_name = UM()->Groups()->setup()->db_groups_table;
		$status = 'approved';
		
		/*$this->member = $wpdb->get_row(
			$wpdb->prepare(
					"UPDATE {$table_name} SET status = %s, date_joined = %s WHERE group_id = %d AND user_id1 = %d ", 
					$status,
					date('y-m-d h:i:s',  current_time( 'timestamp' )  ),
					$group_id, 
					$user_id
			)
		);*/

		$wpdb->update(
			$table_name,
			array(
				'status'        => $status,
				'date_joined'   => date('y-m-d h:i:s',  current_time( 'timestamp' )  ),
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s',
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
			FROM {$table_name} 
			WHERE group_id = %d AND 
			      user_id1 = %d",
			$group_id,
			$user_id
		) );

		do_action("um_groups_after_member_changed_status", $user_id, $group_id, $status );
	
		do_action("um_groups_after_member_confirm_invitation", $user_id, $group_id, $status );
	}


	/**
	 * Reject Invitation
	 * @return json
	 */
	function reject_invitation( $group_id = null, $user_id = null ) {
		
		global $wpdb;

		if( is_null( $group_id ) ){
			$group_id = $_REQUEST['group_id'];
		}

		if( is_null( $user_id ) ){
			$user_id = $_REQUEST['user_id'];
		}
		
		if( ! is_user_logged_in() ){
			return wp_die(__('Invalid request','um-groups') );
		}
		
		$table_name = UM()->Groups()->setup()->db_groups_table;
		$status = 'rejected';

		/*$this->member = $wpdb->get_row(
			$wpdb->prepare(
				"UPDATE {$table_name} SET status = %s WHERE group_id = %d AND user_id1 = %d ",
				$status,
				$group_id,
				$user_id
			)
		);*/


		$wpdb->update(
			$table_name,
			array(
				'status'        => $status,
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
			FROM {$table_name} 
			WHERE group_id = %d AND 
			      user_id1 = %d",
			$group_id,
			$user_id
		) );


		do_action("um_groups_after_member_changed_status", $user_id, $group_id, $status );
		do_action("um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, $role, false );

		do_action("um_groups_after_member_reject_invitation", $user_id, $group_id, $status );
	}


	/**
	 * Set member status to block
	 * @return json
	 */
	function block() {
		UM()->check_ajax_nonce();

		global $wpdb;

		$group_id = $_REQUEST['group_id'];
		$user_id = $_REQUEST['user_id'];
		
		if( ! is_user_logged_in() || ! UM()->Groups()->api()->can_approve_requests( $group_id ) && ! um_groups_admin_all_access() ){
			return wp_die(__('Invalid request','um-groups') );
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;
		$status = 'blocked';

		/*$this->member = $wpdb->get_row(
			$wpdb->prepare(
					"UPDATE {$table_name} SET status = %s WHERE group_id = %d AND user_id1 = %d ", 
					$status,
					$group_id, 
					$user_id 
			)
		);*/

		$wpdb->update(
			$table_name,
			array(
				'status'        => $status,
			),
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%s'
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
			FROM {$table_name} 
			WHERE group_id = %d AND 
			      user_id1 = %d",
			$group_id,
			$user_id
		) );
		
		do_action("um_groups_after_member_changed_status", $user_id, $group_id, $status );
		do_action("um_groups_after_member_changed_status__{$status}", $user_id, $group_id, false, $role, false );
   
		return wp_send_json( array('status' => true ) );
	}


	/**
	 * Set member status to unblock
	 * @return json
	 */
	function unblock() {
		UM()->check_ajax_nonce();

		global $wpdb;

		$group_id = $_REQUEST['group_id'];
		$user_id = $_REQUEST['user_id'];
		
		if( ! is_user_logged_in() || ! UM()->Groups()->api()->can_approve_requests( $group_id ) && ! um_groups_admin_all_access() ){
			return wp_die(__('Invalid request','um-groups') );
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;
		
/*		$this->member = $wpdb->get_row(
			$wpdb->prepare(
					"DELETE FROM {$table_name} WHERE group_id = %d AND user_id1 = %d ", 
					$group_id, 
					$user_id 
			)
		);*/

		$wpdb->delete(
			$table_name,
			array(
				'group_id' => $group_id,
				'user_id1' => $user_id,
			),
			array(
				'%d',
				'%d'
			)
		);

		$this->member = $wpdb->get_row( $wpdb->prepare(
			"SELECT * 
			FROM {$table_name} 
			WHERE group_id = %d AND 
			      user_id1 = %d",
			$group_id,
			$user_id
		) );



		return wp_send_json( array('status' => true ) );
	}

	/**
	 * Get Moderators
	 * @param  integer $group_id 
	 * @return array      
	 */
	function get_moderators( $group_id = null ){
		global $wpdb;

		$table_name = UM()->Groups()->setup()->db_groups_table;
		
		$moderators = $wpdb->get_results( $wpdb->prepare(
			"SELECT user_id1 as uid 
			FROM {$table_name} 
			WHERE group_id = %d AND 
			      role IN( 'moderator', 'admin' )",
			$group_id
		) );
		
		return $moderators;
	}

	/**
	 * Get user's joined groups
	 * @param  integer $user_id 
	 * @return array    
	 */
	function get_groups_joined( $user_id = null ) {
		global $wpdb;

		if ( ! $user_id ) {
			$user_id = um_profile_id();
		}

		$table_name = UM()->Groups()->setup()->db_groups_table;
		
		$groups = $wpdb->get_results( $wpdb->prepare(
			"SELECT gg.group_id 
			FROM {$table_name} AS gg 
			WHERE gg.user_id1 = %d AND 
			      gg.status IN( 'approved' ) AND 
			      gg.group_id IN( SELECT p.ID FROM {$wpdb->posts} AS p WHERE p.post_type = 'um_groups' AND p.ID = gg.group_id ) 
			GROUP BY gg.group_id",
			$user_id
		) );

		return $groups;
	}
}