<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Orders {

    private static function orders_table() {
        global $wpdb;
        return $wpdb->prefix . 'integrare_orders';
    }

    private static function items_table() {
        global $wpdb;
        return $wpdb->prefix . 'integrare_order_items';
    }

    /**
     * Validate stock for all cart items before placing order.
     *
     * @return true|WP_Error
     */
    public static function validate_stock( $cart_items ) {
        foreach ( $cart_items as $item ) {
            $p = $item['pricing'];
            if ( ! $p['stock_sufficient'] ) {
                return new WP_Error(
                    'insufficient_stock',
                    sprintf(
                        'Stock insuficiente para "%s". Disponible: %d unidades, Requerido: %d unidades.',
                        $item['title'],
                        $p['stock'],
                        $p['total_units']
                    ),
                    array( 'product_id' => $item['product_id'] )
                );
            }
        }
        return true;
    }

    /**
     * Create an order from the current cart.
     *
     * @param int    $user_id
     * @param string $notes
     * @return int|WP_Error  Order ID or error.
     */
    public static function create_order( $user_id, $notes = '' ) {
        global $wpdb;

        $user_id = absint( $user_id );
        $cart    = Integrare_Cart::get_totals( $user_id );

        if ( empty( $cart['items'] ) ) {
            return new WP_Error( 'empty_cart', 'El carrito está vacío.' );
        }

        // Validate stock
        $valid = self::validate_stock( $cart['items'] );
        if ( is_wp_error( $valid ) ) {
            return $valid;
        }

        // Start transaction
        $wpdb->query( 'START TRANSACTION' );

        try {
            // Insert order
            $inserted = $wpdb->insert( self::orders_table(), array(
                'user_id'    => $user_id,
                'total'      => $cart['total'],
                'status'     => 'pending',
                'notes'      => sanitize_textarea_field( $notes ),
                'created_at' => current_time( 'mysql' ),
            ), array( '%d', '%f', '%s', '%s', '%s' ) );

            if ( ! $inserted ) {
                throw new Exception( 'No se pudo crear el pedido.' );
            }

            $order_id = $wpdb->insert_id;

            // Insert order items + deduct inventory
            foreach ( $cart['items'] as $item ) {
                $p = $item['pricing'];

                // Insert line item
                $wpdb->insert( self::items_table(), array(
                    'order_id'       => $order_id,
                    'product_id'     => $item['product_id'],
                    'quantity_boxes' => $item['quantity'],
                    'units_per_box'  => $p['units_per_box'],
                    'unit_price'     => $p['discounted_price'],
                    'discount_pct'   => $p['discount_pct'],
                    'line_total'     => $p['line_total'],
                ), array( '%d', '%d', '%d', '%d', '%f', '%f', '%f' ) );

                // Atomic inventory deduction
                $deducted = self::deduct_inventory( $item['product_id'], $p['total_units'] );
                if ( ! $deducted ) {
                    throw new Exception(
                        sprintf( 'No se pudo descontar inventario para "%s".', $item['title'] )
                    );
                }
            }

            // Clear cart
            Integrare_Cart::clear( $user_id );

            $wpdb->query( 'COMMIT' );

            return $order_id;

        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );
            return new WP_Error( 'order_failed', $e->getMessage() );
        }
    }

    /**
     * Atomic inventory deduction.
     * Uses WHERE stock >= units to prevent negative stock.
     */
    private static function deduct_inventory( $product_id, $units_to_deduct ) {
        global $wpdb;

        $current_stock = (int) get_post_meta( $product_id, '_integrare_stock', true );

        if ( $current_stock < $units_to_deduct ) {
            return false;
        }

        // Use direct SQL for atomicity
        $result = $wpdb->query( $wpdb->prepare(
            "UPDATE {$wpdb->postmeta}
             SET meta_value = CAST(meta_value AS SIGNED) - %d
             WHERE post_id = %d
             AND meta_key = '_integrare_stock'
             AND CAST(meta_value AS SIGNED) >= %d",
            $units_to_deduct,
            $product_id,
            $units_to_deduct
        ) );

        return $result > 0;
    }

    /**
     * Get orders for a user.
     */
    public static function get_user_orders( $user_id, $limit = 20, $offset = 0 ) {
        global $wpdb;
        $table = self::orders_table();

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
            absint( $user_id ), $limit, $offset
        ), ARRAY_A );
    }

    /**
     * Get a single order with its items.
     */
    public static function get_order( $order_id ) {
        global $wpdb;

        $order = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::orders_table() . " WHERE id = %d",
            absint( $order_id )
        ), ARRAY_A );

        if ( ! $order ) return null;

        $order['items'] = $wpdb->get_results( $wpdb->prepare(
            "SELECT oi.*, p.post_title as product_name
             FROM " . self::items_table() . " oi
             LEFT JOIN {$wpdb->posts} p ON oi.product_id = p.ID
             WHERE oi.order_id = %d",
            $order_id
        ), ARRAY_A );

        return $order;
    }

    /**
     * Update order status.
     */
    public static function update_status( $order_id, $status ) {
        global $wpdb;
        $allowed = array( 'pending', 'paid', 'completed', 'cancelled' );
        if ( ! in_array( $status, $allowed ) ) return false;

        return $wpdb->update(
            self::orders_table(),
            array( 'status' => $status ),
            array( 'id' => absint( $order_id ) ),
            array( '%s' ),
            array( '%d' )
        );
    }

    /**
     * Update order editable fields (status, notes, payment_method).
     *
     * @param int   $order_id
     * @param array $data  Associative array of fields to update.
     * @return bool|WP_Error
     */
    public static function update_order( $order_id, $data ) {
        global $wpdb;
        $order_id = absint( $order_id );

        $order = self::get_order( $order_id );
        if ( ! $order ) {
            return new WP_Error( 'not_found', 'Pedido no encontrado.' );
        }

        $update = array();
        $format = array();

        if ( isset( $data['status'] ) ) {
            $allowed = array( 'pending', 'paid', 'completed', 'cancelled' );
            if ( ! in_array( $data['status'], $allowed ) ) {
                return new WP_Error( 'invalid_status', 'Estado no válido.' );
            }
            $update['status'] = $data['status'];
            $format[] = '%s';
        }

        if ( isset( $data['notes'] ) ) {
            $update['notes'] = sanitize_textarea_field( $data['notes'] );
            $format[] = '%s';
        }

        if ( isset( $data['payment_method'] ) ) {
            $update['payment_method'] = sanitize_text_field( $data['payment_method'] );
            $format[] = '%s';
        }

        // Shipping fields
        $shipping_fields = array( 'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_state', 'shipping_zip' );
        foreach ( $shipping_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $update[ $field ] = sanitize_text_field( $data[ $field ] );
                $format[] = '%s';
            }
        }

        if ( empty( $update ) ) {
            return new WP_Error( 'no_data', 'No hay datos para actualizar.' );
        }

        $result = $wpdb->update(
            self::orders_table(),
            $update,
            array( 'id' => $order_id ),
            $format,
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Delete an order and its items.
     *
     * @param int $order_id
     * @return bool|WP_Error
     */
    public static function delete_order( $order_id ) {
        global $wpdb;
        $order_id = absint( $order_id );

        $order = self::get_order( $order_id );
        if ( ! $order ) {
            return new WP_Error( 'not_found', 'Pedido no encontrado.' );
        }

        $wpdb->query( 'START TRANSACTION' );

        try {
            // Delete order items first
            $wpdb->delete( self::items_table(), array( 'order_id' => $order_id ), array( '%d' ) );

            // Delete the order
            $deleted = $wpdb->delete( self::orders_table(), array( 'id' => $order_id ), array( '%d' ) );

            if ( ! $deleted ) {
                throw new Exception( 'No se pudo eliminar el pedido.' );
            }

            $wpdb->query( 'COMMIT' );
            return true;

        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );
            return new WP_Error( 'delete_failed', $e->getMessage() );
        }
    }

    /**
     * Get all orders (admin).
     */
    public static function get_all_orders( $status = '', $limit = 20, $offset = 0 ) {
        global $wpdb;
        $table = self::orders_table();

        $where = '';
        $params = array();
        if ( $status ) {
            $where = 'WHERE status = %s';
            $params[] = $status;
        }

        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT o.*, u.display_name, u.user_email
             FROM {$table} o
             LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID
             {$where}
             ORDER BY o.created_at DESC
             LIMIT %d OFFSET %d",
            ...$params
        ), ARRAY_A );
    }

    /**
     * Count orders (for admin pagination).
     */
    public static function count_orders( $status = '' ) {
        global $wpdb;
        $table = self::orders_table();

        if ( $status ) {
            return (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE status = %s", $status
            ) );
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    }
}
