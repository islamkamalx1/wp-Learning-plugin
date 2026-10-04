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

// add_action( 'rest_api_init', function () {
//     register_rest_route(
//         'learning/v1',
//         '/products/(?P<id>\d+)',
//         array(
//             'methods'             => 'GET',
//             'callback'            => 'learning_get_product',
//             'permission_callback' => function () {
//                 return current_user_can( 'manage_woocommerce' );
//             },
//             'args'                => array(
//                 'id' => array(
//                     'validate_callback' => function ( $param ) {
//                         return is_numeric( $param );
//                     },
//                 ),
//             ),
//         )
//     );
// } );

// function learning_get_product( WP_REST_Request $request ) {
//     $product_id = (int) $request['id'];

//     $product = wc_get_product( $product_id );

//     if ( ! $product ) {
//         return new WP_Error(
//             'product_not_found',
//             'Product not found.',
//             array(
//                 'status' => 404,
//             )
//         );
//     }

//     return rest_ensure_response(
//         array(
//             'id'    => $product->get_id(),
//             'name'  => $product->get_name(),
//             'price' => $product->get_price(),
//         )
//     );
// }

// #### point 3 ####

// register_rest_route(
//     'learning/v1',
//     '/notes',
//     array(
//         'methods'             => 'POST',
//         'callback'            => 'learning_create_note',
//         'permission_callback' => function () {
//             return current_user_can( 'manage_woocommerce' );
//         },
//         'args'                => array(
//             'text' => array(
//                 'required'          => true,
//                 'sanitize_callback' => 'sanitize_text_field',
//             ),
//         ),
//     )
// );

// function learning_create_note( WP_REST_Request $request ) {
//     global $wpdb;

//     $table_name = $wpdb->prefix . 'learning_log';

//     $text = $request->get_param( 'text' );

//     $inserted = $wpdb->insert(
//         $table_name,
//         array(
//             'message'    => $text,
//             'created_at' => current_time( 'mysql' ),
//         ),
//         array(
//             '%s',
//             '%s',
//         )
//     );

//     if ( false === $inserted ) {
//         return new WP_Error(
//             'note_insert_failed',
//             'Could not save the note.',
//             array(
//                 'status' => 500,
//             )
//         );
//     }

//     return new WP_REST_Response(
//         array(
//             'id'      => $wpdb->insert_id,
//             'message' => $text,
//         ),
//         201
//     );
// }

// ### point 4 ####

register_activation_hook( __FILE__, 'learning_activate' );
register_deactivation_hook( __FILE__, 'learning_deactivate' );

function learning_activate() {
    add_role(
        'learning_viewer',
        'Learning Viewer',
        array(
            'read'                => true,
            'view_learning_notes' => true,
        )
    );
}


function learning_deactivate() {
    remove_role( 'learning_viewer' );
}

add_action( 'rest_api_init', function () {

    register_rest_route(
        'learning/v1',
        '/notes',
        array(
            'methods'             => 'GET',
            'callback'            => 'learning_get_notes',
            'permission_callback' => function () {
                return current_user_can( 'view_learning_notes' );
            },
        )
    );

} );

function learning_get_notes( WP_REST_Request $request ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'learning_log';

    $notes = $wpdb->get_results(
        "SELECT id, message, created_at
         FROM $table_name
         ORDER BY created_at DESC",
        ARRAY_A
    );

    return rest_ensure_response( $notes );
}