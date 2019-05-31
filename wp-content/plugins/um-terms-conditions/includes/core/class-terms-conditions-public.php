<?php
namespace um_ext\um_terms_conditions\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ultimatemember.com/
 * @since      1.0.0
 *
 * @package    Um_Terms_Conditions
 * @subpackage Um_Terms_Conditions/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Um_Terms_Conditions
 * @subpackage Um_Terms_Conditions/public
 * @author     Ultimate Member <support@ultimatemember.com>
 */
class Terms_Conditions_Public {


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		add_action( 'um_after_form_fields', array( &$this, 'display_option' ) );
		add_action( 'um_submit_form_register', array( &$this, 'agreement_validation' ), 9 );

		add_filter( 'um_before_save_filter_submitted', array( &$this, 'add_agreement_date' ), 10, 1 );
		add_filter( 'um_email_registration_data', array( &$this, 'email_registration_data' ), 10, 1 );
	}


	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um-terms-conditions', um_terms_conditions_url . 'assets/js/um-terms-conditions-public' . $suffix . '.js', array( 'jquery' ), um_terms_conditions_version, false );

		wp_enqueue_script( 'um-terms-conditions' );
	}


	/**
	 * @param $args
	 */
	function display_option( $args ) {

		
		if ( isset( $args['use_terms_conditions'] ) && $args['use_terms_conditions'] == 1 ) {
			
				require um_terms_conditions_path . 'templates/um-terms-conditions-public-display.php';
			
		}
	}


	/**
	 * @param $args
	 */
	function agreement_validation( $args ) {
        $terms_conditions = get_post_meta( $args['form_id'], '_um_register_use_terms_conditions', true );

		if ( $terms_conditions && ! isset( $args['submitted']['use_terms_conditions_agreement'] ) ){
			UM()->form()->add_error('use_terms_conditions_agreement', isset( $args['use_terms_conditions_error_text'] ) ? $args['use_terms_conditions_error_text'] : '' );
		}
	}


	/**
	 * @param $submitted
	 *
	 * @return mixed
	 */
	function add_agreement_date( $submitted ) {
		if ( isset( $submitted['use_terms_conditions_agreement'] ) ) {
			$submitted['use_terms_conditions_agreement'] = time();
		}

		return $submitted;
	}


	/**
	 * @param $submitted
	 *
	 * @return mixed
	 */
	function email_registration_data( $submitted ) {

		if ( ! empty( $submitted['use_terms_conditions_agreement'] ) ) {
			$timestamp = ! empty( $submitted['timestamp'] ) ? $submitted['timestamp'] : $submitted['use_terms_conditions_agreement'];
			$submitted['Terms&Conditions Applied'] = date( "d M Y H:i", $timestamp );
			unset( $submitted['use_terms_conditions_agreement'] );
		}

		return $submitted;
	}

}
