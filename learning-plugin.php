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
add_action('woocommerce_cart_calculate_fees', 'add_packaging_fee');

function add_packaging_fee($cart) {
    // Don't run in the WordPress admin area
    // unless it's an AJAX request.
    if (is_admin() && ! wp_doing_ajax()) {
        return;
    }

    // Count total quantities, not just distinct product types.
    if ($cart->get_cart_contents_count() > 3) {
        $cart->add_fee('Packaging', 5, false);
    }
}