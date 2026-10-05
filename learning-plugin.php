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

// add_action( 'learning_hourly', 'learning_hourly_callback' );

// function learning_hourly_callback() {
//     error_log( 'learning_hourly ran at ' . current_time( 'mysql' ) );
// }

// add_action( 'wp', 'learning_schedule_hourly' );

// function learning_schedule_hourly() {
//     if ( ! wp_next_scheduled( 'learning_hourly' ) ) {
//         wp_schedule_event(
//             time(),
//             'hourly',
//             'learning_hourly'
//         );
//     }
// }

// #### point 3 ####

// add_action(
//     'woocommerce_update_product',
//     'learning_enqueue_product_job'
// );

// function learning_enqueue_product_job( $product_id ) {
//     as_enqueue_async_action(
//         'learning_product_saved',
//         array(
//             'product_id' => $product_id,
//         ),
//         'learning'
//     );
// }

// add_action(
//     'learning_product_saved',
//     'learning_process_product_saved'
// );

// function learning_process_product_saved( $product_id ) {
//     error_log(
//         'Action Scheduler processed product ID: ' . $product_id
//     );
// }

// #### point 4 ####

add_action( 'wp_footer', 'learning_print_products_fast' );

// N+1 query problem
// function learning_print_products_slow() {
//     $products = wc_get_products(
//         array(
//             'limit'  => 50,
//             'status' => 'publish',
//         )
//     );

//     echo '<div style="padding:20px">';
//     echo '<h3>Slow N+1 Products</h3>';

//     foreach ( $products as $product ) {
//         $product_id = $product->get_id();

//         // Lookup categories separately for EVERY product.
//         $category_names = array();

//         $term_ids = wp_get_post_terms(
//             $product_id,
//             'product_cat',
//             array(
//                 'fields' => 'ids',
//             )
//         );

//         foreach ( $term_ids as $term_id ) {
//             $term = get_term( $term_id, 'product_cat' );

//             if ( ! is_wp_error( $term ) ) {
//                 $category_names[] = $term->name;
//             }
//         }

//         printf(
//             '<p>%s — %s — Categories: %s</p>',
//             esc_html( $product->get_name() ),
//             esc_html( $product->get_price() ),
//             esc_html( implode( ', ', $category_names ) )
//         );
//     }

//     echo '</div>';
// }

// batch query
function learning_print_products_fast() {
    $products = wc_get_products(
        array(
            'limit'  => 50,
            'status' => 'publish',
        )
    );

    if ( empty( $products ) ) {
        return;
    }

    $product_ids = array_map(
         function ( $product ) {
            return $product->get_id();
        },
        $products
    );

    // Fetch categories for all products in one batch.
    $terms = wp_get_object_terms(
        $product_ids,
        'product_cat',
        array(
            'fields' => 'all_with_object_id',
        )
    );

    $categories_by_product = array();

    foreach ( $terms as $term ) {
        $categories_by_product[ $term->object_id ][] = $term->name;
    }

    echo '<div style="padding:20px">';
    echo '<h3>Fast Products</h3>';

    foreach ( $products as $product ) {
        $product_id = $product->get_id();

        $category_names = $categories_by_product[ $product_id ] ?? array();

        printf(
            '<p>%s — %s — Categories: %s</p>',
            esc_html( $product->get_name() ),
            esc_html( $product->get_price() ),
            esc_html( implode( ', ', $category_names ) )
        );
    }

    echo '</div>';
}