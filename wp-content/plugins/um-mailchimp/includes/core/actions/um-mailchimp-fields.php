<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Modal field settings
 *
 * @param $val
 */
	function um_admin_field_edit_hook_mailchimp_list( $val ) {
		
		$lists = UM()->Mailchimp_API()->api()->get_lists_data( true );
		 
		if ( !$lists ) return;
		
		?>
		
        <p><label for="_mailchimp_list"><?php _e('Select a List','um-mailchimp'); ?> <?php UM()->tooltip( __('You can set up lists or integrations in Ultimate Member > MailChimp','um-mailchimp') ); ?></label>
            <select name="_mailchimp_list" id="_mailchimp_list" style="width: 100%">

                <?php foreach( $lists as $post_id ) { $list = UM()->Mailchimp_API()->api()->fetch_list( $post_id ); ?>
                <option value="<?php echo $post_id; ?>" <?php selected( $post_id, $val ); ?>><?php echo $list['name']; ?></option>
                <?php } ?>

            </select>
        </p>

		<?php
		
	}
add_action('um_admin_field_edit_hook_mailchimp_list', 'um_admin_field_edit_hook_mailchimp_list');


	add_action('um_admin_field_edit_hook_mailchimp_auto_subscribe', 'um_admin_field_edit_hook_mailchimp_auto_subscribe');
	function um_admin_field_edit_hook_mailchimp_auto_subscribe( $val ) {
		?>
		<p>
			<label for="_required">
				<?php
				_e('Automatically add users to this list on form submit', 'um-mailchimp');
				UM()->tooltip( __( 'If turned on users will be subscribed to list on form submit. When turned on this list will not be shown on form.', 'um-mailchimp' ) );
				?>
			</label>
			<input type="checkbox" name="_mailchimp_auto_subscribe" id="_mailchimp_auto_subscribe" value="1" <?php checked( $val, '1' ) ?> />
		</p>
		<?php
	}

	add_action('um_after_register_fields', 'um_mailchimp_after_register_fields');
	function um_mailchimp_after_register_fields( $val ) {
		$internal_lists = array_keys( UM()->Mailchimp_API()->api()->get_lists( false ) );
		foreach( $internal_lists as $list_id ) {
			$list = UM()->Mailchimp_API()->api()->fetch_list( $list_id );

			if ( $list['status'] == '1' && $list['auto_register'] == '1' ) {
				echo '<input type="hidden" name="um-mailchimp['.$list['id'].']" value="1" />';
			}
		}
	}