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

error_log( 'Learning Plugin: main file loaded' );

define( 'LEARNING_PLUGIN_VERSION', '1.0.0' );
define( 'LEARNING_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LEARNING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook( __FILE__, function () {
    update_option( 'learning_plugin_activated_at', time() );
} );

register_deactivation_hook( __FILE__, function () {
    delete_option( 'learning_plugin_activated_at' );
} );

require_once LEARNING_PLUGIN_PATH . 'includes/class-learning-product-listener.php';
require_once LEARNING_PLUGIN_PATH . 'includes/class-learning-plugin.php';

add_action( 'init', function () {
    error_log( 'Learning Plugin: init' );
} );

add_action( 'wp_loaded', function () {
    error_log( 'Learning Plugin: wp_loaded' );
} );

add_action( 'rest_api_init', function () {
    error_log( 'Learning Plugin: rest_api_init' );
} );

add_action( 'template_redirect', function () {
    error_log( 'Learning Plugin: template_redirect' );
} );

add_action( 'admin_init', function () {
    error_log( 'Learning Plugin: admin_init' );
} );

add_filter( 'the_title', function ( $title, $id ) {
    return 'product' === get_post_type( $id ) ? $title . ' ★' : $title;
}, 20, 2 );

add_filter( 'the_title', function ( $title, $id ) {
    return 'product' === get_post_type( $id ) ? $title . ' (B)' : $title;
}, 10, 2 );

add_action( 'save_post', function ( $post_id, $post, $update ) {
    error_log( sprintf( 'Learning Plugin: save_post id=%d type=%s status=%s update=%s', $post_id, $post->post_type, $post->post_status, $update ? 'yes' : 'no' ) );

    if ( 'product' === $post->post_type ) {
        do_action( 'learning_product_saved', $post_id );
    }
}, 10, 3 );


add_action( 'plugins_loaded', function () {
  error_log( 'Learning Plugin: plugins_loaded' );

    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function () {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong>Learning Plugin</strong>
                    requires WooCommerce to be active.
                </p>
            </div>
            <?php
        } );

        return;
    }


    Learning_Plugin::instance();
} );