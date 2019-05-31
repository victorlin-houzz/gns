<?php
/**
 * Topics Loop - Single
 *
 * @package bbPress
 * @subpackage Theme
 */
?>
<ul id="bbp-topic-<?php bbp_topic_id(); ?>" <?php bbp_topic_class(); ?>>
<div class="boot-container">
<div class="boot-row">
	<div class="boot-col-md-2 boot-text-sm-center boot-p-sm-0">
		<?php do_action( 'bbp_theme_before_topic_started_by' ); ?>

		<span class="bbp-topic-started-by"><?php echo bbp_get_topic_author_link( array( 'size' => '60' ) ); ?></span>

		<?php do_action( 'bbp_theme_after_topic_started_by' ); ?>
	</div>

	<div class="boot-col-md-10">

		<?php if ( bbp_is_user_home() ) : ?>

			<?php if ( bbp_is_favorites() ) : ?>

				<span class="bbp-row-actions">

					<?php do_action( 'bbp_theme_before_topic_favorites_action' ); ?>

					<?php

						bbp_topic_favorite_link(
							array(
								'before' 	=> '',
								'favorite' 	=> '+',
								'favorited' => '&times;',
						) );
					?>

					<?php do_action( 'bbp_theme_after_topic_favorites_action' ); ?>

				</span>

			<?php elseif ( bbp_is_subscriptions() ) : ?>

				<span class="bbp-row-actions">

					<?php do_action( 'bbp_theme_before_topic_subscription_action' ); ?>

					<?php
					bbp_topic_subscription_link(
						array(
							'before' => '',
							'subscribe' => '+',
							'unsubscribe' => '&times;',
						) );
					?>

					<?php do_action( 'bbp_theme_after_topic_subscription_action' ); ?>

				</span>

			<?php endif; ?>

		<?php endif; ?>

		<?php do_action( 'bbp_theme_before_topic_title' ); ?>

		<h5><a class="bbp-topic-permalink" href="<?php bbp_topic_permalink(); ?>"><?php bbp_topic_title(); ?></a></h5>

		<?php do_action( 'bbp_theme_after_topic_title' ); ?>

		<?php bbp_topic_pagination(); ?>

		<?php do_action( 'bbp_theme_before_topic_meta' ); ?>


			<?php if ( ! bbp_is_single_forum() || ( bbp_get_topic_forum_id() !== bbp_get_forum_id() ) ) : ?>
				<p class="bbp-topic-meta">
					<?php do_action( 'bbp_theme_before_topic_started_in' ); ?>

					<span class="bbp-topic-started-in"><?php printf( __( 'in: <a href="%1$s">%2$s</a>', 'um-theme' ), bbp_get_forum_permalink( bbp_get_topic_forum_id() ), bbp_get_forum_title( bbp_get_topic_forum_id() ) ); ?></span>

					<?php do_action( 'bbp_theme_after_topic_started_in' ); ?>
				</p>
			<?php endif; ?>


		<span class="meta um-bb-topic-meta">

			<span class="meta um-bb-topic-reply-count">
				<?php esc_html_e( 'Total Reply: ', 'um-theme' );?>
				<?php bbp_show_lead_topic() ? bbp_topic_reply_count() : bbp_topic_post_count(); ?>
			</span>

			<?php esc_html_e( 'Latest Post: ', 'um-theme' );?>

			<?php do_action( 'bbp_theme_before_topic_freshness_link' ); ?>

			<?php bbp_topic_freshness_link(); ?>

			<?php do_action( 'bbp_theme_after_topic_freshness_link' ); ?>

			<?php do_action( 'bbp_theme_before_topic_freshness_author' ); ?>

			<span class="bbp-topic-freshness-author">
				<?php esc_html_e( 'by ', 'um-theme' );?>
				<?php bbp_topic_author_display_name( bbp_get_topic_id() );?>
			</span>

			<?php do_action( 'bbp_theme_after_topic_freshness_author' ); ?>

		</span>

		<?php do_action( 'bbp_theme_after_topic_meta' ); ?>

		<?php bbp_topic_row_actions(); ?>

	</div>
</div>
</div>
</ul>
