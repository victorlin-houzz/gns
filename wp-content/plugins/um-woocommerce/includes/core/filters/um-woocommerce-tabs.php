<?php
if ( ! defined( 'ABSPATH' ) ) exit;


	/***
	***	@add tab for product reviews
	***/
	add_filter( 'um_profile_tabs', 'um_woocommerce_add_tab', 800 );
	function um_woocommerce_add_tab( $tabs ) {
		$tabs['purchases'] = array(
			'name' => __( 'Purchases', 'um-woocommerce' ),
			'icon' => 'um-faicon-shopping-cart'
		);

		$tabs['product-reviews'] = array(
			'name' => __( 'Product Reviews', 'um-woocommerce' ),
			'icon' => 'um-faicon-star'
		);

		return $tabs;
	}

	/***
	***	@add tabs based on user
	***/
	add_filter( 'um_user_profile_tabs', 'um_woocommerce_user_add_tab', 1000, 1 );
	function um_woocommerce_user_add_tab( $tabs ) {

		if ( ! um_user( 'woo_purchases_tab' ) )
			unset( $tabs['purchases'] );

		if ( ! um_user( 'woo_reviews_tab' ) )
			unset( $tabs['product-reviews'] );

		return $tabs;

	}
