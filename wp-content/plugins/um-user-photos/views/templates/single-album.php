<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! empty( $photos ) && is_array( $photos ) ) {
	$column = UM()->options()->get('um_user_photos_images_column');
	if ( ! $column ) {
		$column = 'um-user-photos-col-3';
	} ?>
	<div class="um-user-photos-single-album">
		<?php for( $i=0; $i < count( $photos );$i++) {
			$full_image = wp_get_attachment_image_src($photos[$i],'full');
			$thumbnail_image = wp_get_attachment_image_src($photos[$i],'gallery_image');
			if ( ! $thumbnail_image) {
				continue;
			}
			$caption = wp_get_attachment_caption($photos[$i]);
			$img_title = get_the_title($photos[$i]);
			if ( ! $is_my_profile ) { ?>

				<div class="um-user-photos-image-block <?= $column; ?>">
					<a data-caption="<?= $caption; ?>"
					   title="<?= $img_title; ?>"
					   href="<?= $full_image[0]; ?>"
					   class="um-user-photos-image"
					   data-umfancybox="images"
					>
						<img
								src="<?= $thumbnail_image[0]; ?>"
								alt="<?= $img_title; ?>"
						/>
					</a>
				</div>

			<?php } else { ?>
   
				<div class="um-user-photos-image-block um-user-photos-image-block-editable <?= $column; ?>">
					<div class="um-user-photos-image-block-buttons">
						<a href="#"
						   data-trigger="um-user-photos-modal"
						   data-modal_title="<?= __('Edit Image','um-user-photos'); ?>"
						   data-modal_view="album-edit"
						   class="um-user-photos-add-link"
						   title="<?= __('Edit Image','um-user-photos'); ?>"
						   data-action="<?= admin_url('admin-ajax.php?action=get_um_user_photos_view'); ?>"
						   data-template="modal/edit-image"
						   data-scope="edit"
						   data-edit="image"
						   data-id="<?= $photos[$i]; ?>"
						   data-album="<?= $id; ?>"
						>
							<i class="um-faicon-pencil"></i>
						</a>
					</div>
					<div class="um-user-photos-image">
						<a data-caption="<?= $caption; ?>"
						   title="<?= $img_title; ?>"
						   href="<?= $full_image[0]; ?>"
						   class="um-user-photos-image"
						   data-umfancybox="images"
						>
							<img
									src="<?= $thumbnail_image[0]; ?>"
									alt="<?= $img_title; ?>"
							/>
						</a>
					</div>
				</div>

			<?php }
		} ?>

		<div class="um-clear"></div>
	</div>
<?php } else { ?>
	<p class="text-center"><?= __('Nothing to display','um-user-photos'); ?></p>
<?php }

if ( $is_my_profile ) {
	UM()->Photos_API()->get_view( 'modal/modal' );
}