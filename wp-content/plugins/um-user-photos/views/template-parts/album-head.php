<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="um-user-photos-album-head">
	<div class="col-back">
		<a
				href="#"
				class="back-to-um-user-photos"
				data-action="<?= admin_url('admin-ajax.php?action=get_um_user_photos_view'); ?>"
				data-template="templates/gallery"
				data-profile="<?php echo esc_attr( $album_owner ); ?>"
		>
			<span class="um-icon-arrow-left-c"></span> <?= __('Back','um-user-photos'); ?>
		</a>
	</div>
  
	<div class="col-title">
		<h2><?= $album_title; ?></h2>
	</div>
  
	<div class="col-delete">
		<?php if ( $is_my_profile ) { ?>
			<a href="" class="um-user-photos-album-options"><i class="um-faicon-cog"></i></a>
			<div class="um-dropdown">
			<div class="um-dropdown-b">
				<div class="um-dropdown-arr"><i class="um-icon-arrow-up-b"></i></div>
				<ul>
					<li>
						<a href="#"
						   data-trigger="um-user-photos-modal"
						   data-modal_title="Edit Album"
						   data-modal_view="album-edit"
						   original-title="Edit Album"
						   data-action="<?= admin_url('admin-ajax.php?action=get_um_user_photos_view'); ?>"
						   data-template="modal/edit-album"
						   data-scope="edit"
						   data-edit="album"
						   data-id="<?php echo esc_attr( $id ); ?>"
						>
							<?= __('Edit album','um-user-photos'); ?>
						</a>
					</li>
					<li>
						<a href="#"
						   data-trigger="um-user-photos-modal"
						   data-modal_title="<?= __('Delete album','um-user-photos'); ?>"
						   data-modal_view="album-delete"
						   original-title="<?= __('Delete album','um-user-photos'); ?>"
						   data-action="<?= admin_url('admin-ajax.php?action=get_um_user_photos_view'); ?>"
						   data-template="modal/delete-album"
						   data-scope="edit"
						   data-edit="album"
						   data-id="<?php echo esc_attr( $id ); ?>"
						>
							<?= __('Delete album','um-user-photos'); ?>
						</a>
					</li>
					<li><a href="#" class="um-dropdown-hide">Cancel</a></li>
				</ul>
			</div>
		</div>
		<?php } ?>
	</div>
	<div class="clearfix"></div>
</div>