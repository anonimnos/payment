<?php
require_once( '../libwebtopay/includes.php' );

if( !isset( $_POST['name'] ) ) {
    return;
}

$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

global $woocommerce;

/**
 * Get client info
 */
$clientInfo = array(
    'name'    => htmlspecialchars( $_POST['name'] ),
    'surname' => htmlspecialchars( $_POST['surname'] ),
    'email'   => htmlspecialchars( $_POST['email'] )
);

/**
 * Initiate order
 */
$cart     = $woocommerce->cart;
$checkout = $woocommerce->checkout();
$orderId  = $checkout->create_order( array() );
$order    = wc_get_order( $orderId );

/**
 * Get order received payment url
 */
$orderReceivedUrl = wc_get_endpoint_url( 'order-received', $orderId, wc_get_checkout_url() );
$orderReceivedUrl = add_query_arg( 'key', $order->get_order_key(), $orderReceivedUrl );

/**
 * Order price, url, currency for payment
 */
$cartTotal  = $cart->total * 100;
$websiteUrl = get_site_url();
$currency   = get_woocommerce_currency();

/**
 * Add billing and shipping information
 */
$address = array(
	'first_name' => $clientInfo['name'],
	'last_name'  => $clientInfo['surname'],
	'email'      => $clientInfo['email'],
);

$order->set_address( $address, 'billing' );
$order->set_address( $address, 'shipping' );

/**
 * Set order payment methods
 */
$order->set_payment_method( 'bacs' );
$order->set_payment_method_title( 'Paysera' );

/**
 * Calculate order total price
 */
$order->calculate_totals();

/**
 * Clear cart
 */
$cart->empty_cart();

/**
 * Initiate payment
 */
try {
    $request = WebToPay::buildRequest( array(
        'projectid'     => PAYMENT_PROJECT_ID,
        'sign_password' => PAYMENT_SIGN_PASSWORD,
        'orderid'       => $orderId,
        'amount'        => $cartTotal,
        'currency'      => $currency,
        'country'       => 'LT',
        'accepturl'     => $orderReceivedUrl,
        'cancelurl'     => $websiteUrl,
        'callbackurl'   => $websiteUrl . '/wc-api/wc_paysera_payment',
        'test'          => 1,
        'p_firstname'   => $clientInfo['name'],
        'p_lastname'    => $clientInfo['surname'],
        'p_email'       => $clientInfo['email'],
    ) );

    $redirectUrl = WebToPay::PAY_URL . '?data=' . $request['data'] . '&sign=' . $request['sign'];

    echo json_encode( $redirectUrl );
} catch ( WebToPayException $e ) {
    echo json_encode( $e );
}