<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	/***
	***	@extend settings
	***/
add_filter( 'um_settings_structure', 'um_mailchimp_settings', 10, 1 );

function um_mailchimp_settings( $settings ) {

	$settings['licenses']['fields'][] = array(
		'id'      		=> 'um_mailchimp_license_key',
		'label'    		=> __( 'MailChimp License Key', 'um-mailchimp' ),
		'item_name'     => 'MailChimp',
		'author' 	    => 'Ultimate Member',
		'version' 	    => um_mailchimp_version,
	);

	$key = ! empty( $settings['extensions']['sections'] ) ? 'mailchimp' : '';

	$settings['extensions']['sections'][$key] = array(
		'title'     => __( 'MailChimp', 'um-mailchimp' ),
		'fields'    => array(
			array(
				'id'       		=> 'mailchimp_api',
				'type'     		=> 'mailchimp_api_key',
				'label'   		=> __( 'MailChimp API Key','um-mailchimp' ),
				'tooltip' 	=> __('The MailChimp API Key is required and enables you access and integration with your lists.','um-mailchimp'),
				'size' => 'medium',
			),

			array(
				'id'       		=> 'mailchimp_unsubscribe_delete',
				'type'     		=> 'checkbox',
				'label'   		=> __( 'Remove subscriber from Mailchimp list when user unsubscribed','um-mailchimp' ),
				'tooltip' 	=> __('If set option then subscriber will be removed from Mailchimp list','um-mailchimp'),
			),

			array(
				'id'       		=> 'mailchimp_double_optin',
				'type'     		=> 'checkbox',
				'label'		    => __( 'Enable double opt-in' ,'um-mailchimp' ),
				'tooltip'		=> __( 'Send contacts an opt-in confirmation email when they subscribe to your list.', 'um-mailchimp'),
			),

			array(
				'id'       		=> 'mailchimp_enable_log',
				'type'     		=> 'checkbox',
				'label'		    => __( 'Enable requests log' ,'um-mailchimp' ),
				'tooltip'		=> __( 'Log all requests to mailchimp server and save to wp-content/uploads/ultimatemember/mailchimp.log.', 'um-mailchimp'),
			),

			array(
				'id'       		=> 'mailchimp_log',
				'type'     		=> 'mailchimp_log',
				'without_label' => true
			),
		)
	);

	return $settings;
}

add_filter('um_render_field_type_mailchimp_log', 'um_render_field_type_mailchimp_log');
function um_render_field_type_mailchimp_log() {
    if( !UM()->options()->get( 'mailchimp_enable_log' ) ) return '';
	ob_start();
	?>
	<p>
		<textarea style="width: 100%;" disabled="disabled" rows="15"><?php echo esc_textarea( UM()->Mailchimp_API()->log()->get() ) ?></textarea>
		<button class="button" id="um_mailchimp_clear_log"><?php _e('Clear log', 'um-mailchimp') ?></button>
	</p>
	<script type="text/javascript">
		jQuery(document.body).on('click', '#um_mailchimp_clear_log', function(e) {
			e.preventDefault();
		    jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'post',
			    data: {
					action: 'um_mailchimp_clear_log',
				    nonce: um_admin_scripts.nonce
			    },
                success: function() {
				    window.location.reload();
                }
			});
		});
	</script>
	<?php
	$content = ob_get_clean();
	return $content;
}

/* Reset cache if api key was changed */
/**
 * @param array $settings
 *
 * @return array
 * */
add_filter('um_change_settings_before_save', 'um_mailchimp_change_settings_before_save');
function um_mailchimp_change_settings_before_save( $settings ) {
	if( isset( $settings['mailchimp_api'] ) && UM()->options()->get('mailchimp_api') != $settings['mailchimp_api'] ) {
		delete_transient('_um_mailchimp_valid_api_key');
	}

	return $settings;
}

/* Generate field for Mailchimp API key */
/**
 * @param string $html
 * @param array $data
 * @param array $form_data
 * @param um\admin\core\Admin_Forms $admin_form
 *
 * @return string
 * */
function um_mailchimp_render_field_type_mailchimp_api_key( $html, $data, $form_data, $admin_form ) {
	$html .= $admin_form->render_text( $data );
	$apikey = UM()->options()->get('mailchimp_api');
	if( !$apikey )
		return $html;

	$check_valid_key = get_transient('_um_mailchimp_valid_api_key');
	if( $check_valid_key === false ) {
	    $api = UM()->Mailchimp_API()->api()->call();
	    if( is_wp_error( $api ) ) {
	        $check_valid_key = array( 'is_valid' => '0', 'error' => $api->get_error_message() );
        } else {
	        $common_request = $api->get();
            if( !empty( $common_request['account_id'] ) ) {
                $check_valid_key = array( 'is_valid' => '1' );
            } else {
                $check_valid_key = array( 'is_valid' => '0', 'error' => '' );
                $check_valid_key['error'] .= !empty( $common_request['title'] ) ? $common_request['title'] . '. ' : '';
                $check_valid_key['error'] .= !empty( $common_request['detail'] ) ? $common_request['detail'] : '';
            }
            set_transient( '_um_mailchimp_valid_api_key', $check_valid_key, 24 * 3600 );
        }
	}

	if ( $check_valid_key['is_valid'] == '1' ) {
		$html .= '<div class="dashicons dashicons-yes" style="color: green;"></div>';
	} else {
		$html .= '<br /><div class="dashicons dashicons-no-alt" style="color: red;"></div> ';
		$html .= !empty( $check_valid_key['error'] ) ? $check_valid_key['error'] : '';
	}

	return $html;
}
add_filter('um_render_field_type_mailchimp_api_key', 'um_mailchimp_render_field_type_mailchimp_api_key', 10, 4);


/* Tweak parameters passed in admin email */
add_filter('um_email_registration_data', 'um_mailchimp_email_registration_data');
function um_mailchimp_email_registration_data( $data ) {
	if ( isset( $data['um-mailchimp'] ) ) {
		 $array_lists = array();
		foreach( $data['um-mailchimp'] as $list_id => $val ) {
				$posts = get_posts( array( 'post_type' => 'um_mailchimp', 'meta_key' => '_um_list', 'meta_value' => $list_id ) );
				if( isset( $posts[0]->post_title ) ){
					$array_lists[] = $posts[0]->post_title . '(#' . $list_id.')';
				}
		}
		$data[ __('Mailchimp Subscription','um-mailchimp') ] = implode(", ", $array_lists );
		unset( $data['um-mailchimp'] );
	}
	return $data;
}