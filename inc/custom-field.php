<?php
/**
 * 
 * Display custom text field
 * 
 */
function payment_create_custom_field() {
    $args = array(
        'id'            => 'payment_cf_text',
        'label'         => __( 'Wish field title', 'payment_cf' ),
        'class'         => 'payment-cf',
        'desc_tip'      => true,
        'description'   => __( 'Enter the title of your wish field.', 'payment_cf' ),
    );
    woocommerce_wp_text_input( $args );
}
add_action( 'woocommerce_product_options_general_product_data', 'payment_create_custom_field' );

/**
 * 
 * Save Custom field
 * 
 */
function payment_save_custom_field( $post_id ) {
    $title   = isset( $_POST['payment_cf_text'] ) ? $_POST['payment_cf_text'] : '';
    $product = wc_get_product( $post_id );
    
    $product->update_meta_data( 'payment_cf_text', sanitize_text_field( $title ) );
    $product->save();
}
add_action( 'woocommerce_process_product_meta', 'payment_save_custom_field' );

/**
 * 
 * Display custom field in product page
 * 
 */
function payment_display_custom_field() {
    global $post;
    
    $product = wc_get_product( $post->ID );
    $title   = $product->get_meta( 'payment_cf_text' );

    // Display custom field only if field is not empty
    if( $title ) {
        printf(
            '<div class="payment-cf-wrapper"><label for="payment-cf-title">%s</label><input type="text" id="payment-cf-textfield" name="payment-cf-textfield" value="" required></div>', esc_html( $title )
        );
    }
}
add_action( 'woocommerce_before_add_to_cart_button', 'payment_display_custom_field' );

/**
 * 
 * Add the custom field as item data to the cart object
 *
 */
function cfwc_add_custom_field_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
    if( !empty( $_POST['payment-cf-textfield'] ) ) {
        $cart_item_data['payment_cf_field'] = $_POST['payment-cf-textfield'];
    }

    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'cfwc_add_custom_field_item_data', 10, 4 );

/**
 * 
 * Add custom field to order page
 * 
 */
function payment_add_custom_data_to_order( $item, $cart_item_key, $values, $order ) {
    foreach( $item as $cart_item_key => $values ) {
        if( isset( $values['payment_cf_field'] ) ) {
            $item->add_meta_data( __( 'Wish text', 'payment' ), $values['payment_cf_field'], true );
        }
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'payment_add_custom_data_to_order', 10, 4 );