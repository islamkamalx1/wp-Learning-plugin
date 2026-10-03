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