<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<form>
	<div class="um-galley-form-response"></div>
	<h3 class="text-center" style="padding-top:0;margin-top:0;">
		<?php _e( 'Are you sure to delete this album?','um-user-photos' ); ?>
	</h3>
	<div class="clearfix"></div>
	<div class="um-user-photos-modal-footer text-right">
		<button
				data-id="<?php echo esc_attr( $album->ID ); ?>"
				id="delete-um-album"
				class="um-modal-btn"
				data-wpnonce="<?= wp_create_nonce('um_delete_album'); ?>"
				data-action="<?= admin_url('admin-ajax.php?action=delete_um_user_photos_album'); ?>">
			<?php _e( 'Delete','um-user-photos' ); ?>
		</button>
		<a href="javascript:void(0);" class="um-modal-btn alt um-user-photos-modal-close-link">
			<?php _e( 'Cancel', 'um-user-photos' ); ?>
		</a>
	</div>
</form>