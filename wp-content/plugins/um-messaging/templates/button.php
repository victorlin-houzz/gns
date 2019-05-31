<?php if ( ! defined( 'ABSPATH' ) ) exit;

$current_url = UM()->permalinks()->get_current_url();
if ( um_get_core_page( 'user' ) ) {
	do_action( "um_messaging_button_in_profile", $current_url, $user_id );
}

if ( ! is_user_logged_in() ) {
	$redirect = um_get_core_page( 'login' );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$redirect = add_query_arg( 'redirect_to', urlencode( $_SERVER['HTTP_REFERER'] ), $redirect );
		}
	} else {
		$redirect = add_query_arg( 'redirect_to', $current_url, $redirect );
	} ?>

	<a href="<?php echo esc_attr( $redirect ) ?>" class="um-login-to-msg-btn um-message-btn um-button" data-message_to="<?php echo esc_attr( $user_id ) ?>" title="<?php echo esc_attr( $title ) ?>">
		<?php echo $title ?>
	</a>

<?php } elseif ( $user_id != get_current_user_id() ) { ?>

	<a href="javascript:void(0);" class="um-message-btn um-button" data-message_to="<?php echo esc_attr( $user_id ) ?>" title="<?php echo esc_attr( $title ) ?>">
		<span><?php echo $title ?></span>
	</a>

<?php }