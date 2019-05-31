<?php
/**
 * Uninstall UM Groups
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


if ( ! defined( 'um_groups_path' ) ) {
	define( 'um_groups_path', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'um_groups_url' ) ) {
	define( 'um_groups_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'um_groups_plugin' ) ) {
	define( 'um_groups_plugin', plugin_basename( __FILE__ ) );
}

$options = get_option( 'um_options', array() );

if ( ! empty( $options['uninstall_on_delete'] ) ) {
	if ( ! class_exists( 'um_ext\um_groups\core\Groups_Setup' ) ) {
		require_once um_groups_path . 'includes/core/class-groups-setup.php';
	}

	$groups_setup = new um_ext\um_groups\core\Groups_Setup();

	//remove settings
	foreach ( $groups_setup->settings_defaults as $k => $v ) {
		unset( $options[ $k ] );
	}

	unset( $options['um_groups_license_key'] );

	update_option( 'um_options', $options );
}