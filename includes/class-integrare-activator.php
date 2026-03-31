<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Activator {

    /**
     * Create custom tables and flush rewrite rules.
     */
    public static function activate() {
        self::create_tables();
        // Flush rewrite rules so /tienda routes work immediately
        Integrare_CPT::register_post_type();
        Integrare_Front::add_rewrite_rules();
        flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules on deactivation.
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Create the 3 custom tables using dbDelta.
     */
    private static function create_tables() {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();
        $cart_table   = $wpdb->prefix . 'integrare_cart';
        $orders_table = $wpdb->prefix . 'integrare_orders';
        $items_table  = $wpdb->prefix . 'integrare_order_items';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // ── Cart Table ─────────────────────────────────────
        $sql_cart = "CREATE TABLE {$cart_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            quantity INT UNSIGNED NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_product (user_id, product_id),
            KEY idx_user (user_id)
        ) {$charset};";

        dbDelta( $sql_cart );

        // ── Orders Table ───────────────────────────────────
        $sql_orders = "CREATE TABLE {$orders_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            payment_method VARCHAR(20) DEFAULT NULL,
            shipping_name VARCHAR(100) DEFAULT NULL,
            shipping_phone VARCHAR(30) DEFAULT NULL,
            shipping_address VARCHAR(255) DEFAULT NULL,
            shipping_city VARCHAR(100) DEFAULT NULL,
            shipping_state VARCHAR(100) DEFAULT NULL,
            shipping_zip VARCHAR(10) DEFAULT NULL,
            notes TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_user (user_id),
            KEY idx_status (status)
        ) {$charset};";

        dbDelta( $sql_orders );

        // ── Order Items Table ──────────────────────────────
        $sql_items = "CREATE TABLE {$items_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            quantity_boxes INT UNSIGNED NOT NULL,
            units_per_box INT UNSIGNED NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            discount_pct DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            line_total DECIMAL(12,2) NOT NULL,
            PRIMARY KEY (id),
            KEY idx_order (order_id)
        ) {$charset};";

        dbDelta( $sql_items );

        // Store DB version for future migrations
        update_option( 'integrare_db_version', INTEGRARE_DB_VERSION );
    }
}
