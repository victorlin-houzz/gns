<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<form method="post" action="<?= admin_url('admin-ajax.php?action=create_um_user_photos_album'); ?>" enctype="multipart/form-data" class="um-user-photos-modal-form"  data-max_size_error="<?= __('is too large. File should be less than ','um-user-photos'); ?>" data-max_size="<?= wp_max_upload_size(); ?>">
	<div class="um-galley-form-response"></div>
	<div>
		<p >
			<input type="text" name="title" placeholder="<?= __('Album title','um-user-photos'); ?>" required/>
		</p>
	</div>
	<div>
		<div class="text-center">
			<h1 class="album-poster-holder">
				<label class="album-poster-label">
					<i class="um-faicon-picture-o"></i><br/>
					<span><?= __('Album cover','um-user-photos'); ?></span>
					<input id="um-user-photos-input-album-cover" style="display:none;" type="file" name="album_cover" accept="image/*" />
				</label>
			</h1>
		</div>
		<br/>
		<p class="text-center">
			<label class="um-modal-btn alt">
				<i class="um-icon-plus"></i>
				<?= __('Add photos','um-user-photos'); ?>
				<input id="um-user-photos-input-album-images" style="display:none;" type="file" name="album_images[]" accept="image/*" multiple />
			</label>
		</p>
		<div id="um-user-photos-images-uploaded"></div>
		<div class="clearfix"></div>
	</div>
	<div class="um-clear"></div>
	<div class="um-user-photos-modal-footer text-right">
		<button type="button" class="um-modal-btn um-galley-modal-submit"><?= __('Add','um-user-photos'); ?></button>
		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?= __('Cancel','um-user-photos'); ?></a>
	</div>
	<?php wp_nonce_field( 'um_add_album' ); ?>
</form>