<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Profile_Completeness_API
 */
class UM_Profile_Completeness_API {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Profile_Completeness_API
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * UM_Profile_Completeness_API constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_profile_completeness'] = $this;
		add_filter( 'um_call_object_Profile_Completeness_API', array( &$this, 'get_this' ) );

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		}

		$this->enqueue();
		$this->shortcode();
		$this->restrict();

		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );

		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-widget.php';
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );

		add_action( 'wp_ajax_um_profile_completeness_save_popup', array( $this, 'ajax_save_popup' ) );
		add_action( 'wp_ajax_um_profile_completeness_edit_popup', array( $this, 'ajax_edit_popup' ) );
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_profile_completeness_enqueue'] ) ) {
			UM()->classes['um_profile_completeness_enqueue'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Enqueue();
		}

		return UM()->classes['um_profile_completeness_enqueue'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_profile_completeness_shortcode'] ) ) {
			UM()->classes['um_profile_completeness_shortcode'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Shortcode();
		}

		return UM()->classes['um_profile_completeness_shortcode'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_profile_completeness_admin'] ) ) {
			UM()->classes['um_profile_completeness_admin'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Admin();
		}

		return UM()->classes['um_profile_completeness_admin'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Restrict()
	 */
	function restrict() {
		if ( empty( UM()->classes['um_profile_completeness_restrict'] ) ) {
			UM()->classes['um_profile_completeness_restrict'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Restrict();
		}

		return UM()->classes['um_profile_completeness_restrict'];
	}


	/**
	 * Init
	 */
	function init() {
		delete_user_meta( 1, 'birthdate' );

		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-profile.php';
		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-fields.php';
		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-directory.php';
	}


	/**
	 * Get factors that increase completion
	 *
	 * @param $role_data
	 *
	 * @return array|bool
	 */
	function get_metrics( $role_data ) {
		$array = array();
		$meta = $role_data;
		foreach ( $meta as $k => $v ) {
			if ( strstr( $k, 'progress_' ) ) {
				$k = str_replace( 'progress_', '', $k );
				if ( $k == 'profile_photo' ) {

					if( um_user( 'profile_photo' ) ) {
						$array['profile_photo'] = $v;
					} elseif( um_user( 'synced_profile_photo' ) ) {
						$array['synced_profile_photo'] = $v;
					}

					if ( UM()->options()->get( 'use_gravatars' ) ) {
						$array['synced_gravatar_hashed_id'] = $v;
						continue;
					}

				}
				$array[ $k ] = $v;
			}
		}

		return ! empty( $array ) ? $array : false;
	}


	/**
	 * Get user profile progress
	 *
	 * @param $user_id
	 *
	 * @return array|int
	 */
	function get_progress( $user_id ) {
		um_fetch_user( $user_id );

		//get priority role here
		$role_data = UM()->roles()->role_data( um_user( 'role' ) );
		if ( empty( $role_data['profilec'] ) ) {
			return -1;
		}

		// get factors
		$array = $this->get_metrics( $role_data );
		if ( ! $array ) {
			$result = array(
				'req_progress'        => $role_data['profilec_pct'],
				'progress'            => 100,
				'steps'               => '',
				'prevent_browse'      => $role_data['profilec_prevent_browse'],
				'prevent_browse_exclude_pages' => empty( $role_data['profilec_prevent_browse_exclude_pages'] ) ? '' : $role_data['profilec_prevent_browse_exclude_pages'],
				'prevent_profileview' => $role_data['profilec_prevent_profileview'],
				'prevent_comment'     => $role_data['profilec_prevent_comment'],
				'prevent_bb'          => $role_data['profilec_prevent_bb']
			);

			$result['raw'] = $result;

			update_user_meta( $user_id, '_profile_progress', $result );
			update_user_meta( $user_id, '_completed', 100 );

			return $result;
		}

		// see what user has completed
		$profile_progress = 0;
		$completed = array();
		foreach ( $array as $key => $value ) {
			$custom = apply_filters( 'um_profile_completeness_get_field_progress', false, $key, $user_id );
			if ( $custom ) {
				$profile_progress = $profile_progress + (int)$value;
				$completed[] = $key;
			} else {
				$user_meta = get_user_meta( $user_id, $key, true );
				if ( $user_meta != '' ) {
					$profile_progress = $profile_progress + (int)$value;
					$completed[] = $key;
				} elseif ( in_array( $key, array( 'user_email' ) ) ) {
					$user = get_user_by( 'ID', $user_id );
					if ( ! empty( $user ) && ! empty( $user->user_email ) ) {
						$profile_progress = $profile_progress + (int)$value;
						$completed[] = $key;
					}
				} elseif ( in_array( $key, array( 'user_url' ) ) ) {
					$user = get_user_by( 'ID', $user_id );
					if ( ! empty( $user ) && ! empty( $user->user_url ) ) {
						$profile_progress = $profile_progress + (int)$value;
						$completed[] = $key;
					}
				}
			}
		}

		$result = array(
			'req_progress'        => $role_data['profilec_pct'],
			'progress'            => $profile_progress,
			'steps'               => $array,
			'completed'           => $completed,
			'prevent_browse'      => ( empty( $role_data['profilec_prevent_browse'] ) ? 0 : 1 ),
			'prevent_browse_exclude_pages' => empty( $role_data['profilec_prevent_browse_exclude_pages'] ) ? '' : $role_data['profilec_prevent_browse_exclude_pages'],
			'prevent_profileview' => ( empty( $role_data['profilec_prevent_profileview'] ) ? 0 : 1 ),
			'prevent_comment'     => ( empty( $role_data['profilec_prevent_comment'] ) ? 0 : 1 ),
			'prevent_bb'          => ( empty( $role_data['profilec_prevent_bb'] ) ? 0 : 1 ),
		);
		update_user_meta( $user_id, '_profile_progress', $result );
		update_user_meta( $user_id, '_completed', $profile_progress );

		$profile_percentage = $role_data['profilec_pct'];

		if ( empty( $profile_percentage ) ) {
			$profile_percentage = 100;
		}

		if ( $profile_progress >= $profile_percentage && $role_data['profilec_upgrade_role'] ) {
			$new_role = $role_data['profilec_upgrade_role'];
			um_fetch_user( $user_id );

			$userdata = get_userdata( $user_id );
			$old_roles = $userdata->roles;
			UM()->roles()->set_role( $user_id, $new_role );

			foreach ( $old_roles as $_role ) {
				UM()->roles()->remove_role( $user_id, $_role );
			}

			do_action( 'um_after_member_role_upgrade', array( $new_role ), $old_roles, $user_id );
		}

		$result['raw'] = $result;
		return $result;
	}


	/**
	 *
	 */
	function widgets_init() {
		register_widget( 'um_profile_completeness' );
		register_widget( 'um_profile_progress_bar' );
	}


	/**
	 * @param string $key
	 *
	 * @return string
	 */
	function get_field_title( $key = '' ) {
		$fields_without_metakey = array( 'block', 'shortcode', 'spacing', 'divider', 'group' );
		remove_filter( 'um_fields_without_metakey', 'um_user_tags_requires_no_metakey' );
		$fields_without_metakey = apply_filters( 'um_fields_without_metakey', $fields_without_metakey );

		UM()->builtin()->fields_dropdown = array( 'image', 'file', 'password', 'rating' );
		UM()->builtin()->fields_dropdown = array_merge( UM()->builtin()->fields_dropdown, $fields_without_metakey );

		$custom = UM()->builtin()->custom_fields;
		$predefined = UM()->builtin()->predefined_fields;

		$all = array( 0 => '' );

		if ( is_array( $custom ) ) {
			$all = $all + array_merge( $predefined, $custom );
		} else {
			$all = $all + $predefined;
		}

		$fields = array( 0 => '' ) + $all;

		if ( ! empty( $fields[ $key ]['label'] ) ) {
			return sprintf( __( '%s', 'um-profile-completeness' ), $fields[ $key ]['label'] );
		}

		if ( ! empty( $fields[ $key ]['title'] ) ) {
			return sprintf( __( '%s', 'um-profile-completeness' ), $fields[ $key ]['title'] );
		}

		return __( 'Custom Field', 'um-profile-completeness' );
	}


	/**
	 * Save field over popup
	 */
	function ajax_save_popup() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['key'] ) || ! isset( $_POST['value'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$user_id = get_current_user_id();

		if ( get_user_meta( $user_id, $_POST['key'], true ) &&
			! in_array( $_POST['key'], array( 'profile_photo', 'cover_photo', 'synced_profile_photo' ) ) ) {
			wp_send_json_error();
		}

		if ( strstr( $_POST['value'], ', ' ) ) {
			$_POST['value'] = explode( ', ', $_POST['value'] );
		}

		update_user_meta( $user_id, $_POST['key'], $_POST['value'] );

		delete_option( "um_cache_userdata_{$user_id}" );

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $user_id );
		$output['percent'] = $result['progress'];
		$output['raw'] = $result['raw'];

		wp_send_json_success( $output );
	}


	/**
	 * Edit field over popup
	 */
	function ajax_edit_popup() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['key'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		ob_start();

		um_fetch_user( get_current_user_id() );

		if ( get_user_meta( get_current_user_id(), $_POST['key'], true ) ) {
			wp_send_json_error();
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );

		$data = UM()->builtin()->get_a_field( $_POST['key'] );

		UM()->fields()->disable_tooltips = true;

		$args['profile_completeness'] = true; ?>

		<div class="um-completeness-editwrap" data-key="<?php echo $_POST['key']; ?>">

			<div class="um-completeness-header">
				<?php _e( 'Complete your profile', 'um-profile-completeness' ); ?>
			</div>

			<div class="um-completeness-complete">
				<?php printf( __( 'Your profile is %s complete', 'um-profile-completeness' ), '<span style="color:#3ba1da"><strong><span class="um-completeness-jx">' . $result['progress'] . '</span>%</strong></span>' ); ?>
			</div>

			<div class="um-completeness-bar-holder">
				<?php echo $result['bar']; ?>
			</div>

			<div class="um-completeness-field">
				<?php echo UM()->fields()->edit_field( $_POST['key'], $data, false ,$args ); ?>
			</div>

			<div class="um-completeness-save">
				<a href="#" class="save"><?php _e( 'Save', 'um-profile-completeness' ); ?></a>
				<a href="#" class="skip"><?php _e( 'Do this later', 'um-profile-completeness' ); ?></a>
			</div>

		</div>

		<?php $output = ob_get_clean();
		wp_send_json_success( $output );
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_profile_completeness', -10, 1 );
function um_init_profile_completeness() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Profile_Completeness_API', true );
	}
}