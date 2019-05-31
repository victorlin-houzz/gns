<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( um_is_myprofile() || $is_my_profile ) { ?>
	<div class="text-center um-user-photos-add">
		<a href="#"
		   data-trigger="um-user-photos-modal"
		   data-modal_title="<?php esc_attr_e( 'New Album', 'um-user-photos' ); ?>"
		   data-modal_view="album-create"
		   class="um-user-photos-add-link um-modal-btn"
		   data-action="<?= admin_url('admin-ajax.php?action=get_um_user_photos_view'); ?>"
		   data-template="modal/add-album"
		   data-scope="new">
			<i class="um-icon-plus"></i> <?= __('New Album','um-user-photos'); ?>
		</a>
	</div>

	<?php UM()->Photos_API()->get_view('modal/modal');
}