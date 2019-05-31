<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	add_filter('um_edit_field_register_mailchimp','um_edit_field_register_mailchimp', 10, 2);
	function um_edit_field_register_mailchimp( $output, $data ) {
		/**
		 * @var $mailchimp_list
		 * @var $metakey
		 */
		extract($data);

		$list = UM()->Mailchimp_API()->api()->fetch_list( $mailchimp_list );

		if ( $list['status'] != '1' || $list['auto_register'] == '1' )
			return '';

		if ( !empty( $data['mailchimp_auto_subscribe'] ) )
			return '<input type="hidden" name="um-mailchimp['.$list['id'].']" value="1" />';

		ob_start();

		?>

		<div class="um-field um-field-b um-field-mailchimp" data-key="<?php echo $metakey; ?>">

			<div class="um-field-area">

				<label class="um-field-checkbox">
					<input type="checkbox" name="um-mailchimp[<?php echo $list['id']; ?>]" value="1"  />
					<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-outline-blank"></i></span>
					<span class="um-field-checkbox-option"><?php echo ( $list['register_desc'] ) ? $list['register_desc'] : $list['description']; ?></span>
				</label>

				<?php wp_reset_postdata(); ?>

				<div class="um-clear"></div>

			</div>

		</div>

		<?php

		$output .= ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/***
	***	@extend core fields
	***/
	add_filter("um_core_fields_hook", 'um_mailchimp_add_field', 10 );
	function um_mailchimp_add_field($fields){

		$fields['mailchimp'] = array(
				'name' => __('MailChimp','um-mailchimp'),
				'col1' => array('_title'),
				'col2' => array('_mailchimp_list'),
				'col3' => array('_mailchimp_auto_subscribe'),
				'validate' => array(
					'_title' => array(
						'mode' => 'required',
						'error' => __('You must provide a title','um-mailchimp')
					),
				)
			);

		return $fields;

	}

	/***
	***	@do not require a metakey on mailchimp field
	***/
	add_filter('um_fields_without_metakey', 'um_mailchimp_requires_no_metakey');
	function um_mailchimp_requires_no_metakey( $array ) {
		$array[] = 'mailchimp';
		return $array;
	}

	add_filter('um_profile_last_login_empty__filter','um_mc_last_login__filter', 999 ,1 );
	function um_mc_last_login__filter( $value ){

		if( ! $value ){
			$value = um_user('user_registered');
		}
		return $value;
	}

