<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add a message button to directory
 *
 * @param $user_id
 * @param $args
 */
function um_messaging_button_in_directory( $user_id, $args ) {
	if ( isset( $args['show_pm_button'] ) && ! $args['show_pm_button'] ) {
		return;
	}

	if ( is_user_logged_in() ) {
		if ( $user_id == get_current_user_id() ) {
			if ( ! UM()->Messaging_API()->api()->can_message( $user_id ) ) {
				return;
			}

			wp_enqueue_script( 'um-messaging' );
			wp_enqueue_style( 'um-messaging' );

			$messages_link = add_query_arg( 'profiletab', 'messages', um_user_profile_url() );
			echo '<a href="' . esc_attr( $messages_link ) . '" class="um-message-abtn um-button"><span>' . __( 'My messages', 'um-messaging' ) . '</span></a>';
		} else {
			echo do_shortcode('[ultimatemember_message_button user_id="'.$user_id.'"]');
		}

	} else {
		echo do_shortcode('[ultimatemember_message_button user_id="'.$user_id.'"]');
	}
}
add_action( 'um_members_just_after_name', 'um_messaging_button_in_directory', 110, 2 );


/**
 * Open modal if $_SESSION is not empty
 */
function um_messaging_open_modal() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( ! isset( $_SESSION["um_messaging_message_to"] ) ) {
		return;
	} ?>

	<script type="text/javascript">
		jQuery('document').ready( function(){
			<?php $message_to = $_SESSION["um_messaging_message_to"]; ?>
			setTimeout( function(){
				if ( jQuery('.um-message-btn[data-message_to="<?php echo $message_to; ?>"]').length ) {
					jQuery('.um-message-btn[data-message_to="<?php echo $message_to; ?>"]')[0].click();
				}
			},1000) ;

		});
	</script>

	<?php unset( $_SESSION["um_messaging_message_to"] );
}
add_action( 'wp_footer', 'um_messaging_open_modal' );


/**
 * Delete messages on user delete
 *
 * @param $user_id
 */
function um_delete_user_messages( $user_id ) {
	//Update with delete old messages conversations
	global $wpdb;

	$conversation_ids = wp_cache_get( "um_all_conversations:$user_id", 'um_messaging' );
	if ( false === $conversation_ids ) {
		$conversation_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT conversation_id
			FROM {$wpdb->prefix}um_conversations
			WHERE user_a = %d OR
				  user_b = %d",
			$user_id,
			$user_id
		) );
		wp_cache_set( "um_all_conversations:$user_id", $conversation_ids, 'um_messaging' );
	}

	$wpdb->query( $wpdb->prepare(
		"DELETE
		FROM {$wpdb->prefix}um_conversations
		WHERE user_a = %d OR
			  user_b = %d",
		$user_id,
		$user_id
	) );

	wp_cache_delete( "um_all_conversations:$user_id", 'um_messaging' );
	wp_cache_delete( "um_conversations:$user_id", 'um_messaging' );
	wp_cache_delete( "um_conversations:all", 'um_messaging' );

	$wpdb->query( $wpdb->prepare(
		"DELETE
		FROM {$wpdb->prefix}um_messages
		WHERE recipient = %d OR
			  author = %d",
		$user_id,
		$user_id
	) );

	if ( ! empty( $conversation_ids ) ) {
		foreach ( $conversation_ids as $id ) {
			wp_cache_delete( "um_conversation_messages_limit:{$id}", 'um_messaging' );
			wp_cache_delete( "um_new_messages:{$id}", 'um_messaging' );
			wp_cache_delete( "um_conversation_messages:{$id}", 'um_messaging' );
			wp_cache_delete( "um_unread_messages:{$id}:{$user_id}", 'um_messaging' );
		}
	}
	wp_cache_delete( "um_unread_messages:$user_id", 'um_messaging' );
	wp_cache_delete( "um_messages:$user_id", 'um_messaging' );
	wp_cache_delete( "um_messages:all", 'um_messaging' );
}
add_action( 'um_delete_user', 'um_delete_user_messages', 10, 1 );


/**
 * @param $user_id
 */
function remove_error_form_cookie( $user_id ) {
	if ( isset( $_COOKIE['um_messaging_invite_login'] ) ) {
		unset( $_COOKIE['um_messaging_invite_login'] );
		setcookie( "um_messaging_invite_login", null, -1, '/' );
	}
}
add_action( 'um_on_login_before_redirect', 'remove_error_form_cookie' );


/**
 * @param $data
 */
function add_error_form_cookie( $data ) {
	if ( ! empty( $_POST ) ) {
		setcookie( "um_messaging_invite_login", json_encode( $_POST ), time()+3600, '/' );
	}
}
add_action( 'um_user_login_extra_hook', 'add_error_form_cookie' );


/**
 * Insert Login form to hidden block
 *
 * @param array $args
 */
function um_members_directory_login_form_footer( $args ) {
	if ( is_user_logged_in() || empty( $args['show_pm_button'] ) ) {
		return;
	}

	wp_enqueue_script( 'um-messaging' );
	wp_enqueue_style( 'um-messaging' );

	if ( ! empty( $_COOKIE['um_messaging_invite_login'] ) ) {
		$_POST = array_merge( json_decode( wp_unslash( $_COOKIE['um_messaging_invite_login'] ), true ), $_POST );
		UM()->form()->form_init();
	}

	add_filter( 'um_browser_url_redirect_to__filter', array( UM()->Messaging_API()->api(), 'set_redirect_to' ), 10, 1 ); ?>

	<div class="um_messaging_hidden_login" style="display: none;">
		<?php echo do_shortcode( '[ultimatemember form_id="' . UM()->shortcodes()->core_login_form() . '"]' ); ?>
	</div>

	<?php
}
add_action( 'um_members_directory_footer', 'um_members_directory_login_form_footer', 99, 1 );