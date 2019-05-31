<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add Notifications tab to account page
 *
 * @param array $tabs
 * @return array
 */
function um_mailchimp_account_notification_tab( $tabs ) {

	if ( empty( $tabs[400]['notifications'] ) ) {
		$tabs[400]['notifications'] = array(
			'icon'          => 'um-faicon-envelope',
			'title'         => __( 'Notifications', 'um-mailchimp' ),
			'submit_title'  => __( 'Update Notifications', 'um-mailchimp' ),
		);
	}

	return $tabs;
}
add_filter( 'um_account_page_default_tabs_hook', 'um_mailchimp_account_notification_tab', 10, 1 );


/**
 * Show mailchimp lists in account
 *
 * @param $output
 * @return string
 */
function um_mailchimp_account_tab( $output ) {
	UM()->Mailchimp_API()->api()->user_id = um_user("ID");
	$lists = UM()->Mailchimp_API()->api()->get_lists_data();
	if ( !$lists ) return $output;

	$mylists = um_user('_mylists');
	ob_start();

	?>

	<div class="um-field um-field-mailchimp" data-key="mailchimp">

		<div class="um-field-label"><label for=""><?php _e('Email Newsletters','um-mailchimp'); ?></label><div class="um-clear"></div></div>

		<div class="um-field-area">

			<?php foreach( $lists as $post_id ) { $list = UM()->Mailchimp_API()->api()->fetch_list($post_id); ?>
				<?php if ( UM()->Mailchimp_API()->api()->is_subscribed( $list['id'] ) ) { // subscribed ?>

				<label class="um-field-checkbox active">
					<input type="checkbox" name="um-mailchimp[<?php echo $list['id']; ?>]" value="1" <?php checked( !empty( $mylists[ $list['id'] ] ) ); ?> />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline"></i></span>
					<span class="um-field-checkbox-option"><?php echo $list['description']; ?></span>
				</label>

				<?php } else { ?>

				<label class="um-field-checkbox">
					<input type="checkbox" name="um-mailchimp[<?php echo $list['id']; ?>]" value="1"  />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span>
					<span class="um-field-checkbox-option"><?php echo $list['description']; ?></span>
				</label>

				<?php } ?>

			<?php } wp_reset_postdata(); ?>

			<div class="um-clear"></div>

		</div>

	</div>

	<?php

	$output .= ob_get_contents();
	ob_end_clean();

	return $output;
}
add_filter('um_account_content_hook_notifications', 'um_mailchimp_account_tab', 100 );


/**
 * Add custom error message
 *
 * @param $err
 * @param $msg
 * @return string
 */
function um_mailchimp_custom_error_message_handler( $err, $msg ) {
	return __( esc_html( $msg ) ,'um-mailchimp');
}
add_filter( 'um_custom_error_message_handler', 'um_mailchimp_custom_error_message_handler', 10, 2 );


/**
 * Store old email to determine email changed
 *
 * @param $data
 * @return array
 */
function um_mailchimp_user_pre_updating_profile_array( $data ) {
    global $old_email;
    $old_email = um_user('user_email');
	return $data;
}
add_filter( 'um_user_pre_updating_profile_array', 'um_mailchimp_user_pre_updating_profile_array', 10, 2 );