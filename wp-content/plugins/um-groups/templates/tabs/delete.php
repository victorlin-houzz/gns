<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<form action="" method="post">
	<strong><?php _e("This action cannot be undone.","um-groups");?></strong>
	<br/>
	<input type="submit" name="um_groups_delete_group" value="<?php _e("Delete group permanently","um-groups"); ?>" />
	<?php wp_nonce_field( 'um-groups-nonce_delete_group_'.get_current_user_id() ); ?>
</form>