<?php
/**
 * Uninstall UM Profile Completeness
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_profile_completeness_path' ) )
    define( 'um_profile_completeness_path', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'um_profile_completeness_url' ) )
    define( 'um_profile_completeness_url', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'um_profile_completeness_plugin' ) )
    define( 'um_profile_completeness_plugin', plugin_basename( __FILE__ ) );

$options = get_option( 'um_options' );
$options = empty( $options ) ? array() : $options;

if ( ! empty( $options['uninstall_on_delete'] ) ) {

    unset( $options['um_profile_completeness_license_key'] );

    update_option( 'um_options', $options );
}