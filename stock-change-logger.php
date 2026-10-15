
<?php
/**
 * Plugin Name: Stock Change Logger
 * Description: Records WooCommerce stock changes for auditing.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Text Domain: stock-change-logger
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SCL_VERSION', '1.0.0' );
define( 'SCL_PLUGIN_FILE', __FILE__ );
define( 'SCL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook(
    __FILE__,
    'scl_activate_plugin'
);

function scl_activate_plugin() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );

        wp_die(
            'Stock Change Logger requires WooCommerce.'
        );
    }

    require_once SCL_PLUGIN_DIR
        . 'includes/class-stock-logger.php';

    SCL_Stock_Logger::activate();
}

add_action( 'plugins_loaded', 'scl_boot_plugin' );

function scl_boot_plugin() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    require_once SCL_PLUGIN_DIR
        . 'includes/class-stock-logger.php';

    SCL_Stock_Logger::init();
}
