<?php
namespace um_ext\um_woocommerce\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class WooCommerce_Main_API
 * @package um_ext\um_woocommerce\core
 */
class WooCommerce_Main_API {


	/**
	 * WooCommerce_Main_API constructor.
	 */
	function __construct() {

	}


	/**
	 * Check if Woo Subscriptions plugin is active
	 *
	 * @return bool
	 */
	function is_wc_subscription_plugin_active() {
		return function_exists( 'wcs_get_subscription' );
	}


	/**
	 * Check single product order need or not need to change user role
	 *
	 * @param int $order_id
	 *
	 * @return array|bool
	 */
	function change_role_data_single( $order_id ) {
		$order = new \WC_Order( $order_id );
		$user_id = $order->get_user_id();
		um_fetch_user( $user_id );

		// fetch role and excluded roles
		$user_role = UM()->user()->get_role();
		$excludes = UM()->options()->get( 'woo_oncomplete_except_roles' );
		$excludes = empty( $excludes ) ? array() : $excludes;

		$data = array();

		//items have more priority
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$id = $item['product_id'];
			if ( get_post_meta( $id, '_um_woo_product_role', true ) != '' && ( empty( $excludes ) || ! in_array( $user_role, $excludes ) ) ) {
				$role = esc_attr( get_post_meta( $id, '_um_woo_product_role', true ) );
				$data = array( 'user_id' => $user_id, 'role' => $role );
			}
		}

		if ( empty( $data ) ) {
			$role = UM()->options()->get( 'woo_oncomplete_role' );
			if ( $role && ! user_can( $user_id, $role ) && ( empty( $excludes ) || ! in_array( $user_role, $excludes ) ) ) {
				return array( 'user_id' => $user_id, 'role' => $role );
			}
		} else {
			return $data;
		}

		return false;
	}


	/**
	 * Check single product order need or not need to change user role
	 *
	 * @param int $order_id
	 *
	 * @return array|bool
	 */
	function change_role_data_single_refund( $order_id ) {
		$order = new \WC_Order( $order_id );
		$user_id = $order->get_user_id();

		$role = UM()->options()->get( 'woo_onrefund_role' );
		if ( $role && ! user_can( $user_id, $role ) ) {
			return array( 'user_id' => $user_id, 'role' => $role );
		}

		return false;
	}


	/**
	 * Get Order Data via AJAX
	 */
	function ajax_get_order() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['order_id'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$is_customer = get_post_meta( $_POST['order_id'], '_customer_user', true );

		if ( $is_customer != get_current_user_id() ) {
			wp_send_json_error();
		}

		ob_start();

		$order_id = $_POST['order_id'];
		$order    = wc_get_order( $order_id );

		um_fetch_user( get_current_user_id() ); ?>

		<div class="um-woo-order-head um-popup-header">

			<div class="um-woo-customer">
				<?php echo get_avatar( get_current_user_id(), 34 ); ?>
				<span><?php echo um_user('display_name'); ?></span>
			</div>

			<div class="um-woo-orderid">
				<?php printf(__('Order# %s','um-woocommerce'), $order_id ); ?>
				<a href="#" class="um-woo-order-hide"><i class="um-icon-close"></i></a>
			</div>

			<div class="um-clear"></div>

		</div>

		<div class="um-woo-order-body um-popup-autogrow2">

			<?php wc_print_notices(); ?>

			<p class="order-info"><?php printf( __( 'Order #<mark class="order-number">%s</mark> was placed on <mark class="order-date">%s</mark> and is currently <mark class="order-status">%s</mark>.', 'um-woocommerce' ), $order->get_order_number(), date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ), wc_get_order_status_name( $order->get_status() ) ); ?></p>

			<?php if ( $notes = $order->get_customer_order_notes() ) : ?>

				<h2><?php _e( 'Order Updates', 'woocommerce' ); ?></h2>
				<ol class="commentlist notes">
					<?php foreach ( $notes as $note ) : ?>
						<li class="comment note">
							<div class="comment_container">
								<div class="comment-text">
									<p class="meta"><?php echo date_i18n( __( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); ?></p>
									<div class="description">
										<?php echo wpautop( wptexturize( $note->comment_content ) ); ?>
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>

				<?php
			endif;

			do_action( 'woocommerce_view_order', $order_id ); ?>

		</div>

		<div class="um-popup-footer" style="height:30px"></div>

		<?php $output = ob_get_clean();
		$output = do_shortcode( $output );

		wp_send_json_success( $output );
	}


	/**
	 * Get Subscription Data via AJAX
	 */
	function ajax_get_subscription() {
		UM()->check_ajax_nonce();

		$subscription = wcs_get_subscription( $_POST['subscription_id'] );
		$actions = wcs_get_all_user_actions_for_subscription( $subscription, get_current_user_id() );
		$notes = $subscription->get_customer_order_notes();

		ob_start();

		$columns = array(
			'last_order_date_created' => _x( 'Last Order Date', 'admin subscription table header', 'ultimate-member' ),
			'next_payment'            => _x( 'Next Payment Date', 'admin subscription table header', 'ultimate-member' ),
			'end'                     => _x( 'End Date', 'table heading', 'ultimate-member' ),
			'trial_end'               => _x( 'Trial End Date', 'admin subscription table header', 'ultimate-member' ),
		); ?>


		<div class="um_account_subscription" style="">
			<a href="#" class="button back_to_subscriptions"><?php _e( 'All subscriptions', 'ultimate-member' ); ?></a>

			<table class="shop_table subscription_details shop_table_responsive my_account_subscriptions my_account_orders">
				<tr>
					<td><?php esc_html_e( 'Subscription', 'ultimate-member' ); ?></td>
					<td>#<?php echo $_POST['subscription_id']; ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Status', 'ultimate-member' ); ?></td>
					<td><?php echo esc_html( wcs_get_subscription_status_name( $subscription->get_status() ) ); ?></td>
				</tr>
				<tr>
					<td><?php echo esc_html_x( 'Start Date', 'table heading',  'ultimate-member' ); ?></td>
					<td><?php echo esc_html( $subscription->get_date_to_display( 'date_created' ) ); ?></td>
				</tr>

				<?php foreach ( $columns as $date_type => $date_title ) {
					$date = $subscription->get_date( $date_type );

					if ( ! empty( $date ) ) { ?>
						<tr>
							<td><?php echo esc_html( $date_title ); ?></td>
							<td><?php echo esc_html( $subscription->get_date_to_display( $date_type ) ); ?></td>
						</tr>
					<?php }
				}

				do_action( 'woocommerce_subscription_before_actions', $subscription );

				if ( ! empty( $actions ) ) { ?>
					<tr>
						<td><?php esc_html_e( 'Actions', 'ultimate-member' ); ?></td>
						<td>
							<?php foreach ( $actions as $key => $action ) { ?>
								<a href="<?php echo esc_url( $action['url'] ); ?>" class="button <?php echo sanitize_html_class( $key ) ?>">
									<?php echo esc_html( $action['name'] ); ?>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php }

				do_action( 'woocommerce_subscription_after_actions', $subscription ); ?>
			</table>

			<?php if ( $notes ) { ?>
				<h2><?php esc_html_e( 'Subscription Updates', 'ultimate-member' ); ?></h2>
				<ol class="commentlist notes">
					<?php foreach ( $notes as $note ) { ?>
						<li class="comment note">
							<div class="comment_container">
								<div class="comment-text">
									<p class="meta">
										<?php echo esc_html( date_i18n( _x( 'l jS \o\f F Y, h:ia', 'date on subscription updates list. Will be localized', 'ultimate-member' ), wcs_date_to_time( $note->comment_date ) ) ); ?>
									</p>
									<div class="description">
										<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>
						</li>
					<?php } ?>
				</ol>
			<?php }

			/** Gets subscription totals table template */
			do_action( 'woocommerce_subscription_totals_table', $subscription );

			/** Related Orders */
			do_action( 'woocommerce_subscription_details_after_subscription_table', $subscription ); ?>

		</div>

		<?php $output = ob_get_clean();
		wp_send_json_success( $output );
	}


	/**
	 * Check if current user has subscriptions and return subscription IDs
	 * @param  integer			$user_id
	 * @param  string				$product_id
	 * @param  string				$status
	 * @param  array|int		$except_subscriptions
	 * @return array|bool		subscription products ids
	 */
	function user_has_subscription( $user_id = 0, $product_id = '', $status = 'any', $except_subscriptions = array() ) {

		if ( ! function_exists('wcs_get_users_subscriptions') ) {
			return '';
		}

		$subscriptions = wcs_get_users_subscriptions( $user_id );
		$has_subscription = false;
		$arr_product_ids = array();
		if ( empty( $product_id ) ) { // Any subscription
			if ( ! empty( $status ) && 'any' != $status ) { // We need to check for a specific status
				foreach ( $subscriptions as $subscription ) {
					if( in_array( $subscription->get_id(), (array) $except_subscriptions ) ){
						continue;
					}
					if ( $subscription->has_status( $status ) ) {
						$order_items  = $subscription->get_items();
						foreach ( $order_items as $order ) {
							$arr_product_ids[ ] = wcs_get_canonical_product_id( $order );
						}
					}
				}

				return $arr_product_ids;

			} elseif ( ! empty( $subscriptions ) ) {
				$has_subscription = true;
			}
		} else {
			foreach ( $subscriptions as $subscription ) {
				if( in_array( $subscription->get_id(), (array) $except_subscriptions ) ){
					continue;
				}
				if ( $subscription->has_product( $product_id ) && ( empty( $status ) || 'any' == $status || $subscription->has_status( $status ) ) ) {
					$has_subscription = true;
					break;
				}
			}
		}
		return $has_subscription;
	}

}
