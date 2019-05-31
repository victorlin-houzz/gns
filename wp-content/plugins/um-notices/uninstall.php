<?php
/**
 * Uninstall UM Notices
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_notices_path' ) )
    define( 'um_notices_path', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'um_notices_url' ) )
    define( 'um_notices_url', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'um_notices_plugin' ) )
    define( 'um_notices_plugin', plugin_basename( __FILE__ ) );

$options = get_option( 'um_options' );
$options = empty( $options ) ? array() : $options;

if ( ! empty( $options['uninstall_on_delete'] ) ) {
    if ( ! class_exists( 'um_ext\um_notices\core\Notices_Setup' ) )
        require_once um_notices_path . 'includes/core/class-notices-setup.php';

    $notices_setup = new um_ext\um_notices\core\Notices_Setup();

    //remove settings
    foreach ( $notices_setup->settings_defaults as $k => $v ) {
        unset( $options[$k] );
    }

    unset( $options['um_notices_license_key'] );

    update_option( 'um_options', $options );
}