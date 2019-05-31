<?php
namespace um_ext\um_instagram\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Instagram_Public
 * @package um_ext\um_instagram\core
 *
 * @since      1.0.0
 */
class Instagram_Public {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		//locale
		add_action( 'plugins_loaded', array( &$this, 'load_plugin_textdomain' ) );

		// Assets
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );

		add_action( 'um_after_user_updated', array( &$this, 'user_after_updating_profile' ), 10, 3 );
		add_filter( 'um_edit_field_profile_instagram_photo', array( &$this, 'edit_field_profile_instagram_photo' ), 9.120, 2 );
		add_filter( 'um_view_field_value_instagram_photo', array( &$this, 'view_field_profile_instagram_photo' ), 10, 2 );
		add_filter( 'body_class', array( &$this, 'body_class' ), 999, 1 );

		add_action( 'wp_ajax_um_instagram_get_photos', array( $this, 'ajax_get_photos' ) );
		add_action( 'wp_ajax_nopriv_um_instagram_get_photos', array( $this, 'ajax_get_photos' ) );

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
		load_textdomain( um_instagram_textdomain, WP_LANG_DIR . '/plugins/' . um_instagram_textdomain . '-' . $locale . '.mo' );
		load_plugin_textdomain( um_instagram_textdomain, false, um_instagram_path . '/languages/' );
	}


	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( $this->is_enabled() ) {
			wp_enqueue_script( 'um_instagram', um_instagram_url . 'assets/js/um-instagram-public.js', array( 'jquery', 'wp-util' ), um_instagram_version, false );

			$translation_array = array(
				'image_loader' => um_url.'/assets/img/loading-dots.gif',
			);

			wp_localize_script( 'um_instagram', 'um_instagram', $translation_array );

			wp_enqueue_style( 'um_instagram', um_instagram_url . 'assets/css/um-instagram-public.css', array(), um_instagram_version, 'all' );
		}
	}


	/**
	 * @return string
	 */
	function nav_template() {
		ob_start(); ?>

		<div class="um-ig-photo-navigation">
			<a href="javascript:void(0);" class="nav-left nav-show">
				<i class="um-faicon-arrow-left"></i>
			</a>
			<a href="javascript:void(0);" class="nav-right nav-show">
				<i class="um-faicon-arrow-right"></i>
			</a>
		</div>

		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * @return string
	 */
	function paginate_template() {
		ob_start(); ?>

		<div class="um-ig-paginate">
			<span>0/0</span>
		</div>

		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * Customize instagram photo field in profile edit
	 * filter hook: um_edit_field_profile_instagram_photo
	 * 
	 * @since 1.0.0
	 * @param string $output
	 * @param array $data
	 * @return string
	 */
	public function edit_field_profile_instagram_photo( $output, $data ) {

		if ( ! $this->is_enabled() ) {
			return '';
		}

		if ( UM()->Instagram_API()->connect()->is_session_started() === false ) {
			session_start();
		}

		$has_token = UM()->Instagram_API()->connect()->get_user_token( $data['metakey'] );
		if ( ! $has_token ) {
			$has_token = get_user_meta( um_user('ID'), $data['metakey'], true );
		}

		ob_start(); ?>

		<div class="um-field um-field-<?php echo $data['type'] ?>" data-key="<?php echo $data['metakey'] ?>">

			<?php $label = ! empty( $data['label'] ) ? $data['label'] : '';
			UM()->fields()->field_label( $label, $data['metakey'], $data );

			if ( $has_token ) { ?>
				<a href="javascript:void(0);" class="um-ig-photos_disconnect">
					<i class="um-faicon-times"></i><?php _e( 'Disconnect', 'um-instagram' ) ?>
				</a>
				<div class="um-clear"></div>
				<div id="um-ig-content" class="um-ig-content">
					<div id="um-ig-photo-wrap" class="um-ig-photos" data-metakey="<?php echo $data['metakey'] ?>" data-viewing="false">
						<?php echo $this->get_user_photos( $has_token, false ); ?>
					</div>
					<?php echo $this->nav_template() ?>
					<div class="um-clear"></div>
					<?php echo $this->get_user_details( $has_token ); ?>
					<?php echo $this->paginate_template() ?>
				</div>
				<div id="um-ig-preload"></div>
				<div class="um-clear"></div>
				<input type="hidden" class="um-ig-photos_metakey" name="<?php echo $data['metakey'] ?>" value="<?php echo $has_token ?>"/>

			<?php } else { ?>

				<div class="um-connect-instagram">
					<div class="um-ig-photo-wrap">
						<div class="um-clear"></div>
						<a href="<?php echo UM()->Instagram_API()->connect()->connect_url() ?>">
							<i class="um-faicon-instagram"></i>
							<div class="um-clear"></div>
							<?php _e( 'Connect to Instagram','um-instagram' ); ?>
						</a>
					</div>
				</div>

			<?php } ?>

		</div>

		<?php $output = ob_get_clean();

		return $output;
	}


	/**
	 * Customize instagram photo in profile view
	 * @param  string $output 
	 * @param  array $data   
	 * @return string
	 */
	public function view_field_profile_instagram_photo( $output, $data ) {

		if ( ! $this->is_enabled() ) {
			add_filter( 'um_instagram_photo_form_show_field', array( &$this, 'instagram_photo_form_show_field' ), 99, 2 );
			return $output;
		}

		$has_token = UM()->Instagram_API()->connect()->get_user_token( $data['metakey'] );
		if ( ! $has_token ) {
			return $output;
		}

		ob_start(); ?>

		<div class="um-clear"></div>
		<div id="um-ig-content" class="um-ig-content">
			<div id="um-ig-photo-wrap" class="um-ig-photos" data-metakey="<?php echo $data['metakey'] ?>" data-viewing="true"></div>
			<?php echo $this->nav_template(); ?>
			<div class="um-clear"></div>
			<?php echo $this->get_user_details( $has_token ) ?>
			<?php echo $this->paginate_template() ?>
		</div>
		<div id="um-ig-preload"></div>
		<div class="um-clear"></div>

		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * Get user Instagram photos
	 *
	 * @param string $access_token
	 * @param bool $viewing
	 * @return string
	 */
	public function get_user_photos( $access_token, $viewing = true ) {

		$response = wp_remote_get( 'https://api.instagram.com/v1/users/self/media/recent/?access_token='.$access_token.'&count=18' );

		if ( empty( $response['body'] ) ) {
			return '';
		}

		$photos = json_decode( $response['body'] );

		if ( ! isset( $photos->data ) ) {
			return '';
		}

		ob_start();

		$photos_count = count( $photos->data ); ?>

		<ul id="um-ig-show_photos" data-viewing="<?php echo $viewing ?>" data-photos-count="<?php echo $photos_count ?>">
			<?php foreach ( $photos->data as $photo ) {
				$standard_resolution = $photo->images->standard_resolution->url;
				$thumb = $photo->images->thumbnail->url; ?>

				<li>
					<a class="um-photo-modal" href="<?php echo $standard_resolution ?>" data-src="<?php echo $standard_resolution ?>">
						<img class="um-lazy" src="<?php echo $thumb ?>" data-original="<?php echo $standard_resolution ?>" />
					</a>
				</li>
			<?php }

			for ( $a = 1; $a <= ( 18 - $photos_count ); $a++ ) { ?>
				<li class="um-ig-photo-placeholder"></li>

				<?php if ( $photos_count < 6 && $a == ( 6 - $photos_count ) ) {
					break;
				}

				if ( $photos_count < 12 && $a == ( 12 - $photos_count ) ) {
					break;
				}
			} ?>
		</ul>

		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * Get instagram user details
	 * @param  string $access_token
	 * @return string
	 * 
	 * @since  1.0.0
	 */
	public function get_user_details( $access_token ) {

		$response = wp_remote_get( 'https://api.instagram.com/v1/users/self/?access_token=' . $access_token );

		if ( empty( $response['body'] ) ) {
			return '';
		}

		$user = json_decode( $response['body'] );
		if ( ! isset( $user->data ) ) {
			return '';
		}

		ob_start(); ?>

		<span class="um-ig-user-details">
			<a href="https://instagram.com/<?php echo $user->data->username ?>/">
				<i class="um-faicon-instagram"></i>&nbsp;
				<span><?php _e( "View all photos on Instagram","um-instagram" ) ?></span>
			</a>
		</span>

		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * Remove IG code from the url
	 * @param array $args
	 * @since 1.0.0
	 */
	public function user_after_updating_profile( $user_id, $args, $to_update ) {
		$flush_option = false;
		$fields = UM()->builtin()->all_user_fields;
		$submitted_fields = array_keys( $args['submitted'] );

		foreach ( $submitted_fields as $key ) {
			if ( isset( $fields[ $key ]['type'] ) && 'instagram_photo' == $fields[ $key ]['type'] ) {
				if ( empty( $args['submitted'][ $key ] ) ) {
					$flush_option = true;
				}
			}
		}

		if ( $flush_option ) {
			delete_user_meta( $user_id, 'um_instagram_code' );
		}
	}


	/**
	 * Get Instagram photos via Ajax
	 * @since  1.0.0
	 */
	public function ajax_get_photos() {
		
		if ( ! $this->is_enabled() ) {
			return;
		}

		$data = $_REQUEST;

		$access_token = UM()->Instagram_API()->connect()->get_user_token( $data['metakey'], $data['um_user_id'] );
		$response = array();

		if ( $access_token ) {
			$photos = $this->get_user_photos( $access_token, $data['viewing'] );
			if( ! empty( $photos ) ){
				$response['photos'] = $photos;
				$response['has_photos'] = true;
				$response['has_error'] = false;
			}else{
				$response['photos'] = '';
				$response['has_photos'] = false;
				$response['has_error'] = true;
				$response['error_code'] = 'no_photos_found';
			}
		}else{
			$response['has_error'] = true;
			$response['photos'] = '';
			$response['error_code'] = 'no_access_token';
		}

		$response['raw_request'] = $_REQUEST;

		return wp_send_json( $response );
	}


	/**
	 * Add body class
	 * @param  array $classes 
	 * @return array
	 * 
	 * @since  1.0.0
	 */
	public function body_class( $classes ) {

		if ( ! $this->is_enabled() ) {
			return $classes;
		}

		if ( um_is_core_page('user') ) {
			$classes[] = 'um-profile-id-' . um_get_requested_user();
		}

		return $classes;
	}


	/**
	 * Checks Instagram extension enable
	 * @return boolean 
	 * @since  1.0.1
	 */
	public function is_enabled() {
		$enable_instagram_photo = UM()->options()->get( 'enable_instagram_photo' );

		if ( $enable_instagram_photo ) {
			return true;
		}
		
		return false;
	}


	/**
	 * Hide instagram field
	 * @param  string $output    
	 * @param  string $form_mode
	 * @return boolean
	 * @since  1.0.1
	 */
	public function instagram_photo_form_show_field( $output, $form_mode ) {
		return '';
	}

}