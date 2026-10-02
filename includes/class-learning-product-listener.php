<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Learning_Product_Listener {

    public function __construct() {
        add_action( 'learning_product_saved', [ $this, 'on_product_saved' ] );
    }

    public function on_product_saved( $post_id ) {
        error_log( 'Learning Product Listener: learning_product_saved id=' . $post_id );
    }
}
