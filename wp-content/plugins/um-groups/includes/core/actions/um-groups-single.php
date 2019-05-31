<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Single Page Tabs
 *
 * @param $group_id
 */
function um_groups_single_page_tabs( $group_id ) {
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	$param_tab = get_query_var('tab');
	$param_sub_tab = get_query_var('sub');

	$tabs = apply_filters('um_groups_tabs', array(), $group_id, $param_tab );

	$arr_tab_keys = array();
	if( ! empty( $tabs ) ){
		foreach ( $tabs as $key => $tab ) {
			$arr_tab_keys[ ] =  $key;
		}
	}

	if( ! empty( $tabs ) ){
		if( ! empty( $param_tab ) ){
			UM()->Groups()->api()->current_group_tab = $param_tab;
			UM()->Groups()->api()->current_group_subtab = $param_sub_tab;
		}elseif(  empty( $param_tab ) || ! in_array( $param_tab, $arr_tab_keys ) ){
			UM()->Groups()->api()->current_group_tab = 'discussion';
			UM()->Groups()->api()->current_group_subtab = '';
		}
	}else{
		UM()->Groups()->api()->current_group_tab = '';
		UM()->Groups()->api()->current_group_subtab = '';
	}

	UM()->Groups()->api()->group_tabs = $arr_tab_keys;

	if( ! empty( $tabs ) ){
		echo '<ul class="um-groups-single-tabs">';
		foreach( $tabs as $tab ):

			$tab_url = add_query_arg('tab',$tab['slug'], '' );

			if( isset( $tab['default_sub'] ) ){
				$tab_url = add_query_arg('sub',$tab['default_sub'], $tab_url  );
			}

			echo '<li class="um-groups-tab-slug_'.$tab['slug'].' '.( ( isset( $tab['default'] ) && empty( $param_tab ) )|| $param_tab == $tab['slug'] ? 'active':'').'"><a href="'.$tab_url.'">'.$tab['name'].'</a></li>';
		endforeach;
		echo '</ul>';


		echo '<input type="hidden" name="group_current_tab" value="'.esc_attr( UM()->Groups()->api()->current_group_tab ).'"/>';
		echo '<div class="um-clear"></div>';

	}

	echo '<input type="hidden" name="group_id" value="'.get_the_ID().'"/>';


}
add_action( 'um_groups_single_page_tabs', 'um_groups_single_page_tabs', 10, 1 );


/**
 * Single Page Content
 *
 * @param $group_id
 * @param $current_tab
 */
function um_groups_single_page_content( $group_id, $current_tab ) {
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	$param_tab = get_query_var('tab');
	$param_sub_tab = get_query_var('sub');
	
	$sub_tabs = apply_filters('um_groups_sub_tabs', array(), $group_id, $param_sub_tab, $param_tab );


	if( ! empty( $sub_tabs ) ){
		echo '<ul class="um-groups-single-subtabs">';
			foreach( $sub_tabs as $sub_tab ):
				UM()->Groups()->api()->group_tabs[ ] = "{$param_tab}_{$sub_tab['slug']}";
				if( in_array( $param_tab, UM()->Groups()->api()->group_tabs ) ){
					UM()->Groups()->api()->group_tabs[ ] = $sub_tab['slug'];
					echo '<li class="um-groups-subtab-slug_'.$sub_tab['slug'].' '.( ( isset( $sub_tab['default'] ) && empty( $param_tab ) )|| $param_sub_tab == $sub_tab['slug'] ? 'active':'').'"><a href="?tab='.$param_tab.'&sub='.$sub_tab['slug'].'">'.$sub_tab['name'].'</a></li>';
				}
			endforeach;
		echo '</ul>';
	}

}
add_action( 'um_groups_single_page_content','um_groups_single_page_content', 10, 2 );


/**
 * Single Page Content - Discussion Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__discussion( $group_id ) {
	load_template( um_groups_path . 'templates/tabs/discussions.php', false );
}
add_action( 'um_groups_single_page_content__discussion', 'um_groups_single_page_content__discussion' );


/**
 * Single Page Content - Members Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__members( $group_id ) {
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
	load_template( um_groups_path . 'templates/tabs/members.php', false );
}
add_action( 'um_groups_single_page_content__members', 'um_groups_single_page_content__members' );


/**
 * Single Page content - Settings > Details Tab
 *
 * @param $group_id
 */
function um_groups_single_page_sub_content__settings_details( $group_id ) {
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
	load_template( um_groups_path . 'templates/tabs/settings.php', false );
}
add_action( 'um_groups_single_page_sub_content__settings_details', 'um_groups_single_page_sub_content__settings_details' );


/**
 * Single Page content - Settings > Avatar Tab
 *
 * @param $group_id
 */
function um_groups_single_page_sub_content__settings_avatar( $group_id ){
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
	load_template( um_groups_path . 'templates/tabs/avatar.php', false );
}
add_action('um_groups_single_page_sub_content__settings_avatar','um_groups_single_page_sub_content__settings_avatar');


/**
 * Single Page content - Settings >Delete Tab
 *
 * @param $group_id
 */
function um_groups_single_page_sub_content__settings_delete( $group_id ){
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
	load_template( um_groups_path . 'templates/tabs/delete.php', false );
}
add_action('um_groups_single_page_sub_content__settings_delete','um_groups_single_page_sub_content__settings_delete');


/**
 * Single Page content - Requests Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__requests( $group_id ){
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
	load_template( um_groups_path . 'templates/tabs/requests.php', false );
}
add_action('um_groups_single_page_content__requests','um_groups_single_page_content__requests');


/**
 * Single Page content - Blocked Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__blocked( $group_id ){
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
	load_template( um_groups_path . 'templates/tabs/blocked.php', false );
}
add_action('um_groups_single_page_content__blocked','um_groups_single_page_content__blocked');


/**
 * Single Page content - Send Invites Tab
 *
 * @param $group_id
 */
function um_groups_single_page_content__invites( $group_id ){
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );
	load_template( um_groups_path . 'templates/tabs/invites.php', false );
}
add_action('um_groups_single_page_content__invites','um_groups_single_page_content__invites');


/**
 * Confirm group invitation
 *
 * @param $group_id
 */
function um_groups_single_confirm_invite( $group_id ){
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	$user_id = um_user('ID');

	$has_joined = UM()->Groups()->api()->has_joined_group( $user_id, $group_id );
	
	echo '<div class="um-groups-single-button-pending">';
	if( in_array( $has_joined, array('pending_member_review') ) ){
		
		$invited_by_user_id = UM()->Groups()->api()->invited_by_user_id;
		um_fetch_user( $invited_by_user_id );
		$profile_name = um_user('display_name');
		$profile_avatar = um_user('profile_photo', 40 );
		$profile_url = um_user_profile_url( $invited_by_user_id );

		echo "<div class='um-groups-confirm-approval-message'>";
		echo __("You've been invited by <a href='{$profile_url}'>{$profile_avatar}{$profile_name}</a>.","um-groups");
		echo "</br>";
		echo __("Would you like to join this group?","um-groups");
		echo "</div>";
		echo '<a href="javascript:;" data-user_id="'.$user_id.'" class="um-button um-groups-ignore-invite um-alt um-right" >'.__("Ignore","um-groups").'</a>';
		echo '<a href="javascript:;" data-user_id="'.$user_id.'" class="um-button um-groups-confirm-invite um-right" >'.__("Confirm","um-groups").'</a>';
	}
	echo '<div class="um-clear"></div>';
	echo '</div>';
}
add_action( 'um_groups_before_page_tabs', 'um_groups_single_confirm_invite' );


/**
 * Remove content of non-existent tabs
 */
function um_groups_single_remove_tab_content() {
	$param_tab = get_query_var('tab');
	$param_sub_tab = get_query_var('sub');
	$tabs = UM()->Groups()->api()->group_tabs;

	if ( ! in_array( $param_tab, $tabs ) && has_action("um_groups_single_page_content__{$param_tab}","um_groups_single_page_content__{$param_tab}") ){
		add_action("um_groups_single_page_content__{$param_tab}","um_groups_single_page_content__discussion");
		remove_action("um_groups_single_page_content__{$param_tab}","um_groups_single_page_content__{$param_tab}");
	}

	if ( ! in_array( $param_sub_tab, $tabs ) && has_action("um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}","um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}")  ){
		add_action("um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}","um_groups_single_page_content__discussion");
		remove_action("um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}","um_groups_single_page_sub_content__{$param_tab}_{$param_sub_tab}");
	}
}
add_action( 'um_groups_single_page_content', 'um_groups_single_remove_tab_content' );