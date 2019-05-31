<?php
namespace um_ext\um_mailchimp\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mailchimp_Func {

	private $mailchimp;

	function __construct() {
		$this->user_id = get_current_user_id();
	}

	function filter_connected_lists( $lists ) {
		$um_list_ids = array_keys( $lists );
		foreach ( $um_list_ids as $_list_value ) {

			$args = array(
				'post_type'	=> 'um_mailchimp',
				'meta_query' => array(
					array(
						'key'     => '_um_list',
						'value'   => $_list_value,
						'compare' => '=',
					),
				)
			);

			$um_list_query = new \WP_Query( $args );
			if( !$um_list_query->post_count ){
				 unset( $lists[ $_list_value ] );
		   }
		}
		return $lists;
	}

	function prepare_data( $merge_vars ) {
		$merge_vars = empty(  $merge_vars ) ? array() : $merge_vars;
		foreach( $merge_vars as $key => $val ) {
			if( ! empty( $val ) ) {
				if ( is_array( $val ) ) {
					$merge_vars[ $key ] = implode(', ', $val );
				}
			}else{
				unset( $merge_vars[ $key ] );
			}
		}
		if( isset( $merge_vars['email_address'] ) ) {
			unset( $merge_vars['email_address'] );
		}
		return $merge_vars;
	}

	/**
	 * Update
	 *
	 * @param bool $array
	 */
	function mailchimp_update( $array ) {
		if ( !$array || !is_array($array) ) $array = array();

		$array = $this->filter_connected_lists( $array );

		// update user info for specific list
		foreach( $array as $list_id => $data ) {
			if ( empty( $data ) ) continue;

			foreach( $data as $user_id => $merge_vars ) {
				if( !empty( $merge_vars['email_address'] ) ) {
					$email = $merge_vars['email_address'];
				} else {
					um_fetch_user( $user_id );
					$email = um_user('user_email');
				}

				$email_md5 = md5( $email );

				$this->call()->put("lists/{$list_id}/members/{$email_md5}",  array(
					'email_address' => um_user('user_email'),
					'merge_fields'  => $this->prepare_data( $merge_vars ),
					'status'        => apply_filters('um_mailchimp_default_subscription_status', 'subscribed', 'update', $list_id, $email ),
				));
			}
		}

	}


	/**
	 * Subscribe
	 *
	 * @param bool $override
	 * @param bool $all
	 */
	function mailchimp_subscribe( $array ) {
		if ( !$array || !is_array($array) ) $array = array();

		$array = $this->filter_connected_lists( $array );

		// update user info for specific list
		foreach ( $array as $list_id => $data ) {
			if ( empty( $data ) ) continue;

			foreach ( $data as $user_id => $merge_vars ) {
				if( ! empty( $merge_vars['email_address'] ) ) {
					$email = $merge_vars['email_address'];
				} else {
					um_fetch_user( $user_id );
					$email = um_user( 'user_email' );
				}
				//$email_md5 = md5( $email );

				$this->call()->post( "lists/{$list_id}/members/", array(
					'email_address' => $email,
					'merge_fields'  => $this->prepare_data( $merge_vars ),
					'status'        => apply_filters_ref_array( 'um_mailchimp_default_subscription_status', array(
						! empty( $merge_vars['subscr_status'] ) ? $merge_vars['subscr_status'] : 'subscribed',
						'subscribe',
						$list_id,
						$email
					) ),
				));
			}
		}
	}


	/**
	 * Unsubscribe
	 *
	 * @param bool $override
	 * @param bool $all
	 */
	function mailchimp_unsubscribe( $array ) {
		if ( !$array || !is_array( $array ) ) $array = array();

		$array = $this->filter_connected_lists( $array );

		// unsubscribe each user to the mailing list
		foreach( $array as $list_id => $data ) {
			if ( empty( $data ) ) continue;

			foreach( $data as $user_id => $merge_vars ) {
				um_fetch_user( $user_id );
				if( isset( $merge_vars['email_address'] ) ) {
					$email = $merge_vars['email_address'];
				} else {
					$email = um_user('user_email');
				}
				$email_md5 = md5( $email );

				if ( UM()->options()->get('mailchimp_unsubscribe_delete') ) {
					$this->call()->delete( "lists/{$list_id}/members/{$email_md5}" );
				} else {
					$this->call()->patch( "lists/{$list_id}/members/{$email_md5}", array(
						'email_address' => $email,
						'status' => 'unsubscribed',
					) );
				}
			}
		}

	}

	/***
	***	@Last Update
	***/
	function get_last_update() {
		return get_option( 'um_mailchimp_last_update' );
	}

	/***
	***	@Last Subscribe
	***/
	function get_last_subscribe() {
		return get_option( 'um_mailchimp_last_subscribe' );
	}

	/***
	***	@Last Unsubscribe
	***/
	function get_last_unsubscribe() {
		return get_option( 'um_mailchimp_last_unsubscribe' );
	}


	/**
	 * Update user
	 *
	 * @param $list_id
	 * @param null $_merge_vars
	 */
	function update( $list_id, $_merge_vars = null ) {
		global $old_email;

		$user_id = $this->user_id;
		um_fetch_user( $user_id );

		$mylists = um_user('_mylists');

		if( !isset( $mylists[ $list_id ] ) ) {
			$this->subscribe( $list_id, $_merge_vars );
			return;
		}

		if ( !um_user('user_email') ) return;

		if( $list_id ) {
			$merge_vars = $this->get_merge_vars_values( $list_id, $user_id );
			$merge_vars = apply_filters('um_mailchimp_single_merge_fields', $merge_vars, $user_id, $list_id, $_merge_vars );
		} else {
			//prevent updates for not subscribed users
			return;
		}

		$_new_update = array();
		$_new_update[ $list_id ][ $user_id ] = $merge_vars;
		$_new_update[ $list_id ][ $user_id ]['email_address'] = um_user('user_email');

		if( !empty( $old_email ) && $old_email != um_user('user_email') ) {
			$_new_update[ $list_id ][ $user_id ]['subscr_status'] = $this->get_subscription_status_by_list_id( $list_id );
		}

		delete_option( "um_cache_userdata_{$user_id}" );

		$this->mailchimp_update( $_new_update );
	}


	/**
	 * Subscribe user
	 *
	 * @param $list_id
	 * @param null $_merge_vars
	 */
	function subscribe( $list_id, $_merge_vars = null ) {
		$user_id = $this->user_id;
		um_fetch_user( $user_id );

		if ( ! um_user('user_email') ) return;

		$list = $this->get_list_by_mailchimp_id( $list_id );
		$user = get_userdata( $user_id );
		$user_roles = array_values( $user->roles );

		if( !empty( $list['roles'] ) && !count( array_intersect( $list['roles'], $user_roles ) ) ) {
			return;
		}

		$mylists = um_user('_mylists');

		$merge_vars = $this->get_merge_vars_values( $list_id, $user_id );
		$merge_vars = apply_filters('um_mailchimp_single_merge_fields', $merge_vars, $user_id, $list_id, $_merge_vars );

		$_mylists = is_array( $mylists ) ? $mylists : array();
		if ( !isset( $_mylists[ $list_id ] ) ) {
			$_mylists[ $list_id ] = 1;
			update_user_meta( $user_id, '_mylists', $_mylists );
		}

		$_new_subscribers = array();
		$_new_subscribers[$list_id][$user_id] = $merge_vars;
		$_new_subscribers[ $list_id ][ $user_id ]['email_address'] = um_user('user_email');
		$_new_subscribers[ $list_id ][ $user_id ]['subscr_status'] = $this->get_subscription_status_by_list_id( $list_id );

		delete_option( "um_cache_userdata_{$user_id}" );
		$this->mailchimp_subscribe( $_new_subscribers );
	}


	/**
	 * Unsubscribe user
	 *
	 * @param $list_id
	 */
	function unsubscribe( $list_id ) {

		$user_id = $this->user_id;
		um_fetch_user( $user_id );

		if ( !um_user('user_email') ) return;

		$_mylists = get_user_meta( $user_id, '_mylists', true);
		if ( isset($_mylists[$list_id]) ) {
			unset( $_mylists[ $list_id ] );
			update_user_meta( $user_id, '_mylists', $_mylists);
		}

		$_new_unsubscribers = array();
		$_new_unsubscribers[$list_id][$user_id] = array( 'email_address' => um_user('user_email') );

		delete_option( "um_cache_userdata_{$user_id}" );
		$this->mailchimp_unsubscribe( $_new_unsubscribers );
	}


	/**
	 * @param $list_id
	 *
	 * @return string
	 */
	function get_subscription_status_by_list_id( $list_id ) {
		global $wpdb;

		$double_optin = $wpdb->get_var("SELECT pm.meta_value 
			FROM {$wpdb->postmeta} pm, {$wpdb->postmeta} pm2
			WHERE pm2.post_id = pm.post_id AND 
				pm.meta_key = '_um_double_optin' AND
				pm2.meta_key = '_um_list' AND
				pm2.meta_value = '{$list_id}'");

		if ( $double_optin == '1' ) {
			$status = 'pending';
		} elseif( $double_optin == '' && UM()->options()->get( 'mailchimp_double_optin' ) ) {
			$status = 'pending';
		} else {
			$status = 'subscribed';
		}

		return $status;
	}


	/**
	 * Fetch list
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	function fetch_list( $id ) {
		$setup = get_post( $id );
		if ( !isset( $setup->post_title ) ) return false;
		$list['id'] = get_post_meta( $id, '_um_list', true );
		$list['auto_register'] =  get_post_meta( $id, '_um_reg_status', true );
		$list['description'] = get_post_meta( $id, '_um_desc', true );
		$list['register_desc'] = get_post_meta( $id, '_um_desc_reg', true );
		$list['name']  = $setup->post_title;
		$list['status'] = get_post_meta( $id, '_um_status', true );
		$list['merge_vars'] = get_post_meta( $id, '_um_merge', true );
		$list['roles'] = get_post_meta( $id, '_um_roles', true);

		return $list;
	}


	/**
	 * Fetch list
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	function get_list_by_mailchimp_id( $id ) {
		$posts = get_posts( array(
			'post_type' => 'um_mailchimp',
			'meta_key' => '_um_list',
			'meta_value' => $id )
		);

		if ( !isset( $posts[0] ) ) return false;
		$list['id'] = get_post_meta( $posts[0]->ID, '_um_list', true );
		$list['auto_register'] =  get_post_meta( $posts[0]->ID, '_um_reg_status', true );
		$list['description'] = get_post_meta( $posts[0]->ID, '_um_desc', true );
		$list['register_desc'] = get_post_meta( $posts[0]->ID, '_um_desc_reg', true );
		$list['name']  = $posts[0]->post_title;
		$list['status'] = get_post_meta( $posts[0]->ID, '_um_status', true );
		$list['merge_vars'] = get_post_meta( $posts[0]->ID, '_um_merge', true );
		$list['roles'] = get_post_meta( $posts[0]->ID, '_um_roles', true);

		return $list;
	}


	/**
	 * Check if there are active integrations
	 *
	 * @param bool $admin
	 * @param null $user_id
	 *
	 * @return array
	 */
	function get_lists_data( $admin = false, $user_id = null ) {
		$args = array(
			'post_status'	=> array('publish'),
			'post_type' 	=> 'um_mailchimp',
			'fields'		=> 'ids',
			'posts_per_page' => -1
		);

		$args['meta_query'][] = array(
			'key' => '_um_status',
			'value' => '1',
			'compare' => '='
		);

		if( is_numeric( $user_id ) ){
			$this->user_id = $user_id;
		}

		um_fetch_user( $this->user_id );
		$lists = new \WP_Query( $args );
		if ( $lists->found_posts > 0 ) {
			$array = $lists->get_posts();

			// frontend-use
			if ( !$admin ) {
				foreach( $array as $k => $post_id ) {
					$roles = get_post_meta( $post_id, '_um_roles', true);
					$current_user_roles = um_user( 'roles' );
					if ( ! empty( $roles ) && ( empty( $current_user_roles ) || count( array_intersect( $current_user_roles, $roles ) ) <= 0 ) ) {
						unset( $array[$k] );
					}
				}
			} 

			if ( $array )
				return $array;
		}
		return array();
	}


	/**
	 * Get merge vars for a specific list
	 *
	 * @param $list_id
	 *
	 * @return array
	 */
	function get_vars( $list_id ) {
		$response = $this->call()->get( "/lists/{$list_id}/merge-fields" );
		return isset( $response['merge_fields'] ) ? $response['merge_fields'] : array();
	}


	/**
	 * Subscribe status
	 *
	 * @param $list_id
	 *
	 * @return bool
	 */
	function is_subscribed( $list_id ) {

		$user_id = $this->user_id;

		$_mylists = get_user_meta( $user_id, '_mylists', true);

		if ( isset( $_mylists[ $list_id ] ) ) {
				return true;
		}

		$email_md5 = md5( um_user('user_email') );
		$lists = $this->call()->get("lists/{$list_id}/members/{$email_md5}");
		if ( !$lists || ( isset( $lists['status'] ) && $lists['status'] == 'unsubscribed' ) || $lists['status'] == 404 ) {
			return false;
		}

		$_mylists[ $list_id ] = 1;
		update_user_meta( $user_id, '_mylists', $_mylists );
		return true;
	}


	/**
	 * Get list names
	 *
	 * @param bool $raw
	 *
	 * @return array
	 */
	function get_lists( $raw = true ) {
		$lists = array();
		if ( $raw ) { // created from MailChimp
			$result = $this->call();

			if( ! is_wp_error( $result ) ){
				$lists = $result->get( 'lists',  array( "count" => apply_filters('um_mailchimp_lists_limit', 100 ) ) );
			}
			
		} else { // created from post type 'um_mailchimp'
			$has_lists = $this->get_lists_data( true );
			if( is_array( $has_lists ) ){
				foreach ( $has_lists as $i => $list_id ){
					$list = $this->fetch_list( $list_id );
					$lists['lists'][] = array(
						'name' => $list['name'],
						'id'   => $list_id,
					);
				}
			}
		}

		$res = array();
		if ( isset( $lists['lists'] ) ) {
			foreach ( $lists['lists'] as $key => $list ) {
				$res[ $list['id'] ] = $list['name'];
			}
		}

		return $res;
	}


	/**
	 * Get list subscriber count
	 *
	 * @param $list_id
	 *
	 * @return int|mixed|string
	 */
	function get_list_member_count( $list_id ) {
		$list_data = $this->call()->get( "lists/{$list_id}");
		return isset( $list_data['stats']['member_count'] ) ? $list_data['stats']['member_count'] : 0;
	}

	/***
	***	@Retrieve connection
	***/
	function call() {
		if( is_object( $this->mailchimp ) ) return $this->mailchimp;

		$apikey = UM()->options()->get('mailchimp_api');
		if ( !$apikey )
			return new \WP_Error( 'um-mailchimp-empty-api-key',
				sprintf(__('<a href="%s"><strong>Please enter your valid API key</strong></a> in settings.','um-mailchimp'), admin_url('admin.php?page=um_options&tab=extensions&section=mailchimp' ) ) );

		try{
			$result = new \UM_MailChimp_V3( $apikey );
			$this->mailchimp = $result;
		} catch ( \Exception $e ) {
			$result = new \WP_Error( 'um-mailchimp-api-error', $e->getMessage() );
		}

		
		return $result;
	}

	/***
	***	@Retrieve connection
	***/
	function get_account_data() {
		$result = $this->call();
		if( !is_wp_error( $result ) ) {
			$result = $result->get('');
		}

		return $result;
	}


	/**
	 * Queue count
	 *
	 * @param $type
	 *
	 * @return int
	 */
	function queue_count( $type ) {
		$count = 0;
		$queue = '';
		if ( $type == 'subscribers' ) {
			$queue = get_option( '_mailchimp_new_subscribers' );
		} elseif ( $type == 'unsubscribers' ) {
			$queue = get_option( '_mailchimp_new_unsubscribers' );
		} else if ( $type == 'update' ) {
			$queue = get_option( '_mailchimp_new_update' );
		/*} else if ( $type == 'not_synced' ) {
			$queue = get_option( '_mailchimp_unable_sync_profiles' );*/
		} else if ( $type == 'not_optedin' ) {
			$queue = get_option( '_mailchimp_not_optedin_profiles' );
		} else if ( $type == 'optedin_not_synced' ) {
			$queue = get_option( '_mailchimp_optedin_not_synced_profiles' );
		} else if ( $type == 'errored_synced_profiles' ) {
			$queue = get_option( '_um_mailchimp_optedin_synced_errored_profiles' );
		}

		if ( $queue && !in_array( $type , array('not_optedin','optedin_not_synced') ) ) {
			foreach( $queue as $list_id => $data ) {
				$count = $count + count($data);
			}
		}else if( $queue ) {
			$count = count( $queue );
		}

		return $count;
	}

	function get_external_list_users( $list_id, $cache = true ) {
		if( $cache ) {
			$mailchimp_members = get_transient('_um_mailchimp_list_members_' . $list_id );
		}

		if( empty( $mailchimp_members ) ) {
			$response          = $this->call()->get( "lists/{$list_id}/members" );
			$mailchimp_members = array();
			if ( ! empty( $response['members'] ) ) {
				foreach ( $response['members'] as $member ) {
					if ( $member['status'] != 'subscribed' ) {
						continue;
					}
					$mailchimp_members[] = $member['email_address'];
				}
			}
		}
		return $mailchimp_members;
	}

	function sync_list_users( $list_id ) {
		//get users list from cache
		if( !( $users = get_transient( '_um_mailchimp_sync_users' ) ) ) {
			//get all users with selected role and status
			$query_users = new \WP_User_Query( array(
				'fields' => array( 'user_email', 'ID' )
			) );
			$users       = $query_users->get_results();
			//set users list cache
			set_transient( '_um_mailchimp_sync_users', $users, 24 * 3600 );
		}

		if( count( $users ) > 0 ) {
			//get subscribers from mailchimp list
			$mailchimp_members = $this->get_external_list_users( $list_id );

			$subscribe = get_transient('_um_mailchimp_subscribe_users');
			foreach ( $users as $key => $user ) {
				$internal_user_lists = isset( $user->internal_lists ) ? $user->internal_lists : get_user_meta( $user->ID, "_mylists", true );
				if( is_array( $internal_user_lists ) && count( $internal_user_lists ) ) {

					//check if user isn't mailchimp subscriber for list with id $list_id but subscribed on current site
					if( !in_array( $user->user_email, $mailchimp_members ) && isset( $internal_user_lists[ $list_id ] ) ) {
						/*$this->mailchimp_subscribe()*/

					//check if user is mailchimp subscriber for list with id $list_id but didn't subscribed on current site
					} else if( in_array( $user->user_email, $mailchimp_members ) && !isset( $internal_user_lists[ $list_id ] ) ) {
						if( !is_array( $subscribe[ $list_id ] ) ) $subscribe[ $list_id ] = array();
						$subscribe[ $list_id ][] = $user;
					}
				} else {
					if( !is_array( $subscribe[ $list_id ] ) ) $subscribe[ $list_id ] = array();
					$subscribe[ $list_id ][] = $user;
				}
			}
			set_transient( '_um_mailchimp_subscribe_users', $subscribe, 24 * 3600 );
		}
	}

	function get_profiles_for_subscription( $action_key, $role = '', $status = '' ) {
		//get users list from cache
		if( !( $users = get_transient( '_um_mailchimp_users_' . $action_key . '_' . $role . '_' . $status ) ) ) {
			//get all users with selected role and status
			$args = array(
				'fields' => array( 'user_email', 'ID' )
			);

			if ( ! empty( $role ) ) {
				$args['role'] = $role;
			}

			if ( ! empty( $status ) ) {
				$args['meta_query'][] = array(
					'key'     => 'account_status',
					'value'   => $status,
					'compare' => '=',
				);
			}

			$query_users = new \WP_User_Query( $args );
			$users       = $query_users->get_results();

			//set users list cache
			set_transient( '_um_mailchimp_users_' . $action_key . '_' . $role . '_' . $status, $users, 24 * 3600 );
		}
		return $users;
	}

	function get_profiles_not_optedin( $role = '', $status = '' ) {
		
		// Not Opted-in
		$args = array(
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'relation' => 'OR',
						array(
							'key'     => '_mylists',
							'compare' => 'NOT EXISTS'
						),
						array(
							'key'     => '_mylists',
							'compare' => '=',
							'value'	  => 'a:0:{}'
						),
						array(
							'key'     => '_mylists',
							'compare' => '=',
							'value'	  => ''
						),
					),

				),
				'fields' => array( 'ID' )
			);


		if( !empty( $role ) ) {
			$args['role'] = $role;
		}

		if( !empty( $status ) ){
			$args['meta_query'][] = array(
				'key' => 'account_status',
				'value' => $status,
				'compare' => '=',
			);
		}
		
		$query_users = new \WP_User_Query( $args );
		$profiles = array();
		foreach( $query_users->get_results() as $user ) {
			$profiles[] = $user->ID;
		}
		update_option( '_mailchimp_not_optedin_profiles', $profiles );
	}

	function get_merge_vars_values( $list_id, $user_id,  $merge_vars = array() ){
		um_fetch_user( $user_id );
		$default_vars = array_filter( array(
			'FNAME'=> um_user('first_name') ? um_user('first_name') : um_user('user_login'),
			'LNAME'=> um_user('last_name')
		) );
		$merge_vars = array_merge( $merge_vars, $default_vars );

		//$key_list = array();
		if( is_array( $list_id )  && ! empty( $list_id ) ){
			$key_list = array(
				'key'     => '_um_list',
				'value'   =>  $list_id,
				'compare' => 'IN',
			);
		}else{
			 $key_list = array(
				'key'     => '_um_list',
				'value'   =>  $list_id,
				'compare' => '=',
			);
		}

		$args = array(
			'post_type' => 'um_mailchimp',
			'meta_query' => array(
				'relation' => 'AND',
				array(
								'key' => '_um_status',
								'value' => 1,
								'compare' => '='
				)
			),
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'fields' => 'ids'
		);

		$args['meta_query'][ ] = $key_list;

		$um_list_query  = new \WP_Query( $args );

		if( $um_list_query->found_posts > 0 ){

		   $_merge_vars = get_post_meta( $um_list_query->posts[0], '_um_merge', true );

			if ( $_merge_vars ) {
				foreach( $_merge_vars as $meta => $var ) {
					if ( $meta != '0' && um_user( $var ) ) {
						$merge_vars[ $meta ] = um_user( $var );
					}
				}
			}
		}

		return $merge_vars;

	}


	/**
	 * @param $list_id
	 * @param $users
	 * @param string $status
	 * @param string $action_key
	 * @param bool $merge_vars
	 *
	 * @return array|false|int|\WP_Error
	 */
	function bulk_subscribe_process( $list_id, $users, $status = 'subscribed', $action_key = '', $merge_vars = false ) {
		if( count( $users ) ) {
			if( function_exists( 'set_time_limit' ) &&
				false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) &&
				!ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved
					@set_time_limit( 0 ); // @codingStandardsIgnoreLine
			}

			if( is_numeric( $list_id ) && !( $list = UM()->Mailchimp_API()->api()->fetch_list( $list_id ) ) ) {
				return new \WP_Error('um_mailchimp_wrong_list', __('Wrong list', 'um-mailchimp') );
			}

			if( !is_numeric( $list_id ) && !( $list = UM()->Mailchimp_API()->api()->get_list_by_mailchimp_id( $list_id ) ) ) {
				return new \WP_Error('um_mailchimp_wrong_list', __('Wrong list', 'um-mailchimp') );
			}

			$Batch = $this->call()->new_batch();
			$mailchimp_members = $this->get_external_list_users( $list['id'] );

			foreach( $users as $key=>$user ) {
				um_fetch_user( $user->ID );
				if( empty( $merge_vars[ $key ] ) ) {
					$_merge_vars = UM()->Mailchimp_API()->api()->get_merge_vars_values( $list['id'], $user->ID );
				} else {
					$_merge_vars = $merge_vars[ $key ];
				}

				$Batch->put("op_uid_{$user->ID}_list_{$list_id}_{$action_key}", "lists/{$list['id']}/members", array(
					'email_address' => $user->user_email,
					'status'        => $status,
					'merge_fields'  => $_merge_vars
				) );

				$user_lists = get_user_meta( $user->ID, '_mylists', true );
				if( !isset( $user_lists[ $list['id'] ] ) ) {
					$user_lists[ $list['id'] ] = 1;
					update_user_meta( $user->ID, '_mylists', $user_lists );
				}
			}
			return $Batch->execute();
		}
		return 0;
	}

	function bulk_unsubscribe_process( $list_id, $users, $action_key = '' ) {
		if( count( $users ) ) {
			if( function_exists( 'set_time_limit' ) &&
				false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) &&
				!ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved
					@set_time_limit( 0 ); // @codingStandardsIgnoreLine
			}

			if( !( $list = UM()->Mailchimp_API()->api()->fetch_list( $list_id ) ) ) {
				return new \WP_Error('um_mailchimp_wrong_list', __('Wrong list', 'um-mailchimp') );
			}

			$Batch = $this->call()->new_batch();
			//$mailchimp_members = $this->get_external_list_users( $list['id'] );

			foreach( $users as $user ) {
				$email_md5 = md5( $user->user_email );
				if ( UM()->options()->get('mailchimp_unsubscribe_delete') ) {
					$Batch->delete( "um_delete_{$user->ID}_{$list_id}_{$action_key}", "lists/{$list_id}/members/{$email_md5}" );
				} else {
					$Batch->patch( "um_unsubscribe_{$user->ID}_{$list_id}_{$action_key}","lists/{$list_id}/members/{$email_md5}", array(
						'email_address' => $user->user_email,
						'status' => 'unsubscribed',
					) );
				}

				$user_lists = get_user_meta( $user->ID, '_mylists', true );
				if( isset( $user_lists[ $list['id'] ] ) ) {
					unset( $user_lists[ $list['id'] ] );
					update_user_meta( $user->ID, '_mylists', $user_lists );
				}
			}
			return $Batch->execute();
		}
		return 0;
	}

	function get_user_lists( $user_id ) {
		um_fetch_user( $user_id );
		if( um_user('account_status') != 'approved' ) return array();

		$user_lists = um_user( '_mylists' );

		if( is_array( $user_lists ) && !empty( $user_lists ) ) {

			$args = array(
				'post_status'	 => array('publish'),
				'post_type' 	 => 'um_mailchimp',
				'posts_per_page' => -1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key' => '_um_status',
						'value' => '1',
						'compare' => '='
					),
					array(
						'key'     => '_um_list',
						'value'   => array_keys( $user_lists ),
						'compare' => 'IN',
					)
				)
			);

			$lists = new \WP_Query( $args );
			if ( $lists->found_posts > 0 ) {
				$result = array();
				foreach( $lists->get_posts() as $post ) {
					$list = array();
					$list['post_id'] = $post->ID;
					$list['id'] = get_post_meta( $post->ID, '_um_list', true );
					$list['auto_register'] =  get_post_meta( $post->ID, '_um_reg_status', true );
					$list['description'] = get_post_meta( $post->ID, '_um_desc', true );
					$list['register_desc'] = get_post_meta( $post->ID, '_um_desc_reg', true );
					$list['name']  = $post->post_title;
					$list['status'] = get_post_meta( $post->ID, '_um_status', true );
					$list['merge_vars'] = get_post_meta( $post->ID, '_um_merge', true );
					$list['roles'] = get_post_meta( $post->ID, '_um_roles', true);
					$result[ $list['id'] ] = $list;
				}

				return $result;
			}
			return array();

		}
		return array();
	}

	function get_all_lists() {
		$args = array(
			'post_status'	 => array('publish'),
			'post_type' 	 => 'um_mailchimp',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key' => '_um_status',
					'value' => '1',
					'compare' => '='
				)
			)
		);

		$lists = new \WP_Query( $args );
		if ( $lists->found_posts > 0 ) {
			$result = array();
			foreach( $lists->get_posts() as $post ) {
				$list = array();
				$list['post_id'] = $post->ID;
				$list['id'] = get_post_meta( $post->ID, '_um_list', true );
				$list['auto_register'] =  get_post_meta( $post->ID, '_um_reg_status', true );
				$list['description'] = get_post_meta( $post->ID, '_um_desc', true );
				$list['register_desc'] = get_post_meta( $post->ID, '_um_desc_reg', true );
				$list['name']  = $post->post_title;
				$list['status'] = get_post_meta( $post->ID, '_um_status', true );
				$list['merge_vars'] = get_post_meta( $post->ID, '_um_merge', true );
				$list['roles'] = get_post_meta( $post->ID, '_um_roles', true);
				$result[ $list['id'] ] = $list;
			}
			return $result;
		}
		return array();
	}

}