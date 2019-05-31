<?php
namespace um_ext\um_social_activity\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Activity_Shortcode
 * @package um_ext\um_social_activity\core
 */
class Activity_Shortcode {


	/**
	 * @var bool
	 */
	var $confirm_box = false;


	/**
	 * Activity_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_wall', array( &$this, 'ultimatemember_wall' ) );
		add_shortcode( 'ultimatemember_activity', array( &$this, 'ultimatemember_activity' ) );

		add_shortcode( 'ultimatemember_trending_hashtags', array( &$this, 'ultimatemember_trending_hashtags' ) );

		$this->args = array('');
	}


	/**
	 * Load a compatible template
	 *
	 * @param $tpl
	 * @param int $post_id
	 */
	function load_template( $tpl, $post_id = 0 ) {
		if ( isset( $this->args ) && $this->args ) {
			$options = $this->args;
			extract( $this->args );
		} else {
			$options = '';
		}
		
		if ( $post_id ) {
			$post_link = UM()->Activity_API()->api()->get_permalink( $post_id );
		}

		$file = um_activity_path . 'templates/' . $tpl . '.php';
		$theme_file = get_stylesheet_directory() . '/ultimate-member/templates/activity/' . $tpl . '.php';

		if ( file_exists( $theme_file ) ) {
			$file = $theme_file;
		}

		if ( file_exists( $file ) ) {
			include $file;
		}
	}


	/**
	 * Display trending hashtags
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_trending_hashtags( $args = array() ) {
		UM()->Activity_API()->enqueue()->enqueue_scripts();

		add_action( 'wp_footer', 'um_activity_confirm_box' );

		$defaults = array(
			'trending_days' => absint( UM()->options()->get( 'activity_trending_days' ) ),
			'number' => 10,
		);
		
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $trending_days
		 * @var $number
		 */
		extract( $args );

		ob_start();

		global $wpdb;

		if ( isset( $trending_days ) ) {

			$term_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT term_id FROM $wpdb->term_taxonomy
				INNER JOIN $wpdb->term_relationships ON $wpdb->term_taxonomy.term_taxonomy_id = $wpdb->term_relationships.term_taxonomy_id
				INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
				WHERE $wpdb->posts.post_type = 'um_activity' AND 
					  $wpdb->term_taxonomy.taxonomy = 'um_hashtag' AND 
					  DATE_SUB( CURDATE(), INTERVAL %d DAY ) <= $wpdb->posts.post_date",
				$trending_days
			) );

			if ( count( $term_ids ) > 0 ) {

				 $hashtags = get_terms( array( 'um_hashtag' ), array(
					'orderby' => 'count',
					'order'   => 'DESC',
					'number'  => $number,
					'include' => $term_ids,
				 ));

				$this->args = array( 'hashtags' => $hashtags );
				$this->load_template( 'trending' );
			}
		}

		$output = ob_get_clean();
		return $output;
	}


	/**
	 * Display User's Wall
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_wall( $args = array() ) {
		UM()->Activity_API()->enqueue()->enqueue_scripts();

		add_action( 'wp_footer', 'um_activity_confirm_box' );

		$defaults = array(
			'user_id'   => get_current_user_id(),
			'hashtag'   => ( isset( $_GET['hashtag'] ) ) ? esc_attr( wp_strip_all_tags( $_GET['hashtag'] ) ) : '',
			'wall_post' =>  ( isset( $_GET['wall_post'] ) ) ? absint( $_GET['wall_post'] ) : '',
			'user_wall' => true
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $wall_post
		 * @var $hashtag
		 * @var $user_id
		 * @var $user_wall
		 */
		extract( $args );

		$um_current_page_tab = get_query_var( 'profiletab' );
		if ( um_is_core_page('user') && $um_current_page_tab == 'activity' ) {
			$user_id = $args['user_id'] = um_profile_id();
		}

		$can_view = UM()->Activity_API()->api()->can_view_wall( $user_id );
		if ( $can_view !== true ) {
			return $can_view;
		}

		$is_single_post = $wall_post ? true : false;
		$per_page = UM()->Activity_API()->api()->get_posts_per_page();

		$this->args = $args;

		ob_start(); ?>

		<div class="um">

			<div class="um-form">

				<?php if ( isset( $hashtag ) && $hashtag ) {
					$get_hashtag = get_term_by( 'slug', $hashtag, 'um_hashtag' );
					$hashtag_name = str_replace( '#', '', $get_hashtag->name );
					if ( isset( $hashtag_name ) ) {
						echo '<div class="um-activity-bigtext">#' . $hashtag_name . '</div>';
					}
				}

				if ( is_user_logged_in() && UM()->Activity_API()->api()->can_write() && ! $hashtag && ! $wall_post ) {
					$this->load_template( 'new' );
				}

				if ( is_user_logged_in() && UM()->Activity_API()->api()->can_write() ) {
					$this->load_template( 'edit-post' );
				} ?>

				<div class="um-activity-wall" data-hashtag="<?php echo esc_attr( $hashtag ) ?>"
				     data-user_id="<?php echo esc_attr( $user_id ) ?>"
				     data-user_wall="<?php echo esc_attr( $user_wall ) ?>"
				     data-single_post="<?php echo esc_attr( $is_single_post ) ?>"
				     data-per_page="<?php echo esc_attr( $per_page ) ?>"
				     data-comments_order="<?php echo esc_attr( UM()->options()->get( 'activity_order_comment' ) ) ?>">

					<?php $this->args = array(
						'user_id'   => $user_id,
						'user_wall' => $user_wall,
						'wall_post' => $wall_post,
						'hashtag'   => $hashtag
					);
					if ( is_user_logged_in() ) {
						$this->load_template( 'clone' );
					}
					$this->load_template( 'user-wall' ); ?>
				</div>
			</div>
		</div>

		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * Display site's activity
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_activity( $args = array() ) {
		UM()->Activity_API()->enqueue()->enqueue_scripts();

		add_action( 'wp_footer', 'um_activity_confirm_box' );

		if ( ! is_user_logged_in() && UM()->options()->get( 'activity_require_login' ) ) {
			return __( 'You must login to view this user activity', 'um-activity' );
		}

		$defaults = array(
			'user_id'   => get_current_user_id(),
			'hashtag'   => ( isset( $_GET['hashtag'] ) ) ? esc_attr( wp_strip_all_tags( $_GET['hashtag'] ) ) : '',
			'wall_post' => ( isset( $_GET['wall_post'] ) ) ? absint( $_GET['wall_post'] ) : '',
			'template'  => 'activity',
			'mode'      => 'activity',
			'form_id'   => 'um_activity_id',
			'user_wall' => false
		);
		$args = wp_parse_args( $args, $defaults );
		$this->args = $args;

		if ( empty( $args['use_custom_settings'] ) ) {
			$args = array_merge( $args, UM()->shortcodes()->get_css_args( $args ) );
		} else {
			$args = array_merge( UM()->shortcodes()->get_css_args( $args ), $args );
		}

		/**
		 * @var $mode
		 * @var $form_id
		 * @var $wall_post
		 * @var $user_id
		 * @var $user_wall
		 */
		extract( $args, EXTR_SKIP );

		$per_page = UM()->Activity_API()->api()->get_posts_per_page();

		ob_start(); ?>

		<div class="um <?php echo UM()->shortcodes()->get_class( $mode ); ?> um-<?php echo $form_id; ?>">
			<div class="um-form">

				<?php if ( isset( $hashtag ) && $hashtag ) {
					$get_hashtag = get_term_by('slug', $hashtag, 'um_hashtag');
					$hashtag_name = str_replace( '#', '', $get_hashtag->name );
					if ( isset( $hashtag_name) ) {
						echo '<div class="um-activity-bigtext">#' . $hashtag_name . '</div>';
					}
				}

				if ( is_user_logged_in() && UM()->Activity_API()->api()->can_write() && ! $wall_post && ! $hashtag ) {
					$this->load_template( 'new' );
				}

				if ( is_user_logged_in() && UM()->Activity_API()->api()->can_write() ) {
					$this->load_template( 'edit-post' );
				}

				$is_single_post = $wall_post ? true : false; ?>

				<div class="um-activity-wall" data-user_wall="<?php echo esc_attr( $user_wall ); ?>"
				     data-per_page="<?php echo sanitize_html_class( $per_page ); ?>"
				     data-single_post="<?php echo $is_single_post;?>"
				     data-hashtag="<?php echo $hashtag; ?>"
				     data-user_id="<?php echo esc_attr( $user_id ); ?>"
				     data-comments_order="<?php echo esc_attr( UM()->options()->get( 'activity_order_comment' ) ) ?>">
					<?php $this->args = array(
						'user_wall' => $user_wall,
						'wall_post' => $wall_post,
						'user_id'   => $user_id,
						'hashtag'   => $hashtag
					);
					if ( is_user_logged_in() ) {
						$this->load_template( 'clone' );
					}
					$this->load_template( 'user-wall' ); ?>
				</div>
			</div>
		</div>

		<?php if ( ! is_admin() && ! defined( 'DOING_AJAX' ) ) {
			UM()->shortcodes()->dynamic_css( $args );
		}

		$output = ob_get_clean();
		return $output;
	}

}