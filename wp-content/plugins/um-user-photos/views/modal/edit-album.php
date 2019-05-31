<?php if ( ! defined( 'ABSPATH' ) ) exit;
$image = wp_get_attachment_image_src(get_post_thumbnail_id($album->ID),'album_cover');
$bg_image = $image[0];
$photos = get_post_meta($album->ID,'_photos',true); ?>

<form id="um-user-photos-form-edit-album" class="um-user-photos-modal-form" action="<?= admin_url('admin-ajax.php?action=update_um_user_photos_album'); ?>" method="post" enctype="multipart/form-data"  data-max_size_error="<?= __(' is too large. File should be less than ','um-user-photos'); ?>" data-max_size="<?= wp_max_upload_size(); ?>">
	<div class="um-galley-form-response"></div>
	<div>
		<input
				type="text"
				placeholder="<?= __('Album title','um-user-photos'); ?>"
				name="album_title"
				value="<?= $album->post_title; ?>"
				required
		/>

	</div>
	<br/>
	<div>
		<div class="text-center">
			<h1 class="album-poster-holder" style="background-image:url(<?= $bg_image; ?>);">
				<label class="album-poster-label">
					<i class="um-faicon-picture-o"></i><br/>
					<span><?= __('Album cover','um-user-photos'); ?></span>
					<input
							id="um-user-photos-input-album-cover"
							style="display:none;"
							type="file"
							name="album_cover"
							accept="image/*"/>
				</label>
			</h1>
		</div>
		<?php if(is_array($photos) && ! empty($photos)): ?>
			<br/>
			<div class="um-user-photos-album-photos">
				<?php
				for($i=0;$i<count($photos);$i++):
					$image = wp_get_attachment_image_src($photos[$i],'thumbnail');
					if(! $image){
						continue;
					}
					?>
					<div class="um-user-photos-photo" id="album-photo-<?= $photos[$i]; ?>">
						<p class="image-holder">
							<img src="<?= $image[0]; ?>"/>
						</p>
						<input type="hidden" name="photos[]" value="<?= $photos[$i]; ?>"/>
						<a class="photo-delete-link um-tip-n"
						   href="<?= admin_url('admin-ajax.php?action=um_delete_album_photo'); ?>"
						   data-id="<?= $photos[$i]; ?>"
						   data-album="<?= $album->ID; ?>"
						   data-wpnonce="<?= wp_create_nonce('um_delete_photo'); ?>"
						   original-title="<?= __('Delete photo','um-user-photos'); ?>"
						   data-confirmation="<?= __('Sure to delete photo?','um-user-photos'); ?>"
						   data-delete_photo="#album-photo-<?= $photos[$i]; ?>"
						><i class="um-faicon-times"></i></a>
					</div>
				<?php
				endfor;
				?>
				<div class="um-clear"></div>
			</div>
			<div class="um-clear"></div>
		<?php endif; ?>
		<br/>
		<p class="text-center">
			<label
					class="um-modal-btn alt">
				<i class="um-icon-plus"></i> <?= __('Add photos','um-user-photos'); ?>
				<input id="um-user-photos-input-album-images"
				       style="display:none;"
				       type="file" name="album_images[]" accept="image/*"
				       multiple/>
			</label>
		</p>
		<div id="um-user-photos-images-uploaded"></div>
		<div class="um-clear"></div>
	</div>

                
	<div class="um-clear"></div>
            
	<div class="um-user-photos-modal-footer text-right">
                
		<button
				id="um-user-photos-album-update"
				class="um-modal-btn"
				data-album_action="<?= admin_url('admin-ajax.php?action=get_um_user_photos_single_album_view'); ?>"
		><?= __('Update','um-user-photos'); ?></button>
                
		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?= __('Cancel','um-user-photos'); ?></a>
	</div>
	<input type="hidden" name="album_id" value="<?= $album->ID; ?>"/>
	<?php wp_nonce_field( 'um_edit_album' ); ?>
</form>