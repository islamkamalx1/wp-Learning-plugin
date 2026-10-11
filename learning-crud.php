
<?php

if ( ! class_exists( 'WooCommerce' ) ) {
    WP_CLI::error( 'WooCommerce is not active.' );
}

// Configure these values for your local store.
$hoodie_name = 'Learning Hoodie (Code)';
$sku         = 'LEARNING-HOODIE-CODE';
$category    = 'shoes';

$color_taxonomy = 'pa_color';
$size_taxonomy  = 'pa_size';

// Use actual term slugs from your database.
$color_slug = 'red';
$size_slug  = 's';

// 1. Find a product by SKU.
$product_id = wc_get_product_id_by_sku( $sku );

if ( $product_id ) {
    WP_CLI::log( 'SKU product ID: ' . $product_id );
} else {
    WP_CLI::warning(
        "SKU '{$sku}' was not found. Assign this SKU in WooCommerce Admin."
    );
}

// 2. Get all published variable products in a category.
$variable_ids = wc_get_products( array(
    'status'   => 'publish',
    'type'     => 'variable',
    'category' => array( $category ),
    'limit'    => -1,
    'return'   => 'ids',
) );

WP_CLI::log(
    'Variable product IDs: ' . wp_json_encode( $variable_ids )
);

// 3. Find the hoodie.
$hoodie_ids = wc_get_products( array(
    'status' => array( 'publish', 'private', 'draft' ),
    'type'   => 'variable',
    'name'   => $hoodie_name,
    'limit'  => -1,
    'return' => 'ids',
) );

$hoodie_id = 0;

foreach ( $hoodie_ids as $id ) {
    if ( get_the_title( $id ) === $hoodie_name ) {
        $hoodie_id = (int) $id;
        break;
    }
}

if ( ! $hoodie_id ) {
    WP_CLI::error( "Could not find {$hoodie_name}." );
}

$hoodie = wc_get_product( $hoodie_id );

if ( ! $hoodie instanceof WC_Product_Variable ) {
    WP_CLI::error( 'The hoodie is not a variable product.' );
}

WP_CLI::log( 'Hoodie ID: ' . $hoodie_id );

// 4. Increase each variation's regular and sale prices by 10%.
// Run this section only once, because each run increases prices again.
foreach ( $hoodie->get_children() as $variation_id ) {
    $variation = wc_get_product( $variation_id );

    if ( ! $variation instanceof WC_Product_Variation ) {
        continue;
    }

    $regular = $variation->get_regular_price( 'edit' );
    $sale    = $variation->get_sale_price( 'edit' );

    if ( $regular === '' ) {
        WP_CLI::warning( "No regular price on variation {$variation_id}." );
        continue;
    }

    $variation->set_regular_price(
        wc_format_decimal(
            (float) $regular * 1.10,
            wc_get_price_decimals()
        )
    );

    if ( $sale !== '' ) {
        $variation->set_sale_price(
            wc_format_decimal(
                (float) $sale * 1.10,
                wc_get_price_decimals()
            )
        );
    }

    $variation->save();

    WP_CLI::log(
        sprintf(
            'Variation %d: regular %s -> %s',
            $variation_id,
            $regular,
            $variation->get_regular_price( 'edit' )
        )
    );
}

// Recalculate the parent product's derived price/stock data.
WC_Product_Variable::sync( $hoodie_id );
wc_delete_product_transients( $hoodie_id );

// 5. Find a variation by color + size.
$data_store = WC_Data_Store::load( 'product' );

$variation_id = $data_store->find_matching_product_variation(
    wc_get_product( $hoodie_id ),
    array(
        'attribute_' . $color_taxonomy => $color_slug,
        'attribute_' . $size_taxonomy  => $size_slug,
    )
);

if ( $variation_id ) {
    $matched = wc_get_product( $variation_id );

    WP_CLI::log(
        sprintf(
            'Matched variation ID: %d; price: %s',
            $variation_id,
            $matched->get_price()
        )
    );
} else {
    WP_CLI::warning(
        'No matching variation. Check color and size term slugs.'
    );
}

// 6. Save and read custom metadata using CRUD.
$hoodie = wc_get_product( $hoodie_id );

$hoodie->update_meta_data(
    '_learning_badge',
    'Featured Learning Product'
);
$hoodie->save();

$reloaded = wc_get_product( $hoodie_id );

WP_CLI::log(
    '_learning_badge = ' .
    $reloaded->get_meta( '_learning_badge', true, 'edit' )
);

WP_CLI::success( 'Learning CRUD tasks completed.' );
