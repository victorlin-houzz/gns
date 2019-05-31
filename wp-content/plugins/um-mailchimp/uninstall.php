<?php
/**
 * Uninstall UM Mailchimp
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_mailchimp_path' ) )
    define( 'um_mailchimp_path', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'um_mailchimp_url' ) )
    define( 'um_mailchimp_url', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'um_mailchimp_plugin' ) )
    define( 'um_mailchimp_plugin', plugin_basename( __FILE__ ) );

$options = get_option( 'um_options' );
$options = empty( $options ) ? array() : $options;

if ( ! empty( $options['uninstall_on_delete'] ) ) {
    if ( ! class_exists( 'um_ext\um_mailchimp\core\Mailchimp_Setup' ) )
        require_once um_mailchimp_path . 'includes/core/class-mailchimp-setup.php';

    $mailchimp_setup = new um_ext\um_mailchimp\core\Mailchimp_Setup();

    //remove settings
    foreach ( $mailchimp_setup->settings_defaults as $k => $v ) {
        unset( $options[$k] );
    }

    unset( $options['um_mailchimp_license_key'] );

    update_option( 'um_options', $options );
}