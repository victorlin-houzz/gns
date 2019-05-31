<?php
/**
 * Uninstall UM Terms Conditions
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


if ( ! defined( 'um_terms_conditions_path' ) )
    define( 'um_terms_conditions_path', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'um_terms_conditions_url' ) )
    define( 'um_terms_conditions_url', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'um_terms_conditions_plugin' ) )
    define( 'um_terms_conditions_plugin', plugin_basename( __FILE__ ) );