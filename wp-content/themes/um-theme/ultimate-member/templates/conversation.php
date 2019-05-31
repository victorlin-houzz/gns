<?php if ( ! defined( 'ABSPATH' ) ) exit;

UM()->Messaging_API()->api()->perms = UM()->Messaging_API()->api()->get_perms( get_current_user_id() );

um_fetch_user( $message_to );
$contact_name = ( um_user( 'display_name' ) ) ? um_user( 'display_name' ) : __( 'Deleted User', 'um-messaging' );
$contact_url = um_user_profile_url();

$limit = UM()->options()->get( 'pm_char_limit' );

um_fetch_user( $user_id );

$response = UM()->Messaging_API()->api()->get_conversation_id( $message_to, $user_id ); ?>

<div class="um-message-header um-popup-header">
	<div class="um-message-header-left">
		<?php echo get_avatar( $message_to, 40 ); ?>
		<a href="<?php echo esc_attr( um_user_profile_url() ) ?>"><?php echo $contact_name ?></a>
	</div>
	<div class="um-message-header-right">
		<a href="#" class="um-message-blocku um-tip-e" title="<?php esc_attr_e( 'Block user', 'um-messaging' ); ?>" data-other_user="<?php echo $message_to; ?>" data-conversation_id="<?php echo ! empty( $response['conversation_id'] ) ? $response['conversation_id'] : 'new'; ?>"><i class="um-faicon-ban"></i></a>
		<a href="#" class="um-message-delconv um-tip-e"
		   title="<?php esc_attr_e( 'Delete conversation', 'um-messaging' ); ?>"
		   data-other_user="<?php echo $message_to; ?>"
		   data-conversation_id="<?php echo ! empty( $response['conversation_id'] ) ? $response['conversation_id'] : 'new'; ?>"
		   <?php if ( empty( $response ) ) { ?>style="display:none;"<?php } ?>>
			<i class="um-icon-trash-b"></i>
		</a>
		<a href="#" class="um-message-hide um-tip-e" title="<?php esc_attr_e( 'Close chat', 'um-messaging' ); ?>"><i class="um-icon-android-close"></i></a>

		<?php do_action( 'um_messaging_after_conversation_links', $message_to, $user_id ); ?>
	</div>
</div>

<div class="um-message-body um-popup-autogrow um-message-autoheight" data-message_to="<?php echo $message_to; ?>">
	<div class="um-message-ajax" data-message_to="<?php echo $message_to; ?>" data-conversation_id="<?php echo ! empty( $response['conversation_id'] ) ? $response['conversation_id'] : 'new'; ?>" data-last_updated="<?php echo ! empty( $response['last_updated'] ) ? $response['last_updated'] : ''; ?>">

		<?php if ( UM()->Messaging_API()->api()->perms['can_read_pm'] || UM()->Messaging_API()->api()->perms['can_start_pm'] ) {

			if ( ! empty( $response['conversation_id'] ) ) {
				echo UM()->Messaging_API()->api()->get_conversation( $message_to, $user_id, $response['conversation_id'] );
			}

		} else { ?>

			<span class="um-message-notice">
				<?php esc_html_e( 'Your membership level does not allow you to view conversations.', 'um-messaging' ) ?>
			</span>

		<?php } ?>
	</div>
</div>

<?php if ( ! empty( $response ) ) {
	global $wpdb;
	$other_message = $wpdb->get_var( $wpdb->prepare(
		"SELECT message_id
		FROM {$wpdb->prefix}um_messages
		WHERE conversation_id = %d AND
			  author = %d
		ORDER BY time ASC
		LIMIT 1",
		$response['conversation_id'],
		$message_to
	) );
}

if ( ! UM()->Messaging_API()->api()->can_message( $message_to ) ) {

	esc_html_e( 'You are blocked and not allowed continue this conversation.', 'um-messaging' );

} else { ?>

	<div class="um-message-footer um-popup-footer" data-limit_hit="<?php esc_attr_e( 'You have reached your limit for sending messages.', 'um-messaging' ); ?>" >

		<?php if ( UM()->Messaging_API()->api()->limit_reached() ) {

			esc_html_e( 'You have reached your limit for sending messages.', 'um-messaging' );

		} elseif ( ( UM()->Messaging_API()->api()->perms['can_reply_pm'] && ! empty( $response ) ) ||
		           ( UM()->Messaging_API()->api()->perms['can_start_pm'] && ( empty( $response ) || empty( $other_message ) ) ) ) { ?>

		           	<div class="boot-row">

		           		<div class="boot-col-md-9">
							<div class="um-message-textarea">
								<?php echo UM()->Messaging_API()->api()->emoji(); ?>
								<textarea name="um_message_text" class="um_message_text" data-maxchar="<?php echo $limit; ?>" placeholder="<?php esc_attr_e( 'Type your message...', 'um-messaging' ); ?>"></textarea>
							</div>
							<span class="um-message-limit"><?php echo $limit; ?></span>
		           		</div>

		           		<div class="boot-col-md-3 um-send-messeage">
							<div class="um-message-buttons">

								<a href="javascript:void(0);" class="um-message-send disabled">
									<i class="um-faicon-envelope-o"></i>
									<?php esc_html_e( 'Send message', 'um-messaging' ); ?>
								</a>
							</div>
		           		</div>
		           	</div>





			<div class="um-clear"></div>

		<?php } elseif ( ! ( UM()->Messaging_API()->api()->perms['can_start_pm'] && ( empty( $response ) || empty( $other_message ) ) ) ) {
			esc_html_e( 'You are not allowed to start conversations.', 'um-messaging' );
		} else {
			esc_html_e( 'You are not allowed to reply to private messages.', 'um-messaging' );
		} ?>

	</div>
<?php }