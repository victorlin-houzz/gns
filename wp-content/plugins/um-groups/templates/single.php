<?php
/**
 * @Template: Single Group page
 */


if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um um-groups-single">

	<div class='um-group-single-header'>
		<div class='um-group-join-button'>
			<?php do_action('um_groups_join_button', get_the_ID() ); ?>
		</div>
		<?php if ( UM()->options()->get('groups_show_avatars') ): ?>
			<div class='um-group-image-wrap'>
				<?php echo UM()->Groups()->api()->get_group_image( get_the_ID(), 'default', 100, 100); ?>
			</div>
		<?php endif; ?>
		<div class='um-group-name'>
			<strong><?php echo esc_attr( UM()->Groups()->api()->single_group_title ); ?></strong>
		</div>
		<div class='um-group-privacy'>
			<?php echo UM()->Groups()->api()->get_single_privacy( get_the_ID() ).' '.__('Group','um-groups'); ?>
		</div>
		
		<div class='um-group-members-count'>
			<?php $count = um_groups_get_member_count( get_the_ID(), true );
			echo sprintf( _n( '<span class="count">%s</span> member', '<span class="count">%s</span> members', $count, 'um-groups' ), number_format_i18n( $count ) ); ?>
		</div>

		<div class='um-group-description'>
			<?php echo get_the_content(); ?>
		</div>
		<div class='um-clear'></div>
	</div>

	<?php do_action('um_groups_before_page_tabs', get_the_ID() ); ?>

	<?php if( is_user_logged_in() ): ?>
		<div class='um-group-tabs-wrap'>
			<?php do_action('um_groups_single_page_tabs', get_the_ID() ); ?>
		</div>

		<?php $current_tab = UM()->Groups()->api()->current_group_tab; ?>
		<?php $sub_tab = UM()->Groups()->api()->current_group_subtab; ?>

		<div class='um-group-tab-content-wrap um-group-tab-content__<?php echo $current_tab; ?>'>
			<?php do_action("um_groups_single_page_content", get_the_ID(), $current_tab, $sub_tab ); ?>
			<?php do_action("um_groups_single_page_content__{$current_tab}", get_the_ID() ); ?>
			<?php do_action("um_groups_single_page_sub_content__{$current_tab}_{$sub_tab}", get_the_ID() ); ?>
		</div>
	<?php endif; ?>
</div>