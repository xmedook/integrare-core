<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Synchronization logic between Integrare and Dolibarr ERP.
 */
class Integrare_Sync {

    /** @var Integrare_Dolibarr */
    private $dolibarr;

    public function __construct() {
        $this->dolibarr = new Integrare_Dolibarr();

        add_action( 'user_register',              array( $this, 'on_user_register' ) );
        add_action( 'integrare_order_created',    array( $this, 'on_order_created' ) );
        add_action( 'wp_ajax_integrare_sync_products',    array( $this, 'ajax_sync_products' ) );
        add_action( 'wp_ajax_integrare_test_dolibarr',    array( $this, 'ajax_test_connection' ) );
    }

    /**
     * Hook: user_register
     * Find or create the corresponding Dolibarr thirdparty and store socid in user_meta.
     *
     * @param int $user_id
     */
    public function on_user_register( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        // Try to find existing thirdparty by email
        $third = $this->dolibarr->get_thirdparty_by_email( $user->user_email );

        if ( is_wp_error( $third ) ) {
            // Log already handled inside get_thirdparty_by_email; bail silently
            return;
        }

        if ( $third ) {
            update_user_meta( $user_id, '_dolibarr_socid', intval( $third['id'] ) );
            return;
        }

        // Create new thirdparty
        $result = $this->dolibarr->create_thirdparty( array(
            'name'  => $user->display_name ?: $user->user_login,
            'email' => $user->user_email,
            'phone' => get_user_meta( $user_id, 'billing_phone', true ),
        ) );

        if ( is_wp_error( $result ) ) {
            return;
        }

        $socid = isset( $result['id'] ) ? intval( $result['id'] ) : intval( $result );
        if ( $socid > 0 ) {
            update_user_meta( $user_id, '_dolibarr_socid', $socid );
        }
    }

    /**
     * Hook: integrare_order_created
     * Build and validate a Dolibarr order; store the remote ID in order meta.
     *
     * @param int $order_id  Local WP order ID
     */
    public function on_order_created( $order_id ) {
        $order = Integrare_Orders::get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $user_id = intval( $order['user_id'] );

        // Ensure thirdparty exists
        $socid = intval( get_user_meta( $user_id, '_dolibarr_socid', true ) );

        if ( ! $socid ) {
            // Try to create it now
            $user  = get_userdata( $user_id );
            if ( $user ) {
                $third = $this->dolibarr->get_thirdparty_by_email( $user->user_email );
                if ( ! is_wp_error( $third ) && $third ) {
                    $socid = intval( $third['id'] );
                    update_user_meta( $user_id, '_dolibarr_socid', $socid );
                } else {
                    $result = $this->dolibarr->create_thirdparty( array(
                        'name'  => $user->display_name ?: $user->user_login,
                        'email' => $user->user_email,
                        'phone' => get_user_meta( $user_id, 'billing_phone', true ),
                    ) );
                    if ( ! is_wp_error( $result ) ) {
                        $socid = isset( $result['id'] ) ? intval( $result['id'] ) : intval( $result );
                        if ( $socid > 0 ) {
                            update_user_meta( $user_id, '_dolibarr_socid', $socid );
                        }
                    }
                }
            }
        }

        if ( ! $socid ) {
            $this->dolibarr->log_error(
                'on_order_created',
                "No se pudo obtener socid para user_id={$user_id}, order_id={$order_id}"
            );
            return;
        }

        // Build order lines
        $lines = array();
        foreach ( $order['items'] as $item ) {
            $dol_product_id = intval( get_post_meta( intval( $item['product_id'] ), '_dolibarr_product_id', true ) );

            $line = array(
                'qty'        => floatval( $item['quantity_boxes'] ),
                'subprice'   => floatval( $item['unit_price'] ),
                'product_id' => $dol_product_id ?: null,
                'desc'       => sanitize_text_field( $item['product_name'] ?? '' ),
            );

            if ( ! $line['product_id'] ) {
                unset( $line['product_id'] );
            }

            $lines[] = $line;
        }

        $dol_order = $this->dolibarr->create_order( array(
            'socid' => $socid,
            'lines' => $lines,
        ) );

        if ( is_wp_error( $dol_order ) ) {
            return;
        }

        $dol_order_id = isset( $dol_order['id'] ) ? intval( $dol_order['id'] ) : intval( $dol_order );

        if ( $dol_order_id > 0 ) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'integrare_orders',
                array( 'dolibarr_order_id' => $dol_order_id ),
                array( 'id' => absint( $order_id ) ),
                array( '%d' ),
                array( '%d' )
            );

            // Validate/confirm the order
            $this->dolibarr->validate_order( $dol_order_id );
        }
    }

    /**
     * Iterate farmacia_product posts and create/update each one in Dolibarr.
     * Stores _dolibarr_product_id in post_meta.
     *
     * @return array  Results summary
     */
    public function sync_products() {
        $posts = get_posts( array(
            'post_type'      => 'farmacia_product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) );

        $created = 0;
        $updated = 0;
        $errors  = 0;

        foreach ( $posts as $post_id ) {
            $dol_id = intval( get_post_meta( $post_id, '_dolibarr_product_id', true ) );
            $title  = get_the_title( $post_id );
            $price  = floatval( get_post_meta( $post_id, '_integrare_price', true ) );
            $stock  = floatval( get_post_meta( $post_id, '_integrare_stock', true ) );
            $sku    = get_post_meta( $post_id, '_integrare_sku', true );
            $ref    = $sku ?: 'WP-' . $post_id;

            $data = array(
                'ref'        => $ref,
                'label'      => $title,
                'price'      => $price,
                'stock_reel' => $stock,
            );

            if ( $dol_id ) {
                $result = $this->dolibarr->update_product( $dol_id, $data );
                if ( is_wp_error( $result ) ) {
                    $errors++;
                } else {
                    $updated++;
                }
            } else {
                $result = $this->dolibarr->create_product( $data );
                if ( is_wp_error( $result ) ) {
                    $errors++;
                } else {
                    $new_id = isset( $result['id'] ) ? intval( $result['id'] ) : intval( $result );
                    if ( $new_id > 0 ) {
                        update_post_meta( $post_id, '_dolibarr_product_id', $new_id );
                        $created++;
                    }
                }
            }
        }

        return array(
            'total'   => count( $posts ),
            'created' => $created,
            'updated' => $updated,
            'errors'  => $errors,
        );
    }

    /**
     * AJAX handler: sync all products to Dolibarr.
     * Action: wp_ajax_integrare_sync_products
     */
    public function ajax_sync_products() {
        check_ajax_referer( 'integrare_dolibarr_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Sin permisos.' ), 403 );
        }

        $result = $this->sync_products();

        wp_send_json_success( array(
            'message' => sprintf(
                'Sincronizacion completada. Creados: %d, Actualizados: %d, Errores: %d (de %d productos).',
                $result['created'],
                $result['updated'],
                $result['errors'],
                $result['total']
            ),
        ) );
    }

    /**
     * AJAX handler: test Dolibarr connection.
     * Action: wp_ajax_integrare_test_dolibarr
     */
    public function ajax_test_connection() {
        check_ajax_referer( 'integrare_dolibarr_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Sin permisos.' ), 403 );
        }

        $result = $this->dolibarr->request( 'GET', '/status' );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        $version = isset( $result['success'] ) ? $result['success'] : ( $result['dolibarr_version'] ?? json_encode( $result ) );

        wp_send_json_success( array(
            'message' => 'Conexion exitosa. Dolibarr: ' . esc_html( $version ),
        ) );
    }
}
