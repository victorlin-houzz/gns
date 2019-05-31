<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Default reviews tab
 *
 * @param $args
 */
function um_profile_content_reviews_default( $args ) {

	wp_enqueue_script( 'um_reviews' );
	wp_enqueue_style( 'um_reviews' );

	if ( file_exists( get_stylesheet_directory() . '/ultimate-member/templates/review-overview.php' ) ) {
		include_once get_stylesheet_directory() . '/ultimate-member/templates/review-overview.php';
	} else {
		include_once um_reviews_path . 'templates/review-overview.php';
	}

	if ( file_exists( get_stylesheet_directory() . '/ultimate-member/templates/review-add.php' ) ) {
		include_once get_stylesheet_directory() . '/ultimate-member/templates/review-add.php';
	} else {
		include_once um_reviews_path . 'templates/review-add.php';
	}

	if ( file_exists( get_stylesheet_directory() . '/ultimate-member/templates/review-edit.php' ) ) {
		include_once get_stylesheet_directory() . '/ultimate-member/templates/review-edit.php';
	} else {
		include_once um_reviews_path . 'templates/review-edit.php';
	}

    UM()->Reviews_API()->api()->set_filter();

	$reviews = UM()->Reviews_API()->api()->get_reviews( um_profile_id() );
	if ( $reviews && $reviews != -1 ) {

		if ( file_exists( get_stylesheet_directory() . '/ultimate-member/templates/review-list.php' ) ) {
			include_once get_stylesheet_directory() . '/ultimate-member/templates/review-list.php';
		} else {
			include_once um_reviews_path . 'templates/review-list.php';
		}

	} else {

		if ( UM()->Reviews_API()->api()->already_reviewed( um_profile_id() ) ) {
			$my_review = UM()->Reviews_API()->api()->get_review_detail( um_profile_id(), get_current_user_id() );
			if ( isset( $my_review->ID ) ) {

			}
		} else {

			if ( $reviews == -1 ) {
				if ( file_exists( get_stylesheet_directory() . '/ultimate-member/templates/review-my.php' ) ) {
					include_once get_stylesheet_directory() . '/ultimate-member/templates/review-my.php';
				} else {
					include_once um_reviews_path . 'templates/review-my.php';
				}
			} else {
				if ( file_exists( get_stylesheet_directory() . '/ultimate-member/templates/review-none.php' ) ) {
					include_once get_stylesheet_directory() . '/ultimate-member/templates/review-none.php';
				} else {
					include_once um_reviews_path . 'templates/review-none.php';
				}
			}

		}
	}

}
add_action( 'um_profile_content_reviews_default', 'um_profile_content_reviews_default' );