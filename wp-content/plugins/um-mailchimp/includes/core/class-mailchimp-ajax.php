<?php
namespace um_ext\um_mailchimp\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mailchimp_Ajax {

	function ajax_test_subscribe() {
		UM()->admin()->check_ajax_nonce();

		$data = !empty( $_POST['test_data'] ) ? $_POST['test_data'] : array();
		if( !empty( $data['_um_test_email'] ) && is_email( $data['_um_test_email'] ) ) {
			$email = $data['_um_test_email'];
			unset($data['_um_test_emaild']);
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		if( !empty( $data['list_id'] ) ) {
			$list_id = $data['list_id'];
			unset($data['list_id']);
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		$response = UM()->Mailchimp_API()->api()->call()->post( "lists/{$list_id}/members/", array(
			'email_address' => $email,
			'merge_fields'  => $response = UM()->Mailchimp_API()->api()->prepare_data( $data ),
			'status'        => apply_filters_ref_array( 'um_mailchimp_default_subscription_status', array(
				'subscribed',
				'subscribe',
				$list_id,
				$email
			) ),
		));

		wp_send_json_success( array(
			'result' => !empty( $response['id'] ),
			'message'=> json_encode( $response )
		) );
	}

	function ajax_test_update() {
		UM()->admin()->check_ajax_nonce();

		$data = !empty( $_POST['test_data'] ) ? $_POST['test_data'] : array();
		if( !empty( $data['_um_test_email'] ) && is_email( $data['_um_test_email'] ) ) {
			$email = $data['_um_test_email'];
			unset($data['_um_test_email']);
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		if( !empty( $data['list_id'] ) ) {
			$list_id = $data['list_id'];
			unset($data['list_id']);
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		$response = UM()->Mailchimp_API()->api()->call()->put( "lists/{$list_id}/members/" . md5( $email ), array(
			'email_address' => $email,
			'merge_fields'  => $response = UM()->Mailchimp_API()->api()->prepare_data( $data ),
			'status'        => apply_filters_ref_array( 'um_mailchimp_default_subscription_status', array(
				'subscribed',
				'update',
				$list_id,
				$email
			) ),
		));

		wp_send_json_success( array(
			'result' => !empty( $response['id'] ),
			'message'=> json_encode( $response )
		) );
	}

	function ajax_test_unsubscribe() {
		UM()->admin()->check_ajax_nonce();

		$data = !empty( $_POST['test_data'] ) ? $_POST['test_data'] : array();
		if( !empty( $data['_um_test_email'] ) && is_email( $data['_um_test_email'] ) ) {
			$email = $data['_um_test_email'];
			unset($data['_um_test_email']);
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		if( !empty( $data['list_id'] ) ) {
			$list_id = $data['list_id'];
			unset($data['list_id']);
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		$response = UM()->Mailchimp_API()->api()->call()->patch( "lists/{$list_id}/members/" . md5( $email ), array(
			'email_address' => $email,
			'status' => 'unsubscribed',
		) );

		wp_send_json_success( array(
			'result' => !empty( $response['id'] ),
			'message'=> json_encode( $response )
		) );
	}

	function ajax_test_delete() {
		UM()->admin()->check_ajax_nonce();

		$data = !empty( $_POST['test_data'] ) ? $_POST['test_data'] : array();
		if( !empty( $data['_um_test_email'] ) && is_email( $data['_um_test_email'] ) ) {
			$email = $data['_um_test_email'];
			unset($data['_um_test_email']);
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		if( !empty( $data['list_id'] ) ) {
			$list_id = $data['list_id'];
			unset($data['list_id']);
		} else {
			wp_send_json_error( __( 'Please enter email', 'um-mailchimp' ) );
		}

		$response = UM()->Mailchimp_API()->api()->call()->delete( "lists/{$list_id}/members/" . md5( $email ) );

		wp_send_json_success( array(
			'result' => empty( $response ),
			'message'=> empty( $response ) ? '' : json_encode( $response )
		) );
	}

	function ajax_get_merge_fields() {
		check_ajax_referer( 'um_mailchimp_get_merge_fields', 'nonce' );

		if( empty( $_POST['list_id'] ) ) {
			wp_send_json_error('Empty list ID');
		}

		$list_id = $_POST['list_id'];
		ob_start();
		include_once um_mailchimp_path . 'includes/admin/templates/merge.php';
		$content = ob_get_clean();
		wp_send_json_success( $content );
	}

	function ajax_clear_log() {
		UM()->admin()->check_ajax_nonce();

		UM()->Mailchimp_API()->log()->clear();
	}


	function ajax_force_action() {
		UM()->admin()->check_ajax_nonce();

		if ( ! empty( $_POST['force'] ) ) {
			$action = $_POST['force'];
			$queue_message = '';
			switch( $action ){
				case 'subscribe':
				case 'update':
					$array = $action == 'subscribe' ? get_option('_mailchimp_new_subscribers') : get_option('_mailchimp_new_update');
					if ( !$array || !is_array($array) ) wp_send_json_error( __('User list is empty', 'um-mailchimp') );

					$array = UM()->Mailchimp_API()->api()->filter_connected_lists( $array );

					foreach ( $array as $list_id => $data ) {
						if ( empty( $data ) ) {
							continue;
						}

						$user_ids = array_keys( $data );
						$users = get_users(array(
							'include' => $user_ids
						));

						$_merge_vars = array();
						foreach( $users as $user ) {
							$_merge_vars[] = $data[ $user->ID ];
						}

						UM()->Mailchimp_API()->api()->bulk_subscribe_process( $list_id, $users, 'subscribed', '', $_merge_vars );
					}

					$queue_message = $action == 'subscribe' ? __('0 new subscribers', 'um-mailchimp') : __('0 new profile updates', 'um-mailchimp');
					break;
				case 'unsubscribe':
					$array = get_option('_mailchimp_new_unsubscribers');
					if ( !$array || !is_array($array) ) wp_send_json_error( __('User list is empty', 'um-mailchimp') );

					$array = UM()->Mailchimp_API()->api()->filter_connected_lists( $array );

					foreach ( $array as $list_id => $data ) {
						if ( empty( $data ) ) {
							continue;
						}

						$user_ids = array_keys( $data );
						$users = get_users(array(
							'include' => $user_ids
						));

						UM()->Mailchimp_API()->api()->bulk_unsubscribe_process( $list_id, $users );
					}

					// reset new unsubscribers sync
					update_option('_mailchimp_new_unsubscribers', array());

					// update last unsubscribe data
					update_option( 'um_mailchimp_last_unsubscribe', time() );

					$queue_message = __('0 new unsubscribers', 'um-mailchimp');
					break;
			}
			wp_send_json_success(array(
				'message' => __( 'Done', 'um-mailchimp' ),
				'queue'   => $queue_message
			));
		}
		wp_send_json_error( __('Wrong queue action', 'um-mailchimp') );
	}


	function ajax_sync_now() {
		UM()->admin()->check_ajax_nonce();

		if ( ! empty( $_POST['list'] ) ) {
			$list_id = $_POST['list'];
			if( !( $list = UM()->Mailchimp_API()->api()->fetch_list( $list_id ) ) ) {
				wp_send_json_error( __('Wrong list') );
			}
		} else {
			wp_send_json_error( __('Empty list ID') );
		}

		$action_key = empty( $_POST['key'] ) ? uniqid() : $_POST['key'];
		$users = UM()->Mailchimp_API()->api()->get_profiles_for_subscription( $action_key );

		if( count( $users ) ) {
			if ( function_exists( 'set_time_limit' ) &&
				 false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) &&
				 ! ini_get( 'safe_mode' )
			) { // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved
				@set_time_limit( 0 ); // @codingStandardsIgnoreLine
			}

			$Batch             = UM()->Mailchimp_API()->api()->call()->new_batch();
			$mailchimp_members = UM()->Mailchimp_API()->api()->get_external_list_users( $list['id'] );

			//$internal_lists = array_keys( UM()->Mailchimp_API()->api()->get_lists( false ) );
			foreach ( $users as $user ) {
				um_fetch_user( $user->ID );
				$user_internal_lists = um_user('_mylists');
				$user_internal_lists = is_array( $user_internal_lists ) ? $user_internal_lists : array();

				$data = UM()->Mailchimp_API()->api()->get_merge_vars_values( $list['id'], $user->ID );

				if( in_array( $user->user_email, $mailchimp_members ) && empty( $user_internal_lists[ $list['id'] ] ) ) {
					//user only in mailchimp list
					$user_internal_lists[ $list['id'] ] = 1;
					update_user_meta( $user->ID,'_mylists', $user_internal_lists );
				} else if( !in_array( $user->user_email, $mailchimp_members ) && !empty( $user_internal_lists[ $list['id'] ] ) ) {
					//user only in internal list
					$Batch->post("op_uid_{$user->ID}_list_{$list_id}_{$action_key}", "lists/{$list['id']}/members", array(
						'email_address' => $user->user_email,
						'status'        => 'subscribed',
						'merge_fields'  => $data
					) );
				} else if( in_array( $user->user_email, $mailchimp_members ) && !empty( $user_internal_lists[ $list['id'] ] ) ) {
					//user in both lists, need only update data in mailchimp list
					$Batch->put("op_uid_{$user->ID}_list_{$list_id}_{$action_key}", "lists/{$list['id']}/members", array(
						'email_address' => $user->user_email,
						'status'        => 'subscribed',
						'merge_fields'  => $data
					) );
				}
			}
			$batch_id = $Batch->execute();
			$index = !empty( $_POST['index'] ) ? (int)$_POST['index'] + 1 : 1;
			$total = !empty( $_POST['total'] ) ? (int)$_POST['total'] : 1;
			if( $index == $total ) {
				$message = __('Completed', 'um-mailchimp');
			} else {
				$message = sprintf( __('Processed... %s', 'um-mailchimp'), ($index/$total*100) . '%' );
			}
			wp_send_json_success( array(
				'batch_id' => $batch_id,
				'message' => $message,
				'key' => $action_key
			) );
		} else {
			wp_send_json_error( __( 'You don\'t have any users', 'um-mailchimp' ) );
		}
	}


	function ajax_scan_now() {
		UM()->admin()->check_ajax_nonce();

		$role = isset( $_POST['role'] ) ? $_POST['role'] : '';
		$status = isset( $_POST['status'] ) ? $_POST['status'] : '';
		$action_key = isset( $_POST['key'] ) ? $_POST['key'] : uniqid();

		$users = UM()->Mailchimp_API()->api()->get_profiles_for_subscription( $action_key, $role, $status );

		// display the results
		wp_send_json_success( array(
			'key' => $action_key,
			'total' => count( $users ),
			'scan_total_message' => sprintf( _n( '%d user was selected', '%d users were selected', count( $users ), 'um-mailchimp' ), count( $users ) )
		) );
	}


	function ajax_bulk_subscribe() {
		UM()->admin()->check_ajax_nonce();

		if ( empty( $_POST['list'] ) ) {
			wp_send_json_error( __( 'Empty list ID', 'um-mailchimp' ) );
		}

		if ( empty( $_POST['key'] ) ) {
			wp_send_json_error( __( 'Empty key', 'um-mailchimp' ) );
		}

		$list_id = $_POST['list'];
		$action_key = $_POST['key'];
		$role = isset( $_POST['role'] ) ? $_POST['role'] : '';
		$status = isset( $_POST['status'] ) ? $_POST['status'] : '';

		$users = UM()->Mailchimp_API()->api()->get_profiles_for_subscription( $action_key, $role, $status );

		$batch_id = UM()->Mailchimp_API()->api()->bulk_subscribe_process( $list_id, $users, 'subscribed', $action_key );
		if ( is_wp_error( $batch_id ) ) {
			wp_send_json_error( $batch_id->get_error_message() );
		}

		if ( $batch_id === 0 ) {
			wp_send_json_error( __( 'Empty users list', 'um-mailchimp' ) );
		}

		$index = !empty( $_POST['index'] ) ? (int)$_POST['index'] + 1 : 1;
		$total = !empty( $_POST['total'] ) ? (int)$_POST['total'] : 1;

		if( $index == $total ) {
			$message = __('Completed', 'um-mailchimp');
		} else {
			$message = sprintf( __('Processed... %s', 'um-mailchimp'), ($index/$total*100) . '%' );
		}
		wp_send_json_success( array( 'batch_id' => $batch_id, 'message' => $message ) );
	}

}