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

// register_activation_hook( __FILE__, 'learning_activate' );
// register_deactivation_hook( __FILE__, 'learning_deactivate' );

// function learning_activate() {
//     add_role(
//         'learning_viewer',
//         'Learning Viewer',
//         array(
//             'read'                => true,
//             'view_learning_notes' => true,
//         )
//     );
// }


// function learning_deactivate() {
//     remove_role( 'learning_viewer' );
// }

// add_action( 'rest_api_init', function () {

//     register_rest_route(
//         'learning/v1',
//         '/notes',
//         array(
//             'methods'             => 'GET',
//             'callback'            => 'learning_get_notes',
//             'permission_callback' => function () {
//                 return current_user_can( 'view_learning_notes' );
//             },
//         )
//     );

// } );

// function learning_get_notes( WP_REST_Request $request ) {
//     global $wpdb;

//     $table_name = $wpdb->prefix . 'learning_log';

//     $notes = $wpdb->get_results(
//         "SELECT id, message, created_at
//          FROM $table_name
//          ORDER BY created_at DESC",
//         ARRAY_A
//     );

//     return rest_ensure_response( $notes );
// }

// ### point 5 ####

// Nobody	NO Auth	401
// Admin	Application Password	403
// Learning Viewer	Application Password	200
// Customer	Application Password	403

// #### point 6 ####
// Without authentication any stranger could access the endpoint and retrieve learning notes that should only be available to users with the view_learning_notes capability.

// #### point 7 ####
// a:8:{s:13:"administrator";a:2:{s:4:"name";s:13:"Administrator";s:12:"capabilities";a:115:{s:13:"switch_themes";b:1;s:11:"edit_themes";b:1;s:16:"activate_plugins";b:1;s:12:"edit_plugins";b:1;s:10:"edit_users";b:1;s:10:"edit_files";b:1;s:14:"manage_options";b:1;s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:6:"import";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:8:"level_10";b:1;s:7:"level_9";b:1;s:7:"level_8";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;s:12:"delete_users";b:1;s:12:"create_users";b:1;s:17:"unfiltered_upload";b:1;s:14:"edit_dashboard";b:1;s:14:"update_plugins";b:1;s:14:"delete_plugins";b:1;s:15:"install_plugins";b:1;s:13:"update_themes";b:1;s:14:"install_themes";b:1;s:11:"update_core";b:1;s:10:"list_users";b:1;s:12:"remove_users";b:1;s:13:"promote_users";b:1;s:18:"edit_theme_options";b:1;s:13:"delete_themes";b:1;s:6:"export";b:1;s:18:"manage_woocommerce";b:1;s:16:"create_customers";b:1;s:24:"view_woocommerce_reports";b:1;s:12:"edit_product";b:1;s:12:"read_product";b:1;s:14:"delete_product";b:1;s:13:"edit_products";b:1;s:20:"edit_others_products";b:1;s:16:"publish_products";b:1;s:21:"read_private_products";b:1;s:15:"delete_products";b:1;s:23:"delete_private_products";b:1;s:25:"delete_published_products";b:1;s:22:"delete_others_products";b:1;s:21:"edit_private_products";b:1;s:23:"edit_published_products";b:1;s:20:"manage_product_terms";b:1;s:18:"edit_product_terms";b:1;s:20:"delete_product_terms";b:1;s:20:"assign_product_terms";b:1;s:15:"edit_shop_order";b:1;s:15:"read_shop_order";b:1;s:17:"delete_shop_order";b:1;s:16:"edit_shop_orders";b:1;s:23:"edit_others_shop_orders";b:1;s:19:"publish_shop_orders";b:1;s:24:"read_private_shop_orders";b:1;s:18:"delete_shop_orders";b:1;s:26:"delete_private_shop_orders";b:1;s:28:"delete_published_shop_orders";b:1;s:25:"delete_others_shop_orders";b:1;s:24:"edit_private_shop_orders";b:1;s:26:"edit_published_shop_orders";b:1;s:23:"manage_shop_order_terms";b:1;s:21:"edit_shop_order_terms";b:1;s:23:"delete_shop_order_terms";b:1;s:23:"assign_shop_order_terms";b:1;s:16:"edit_shop_coupon";b:1;s:16:"read_shop_coupon";b:1;s:18:"delete_shop_coupon";b:1;s:17:"edit_shop_coupons";b:1;s:24:"edit_others_shop_coupons";b:1;s:20:"publish_shop_coupons";b:1;s:25:"read_private_shop_coupons";b:1;s:19:"delete_shop_coupons";b:1;s:27:"delete_private_shop_coupons";b:1;s:29:"delete_published_shop_coupons";b:1;s:26:"delete_others_shop_coupons";b:1;s:25:"edit_private_shop_coupons";b:1;s:27:"edit_published_shop_coupons";b:1;s:24:"manage_shop_coupon_terms";b:1;s:22:"edit_shop_coupon_terms";b:1;s:24:"delete_shop_coupon_terms";b:1;s:24:"assign_shop_coupon_terms";b:1;}}s:6:"editor";a:2:{s:4:"name";s:6:"Editor";s:12:"capabilities";a:34:{s:17:"moderate_comments";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:12:"upload_files";b:1;s:15:"unfiltered_html";b:1;s:10:"edit_posts";b:1;s:17:"edit_others_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:10:"edit_pages";b:1;s:4:"read";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:17:"edit_others_pages";b:1;s:20:"edit_published_pages";b:1;s:13:"publish_pages";b:1;s:12:"delete_pages";b:1;s:19:"delete_others_pages";b:1;s:22:"delete_published_pages";b:1;s:12:"delete_posts";b:1;s:19:"delete_others_posts";b:1;s:22:"delete_published_posts";b:1;s:20:"delete_private_posts";b:1;s:18:"edit_private_posts";b:1;s:18:"read_private_posts";b:1;s:20:"delete_private_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"read_private_pages";b:1;}}s:6:"author";a:2:{s:4:"name";s:6:"Author";s:12:"capabilities";a:10:{s:12:"upload_files";b:1;s:10:"edit_posts";b:1;s:20:"edit_published_posts";b:1;s:13:"publish_posts";b:1;s:4:"read";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:12:"delete_posts";b:1;s:22:"delete_published_posts";b:1;}}s:11:"contributor";a:2:{s:4:"name";s:11:"Contributor";s:12:"capabilities";a:5:{s:10:"edit_posts";b:1;s:4:"read";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:12:"delete_posts";b:1;}}s:10:"subscriber";a:2:{s:4:"name";s:10:"Subscriber";s:12:"capabilities";a:2:{s:4:"read";b:1;s:7:"level_0";b:1;}}s:8:"customer";a:2:{s:4:"name";s:8:"Customer";s:12:"capabilities";a:1:{s:4:"read";b:1;}}s:12:"shop_manager";a:2:{s:4:"name";s:12:"Shop manager";s:12:"capabilities";a:93:{s:7:"level_9";b:1;s:7:"level_8";b:1;s:7:"level_7";b:1;s:7:"level_6";b:1;s:7:"level_5";b:1;s:7:"level_4";b:1;s:7:"level_3";b:1;s:7:"level_2";b:1;s:7:"level_1";b:1;s:7:"level_0";b:1;s:4:"read";b:1;s:18:"read_private_pages";b:1;s:18:"read_private_posts";b:1;s:10:"edit_posts";b:1;s:10:"edit_pages";b:1;s:20:"edit_published_posts";b:1;s:20:"edit_published_pages";b:1;s:18:"edit_private_pages";b:1;s:18:"edit_private_posts";b:1;s:17:"edit_others_posts";b:1;s:17:"edit_others_pages";b:1;s:13:"publish_posts";b:1;s:13:"publish_pages";b:1;s:12:"delete_posts";b:1;s:12:"delete_pages";b:1;s:20:"delete_private_pages";b:1;s:20:"delete_private_posts";b:1;s:22:"delete_published_pages";b:1;s:22:"delete_published_posts";b:1;s:19:"delete_others_posts";b:1;s:19:"delete_others_pages";b:1;s:17:"manage_categories";b:1;s:12:"manage_links";b:1;s:17:"moderate_comments";b:1;s:12:"upload_files";b:1;s:6:"export";b:1;s:6:"import";b:1;s:10:"list_users";b:1;s:18:"edit_theme_options";b:1;s:18:"manage_woocommerce";b:1;s:16:"create_customers";b:1;s:24:"view_woocommerce_reports";b:1;s:12:"edit_product";b:1;s:12:"read_product";b:1;s:14:"delete_product";b:1;s:13:"edit_products";b:1;s:20:"edit_others_products";b:1;s:16:"publish_products";b:1;s:21:"read_private_products";b:1;s:15:"delete_products";b:1;s:23:"delete_private_products";b:1;s:25:"delete_published_products";b:1;s:22:"delete_others_products";b:1;s:21:"edit_private_products";b:1;s:23:"edit_published_products";b:1;s:20:"manage_product_terms";b:1;s:18:"edit_product_terms";b:1;s:20:"delete_product_terms";b:1;s:20:"assign_product_terms";b:1;s:15:"edit_shop_order";b:1;s:15:"read_shop_order";b:1;s:17:"delete_shop_order";b:1;s:16:"edit_shop_orders";b:1;s:23:"edit_others_shop_orders";b:1;s:19:"publish_shop_orders";b:1;s:24:"read_private_shop_orders";b:1;s:18:"delete_shop_orders";b:1;s:26:"delete_private_shop_orders";b:1;s:28:"delete_published_shop_orders";b:1;s:25:"delete_others_shop_orders";b:1;s:24:"edit_private_shop_orders";b:1;s:26:"edit_published_shop_orders";b:1;s:23:"manage_shop_order_terms";b:1;s:21:"edit_shop_order_terms";b:1;s:23:"delete_shop_order_terms";b:1;s:23:"assign_shop_order_terms";b:1;s:16:"edit_shop_coupon";b:1;s:16:"read_shop_coupon";b:1;s:18:"delete_shop_coupon";b:1;s:17:"edit_shop_coupons";b:1;s:24:"edit_others_shop_coupons";b:1;s:20:"publish_shop_coupons";b:1;s:25:"read_private_shop_coupons";b:1;s:19:"delete_shop_coupons";b:1;s:27:"delete_private_shop_coupons";b:1;s:29:"delete_published_shop_coupons";b:1;s:26:"delete_others_shop_coupons";b:1;s:25:"edit_private_shop_coupons";b:1;s:27:"edit_published_shop_coupons";b:1;s:24:"manage_shop_coupon_terms";b:1;s:22:"edit_shop_coupon_terms";b:1;s:24:"delete_shop_coupon_terms";b:1;s:24:"assign_shop_coupon_terms";b:1;}}s:15:"learning_viewer";a:2:{s:4:"name";s:15:"Learning Viewer";s:12:"capabilities";a:2:{s:4:"read";b:1;s:19:"view_learning_notes";b:1;}}}