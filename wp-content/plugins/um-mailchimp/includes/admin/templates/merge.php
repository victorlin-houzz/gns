<div class="um-admin-metabox">
    <?php
    if( empty( $list_id ) && !empty( $post_id ) ) {
	    $list_id = get_post_meta( $post_id, '_um_list', true );
	    if ( empty( $list_id ) ) {
		    $lists = UM()->Mailchimp_API()->api()->get_lists();
		    if ( count( $lists ) ) {
			    list( $list_id ) = array_keys( $lists );
		    }
	    }
	    $merged = get_post_meta( $post_id, '_um_merge', true );
    }

    $merge_vars = UM()->builtin()->all_user_fields();
    $options = array( '0' => __( 'Ignore this field', 'um-mailchimp' ) );
    $options_for_required = array();

    foreach ($merge_vars as $k => $var) {
        if( empty( $var['title'] ) ) continue;
        $options[ $k ] = $options_for_required[ $k ] = $var['title'];
    }

    $fields = array();
    foreach( UM()->Mailchimp_API()->api()->get_vars( $list_id ) as $arr ) {
        $fields[] = array(
            'id'       => $arr['tag'],
            'type'     => 'select',
            'size'     => 'medium',
            'required' => isset( $arr['required'] ) ? $arr['required'] : false,
            'label'    => $arr['name'],
            'value'    => isset( $merged[ $arr['tag'] ] ) ? $merged[ $arr['tag'] ] : '',
            'options'  => isset( $arr['required'] ) && $arr['required'] ? $options_for_required : $options,
        );
    }
    UM()->admin_forms( array(
        'class'  => 'um-form-mailchimp-merge um-half-column',
        'fields' => $fields,
        'prefix_id'=> 'mailchimp[_um_merge]'
    ) )->render_form(); ?>

    <div class="um-admin-clear"></div>
</div>