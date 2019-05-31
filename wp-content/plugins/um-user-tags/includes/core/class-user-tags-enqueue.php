<?php
namespace um_ext\um_user_tags\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Tags_Enqueue
 * @package um_ext\um_user_tags\core
 */
class User_Tags_Enqueue {


	/**
	 * User_Tags_Enqueue constructor.
	 */
	function __construct() {
		$priority = apply_filters( 'um_user_tags_enqueue_priority', 0 );

		add_action( 'wp_enqueue_scripts',  array( &$this, '_enqueue_scripts' ), $priority );
		add_action( 'admin_enqueue_scripts',  array( &$this, '_enqueue_scripts' ), $priority );
	}


	/**
	 * Enqueue scripts
	 */
	function _enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';
		wp_register_script( 'um-user-tags', um_user_tags_url . 'assets/js/um-user-tags' . $suffix . '.js', array( 'jquery', 'select2', 'um_tipsy' ), um_user_tags_version, true );
		wp_register_style( 'um-user-tags', um_user_tags_url . 'assets/css/um-user-tags' . $suffix . '.css', array( 'select2', 'um_tipsy' ), um_user_tags_version );
	}
}