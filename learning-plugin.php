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
add_action( 'rest_api_init', function () {
    register_rest_route(
        'learning/v1',
        '/products/latest',
        array(
            'methods'             => 'GET',
            'callback'            => 'learning_get_latest_products',
         'permission_callback' => function () {
    return current_user_can( 'manage_woocommerce' );
},
        )
    );
} );

function learning_get_latest_products( WP_REST_Request $request ) {
    $products = wc_get_products(
        array(
            'limit'   => 5,
            'orderby' => 'date',
            'order'   => 'DESC',
            'status'  => 'publish',
        )
    );

    $data = array();

    foreach ( $products as $product ) {
        $data[] = array(
            'id'    => $product->get_id(),
            'name'  => $product->get_name(),
            'price' => $product->get_price(),
        );
    }

    return rest_ensure_response( $data );
}

// [{"id":62,"name":"Logo Collection","price":"18"},{"id":63,"name":"WordPress Pennant edited","price":"11.05"},{"id":60,"name":"T-Shirt with Logo","price":"18"},{"id":61,"name":"Beanie with Logo","price":"18"},{"id":50,"name":"Long Sleeve Tee","price":"25"}]