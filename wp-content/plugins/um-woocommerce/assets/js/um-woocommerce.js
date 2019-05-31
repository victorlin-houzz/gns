jQuery(document).ready(function() {

	if ( jQuery('.um-account-tab select.country_select,.um-account-tab select.state_select').length ) {
		jQuery('.um-account-tab select.country_select,.um-account-tab select.state_select').select2({
			width: '100%'
		});
	}

	if ( jQuery('.um-woo-review-avg').length ) {
		jQuery('.um-woo-review-avg').um_raty({
			half: 		true,
			starType: 	'i',
			number: 	function() {return jQuery(this).attr('data-number');},
			score: 		function() {return jQuery(this).attr('data-score');},
			hints: ['1 Star','2 Star','3 Star','4 Star','5 Star'],
			space: false,
			readOnly: true
		});
	}

	if ( window.location.href.indexOf("#!/") > -1 ) {
		var order_id = window.location.href.split(/[/ ]+/).pop();

		if ( order_id ) {

			prepare_Modal();

			wp.ajax.send( 'um_woocommerce_get_order', {
				data: {
					order_id: order_id,
					nonce: um_scripts.nonce
				},
				success: function( data ) {
					if ( data ) {
						show_Modal( data );
						responsive_Modal();
					} else {
						remove_Modal();
					}
				},
				error: function( e ) {
					remove_Modal();
					console.log( '===UM Woocommerce error===', e );
				}
			});
		}
	}

	jQuery( document.body ).on('click', '.um-woo-view-order', function(e){
		e.preventDefault();

		var order_id = jQuery(this).parents('tr').data('order_id');

		window.history.pushState("string", "Orders",  jQuery(this).attr('href') );

		prepare_Modal();

		wp.ajax.send( 'um_woocommerce_get_order', {
			data: {
				order_id: order_id,
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				if ( data ) {
					show_Modal( data );
					responsive_Modal();
				} else {
					remove_Modal();
				}
			},
			error: function( e ) {
				remove_Modal();
				console.log( '===UM Woocommerce error===', e );
			}
		});

		return false;
	});


	jQuery( document.body ).on('click', '.um-woo-order-hide',function(e){
		e.preventDefault();
		remove_Modal();
		return false;
	});
	
	
	jQuery( document.body ).on('click', '.my_account_subscriptions a.button.view, .my_account_subscriptions .subscription-id.order-number > a', function(e) {
		e.preventDefault();

		var subscription_id;
		if ( jQuery(this).parents('.order').find('.subscription-id a').length ) {
			subscription_id = jQuery(this).parents('.order').find('.subscription-id a').html().substr(1);
		}

		wp.ajax.send( 'um_woocommerce_get_subscription', {
			data: {
				subscription_id: subscription_id,
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				jQuery('.woocommerce_account_subscriptions').hide();
				jQuery('.um-account-tab-subscription .um-account-heading').after( data );
				jQuery('.um_account_subscription').fadeIn();
			},
			error: function( e ) {
				console.log( '===UM Woocommerce error===', e );
			}
		});
	});



	jQuery( document.body ).on('click', '.my_account_orders:not(.my_account_subscriptions) .order-number > a, .my_account_orders:not(.my_account_subscriptions) .order-actions > a.button.view', function(e){
		e.preventDefault();

		var order_id = jQuery(this).parents('tr').find('.order-number a').html();
		order_id = order_id.replace("#", "").trim();

		//window.history.pushState("string", "Orders",  jQuery(this).attr('href') );

		prepare_Modal();

		wp.ajax.send( 'um_woocommerce_get_order', {
			data: {
				order_id: order_id,
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				if ( data ) {
					show_Modal( data );
					responsive_Modal();
				} else {
					remove_Modal();
				}
			},
			error: function( e ) {
				remove_Modal();
				console.log( '===UM Woocommerce error===', e );
			}
		});

		return false;
	});


	jQuery( document.body ).on('click', '.back_to_subscriptions', function (e) {
		e.preventDefault();
		jQuery('.woocommerce_account_subscriptions').fadeIn();
		jQuery('.um_account_subscription').remove();
	});

});
