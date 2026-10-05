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

// add_action( 'rest_api_init', function () {
//     register_rest_route(
//         'learning/v1',
//         '/products/latest',
//         array(
//             'methods'             => 'GET',
//             'callback'            => 'learning_get_latest_products',
//          'permission_callback' => function () {
//     return current_user_can( 'manage_woocommerce' );
// },
//         )
//     );
// } );

// function learning_get_latest_products( WP_REST_Request $request ) {

//     $cache_key = 'learning_latest_products';

//     // Try to get cached products.
//     $cached_products = get_transient( $cache_key );

//     if ( false !== $cached_products ) {

//         error_log( 'Latest products cache HIT' );

//         return rest_ensure_response( $cached_products );
//     }

//     // Cache miss.
//     error_log( 'Latest products cache MISS' );

//     $products = wc_get_products(
//         array(
//             'limit'   => 5,
//             'orderby' => 'date',
//             'order'   => 'DESC',
//             'status'  => 'publish',
//         )
//     );

//     $data = array();

//     foreach ( $products as $product ) {
//         $data[] = array(
//             'id'    => $product->get_id(),
//             'name'  => $product->get_name(),
//             'price' => $product->get_price(),
//         );
//     }

//     // Cache for 10 minutes.
//     set_transient(
//         $cache_key,
//         $data,
//         10 * MINUTE_IN_SECONDS
//     );

//     return rest_ensure_response( $data );
// }

// add_action(
//     'woocommerce_update_product',
//     'learning_clear_latest_products_cache'
// );

// function learning_clear_latest_products_cache( $product_id ) {

//     delete_transient( 'learning_latest_products' );

//     error_log(
//         'Latest products cache CLEARED after product update: ' . $product_id
//     );
// }

// ### point 2 ####

add_action( 'learning_hourly', 'learning_hourly_callback' );

function learning_hourly_callback() {
    error_log( 'learning_hourly ran at ' . current_time( 'mysql' ) );
}

add_action( 'wp', 'learning_schedule_hourly' );

function learning_schedule_hourly() {
    if ( ! wp_next_scheduled( 'learning_hourly' ) ) {
        wp_schedule_event(
            time(),
            'hourly',
            'learning_hourly'
        );
    }
}