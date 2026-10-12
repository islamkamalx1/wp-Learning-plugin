<?php
/**
 * Plugin Name: Learning Pay
 * Description: Fake WooCommerce payment gateway for learning/testing. Always succeeds, puts the order on-hold with a note, and redirects to the thank-you page.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 * Text Domain: learning-pay
 */

defined( 'ABSPATH' ) || exit;

// Declare compatibility with WooCommerce HPOS (custom order tables).
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

// Register Learning Pay with the block-based Checkout.
add_action( 'woocommerce_blocks_loaded', function () {
	if ( ! class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		return;
	}

	class WC_Learning_Pay_Blocks extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
		protected $name = 'learning_pay';

		public function initialize() {
			$this->settings = get_option( 'woocommerce_learning_pay_settings', array() );
		}

		public function is_active() {
			return empty( $this->settings['enabled'] ) || 'yes' === $this->settings['enabled'];
		}

		public function get_payment_method_script_handles() {
			wp_register_script(
				'learning-pay-blocks',
				plugins_url( 'learning-pay-blocks.js', __FILE__ ),
				array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities' ),
				'1.0.0',
				true
			);
			return array( 'learning-pay-blocks' );
		}

		public function get_payment_method_data() {
			return array(
				'title'       => ! empty( $this->settings['title'] ) ? $this->settings['title'] : 'Learning Pay',
				'description' => ! empty( $this->settings['description'] ) ? $this->settings['description'] : '',
				'supports'    => array( 'products' ),
			);
		}
	}

	add_action(
		'woocommerce_blocks_payment_method_type_registration',
		function ( $registry ) {
			$registry->register( new WC_Learning_Pay_Blocks() );
		}
	);
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	class WC_Gateway_Learning_Pay extends WC_Payment_Gateway {

		public function __construct() {
			$this->id                 = 'learning_pay';
			$this->method_title       = __( 'Learning Pay', 'learning-pay' );
			$this->method_description = __( 'Fake gateway for learning. Always succeeds and sets the order to on-hold.', 'learning-pay' );
			$this->has_fields         = false;
			$this->supports           = array( 'products' );

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled     = $this->get_option( 'enabled' );

			add_action(
				'woocommerce_update_options_payment_gateways_' . $this->id,
				array( $this, 'process_admin_options' )
			);
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'     => array(
					'title'   => __( 'Enable/Disable', 'learning-pay' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Learning Pay', 'learning-pay' ),
					'default' => 'yes',
				),
				'title'       => array(
					'title'   => __( 'Title', 'learning-pay' ),
					'type'    => 'text',
					'default' => __( 'Learning Pay', 'learning-pay' ),
				),
				'description' => array(
					'title'   => __( 'Description', 'learning-pay' ),
					'type'    => 'textarea',
					'default' => __( 'Fake payment for testing. No real charge is made.', 'learning-pay' ),
				),
			);
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				wc_add_notice( __( 'Order not found.', 'learning-pay' ), 'error' );
				return array( 'result' => 'failure' );
			}

			// Always succeeds: put the order on-hold with a note.
			$order->update_status(
				'on-hold',
				__( 'Learning Pay: fake payment accepted. Order is on-hold.', 'learning-pay' )
			);

			wc_reduce_stock_levels( $order_id );
			WC()->cart->empty_cart();

			// Redirect to the thank-you page.
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}

	add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {
		$gateways[] = 'WC_Gateway_Learning_Pay';
		return $gateways;
	} );
} );