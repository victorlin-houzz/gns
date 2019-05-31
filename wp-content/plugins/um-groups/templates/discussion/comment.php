<?php if ( ! defined( 'ABSPATH' ) ) exit;


foreach( $comments as $comment ) {
	um_fetch_user($comment->user_id);
	
	$avatar      = get_avatar( um_user( 'ID' ),80);
	$likes       = get_comment_meta( $comment->comment_ID, '_likes', true );
	$user_hidden = UM()->Groups()->discussion()->user_hidden_comment( $comment->comment_ID );
?>

			<div class="um-groups-commentwrap" data-comment_id="<?php echo $comment->comment_ID; ?>">

			<div class="um-groups-commentl" id="commentid-<?php echo $comment->comment_ID; ?>">

				<?php if ( !$user_hidden ) { ?>
				<a href="#" class="um-groups-comment-hide um-tip-s"><i class="um-icon-close-round"></i></a>
				<?php } ?>

				<div class="um-groups-comment-avatar hidden-<?php echo $user_hidden; ?>"><a href="<?php echo um_user_profile_url(); ?>"><?php echo $avatar; ?></a></div>

				<div class="um-groups-comment-hidden hidden-<?php echo $user_hidden; ?>"><?php _e('Comment hidden. <a href="#" class="um-link">Show this comment</a>','um-groups'); ?></div>

				<div class="um-groups-comment-info hidden-<?php echo $user_hidden; ?>">

					<div class="um-groups-comment-data">
						<span class="um-groups-comment-author-link"><a href="<?php echo um_user_profile_url(); ?>" class="um-link"><?php
							$um_activity_comment_text = UM()->Groups()->discussion()->commentcontent( $comment->comment_content );
							echo um_user('display_name'); ?></a></span> <span class="um-groups-comment-text"><?php echo str_replace("\'" ,"'",$um_activity_comment_text); ?>
						</span>
						<textarea id="um-groups-reply-<?php echo $comment->comment_ID; ?>" class="original-content" style="display:none!important"><?php echo $comment->comment_content; ?></textarea>
					</div>

					<div class="um-groups-comment-meta">
						<?php if ( is_user_logged_in() ) { ?>

						<?php if ( UM()->Groups()->discussion()->user_liked_comment( $comment->comment_ID ) ) { ?>
						<span><a href="#" class="um-link um-groups-comment-like active" data-like_text="<?php _e('Like','um-groups'); ?>" data-unlike_text="<?php _e('Unlike','um-groups'); ?>"><?php _e('Unlike','um-groups'); ?></a></span>
						<?php } else { ?>
						<span><a href="#" class="um-link um-groups-comment-like" data-like_text="<?php _e('Like','um-groups'); ?>" data-unlike_text="<?php _e('Unlike','um-groups'); ?>"><?php _e('Like','um-groups'); ?></a></span>
						<?php } ?>

						<span class="um-groups-comment-likes count-<?php echo (int) $likes; ?>"><a href="#"><i class="um-faicon-thumbs-up"></i><ins class="um-groups-ajaxdata-commentlikes"><?php echo (int) $likes; ?></ins></a></span>

						<?php if ( UM()->Groups()->discussion()->can_comment() ) { ?><span><a href="#" class="um-link um-groups-comment-reply" data-commentid="<?php echo $comment->comment_ID; ?>"><?php _e('Reply','um-groups'); ?></a></span><?php } ?>

						<?php } ?>

						<span><a href="<?php echo UM()->Groups()->discussion()->get_comment_link( UM()->Groups()->discussion()->get_permalink( absint( $comment->comment_post_ID ) ), $comment->comment_ID ); ?>" class="um-groups-comment-permalink"><?php echo UM()->Groups()->discussion()->get_comment_time( $comment->comment_date ); ?></a></span>

						<?php if ( UM()->Groups()->discussion()->can_edit_comment( $comment->comment_ID, get_current_user_id() ) ) { ?>
						<span class="um-groups-editc"><a href="#"><i class="um-icon-edit"></i></a>
							<span class="um-groups-editc-d">
								<a href="#" class="edit" data-commentid="<?php echo $comment->comment_ID; ?>"><?php _e('Edit','um-groups'); ?></a>
								<a href="#" class="delete" data-msg="<?php _e('Are you sure you want to delete this comment?','um-groups'); ?>"><?php _e('Delete','um-groups'); ?></a>
							</span>
						</span>
						<?php } ?>

					</div>

				</div>

			</div>

		<?php

		$comm_num = ( isset( $_GET['wall_comment_id'] ) && absint( $_GET['wall_comment_id'] ) ) ? 10000 : UM()->options()->get('activity_init_comments_count');

		$child = get_comments( array( 'post_id' => $post_id, 'parent' => $comment->comment_ID, 'number' => $comm_num, 'offset' => 0, 'order' => UM()->options()->get('activity_order_comment') ) );
		$child_all = get_comments( array( 'post_id' => $post_id, 'parent' => $comment->comment_ID, 'number' => 999, 'offset' => 0, 'order' => UM()->options()->get('activity_order_comment') ) );

		echo '<div class="um-groups-comment-child">';

			foreach( $child as $commentc ) {
				um_fetch_user($commentc->user_id);

				$likes       = get_comment_meta( $commentc->comment_ID, '_likes', true );
				$avatar      = get_avatar(um_user('ID'),80);
				$user_hidden = UM()->Groups()->discussion()->user_hidden_comment( $commentc->comment_ID );

				include um_groups_path . 'templates/discussion/comment-reply.php';

			}

			// Do we have more comments
			if ( count( $child_all ) > count( $child ) ) {
				$calc = count( $child_all ) - count( $child );
				if ( $calc > 1 ) {
					$text = sprintf(__('load %s more replies','um-groups'), $calc );
				} else if ( $calc == 1 ) {
					$text = sprintf(__('load %s more reply','um-groups'), $calc );
				}
				echo '<a href="#" class="um-groups-ccommentload" data-load_replies="'. __('load more replies','um-groups').'" data-load_comments="'.__('load more comments','um-groups') . '" data-loaded="'. count( $child ) . '"><i class="um-icon-forward"></i><span>' . $text . '</span></a>';
				echo '<div class="um-groups-ccommentload-spin"></div>';
			}

		echo '</div>';

echo '</div>';
}

// reset um user
um_reset_user();