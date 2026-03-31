<?php
/**
 * Uninstall Integrare Core
 * Fires when the plugin is deleted from WordPress.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop custom tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}integrare_order_items" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}integrare_orders" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}integrare_cart" );

// Clean up options
delete_option( 'integrare_db_version' );

// Clean up post meta for all farmacia_product posts
$product_ids = $wpdb->get_col(
    "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'farmacia_product'"
);

if ( ! empty( $product_ids ) ) {
    $ids_placeholder = implode( ',', array_map( 'intval', $product_ids ) );
    $wpdb->query(
        "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ({$ids_placeholder}) AND meta_key LIKE '_integrare_%'"
    );
}

// Flush rewrite rules
flush_rewrite_rules();
