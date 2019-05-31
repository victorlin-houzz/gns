<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Hide role
 */
function um_mycred_bb_norole() {
	if ( ! UM()->options()->get( 'mycred_hide_role' ) ) {
		return;
	} ?>

	<style type="text/css">
		div.bbp-author-role {display: none !important}
	</style>

	<?php
}
add_action( 'um_bbpress_theme_after_reply_author_details', 'um_mycred_bb_norole' );


/**
 * Show rank
 */
function um_mycred_bb_rank() {
	if ( ! UM()->options()->get('mycred_show_bb_rank') ) {
		return;
	}
	if ( ! function_exists( 'mycred_get_users_rank' ) ) {
		return;
	}
	$reply_author_id = get_post_field( 'post_author', bbp_get_reply_id() );
	$rank = mycred_get_users_rank( $reply_author_id );

	wp_enqueue_script( 'um_mycred' );
	wp_enqueue_style( 'um_mycred' );

	// If the user has a rank, $rank will be an object
	if ( is_object( $rank ) ) { ?>
		<div class="um-mycred-bb-rank"><?php echo $rank->title ?></div>
	<?php }
}
add_action( 'um_bbpress_theme_after_reply_author_details', 'um_mycred_bb_rank' );


/**
 * Show points
 */
function um_mycred_bb_points() {
	if ( ! UM()->options()->get( 'mycred_show_bb_points' ) ) {
		return;
	}

	wp_enqueue_script( 'um_mycred' );
	wp_enqueue_style( 'um_mycred' );

	$reply_author_id = get_post_field( 'post_author', bbp_get_reply_id() ); ?>

	<div class="um-mycred-bb-points">
		<?php echo UM()->myCRED_API()->get_points( $reply_author_id ); ?>
	</div>

	<?php
}
add_action( 'um_bbpress_theme_after_reply_author_details', 'um_mycred_bb_points' );


/**
 * Show progress
 */
function um_mycred_bb_rank_bar() {
	if ( ! UM()->options()->get('mycred_show_bb_progress') ) {
		return;
	}
		
	if ( ! function_exists('mycred_get_users_rank') ) {
		return;
	}

	wp_enqueue_script( 'um_mycred' );
	wp_enqueue_style( 'um_mycred' );

	$user_id = get_post_field( 'post_author', bbp_get_reply_id() );
	$rank = mycred_get_users_rank( $user_id );

	if ( is_object( $rank ) ) { ?>

		<div class="um-mycred-bb-progress">
			<span class="um-mycred-progress um-tip-n" title="<?php echo esc_attr( $rank->title . ' ' . (int) UM()->myCRED_API()->get_rank_progress( $user_id ) . '%' ) ?>">
				<span class="um-mycred-progress-done" style="" data-pct="<?php echo esc_attr( UM()->myCRED_API()->get_rank_progress( $user_id ) ) ?>"></span>
			</span>
		</div>

	<?php }
}
add_action( 'um_bbpress_theme_after_reply_author_details', 'um_mycred_bb_rank_bar' );