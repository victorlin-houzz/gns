<?php
namespace um_ext\um_notifications\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notifications_Shortcode
 * @package um_ext\um_notifications\core
 */
class Notifications_Shortcode {


	/**
	 * Notifications_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_notifications', array( &$this, 'ultimatemember_notifications' ) );
		add_shortcode( 'ultimatemember_notification_count', array( &$this, 'ultimatemember_notification_count' ) );
		
		add_filter( 'wp_title', array( &$this, 'wp_title' ), 10, 2 );
	}


	/**
	 * Custom title for page
	 *
	 * @param $title
	 * @param null $sep
	 *
	 * @return string
	 */
	function wp_title( $title, $sep=null ) {
		global $post;
		if ( isset( $post->ID ) && $post->ID == UM()->permalinks()->core['notifications'] ) {
			$unread = UM()->Notifications_API()->api()->get_notifications( 0, 'unread', true );
			if ( $unread ){
				$title = "($unread) $title";
			}
		}
		return $title;
	}


	/**
	 * Notifications list shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_notifications( $args = array() ) {
		if ( ! is_user_logged_in() ) {
			exit( wp_redirect( home_url() ) );
		}

		wp_enqueue_script( 'um_notifications' );
		wp_enqueue_style( 'um_notifications' );

		$has_notifications = UM()->Notifications_API()->api()->get_notifications( 1 );
		if ( ! $has_notifications ) {
			$template = 'no-notifications';
		} else {
			$notifications = UM()->Notifications_API()->api()->get_notifications( 50 );
			$template = 'notifications';
		}

		$file = str_replace( '/', DIRECTORY_SEPARATOR, um_notifications_path . "templates/{$template}.php" );
		$theme_file = str_replace( '/', DIRECTORY_SEPARATOR, get_stylesheet_directory() . "/ultimate-member/templates/notifications/{$template}.php" );

		if ( file_exists( $theme_file ) ) {
			$file = $theme_file;
		}

		ob_start(); ?>

		<div class="um-notification-shortcode">
			<?php require $file; ?>
		</div>

		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return int
	 */
	function ultimatemember_notification_count( $args = array() ) {
		wp_enqueue_script( 'um_notifications' );
		wp_enqueue_style( 'um_notifications' );

		$count = UM()->Notifications_API()->api()->unread_count( get_current_user_id() );
		return (int) $count;
	}

}