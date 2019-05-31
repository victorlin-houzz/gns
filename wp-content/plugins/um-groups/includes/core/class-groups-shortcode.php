<?php 
namespace um_ext\um_groups\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Groups_Shortcode{

	function __construct(){
		add_shortcode('ultimatemember_group_new', array( $this, 'create' ) );
		add_shortcode('ultimatemember_groups', array( $this, 'list_shortcode' ) );
		add_shortcode('ultimatemember_my_groups', array( $this, 'own_groups' ) );
		add_shortcode('ultimatemember_groups_profile_list', array( $this, 'profile_list' ) );
		add_shortcode('ultimatemember_group_single', array( $this, 'single' ) );
		add_shortcode('ultimatemember_group_discussion_activity', array( $this, 'discussion_activity' ) );
		add_shortcode('ultimatemember_group_discussion_wall', array( $this, 'discussion_wall' ) );
		add_shortcode('ultimatemember_group_invite_list', array( $this, 'invite_list' ) );


		add_action( 'template_redirect', array( $this, 'redirect_to_login' ) );

		$this->args = array('');
	}


	function redirect_to_login() {
		if ( ( um_is_core_page( 'group_invites' ) || um_is_core_page( 'create_group' ) ) && ! is_user_logged_in() ) {
			$curr = UM()->permalinks()->get_current_url();
			exit( wp_redirect( esc_url( add_query_arg( 'redirect_to', urlencode_deep( $curr ), um_get_core_page( 'login' ) ) ) ) );
		}
	}


	/**
	 * Create a group form
	 */
	public function create( $atts ) {
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		$arr_settings = shortcode_atts( array(
			'group_id' => null,
		), $atts );

		ob_start();

		require_once um_groups_path . 'templates/create.php';
		
		$output = ob_get_clean();
		return $output;

	}


	/**
	 * List profile groups
	 *
	 */
	public function profile_list( $atts ) {
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		$arr_settings = shortcode_atts( array(
			'avatar_size'				=> 'default',
			'category' 					=> 0,
			'groups_per_page' 			=> 10,
			'groups_per_page_mobile' 	=> 10,
			'privacy' 					=> 'all',
			'show_actions'				=> true,
			'show_pagination'			=> true,
			'show_search_form'			=> true,
			'show_search_categories'	=> 1,
			'show_search_tags'			=> 1,
			'show_total_groups_count'	=> false,
			'show_with_greater_than' 	=> 0,
			'show_with_less_than'   	=> 0,
			'sort' 						=> 'newest_groups',
			'own_groups'				=> ''

		), $atts );

		do_action('pre_groups_shortcode_query_list', $arr_settings  );

		ob_start();

		require_once um_groups_path . 'templates/profile/groups-list.php';

		$output = ob_get_clean();
		return $output;
	}


	/**
	 * List groups form
	 *
	 * Sort users by recent_activity, newest_groups, oldest_groups and most_members 
	 */
	public function list_shortcode( $atts ) {
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		$arr_settings = shortcode_atts( array(
			'avatar_size'				=> 'default',
			'category' 					=> 0,
			'groups_per_page' 			=> 10,
			'groups_per_page_mobile' 	=> 10,
			'privacy' 					=> 'all',
			'show_actions'				=> true,
			'show_pagination'			=> true,
			'show_search_form'			=> true,
			'show_search_categories'	=> 1,
			'show_search_tags'			=> 1,
			'show_total_groups_count'	=> true,
			'show_with_greater_than' 	=> 0,
			'show_with_less_than'   	=> 0,
			'sort' 						=> 'newest_groups',


		), $atts );

		do_action('pre_groups_shortcode_query_list', $arr_settings  );

		ob_start();

		include um_groups_path . 'templates/list.php';

		$output = ob_get_clean();
		return $output;
	}


	/**
	 * List own groups
	 */
	public function own_groups( $atts ) {
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		$arr_settings = shortcode_atts( array(
			'avatar_size'				=> 'default',
			'category' 					=> 0,
			'groups_per_page' 			=> 10,
			'groups_per_page_mobile' 	=> 10,
			'privacy' 					=> 'all',
			'show_actions'				=> true,
			'show_pagination'			=> true,
			'show_search_form'			=> true,
			'show_search_categories'	=> 1,
			'show_search_tags'			=> 1,
			'show_total_groups_count'	=> true,
			'show_with_greater_than' 	=> 0,
			'show_with_less_than'   	=> 0,
			'sort' 						=> 'newest_groups',
			'own_groups'				=> true

		), $atts );

		do_action('pre_groups_shortcode_query_list', $arr_settings  );

		ob_start();

		require_once um_groups_path . 'templates/own.php';

		$output = ob_get_clean();
		return $output;
	}


	/**
	 * List single group
	 */
	public function single() {
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );
		wp_enqueue_script( 'um_groups_discussion' );
		wp_enqueue_style( 'um_groups_discussion' );

		ob_start();

		load_template( um_groups_path . 'templates/single.php', false );

		$output = ob_get_clean();
		return $output;
	}


	/**
	 * Display Discussion
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function discussion_activity( $args = array() ) {
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );
		wp_enqueue_script( 'um_groups_discussion' );
		wp_enqueue_style( 'um_groups_discussion' );

		$defaults = array(
			'user_id' => get_current_user_id(),
			'hashtag' => ( isset( $_GET['hashtag'] ) ) ? esc_attr( wp_strip_all_tags( $_GET['hashtag'] ) ) : '',
			'wall_post' =>  ( isset( $_GET['group_post'] ) ) ? absint( $_GET['group_post'] ) : '',
			'template' => 'activity',
			'mode' => 'activity',
			'form_id' => 'um_group_id',
			'user_wall' => 0
		);
		$args = wp_parse_args( $args, $defaults );
		$this->args = $args;

		if ( empty( $args['use_custom_settings'] ) ) {
			$args = array_merge( $args, UM()->shortcodes()->get_css_args( $args ) );
		} else {
			$args = array_merge( UM()->shortcodes()->get_css_args( $args ), $args );
		}
			
		extract( $args, EXTR_SKIP );
			
		ob_start();
			
		$per_page = ( UM()->mobile()->isMobile() ) ? UM()->options()->get( 'groups_posts_num_mob' ) : UM()->options()->get( 'groups_posts_num' );
				
		?>
				
		<div class="um <?php echo UM()->shortcodes()->get_class( $mode ); ?> um-<?php echo $form_id; ?>">

			<div class="um-form">
					
				<?php
				if ( isset( $hashtag ) && $hashtag ) {
					$get_hashtag = get_term_by('slug', $hashtag, 'um_hashtag');
					if ( isset( $get_hashtag->name ) ) {
						echo '<div class="um-groups-bigtext">#' . $get_hashtag->name . '</div>';
					}
				}
						
				if ( UM()->Groups()->discussion()->can_write() ) {
					$this->load_template('new');
				}
				?>
				<?php
				$um_current_page_tab = get_query_var('tab');

				$widget_class = '';
				if(  $um_current_page_tab == 'discussion' ){
					$widget_class = 'user';
				}else{
					$widget_class = "custom_page-{$um_current_page_tab}";
				}

				$is_single_post = false;
				if( $wall_post  ){
					$is_single_post = true;
				}

				$show_pending_approval = get_query_var('show');

				if( ! empty( $show_pending_approval ) && 'pending' == $show_pending_approval ){
					$show_pending_approval = true;
				}else{
					$show_pending_approval = false;
				}
				?>


				<div class="um-groups-wall" data-show-pending="<?php echo $show_pending_approval;?>" data-core_page="<?php echo $widget_class; ?>" data-user_id="<?php echo sanitize_html_class( $user_id ); ?>" data-user_wall="<?php echo sanitize_html_class( $user_wall ); ?>" data-per_page="<?php echo sanitize_html_class( $per_page ); ?>" data-single_post="<?php echo $is_single_post;?>" data-hashtag="<?php echo $hashtag; ?>">
						
					<?php $this->load_template('clone'); ?>
					<?php $this->load_template('user-wall'); ?>
				
				</div>
				
			</div>
					
		</div>

		<?php if ( !is_admin() && !defined( 'DOING_AJAX' ) ) {
			UM()->shortcodes()->dynamic_css( $args );
		}
				
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Load templates
	 */
	function load_template( $tpl, $post_id = 0 ) {
		if ( isset( $this->args ) && $this->args ) {
			$options = $this->args;
			extract( $this->args );
		} else {
			$options = '';
		}
		
		if ( $post_id ) {
			$post_link = UM()->Groups()->discussion()->get_permalink( $post_id );
		}
		
		$file = um_groups_path . 'templates/discussion/' . $tpl . '.php';
		$theme_file = get_stylesheet_directory() . '/ultimate-member/templates/groups/' . $tpl . '.php';

		if ( file_exists( $theme_file ) )
			$file = $theme_file;
			
		if ( file_exists( $file ) )
			include $file;
	}

	/**
	 * Discussion Wall
	 */
	public function discussion_wall( $args = array() ){
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );
		wp_enqueue_script( 'um_groups_discussion' );
		wp_enqueue_style( 'um_groups_discussion' );

		$defaults = array(
			'user_id' => get_current_user_id(),
			'hashtag' => ( isset( $_GET['hashtag'] ) ) ? esc_attr( wp_strip_all_tags( $_GET['hashtag'] ) ) : '',
			'wall_post' =>  ( isset( $_GET['group_post'] ) ) ? absint( $_GET['group_post'] ) : '',
			'user_wall' => 1
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		$this->args = $args;

		ob_start();
		
		if ( UM()->Groups()->discussion()->can_write() && $wall_post == 0 && !$hashtag ) {
			$this->load_template('new');
		}
		
		$per_page = ( UM()->mobile()->isMobile() ) ? UM()->options()->get( 'groups_posts_num_mob' ) : UM()->options()->get( 'groups_posts_num' );
		
		$um_current_page_tab = get_query_var('tab');

		$widget_class = '';
		if ( 'um_groups' == get_post_type() && $um_current_page_tab == 'discussion' ) {
			$widget_class = 'group_profile';
		} else {
			$widget_class = "custom_page-{$um_current_page_tab}";
		}
		
		$is_single_post = false;
		if ( $wall_post ) {
			$is_single_post = true;
		}

		echo '<div class="um-groups-wall" data-hashtag="'.$hashtag.'" data-core_page="'.$widget_class.'" data-user_id="'. esc_attr( $user_id ) . '" data-user_wall="'. esc_attr( $user_wall ) . '" data-single_post="'.$is_single_post.'" data-per_page="' . $per_page . '">';
		
		$this->load_template('clone');
		$this->load_template('user-wall');
		
		echo '</div>';
		
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Invite List
	 */
	public function invite_list( $atts ){
		wp_enqueue_script( 'um_groups' );
		wp_enqueue_style( 'um_groups' );

		$arr_settings = shortcode_atts( array(
			'avatar_size'				=> 'default',
			'category' 					=> 0,
			'groups_per_page' 			=> 10,
			'groups_per_page_mobile' 	=> 10,
			'privacy' 					=> 'all',
			'show_actions'				=> true,
			'show_pagination'			=> true,
			'show_search_form'			=> true,
			'show_search_categories'	=> 1,
			'show_search_tags'			=> 1,
			'show_total_groups_count'	=> true,
			'show_with_greater_than' 	=> 0,
			'show_with_less_than'   	=> 0,
			'sort' 						=> 'newest_groups',
			'own_groups'				=> true

		), $atts );

		do_action( 'pre_groups_shortcode_query_list', $arr_settings );

		ob_start();

		//load_template( um_groups_path . 'templates/invite-list.php', false );
		include um_groups_path . 'templates/invite-list.php';

		$output = ob_get_clean();
		return $output;
	}
}