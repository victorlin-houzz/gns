<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add stats to member directory
 *
 * @param $user_id
 * @param $args
 */
function um_followers_follow_button_in_directory( $user_id, $args ) {
	wp_enqueue_style( 'um_followers' );
	wp_enqueue_script( 'um_followers' );

	$can_view = true;

	if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
		$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Followers', 'um-followers' ) );
		$is_private_case = UM()->user()->is_private_case( $user_id, 'follower' );
		if ( $is_private_case || $is_private_case_old ) { // only followers can view my profile
			$can_view = false;
		}
		$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Only people I follow can view my profile', 'um-followers' ) );
		$is_private_case = UM()->user()->is_private_case( $user_id, 'followed' );
		if ( $is_private_case || $is_private_case_old ) { // only people i follow can view my profile
			$can_view = false;
		}
	}

	if ( UM()->options()->get( 'followers_show_stats' ) && $can_view ) { ?>
		<div class="um-members-follow-stats">
			<div><?php echo UM()->Followers_API()->api()->count_followers( $user_id ); ?><?php _e( 'followers', 'um-followers' ); ?></div>
			<div><?php echo UM()->Followers_API()->api()->count_following( $user_id ); ?><?php _e( 'following', 'um-followers' ); ?></div>
		</div>
	<?php }


	if ( UM()->options()->get( 'followers_show_button' ) ) {
		$btn = UM()->Followers_API()->api()->follow_button( $user_id, get_current_user_id() );

		if ( $btn ) {
			echo '<div class="um-members-follow-btn">' . $btn . '</div>';
		}
	}
}
add_action( 'um_members_just_after_name', 'um_followers_follow_button_in_directory', 99, 2 );