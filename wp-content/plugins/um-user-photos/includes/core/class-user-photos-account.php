<?php
namespace um_ext\um_user_photos\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Photos_Account
 * @package um_ext\um_user_photos\core
 */
class User_Photos_Account {


	/**
	 * User_Photos_Account constructor.
	 */
	function __construct() {
		add_filter( 'um_account_page_default_tabs_hook', array( $this, 'add_user_photos_tab' ), 100 );
		add_action( 'um_account_tab__um_user_photos', array( $this, 'um_account_tab__um_user_photos' ) );
		add_filter( 'um_account_content_hook_um_user_photos', array( $this, 'um_account_content_hook_um_user_photos' ) );
	}


	/**
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function add_user_photos_tab( $tabs ) {
		$tabs[800]['um_user_photos']['icon'] = 'um-faicon-image';
		$tabs[800]['um_user_photos']['title'] = __( 'My Photos', 'um-user-photos' );
		$tabs[800]['um_user_photos']['custom'] = true;
		$tabs[800]['um_user_photos']['show_button']  = false;

		return $tabs;
	}


	/**
	 * @param $info
	 */
	function um_account_tab__um_user_photos( $info ) {
		if ( is_array( $info ) ) {
			extract( $info );
		}

		$output = UM()->account->get_tab_output( 'um_user_photos' );
		if ( $output ) {
			echo $output;
		}
	}


	/**
	 * @param $output
	 *
	 * @return string
	 */
	function um_account_content_hook_um_user_photos( $output ) {
		ob_start(); ?>

		<div class="um_user_photos_account">
			<p>
				<?php _e( 'Once photos and albums are deleted, they are deleted permanantly and cannot be recovered.', 'um-user-photos' ); ?>
			</p>
			<p>
				<a id="um_user_photos_download_all"
				   class="um-button"
				   data-profile="<?php echo esc_attr( um_user('ID') ); ?>"
				   data-wpnonce="<?php echo esc_attr( wp_create_nonce('um_user_photos_download_all') ); ?>"
				   href="<?= admin_url('admin-ajax.php?action=download_my_photos&profile_id='.um_user('ID')); ?>">
					<?php _e('Download my photos','um-user-photos' ); ?>
				</a>

				<a id="um_user_photos_delete_all"
				   class="um-button danger"
				   data-profile="<?php echo esc_attr( um_user('ID') ); ?>"
				   data-wpnonce="<?php echo esc_attr( wp_create_nonce('um_user_photos_delete_all') ); ?>"
				   data-alert_message="<?php esc_attr_e( 'Are you sure to delete all your albums & photos?','um-user-photos' ); ?>"
				   href="<?= admin_url('admin-ajax.php?action=delete_my_albums_photos'); ?>">
					<?php _e('Delete my all albums & photos','um-user-photos'); ?>
				</a>
			</p>
		</div>
		<div class="um-clear"></div>

		<?php $output .= ob_get_clean();
		return $output;
	}
}