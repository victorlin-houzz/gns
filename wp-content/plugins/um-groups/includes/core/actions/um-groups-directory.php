<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Clear groups title and content in single page template
 *
 * @param WP_Post $the_post
 *
 * @return mixed
 */
function um_groups_single_page_template( $the_post ) {
	if ( isset( $the_post ) && 'um_groups' == $the_post->post_type && is_single() ) {
		UM()->Groups()->api()->single_group_title = $the_post->post_title;
	}

	return $the_post;
}
add_action( 'the_post', 'um_groups_single_page_template' );


/**
 * Pre query list in shortcode
 *
 * @param $args
 */
function pre_groups_shortcode_query_list( $args ) {

	$search = get_query_var('groups_search');
	$cat 	= get_query_var('cat');
	$tags 	= get_query_var('tags');
	$filter = get_query_var('filter');

	if( 1 == $args['show_search_form'] ){
			
		if( ! empty( $search ) ){
			$args['s'] = $search;
		}

		if( ! empty( $cat )  ){
			$args['cat'] = $cat;
		}

		if( ! empty( $tags )  ){
			$args['tags'] = $tags;
		}
	}

	if( 'own' == $filter ){
		$array_groups = array();
		$groups = UM()->Groups()->member()->get_groups_joined( );
		foreach( $groups as $data ){
			$array_groups[ ] = $data->group_id;
		}
		$args['_um_groups_filter'] = $filter;
		$args['post__in'] = $array_groups;
	}
	
	

	UM()->Groups()->api()->results = UM()->Groups()->api()->get_groups( $args );

	
		
}
add_action( 'pre_groups_shortcode_query_list', 'pre_groups_shortcode_query_list' );


/**
 * Group directory search form
 *
 * @param $args
 */
function um_groups_directory_search_form( $args ) {
	if ( 0 == $args['show_search_form'] ) {
		return;
	}

	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	$search = get_query_var('groups_search');
	$filter = get_query_var('filter');



	echo '<div class="um-groups-directory-header">';
		
		echo '<form class="um-groups-search-form">';

		echo '<input type="text" name="groups_search" placeholder="'.__('Search groups...', 'um-groups' ).'" value="'.esc_attr( $search ).'"/>';

		if( 1 == $args['show_search_categories'] ){
			
			$cat = get_query_var('cat');

			$arr_categories = um_groups_get_categories();

			echo "<select name=\"cat\">";
			echo "<option value=\"\">".__("All Categories","um-groups")."</option>";
			if( ! empty( $arr_categories ) ){
				foreach( $arr_categories as $value => $title ){
					echo "<option value=\"{$value}\" ". selected( $cat, $value, false ) .">{$title}</option>";
				}
			}
			echo "</select>";
			
		}

		if( 1 == $args['show_search_tags'] ){

			$tags = get_query_var('tags');

			$arr_tags = um_groups_get_tags();

			echo "<select name=\"tags\" >";
			echo "<option value=\"\">".__("All Tags","um-groups")."</option>";
			if( ! empty( $arr_tags ) ){
				foreach( $arr_tags as $value => $title ){
					echo "<option value=\"{$value}\" ". selected( $tags, $value, false ) ." >{$title}</option>";
				}
			}
			echo "</select>";

		}

		if( 'own' == $filter ){
			echo '<input type="hidden" name="filter" value="'. esc_attr( $filter ) .'" />';
		}

		echo '<input type="submit" class="" value="'.__('Search', 'um-groups' ).'"/> ';
		echo '<a href="'. get_the_permalink() .'" class="primary-button">'.__('Clear', 'um-groups' ).'</a>';
		
		echo '</form>';
		
		echo '<div class="um-clear"></div>';
	echo '</div>';
}
add_action( 'um_groups_directory_search_form', 'um_groups_directory_search_form' );


/**
 * Display groups directory
 *
 * @param $args
 */
function um_groups_directory( $args ) {
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	if ( um_groups('total_groups') > 0 ) {
		echo '<div class="um-groups-directory">';
		

		foreach( um_groups('groups') as $group ): 
			$slug = UM()->Groups()->api()->get_privacy_slug( $group->ID );
			$count = um_groups_get_member_count( $group->ID );
				
			echo '<div class="um-group-item">';

			if( true == $args['show_actions'] ){
				echo '<div class="actions">';
				echo '<ul>';

				echo '<li>';
				do_action('um_groups_join_button', $group->ID );
				echo '</li>';

				echo '<li class="count-members">';
				echo sprintf( _n( '<span>%s</span> member', '<span>%s</span> members', $count, 'um-groups' ), number_format_i18n( $count ) );
				echo '</li>';

				echo '<li class="last-active">';
				echo '<span>';
				echo __('active, ','um-groups').human_time_diff( UM()->Groups()->api()->get_group_last_activity( $group->ID, true ) ).__(' ago','um-groups');
				echo '</span>';
				echo '</li>';

				echo '</ul>';
				echo '</div>';
			}
					
			echo '<a href="'.get_permalink( $group->ID ).'">';
			if( 'small' == $args['avatar_size'] ){
				echo UM()->Groups()->api()->get_group_image( $group->ID, 'default', 50, 50 );
			}else{
				echo UM()->Groups()->api()->get_group_image( $group->ID, 'default', 100, 100 );
			}
			echo "<h4 class='um-group-name'>".get_the_title( $group->ID )."</h4>";
			echo '</a>';

			echo '<div class="um-group-meta">';
			echo '<ul>';
			echo '<li class="privacy">';
			echo um_groups_get_privacy_icon( $group->ID );
			echo sprintf( __('%s Group', 'um-groups' ), um_groups_get_privacy_title( $group->ID ) );
			echo '</li>';
			echo '<li class="description">' ;
			echo $group->post_content;
			echo '</li>';
			echo '</ul>';
			echo '</div>';
			echo '<div class="um-clear"></div>';
					

			echo '</div>';
			echo '<div class="um-clear"></div>';
		
		endforeach;

		echo '</div>';
		// Restore original Post Data
	} else {
		_e('No groups found.','um-groups');
	}
}
add_action( 'um_groups_directory', 'um_groups_directory' );


/**
 * Pagination
 *
 * @param $args
 */
function um_groups_directory_lazy_load( $args ) {

	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	if(  um_groups('total_groups') > $args['groups_per_page'] && 1 == $args['show_pagination'] ){
		
		$search = get_query_var('groups_search');
		$cat 	= get_query_var('cat');
		$tags 	= get_query_var('tags');
		$filter = get_query_var('filter');


		if( 1 == $args['show_search_form'] ){
			
			if( ! empty( $search ) ){
				$args['s'] = $search;
			}

			if( ! empty( $cat )  ){
				$args['cat'] = $cat;
			}

			if( ! empty( $tags )  ){
				$args['tags'] = $tags;
			}
		}

		
		if( 'own' == $filter ){
			$args['own_groups'] = true;
		}


		echo "<div class='um-groups-list-pagination'>";
		echo "<a href='#' class='um-groups-lazy-load' data-groups-page='1' data-groups-pagi-settings='".htmlspecialchars( json_encode( $args ) )."' data-load-more-text='".__("load more...","um-groups")."'  data-no-more-groups-text='".__("No more groups to show","um-groups")."' >";
		_e("load more...","um-groups");
		echo "</a>";
		echo "</div>";
	}
}
add_action( 'um_groups_directory_footer', 'um_groups_directory_lazy_load' );


/**
 * Groups directory tabs
 *
 * @param $args
 */
function um_groups_directory_tabs( $args ) {

	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	if( false == $args['show_total_groups_count'] || um_is_core_page('my_groups') ) return;

	$filter = get_query_var('filter');
	
	echo '<div id="um-groups-filters" class="um-groups-found-posts">';
	echo '<ul class="filters">';
	echo '<li class="all '.( 'all' == $filter || empty( $filter ) ? 'active': '' ).' "><a href="'.um_get_core_page('groups').'">'.sprintf( __('All Groups <span>%s</span>','um-groups'), um_groups_get_all_groups_count() ).'</a></li>';

	if( is_user_logged_in() ){
		echo '<li class="own '.( 'own' == $filter ? 'active': '' ).'"><a href="'.um_get_core_page('groups').'?filter=own">'.sprintf( __('My Groups <span>%s</span>', 'um-groups'), um_groups_get_own_groups_count() ).'</a></li>';
		echo '<li class="create"><a href="'.um_get_core_page('create_group').'">'.__('Create a Group', 'um-groups').'</a></li>';
	}
	
	echo '</ul>';
	echo '</div>';

}
add_action( 'um_groups_directory_tabs', 'um_groups_directory_tabs' );


/**
 * Own groups directory tabs
 *
 * @param $args
 */
function um_groups_own_directory_tabs( $args ) {
	wp_enqueue_script( 'um_groups' );
	wp_enqueue_style( 'um_groups' );

	if( um_is_core_page('my_groups') ) return;

	echo '<div class="um-groups-found-own-posts">'.sprintf( __('All Groups <span>%s</span>','um-groups'), UM()->Groups()->api()->get_own_groups_count() ).'</div>';
}
add_action( 'um_groups_own_directory_tabs', 'um_groups_own_directory_tabs' );