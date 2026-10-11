
<?php
defined('ABSPATH') || exit;

$simple_product_id   = 52;
$hoodie_variation_id = 96;

$log_file = WP_CONTENT_DIR . '/learning-order-lifecycle.log';

$log = static function ($message) use ($log_file) {
    file_put_contents(
        $log_file,
        '[' . gmdate('Y-m-d H:i:s') . ' UTC] ' . $message . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
};

if (!function_exists('wc_get_product')) {
    throw new RuntimeException('WooCommerce is not loaded.');
}

$simple = wc_get_product($simple_product_id);
$variation = wc_get_product($hoodie_variation_id);

if (!$simple || !$simple->is_type('simple')) {
    throw new RuntimeException('Invalid simple product ID.');
}

if (!$variation || !$variation->is_type('variation')) {
    throw new RuntimeException('Invalid variation ID.');
}

$parent = wc_get_product($variation->get_parent_id());

if (!$parent || !$parent->is_type('variable')) {
    throw new RuntimeException('Variation parent is invalid.');
}

$get_stock = static function ($product) {
    $owner_id = $product->get_stock_managed_by_id();
    $owner = wc_get_product($owner_id);

    if (!$owner || !$owner->managing_stock()) {
        return 'not managed';
    }

    return (string) $owner->get_stock_quantity();
};

$log('START');
$log('Simple stock before: ' . $get_stock($simple));
$log('Variation stock before: ' . $get_stock($variation));

// Record status changes in the log.
add_action(
    'woocommerce_order_status_changed',
    static function ($order_id, $old_status, $new_status) use ($log) {
        $log("Order #{$order_id}: {$old_status} -> {$new_status}");
    },
    10,
    3
);

$order = wc_create_order([
    'status' => 'pending',
    'customer_id' => 0,
]);

if (is_wp_error($order) || !$order instanceof WC_Order) {
    throw new RuntimeException('Could not create order.');
}

$order->set_created_via('learning-order-lifecycle');
$order->set_currency(get_woocommerce_currency());

$order->set_billing_first_name('Learning');
$order->set_billing_last_name('Customer');
$order->set_billing_address_1('1 Test Street');
$order->set_billing_city('Cairo');
$order->set_billing_country('EG');
$order->set_billing_email('learning-test@example.com');
$order->set_billing_phone('01000000000');

$order->set_payment_method('cod');
$order->set_payment_method_title('Cash on delivery');

$order->add_product($simple, ['qty' => 1]);
$order->add_product($variation, ['qty' => 1]);

$shipping = new WC_Order_Item_Shipping();
$shipping->set_method_title('Learning Test Shipping');
$shipping->set_method_id('learning_test_shipping');
$shipping->set_total(60);
$order->add_item($shipping);

$fee = new WC_Order_Item_Fee();
$fee->set_name('Learning Test Fee');
$fee->set_amount(25);
$fee->set_total(25);
$fee->set_tax_status('none');
$order->add_item($fee);

$order->calculate_totals(false);
$order->save();

$order_id = $order->get_id();

$order->add_order_note(
    'Learning lifecycle test: order created with products, shipping, and fee.',
    false
);

$log("Created order #{$order_id}; total={$order->get_total()}");

// Move through the lifecycle.
$order->update_status(
    'processing',
    'Learning test: processing.',
    true
);

$order = wc_get_order($order_id);
$log('After processing: simple stock=' . $get_stock($simple));
$log('After processing: variation stock=' . $get_stock($variation));

$order->update_status(
    'completed',
    'Learning test: completed.',
    true
);

$order = wc_get_order($order_id);
$log('After completed: simple stock=' . $get_stock($simple));
$log('After completed: variation stock=' . $get_stock($variation));

// This changes the status only; it does NOT issue a financial refund.
$order->update_status(
    'refunded',
    'Learning test: refunded status only; no refund issued.',
    true
);

$order = wc_get_order($order_id);
$log('After refunded status: simple stock=' . $get_stock($simple));
$log('After refunded status: variation stock=' . $get_stock($variation));
$log('Total refunded=' . $order->get_total_refunded());
$log('END');

echo "Order ID: {$order_id}\n";
echo "Final status: {$order->get_status()}\n";
echo "Total: {$order->get_total()} {$order->get_currency()}\n";
echo "Log: {$log_file}\n";

foreach (wc_get_order_notes(['order_id' => $order_id]) as $note) {
    echo $note->content . "\n";
}
