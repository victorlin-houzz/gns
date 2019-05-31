<?php
namespace um_ext\um_woocommerce\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class WooCommerce_Account
 * @package um_ext\um_woocommerce\core
 */
class WooCommerce_Account {


	/**
	 * WooCommerce_Account constructor.
	 */
	function __construct() {

		add_filter( 'um_account_page_default_tabs_hook', array( &$this, 'account_tabs' ), 100 );

		add_filter( 'um_account_content_hook_billing', array( &$this, 'account_billing_tab_content' ), 10, 2 );
		add_filter( 'um_account_content_hook_shipping', array( &$this, 'account_shipping_tab_content' ), 10, 2 );
		add_filter( 'um_account_content_hook_orders', array( &$this, 'account_orders_tab_content' ), 10, 2 );
		add_filter( 'um_account_content_hook_downloads', array( &$this, 'account_downloads_tab_content' ), 10, 2 );
		add_filter( 'um_account_content_hook_payment-methods', array( &$this, 'account_payment_methods_tab_content' ), 10, 2 );

		if ( class_exists( 'WC_Subscriptions' ) ) {
			add_filter( 'um_account_content_hook_subscription', array( &$this, 'account_subscription_tab_content' ), 10, 1 );
		}

		add_action( 'um_submit_account_billing_tab_errors_hook', array( &$this, 'account_errors_hook' ), 10 );
		add_action( 'um_submit_account_shipping_tab_errors_hook', array( &$this, 'account_errors_hook' ), 10 );

		add_action( 'template_redirect', array( &$this, 'um_woocommerce_pre_update' ), 1 );
		add_action( 'um_update_profile_full_name', array( &$this, 'um_sync_update_user_wc_email' ), 10, 2 );
		add_action( 'woocommerce_checkout_update_user_meta', array( &$this, 'um_update_um_profile_from_wc_billing' ), 10, 2 );
		add_action( 'woocommerce_customer_save_address', array( &$this, 'um_update_um_profile_from_wc_billing' ), 10, 2 );
		add_action( 'um_after_user_account_updated', array( &$this, 'um_call_wc_user_account_update' ), 99, 2 );

		add_filter( 'um_custom_success_message_handler', array( &$this, 'um_woocommerce_custom_notice' ), 10, 2 );
	}


	/**
	 * Add tab to account page
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function account_tabs( $tabs ) {

		if ( um_user( 'woo_account_billing' ) && ! UM()->options()->get( 'woo_hide_billing_tab_from_account' ) ) {
			$tabs[210]['billing'] = array(
				'icon'          => 'um-faicon-credit-card',
				'title'         => __( 'Billing Address', 'um-woocommerce' ),
				'submit_title'  => __( 'Save Address', 'um-woocommerce' ),
				'custom'        => true,
			);
		}

		if ( um_user( 'woo_account_shipping' ) && ! UM()->options()->get('woo_hide_shipping_tab_from_account') ) {
			$tabs[220]['shipping'] = array(
				'icon'          => 'um-faicon-truck',
				'title'         => __( 'Shipping Address', 'um-woocommerce' ),
				'submit_title'  => __( 'Save Address', 'um-woocommerce' ),
				'custom'        => true,
			);
		}

		if ( um_user( 'woo_account_orders' ) ) {
			$tabs[230]['orders'] = array(
				'icon'          => 'um-faicon-shopping-cart',
				'title'         => __( 'My Orders', 'um-woocommerce' ),
				'custom'        => true,
				'show_button'   => false,
			);
		}

		if ( class_exists( 'WC_Subscriptions' ) ) {
			$tabs[240]['subscription'] = array(
				'icon'          => 'um-faicon-book',
				'title'         => __( 'Subscriptions', 'um-woocommerce' ),
				'custom'        => true,
				'show_button'   => false,
			);
		}

		if ( um_user( 'woo_account_downloads' ) ) {
			$tabs[250]['downloads'] = array(
				'icon'          => 'um-faicon-download',
				'title'         => __( 'Downloads', 'um-woocommerce' ),
				'custom'        => true,
				'show_button'   => false,
			);
		}

		if ( um_user( 'woo_account_payment_methods' ) ) {
			$tabs[260]['payment-methods'] = array(
				'icon'          => 'um-faicon-credit-card',
				'title'         => __( 'Payment methods', 'um-woocommerce' ),
				'custom'        => true,
				'show_button'   => false,
			);
		}

		return $tabs;
	}


	/**
	 * Edit Address
	 *
	 * @param $address
	 */
	function edit_address( $address ) {
		// Current user
		global $current_user;
		wp_get_current_user();

		$load_address = $address;
		$load_address = sanitize_key( $load_address );

		$address = WC()->countries->get_address_fields( get_user_meta( get_current_user_id(), $load_address . '_country', true ), $load_address . '_' );

		// Enqueue scripts
		wp_enqueue_script( 'wc-country-select' );
		wp_enqueue_script( 'wc-address-i18n' );

		$arr_fields = array();

		// Prepare values
		foreach ( $address as $key => $field ) {

			$value = get_user_meta( get_current_user_id(), $key, true );

			if ( ! $value ) {
				switch( $key ) {
					case 'billing_email' :
					case 'shipping_email' :
						$value = $current_user->user_email;
						break;
					case 'billing_country' :
					case 'shipping_country' :
						$value = WC()->countries->get_base_country();
						break;
					case 'billing_state' :
					case 'shipping_state' :
						$value = WC()->countries->get_base_state();
						break;
				}
			}

			$address[ $key ]['value'] = apply_filters( 'woocommerce_my_account_edit_address_field_value', $value, $key, $load_address );

			$arr_fields[ $key ] = array( 'metakey' => $key );

		}

		do_action( "woocommerce_before_edit_address_form_{$load_address}" );

		/**
		 * @deprecated since version 2.1.6
		 */
//		$output = '';
//		foreach ( $address as $key => $data ) {
//			$output .= UM()->fields()->edit_field( $key, $data );
//		}
//		echo $output;

		foreach ( $address as $key => $field ) {
			$field['input_class'][] = 'um-form-field';
			$field['custom_attributes']['data-key'] = $key;
			$field['type'] = ! empty( $field['type'] ) ? $field['type'] : 'text'; ?>

			<div class="um-field um-field-<?php echo $key ?> um-field-<?php echo $field['type'] ?> um-field-type_<?php echo $field['type'] ?>" data-key="<?php echo $key ?>">
				<?php $arr_fields[ $key ] = array( 'metakey' => $key );

				$field['return'] = true;
				$field = woocommerce_form_field( $key, $field, ! empty( $_POST[ $key ] ) ? wc_clean( $_POST[ $key ] ) : $field['value'] );

				if(in_array($key, array('billing_email'))) {
					$field = str_replace('<input', '<input disabled', $field);
				}

				$field = str_replace('<label', '<div class="um-field-label"><label', $field );
				$field = str_replace('</label>', '</label></div><div class="um-clear"></div>', $field );

				$field = preg_replace('/\<span class\=\"woocommerce-input-wrapper\"\>(.*?)\<\/span\>/im', "<div class=\"um-field-area\">$1</div>", $field );

				$field = preg_replace('/\<p([^\>]*?)\>(.*?)\<\/p\>/im', "$2", $field );

				echo $field;

				if ( UM()->fields()->is_error( $key ) ) {
					echo UM()->fields()->field_error( UM()->fields()->show_error( $key ) );
				} ?>
			</div>

		<?php }

		$arr_fields = apply_filters('um_account_secure_fields', $arr_fields, $load_address );

		do_action( "woocommerce_after_edit_address_form_{$load_address}" );

	}


	/**
	 * Trigger Shipping/Billing fields validation
	 */
	function account_errors_hook() {

		$load_address = $_POST['_um_account_tab'];
		$load_address = sanitize_key( $load_address );

		$address = WC()->countries->get_address_fields( get_user_meta( get_current_user_id(), $load_address . '_country', true ), $load_address . '_' );

		$error_trigger = false;
		foreach ( $address as $key => $field_data ) {
			if ( $key == 'billing_email' ) {
				continue;
			}
			if ( ! empty( $field_data['required'] ) && empty( $_POST[ $key ] ) ) {
				UM()->form()->add_error( $key, sprintf( __( '"%s" field is required', 'ultimate-member' ), $field_data['label'] ) );
				$error_trigger = true;
			}
		}

		if ( $error_trigger ) {
			return;
		}
	}


	/**
	 * Add content to account tab
	 *
	 * @param $output
	 * @param $shortcode_args
	 *
	 * @return string
	 */
	function account_billing_tab_content( $output, $shortcode_args ) {
		global $wp;

		wp_enqueue_script( 'um-woocommerce' );
		wp_enqueue_style( 'um-woocommerce' );

		$wp->query_vars['edit-address'] = 'billing';
		ob_start(); ?>

		<div class="um-woo-form um-woo-billing">
			<?php $this->edit_address( 'billing' ); ?>
		</div>

		<?php $output .= ob_get_clean();

		return do_shortcode( $output );
	}


	/**
	 * Add content to account tab
	 *
	 * @param $output
	 * @param $shortcode_args
	 *
	 * @return string
	 */
	function account_shipping_tab_content( $output, $shortcode_args ) {
		global $wp;

		wp_enqueue_script( 'um-woocommerce' );
		wp_enqueue_style( 'um-woocommerce' );

		$wp->query_vars['edit-address'] = 'shipping';
		ob_start(); ?>

		<div class="um-woo-form um-woo-shipping">
			<?php $this->edit_address( 'shipping' ); ?>
		</div>

		<?php $output .= ob_get_clean();

		return do_shortcode( $output );
	}


	/**
	 * Add content to account tab
	 *
	 * @param $output
	 * @param $shortcode_args
	 *
	 * @return string
	 */
	function account_orders_tab_content( $output, $shortcode_args ) {
		wp_enqueue_script( 'um-woocommerce' );
		wp_enqueue_style( 'um-woocommerce' );

		ob_start(); ?>

		<div class="um-woo-form um-woo-orders">

			<?php $orders_page = isset( $_REQUEST['orders_page'] ) ? $_REQUEST['orders_page'] : 1;
			$page = $orders_page;
			$orders_per_page = 10;
			$args = apply_filters( "um_woocommerce_account_orders_args", array(
				'posts_per_page'	=> $orders_per_page,
				'paged'				=> $orders_page,
				'meta_key'    		=> '_customer_user',
				'meta_value'  		=> get_current_user_id(),
				'post_type' 		=> wc_get_order_types( 'view-orders' ),
				'post_status' 		=> array_keys( wc_get_order_statuses() ),
				'order'				=> 'ASC'
			) );

			$loop = new \WP_Query( $args );

			$total_pages =  ceil( $loop->found_posts / $orders_per_page );
			$pages_to_show = $total_pages ;

			$order = '';
			$url = UM()->account()->tab_link( 'orders' );

			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );
			$date_time_format = $date_format . ' ' . $time_format;

			$customer_orders = $loop->posts;

			if ( $total_pages ) { ?>

				<table class="shop_table shop_table_responsive my_account_orders">

					<thead>
					<tr>
						<?php do_action('um_woocommerce_orders_tab_before_table_header_row', $order, $customer_orders ); ?>
						<th class="order-date"><span class="nobr"><?php _e( 'Date', 'woocommerce' ); ?></span></th>
						<th class="order-status"><span class="nobr"><?php _e( 'Status', 'woocommerce' ); ?></span></th>
						<th class="order-total"><span class="nobr"><?php _e( 'Total', 'woocommerce' ); ?></span></th>
						<th class="order-actions">&nbsp;</th>
						<?php do_action('um_woocommerce_orders_tab_after_table_header_row', $order, $customer_orders ); ?>
					</tr>
					</thead>

					<tbody>
					<?php foreach ( $customer_orders as $customer_order ) {
						$order = wc_get_order( $customer_order->ID );
						$order_id = $customer_order->ID;
						$order_data = $order->get_data();
						$order_date = strtotime( $order->get_date_created() );
						?>

					<tr class="order" data-order_id="<?php echo $order_id; ?>">
						<?php do_action('um_woocommerce_orders_tab_before_table_row', $order, $customer_orders ); ?>
						<td class="order-date" data-title="<?php _e( 'Date', 'woocommerce' ); ?>">
							<time datetime="<?php echo date( 'Y-m-d', strtotime( $order->get_date_created() ) ); ?>" title=""><?php echo date_i18n( $date_time_format, $order_date, true ); ?></time>
						</td>
						<td class="order-status" data-title="<?php _e( 'Status', 'woocommerce' ); ?>" style="text-align:left; white-space:nowrap;">
							<span class="um-woo-status <?php echo $order->get_status(); ?>"><?php echo wc_get_order_status_name( $order->get_status() ); ?></span>
						</td>
						<td class="order-total" data-title="<?php _e( 'Total', 'woocommerce' ); ?>"><?php echo $order->get_formatted_order_total() ?></td>
						<td class="order-detail">
							<?php echo '<a href="' . $url . '#!/' . $order_id . '" class="um-woo-view-order um-tip-n" title="'.__('View order','um-woocommerce').'"><i class="um-icon-eye"></i></a>'; ?>
						</td>
						<?php do_action('um_woocommerce_orders_tab_after_table_row', $order, $customer_orders ); ?>
						</tr><?php
					} ?>
					</tbody>

				</table>

				<div class="um-members-pagidrop uimob340-show uimob500-show">

					<?php _e('Jump to page:','um-woocommerce');

					if ( $pages_to_show ) { ?>
						<select onChange="window.location.href=this.value" class="um-s2" style="width: 100px">
							<?php for( $i = 1; $i<=$pages_to_show; $i++ ) { ?>
								<option value="<?php echo '?orders_page='.$i; ?>" <?php selected($i, $page ); ?>><?php printf(__('%s of %d','um-woocommerce'), $i, $total_pages ); ?></option>
							<?php } ?>
						</select>
					<?php } ?>

				</div>

				<div class="um-members-pagi uimob340-hide uimob500-hide">

					<?php if ( $page != 1 ) { ?>
						<a href="<?php echo '?orders_page=1'; ?>" class="pagi pagi-arrow um-tip-n" title="<?php _e('First Page','um-woocommerce'); ?>"><i class="um-faicon-angle-double-left"></i></a>
					<?php } else { ?>
						<span class="pagi pagi-arrow disabled"><i class="um-faicon-angle-double-left"></i></span>
					<?php }

					if ( $page > 1 ) { ?>
						<a href="<?php echo '?orders_page='.( $page - 1 ); ?>" class="pagi pagi-arrow um-tip-n" title="<?php _e('Previous','um-woocommerce'); ?>"><i class="um-faicon-angle-left"></i></a>
					<?php } else { ?>
						<span class="pagi pagi-arrow disabled"><i class="um-faicon-angle-left"></i></span>
					<?php }

					if ( $pages_to_show ) {
						for( $i = 1; $i<=$pages_to_show; $i++ ) {
							if ( $page == $i ) { ?>
								<span class="pagi current"><?php echo $i; ?></span>
							<?php } else { ?>
								<a href="<?php echo '?orders_page='.$i; ?>" class="pagi"><?php echo $i; ?></a>
							<?php }
						}
					}

					if ( $page != $total_pages ) { ?>
						<a href="<?php echo '?orders_page='.( $page + 1 ); ?>" class="pagi pagi-arrow um-tip-n" title="<?php _e('Next','um-woocommerce'); ?>"><i class="um-faicon-angle-right"></i></a>
					<?php } else { ?>
						<span class="pagi pagi-arrow disabled"><i class="um-faicon-angle-right"></i></span>
					<?php }

					if ( $page != $total_pages ) { ?>
						<a href="<?php echo '?orders_page='.( $total_pages ); ?>" class="pagi pagi-arrow um-tip-n" title="<?php _e('Last Page','um-woocommerce'); ?>"><i class="um-faicon-angle-double-right"></i></a>
					<?php } else { ?>
						<span class="pagi pagi-arrow disabled"><i class="um-faicon-angle-double-right"></i></span>
					<?php } ?>

				</div>

			<?php } else { ?>
				<div class="um-field"><?php _e( 'You don\'t have orders yet', 'um-woocommerce' ); ?></div>
			<?php } ?>

		</div>

		<?php $output .= ob_get_clean();

		return do_shortcode( $output );
	}
	
	
	/**
	 * Add content to account tab 'Downloads'
	 * @param string $output
	 * @return string
	 */
	function account_downloads_tab_content( $output = '' ) {
		wp_enqueue_script( 'um-woocommerce' );
		wp_enqueue_style( 'um-woocommerce' );

		ob_start();
		echo '<div class="um-woo-form um-woo-downloads">';
		do_action( 'woocommerce_account_downloads_endpoint' );
		echo '</div>';
		$output .= ob_get_clean();
		
		return do_shortcode( $output );
	}
	
	
	/**
	 * Add content to account tab 'Payment methods'
	 * @param string $output
	 * @return string
	 */
	function account_payment_methods_tab_content( $output = '' ) {
		wp_enqueue_script( 'um-woocommerce' );
		wp_enqueue_style( 'um-woocommerce' );
		
		// fake data for function is_add_payment_method_page()
		add_filter( 'woocommerce_get_myaccount_page_id', function( $page_id ){
			global $post, $wp;
			$wp->query_vars['payment-methods'] = 1;
			return $post->ID;
		}, 20 );

		ob_start();
		echo '<div class="um-woo-form um-woo-payment-methods">';
		do_action( 'woocommerce_account_payment-methods_endpoint' );
		echo '</div>';
		$output .= ob_get_clean();
		
		return do_shortcode( $output );
	}


	/**
	 * @param $output
	 *
	 * @return string
	 */
	function account_subscription_tab_content( $output ) {
		wp_enqueue_script( 'um-woocommerce' );
		wp_enqueue_style( 'um-woocommerce' );

		ob_start();

		do_action( 'woocommerce_add_subscriptions_to_my_account' );

		$output .= ob_get_clean();
		return do_shortcode( $output );
	}


	/**
	 * Before woocommerce update address
	 */
	function um_woocommerce_pre_update() {
		global $wp;

		if ( isset( $_POST['um_account_submit'] ) && get_query_var('um_tab') == 'shipping' ) {
			$wp->query_vars['edit-address'] = 'shipping';
		}

		if ( isset( $_POST['um_account_submit'] ) && get_query_var('um_tab') == 'billing' ) {
			$wp->query_vars['edit-address'] = 'billing';
		}

		if ( wc_has_notice( __( 'Address changed successfully.', 'woocommerce' ) ) ) {
			wc_clear_notices();
			$url = UM()->account()->tab_link( 'billing' );
			exit( wp_redirect( add_query_arg('updated','edit-billing', $url ) ) );
		}

	}


	/**
	 * Update billing email when the user's email address is changed
	 *
	 * @param $user_id
	 * @param $changes
	 */
	function um_sync_update_user_wc_email( $user_id, $changes ) {
		if(isset($changes['user_email'])) {
			update_user_meta( UM()->user()->id, 'billing_email', $changes['user_email']);
		}

		if(isset($changes['first_name'])) {
			update_user_meta( UM()->user()->id, 'billing_first_name', $changes['first_name']);
		}

		if(isset($changes['last_name'])) {
			update_user_meta( UM()->user()->id, 'billing_last_name', $changes['last_name']);
		}
	}


	/**
	 * Update um profile when wc billing is updated
	 *
	 * @param $user_id
	 * @param null $data
	 */
	function um_update_um_profile_from_wc_billing($user_id, $data = null) {

		if ( isset( $_POST['um_account_submit'] ) && isset( $_POST[ 'billing_first_name'] ) && isset( $_POST['billing_last_name'] ) && isset( $_POST[ 'billing_email' ] ) ) {
			$changes = array();
			foreach($_POST as $key => $value) {
				if(preg_match('/^billing_/', $key)) {
					$key           = str_replace('billing_', '', $key);

					if (in_array($key, array('first_name', 'last_name', 'user_email'))) {
						$changes[$key] = $value;

						update_user_meta( $user_id, $key, $value );
					}
				}
			}

			wp_update_user( array(
				'ID'            => $user_id,
				'user_email'    => $_POST['billing_email']
			) );

			// hook for name changes
			do_action( 'um_update_profile_full_name', $user_id, $changes );

			UM()->user()->remove_cache( $user_id );
		}
	}


	/**
	 * @param $user_id
	 * @param $changes
	 */
	function um_call_wc_user_account_update( $user_id, $changes ) {
		global $wp;

		if( $wp->query_vars['edit-address'] == 'billing' || $wp->query_vars['edit-address'] == 'shipping' ) {
			do_action( 'woocommerce_customer_save_address', $user_id, $wp->query_vars['edit-address'] );
		}

		if ( isset( $_POST['um_account_submit'] ) && get_query_var('um_tab') == 'shipping' ) {
			exit( wp_redirect( add_query_arg('updated','edit-shipping') ) );
		}

		if ( isset( $_POST['um_account_submit'] ) && get_query_var('um_tab') == 'billing' ) {
			exit( wp_redirect( add_query_arg('updated','edit-billing') ) );
		}
	}






	/**
	 * Custom notice
	 *
	 * @param $msg
	 * @param $err_t
	 *
	 * @return string
	 */
	function um_woocommerce_custom_notice( $msg, $err_t ) {

		if ( $err_t == 'edit-billing' ) {
			$msg = __( 'Your billing address is updated.', 'um-woocommerce' );
		}

		if ( $err_t == 'edit-shipping' ) {
			$msg = __( 'Your shipping address is updated.', 'um-woocommerce' );
		}

		return $msg;
	}




}