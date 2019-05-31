<?php
namespace um_ext\um_reviews\core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Reviews_Shortcode {


	/**
	 * Reviews_Shortcode constructor.
	 */
	function __construct() {
	
		add_shortcode( 'ultimatemember_top_rated', array( &$this, 'ultimatemember_top_rated' ) );
		add_shortcode( 'ultimatemember_most_rated', array( &$this, 'ultimatemember_most_rated' ) );
		add_shortcode( 'ultimatemember_lowest_rated', array( &$this, 'ultimatemember_lowest_rated' ) );
		
	}


	/**
	 * Most Rated Shortcode
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_most_rated( $args = array() ) {
		wp_enqueue_script( 'um_reviews' );
		wp_enqueue_style( 'um_reviews' );

		$defaults = array(
			'roles' => 0,
			'number' => 5
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $number
		 */
		extract( $args );

		ob_start();
		
		$query_args = array(
			'fields'    => 'ID',
			'number'    => $number,
			'meta_key'  => '_reviews_total',
			'orderby'   => 'meta_value',
			'order'     => 'desc'
		);

		if ( isset( $roles ) && $roles != 'all' ) {
			$query_args['role__in'] = $roles;
		}

		$users = new \WP_User_Query( $query_args ); ?>
		
		<ul class="um-reviews-widget top-rated">
		
			<?php foreach( $users->results as $user_id ) {
				
				um_fetch_user( $user_id );

				$count = UM()->Reviews_API()->api()->get_reviews_count( $user_id ); ?>
			
				<li>

					<div class="um-reviews-widget-pic">
						<a href="<?php echo um_user_profile_url(); ?>"><?php echo get_avatar( $user_id, 40 ); ?></a>
					</div>

					<div class="um-reviews-widget-user">

						<div class="um-reviews-widget-name"><a href="<?php echo um_user_profile_url(); ?>"><?php echo um_user('display_name'); ?></a></div>

						<div class="um-reviews-widget-rating"><span class="um-reviews-avg" data-number="5" data-score="<?php echo UM()->Reviews_API()->api()->get_rating( $user_id ); ?>"></span></div>

						<?php if ( $count == 1 ) { ?>
							<div class="um-reviews-widget-avg"><?php printf(__('%s average based on %s review','um-reviews'), UM()->Reviews_API()->api()->get_avg_rating( $user_id ), $count ); ?></div>
						<?php } else { ?>
							<div class="um-reviews-widget-avg"><?php printf(__('%s average based on %s reviews','um-reviews'), UM()->Reviews_API()->api()->get_avg_rating( $user_id ), $count ); ?></div>
						<?php } ?>

					</div><div class="um-clear"></div>

				</li>
			
			<?php } ?>
			
		</ul>
		
		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * Top Rated Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_top_rated( $args = array() ) {
		wp_enqueue_script( 'um_reviews' );
		wp_enqueue_style( 'um_reviews' );

		$defaults = array(
			'roles' => 0,
			'number' => 5
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $number
		 */
		extract( $args );

		ob_start();
		
		$query_args = array(
			'fields'    => 'ID',
			'number'    => $number,
			'meta_query' => array(
				'_reviews_avg' => array(
					'key'       => '_reviews_avg',
					'compare'   => 'EXISTS'
				),
				'_reviews_total' => array(
					'key'       => '_reviews_total',
					'compare'   => 'EXISTS'
				),
				'relation' => 'AND',
			),
			'orderby'   => array(
				'_reviews_avg'      => 'desc',
				'_reviews_total'    => 'desc'
			)
		);

		if ( isset( $roles ) && $roles != 'all' ) {
			$query_args['role__in'] = $roles;
		}

		$users = new \WP_User_Query( $query_args ); ?>
		
		<ul class="um-reviews-widget top-rated">
		
			<?php foreach( $users->results as $user_id ) {
				
				um_fetch_user( $user_id );

				$count = UM()->Reviews_API()->api()->get_reviews_count( $user_id ); ?>
			
				<li>

					<div class="um-reviews-widget-pic">
						<a href="<?php echo um_user_profile_url(); ?>"><?php echo get_avatar( $user_id, 40 ); ?></a>
					</div>

					<div class="um-reviews-widget-user">

						<div class="um-reviews-widget-name"><a href="<?php echo um_user_profile_url(); ?>"><?php echo um_user('display_name'); ?></a></div>

						<div class="um-reviews-widget-rating"><span class="um-reviews-avg" data-number="5" data-score="<?php echo UM()->Reviews_API()->api()->get_rating( $user_id ); ?>"></span></div>

						<?php if ( $count == 1 ) { ?>
							<div class="um-reviews-widget-avg"><?php printf(__('%s average based on %s review','um-reviews'), UM()->Reviews_API()->api()->get_avg_rating( $user_id ), $count ); ?></div>
						<?php } else { ?>
							<div class="um-reviews-widget-avg"><?php printf(__('%s average based on %s reviews','um-reviews'), UM()->Reviews_API()->api()->get_avg_rating( $user_id ), $count ); ?></div>
						<?php } ?>

					</div><div class="um-clear"></div>

				</li>
			
			<?php } ?>
			
		</ul>
		
		<?php $output = ob_get_clean();
		return $output;
	}


	/**
	 * Lowest Rated Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_lowest_rated( $args = array() ) {
		wp_enqueue_script( 'um_reviews' );
		wp_enqueue_style( 'um_reviews' );

		$defaults = array(
			'roles' => 0,
			'number' => 5
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $number
		 */
		extract( $args );
		
		ob_start();
		
		$query_args = array(
			'fields'    => 'ID',
			'number'    => $number,
			'meta_key'  => '_reviews_avg',
			'orderby'   => 'meta_value',
			'order'     => 'asc'
		);
		
		if ( isset( $roles ) && $roles != 'all' ) {
			$query_args['role__in'] = $roles;
		}

		$users = new \WP_User_Query( $query_args ); ?>
		
		<ul class="um-reviews-widget top-rated">
		
			<?php foreach( $users->get_results() as $user_id ) {
				
				um_fetch_user( $user_id );

				$count = UM()->Reviews_API()->api()->get_reviews_count( $user_id ); ?>
			
				<li>

					<div class="um-reviews-widget-pic">
						<a href="<?php echo um_user_profile_url(); ?>"><?php echo get_avatar( $user_id, 40 ); ?></a>
					</div>

					<div class="um-reviews-widget-user">

						<div class="um-reviews-widget-name"><a href="<?php echo um_user_profile_url(); ?>"><?php echo um_user('display_name'); ?></a></div>

						<div class="um-reviews-widget-rating"><span class="um-reviews-avg" data-number="5" data-score="<?php echo UM()->Reviews_API()->api()->get_rating( $user_id ); ?>"></span></div>

						<?php if ( $count == 1 ) { ?>
						<div class="um-reviews-widget-avg"><?php printf(__('%s average based on %s review','um-reviews'), UM()->Reviews_API()->api()->get_avg_rating( $user_id ), $count ); ?></div>
						<?php } else { ?>
						<div class="um-reviews-widget-avg"><?php printf(__('%s average based on %s reviews','um-reviews'), UM()->Reviews_API()->api()->get_avg_rating( $user_id ), $count ); ?></div>
						<?php } ?>

					</div><div class="um-clear"></div>

				</li>
			
			<?php } ?>
			
		</ul>
		
		<?php $output = ob_get_clean();
		return $output;
	}

	//class end
}