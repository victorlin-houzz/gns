<?php $likes = get_comment_meta( $commentc->comment_ID, '_likes', true );
$avatar      = get_avatar( um_user('ID'),80 );
$user_hidden = UM()->Activity_API()->api()->user_hidden_comment( $commentc->comment_ID ); ?>

<div class="um-activity-commentl is-child" id="commentid-<?php echo $commentc->comment_ID; ?>">

	<?php if ( is_user_logged_in() && ! $user_hidden ) { ?>
		<a href="#" class="um-activity-comment-hide um-tip-s"><i class="um-icon-close-round"></i></a>
	<?php } ?>

	<div class="um-activity-comment-avatar hidden-<?php echo $user_hidden; ?>"><a href="<?php echo um_user_profile_url(); ?>"><?php echo $avatar; ?></a></div>

	<div class="um-activity-comment-hidden hidden-<?php echo $user_hidden; ?>"><?php _e('Reply hidden. <a href="#" class="um-link">Show this reply</a>','um-activity'); ?></div>

	<div class="um-activity-comment-info hidden-<?php echo $user_hidden; ?>">
		<div class="um-activity-comment-data">
			<span class="um-activity-comment-author-link"><a href="<?php echo um_user_profile_url(); ?>" class="um-link"><?php echo um_user('display_name'); ?></a></span> <span class="um-activity-comment-text"><?php echo UM()->Activity_API()->api()->commentcontent( $commentc->comment_content ); ?></span>
			<textarea id="um-activity-reply-<?php echo $commentc->comment_ID; ?>" class="original-content" style="display:none!important"><?php if( isset( $commentc->comment_content ) ){ echo $commentc->comment_content; } ?></textarea>
		</div>
		<div class="um-activity-comment-meta">
			<?php if ( is_user_logged_in() ) { ?>

				<?php if ( UM()->Activity_API()->api()->user_liked_comment( $commentc->comment_ID ) ) { ?>
					<span><a href="#" class="um-link um-activity-comment-like active" data-like_text="<?php _e('Like','um-activity'); ?>" data-unlike_text="<?php _e('Unlike','um-activity'); ?>"><?php _e('Unlike','um-activity'); ?></a></span>
				<?php } else { ?>
					<span><a href="#" class="um-link um-activity-comment-like" data-like_text="<?php _e('Like','um-activity'); ?>" data-unlike_text="<?php _e('Unlike','um-activity'); ?>"><?php _e('Like','um-activity'); ?></a></span>
				<?php } ?>

				<span class="um-activity-comment-likes count-<?php echo (int) $likes; ?>"><a href="#"><i class="um-faicon-thumbs-up"></i><ins class="um-activity-ajaxdata-commentlikes"><?php echo (int) $likes; ?></ins></a></span>

			<?php } ?>
			<span><a href="<?php echo UM()->Activity_API()->api()->get_comment_link( $post_link, $commentc->comment_ID ); ?>" class="um-activity-comment-permalink"><?php echo UM()->Activity_API()->api()->get_comment_time( $commentc->comment_date ); ?></a></span>

			<?php if ( UM()->Activity_API()->api()->can_edit_comment( $commentc->comment_ID, get_current_user_id() ) ) { ?>
				<span class="um-activity-editc"><a href="#" ><i class="um-icon-edit"></i></a>
					<span class="um-activity-editc-d">
						<a href="#" class="edit" data-commentid="<?php echo $commentc->comment_ID; ?>"><?php _e('Edit','um-activity'); ?></a>
						<a href="#" class="delete" data-msg="<?php _e('Are you sure you want to delete this comment?','um-activity'); ?>"><?php _e('Delete','um-activity'); ?></a>
					</span>
				</span>
			<?php } ?>

		</div>
	</div>
</div>