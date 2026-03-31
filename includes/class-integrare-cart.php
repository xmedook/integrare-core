<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Cart {

    /**
     * Get the cart table name.
     */
    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'integrare_cart';
    }

    /**
     * Add item to cart (or increment quantity).
     */
    public static function add_item( $user_id, $product_id, $quantity = 1 ) {
        global $wpdb;
        $table = self::table();
        $user_id    = absint( $user_id );
        $product_id = absint( $product_id );
        $quantity   = max( 1, absint( $quantity ) );

        // Check if item already exists
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT quantity FROM {$table} WHERE user_id = %d AND product_id = %d",
            $user_id, $product_id
        ) );

        if ( $existing !== null ) {
            $new_qty = (int) $existing + $quantity;
            return $wpdb->update(
                $table,
                array( 'quantity' => $new_qty, 'updated_at' => current_time( 'mysql' ) ),
                array( 'user_id' => $user_id, 'product_id' => $product_id ),
                array( '%d', '%s' ),
                array( '%d', '%d' )
            );
        }

        return $wpdb->insert( $table, array(
            'user_id'    => $user_id,
            'product_id' => $product_id,
            'quantity'   => $quantity,
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' ),
        ), array( '%d', '%d', '%d', '%s', '%s' ) );
    }

    /**
     * Update item quantity. If 0, remove.
     */
    public static function update_item( $user_id, $product_id, $quantity ) {
        global $wpdb;
        $quantity = absint( $quantity );

        if ( $quantity === 0 ) {
            return self::remove_item( $user_id, $product_id );
        }

        return $wpdb->update(
            self::table(),
            array( 'quantity' => $quantity, 'updated_at' => current_time( 'mysql' ) ),
            array( 'user_id' => absint( $user_id ), 'product_id' => absint( $product_id ) ),
            array( '%d', '%s' ),
            array( '%d', '%d' )
        );
    }

    /**
     * Remove item from cart.
     */
    public static function remove_item( $user_id, $product_id ) {
        global $wpdb;
        return $wpdb->delete(
            self::table(),
            array( 'user_id' => absint( $user_id ), 'product_id' => absint( $product_id ) ),
            array( '%d', '%d' )
        );
    }

    /**
     * Get all cart items with product data + pricing.
     */
    public static function get_items( $user_id ) {
        global $wpdb;
        $table = self::table();

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT product_id, quantity FROM {$table} WHERE user_id = %d ORDER BY created_at ASC",
            absint( $user_id )
        ), ARRAY_A );

        if ( ! $rows ) return array();

        $items = array();
        foreach ( $rows as $row ) {
            $product = get_post( $row['product_id'] );
            if ( ! $product || $product->post_status !== 'publish' ) continue;

            $pricing = Integrare_Pricing::calculate( $row['product_id'], $row['quantity'] );

            if ( is_wp_error( $pricing ) ) continue;

            $thumb = get_the_post_thumbnail_url( $row['product_id'], 'medium' );

            $items[] = array(
                'product_id'   => (int) $row['product_id'],
                'title'        => $product->post_title,
                'slug'         => $product->post_name,
                'thumbnail'    => $thumb ?: '',
                'quantity'     => (int) $row['quantity'],
                'pricing'      => $pricing,
            );
        }

        return $items;
    }

    /**
     * Get cart totals.
     */
    public static function get_totals( $user_id ) {
        $items = self::get_items( $user_id );
        $total = 0;
        $count = 0;

        foreach ( $items as $item ) {
            $total += $item['pricing']['line_total'];
            $count += $item['quantity'];
        }

        return array(
            'items'       => $items,
            'total'       => round( $total, 2 ),
            'item_count'  => $count,
            'total_lines' => count( $items ),
        );
    }

    /**
     * Clear all cart items for a user.
     */
    public static function clear( $user_id ) {
        global $wpdb;
        return $wpdb->delete(
            self::table(),
            array( 'user_id' => absint( $user_id ) ),
            array( '%d' )
        );
    }

    /**
     * Get cart item count for badge display.
     */
    public static function get_count( $user_id ) {
        global $wpdb;
        $table = self::table();
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE( SUM(quantity), 0 ) FROM {$table} WHERE user_id = %d",
            absint( $user_id )
        ) );
    }
}
