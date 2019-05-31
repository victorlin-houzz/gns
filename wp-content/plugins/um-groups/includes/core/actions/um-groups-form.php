<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build settings
 * @param $settings
 */
function um_groups_create_form( $settings ){
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	echo "<div class='um-groups-form'>";
	echo "<form method='post' action='' name='um-form um-groups-form'>";
	echo "<div class='um-group-fields'>";
			
	do_action('um_groups_create_form_header', $settings );
			
	echo "<div class='um-group-field' data-key='group_name'>";
	echo "<label for='group_name'>";
	echo "<div class='group-form-label'>";
	_e("Name","um-groups");
	echo "</div>";
	echo "<input type='text' name='group_name' value='".esc_attr( UM()->Groups()->api()->single_group_title )."'/>";
	echo "</label>";
			
	if ( UM()->form()->has_error('group_name') ) {
		UM()->Groups()->form_process()->show_error( UM()->form()->errors['group_name'] );
	}

	echo "</div>";


	echo "<div class='um-group-field'  data-key='group_description'>";
	echo "<label for='group_description'>";
	echo "<div class='group-form-label'>";
	_e("Description","um-groups");
	echo "</div>";
	echo "<textarea name='group_description'>".esc_attr( UM()->fields()->field_value('group_description',true, array('key'=>'group_description') ) )."</textarea>";
	echo "</label>";
	if ( UM()->form()->has_error('group_description') ) {
		UM()->Groups()->form_process()->show_error( UM()->form()->errors['group_description'] );
	}
	echo "</div>";

	$privacy =  UM()->fields()->field_value('group_privacy','public',array('key'=>'group_privacy'));
	echo "<div class='um-group-field'  data-key='group_privacy'>";
	echo "<label for='group_privacy'>";
	echo "<div class='group-form-label'>";
	_e("Privacy","um-groups");
	echo "</div>";
	echo "</label>";
	echo "<ul class='um-privacy-wrap'>";
	echo "<li>";
	echo "<label>";
	echo "<input type='radio' name='group_privacy' value='public' ".checked( $privacy,'public', false )." /> ".__("Public","um-groups");
	echo "<ul>";
	echo "<li>".__("Any site member can join this group.","um-groups")."</li>";
	echo "<li>".__("This group will be listed in the groups directory and in search results.","um-groups")."</li>";
	echo "<li>".__("Group content and activity will be visible to any site member.","um-groups")."</li>";
	echo "</ul>";
	echo "</label>";
	echo "</li>";
	echo "<li>";
	echo "<label>";
	echo "<input type='radio' name='group_privacy' value='private' ".checked( $privacy,'private', false )." /> ".__("Private","um-groups");
	echo "<ul>";
	echo "<li>".__("Only users who request membership and are accepted can join the group.","um-groups")."</li>";
	echo "<li>".__("This group will be listed in the groups directory and in search results.","um-groups")."</li>";
	echo "<li>".__("Group content and activity will only be visible to members of the group.","um-groups")."</li>";
	echo "</ul>";
	echo "</label>";
	echo "</li>";
	echo "<li>";
	echo "<label>";
	echo "<input type='radio' name='group_privacy' value='hidden' ".checked( $privacy,'hidden', false )." /> ".__("Hidden","um-groups");
	echo "<ul>";
	echo "<li>".__("Only users who are invited can join the group.","um-groups")."</li>";
	echo "<li>".__("This group will not be listed in the groups directory or search results.","um-groups")."</li>";
	echo "<li>".__("Group content and activity will only be visible to members of the group.","um-groups")."</li>";
	echo "</ul>";
	echo "</label>";
	echo "</li>";
	echo "</ul>";
	echo "</div>";

	$can_invite_members =  UM()->fields()->field_value('can_invite_members','1',array('key'=>'can_invite_members'));
			
	echo "<div class='um-group-field' data-key='can_invite_members'>";
	echo "<label for='can_invite_members'>";
	echo "<div class='group-form-label'>";
	_e("Who can invite members to the group?","um-groups");
	echo "</div>";
	echo "<select name='can_invite_members' class='um-s2' >";
	echo "<option value='0' ".selected( $can_invite_members, 0 ) ." >".__("All Group Members","um-groups")."</option>";
	echo "<option value='1' ".selected( $can_invite_members, 1 ) .">".__("Group Administrators & Moderators only","um-groups")."</option>";
	echo "<option value='2' ".selected( $can_invite_members, 2 ) .">".__("Group Administrators only","um-groups")."</option>";
	echo "</select>";
	echo "</label>";
	echo "</div>";

	$post_moderations =  UM()->fields()->field_value('post_moderations','auto-published',array('key'=>'post_moderations'));
			
	echo "<div class='um-group-field' data-key='post_moderations'>";
	echo "<label for='posts_moderation'>";
	echo "<div class='group-form-label'>";
	_e("Posts Moderation","um-groups");
	echo "</div>";
	echo "<select name='post_moderations' class='um-s2' >";
	echo "<option value='auto-published' ".selected( $post_moderations, 'auto-published' ) ." >".__("Auto Published","um-groups")."</option>";
	echo "<option value='require-moderation' ".selected( $post_moderations, 'require-moderation' ) ." >".__("Require Mod/Admin","um-groups")."</option>";
	echo "</select>";
	echo "</label>";
	echo "</div>";

	$category =  UM()->fields()->field_value('categories',true,array('key'=>'categories') );
			
	echo "<div class='um-group-field' data-key='categories'>";
	echo "<label for='categories'>";
	echo "<div class='group-form-label'>";
	_e("Category","um-groups");
	echo "</div>";
	$categories = get_categories( array('taxonomy' => 'um_group_categories', 'hide_empty' => 0 ) );
	echo "<select name='categories' class='um-s2' >";
	echo "<option value=''>".__("-Choose a category-","um-groups")."</option>";
	foreach( $categories as $cat ):
		echo "<option value='".$cat->slug."' ".selected( $category, $cat->slug ) .">".$cat->name."</option>";
	endforeach;
	echo "</select>";
	echo "</label>";
	if ( UM()->form()->has_error('categories') ) {
		UM()->Groups()->form_process()->show_error( UM()->form()->errors['categories'] );
	}
	echo "</div>";

	$group_tags =  UM()->fields()->field_value('group_tags',true,array('key'=>'group_tags'));
			
	echo "<div class='um-group-field' data-key='group_tags'>";
	echo "<label for='tags'>";
	echo "<div class='group-form-label'>";
	_e("Tags","um-groups");
	echo "</div>";
	$categories = get_terms( array('taxonomy' => 'um_group_tags', 'hide_empty' => 0 ) );
	echo "<select name='group_tags[]' class='um-s1' multiple='multiple' placeholder='Choose tags'>";
					
	foreach( $categories as $category ):
		if( ! empty( $group_tags ) && in_array( $category->name,  $group_tags ) ){
			echo "<option value='".$category->name."' selected='selected'>".$category->name."</option>";
		}else{
			echo "<option value='".$category->name."'>".$category->name."</option>";
		}
	endforeach;
	echo "</select>";
	echo "</label>";
	if ( UM()->form()->has_error('group_tags') ) {
		UM()->Groups()->form_process()->show_error( UM()->form()->errors['group_tags'] );
	}
	echo "</div>";

	echo "<div class='um-group-field'>";
	wp_nonce_field( 'um-groups-nonce_'.get_current_user_id() );
	if( um_is_core_page('create_group') || 'um_groups' != get_post_type() ){
		echo "<input type='submit' name='um_groups_submit' class='um-button' value='".__("Submit","um-groups")."'/>";
	}else{
		echo "<input type='submit' name='um_groups_update' class='um-button' value='".__("Update","um-groups")."'/>";
	}
	echo "</div>";

	do_action('um_groups_create_form_footer', $settings );
			
	echo "</div>";
	echo "</form>";
	echo "</div>";
}
add_action( 'um_groups_create_form', 'um_groups_create_form', 10, 1 );