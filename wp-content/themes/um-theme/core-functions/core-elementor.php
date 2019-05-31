<?php
/**
 * Elementor Page Builder Support
 *
 * @package 	um-theme
 * @subpackage 	Elementor
 * @link      	https://wordpress.org/plugins/elementor/
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Register Theme Location API.
add_action( 'elementor/theme/register_locations', 'um_theme_register_elementor_locations' );

/*
 * Registering Elementor Theme Location API locations
 */
if ( ! function_exists( 'um_theme_register_elementor_locations' ) ) {
	function um_theme_register_elementor_locations( $elementor_theme_manager ) {
		$elementor_theme_manager->register_all_core_location();
	}
}
