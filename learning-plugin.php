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

//     return rest_ensure_response( $data );
// }

// [{"id":62,"name":"Logo Collection","price":"18"},{"id":63,"name":"WordPress Pennant edited","price":"11.05"},{"id":60,"name":"T-Shirt with Logo","price":"18"},{"id":61,"name":"Beanie with Logo","price":"18"},{"id":50,"name":"Long Sleeve Tee","price":"25"}]

// ### point 2 ####

add_action( 'rest_api_init', function () {
    register_rest_route(
        'learning/v1',
        '/products/(?P<id>\d+)',
        array(
            'methods'             => 'GET',
            'callback'            => 'learning_get_product',
            'permission_callback' => function () {
                return current_user_can( 'manage_woocommerce' );
            },
            'args'                => array(
                'id' => array(
                    'validate_callback' => function ( $param ) {
                        return is_numeric( $param );
                    },
                ),
            ),
        )
    );
} );

function learning_get_product( WP_REST_Request $request ) {
    $product_id = (int) $request['id'];

    $product = wc_get_product( $product_id );

    if ( ! $product ) {
        return new WP_Error(
            'product_not_found',
            'Product not found.',
            array(
                'status' => 404,
            )
        );
    }

    return rest_ensure_response(
        array(
            'id'    => $product->get_id(),
            'name'  => $product->get_name(),
            'price' => $product->get_price(),
        )
    );
}