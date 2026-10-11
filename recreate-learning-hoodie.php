
<?php
/**
 * Recreate Learning Hoodie using WooCommerce CRUD.
 *
 * should be in /public but i put it here to push in the repo
 *
 * Run:
 * wp eval-file recreate-learning-hoodie.php
 */

if ( ! class_exists( 'WooCommerce' ) ) {
    WP_CLI::error( 'WooCommerce is not active.' );
}

if ( ! function_exists( 'wc_get_product' ) ) {
    WP_CLI::error( 'WooCommerce product APIs are unavailable.' );
}

/**
 * Find an existing global attribute by its slug.
 */
function learning_find_attribute_id( $slug ) {
    foreach ( wc_get_attribute_taxonomies() as $attribute ) {
        if ( $attribute->attribute_name === $slug ) {
            return (int) $attribute->attribute_id;
        }
    }

    WP_CLI::error(
        "Global attribute '{$slug}' does not exist. Create it in Products > Attributes first."
    );
}

/**
 * Find a term by its displayed name.
 */
function learning_find_term( $name, $taxonomy ) {
    $term = get_term_by( 'name', $name, $taxonomy );

    if ( ! $term || is_wp_error( $term ) ) {
        WP_CLI::error(
            "Term '{$name}' not found in {$taxonomy}."
        );
    }

    return $term;
}

/**
 * Find a product category by name.
 */
$category = get_term_by( 'name', 'Shoes', 'product_cat' );

if ( ! $category || is_wp_error( $category ) ) {
    WP_CLI::error( 'Create the Shoes category first.' );
}

/**
 * Resolve global attribute taxonomies.
 */
$color_attribute_id = learning_find_attribute_id( 'color' );
$size_attribute_id  = learning_find_attribute_id( 'size' );

$color_taxonomy = wc_attribute_taxonomy_name( 'color' );
$size_taxonomy  = wc_attribute_taxonomy_name( 'size' );

/**
 * Resolve existing global terms.
 */
$red  = learning_find_term( 'أحمر', $color_taxonomy );
$blue = learning_find_term( 'Blue', $color_taxonomy );

$small  = learning_find_term( 'S', $size_taxonomy );
$medium = learning_find_term( 'M', $size_taxonomy );

/**
 * Create a separate product to preserve the hand-made one.
 */
$product = new WC_Product_Variable();

$product->set_name( 'Learning Hoodie (Code)' );
$product->set_status( 'publish' );
$product->set_category_ids( array( (int) $category->term_id ) );
$product->set_catalog_visibility( 'visible' );

$color = new WC_Product_Attribute();
$color->set_id( $color_attribute_id );
$color->set_name( $color_taxonomy );
$color->set_options( array(
    (int) $red->term_id,
    (int) $blue->term_id,
) );
$color->set_position( 0 );
$color->set_visible( true );
$color->set_variation( true );

$size = new WC_Product_Attribute();
$size->set_id( $size_attribute_id );
$size->set_name( $size_taxonomy );
$size->set_options( array(
    (int) $small->term_id,
    (int) $medium->term_id,
) );
$size->set_position( 1 );
$size->set_visible( true );
$size->set_variation( true );

/**
 * Custom attribute: Material = Cotton.
 * This is NOT a global pa_material taxonomy.
 */
$material = new WC_Product_Attribute();
$material->set_id( 0 );
$material->set_name( 'Material' );
$material->set_options( array( 'Cotton' ) );
$material->set_position( 2 );
$material->set_visible( true );
$material->set_variation( false );

$product->set_attributes( array(
    $color_taxonomy => $color,
    $size_taxonomy  => $size,
    'Material'      => $material,
) );

$product_id = $product->save();

if ( ! $product_id ) {
    WP_CLI::error( 'Failed to create the parent product.' );
}

/**
 * Four combinations: Color x Size.
 *
 * Example prices and stock quantities:
 * replace these with your hand-made values for comparison.
 */
$variations = array(
    array(
        'color' => $red,
        'size'  => $small,
        'price' => '120',
        'stock' => 5,
    ),
    array(
        'color' => $red,
        'size'  => $medium,
        'price' => '130',
        'stock' => 8,
    ),
    array(
        'color' => $blue,
        'size'  => $small,
        'price' => '140',
        'stock' => 10,
    ),
    array(
        'color' => $blue,
        'size'  => $medium,
        'price' => '150',
        'stock' => 12,
    ),
);

foreach ( $variations as $index => $data ) {
    $variation = new WC_Product_Variation();

    $variation->set_parent_id( $product_id );

    $variation->set_attributes( array(
        $color_taxonomy => $data['color']->slug,
        $size_taxonomy  => $data['size']->slug,
    ) );

    $variation->set_regular_price( $data['price'] );
    $variation->set_manage_stock( true );
    $variation->set_stock_quantity( $data['stock'] );
    $variation->set_status( 'publish' );

    $variation_id = $variation->save();

    if ( ! $variation_id ) {
        WP_CLI::warning(
            'Failed to save variation ' . ( $index + 1 )
        );
        continue;
    }

    WP_CLI::log(
        sprintf(
            'Variation ID: %d | Color: %s | Size: %s | Price: %s | Stock: %d',
            $variation_id,
            $data['color']->name,
            $data['size']->name,
            $data['price'],
            $data['stock']
        )
    );
}

/**
 * Recalculate the parent variable product's derived data.
 */
WC_Product_Variable::sync( $product_id );
wc_delete_product_transients( $product_id );

WP_CLI::success(
    "Created Learning Hoodie (Code). Parent ID: {$product_id}"
);
