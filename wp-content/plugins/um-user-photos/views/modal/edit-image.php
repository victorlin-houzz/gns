<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<form method="post" action="<?= admin_url('admin-ajax.php?action=update_um_user_photos_image'); ?>" enctype="multipart/form-data">
	<div class="um-galley-form-response"></div>
	<div>
		<p >
			<input
					type="text"
					name="title"
					placeholder="<?= __('Image title','um-user-photos'); ?>"
					value="<?= $photo->post_title; ?>"
					required
			/>
		</p>
	</div>
	<div>
		<p >
			<textarea
					name="caption"
					placeholder="<?= __('Image caption','um-user-photos'); ?>"
					required><?= $photo->post_excerpt; ?></textarea>
		</p>
	</div>

	<div class="um-clear"></div>
	<div class="um-user-photos-modal-footer text-right">
		<button type="button" id="um-user-photos-image-update-btn" class="um-modal-btn um-galley-modal-update"><?= __('Update','um-user-photos'); ?></button>
		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link"><?= __('Cancel','um-user-photos'); ?></a>
	</div>
	<input type="hidden" name="id" value="<?= $photo->ID; ?>"/>
	<input type="hidden" name="album" value="<?= $album->ID; ?>"/>
	<?php wp_nonce_field( 'um_edit_image' ); ?>
</form>