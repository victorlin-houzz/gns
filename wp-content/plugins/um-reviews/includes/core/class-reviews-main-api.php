<?php
namespace um_ext\um_reviews\core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Reviews_Main_API {


	/**
	 * Reviews_Main_API constructor.
	 */
	function __construct() {

	}


	/**
	 * @param $postid
	 * @param $old_rating
	 * @param $new_rating
	 */
	function adjust_rating( $postid, $old_rating, $new_rating ) {
		update_post_meta( $postid, '_rating', $new_rating );

		$user_id = get_post_meta( $postid, '_user_id', true );
		$reviewer_id = get_post_meta( $postid, '_reviewer_id', true );

		//get all reviews by user_id and update review rating
		$reviews = get_user_meta( $user_id, '_reviews', true );
		if ( isset( $reviews[ $reviewer_id ] ) ) {
			$reviews[ $reviewer_id ] = $new_rating;
			update_user_meta( $user_id, '_reviews', $reviews );
		}

		//update reviews compound
		$reviews_compound = get_user_meta( $user_id, '_reviews_compound', true );
		$reviews_compound = (int)$reviews_compound - $old_rating;
		$reviews_compound = (int)$reviews_compound + $new_rating;
		update_user_meta( $user_id, '_reviews_compound', $reviews_compound );

		//total reviews
		$reviews_total = get_user_meta( $user_id, '_reviews_total', true );

		//update rating average
		if ( $reviews_compound > 0 && $reviews_total > 0 ) {
			$reviews_avg = $reviews_compound / $reviews_total;
			$reviews_avg = number_format( $reviews_avg, 2 );
			update_user_meta( $user_id, '_reviews_avg', (float) $reviews_avg );
		} else {
			update_user_meta( $user_id, '_reviews_avg', (float) number_format( 0, 2 ) );
		}
	}


	/**
	 * Review was created, renew calculations
	 *
	 * @param $postid
	 */
	function publish_review( $postid ) {
		$user_id = (int) get_post_meta( $postid, '_user_id', true );
		$reviewer_id = (int) get_post_meta( $postid, '_reviewer_id', true );
		$rating = get_post_meta( $postid, '_rating', true );

		// update users who reviewed the user
		$reviews = get_user_meta( $user_id, '_reviews', true );
		if ( empty( $reviews ) ) {
			$reviews = array();
		}
		if ( ! isset( $reviews[ $reviewer_id ] ) ) {
			$reviews[ $reviewer_id ] = $rating;
			update_user_meta( $user_id, '_reviews', $reviews );
		}

		//update compounded ratings
		$reviews_compound = absint( get_user_meta( $user_id, '_reviews_compound', true ) );
		$reviews_compound = ( $reviews_compound <= 0 ) ? $rating : $reviews_compound + $rating;
		update_user_meta( $user_id, '_reviews_compound', $reviews_compound );

		// total reviews
		$reviews_total = get_user_meta( $user_id, '_reviews_total', true );
		$reviews_total = empty( $reviews_total ) ? 1 : $reviews_total + 1;
		update_user_meta( $user_id, '_reviews_total', $reviews_total );

		// update rating average
		if ( $reviews_compound > 0 ) {
			$reviews_avg = $reviews_compound / $reviews_total;
			$reviews_avg = number_format( $reviews_avg, 2 );
			update_user_meta( $user_id, '_reviews_avg', (float) $reviews_avg );
		} else {
			update_user_meta( $user_id, '_reviews_avg', (float) number_format( 0, 2 ) );
		}
	}


	/**
	 * Review was removed
	 *
	 * @param $postid
	 */
	function undo_review( $postid ) {
		$user_id = (int) get_post_meta( $postid, '_user_id', true );
		$reviewer_id = (int) get_post_meta( $postid, '_reviewer_id', true );
		$rating = get_post_meta( $postid, '_rating', true );

		$user_exists = get_userdata( $user_id );
		if ( ! $user_exists ) {
			return;
		}

		// update users who reviewed the user
		$reviews = get_user_meta( $user_id, '_reviews', true );
		if ( ! empty( $reviews[ $reviewer_id ] ) ) {
			unset( $reviews[ $reviewer_id ] );
			update_user_meta( $user_id, '_reviews', $reviews );
		}

		// update compounded ratings
		$reviews_compound = get_user_meta( $user_id, '_reviews_compound', true );
		$reviews_compound = (int)$reviews_compound - $rating;
		update_user_meta( $user_id, '_reviews_compound', $reviews_compound );

		// total reviews
		$reviews_total = get_user_meta( $user_id, '_reviews_total', true );
		$reviews_total = (int) $reviews_total - 1;
		if ( $reviews_total < 0 ) {
			$reviews_total = 0;
		}
		update_user_meta( $user_id, '_reviews_total', $reviews_total );

		// update rating average
		if ( $reviews_compound > 0 ) {
			$reviews_avg = $reviews_compound / $reviews_total;
			$reviews_avg = number_format( $reviews_avg, 2 );
			update_user_meta( $user_id, '_reviews_avg', (float) $reviews_avg );
		} else {
			update_user_meta( $user_id, '_reviews_avg', (float) number_format( 0, 2 ) );
		}
	}


	/**
	 * @return int
	 */
	function get_filter() {
		if ( isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] <= 5 && $_REQUEST['filter'] > 0 ) {
			return $_REQUEST['filter'];
		}
		return 0;
	}


	/**
	 *
	 */
	function set_filter() {
		if ( isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] <= 5 && $_REQUEST['filter'] > 0 ) {
			$this->rating_filter = $_REQUEST['filter'];
		} else {
			$this->rating_filter = '';
		}
	}


	/**
	 * Get reviews
	 *
	 * @param $user_id
	 *
	 * @return array|bool|int
	 */
	function get_reviews( $user_id ) {
		$my_review_ = false;
		
		$args = array(
			'post_type' => 'um_review',
			'posts_per_page' => -1,
			'author' => $user_id,
			'post_status' => array('publish'),
		);
		
		if ( $this->already_reviewed( $user_id ) ) {
			$my_review = $this->get_review_detail( $user_id, get_current_user_id() );
			if( isset( $my_review->ID ) ){
				$args['post__not_in'] = array($my_review->ID);
				$my_review_ = true;
			}
		}
		
		$args['meta_query'][] = array(
			'key' => '_status',
			'value' => 1,
			'compare' => '='
		);
			
		if ( $this->rating_filter ) {
			$args['meta_query'][] = array(
				'key' => '_rating',
				'value' => $this->rating_filter,
				'compare' => '='
			);
		}
		
		$review_query = new \WP_Query( $args );
		
		$reviews = $review_query->posts;
		
		if ( isset( $review_query->posts ) && $review_query->found_posts > 0 ) {
			
			return $review_query->posts;
		
		} else {
			if ( $my_review_ == true ) {
				return -1;
			} else {
				return false;
			}
		}
	}


	/**
	 * Get review details
	 *
	 * @param $user_id
	 * @param $reviewer_id
	 *
	 * @return int
	 */
	function get_review_detail( $user_id, $reviewer_id ) {
		
		$args = array(
			'post_type' => 'um_review',
			'posts_per_page' => 1,
			'author' => $user_id,
			'post_status' => array('publish'),
		);
		
		$args['meta_query'][] = array(
			'key' => '_reviewer_id',
			'value' => $reviewer_id,
			'compare' => '='
		);
		
		$review_query = new \WP_Query( $args );
		$review = $review_query->posts;
		if ( isset( $review[0] ) )
			return $review[0];

		return 0;
	}


	/**
	 * Check review pending status
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	function is_pending( $post_id ) {
		$status = get_post_meta( $post_id, '_status', true );
		if ( $status == 0 )
			return true;

		return false;
	}


	/**
	 * Check that already reviewed
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	function already_reviewed( $user_id ) {

		$allow_multiple = apply_filters( "um_reviews_allow_multiple_reviews", false );

		if ( $allow_multiple ) {
			return false;
		}

		$args = array(
			'post_type' => 'um_review',
			'posts_per_page' => 1,
			'author' => $user_id,
			'post_status' => array('publish'),
		);

		$args['meta_query'][] = array(
			'key' => '_reviewer_id',
			'value' => get_current_user_id(),
			'compare' => '='
		);

		$review_query = new \WP_Query( $args );
		$review = $review_query->found_posts;

		if ( $review > 0 ) {
			return true;
		}
		return false;
	}


	/**
	 * Can user edit their review
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	function can_edit( $user_id ) {
		if ( $user_id == get_current_user_id() && UM()->roles()->um_user_can( 'can_remove_own_review' ) ) {
			return true;
		}

		if ( UM()->roles()->um_user_can( 'can_remove_review' ) ) {
			return true;
		}

		return false;
	}


	/**
	 * @param $review_id
	 *
	 * @return bool
	 */
	function is_flagged( $review_id ) {
		if ( get_post_meta( $review_id, '_flagged', true ) )
			return true;
		return false;
	}


	/**
	 * @param $review_id
	 *
	 * @return bool
	 */
	function can_flag( $review_id ) {
		
		// already flagged
		if ( get_post_meta( $review_id, '_flagged', true ) )
			return false;
	
		// logged in but trying as guest
		if ( UM()->options()->get( 'can_flag_review' ) == 'loggedin' && ! is_user_logged_in() )
			return false;
		
		// reviewed only but not true
		if ( UM()->options()->get( 'can_flag_review' ) == 'reviewed' ) {
			$user_id = get_post_meta( $review_id, '_user_id', true );
			if ( $user_id != get_current_user_id() ) {
				return false;
			}
		}
		
		return true;
	}


	/**
	 * @param $user_id
	 * @param bool $reviewer_id
	 * @param bool $raw
	 *
	 * @return bool
	 */
	function can_remove( $user_id, $reviewer_id = false, $raw = false ) {
		if ( ( $reviewer_id == get_current_user_id()  && UM()->roles()->um_user_can('can_remove_own_review') ) ||
		 ( $user_id == get_current_user_id()  && UM()->roles()->um_user_can('can_remove_own_review') ) ||
			UM()->roles()->um_user_can('can_remove_review') ){
			return true;
		
		}

		if ( $raw ) {
			$arr['can_remove_own_review'] = UM()->roles()->um_user_can('can_remove_own_review');
			$arr['can_remove_review'] = UM()->roles()->um_user_can('can_remove_review');
			$arr['user_id'] = $user_id;
			$arr['current_user_id'] = get_current_user_id();
			$arr['is_own_review'] = $this->is_own_review( $reviewer_id );
			$arr['reviewer_id'] = $reviewer_id;
			return $arr;
		}

		return false;
	}


	/**
	 * @param bool $user_id
	 *
	 * @return bool
	 */
	function is_blocked( $user_id = false ) {
		$user_id = ( $user_id ) ? $user_id : get_current_user_id();

		if ( get_user_meta( $user_id, '_cannot_add_review', true ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Can leave a review?
	 *
	 * @param bool $user_id
	 * @return bool
	 */
	function can_review( $user_id = false ) {

		if ( ! is_user_logged_in() ) {
			return false;
		}
		
		if ( $this->is_blocked() ) {
			return false;
		}
		
		if ( ! UM()->roles()->um_user_can( 'can_review' ) ) {
			return false;
		}

		$roles = UM()->roles()->get_all_user_roles( $user_id );
		if ( UM()->roles()->um_user_can( 'can_review_roles' ) &&
		     ( empty( $roles ) || count( array_intersect( $roles, UM()->roles()->um_user_can( 'can_review_roles' ) ) ) <= 0 ) ) {
			return false;
		}

		if ( $this->already_reviewed( $user_id ) ) {
			return false;
		}

		if ( $user_id && $user_id == get_current_user_id() ) {
			return false;
		}

		return true;
	}


	/**
	 * @return string|void
	 */
	function rating_header() {
		$result = ( um_is_myprofile() ) ? __( 'Your Rating', 'um-reviews' ) : __( 'User Rating', 'um-reviews' );
		return $result;
	}


	/**
	 * @param null $user_id
	 *
	 * @return string|void
	 */
	function avg_rating( $user_id = null ) {
		
		$user_id = ( $user_id ) ? $user_id : um_profile_id();
		$total_ratings = (int) get_user_meta( $user_id, '_reviews_total', true );
		$avg_rating = (double) get_user_meta( $user_id, '_reviews_avg', true );
		
		if ( $total_ratings == 1 ) {
			$result = sprintf(__('%s average based on %s review.','um-reviews'), number_format( $avg_rating, 2), number_format( $total_ratings ) );
		} else {
			$result = sprintf(__('%s average based on %s reviews.','um-reviews'), number_format( $avg_rating, 2), number_format( $total_ratings ) );
		}
		
		if ( $total_ratings == 0 && um_is_myprofile() )
			$result = __('Nobody has reviewed you yet.','um-reviews');
		
		if ( $total_ratings == 0 && !um_is_myprofile() )
			$result = __('Nobody has reviewed this user yet.','um-reviews');
		
		return $result;
	}


	/**
	 * @param null $user_id
	 *
	 * @return string
	 */
	function get_rating( $user_id = null ) {
		$user_id = ( $user_id ) ? $user_id : um_profile_id();
		$result = (double) get_user_meta( $user_id, '_reviews_avg', true );
		return number_format( $result, 2 );
	}


	/**
	 * @param null $user_id
	 *
	 * @return string
	 */
	function get_avg_rating( $user_id = null ) {
		$user_id = ( $user_id ) ? $user_id : um_profile_id();
		$result = (double) get_user_meta( $user_id, '_reviews_avg', true );
		return number_format( $result, 2 );
	}


	/**
	 * @param null $user_id
	 *
	 * @return int
	 */
	function get_reviews_count( $user_id = null ) {
		$user_id = ( $user_id ) ? $user_id : um_profile_id();
		$result = (int) get_user_meta( $user_id, '_reviews_total', true );
		return $result;
	}


	/**
	 * @param $i
	 *
	 * @return mixed|string|void
	 */
	function generate_star_rating_url( $i ) {
		$nav_link = UM()->permalinks()->get_current_url( get_option('permalink_structure') );
		$nav_link = remove_query_arg( 'um_action', $nav_link );
		$nav_link = remove_query_arg( 'subnav', $nav_link );
		$nav_link = add_query_arg('profiletab', 'reviews', $nav_link );
		$nav_link = add_query_arg('filter', $i, $nav_link );
		return $nav_link;
	}


	/**
	 *
	 */
	function get_details() {
		$vals = '';

		$reviews = get_user_meta( um_profile_id(), '_reviews', true );
		$reviews_total = get_user_meta( um_profile_id(), '_reviews_total', true );
		$arr_reviews = array();

		if ( isset( $reviews ) && is_array( $reviews ) ) {
			foreach ( $reviews as $reviewer_id => $rating ) {

				/* maybe new code

				$reviewer = get_userdata( $reviewer_id );
				if ( $reviewer === false || um_profile_id() == $reviewer_id ) {
					continue;
				}*/

				if ( is_null( $rating ) ) {
					$arr_reviews[ $reviewer_id ] = 0;
				} else {
					$arr_reviews[ $reviewer_id ] = $rating;
				}
			}
		}
		
		if ( is_array( $arr_reviews ) ) {
			$vals = array_count_values( $arr_reviews );
		}

		for ( $i = 5; $i >= 1; $i-- ) {
			
			$count_of_reviews = ( isset( $vals[ $i ] ) && $reviews_total > 0 ) ? $vals[ $i ] : 0;
			
			if ( $reviews_total ) {
				$progress = number_format( ( ( $count_of_reviews / $reviews_total ) * 100 ) );
			} else {
				$progress = 0;
			} ?>
		
			<span class="um-reviews-detail">
				<span class="um-reviews-d-s"><a href="<?php echo $this->generate_star_rating_url( $i ); ?>"><?php if ( $this->get_filter() == $i ) { echo '<strong>'. sprintf(__('%s Star','um-reviews'), $i ) .'</strong>'; } else { echo sprintf(__('%s Star','um-reviews'), $i); } ?></a></span>
				<a href="<?php echo $this->generate_star_rating_url( $i ); ?>" class="um-reviews-d-p um-tip-n" title="<?php echo sprintf(__('%s reviews (%s)','um-reviews'), $count_of_reviews, $progress . '%' ); ?>"><span data-width="<?php echo $progress; ?>"></span></a>
				<span class="um-reviews-d-n"><?php echo $count_of_reviews; ?></span>
			</span>
		
		<?php }
	}


	/**
	 * @param int $reviewer_id
	 *
	 * @return bool
	 */
	function is_own_review( $reviewer_id ) {
		if ( $reviewer_id == get_current_user_id() ) {
			return true;
		}

		return false;
	}


	/**
	 * Checks the global tab privacy
	 * @return boolean
	 */
	function get_global_tab_privacy() {

		$tab_reviews_privacy = UM()->options()->get( 'profile_tab_reviews_privacy' );

		switch ( $tab_reviews_privacy ) {
			case 0: // anyone - default
				# code...
				break;

			case 1: // guests
				if( is_user_logged_in() ){
					return false;
				}
				break;

			case 2: // members only

				if( ! is_user_logged_in() ){
					return false;
				}

				break;

			case 3: // only the owner
				  if( ! um_is_user_himself() ){
				    return false;
				  }
				break;

			case 4: // specifc roles
				$tab_reviews_roles = UM()->options()->get( 'profile_tab_reviews_roles' );

				if ( is_user_logged_in() ) {
					um_fetch_user( get_current_user_id() );
					if ( empty( $tab_reviews_roles ) ) {
						um_reset_user();
						return true;
					}

					$current_user_roles = um_user( 'roles' );
					if ( empty( $current_user_roles ) || count( array_intersect( $current_user_roles, $tab_reviews_roles ) ) <= 0 ) {
						um_reset_user();
						return false;
					}
				}

				if ( ! is_user_logged_in() ) {

					if ( empty( $tab_reviews_roles ) ) {
						um_reset_user();
						return true;
					}

					return false;
				}

				break;

		}

		return true; // default
	}


	/**
	 *  Checks the user role tab privacy
	 */
	function get_role_tab_privacy() {
		if ( um_is_user_himself() ) {
			um_reset_user();
		} else {
			um_fetch_user( um_get_requested_user() );
		}

		$can_review_tabs = um_user( 'can_have_reviews_tab' );
		if ( empty( $can_review_tabs ) ) {
			return false;
		}

		return true; // default
	}


	/**
	 * Remove reviews from deleted old users
	 */
	function flush_reviews() {

		$user_ids = get_users( array(
			'fields' => 'ids'
		) );

		$args = array(
			'post_type'         => 'um_review',
			'posts_per_page'    => -1,
			'post_status'       => array( 'publish' ),
			'meta_query'        => array(
				'relation'  => 'OR',
				array(
					'key'     => '_reviewer_id',
					'value'   => $user_ids,
					'compare' => 'NOT IN'
				),
				array(
					'key'     => '_user_id',
					'value'   => $user_ids,
					'compare' => 'NOT IN'
				)
			),
			'fields' => 'ids'
		);

		$reviews = get_posts( $args );

		if ( empty( $reviews ) ) {
			return;
		}

		foreach ( $reviews as $review_id ) {
			UM()->Reviews_API()->api()->undo_review( $review_id );
			wp_delete_post( $review_id, true );
		}
	}


	/**
	 * add a review to user
	 */
	function ajax_review_add() {
		UM()->check_ajax_nonce();

		/**
		 * @var string $action ( review_add || review_edit )
		 * @var int $reviewer_id
		 * @var int $user_id
		 * @var $rating
		 * @var string $title
		 * @var string $content
		 * @var int $review_id
		 * @var bool $reviewer_publish
		 */
		extract( $_POST );

		$output = array();
		$output['error'] = '';

		if ( ! isset( $action ) || ! isset( $reviewer_id ) || ! isset( $user_id ) ||
		     ! isset( $rating ) || ! isset( $title ) || ! isset( $content ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-reviews' ) );
		}

		//check capability to review user
		if ( $action != 'um_review_edit' && ! $this->can_review( $user_id ) ) {
			wp_send_json_error( __( 'You can not rate this user.', 'um-reviews' ) );
		}

		if ( empty( $rating ) ) {
			wp_send_json_error( __( 'Please add a rating.', 'um-reviews' ) );
		}

		if ( empty( $title ) ) {
			wp_send_json_error( __( 'You must provide a title.', 'um-reviews' ) );
		}

		if ( empty( $content ) ) {
			wp_send_json_error( __( 'You must provide review content.', 'um-reviews' ) );
		}


		$can_publish_review = (int) $reviewer_publish;

		//prepare review array
		$output = array(
			'rating'    => $rating,
			'title'     => stripslashes( $title ),
			'content'   => wpautop( stripslashes( $content ) ),
		);

		switch( $action ) {
			case 'um_review_edit':
				//update review
				wp_update_post( array(
					'ID'				=> $review_id,
					'post_title'		=> $output['title'],
					'post_content'		=> $output['content']
				) );

				$old_rating = get_post_meta( $review_id, '_rating', true );
				update_post_meta( $review_id, '_rating', $output['rating'] );

				if ( $can_publish_review ) {
					update_post_meta( $review_id, '_status', 1 );
					$this->adjust_rating( $review_id, $old_rating, $output['rating'] );
				} else {
					$output['pending'] = __( 'This review will be moderated by an admin before it is live.', 'um-reviews' );
				}

				break;
			case 'um_review_add':
				//add review
				$review_id = wp_insert_post( array(
					'post_title'    => $output['title'],
					'post_content'  => $output['content'],
					'post_type'     => 'um_review',
					'post_status'   => 'publish',
					'post_author'   => $user_id,
				) );

				update_post_meta( $review_id, '_reviewer_id', $reviewer_id );
				update_post_meta( $review_id, '_user_id', $user_id );
				update_post_meta( $review_id, '_status', $can_publish_review );
				update_post_meta( $review_id, '_rating', $output['rating'] );


				if ( $can_publish_review ) {
					update_post_meta( $review_id, '_status', 1 );
					$this->publish_review( $review_id );
				} else {
					$output['pending'] = __( 'This review will be moderated by an admin before it is live.', 'um-reviews' );
				}

				// send a mail notification
				um_fetch_user( $user_id );
				$reviews_url = add_query_arg( 'profiletab', 'reviews', um_user_profile_url() );

				$reviewer = get_userdata( $reviewer_id );
				$reviewer = $reviewer->display_name;

				UM()->mail()->send( um_user( 'user_email' ), 'review_notice', array(
					'plain_text' => 1,
					'path' => um_reviews_path . 'templates/email/',
					'tags' => array(
						'{rating}',
						'{reviews_link}',
						'{reviewer}',
						'{review_content}'
					),
					'tags_replace' => array(
						sprintf( __( '%s star', 'um-reviews' ), $output['rating'] ),
						$reviews_url,
						$reviewer,
						stripslashes( $content )
					)
				) );

				// reviewed, reviewer (ID and display name), and reviews link
				do_action( 'um_review_is_published', $user_id, $reviewer_id, $reviewer, $reviews_url, $output['title'], $review_id );
				break;
		}

		wp_send_json_success( $output );
	}


	/**
	 * Delete review
	 */
	function ajax_review_trash() {
		UM()->check_ajax_nonce();

		/**
		 * @var $review_id
		 * @var $user_id
		 */
		extract( $_POST );

		$reviewer_id = get_post_meta( $review_id, '_reviewer_id', true );

		if ( ! $this->can_remove( $user_id, $reviewer_id ) ) {
			$output ['message'] = 'error';
			wp_send_json_error( $output );
		}

		$this->undo_review( $review_id );
		$deleted = wp_delete_post( $review_id, true );

		if ( $deleted ) {
			$output['message'] = 'success';
			wp_send_json_success( $output );
		} else {
			$output ['message'] = 'error';
			wp_send_json_error( $output );
		}
	}


	/**
	 * flag a review
	 */
	function ajax_review_flag() {
		UM()->check_ajax_nonce();

		/**
		 * @var $review_id
		 */
		extract( $_POST );

		update_post_meta( $review_id, '_flagged', 1 );

		$output['response'] = __( 'This review has been flagged for admin review', 'um-reviews' );

		wp_send_json_success( $output );
	}


	//class end
}