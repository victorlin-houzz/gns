<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Integration with UM social activity
 *
 * Class User_Photos_Activity
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Activity {


	/**
	 * User_Photos_Activity constructor.
	 */
	function __construct() {
		add_action( 'um_user_photos_after_album_created', array( $this, 'um_social_activity_post' ) );

		add_action( 'um_user_photos_before_photo_deleted', array( $this,'um_user_photos_before_photo_deleted' ), 10, 2 );

		add_action( 'um_user_photos_after_album_updated', array( $this, 'um_user_photos_after_album_updated' ) );

		add_action( 'um_user_photos_before_album_deleted', array( $this,'um_social_activity_post_delete' ) );
		add_action( 'um_user_photos_after_user_albums_deleted', array( $this,'um_user_photos_after_user_albums_deleted') );
	}


	/**
	 * Build activity template
	 *
	 * @param $album_id
	 * @param array $exclude
	 *
	 * @return string
	 */
	function activity_template( $album_id, $exclude = array() ) {
		$photos = get_post_meta( $album_id, '_photos', true );
		if ( ! is_array( $photos ) || count( $photos ) == 0 ) {
			return '';
		}

		if ( ! empty( $exclude ) ) {
			$photos = array_diff( $photos, $exclude );
			sort( $photos );
		}

		ob_start(); ?>
		<p style="margin:0;padding:0;"><?php for ( $i = 0; $i < count( $photos ); $i++ ) {
				$thumbnail = wp_get_attachment_image_src( $photos[ $i ], 'thumbnail' ); ?>
				<img style="display:inline-block;width:80px;max-width:20%;margin:2px;" src="<?php echo esc_attr( $thumbnail[0] ); ?>" /><?php if ( $i == 4 ) {
					if ( count( $photos ) > 5 ) {
						$remaining = count( $photos ) - 5; 
			?><span class="more-photos" style="text-align:center;display: inline-block;font-size: 2em;width:100px;background: rgba(0,0,0,0.1);height:85px;line-height: 80px;padding:0;margin:0;">&nbsp;+<?php echo $remaining ?></span>
					<?php }
					break;
				}
			} ?></p>
		<?php $album_photos = ob_get_clean();
		return trim($album_photos);
	}


	/**
	 * Get album's cover image
	 *
	 * @param $album_id
	 *
	 * @return string
	 */
	function get_album_cover( $album_id ) {
		$cover_image = UM()->Photos_API()->common()->um_photos_get_album_cover($album_id);
		ob_start();
		?>
		<span data-album="<?= $album_id; ?>" class="post-image"><img src="<?php echo esc_attr($cover_image); ?>" class="um-activity-featured-img" alt="" title="" /></span>
		<?php 
		$cover_image = ob_get_clean();

		return trim($cover_image);
	}


	/**
	 * Create social activity when new album is created
	 *
	 * @param int $album_id
	 */
	function um_social_activity_post( $album_id ) {
		$album = get_post( $album_id );
		$user_id = $album->post_author;

		um_fetch_user( $user_id );
		$author_name = um_user( 'display_name' );
		$author_profile = um_user_profile_url();
		$album_link = add_query_arg( array( 'profiletab' => 'photos' ), $author_profile );

		$cover_image = $this->get_album_cover( $album_id );
		$album_photos = $this->activity_template( $album_id );

		UM()->Activity_API()->api()->save(
			array(
				'template'          => 'new-album',
				'custom_path'       => um_user_photos_path . '/views/social-activity/new-album.php',
				'wall_id'           => $user_id,
				'related_id'        => $album_id,
				'author'            => $user_id,
				'author_name'       => $author_name,
				'author_profile'    => $author_profile,
				'post_title'        => $album->post_title,
				'post_url'          => $album_link,
				'post_excerpt'      => $album_photos,
				'post_image'        => $cover_image,
			)
		);
	}


	/**
	 * Update social activity content when photo in album is removed
	 *
	 * @param int $image_id
	 * @param int $album_id
	 */
	function um_user_photos_before_photo_deleted( $image_id, $album_id ) {

		$activities = get_posts( array(
			'post_type' => 'um_activity',
			'meta_query' => array(
				array(
					'key'       => '_related_id',
					'value'     => $album_id,
					'compare'   => '='
				),
				array(
					'key'       => '_action',
					'value'     => 'new-album',
					'compare'   => '='
				)
			)
		) );

		if ( empty( $activities ) ) {
			return;
		}

		foreach ( $activities as $post ) {

			setup_postdata( $post );

			$album   = get_post( $album_id );
			$user_id = $album->post_author;

			um_fetch_user( $user_id );
			$author_name    = um_user( 'display_name' );
			$author_profile = um_user_profile_url();
			$album_link = add_query_arg( array( 'profiletab' => 'photos' ), $author_profile );

			$cover_image = $this->get_album_cover( $album_id );
			$album_photos = $this->activity_template( $album_id, array( intval( $image_id ) ) );

			UM()->Activity_API()->api()->save(
				array(
					'template'       => 'new-album',
					'custom_path'    => um_user_photos_path . '/views/social-activity/new-album.php',
					'wall_id'        => $user_id,
					'related_id'     => $album_id,
					'author'         => $user_id,
					'author_name'    => $author_name,
					'author_profile' => $author_profile,
					'post_title'     => $album->post_title,
					'post_url'       => $album_link,
					'post_excerpt'   => $album_photos,
					'post_image'     => $cover_image,
				),
				true,
				$post->ID
			);

		}
		wp_reset_postdata();
	}


	/**
	 * Update social activity when user updated album
	 *
	 * @param $album_id
	 */
	function um_user_photos_after_album_updated( $album_id ) {
		$activities = get_posts( array(
			'post_type'     => 'um_activity',
			'meta_query'    => array(
				array(
					'key'       => '_related_id',
					'value'     => $album_id,
					'compare'   => '='
				),
				array(
					'key'       => '_action',
					'value'     => 'new-album',
					'compare'   => '='
				)
			)
		) );

		if ( empty( $activities ) ) {
			return;
		}

		foreach ( $activities as $post ) {
			setup_postdata( $post );

			$album   = get_post( $album_id );
			$user_id = $album->post_author;

			um_fetch_user( $user_id );
			$author_name    = um_user( 'display_name' );
			$author_profile = um_user_profile_url();
			$album_link     = add_query_arg( array( 'profiletab' => 'photos' ), $author_profile );

			$cover_image = $this->get_album_cover( $album_id );
			$album_photos = $this->activity_template( $album_id );

			UM()->Activity_API()->api()->save(
				array(
					'template'       => 'new-album',
					'custom_path'    => um_user_photos_path . '/views/social-activity/new-album.php',
					'wall_id'        => $user_id,
					'related_id'     => $album_id,
					'author'         => $user_id,
					'author_name'    => $author_name,
					'author_profile' => $author_profile,
					'post_title'     => $album->post_title,
					'post_url'       => $album_link,
					'post_excerpt'   => $album_photos,
					'post_image'     => $cover_image,
				),
				true,
				$post->ID
			);

		}
		wp_reset_postdata();
	}


	/**
	 * Delete social activity when album is deleted
	 *
	 * @param int $album_id
	 */
	function um_social_activity_post_delete( $album_id = 0 ) {
		if ( ! $album_id ) {
			return;
		}

		$activities = get_posts( array(
			'post_type' => 'um_activity',
			'meta_query' => array(
				array(
					'key' => '_related_id',
					'value' => $album_id,
					'compare' => '='
				),
				array(
					'key' => '_action',
					'value' => 'new-album',
					'compare' => '='
				)
			)
		) );

		if ( empty( $activities ) ) {
			return;
		}

		foreach ( $activities as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}


	/**
	 * Delete all album related social activity when user deleted all photos & albums
	 *
	 * @param int $user_id
	 */
	function um_user_photos_after_user_albums_deleted( $user_id ) {
		$activities = get_posts( array(
			'post_type'     => 'um_activity',
			'author'	    => $user_id,
			'meta_query'    => array(
				array(
					'key'       => '_action',
					'value'     => 'new-album',
					'compare'   => '='
				)
			)
		) );

		if ( empty( $activities ) ) {
			return;
		}

		foreach ( $activities as $post ) {
			wp_delete_post( $post->ID, true );
		};
	}
}