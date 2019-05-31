<?php if ( ! defined( 'ABSPATH' ) ) exit;

$column = UM()->options()->get('um_user_photos_albums_column');
if ( ! $column ) {
	$column = 'um-user-photos-col-2';
}

$img = UM()->Photos_API()->common()->um_photos_get_album_cover($id);
?>

<div class="um-user-photos-album <?= $column; ?>">
	<a href="#"
	   class="um-user-photos-album-block"
	   original-title="<?= $title; ?>"
	   data-id="<?= $id; ?>"
	   data-scope="page"
	   data-action="<?= admin_url('admin-ajax.php?action=get_um_user_photos_single_album_view'); ?>">

		<div class="album-overlay"></div>
		<img src="<?php echo esc_attr( $img ); ?>" alt="<?php echo esc_attr( $title ); ?>"/>
	</a>

	<div class="um-clear"></div>

	<p class="album-title">
		<strong><?php echo $title; ?></strong>
		<?php if( $count_msg ) { ?>
			<small> - <?php echo $count_msg; ?></small>
		<?php } ?>
	</p>
</div>