
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SCL_Stock_Logger {

    const TABLE_SUFFIX = 'scl_stock_logs';

    private static $pending = array();
    private static $bulk_before = array();
    private static $manage_stock_before = array();
    private static $deferred_stock_changes = array();
    private static $is_csv_import = false;

    public static function init() {
        add_action(
            'woocommerce_product_before_set_stock',
            array( __CLASS__, 'capture_before' ),
            10,
            1
        );

        add_action(
            'woocommerce_product_set_stock',
            array( __CLASS__, 'capture_after' ),
            10,
            1
        );

        add_action(
            'woocommerce_variation_before_set_stock',
            array( __CLASS__, 'capture_before' ),
            10,
            1
        );

        add_action(
            'woocommerce_variation_set_stock',
            array( __CLASS__, 'capture_after' ),
            10,
            1
        );

        add_action(
            'woocommerce_product_bulk_edit_start',
            array( __CLASS__, 'capture_bulk_before' )
        );

        add_action(
            'woocommerce_product_bulk_edit_save',
            array( __CLASS__, 'handle_bulk_edit' ),
            10,
            1
        );

      add_action(
        'woocommerce_before_product_object_save',
        array( __CLASS__, 'capture_manage_stock_before_save' ),
        10,
        2
    );

    add_action(
        'woocommerce_after_product_object_save',
        array( __CLASS__, 'capture_manage_stock_after_save' ),
        10,
        2
    );

    add_action(
        'woocommerce_reduce_order_item_stock',
        array( __CLASS__, 'log_order_stock_reduction' ),
        10,
        3
    );

    add_action(
        'woocommerce_restore_order_item_stock',
        array( __CLASS__, 'log_order_stock_restoration' ),
        10,
        4
    );
   
    add_action(
        'shutdown',
        array( __CLASS__, 'flush_deferred_stock_changes' )
    );

    add_action(
        'rest_api_init',
        array( __CLASS__, 'register_rest_routes' )
    );
    add_action(
        'woocommerce_product_importer_before_save_product_object',
        array( __CLASS__, 'start_csv_import' )
    );

    add_action(
        'woocommerce_product_import_inserted_product_object',
        array( __CLASS__, 'end_csv_import' )
    );
    }

    public static function activate() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name = $wpdb->prefix . self::TABLE_SUFFIX;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint(20) unsigned NOT NULL,
            parent_id bigint(20) unsigned DEFAULT NULL,
            sku varchar(100) DEFAULT NULL,
            event_type varchar(32) NOT NULL,
            stock_before decimal(20,6) DEFAULT NULL,
            stock_after decimal(20,6) DEFAULT NULL,
            stock_change decimal(20,6) DEFAULT NULL,
            value_before varchar(20) DEFAULT NULL,
            value_after varchar(20) DEFAULT NULL,
            cause varchar(50) NOT NULL,
            order_id bigint(20) unsigned DEFAULT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY order_id (order_id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta( $sql );
    }

    public static function capture_before( $product ) {
        if ( ! $product instanceof WC_Product ) {
            return;
        }

        $product_id = $product->get_id();

        // Read the persisted value before the stock update.
        $raw_stock = get_post_meta(
            $product_id,
            '_stock',
            true
        );

        self::$pending[ $product_id ] = array(
            'before' => (
                $raw_stock !== '' && is_numeric( $raw_stock )
            ) ? (float) $raw_stock : null,
            'product' => $product,
        );
    }



public static function capture_after( $product ) {
    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $product_id = $product->get_id();

    if ( ! isset( self::$pending[ $product_id ] ) ) {
        return;
    }

    $pending = self::$pending[ $product_id ];
    unset( self::$pending[ $product_id ] );

    $before = $pending['before'];
    $after  = $product->get_stock_quantity( 'edit' );

    if (
        $before === null ||
        $after === null ||
        ! is_numeric( $after )
    ) {
        return;
    }

    $after = (float) $after;
    $difference = $after - $before;

    if ( abs( $difference ) < 0.000001 ) {
        return;
    }

    $key = self::stock_change_key(
        $product_id,
        $before,
        $after
    );

    self::$deferred_stock_changes[ $key ] = array(
        'product_id'   => $product_id,
        'parent_id'    => $product->get_parent_id() ?: null,
        'sku'          => $product->get_sku(),
        'event_type'   => 'stock_quantity',
        'stock_before' => $before,
        'stock_after'  => $after,
        'stock_change' => $difference,
        'value_before' => null,
        'value_after'  => null,
    
'cause' => (
    defined( 'REST_REQUEST' ) && REST_REQUEST
)
    ? 'rest_api'
    : (
        self::is_csv_import_request()
            ? 'csv_import'
            : ( is_admin() ? 'admin_edit' : 'unknown' )
    ),

        'order_id'     => null,
        'user_id'      => get_current_user_id() ?: null,
        'created_at'   => current_time( 'mysql', true ),
    );
}



    private static function insert_log( $data ) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_SUFFIX;

        $wpdb->insert(
            $table_name,
            $data,
            array(
                '%d', '%d', '%s', '%s',
                '%f', '%f', '%f', '%s',
                '%s', '%s', '%d', '%d', '%s',
            )
        );
    }

    public static function capture_bulk_before() {
    $ids = isset( $_REQUEST['product_ids'] )
        ? wp_parse_id_list( wp_unslash( $_REQUEST['product_ids'] ) )
        : array();

    self::$bulk_before = array();

    foreach ( $ids as $product_id ) {
        $product = wc_get_product( $product_id );

        if ( ! $product ) {
            continue;
        }

        $raw_stock = get_post_meta(
            $product_id,
            '_stock',
            true
        );

        self::$bulk_before[ $product_id ] = (
            $raw_stock !== '' && is_numeric( $raw_stock )
        ) ? (float) $raw_stock : null;
    }
}

public static function handle_bulk_edit( $product ) {
    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $product_id = $product->get_id();

    if ( ! array_key_exists( $product_id, self::$bulk_before ) ) {
        return;
    }

    $before = self::$bulk_before[ $product_id ];
    unset( self::$bulk_before[ $product_id ] );

    $after = $product->get_stock_quantity( 'edit' );

    if (
        $before === null ||
        $after === null ||
        ! is_numeric( $after )
    ) {
        return;
    }

    $after = (float) $after;
    $difference = $after - $before;

    if ( abs( $difference ) < 0.000001 ) {
        return;
    }

    // Prevent a second log if the stock hooks already recorded
    // this exact transition during the current request.
    foreach ( self::$pending as $pending_id => $pending ) {
        if ( (int) $pending_id === (int) $product_id ) {
            return;
        }
    }

    self::insert_log(
        array(
            'product_id'   => $product_id,
            'parent_id'    => $product->get_parent_id() ?: null,
            'sku'          => $product->get_sku(),
            'event_type'   => 'stock_quantity',
            'stock_before' => $before,
            'stock_after'  => $after,
            'stock_change' => $difference,
            'value_before' => null,
            'value_after'  => null,
            'cause'        => 'admin_edit',
            'order_id'     => null,
            'user_id'      => get_current_user_id() ?: null,
            'created_at'   => current_time( 'mysql', true ),
        )
    );
}



public static function capture_manage_stock_before_save( $product, $data_store ) {
    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $product_id = $product->get_id();

    // New products have no persisted ID yet.
    if ( ! $product_id ) {
        return;
    }

    $raw_before = get_post_meta(
        $product_id,
        '_manage_stock',
        true
    );

    // WooCommerce defaults to not managing stock.
    $before = ( $raw_before === 'yes' ) ? 'yes' : 'no';

    self::$manage_stock_before[ $product_id ] = $before;
}

public static function capture_manage_stock_after_save( $product, $data_store ) {
    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $product_id = $product->get_id();

    if ( ! array_key_exists( $product_id, self::$manage_stock_before ) ) {
        return;
    }

    $before = self::$manage_stock_before[ $product_id ];
    unset( self::$manage_stock_before[ $product_id ] );

    $after = $product->get_manage_stock( 'edit' ) ? 'yes' : 'no';

    // Only log an actual change.
    if ( $before === $after ) {
        return;
    }

    self::insert_log(
        array(
            'product_id'   => $product_id,
            'parent_id'    => $product->get_parent_id() ?: null,
            'sku'          => $product->get_sku(),
            'event_type'   => 'manage_stock',
            'stock_before' => null,
            'stock_after'  => null,
            'stock_change' => null,
            'value_before' => $before,
            'value_after'  => $after,
            'cause'        => (
                is_admin() &&
                ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
            ) ? 'admin_edit' : 'unknown',
            'order_id'     => null,
            'user_id'      => get_current_user_id() ?: null,
            'created_at'   => current_time( 'mysql', true ),
        )
    );
}



public static function log_order_stock_reduction( $item, $change, $order ) {
    if (
        ! $item instanceof WC_Order_Item_Product ||
        ! $order instanceof WC_Order ||
        empty( $change['product'] )
    ) {
        return;
    }

    $product = $change['product'];

    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $before = isset( $change['from'] ) ? (float) $change['from'] : null;
    $after  = isset( $change['to'] ) ? (float) $change['to'] : null;

    if ( $before === null || $after === null || $before === $after ) {
        return;
    }

    $key = self::stock_change_key(
    $product->get_id(),
    $before,
    $after
);

unset( self::$deferred_stock_changes[ $key ] );

    self::insert_log(
        array(
            'product_id'   => $product->get_id(),
            'parent_id'    => $product->get_parent_id() ?: null,
            'sku'          => $product->get_sku(),
            'event_type'   => 'stock_quantity',
            'stock_before' => $before,
            'stock_after'  => $after,
            'stock_change' => $after - $before,
            'value_before' => null,
            'value_after'  => null,
            'cause'        => 'order_placed',
            'order_id'     => $order->get_id(),
            'user_id'      => $order->get_customer_id() ?: null,
            'created_at'   => current_time( 'mysql', true ),
        )
    );
}


public static function log_order_stock_restoration(
    $item,
    $new_stock,
    $old_stock,
    $order
) {
    if (
        ! $item instanceof WC_Order_Item_Product ||
        ! $order instanceof WC_Order
    ) {
        return;
    }

    $product = $item->get_product();

    if ( ! $product instanceof WC_Product ) {
        return;
    }

    if (
        ! is_numeric( $new_stock ) ||
        ! is_numeric( $old_stock )
    ) {
        return;
    }

    $before = (float) $old_stock;
    $after  = (float) $new_stock;

    if ( abs( $after - $before ) < 0.000001 ) {
        return;
    }

    $key = self::stock_change_key(
        $product->get_id(),
        $before,
        $after
    );

    unset( self::$deferred_stock_changes[ $key ] );

    self::insert_log(
        array(
            'product_id'   => $product->get_id(),
            'parent_id'    => $product->get_parent_id() ?: null,
            'sku'          => $product->get_sku(),
            'event_type'   => 'stock_quantity',
            'stock_before' => $before,
            'stock_after'  => $after,
            'stock_change' => $after - $before,
            'value_before' => null,
            'value_after'  => null,
            'cause'        => 'order_cancelled',
            'order_id'     => $order->get_id(),
            'user_id'      => get_current_user_id() ?: null,
            'created_at'   => current_time( 'mysql', true ),
        )
    );
}



private static function stock_change_key(
    $product_id,
    $before,
    $after
) {
    return implode(
        ':',
        array(
            (int) $product_id,
            (string) (float) $before,
            (string) (float) $after,
        )
    );
}

public static function flush_deferred_stock_changes() {
    foreach ( self::$deferred_stock_changes as $data ) {
        self::insert_log( $data );
    }

    self::$deferred_stock_changes = array();
}



public static function register_rest_routes() {
    register_rest_route(
        'scl/v1',
        '/stock-logs',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( __CLASS__, 'get_stock_logs' ),
            'permission_callback' => function () {
                return current_user_can( 'manage_woocommerce' );
            },
            'args' => array(
                'page' => array(
                    'default'           => 1,
                    'sanitize_callback' => 'absint',
                ),
                'per_page' => array(
                    'default'           => 20,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function ( $value ) {
                        return (int) $value >= 1 && (int) $value <= 100;
                    },
                ),
                'product_id' => array(
                    'sanitize_callback' => 'absint',
                ),
            ),
        )
    );
}

public static function get_stock_logs( WP_REST_Request $request ) {
    global $wpdb;

    $table_name = $wpdb->prefix . self::TABLE_SUFFIX;

    $page     = max( 1, (int) $request->get_param( 'page' ) );
    $per_page = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ) );
    $offset   = ( $page - 1 ) * $per_page;
    $product_id = (int) $request->get_param( 'product_id' );

    if ( $product_id > 0 ) {
        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE product_id = %d",
                $product_id
            )
        );

        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE product_id = %d
                ORDER BY id DESC
                LIMIT %d OFFSET %d",
                $product_id,
                $per_page,
                $offset
            ),
            ARRAY_A
        );
    } else {
        $total = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$table_name}"
        );

        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                ORDER BY id DESC
                LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
    }

    return rest_ensure_response(
        array(
            'data'        => $logs,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil( $total / max( 1, $per_page ) ),
        )
    );
}

public static function start_csv_import( $product ) {
    self::$is_csv_import = true;
}

public static function end_csv_import( $product ) {
    self::$is_csv_import = false;
}

private static function is_csv_import_request() {
    if ( ! is_admin() ) {
        return false;
    }

    $page = isset( $_GET['page'] )
        ? sanitize_key( wp_unslash( $_GET['page'] ) )
        : '';

    return 'product_importer' === $page;
}
}
