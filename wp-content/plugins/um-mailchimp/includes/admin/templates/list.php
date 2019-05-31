<div class="um-admin-metabox">

	<?php $fields = array();

	$lists = UM()->Mailchimp_API()->api()->get_lists();

	$current_roles = array();
	foreach ( UM()->roles()->get_roles() as $key => $value) {
		if ( UM()->query()->get_meta_value( '_um_roles', $key ) ) {
			$current_roles[] = $key;
		}
	}

	if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
		$fields[] = array(
			'id'		    => 'mailing_list_id',
			'type'		    => 'info_text',
			'label'		    => __( 'Connected to Mailing List ID','um-mailchimp' ),
			'value'		    => UM()->query()->get_meta_value('_um_list'),
		);
	} else {
		$fields[] = array(
			'id'		    => '_um_list',
			'type'		    => 'select',
			'size'		    => 'medium',
			'label'		    => __( 'Choose a list','um-mailchimp' ),
			'tooltip'		=> __('Choose a list from your MailChimp account','um-mailchimp'),
			'value'		    => '',
			'options'		=> $lists,
		);
	}

	$fields = array_merge( $fields, array(
		array(
			'id'		    => '_um_status',
			'type'		    => 'checkbox',
			'label'		    => __( 'Enable this MailChimp list','um-mailchimp' ),
			'tooltip'		=> __( 'Turn on or off this list globally. If enabled the list will be available in user account page.','um-mailchimp' ),
			'value'		    => UM()->query()->get_meta_value( '_um_status', null, 1 ),
		),
		array(
			'id'		    => '_um_double_optin',
			'type'		    => 'select',
			'size'		    => 'medium',
			'label'		    => __( 'Enable double opt-in' ,'um-mailchimp' ),
			'tooltip'		=> __( 'Send contacts an opt-in confirmation email when they subscribe to your list.', 'um-mailchimp'),
			'value'		    => UM()->query()->get_meta_value('_um_double_optin'),
			'options'		=> array(
                ''    => __( 'Default' ,'um-mailchimp' ),
                '1' => __( 'Yes' ,'um-mailchimp' ),
                '0'  => __( 'No' ,'um-mailchimp' ),
            ),
		),
		array(
			'id'		    => '_um_desc',
			'type'		    => 'text',
			'label'		    => __( 'List Description in Account Page','um-mailchimp' ),
			'tooltip'		=> __( 'This text will be displayed in Account > Notifications to encourage user to sign or know what this list is about','um-mailchimp' ),
			'value'		    => UM()->query()->get_meta_value('_um_desc', null, 'na'),
		),
		array(
			'id'		    => '_um_desc_reg',
			'type'		    => 'text',
			'label'		    => __( 'List Description in Registration','um-mailchimp' ),
			'tooltip'		=> __( 'This text will be displayed in register form if you enable this mailing list to be available during signup','um-mailchimp' ),
			'value'		    => UM()->query()->get_meta_value('_um_desc_reg', null, 'na'),
		),
		array(
			'id'		    => '_um_reg_status',
			'type'		    => 'checkbox',
			'label'		    => __( 'Automatically add new users to this list', 'um-mailchimp' ),
			'tooltip'		=> __( 'If turned on users will automatically be subscribed to this when they register. When turned on this list will not show on register form even if you add MailChimp field to register form.','um-mailchimp' ),
			'value'		    => UM()->query()->get_meta_value( '_um_reg_status', null, 0 ),
		),
		array(
			'id'		    => '_um_roles',
			'multi'		    => true,
			'type'		    => 'select',
			'size'		    => 'medium',
			'label'		    => __( 'Which roles can subscribe to this list' ,'um-mailchimp' ),
			'tooltip'		=> __( 'Select which roles can subscribe to this list. Users who cannot subscribe to this list will not see this list on their account page.', 'um-mailchimp'),
			'value'		    => ! empty( $current_roles ) ? $current_roles : array(),
			'options'		=> UM()->roles()->get_roles(),
		),
	) );

	UM()->admin_forms( array(
		'class'		=> 'um-form-mailchimp um-half-column',
		'prefix_id'	=> 'mailchimp',
		'fields'    => $fields
	) )->render_form(); ?>

	<div class="um-admin-clear"></div>
</div>

<script type="text/javascript">
    jQuery(document).on('change', '#mailchimp__um_list', function() {
        jQuery('#um-admin-mailchimp-merge .inside').html('');
        var list_id = jQuery(this).val();
        wp.ajax.post('um_mailchimp_get_merge_fields', {
            list_id : list_id,
            nonce : '<?php echo wp_create_nonce('um_mailchimp_get_merge_fields') ?>'
        }).done( function( response ) {
            jQuery('#um-admin-mailchimp-merge .inside').html(response);
        } )
        .fail( function( response ) {
            alert( response );
        } );
    });

    jQuery(document).on('click', '#um_mailchimp_test_subscribe', function() {
        var data = {},
            serialize = jQuery('.um-form-mailchimp-test').parent().find(':input').serializeArray();

        jQuery('#um_test_message').html('');

        for( k in serialize ) {
            data[ serialize[ k ].name ] = serialize[ k ].value;
        }

        if( jQuery('#mailchimp__um_list').length ) {
            data['test_data[list_id]'] = jQuery('#mailchimp__um_list').val();
        }

        data.nonce = um_admin_scripts.nonce;

        wp.ajax.post('um_mailchimp_test_subscribe', data).done(function( response ) {
            jQuery('#um_test_message').html('<span style="color: ' + ( response.result ? 'green' : 'red' ) + ';">' + response.message + '</span>');
        }).fail(function( response ) {
            alert( response );
        });
    });

    jQuery(document).on('click', '#um_mailchimp_test_update', function() {
        var data = {},
            serialize = jQuery('.um-form-mailchimp-test :input').serializeArray();

        jQuery('#um_test_message').html('');

        for( k in serialize ) {
            data[ serialize[ k ].name ] = serialize[ k ].value;
        }

        if( jQuery('#mailchimp__um_list').length ) {
            data['test_data[list_id]'] = jQuery('#mailchimp__um_list').val();
        }

        data.nonce = um_admin_scripts.nonce;

        wp.ajax.post('um_mailchimp_test_update', data).done(function( response ) {
            jQuery('#um_test_message').html('<span style="color: ' + ( response.result ? 'green' : 'red' ) + ';">' + response.message + '</span>');
        }).fail(function( response ) {
            alert( response );
        });
    });

    jQuery(document).on('click', '#um_mailchimp_test_unsubscribe', function() {
        var data = {},
            serialize = jQuery('.um-form-mailchimp-test :input').serializeArray();

        jQuery('#um_test_message').html('');

        for( k in serialize ) {
            data[ serialize[ k ].name ] = serialize[ k ].value;
        }

        if( jQuery('#mailchimp__um_list').length ) {
            data['test_data[list_id]'] = jQuery('#mailchimp__um_list').val();
        }

        data.nonce = um_admin_scripts.nonce;

        wp.ajax.post('um_mailchimp_test_unsubscribe', data).done(function( response ) {
            jQuery('#um_test_message').html('<span style="color: ' + ( response.result ? 'green' : 'red' ) + ';">' + response.message + '</span>');
        }).fail(function( response ) {
            alert( response );
        });
    });

    jQuery(document).on('click', '#um_mailchimp_test_delete', function() {
        var data = {},
            serialize = jQuery('.um-form-mailchimp-test :input').serializeArray();

        jQuery('#um_test_message').html('');

        for( k in serialize ) {
            data[ serialize[ k ].name ] = serialize[ k ].value;
        }

        if( jQuery('#mailchimp__um_list').length ) {
            data['test_data[list_id]'] = jQuery('#mailchimp__um_list').val();
        }

        data.nonce = um_admin_scripts.nonce;

        wp.ajax.post('um_mailchimp_test_delete', data).done(function( response ) {
            jQuery('#um_test_message').html('<span style="color: ' + ( response.result ? 'green' : 'red' ) + ';">' + ( response.result ? 'Success' : response.message ) + '</span>');
        }).fail(function( response ) {
            alert( response );
        });
    });
</script>