<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $post; ?>

<div class="um-admin-metabox">
	<div class="um-admin-metabox">
		<?php
		$searchable_fields = UM()->builtin()->all_user_fields('date,time,url');
		$user_fields = array();
		foreach ( $searchable_fields as $key => $arr ) {
			$user_fields[$key] = isset( $arr['title'] ) ? $arr['title'] : '';
		}

		$post_id = get_the_ID();
		$_um_groups_invites_fields = get_post_meta( $post_id, '_um_groups_invites_fields', true );

		UM()->admin_forms( array(
			'class'		=> 'um-member-directory-search um-half-column',
			'prefix_id'	=> 'um_metadata',
			'fields' => array(
				array(
					'id'		=> '_um_groups_invites_settings',
					'type'		=> 'checkbox',
					'label'		=> __( 'Enable Invites feature', 'ultimate-member' ),
					'value'		=> UM()->query()->get_meta_value( '_um_groups_invites_settings' ),
				),
				array(
					'id'		=> '_um_groups_invites_fields',
					'type'		=> 'multi_selects',
					'label'		=> __( 'Choose field(s) to enable in search', 'ultimate-member' ),
					'value'		=> $_um_groups_invites_fields,
					'conditional'   => array( '_um_groups_invites_settings', '=', 1 ),
					'options'   => $user_fields,
					'add_text'		=> __( 'Add New Custom Field','ultimate-member' ),
					'show_default_number'	=> 1,
				),

			)
		) )->render_form(); ?>
	</div>
	<div class="um-admin-clear"></div>

</div>