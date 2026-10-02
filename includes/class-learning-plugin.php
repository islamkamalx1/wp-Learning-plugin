<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Learning_Plugin {

    private static $instance = null;

    private function __construct() {
        $this->register_hooks();
    }

    public static function instance() {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function register_hooks() {
        add_action( 'init', [ $this, 'init' ] );
        new Learning_Product_Listener();
    }

    public function init() {
        // Plugin initialization code.
    }
}