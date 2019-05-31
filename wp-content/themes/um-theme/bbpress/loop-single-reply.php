<?php
/**
 * Replies Loop - Single Reply
 *
 * @package bbPress
 * @subpackage Theme
 */
?>

<div id="post-<?php bbp_reply_id(); ?>" class="um-bb-each-reply">
	<div <?php bbp_reply_class(); ?>>
	<div class="um-bb-reply-author">
	<div class="boot-row">
				<div class="boot-col-sm-2 boot-text-md-center um-bb-reply-author-avatar">
					<?php do_action( 'bbp_theme_before_reply_author_details' ); ?>
					<a href="<?php bbp_reply_author_url( bbp_get_reply_id() );?>" title="<?php bbp_reply_author_display_name( bbp_get_reply_id() );?>" class="um-bb-author-avatar" rel="nofollow">
						<?php bbp_reply_author_avatar( bbp_get_reply_id(), 65 );?>
					</a>
					<?php do_action( 'bbp_theme_after_reply_author_details' ); ?>
				</div>
				<div class="boot-col-sm-8 boot-p-sm-0 um-bb-reply-author-info">
					<?php
						$reply_author_id 	= bbp_get_reply_author_id( bbp_get_reply_id() );
						$role 				= bbp_get_reply_author_role( $reply_author_id );
						echo '<span class="meta bb-author-role">';
						echo $role;
						echo '</span>';
					?>
					<span class="meta bb-author-name">
						<a href="<?php bbp_reply_author_url( bbp_get_reply_id() );?>" title="<?php bbp_reply_author_display_name( bbp_get_reply_id() );?>" class="um-bb-author-avatar" rel="nofollow">
							<?php bbp_reply_author_display_name( bbp_get_reply_id() );?>
						</a>
					</span>

					<span class="small meta bb-author-date">
						<?php esc_html_e( 'Member Since: ', 'um-theme' );?>
						<?php echo date( get_option( 'date_format' ),strtotime( get_the_author_meta( 'user_registered' ) ) );?>
					</span>
				</div>
				<div class="boot-col-2 um-bb-reply-content-id boot-text-right">
					<p class="meta">
						<a href="<?php bbp_reply_url(); ?>" class="bbp-reply-permalink">
							<i class="fas fa-link"></i>
						</a>
					</p>
				</div>
	</div>
	</div><!-- .bbp-reply-author -->

			<div class="boot-col-12 um-bb-reply-content">
				<span class="meta bbp-reply-post-date">
					<?php esc_html_e( 'On', 'um-theme' );?>
					<?php bbp_reply_post_date(); ?>
					<?php esc_html_e( 'by', 'um-theme' );?>
					<?php bbp_reply_author_display_name( bbp_get_reply_id() );?>
				</span>
				<?php do_action( 'bbp_theme_before_reply_content' ); ?>

				<?php bbp_reply_content(); ?>

				<?php do_action( 'bbp_theme_after_reply_content' ); ?>
			</div><!-- .bbp-reply-content -->
	</div>

	<div class="bbp-meta">
		<?php if ( bbp_is_single_user_replies() ) : ?>

			<span class="bbp-header">
				<?php esc_html_e( 'in reply to: ', 'um-theme' ); ?>
				<a class="bbp-topic-permalink" href="<?php bbp_topic_permalink( bbp_get_reply_topic_id() ); ?>"><?php bbp_topic_title( bbp_get_reply_topic_id() ); ?></a>
			</span>

		<?php endif; ?>

		<?php do_action( 'bbp_theme_before_reply_admin_links' ); ?>

		<?php bbp_reply_admin_links(); ?>

		<?php do_action( 'bbp_theme_after_reply_admin_links' ); ?>
	</div><!-- .bbp-meta -->
</div>
