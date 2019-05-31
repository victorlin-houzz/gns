<?php
/**
 * Uninstall UM User Photos
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_user_photos_path' ) ) {
	define( 'um_user_photos_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_user_photos_url' ) ) {
	define( 'um_user_photos_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_user_photos_plugin' ) ) {
	define( 'um_user_photos_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_user_photos\core\User_Photos_Setup' ) ) {
		require_once um_user_photos_path . 'includes/core/class-user-photos-setup.php';
	}

	$user_photos_setup = new um_ext\um_user_photos\core\User_Photos_Setup();

	//remove settings
	foreach ( $user_photos_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_user_photos_license_key'] );

	update_option( 'um_options', $options );
}