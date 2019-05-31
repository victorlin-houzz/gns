<?php
namespace um_ext\um_mailchimp\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


class Mailchimp_Admin {

	function __construct() {
		$this->pagehook = 'toplevel_page_ultimatemember';

		add_action( 'load-post.php', array(&$this, 'add_metabox'), 9 );
		add_action( 'load-post-new.php', array(&$this, 'add_metabox'), 9 );

		add_action( 'admin_notices', array( &$this, 'admin_notices' ), 1 );

		add_filter('manage_edit-um_mailchimp_columns', array(&$this, 'manage_edit_um_mailchimp_columns') );
		add_action('manage_um_mailchimp_posts_custom_column', array(&$this, 'manage_um_mailchimp_posts_custom_column'), 10, 3);

		add_action('um_extend_admin_menu',  array(&$this, 'um_extend_admin_menu'), 200);
		
		add_action('admin_enqueue_scripts',  array(&$this, 'admin_enqueue_scripts'), 9);
		
		add_filter('enter_title_here', array(&$this, 'enter_title_here') );
		
		add_action('admin_menu', array(&$this, 'prepare_metabox'), 20);
		
		add_action('um_admin_do_action__um_hide_mailchimp_notice', array(&$this, 'hide_notice') );
		
		add_action('um_admin_do_action__force_mailchimp_subscribe', array(&$this, 'force_mailchimp_subscribe') );
		add_action('um_admin_do_action__force_mailchimp_unsubscribe', array(&$this, 'force_mailchimp_unsubscribe') );
		add_action('um_admin_do_action__force_mailchimp_update', array(&$this, 'force_mailchimp_update') );

	}


	/***
	 ***	@Init the metaboxes
	 ***/
	function add_metabox() {
		global $current_screen;

		if ( $current_screen->id == 'um_mailchimp') {
			add_action( 'add_meta_boxes', array( &$this, 'add_metabox_form'), 1 );
			add_action( 'save_post_um_mailchimp', array( &$this, 'save_metabox_form' ), 10, 2 );
		}

	}

	/***
	 ***	@add form metabox
	 ***/
	function add_metabox_form() {

		add_meta_box(
			'um-admin-mailchimp-list',
			__( 'Setup List', 'um-mailchimp' ),
			array( &$this, 'load_metabox_form' ),
			'um_mailchimp',
			'normal',
			'default'
		);

		add_meta_box(
			'um-admin-mailchimp-merge',
			__( 'Merge User Meta', 'um-mailchimp' ),
			array( &$this, 'load_metabox_form' ),
			'um_mailchimp',
			'normal',
			'default'
		);

		add_meta_box(
			'um-admin-mailchimp-test-connection',
			__( 'Testing connection with Mailchimp server', 'um-mailchimp' ),
			array( &$this, 'load_metabox_form' ),
			'um_mailchimp',
			'normal',
			'default'
		);

	}


	/**
	 * Load a form metabox
	 *
	 * @param $object
	 * @param $box
	 */
	function load_metabox_form( $object, $box ) {
		$post_id = get_the_ID();
		$box['id'] = str_replace('um-admin-mailchimp-','', $box['id']);
		include_once um_mailchimp_path . 'includes/admin/templates/'. $box['id'] . '.php';
		wp_nonce_field( basename( __FILE__ ), 'um_admin_metabox_mailchimp_form_nonce' );
	}


	/**
	 * Save form metabox
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return mixed
	 */
	function save_metabox_form( $post_id, $post ) {
		// validate nonce
		if ( ! isset( $_POST['um_admin_metabox_mailchimp_form_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['um_admin_metabox_mailchimp_form_nonce'], basename( __FILE__ ) ) ) return $post_id;

		// validate user
		$post_type = get_post_type_object( $post->post_type );
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) return $post_id;

		$list_id = get_post_meta( $post_id, '_um_list', true );
		if( $list_id ) {
			foreach ( UM()->Mailchimp_API()->api()->get_vars( $list_id ) as $data ) {
				if( isset( $data['required'] ) && $data['required'] && empty( $_POST['mailchimp']['_um_merge'][ $data['tag'] ] ) ) {
					return $post_id;
				}
			}
		}

		// save
		//delete_post_meta( $post_id, '_um_roles' );

		foreach ( $_POST['mailchimp'] as $k => $v ) {
			if ( strstr( $k, '_um_' ) ) {
				update_post_meta( $post_id, $k, $v );
			}
		}

		return $post_id;
	}


	/**
	 * Show main notices
	 */
	function admin_notices() {
		$hide_notice = get_option( 'um_hide_mailchimp_notice' );

		if ( $hide_notice ) return;

		$hide_link = add_query_arg( 'um_adm_action', 'um_hide_mailchimp_notice' );
		$key = UM()->options()->get( 'mailchimp_api' );

		if ( !$key ) {

			echo '<div class="updated um-admin-notice"><p>';

			echo sprintf(__( 'You must add your <strong>MailChimp API</strong> key before connecting your newsletter lists. <a href="%s">Hide this notice</a>','um-mailchimp'), $hide_link);

			echo '</p>';

			echo '<p><a href="' . admin_url('admin.php?page=um_options&tab=extensions&section=mailchimp' ) . '" class="button button-primary">' . __( 'Setup MailChimp API', 'um-mailchimp' ) . '</a></p></div>';

		}
	}

	/**
	 * Custom columns
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	function manage_edit_um_mailchimp_columns( $columns ) {
		$new_columns['cb'] = '<input type="checkbox" />';
		$new_columns['title'] = __('Title','um-mailchimp');
		$new_columns['connection'] = __('Connection','um-mailchimp');
		$new_columns['status'] = __('Status','um-mailchimp');
		$new_columns['reg_status'] = __('Automatic Signup','um-mailchimp');
		$new_columns['list_id'] = __('List ID','um-mailchimp');
		$new_columns['subscribers'] = __('Subscribers','um-mailchimp');
		$new_columns['available_to'] = __('Roles','um-mailchimp');

		return $new_columns;
	}


	/**
	 * Display custom columns
	 *
	 * @param $column_name
	 * @param $id
	 */
	function manage_um_mailchimp_posts_custom_column( $column_name, $id ) {
		switch ( $column_name ) {

			case 'connection':
				$remote_lists = get_transient('um_mailchimp_remote_lists');
				if( $remote_lists === false ) {
					$remote_lists = UM()->Mailchimp_API()->api()->get_lists();
					set_transient( 'um_mailchimp_remote_lists', $remote_lists, 300 );
				}
				$list_id = get_post_meta( $id, '_um_list', true );
				if ( isset( $remote_lists[ $list_id ] ) ) {
					echo '<span class="um-adm-ico um-admin-tipsy-n" title="'.__('List found','um-mailchimp').'"><i class="um-faicon-check"></i></span>';
				} else {
					delete_post_meta( $id, '_um_status');
					echo '<span class="um-adm-ico inactive um-admin-tipsy-n" title="'.__('Unknown list','um-mailchimp').'"><i class="um-faicon-remove"></i></span>';
				}
				break;

			case 'status':
				$status = get_post_meta( $id, '_um_status', true );
				if ( $status ) {
					echo '<span class="um-adm-ico um-admin-tipsy-n" title="'.__('Active','um-mailchimp').'"><i class="um-faicon-check"></i></span>';
				} else {
					echo '<span class="um-adm-ico inactive um-admin-tipsy-n" title="'.__('Inactive','um-mailchimp').'"><i class="um-faicon-remove"></i></span>';
				}
				break;

			case 'reg_status':
				$status = get_post_meta( $id, '_um_reg_status', true );
				if ( $status ) {
					echo '<span class="um-adm-ico um-admin-tipsy-n" title="'.__('Active','um-mailchimp').'"><i class="um-faicon-check"></i></span>';
				} else {
					echo __('Manual','um-mailchimp');
				}
				break;

			case 'list_id':
				$list_id = get_post_meta( $id, '_um_list', true );
				echo $list_id;
				break;

			case 'subscribers':
				$list_id = get_post_meta( $id, '_um_list', true );
				echo UM()->Mailchimp_API()->api()->get_list_member_count( $list_id );
				break;

			case 'available_to':
				$roles = get_post_meta( $id, '_um_roles', true );
				$res = __('Everyone','um-mailchimp');
				if ( $roles && is_array( $roles ) ) {
					$res = array();
					$data = UM()->roles()->get_roles();
					foreach( $roles as $role ) {
						$res[] = isset( $data[ $role ] ) ? $data[ $role ] : '';
					}
					echo implode(", ", $res);
				}else{
					echo $res;
				}
				break;

		}

	}

	
	/***
	***	@force sync subscribe
	***/
	function force_mailchimp_subscribe() {
		if ( !is_admin() || !current_user_can('manage_options') ) die();
		UM()->Mailchimp_API()->api()->mailchimp_subscribe( true );
		exit( wp_redirect( remove_query_arg('um_adm_action') ) );
	}
	
	/***
	***	@force sync unsubscribe
	***/
	function force_mailchimp_unsubscribe() {
		if ( !is_admin() || !current_user_can('manage_options') ) die();
		UM()->Mailchimp_API()->api()->mailchimp_unsubscribe(true);
		exit( wp_redirect( remove_query_arg('um_adm_action') ) );
	}
	
	/***
	***	@force sync update
	***/
	function force_mailchimp_update() {
		if ( !is_admin() || !current_user_can('manage_options') ) die();
		UM()->Mailchimp_API()->api()->mailchimp_update(true);
		exit( wp_redirect( remove_query_arg('um_adm_action') ) );
	}


	/**
	 * Hide notice
	 *
	 * @param $action
	 */
	function hide_notice( $action ) {
		if ( !is_admin() || !current_user_can('manage_options') ) die();
		update_option( $action, 1 );
		exit( wp_redirect( remove_query_arg('um_adm_action') ) );
	}
	
	/***
	***	@prepare metabox
	***/
	function prepare_metabox() {
		
		add_action('load-'.$this->pagehook, array(&$this, 'load_metabox'));
		
	}
	
	/***
	***	@load metabox
	***/
	function load_metabox() {
		add_meta_box(
			'um-metaboxes-mailchimp',
			__( 'MailChimp','um-mailchimp' ),
			array( &$this, 'metabox_content' ),
			$this->pagehook,
			'core',
			'core'
		);
	}
	
	/***
	***	@metabox content
	***/
	function metabox_content() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';
		wp_enqueue_script('um-mailchimp-dashboard', um_mailchimp_url . 'assets/js/dashboard' . $suffix . '.js', array('jquery', 'wp-util', 'um_admin_global'), um_mailchimp_version, true );
		wp_localize_script('um-mailchimp-dashboard', 'um_mailchimp_data', array(
			'current_url'           => UM()->permalinks()->get_current_url(),
			'internal_lists'        => UM()->Mailchimp_API()->api()->get_lists( false ),
			'role'                  => isset( $_SESSION['_um_mailchimp_selected_role'] ) ? $_SESSION['_um_mailchimp_selected_role'] : '',
			'roles'                 => UM()->roles()->get_roles(),
			'status'                => isset( $_SESSION['_um_mailchimp_selected_status'] ) ? $_SESSION['_um_mailchimp_selected_status'] : '',

			'status_list'           => array(
				'approved'                      => __( 'Approved','um-mailchimp' ),
				'awaiting_admin_review'         => __( 'Awaiting Admin Review', 'um-mailchimp' ),
				'awaiting_email_confirmation'   => __( 'Awaiting Email Confirmation', 'um-mailchimp' ),
				'inactive'                      => __( 'Inactive', 'um-mailchimp' ),
				'rejected'                      => __( 'Rejected', 'um-mailchimp' ),
			),
			'labels'                => array(
				'sync_message'                  => __( 'Starting synchronization...', 'um-mailchimp' ),
				'scan_message'                  => __( 'Checking subscription status...', 'um-mailchimp' ),
				'start_bulk_subscribe_process'  => __( 'Opt-in users... 0%', 'um-mailchimp' ),
				'sync_process'                  => __( 'Syncronization...', 'um-mailchimp' ),
				'processing'                  => __( 'Processing...', 'um-mailchimp' )
			)
		));
		include_once um_mailchimp_path . 'includes/admin/templates/metabox.php';
	}


	/**
	 * Custom title
	 *
	 * @param $title
	 *
	 * @return string
	 */
	function enter_title_here( $title ) {
		$screen = get_current_screen();

		if ( 'um_mailchimp' == $screen->post_type ) {
			$title = __('e.g. My First Mailing List','um-mailchimp');
		}

		return $title;
	}


	/**
	 * Admin Styles
	 */
	function admin_enqueue_scripts() {
		wp_register_style('um_admin_mailchimp', um_mailchimp_url . 'assets/css/um-admin-mailchimp.css' );
		wp_enqueue_style('um_admin_mailchimp');

		wp_enqueue_script('wp-util');
	}
	
	/***
	***	@extends the admin menu
	***/
	function um_extend_admin_menu() {
	
		add_submenu_page( UM()->admin_menu()->slug, __('MailChimp','um-mailchimp'), __('MailChimp','um-mailchimp'), 'manage_options', 'edit.php?post_type=um_mailchimp', '' );
		
	}

}