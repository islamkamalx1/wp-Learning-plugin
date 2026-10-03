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