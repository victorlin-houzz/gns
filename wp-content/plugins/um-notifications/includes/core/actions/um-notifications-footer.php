<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Show Notifications Bell
 */
function um_notification_show_feed() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$notifications = UM()->Notifications_API()->api()->get_notifications( 10 );
	if ( ! $notifications ) {
		$template = 'no-notifications';
	} else {
		$template = 'notifications';
	}

	$unread = (int)UM()->Notifications_API()->api()->get_notifications( 0, 'unread', true );
	$unread_count = ( absint( $unread ) > 9 ) ? '+9' : $unread;

	$file = str_replace( '/', DIRECTORY_SEPARATOR, um_notifications_path . "templates/{$template}.php" );
	$theme_file = str_replace( '/', DIRECTORY_SEPARATOR, get_stylesheet_directory() . "/ultimate-member/templates/notifications/{$template}.php" );

	if ( file_exists( $theme_file ) ) {
		$file = $theme_file;
	}
	ob_start(); ?>

	<div class="um-notification-b <?php echo esc_attr( UM()->options()->get( 'notify_pos' ) ) ?>"
	     data-show-always="<?php echo esc_attr( UM()->options()->get( 'notification_icon_visibility' ) ) ?>">
		<i class="um-icon-ios-bell"></i>
		<span class="um-notification-live-count count-<?php echo esc_attr( $unread ) ?>">
			<?php echo $unread_count ?>
		</span>
	</div>

	<div class="um-notification-live-feed">
		<div class="um-notification-live-feed-inner">
			<?php include $file; ?>
		</div>
	</div>

	<?php ob_end_flush();
}
add_action( 'wp_footer', 'um_notification_show_feed', 99999999999 );


/**
 *
 */
function um_enqueue_feed_scripts() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	wp_enqueue_script( 'um_notifications' );
	wp_enqueue_style( 'um_notifications' );
}
add_action( 'wp_footer', 'um_enqueue_feed_scripts', -1 );