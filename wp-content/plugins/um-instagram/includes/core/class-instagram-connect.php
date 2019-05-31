<?php namespace um_ext\um_instagram\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * class Instagram_Connect
 * 
 * @since  1.0.0
 */
class Instagram_Connect {

	/**
	 * @var int
	 */
	public $client_id;


	/**
	 * @var string
	 */
	public $client_secret;


	/**
	 * @var string
	 */
	public $callback_url;


	/**
	 * @var
	 */
	//public $login_url;


	/**
	 * @var int
	 */
	public $auth_called = 0;


	/**
	 *  init
	 * 
	 * @since  1.0.0
	 */
	function __construct() {
		add_action( 'template_redirect', array( &$this, 'load' ), 99 );
		add_action( 'template_redirect', array( &$this, 'get_auth' ), 100 );
	}


	/**
	 * @param $api_data
	 *
	 * @return \Instagram
	 */
	function call_API( $api_data ) {
		if ( ! class_exists( '\Instagram' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libs/api/Instagram.php';
		}

		return new \Instagram( $api_data );
	}


	/**
	 * Prepare variables
	 * action hook: template_redirect
	 * 
	 * @since  1.0.0
	 */
	function load() {
		$this->client_id = UM()->options()->get( 'instagram_photo_client_id' );
		$this->client_secret = UM()->options()->get( 'instagram_photo_client_secret' );

		$url = add_query_arg( 'um-connect-instagram', 'true', site_url( '/' ) );
		$this->callback_url = apply_filters( "um_instagram_callback_url", $url );
	}


	/**
	 * Get authorization callback response
	 * action hook: template_redirect
	 * 
	 * @since  1.0.0
	 */
	function get_auth() {

		if ( isset( $_REQUEST['um-connect-instagram'] ) && $_REQUEST['um-connect-instagram'] == 'true' && isset( $_REQUEST['code'] ) && $this->auth_called == 0 ) {
			
			if ( $this->is_session_started() === false ) {
				session_start();
			}

			$instagram = $this->call_API( array(
				'apiKey'      => $this->client_id,
				'apiSecret'   => $this->client_secret,
				'apiCallback' => $this->callback_url
			));
			
			if ( isset( $_SESSION['insta_access_token'] ) ) {
				$token = $_SESSION['insta_access_token'];
			} else {
				$code = $_REQUEST['code'];
				$data = $instagram->getOAuthToken( $code );
				$token = $data->access_token;
				$_SESSION['insta_access_token'] = $token;
			}

			if ( ! empty( $token ) ) {
				update_user_meta( um_user('ID'), 'um_instagram_code', $token );
				unset( $_SESSION['insta_access_token'] );

				$profile_url = add_query_arg( array( 'profiletab' => 'main', 'um_action' => 'edit'/*, 'um_ig_code' => $code*/ ), um_user_profile_url() );
				wp_redirect( $profile_url );
			}

			$this->auth_called++;
		}
	}


	/**
	 * Get Authorization URL
	 * @return string Login url for App authorization
	 * 
	 * @since  1.0.0
	 */
	function connect_url() {
		$instagram = $this->call_API( array(
			'apiKey'      => $this->client_id,
			'apiSecret'   => $this->client_secret,
			'apiCallback' => $this->callback_url
		));

		return $instagram->getLoginUrl();
	}


	/**
	 * Get current user's access token
	 *
	 * @param  string $metakey field meta key
	 * @param  int $user_id User ID
	 * @return string | boolean  returns token strings on success, otherwise return false when empty token
	 * 
	 * @since  1.0.0
	 */
	function get_user_token( $metakey = '', $user_id = 0 ) {
		$token = false;

		if ( $this->is_session_started() === false ) {
			session_start();
		}

		if ( empty( $user_id ) ) {
			$user_id = um_user('ID');

			if ( empty( $user_id ) ) {
				return false;
			}
		}

		$token = get_user_meta( $user_id, $metakey, true );

		$um_instagram_code = apply_filters( 'um_instagram_code_in_user_meta', true );
		if ( ! $token && $um_instagram_code ) {
			$token = get_user_meta( um_user('ID'), 'um_instagram_code', true );
		}

		return $token;
	}


	/**
	 * Checks if session has been started
	 * @return bool
	 */
	function is_session_started() {
		if ( php_sapi_name() !== 'cli' ) {
			return session_status() === PHP_SESSION_ACTIVE ? true : false;
		}

		return false;
	}
}