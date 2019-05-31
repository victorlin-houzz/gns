<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Ajax
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Ajax {


	/**
	 * User_Photos_Ajax constructor.
	 */
	function __construct() {
		// delete image
		add_action( 'wp_ajax_um_delete_album_photo', array( $this, 'um_delete_album_photo' ) );

		// update image data
		add_action( 'wp_ajax_update_um_user_photos_image', array( $this, 'update_um_user_photos_image' ) );

		// delete album
		add_action( 'wp_ajax_delete_um_user_photos_album', array( $this, 'delete_um_user_photos_album' ) );

		// update album
		add_action( 'wp_ajax_update_um_user_photos_album', array( $this, 'update_um_user_photos_album' ) );

		// create new album
		add_action( 'wp_ajax_create_um_user_photos_album', array( $this, 'create_um_user_photos_album' ) );

		// Delete all albums & photos
		add_action( 'wp_ajax_delete_my_albums_photos', array( $this, 'delete_my_albums_photos' ) );

		// download all photos
		add_action( 'wp_ajax_download_my_photos', array( $this, 'download_my_photos' ) );




		// load images
		add_action( 'wp_ajax_um_user_photos_load_more', array( $this, 'um_user_photos_load_more' ) );
		add_action( 'wp_ajax_nopriv_um_user_photos_load_more', array( $this, 'um_user_photos_load_more' ) );

		// load view with ajax
		add_action( 'wp_ajax_get_um_user_photos_view', array( $this, 'get_um_ajax_gallery_view' ) );
		add_action( 'wp_ajax_nopriv_get_um_user_photos_view', array( $this, 'get_um_ajax_gallery_view' ) );

		//single album
		add_action( 'wp_ajax_get_um_user_photos_single_album_view', array( $this, 'get_um_user_photos_single_album_view' ) );
		add_action( 'wp_ajax_nopriv_get_um_user_photos_single_album_view', array( $this, 'get_um_user_photos_single_album_view' ) );

	}


	/**
	 *
	 */
	function um_user_photos_load_more() {

		$profile_id = intval( $_POST['profile'] );
		$per_page = intval( $_POST['per_page'] );
		$page_no = intval( $_POST['page'] );
		$offset = $page_no * $per_page;

		$is_my_profile = false;
		if ( is_user_logged_in() && get_current_user_id() == $profile_id ) {
			$is_my_profile = true;
		}
		$photos = [];

		$latest_photos = new \WP_Query( array(
			'post_type'         => 'attachment',
			'author__in'        => array( $profile_id ),
			'post_status'       => 'inherit',
			'post_mime_type'    => 'image',
			'posts_per_page'    => $per_page,
			'offset'            => $offset,
			'meta_query'        => array(
				array(
					'key'     => '_part_of_gallery',
					'value'   => 'yes',
					'compare' => '=',
				)
			)
		) );
		if ( $latest_photos->have_posts() ) {
			while ( $latest_photos->have_posts() ) {
				$latest_photos->the_post();
				$photos[] = get_the_ID();
			}

			UM()->Photos_API()->get_view( 'templates/single-album', array(
				'photos'        => $photos,
				'is_my_profile' => $is_my_profile
			) );
			die;

		} else {
			echo 'empty';
			die;
		}

		wp_reset_postdata();

	}


	/**
	 * Load view with ajax
	 *
	 * @todo wp-util template in future
	 */
	function get_um_ajax_gallery_view() {
		$view = $_POST['template'];
		$theme_view = get_stylesheet_directory().'/ultimate-member/um-user-photos/' . $view . '.php';
		$plugin_view = um_user_photos_path.'/views/' . $view . '.php';

		if ( file_exists( $theme_view ) ) {
			$view_file = $theme_view;
		} else {
			$view_file = $plugin_view;
		}

		$is_my_profile = false;
		$user = um_user('ID');
		if( isset($_POST['user_id'] ) ){
			$user = $_POST['user_id'];
		}
		if ( is_user_logged_in() ) {
			if ( get_current_user_id() == $user ) {
				$is_my_profile = true;
			}
		}

		ob_start();

		if ( isset( $_POST['album_id'] ) ) {
			$album = get_post( $_POST['album_id'] );
		}

		if( isset( $_POST['image_id'] ) ) {
			$photo = get_post( $_POST['image_id'] );
		}

		include $view_file;
		$content = ob_get_clean();
		echo $content;
		die;
	}


	/**
	 * Single album loading
	 *
	 * @todo wp-util template
	 */
	function get_um_user_photos_single_album_view() {

		$id = $_POST['id'];

		$template = 'templates/single-album';
		$theme_view = get_stylesheet_directory().'/ultimate-member/um-user-photos/'.$template.'.php';
		$view_file = um_user_photos_path.'/views/'.$template.'.php';

		if(file_exists($theme_view)){
			$view_file = $theme_view;
		}

		$is_my_profile = false;
		if(is_user_logged_in()){
			if(get_current_user_id() == get_post_field( 'post_author', $id )){
				$is_my_profile = true;
			}
		}

		$photos = get_post_meta($id,'_photos',true);
		$album_title = get_the_title($id);
		$album = get_post($id);
		$album_owner = $album->post_author;

		$album_head_view = um_user_photos_path.'/views/template-parts/album-head.php';
		if(file_exists(get_stylesheet_directory().'/ultimate-member/um-user-photos/views/template-parts/album-head.php')){

			$album_head_view = get_stylesheet_directory().'/ultimate-member/um-user-photos/views/template-parts/album-head.php';

		}

		ob_start();
		include $album_head_view;
		include $view_file;
		$content = ob_get_contents();
		ob_end_clean();
		echo $content;
		die;

	}


	/**
	 * Create new album
	 */
	function create_um_user_photos_album() {

		if (! wp_verify_nonce( $_POST['_wpnonce'],'um_add_album')){
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __('Invalid nonce','um-user-photos');
			echo json_encode($response);
			die;
		}

		/*validation*/
		$error = false;
		$response = [
			'type' => 'error',
			'messages' => []
		];

		$allowed = [
			'image/jpeg',
			'image/png',
			'image/jpg',
			'image/gif',
		];

		if(! isset($_POST['title']) || trim($_POST['title']) == ''){
			$error = true;
			$response['messages'][] = __('Album title is required','um-user-photos');
		}

		if(isset($_FILES['album_cover']['tmp_name']) && $_FILES['album_cover']['tmp_name'] !=''){
			
			if(! in_array($_FILES['album_cover']['type'],$allowed)){
				$error = true;
				$response['messages'][] = $_FILES['album_cover']['type'].' '.__('files are not allowed','um-user-photos');
			}
			
			um_maybe_unset_time_limit();
			
			add_filter( "wp_handle_upload_prefilter", array( $this, "validate_upload" ) );
		}

		if(! is_user_logged_in()){
			$error = true;
			$response['messages'][] = __('Invalid request','um-user-photos');
		}

		if($error){
			echo json_encode($response);
			die;
		}

		/*end validation*/
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$title = sanitize_text_field($_POST['title']);

		$post_id = wp_insert_post([
			'post_type' => 'um_user_photos',
			'post_title' => $title,
			'post_author' => get_current_user_id(),
			'post_status' => 'publish'
		]);
		
		$photos = [];
		
		if(isset($_FILES['album_cover']['tmp_name']) && $_FILES['album_cover']['tmp_name'] !=''){
			
			$uploadedfile = $_FILES['album_cover'];
			$upload_overrides = array( 'test_form' => false );
			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
			
			if($movefile && ! isset($movefile['error'])){

				$wp_filetype = $movefile['type'];
				$filename = $movefile['file'];
				$wp_upload_dir = wp_upload_dir();
				$attachment = array(
					'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
					'post_mime_type' => $wp_filetype,
					'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
					'post_content' => '',
					'post_parent' => $post_id,
					'post_author' => get_current_user_id(),
					'post_status' => 'inherit'
				);

				$attach_id = wp_insert_attachment( $attachment, $filename);
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
				update_post_meta( $attach_id,'_part_of_gallery','yes');
				update_post_meta($post_id,'_thumbnail_id',$attach_id);
				
			}else{
				$response = ['type' => 'error','messages' => [$movefile['error']]];
				echo json_encode($response);
				die;
			}
		}
		

		if(isset($_FILES['album_images']) && count($_FILES['album_images'])){

			$gallery_images = $_FILES['album_images'];
			$count_images = count($_FILES['album_images']['name']);
			for($i=0;$i<$count_images;$i++){

				if(! isset($_FILES['album_images']['tmp_name'][$i]) || trim($_FILES['album_images']['tmp_name'][$i]) == ''){
					continue;
				}

				$uploadedfile = [
					'name' => $_FILES['album_images']['name'][$i],
					'type' => $_FILES['album_images']['type'][$i],
					'tmp_name' => $_FILES['album_images']['tmp_name'][$i],
					'error' => $_FILES['album_images']['error'][$i],
					'size' => $_FILES['album_images']['size'][$i]
				];

				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );

				if($movefile && ! isset( $movefile['error'] )){

					$wp_filetype = $movefile['type'];
					$filename = $movefile['file'];
					$wp_upload_dir = wp_upload_dir();
					$attachment = array(
						'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
						'post_mime_type' => $wp_filetype,
						'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						'post_content' => '',
						'post_parent' => $post_id,
						'post_author' => get_current_user_id(),
						'post_status' => 'inherit'
					);

					$attach_id = wp_insert_attachment( $attachment, $filename);
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id,  $attach_data );
					update_post_meta( $attach_id,'_part_of_gallery','yes');

					$photos[] = $attach_id;
				}
				else{
					$response = ['type' => 'error','messages' => [$movefile['error']]];
					echo json_encode($response);
					die;
				}

			}

		}

		update_post_meta($post_id,'_photos',$photos);
		
		/*
		@param $post_id (int)
		add_action('um_user_photos_after_album_created',function($post_id){
			// custom code
		});
		*/
		do_action('um_user_photos_after_album_created',$post_id);

		echo 'success';
		die;
	}


	/**
	 * Update album
	 */
	function update_um_user_photos_album() {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'],'um_edit_album' ) ) {
			$response = array(
				'type'      => 'error',
				'messages'  => []
			);
			$response['messages'][] = __( 'Invalid nonce', 'um-user-photos' );
			echo json_encode( $response);
			die;
		}

		/*validation*/

		$error = false;
		$response = [
			'type'      => 'error',
			'messages'  => []
		];

		if ( ! isset( $_POST['album_id'] ) || ! is_numeric($_POST['album_id'])){
			$error = true;
			$response['messages'][] = 'Invalid album';
		}

		if(! isset($_POST['album_title']) || trim($_POST['album_title']) == ''){
			$error = true;
			$response['messages'][] = __('Album title is required','um-user-photos');
		}


		$album = get_post(intval($_POST['album_id']));
		if($album && is_user_logged_in()){

			if($album->post_author != get_current_user_id()){
				$error = true;
				$response['messages'][] = __('Invalid request','um-user-photos');
			}

		}else{
			$error = true;
			$response['messages'][] = __('Invalid request','um-user-photos');
		}

		if($error){
			echo json_encode($response);
			die;
		}

		um_maybe_unset_time_limit();

		add_filter( "wp_handle_upload_prefilter", array( $this, "validate_upload" ) );

		/*end validation*/

		$post_id = wp_update_post([
			'ID' => intval($_POST['album_id']),
			'post_title' => sanitize_text_field($_POST['album_title'])
		]);

		$photos = [];
		if(isset($_POST['photos']) && is_array($_POST['photos']) && ! empty($_POST['photos'])){
			$photos = $_POST['photos'];
		}

		if(isset($_FILES['album_cover']['tmp_name']) && trim($_FILES['album_cover']['tmp_name']) !=''){
			$uploadedfile = $_FILES['album_cover'];
			$upload_overrides = array( 'test_form' => false );
			$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
        
			if($movefile && ! isset( $movefile['error'] )){

				$wp_filetype = $movefile['type'];
				$filename = $movefile['file'];
				$wp_upload_dir = wp_upload_dir();
				$attachment = array(
					'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
					'post_mime_type' => $wp_filetype,
					'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
					'post_content' => '',
					'post_parent' => $_POST['album_id'],
					'post_author' => get_current_user_id(),
					'post_status' => 'inherit'
				);

				$attach_id = wp_insert_attachment( $attachment, $filename);
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id,  $attach_data );
				update_post_meta( $attach_id,'_part_of_gallery','yes');

				update_post_meta($_POST['album_id'],'_thumbnail_id',$attach_id);
			} else {
				$response = ['type' => 'error','messages' => [$movefile['error']]];
				echo json_encode($response);
				die;
			}
		}

		// upload more photos and add to $photos array
		if(isset($_FILES['album_images']) && count($_FILES['album_images'])){

			$gallery_images = $_FILES['album_images'];
			$count_images = count($_FILES['album_images']['name']);
			for($i=0;$i<$count_images;$i++){

				if(! isset($_FILES['album_images']['tmp_name'][$i]) || trim($_FILES['album_images']['tmp_name'][$i]) == ''){
					continue;
				}

				$uploadedfile = [
					'name' => $_FILES['album_images']['name'][$i],
					'type' => $_FILES['album_images']['type'][$i],
					'tmp_name' => $_FILES['album_images']['tmp_name'][$i],
					'error' => $_FILES['album_images']['error'][$i],
					'size' => $_FILES['album_images']['size'][$i]
				];

				$upload_overrides = array( 'test_form' => false );
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
            
				if($movefile && ! isset( $movefile['error'] )){

					$wp_filetype = $movefile['type'];
					$filename = $movefile['file'];
					$wp_upload_dir = wp_upload_dir();
					$attachment = array(
						'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ),
						'post_mime_type' => $wp_filetype,
						'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						'post_content' => '',
						'post_parent' => $post_id,
						'post_author' => get_current_user_id(),
						'post_status' => 'inherit'
					);

					$attach_id = wp_insert_attachment( $attachment, $filename);
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id,  $attach_data );
					update_post_meta( $attach_id,'_part_of_gallery','yes');

					$photos[] = $attach_id;
				} else {
					$response = ['type' => 'error','messages' => [$movefile['error']]];
					echo json_encode($response);
					die;
				}

			}

		}

		if ( is_array( $photos ) && ! empty( $photos ) ) {
			update_post_meta( $_POST['album_id'],'_photos', $photos );
		} else {
			delete_post_meta( $_POST['album_id'],'_photos' );
		}
		
		/*
		@param $post_id (int)
		add_action('um_user_photos_after_album_updated',function($post_id){
			// custom code
		});
		*/
		do_action('um_user_photos_after_album_updated',$_POST['album_id']);
		
		echo 'success';
		die;
	}


	/**
	 * delete album
	 */
	function delete_um_user_photos_album() {

		if (! wp_verify_nonce( $_POST['_wpnonce'],'um_delete_album')){
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __('Invalid nonce','um-user-photos');
			echo json_encode($response);
			die;
		}


		$error = false;
		$response = [
			'type' => 'error',
			'messages' => []
		];
		$id = $_POST['id'];
		$album = get_post($id);

		if(! $album){
			$error = true;
			$response['messages'][] = __('Invalid request','um-user-photos');
		}
		else{
			if(! is_user_logged_in() || $album->post_author != get_current_user_id()){
				$error = true;
				$response['messages'][] = __('Invalid request','um-user-photos');
			}
		}

		if($error){
			echo json_encode($response);
			die;
		}

		$photos = get_post_meta($id,'_photos',true);
		$wall_photo = get_post_meta($id,'_thumbnail_id',true);
		
		/*
		@param $post_id (int)
		add_action('um_user_photos_before_album_deleted',function($post_id){
			// custom code
		});
		*/
		do_action('um_user_photos_before_album_deleted',$id);
		
		if(is_array($photos) && ! empty($photos)){
			for($i=0;$i<count($photos);$i++){
				wp_delete_attachment($photos[$i],true);
			}
		}
		if($wall_photo){
			wp_delete_attachment($wall_photo,true);
		}

		wp_delete_post($id,true);
		
		/*
		@param $post_id (int)
		add_action('um_user_photos_after_album_deleted',function($post_id){
			// custom code
		});
		*/
		do_action('um_user_photos_after_album_deleted',$id);
		
		echo 'success';
		die;
	}


	/**
	 * update image
	 */
	function update_um_user_photos_image() {

		if (! wp_verify_nonce( $_POST['_wpnonce'],'um_edit_image')){
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __('Invalid nonce','um-user-photos');
			echo json_encode($response);
			die;
		}

		$error = false;
		$response = [];

		$id = $_POST['id'];

		if(! $id || ! is_numeric($id)){
			$error = true;
			$response['type'] = 'error';
			$response['messages'][] = __('Invalid request','um-user-photos');
		}

		$image = get_post($id);
		if(! $image || $image->post_author != get_current_user_id()){
			$error = true;
			$response['type'] = 'error';
			$response['messages'][] = __('Invalid request','um-user-photos');
		}

		if(! isset($_POST['title']) || trim($_POST['title']) ==''){
			$error = true;
			$response['type'] = 'error';
			$response['messages'][] = __('Title is required','um-user-photos');
		}

		if($error){
			echo json_encode($response);
			die;
		}

		wp_update_post([
			'ID' => intval($id),
			'post_title' => sanitize_text_field($_POST['title']),
			'post_excerpt' => sanitize_text_field($_POST['caption'])
		]);
		
		/*
		@param $attachment_id (int)
		add_action('um_user_photos_after_photo_updated',function($attachment_id){
			// custom code
		});
		*/
		do_action('um_user_photos_after_photo_updated',$id);

		$success_text = __('Update successfull','um-user-photos');
		echo json_encode([
			'type' => 'success',
			'messages' => [$success_text]
		]);
		die;

	}


	/**
	 * Delete image
	 */
	function um_delete_album_photo() {

		if (! wp_verify_nonce( $_POST['_wpnonce'],'um_delete_photo')){
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __('Invalid nonce','um-user-photos');
			echo json_encode($response);
			die;
		}
		
		

		$image_id = intval($_POST['image_id']);
		$album_id = intval($_POST['album_id']);
		
		/*
		@param $attachment_id (int)
		@param $album_id (int)
		add_action('um_user_photos_before_photo_delete',function($attachment_id,$album_id){
			// custom code
		});
		*/
		do_action('um_user_photos_before_photo_delete',$image_id,$album_id);
		
		$album = get_post($album_id);
		$image = get_post($image_id);
		
		$user_id = 0;
		
		if(is_user_logged_in()){
			$user_id = get_current_user_id();
		}

		if(! $user_id || ! $image || ! $album){
			echo 'Invalid request';
			die;
		}
		elseif($image->post_author != $user_id || $album->post_author != $user_id){
			echo 'Invalid request';
			die;
		}
		else{
			
			/*
				@param $attachment_id (int)
				@param $album_id (int)
				add_action('um_user_photos_before_photo_deleted',function($image_id,$album_id){
					// custom code
				});
			*/
			
			do_action('um_user_photos_before_photo_deleted',$image_id,$album_id);
			
			
			wp_delete_attachment($image_id,true);
			
			/*
				@param $attachment_id (int)
				@param $album_id (int)
				add_action('um_user_photos_after_photo_deleted',function($image_id,$album_id){
					// custom code
				});
			*/
			do_action('um_user_photos_after_photo_deleted',$image_id,$album_id);
			
			echo 'success';
			die;
		}

	}

	/**
	 *
	 */
	function delete_my_albums_photos() {

		if (! wp_verify_nonce( $_POST['_wpnonce'],'um_user_photos_delete_all')){
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __('Invalid nonce','um-user-photos');
			echo json_encode($response);
			die;
		}

		if (! is_user_logged_in()){
			$response = [
				'type' => 'error',
				'messages' => []
			];
			$response['messages'][] = __('Invalid request','um-user-photos');
			echo json_encode($response);
			die;
		}

		$profile = $_POST['profile_id'];
		$user_id = get_current_user_id();

		if($profile  == $user_id){

			$photos = new \WP_Query([
				'post_type' => 'attachment',
				'author__in' => [$user_id],
				'post_status' => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'meta_query'    => [
					[
						'key'     => '_part_of_gallery',
						'value'   => 'yes',
						'compare' => '=',
					]
				]
			]);
			if($photos->have_posts()):
				while($photos->have_posts()):$photos->the_post();
					wp_delete_attachment(get_the_ID(),true);
				endwhile;
			endif;
			wp_reset_postdata();
			$albums = new \WP_Query([
				'post_type' => 'um_user_photos',
				'author__in' => [$user_id],
				'posts_per_page' => -1
			]);
			if($albums->have_posts()):
				while($albums->have_posts()):$albums->the_post();
					wp_delete_post(get_the_ID(),true);
				endwhile;
			endif; // has albums
			wp_reset_postdata();
			
			/*
			@param $user_id (int)
			add_action('um_user_photos_after_user_albums_deleted',function($user_id){
				// custom code
			});
			*/
			do_action('um_user_photos_after_user_albums_deleted',$user_id);
			
			echo 'success';
			die;

		} // Profile owner

	}


	/**
	 * Download all photos
	 */
	function download_my_photos() {

		if (! is_user_logged_in()){
			echo __('Invalid request','um-user-photos');;
			die;
		}

		if (! class_exists( '\ZipArchive' ) ) {
			echo __('Your download could not be created. It looks like you do not have ZipArchive installed on your server.','um-user-photos');
			die;
		}

		$profile = $_REQUEST['profile_id'];
		$user_id = get_current_user_id();
		if($profile  == $user_id){

			$photos = new \WP_Query([
				'post_type' => 'attachment',
				'author__in' => [$user_id],
				'post_status' => 'inherit',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'meta_query'    => [
					[
						'key'     => '_part_of_gallery',
						'value'   => 'yes',
						'compare' => '=',
					]
				]
			]);
			if($photos->have_posts()):

				$zip = new \ZipArchive();
				$zip_name = time().'.zip';
				$uploads_dir = WP_CONTENT_DIR.'/uploads/user-photos';
				if(! is_dir($uploads_dir)){
					mkdir($uploads_dir);
				}
				$new_zip = $uploads_dir.'/'.$zip_name;
				$zip_opened = $zip->open($new_zip, \ZipArchive::CREATE );

				while($photos->have_posts()):$photos->the_post();
					$file_path = get_attached_file(get_the_ID(), true );
					$file_type = $filetype = wp_check_filetype($file_path);
					$ext = $file_type['ext'];
					$image_name = get_the_title().'.'.$ext;
					if(file_exists($file_path)){
						$zip->addFile($file_path,$image_name);
					}
				endwhile;

				$zip->close();
				header("Content-type:application/zip");
				header('Content-Disposition: attachment; filename=' . $zip_name);
				readfile( $new_zip );
				unlink($new_zip);
				die;
			endif;
			wp_reset_postdata();

		}

	}


	/**
	 * @param $file
	 *
	 * @return mixed
	 */
	public function validate_upload( $file ) {

		$error = $this->validate_image_data( $file['tmp_name'] );

		if ( $error ) {
			$file['error'] = $error;
		}

		return $file;
	}


	/**
	 * Check image upload and handle errors
	 *
	 * @param $file
	 *
	 * @return null|string
	 */
	public function validate_image_data( $file ) {
		$error = null;

		if ( ! function_exists( 'wp_get_image_editor' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		$image = wp_get_image_editor( $file );
		if ( is_wp_error( $image ) ) {
			return __( 'Your image is invalid!', 'um-user-photos' );
		}

		$image_sizes = $image->get_size();
		$image_info['width'] = $image_sizes['width'];
		$image_info['height'] = $image_sizes['height'];
		$image_info['ratio'] = $image_sizes['width'] / $image_sizes['height'];

		$image_info['quality'] = $image->get_quality();

		$image_type = wp_check_filetype( $file );
		$image_info['extension'] = $image_type['ext'];
		$image_info['mime']= $image_type['type'];
		$image_info['size'] = filesize( $file );

		if ( isset( $image_info['invalid_image'] ) && $image_info['invalid_image'] == true ) {
			$error = __( 'Your image is invalid or too large!', 'um-user-photos' );
		}

		return $error;
	}

}