
<?php
/**
 * Plugin Name: Learning Per Item Shipping
 * Description: Learning shipping method costing 3 per cart item quantity.
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

add_action('woocommerce_shipping_init', function () {
    if (class_exists('WC_Learning_Per_Item_Shipping')) {
        return;
    }

    class WC_Learning_Per_Item_Shipping extends WC_Shipping_Method
    {
        public function __construct($instance_id = 0)
        {
            $this->id = 'learning_per_item';
            $this->instance_id = absint($instance_id);
            $this->method_title = 'Learning per item';
            $this->method_description = 'Shipping costs 3 per item quantity.';
            $this->supports = [
                'shipping-zones',
                'instance-settings',
                'instance-settings-modal',
            ];

            $this->init();

            $this->title = $this->get_option(
                'title',
                'Learning per item'
            );

            $this->tax_status = $this->get_option(
                'tax_status',
                'none'
            );

            add_action(
                'woocommerce_update_options_shipping_' . $this->id,
                [$this, 'process_admin_options']
            );
        }

        public function init()
        {
            $this->init_instance_form_fields();
            $this->init_instance_settings();
        }

        public function init_instance_form_fields()
        {
            $this->instance_form_fields = [
                'title' => [
                    'title' => 'Method title',
                    'type' => 'text',
                    'default' => 'Learning per item',
                ],
                'tax_status' => [
                    'title' => 'Tax status',
                    'type' => 'select',
                    'default' => 'none',
                    'options' => [
                        'taxable' => 'Taxable',
                        'none' => 'None',
                    ],
                ],
            ];
        }

        public function calculate_shipping($package = [])
        {
            $quantity = 0;

            foreach ($package['contents'] ?? [] as $item) {
                $quantity += (int) ($item['quantity'] ?? 0);
            }

            $cost = 3 * $quantity;

            $this->add_rate([
                'id' => $this->get_rate_id(),
                'label' => $this->title,
                'cost' => $cost,
                'package' => $package,
            ]);
        }
    }
});

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['learning_per_item'] =
        'WC_Learning_Per_Item_Shipping';

    return $methods;
});
