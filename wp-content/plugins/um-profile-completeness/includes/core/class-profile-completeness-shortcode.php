<?php
namespace um_ext\um_profile_completeness\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile_Completeness_Shortcode
 * @package um_ext\um_profile_completeness\core
 */
class Profile_Completeness_Shortcode {


	/**
	 * Profile_Completeness_Shortcode constructor.
	 */
	function __construct() {

		add_shortcode( 'ultimatemember_profile_completeness', array( &$this, 'ultimatemember_profile_completeness' ) );
		add_shortcode( 'ultimatemember_profile_progress_bar', array( &$this, 'ultimatemember_profile_progress_bar' ) );
		add_shortcode( 'um_profile_completeness_show_content', array( &$this, 'show_content' ) );

	}


	/**
	 * Bar only widget
	 *
	 * @param array $args
	 * @return string
	 */
	function ultimatemember_profile_progress_bar( $args = array() ) {
		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );

		$defaults = array(
			'user_id' => get_current_user_id(),
			'who'     => 'loggedin',
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $user_id
		 * @var $who
		 */
		extract( $args );

		if ( ! $user_id || $who == 'loggedin') {
			$user_id = get_current_user_id();
		}

		if ( in_array( $who, array( 'loggedin', 'admin' ) ) && um_profile_id() && $user_id != um_profile_id() ) {
			return '';
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $user_id );

		if ( ! $result || $result['progress'] >= 100 ) {
			return '';
		}

		return $result['bar'];
	}


	/**
	 * Completeness widget
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_profile_completeness( $args = array() ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$requested_user_id = um_get_requested_user() ? um_get_requested_user() : get_current_user_id();
		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $requested_user_id );
		if ( ! $result || $result['progress'] >= 100 ) {
			return '';
		}

		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );

		$defaults = array();
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( is_array( $result['steps'] ) ) {
			$result['steps'] = $this->reorder( $result['steps'] );
		}

		ob_start();

		$file = str_replace( '/', DIRECTORY_SEPARATOR, um_profile_completeness_path . "templates/widget.php" );
		$theme_file = str_replace( '/', DIRECTORY_SEPARATOR, get_stylesheet_directory() . "/ultimate-member/templates/profile-completeness/widget.php" );

		if ( file_exists( $theme_file ) ) {
			$file = $theme_file;
		}

		include_once $file;

		$output = ob_get_clean();
		return $output;
	}


	/**
	 * re-order profile completion steps
	 *
	 * @param $steps
	 *
	 * @return mixed
	 */
	function reorder( $steps ) {
		if ( isset( $steps['profile_photo'] ) ) {
			$value = $steps['profile_photo'];
			unset( $steps['profile_photo'] );
			$steps['profile_photo'] = $value;
		}
		if ( isset( $steps['cover_photo'] ) ) {
			$value = $steps['cover_photo'];
			unset( $steps['cover_photo'] );
			$steps['cover_photo'] = $value;
		}

		return $steps;
	}


	/**
	 * Show content on specific progress
	 *
	 * @param array $atts
	 * @param string $content
	 *
	 * @return string
	 */
	function show_content( $atts = array(), $content = '' ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$a = shortcode_atts( array(
			'user_id'  => get_current_user_id(),
			'who'      => 'loggedin',
			'role'     => um_user( 'role' ),
			'progress' => 100,
			'not'      => FALSE,
		), $atts );

		if ( $a['who'] == 'current_profile' ) {
			$a['user_id'] = um_profile_id();
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $a['user_id'] );

		if ( ! $result ) {
			return '';
		}

		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );

		if ( $a['not'] ) {
			if ( $a['role'] == um_user( 'role' ) && $result['progress'] != $a['progress'] ) {
				return do_shortcode( $content );
			}
		} elseif ( $result['progress'] == $a['progress'] && $a['role'] == um_user( 'role' ) ) {
			return do_shortcode( $content );
		}

		return '';
	}


	/**
	 * Get progress result
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	function profile_progress( $user_id ) {
		$get_progress = UM()->Profile_Completeness_API()->get_progress( $user_id );
		if ( $get_progress == -1 ) {
			return false;
		}

		$output['bar'] = '<span class="um-completeness-bar um-tip-n" title="' . sprintf( __( '%s Complete', 'um-profile-completeness' ), $get_progress['progress'] . '%' ) . '">';

		if ($get_progress['progress'] == 100) {
			$radius = '999px !important';
		} else {
			$radius = '999px 0 0 999px';
		}

		$output['bar'] .= '<span class="um-completeness-done" style="width: ' . $get_progress['progress'] . '%;border-radius: ' . $radius . '"></span>';

		for ( $i = 0; $i <= 9; $i++ ) {
			$left = $i * 10;
			$output['bar'] .= '<span class="um-completeness-i" style="left: ' . $left . '%;"></span>';
		}

		$output['bar'] .= '</span>';

		$output['progress'] = $get_progress['progress'];
		$output['steps'] = $get_progress['steps'];
		$output['completed'] = ( isset( $get_progress['completed'] ) ) ? $get_progress['completed'] : '';
		$output['req_progress'] = $get_progress['req_progress'];
		$output['prevent_browse'] = $get_progress['prevent_browse'];
		$output['prevent_browse_exclude_pages'] = $get_progress['prevent_browse_exclude_pages'];
		$output['prevent_profileview'] = $get_progress['prevent_profileview'];
		$output['prevent_comment'] = $get_progress['prevent_comment'];
		$output['prevent_bb'] = $get_progress['prevent_bb'];
		$output['raw'] = $get_progress;
		return $output;
	}
}