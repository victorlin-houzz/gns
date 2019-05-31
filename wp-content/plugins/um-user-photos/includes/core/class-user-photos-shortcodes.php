<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Shortcodes
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Shortcodes {


	/**
	 * User_Photos_Shortcodes constructor.
	 */
	function __construct() {

		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ), 9999 );

		add_shortcode( 'ultimatemember_gallery', array( $this, 'get_gallery_content' ) );
		add_shortcode( 'ultimatemember_gallery_photos', array( $this, 'gallery_photos_content' ) );
		
	}


	function wp_enqueue_scripts(){
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_style( 'umfancyboxcss', um_user_photos_url . 'assets/css/um-fancybox' . $suffix . '.css', array(),um_user_photos_version );
		wp_register_style( 'um-user-photos', um_user_photos_url . 'assets/css/um-user-photos' . $suffix . '.css', array( 'umfancyboxcss' ), um_user_photos_version );

		wp_register_script( 'umfancybox', um_user_photos_url . 'assets/js/um-fancybox' . $suffix . '.js', array( 'jquery' ), um_user_photos_version, true );
		wp_register_script( 'um-user-photos', um_user_photos_url . 'assets/js/um-user-photos' . $suffix . '.js', array( 'wp-util', 'umfancybox' ), um_user_photos_version, true );
	}


	/**
	 * @param array $atts
	 */
	function get_gallery_content( $atts = array() ) {
		wp_enqueue_script('um-user-photos');
		wp_enqueue_style('um-user-photos');


		if ( ! empty( $atts ) ) {
			extract( $atts );
		}
		
		if ( ! isset( $user_id ) ) {
			$user_id = um_user('ID');
		}
		
		$template_file = 'templates/gallery';
		UM()->Photos_API()->get_view( $template_file, array( 'user_id' => $user_id ) );
	}


	/**
	 * @param array $atts
	 */
	function gallery_photos_content( $atts = array() ) {
		wp_enqueue_script('um-user-photos');
		wp_enqueue_style('um-user-photos');


		if ( ! empty( $atts ) ) {
			extract( $atts );
		}
		
		if ( ! isset( $user_id ) ) {
			$user_id = um_user('ID');
		}
		
		$template_file = 'templates/photos';
		UM()->Photos_API()->get_view( $template_file, array( 'user_id' => $user_id ) );
	}
}