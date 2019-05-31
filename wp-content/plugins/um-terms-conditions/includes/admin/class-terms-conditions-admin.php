<?php
namespace um_ext\um_terms_conditions\admin;


if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ultimatemember.com/
 * @since      1.0.0
 *
 * @package    Um_Terms_Conditions
 * @subpackage Um_Terms_Conditions/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Um_Terms_Conditions
 * @subpackage Um_Terms_Conditions/admin
 * @author     Ultimate Member <support@ultimatemember.com>
 */
class Terms_Conditions_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'um_admin_custom_register_metaboxes', array( &$this, 'add_metabox_register' ) );
	}


	function add_metabox_register( $action ) {
		//UM()->metabox()->is_loaded = true;

		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) die();

		add_meta_box(
			"um-admin-form-register_terms-conditions{" . um_terms_conditions_path . "}",
			__( 'Terms & Conditions', 'um-terms-conditions' ),
			array( UM()->metabox(), 'load_metabox_form'),
			'um_form',
			'side',
			'default'
		);

	}

}
