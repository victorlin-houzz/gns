<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Extend settings
 *
 * @param array $settings
 *
 * @return array
 */
function um_reviews_settings( $settings ) {
	$settings['licenses']['fields'][] = array(
		'id'        => 'um_reviews_license_key',
		'label'     => __( 'Reviews License Key', 'um-reviews' ),
		'item_name' => 'User Reviews',
		'author'    => 'Ultimate Member',
		'version'   => um_reviews_version,
	);

	$key = ! empty( $settings['extensions']['sections'] ) ? 'reviews' : '';
	$settings['extensions']['sections'][$key] = array(
		'title'     => __( 'Reviews','um-reviews'),
		'fields'    => array(
			array(
				'id'    => 'members_show_rating',
				'type'  => 'checkbox',
				'label' => __( 'Show user rating in members directory','um-reviews' ),
			),
			array(
				'id'            => 'can_flag_review',
				'type'          => 'select',
				'label'         => __( 'Who can flag reviews', 'um-reviews' ),
				'options'       => array(
					'everyone'  => __( 'Everyone', 'um-reviews' ),
					'reviewed'  => __( 'Reviewed user only', 'um-reviews' ),
					'loggedin'  => __( 'All Logged-in Users', 'um-reviews' ),
				),
				'placeholder'   => __( 'Select...', 'um-reviews' ),
				'size'          => 'small',
			)
		)
	);

	return $settings;
}
add_filter( 'um_settings_structure', 'um_reviews_settings', 10, 1 );


/**
 * Extend email notifications
 *
 * @param array $email_notifications
 *
 * @return array
 */
function um_reviews_email_notifications( $email_notifications ) {
	$email_notifications['review_notice'] = array(
		'key'               => 'review_notice',
		'title'             => __( 'New Review Notification','um-reviews' ),
		'subject'           => 'You\'ve got a new {rating} review!',
		'body'              => 'Hi {display_name},<br /><br />' .
		                       'You\'ve received a new {rating} review from {reviewer}!<br /><br />' .
		                       'Here is the review content:<br /><br />' .
		                       '{review_content}<br /><br />' .
		                       '{reviews_link}<br /><br />' .
		                       'This is an automated notification from {site_name}. You do not need to reply.',
		'description'       => __('Send a notification to user when he receives a new review','um-reviews'),
		'recipient'         => 'user',
		'default_active'    => true
	);

	return $email_notifications;
}
add_filter( 'um_email_notifications', 'um_reviews_email_notifications', 10, 1 );


/**
 * Extend UM:Notifications notifications
 *
 * @param array $notifications_log
 *
 * @return array
 */
function um_reviews_notifications_log( $notifications_log ) {
	$notifications_log['user_review'] = array(
		'title'         => __( 'New user review', 'um-reviews' ),
		'template'      => __( '<strong>{member}</strong> has left you a new review. <span class="b1">"{review_excerpt}"</span>', 'um-reviews' ),
		'account_desc'  => __( 'When someone leaves me a review', 'um-reviews' ),
	);

	return $notifications_log;
}
add_filter( 'um_notifications_core_log_types', 'um_reviews_notifications_log', 10, 1 );