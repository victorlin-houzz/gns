<?php if ( ! defined( 'ABSPATH' ) ) exit;

$column = UM()->options()->get('um_user_photos_images_column');
if(! $column){
	$column = 'um-user-photos-col-3';
}
$column_num = intval(substr($column,-1));
$per_page = $column_num;

$user_id = um_user('ID');
if(isset($_POST['user_id'])){
	$user_id = $_POST['user_id'];
}
$is_my_profile = false;
if(is_user_logged_in() && get_current_user_id() == $user_id){
	$is_my_profile = true;
}
$photos = [];
$query = new WP_Query([
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

$count = $query->post_count;

$latest_photos = new WP_Query([
	'post_type' => 'attachment',
	'author__in' => [$user_id],
	'post_status' => 'inherit',
	'post_mime_type' => 'image',
	'posts_per_page' => $per_page,
	'meta_query'    => [
		[
			'key'     => '_part_of_gallery',
			'value'   => 'yes',
			'compare' => '=',
		]
	]
]);

if ( $latest_photos->have_posts() ) { ?>
	<div class="um-user-photos-albums">

		<?php while ( $latest_photos->have_posts() ) {
			$latest_photos->the_post();
			$photos[] = get_the_ID();
		} ?>

		<div class="photos-container">

			<?php UM()->Photos_API()->get_view( 'templates/single-album', [
				'photos'        => $photos,
				'is_my_profile' => $is_my_profile
			] ); ?>

		</div>

		<?php if ( $count > $per_page ) { ?>
			<div class="um-load-more">
				<div class="um-clear">
					<hr/>
				</div>
				<p class="text-center">
					<button id="um-user-photos-toggle-view-photos-load-more"
					        data-href="<?= admin_url( 'admin-ajax.php?action=um_user_photos_load_more' ); ?>"
					        class="um-modal-btn alt"
					        data-current_page="1"
					        data-per_page="<?= $per_page; ?>"
					        data-profile="<?= $user_id; ?>">
						<?= __( 'Load more', 'um-user-photos' ); ?>
					</button>
				</p>
			</div>
		<?php } ?>
		<div class="um-clear"></div>
	</div>

<?php } else { ?>
	<p class="text-center"><?php _e('Nothing to display','um-user-photos') ?></p>
<?php }

wp_reset_postdata();