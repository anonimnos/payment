<?php

/*
 * 
 * WooCommerce setup function.
 * 
 */
function payment_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 150,
			'single_image_width'    => 300,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 1,
				'max_columns'     => 6,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'payment_woocommerce_setup' );

/**
 * 
 * Remove tabs from single product page
 * 
 */
add_filter( 'woocommerce_product_tabs', 'payment_remove_product_tabs', 98 );
function payment_remove_product_tabs( $tabs ) {

    unset( $tabs['description'] );
    unset( $tabs['reviews'] );
    unset( $tabs['additional_information'] ); 

    return $tabs;
}

/**
 * 
 * Change add to cart button text
 * 
 */
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_single_page_add_to_cart_callback' ); 
function woocommerce_single_page_add_to_cart_callback() {
    return __( 'Pirkti', 'payment' ); 
}

/**
 * 
 * Remove unnecessary functionality
 * 
 */
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');

/**
 * 
 * Clear cart in single page
 * 
 */
function payment_clear_cart_data() {
	global $woocommerce;
    $woocommerce->cart->empty_cart();
}
add_action( 'woocommerce_before_main_content', 'payment_clear_cart_data' );

/**
 * 
 * Add payment information to product page
 * 
 */
function payment_fill_payment_information() {
    ?>
	<div class="payments-order-wrapper">
		<div class="payments-information">
			<div class="payments-title"><?php _e( 'Fill payment information:', 'payment' ); ?></div>
			<form id="init-payment" action="">
				<div class="payments-row">
					<label for="payments-name"><?php _e( 'Name', 'payment' ); ?>*:</label>
					<input id="payments-name" type="text" name="name" required>
				</div>
				<div class="payments-row">
					<label for="payments-surname"><?php _e( 'Surname', 'payment' ); ?>*:</label>
					<input id="payments-surname" type="text" name="surname" required>
				</div>
				<div class="payments-row">
					<label for="payments-email"><?php _e( 'Email', 'payment' ); ?>*:</label>
					<input id="payments-email" type="email" name="email" required>
				</div>
				<div class="payments-row">
					<input type="submit" value="<?php _e( 'Pay', 'payment' ); ?>">
				</div>
			</form>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_after_add_to_cart_form', 'payment_fill_payment_information' );

/**
 * 
 * Payment callback url
 * 
 */
function wc_paysera_payment_handler() {
	require_once( PAYMENT_REAL_PATH . '/libwebtopay/includes.php' );
	
	try {
		$response = WebToPay::validateAndParseData(
			$_REQUEST,
			PAYMENT_PROJECT_ID,
			PAYMENT_SIGN_PASSWORD
		);

		if ( $response['status'] === '1' ) {
			$order = wc_get_order( $response['orderid'] );

			if ( isPaymentValid( $order, $response ) === true ) {
				$order->add_order_note( __('Paysera callback order payment completed') );
				$order->update_status( 'completed' );

				echo 'OK';
			}
		} else {
			throw new Exception( 'Payment was not successful' );
		}
	} catch ( Exception $exception ) {
        echo get_class( $exception ) . ':' . $exception->getMessage();
    }

	die();
}
add_action( 'woocommerce_api_wc_paysera_payment', 'wc_paysera_payment_handler' );

/**
 * 
 * Check if payment request is valid
 * 
 */
function isPaymentValid( $order, $response ) {
	if( (string) ( $order->get_total() * 100 ) !== $response['amount'] ) {
		throw new Exception( 'Wrong payment amount' );
	}

	if( $order->get_currency() !== $response['currency'] ) {
		throw new Exception( 'Wrong currency' );
	}

	return true;
}