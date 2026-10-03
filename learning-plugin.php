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
// #### point 1 ####

// رجع ال post + revisions بتوعه
// SELECT ID, post_parent, post_type, post_status, post_title
// FROM wp_posts
// WHERE ID = 87
//    OR post_parent = 87;

// هنا رجع الmetadata بتاعة البوست و اللي فيها الcustom field اللي ضيفته
// SELECT *
// FROM wp_postmeta
// WHERE post_id = 87;

// رجع ال terms بتوعه و اللي هي ال categories و ال tags
// SELECT *
// FROM wp_term_relationships
// WHERE object_id = 87;

// دا رجع الcategory
// SELECT *
// FROM wp_term_taxonomy
// WHERE term_taxonomy_id IN (87);

// دا رجع ال tag
// SELECT *
// FROM wp_term_taxonomy
// WHERE term_taxonomy_id IN (86);

// رجع اسم الكاتيجوري
// SELECT *
// FROM wp_terms
// WHERE term_id IN (87);

// رجع اسم التاج
// SELECT *
// FROM wp_terms
// WHERE term_id IN (86);

// رجع الميتاداتا للكاتيجوري
// SELECT *
// FROM wp_termmeta
// WHERE term_id IN (87);

//رجع الميتاداتا للتاج
// SELECT *
// FROM wp_termmeta
// WHERE term_id IN (86);

#### point 2 ####

// في WP CLI
// wp eval "update_option('learning_test', ['a' => 1, 'b' => [2, 3]]);"

// SELECT option_name, option_value, autoload
// FROM wp_options
// WHERE option_name = 'learning_test';
// النتيجة
// option_name	option_value	autoload
// learning_test	a:2:{s:1:"a";i:1;s:1:"b";a:2:{i:0;i:2;i:1;i:3;}}	auto
// [
//     'a' => 1,
//     'b' => [2, 3]
// ]

// #### point 3 ####

// SELECT
//     option_name,
//     LENGTH(option_value) AS size,
//     autoload
// FROM wp_options
// WHERE autoload IN ('yes', 'on', 'auto-on', 'auto')
// ORDER BY size DESC
// LIMIT 10;
// النتيجة
// option_name	size	autoload
// _transient_wp_core_block_css_files	23778	on
// rewrite_rules	18070	on
// _transient_wp_styles_for_blocks	11341	on
// wp_user_roles	7811	on
// cron	4391	on
// widget_block	890	auto
// woocommerce_paypal_settings	866	on
// _transient_wc_attribute_taxonomies	464	on
// woocommerce_ces_tracks_queue	462	auto
// woocommerce_onboarding_profile	365	auto

// #### point 4 ####

function learning_query_products() {

    $query = new WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 10,
        'paged'          => 1,
        'fields'         => 'ids',

        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'name',
                'terms'    => 'Clothing',
            ],
        ],

        'meta_query' => [
            [
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '=',
            ],
        ],
    ]);

    error_log('Products Query: ' . $query->request);
//     [03-Oct-2026 15:37:41 UTC] Products Query: SELECT SQL_CALC_FOUND_ROWS  wp_posts.ID
//                                          FROM wp_posts  LEFT JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id) INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )
//                                          WHERE 1=1  AND ( 
//   wp_term_relationships.term_taxonomy_id IN (70,71,72,73)
// ) AND ( 
//   ( wp_postmeta.meta_key = '_stock_status' AND wp_postmeta.meta_value = 'instock' )
// ) AND ((wp_posts.post_type = 'product' AND (wp_posts.post_status = 'publish' 
// OR wp_posts.post_status = 'private')))
//                                          GROUP BY wp_posts.ID
//                                          ORDER BY wp_posts.post_date DESC
//                                          LIMIT 0, 10
}

add_action('wp_loaded', 'learning_query_products');
// شرح الjoins
// 1- بتربط الproduct الterm
// INNER JOIN wp_term_relationships
//     ON wp_posts.ID = wp_term_relationships.object_id
// 2- بتميز product category عن taxonomies تانية زي tags
// INNER JOIN wp_term_taxonomy
//     ON wp_term_relationships.term_taxonomy_id
//        = wp_term_taxonomy.term_taxonomy_id
// 3- بتجيب ال custom meta للproduct
// INNER JOIN wp_postmeta
//     ON wp_posts.ID = wp_postmeta.post_id