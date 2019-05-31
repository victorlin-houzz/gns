<?php
/**
 * Uninstall UM Social Activity
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_activity_path' ) )
    define( 'um_activity_path', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'um_activity_url' ) )
    define( 'um_activity_url', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'um_activity_plugin' ) )
    define( 'um_activity_plugin', plugin_basename( __FILE__ ) );

$options = get_option( 'um_options' );
$options = empty( $options ) ? array() : $options;

if ( ! empty( $options['uninstall_on_delete'] ) ) {
    if ( ! class_exists( 'um_ext\um_social_activity\core\Activity_Setup' ) )
        require_once um_activity_path . 'includes/core/class-activity-setup.php';

    $activity_setup = new um_ext\um_social_activity\core\Activity_Setup();

    //remove settings
    foreach ( $activity_setup->settings_defaults as $k => $v ) {
        unset( $options[$k] );
    }

    unset( $options['um_activity_license_key'] );

    update_option( 'um_options', $options );
}