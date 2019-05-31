<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	/***
	***	@display um activities in rss
	***/
	add_filter( 'request', 'um_activity_display_in_rss' );
	function um_activity_display_in_rss( $query ) {
		if ( isset( $query['feed'] ) && isset( $_GET['post_type'] )
			&& $_GET['post_type'] == 'um_activity' ) {
			$query['post_type'] = 'um_activity';
		}

		return $query;
	}

	/***
	***	@Update the RSS item permalink
	***/
	add_filter( 'the_permalink_rss', 'um_activity_permalink_rss' );
	function um_activity_permalink_rss( $link ) {
		if( isset( $_GET['post_type'] ) && $_GET['post_type'] = 'um_activity'
			&& preg_match( '/um_activity\/(\d+)/', $link, $matches ) ) {
			return UM()->Activity_API()->api()->get_permalink( $matches[1] );
		}

		return $link;
	}

	/***
	***	@Update the RSS item title
	***/
	add_filter( 'the_title_rss', 'um_activity_title_rss' );
	function um_activity_title_rss( $title ) {
		if( isset( $_GET['post_type'] ) && $_GET['post_type'] = 'um_activity'
			&& preg_match( '/(\d+)/', $title, $matches ) ) {
			$title = strip_tags( get_the_excerpt() );
			$end   = strpos( $title, '.' );

			// if the content has more than on one sentence.
			if( $end !== false ) {
				$title = substr( $title, 0, $end );
			}
		}

		return $title;
	}
