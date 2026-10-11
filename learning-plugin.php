<?php

/**
 * Plugin Name: Learning Plugin
 * Description: A learning plugin for WooCommerce.
 * Version: 1.0.0
 * Author: Islam Kamal
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// #### point 1 ####
// add_action('woocommerce_cart_calculate_fees', 'add_packaging_fee');

// function add_packaging_fee($cart) {
//     // Don't run in the WordPress admin area
//     // unless it's an AJAX request.
//     if (is_admin() && ! wp_doing_ajax()) {
//         return;
//     }

//     // Count total quantities, not just distinct product types.
//     if ($cart->get_cart_contents_count() > 3) {
//         $cart->add_fee('Packaging', 5, false);
//     }
// }

// #### point 2 ####
// add_action(
//     'woocommerce_before_calculate_totals',
//     'apply_shoes_discount'
// );

// function apply_shoes_discount($cart) {
//     if (is_admin() && ! wp_doing_ajax()) {
//         return;
//     }

//     if ($cart->is_empty()) {
//         return;
//     }

//     foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
//         $product = $cart_item['data'];

//         // Variations inherit the category from their parent product.
//         $product_id = $product->get_parent_id()
//             ?: $product->get_id();

//         $is_shoes = has_term(
//             'shoes',
//             'product_cat',
//             $product_id
//         );

//         // Save the original price only once.
//         if (!isset(
//             $cart->cart_contents[$cart_item_key]['_shoes_original_price']
//         )) {
//             $cart->cart_contents[$cart_item_key]['_shoes_original_price']
//                 = (float) $product->get_price();
//         }

//         $original_price = (float)
//             $cart->cart_contents[$cart_item_key]['_shoes_original_price'];

//         if ($is_shoes) {
//             // Always calculate from the saved original price.
//             $product->set_price($original_price * 0.90);
//         } else {
//             // Don't leave a discount on a non-Shoes item.
//             $product->set_price($original_price);
//         }
//     }
// }

// #### point 3 ####
// add_action(
//     'woocommerce_order_status_changed',
//     'log_order_status_change',
//     10,
//     4
// );

// function log_order_status_change(
//     $order_id,
//     $old_status,
//     $new_status,
//     $order
// ) {
//     $message = sprintf(
//         'order #%d: %s → %s',
//         $order_id,
//         $old_status,
//         $new_status
//     );

//     error_log($message);
// }
// order #108: refunded → processing