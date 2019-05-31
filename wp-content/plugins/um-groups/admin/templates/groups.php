<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $post; ?>

<div class="um-admin-metabox">

	<div class="">
		<p>
			<label class="um-admin-half"><?php _e('Privacy','um-groups');?> </label>
			<span class="um-admin-half">
		
				<select name="_um_groups_privacy" id="_um_groups_privacy" class="umaf-selectjs" style="width: 300px">
				<?php $privacy = UM()->Query()->get_meta_value('_um_groups_privacy', null, 'public') ; ?>
					<option value="public"  <?php selected('public',  $privacy);?> ><?php _e('Public','um-groups');?></option>
					<option value="private" <?php selected('private', $privacy);?> ><?php _e('Private','um-groups');?></option>
					<option value="hidden"  <?php selected('hidden',  $privacy);?> ><?php _e('Hidden','um-groups');?></option>
				</select>
			
			</span>
		</p><div class="um-admin-clear"></div>


		<p>
			<label class="um-admin-half"><?php _e('Who can invite members to the group?','um-groups'); ?></label>
			<span class="um-admin-half">

				<select name="_um_groups_can_invite" id="_um_groups_can_invite" class="umaf-selectjs" style="width: 300px">
					<?php foreach( UM()->Groups()->api()->can_invite as $key => $value) { ?>
					<option value="<?php echo $key; ?>" <?php selected($key, UM()->Query()->get_meta_value('_um_groups_can_invite', $key) ); ?>><?php echo $value; ?></option>
					<?php } ?>
				</select>

			</span>
		</p><div class="um-admin-clear"></div>

		<p>
			<label class="um-admin-half"><?php _e('Posts Moderation','um-groups'); ?></label>
			<span class="um-admin-half">

				<select name="_um_groups_posts_moderation" id="_um_groups_posts_moderation" class="umaf-selectjs" style="width: 300px">
					<?php foreach( UM()->Groups()->api()->group_posts_moderation_options as $key => $value) { ?>
					<option value="<?php echo $key; ?>" <?php selected($key, UM()->Query()->get_meta_value('_um_groups_posts_moderation', $key) ); ?>><?php echo $value; ?></option>
					<?php } ?>
				</select>

			</span>
		</p><div class="um-admin-clear"></div>

	</div>

	<div class="um-admin-clear"></div>
	<?php wp_nonce_field( basename( __FILE__ ), 'um_admin_save_metabox_groups_nonce' );?>


</div>