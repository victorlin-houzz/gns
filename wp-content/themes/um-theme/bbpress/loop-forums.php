<?php
/**
 * Forums Loop
 *
 * @package bbPress
 * @subpackage Theme
 */
?>

<?php do_action( 'bbp_template_before_forums_loop' ); ?>

<ul id="forums-list-<?php bbp_forum_id(); ?>" class="bbp-forums">

	<li class="bbp-header">

		<ul class="boot-row forum-titles boot-align-items-center">
			<li class="boot-col-md-6 bbp-forum-info"><?php esc_html_e( 'Forum', 'um-theme' ); ?></li>
			<li class="boot-col-md-2 bbp-forum-topic-count"><?php esc_html_e( 'Topics', 'um-theme' ); ?></li>
			<li class="boot-col-md-2 bbp-forum-reply-count"><?php bbp_show_lead_topic() ? esc_html_e( 'Replies', 'um-theme' ) : esc_html_e( 'Posts', 'um-theme' ); ?></li>
			<li class="boot-col-md-2 bbp-forum-freshness"><?php esc_html_e( 'Recent Activity', 'um-theme' ); ?></li>
		</ul>

	</li><!-- .bbp-header -->

	<li class="bbp-body">

		<?php while ( bbp_forums() ) : bbp_the_forum(); ?>

			<?php bbp_get_template_part( 'loop', 'single-forum' ); ?>

		<?php endwhile; ?>

	</li><!-- .bbp-body -->

</ul><!-- .forums-directory -->

<?php do_action( 'bbp_template_after_forums_loop' ); ?>
