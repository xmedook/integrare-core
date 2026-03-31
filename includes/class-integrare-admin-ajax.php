<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin AJAX endpoints for the frontend dashboard.
 * All handlers require manage_options capability.
 */
class Integrare_Admin_Ajax {

    public function __construct() {
        // Dashboard stats
        add_action( 'wp_ajax_integrare_admin_get_stats', array( $this, 'get_stats' ) );

        // Products CRUD
        add_action( 'wp_ajax_integrare_admin_get_products', array( $this, 'get_products' ) );
        add_action( 'wp_ajax_integrare_admin_create_product', array( $this, 'create_product' ) );
        add_action( 'wp_ajax_integrare_admin_update_product', array( $this, 'update_product' ) );
        add_action( 'wp_ajax_integrare_admin_delete_product', array( $this, 'delete_product' ) );
        add_action( 'wp_ajax_integrare_admin_upload_image', array( $this, 'upload_image' ) );

        // Orders CRUD
        add_action( 'wp_ajax_integrare_admin_get_orders', array( $this, 'get_orders' ) );
        add_action( 'wp_ajax_integrare_admin_get_order', array( $this, 'get_order' ) );
        add_action( 'wp_ajax_integrare_admin_update_order_status', array( $this, 'update_order_status' ) );
        add_action( 'wp_ajax_integrare_admin_update_order', array( $this, 'update_order' ) );
        add_action( 'wp_ajax_integrare_admin_delete_order', array( $this, 'delete_order' ) );

        // Users
        add_action( 'wp_ajax_integrare_admin_get_users', array( $this, 'get_users' ) );

        // Categories CRUD
        add_action( 'wp_ajax_integrare_admin_get_categories', array( $this, 'get_categories' ) );
        add_action( 'wp_ajax_integrare_admin_create_category', array( $this, 'create_category' ) );
        add_action( 'wp_ajax_integrare_admin_update_category', array( $this, 'update_category' ) );
        add_action( 'wp_ajax_integrare_admin_delete_category', array( $this, 'delete_category' ) );
    }

    /**
     * Verify admin permissions.
     */
    private function verify_admin() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Acceso denegado.' ) );
        }
    }

    /* ═══════════════════════════════════════════════════════
       DASHBOARD STATS
       ═══════════════════════════════════════════════════════ */

    public function get_stats() {
        $this->verify_admin();
        global $wpdb;

        $orders_table = $wpdb->prefix . 'integrare_orders';

        // Products
        $total_products = wp_count_posts( 'farmacia_product' )->publish;

        // Low stock products (< 50 units)
        $low_stock = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta}
             WHERE meta_key = '_integrare_stock'
             AND CAST(meta_value AS SIGNED) < 50
             AND CAST(meta_value AS SIGNED) > 0
             AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'farmacia_product' AND post_status = 'publish')"
        );

        // Orders
        $total_orders   = Integrare_Orders::count_orders();
        $pending_orders = Integrare_Orders::count_orders( 'pending' );
        $paid_orders    = Integrare_Orders::count_orders( 'paid' );
        $completed_orders = Integrare_Orders::count_orders( 'completed' );

        // Revenue (paid + completed)
        $total_revenue = $wpdb->get_var(
            "SELECT COALESCE(SUM(total), 0) FROM {$orders_table} WHERE status IN ('paid', 'completed')"
        );

        // Revenue this month
        $month_revenue = $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE(SUM(total), 0) FROM {$orders_table}
             WHERE status IN ('paid', 'completed')
             AND created_at >= %s",
            date( 'Y-m-01 00:00:00' )
        ) );

        // Orders today
        $orders_today = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$orders_table} WHERE created_at >= %s",
            date( 'Y-m-d 00:00:00' )
        ) );

        // Total clients (users who have placed orders)
        $total_clients = $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) FROM {$orders_table}"
        );

        // Recent orders (last 10)
        $recent_orders = $wpdb->get_results(
            "SELECT o.*, u.display_name, u.user_email
             FROM {$orders_table} o
             LEFT JOIN {$wpdb->users} u ON o.user_id = u.ID
             ORDER BY o.created_at DESC LIMIT 10",
            ARRAY_A
        );

        // Low stock products list
        $low_stock_products = $wpdb->get_results(
            "SELECT p.ID, p.post_title,
                    pm_stock.meta_value as stock,
                    pm_sku.meta_value as sku
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_integrare_stock'
             LEFT JOIN {$wpdb->postmeta} pm_sku ON p.ID = pm_sku.post_id AND pm_sku.meta_key = '_integrare_sku'
             WHERE p.post_type = 'farmacia_product'
             AND p.post_status = 'publish'
             AND CAST(pm_stock.meta_value AS SIGNED) < 50
             AND CAST(pm_stock.meta_value AS SIGNED) > 0
             ORDER BY CAST(pm_stock.meta_value AS SIGNED) ASC
             LIMIT 10",
            ARRAY_A
        );

        wp_send_json_success( array(
            'total_products'    => (int) $total_products,
            'total_orders'      => (int) $total_orders,
            'pending_orders'    => (int) $pending_orders,
            'paid_orders'       => (int) $paid_orders,
            'completed_orders'  => (int) $completed_orders,
            'total_revenue'     => (float) $total_revenue,
            'month_revenue'     => (float) $month_revenue,
            'orders_today'      => (int) $orders_today,
            'total_clients'     => (int) $total_clients,
            'low_stock_count'   => (int) $low_stock,
            'recent_orders'     => $recent_orders,
            'low_stock_products' => $low_stock_products,
        ) );
    }

    /* ═══════════════════════════════════════════════════════
       PRODUCTS CRUD
       ═══════════════════════════════════════════════════════ */

    public function get_products() {
        $this->verify_admin();

        $paged    = max( 1, absint( $_POST['page'] ?? 1 ) );
        $per_page = 15;
        $search   = sanitize_text_field( $_POST['search'] ?? '' );
        $cat_slug = sanitize_text_field( $_POST['category'] ?? '' );

        $args = array(
            'post_type'      => 'farmacia_product',
            'post_status'    => array( 'publish', 'draft' ),
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if ( $search ) {
            $args['s'] = $search;
        }

        if ( $cat_slug ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'producto_categoria',
                    'field'    => 'slug',
                    'terms'    => $cat_slug,
                ),
            );
        }

        $query = new WP_Query( $args );

        $products = array();
        foreach ( $query->posts as $p ) {
            $cats = wp_get_post_terms( $p->ID, 'producto_categoria', array( 'fields' => 'all' ) );
            $cat_data = array();
            foreach ( $cats as $cat ) {
                $cat_data[] = array( 'id' => $cat->term_id, 'name' => $cat->name, 'slug' => $cat->slug );
            }

            $products[] = array(
                'id'          => $p->ID,
                'title'       => $p->post_title,
                'slug'        => $p->post_name,
                'status'      => $p->post_status,
                'excerpt'     => $p->post_excerpt,
                'thumbnail'    => get_the_post_thumbnail_url( $p->ID, 'thumbnail' ) ?: '',
                'thumbnail_id' => (int) get_post_thumbnail_id( $p->ID ),
                'sku'         => get_post_meta( $p->ID, '_integrare_sku', true ),
                'price'       => (float) get_post_meta( $p->ID, '_integrare_precio_unitario', true ),
                'upb'         => (int) get_post_meta( $p->ID, '_integrare_unidades_caja', true ),
                'stock'       => (int) get_post_meta( $p->ID, '_integrare_stock', true ),
                'categories'  => $cat_data,
                'date'        => get_the_date( 'd/m/Y', $p->ID ),
                'discounts'   => get_post_meta( $p->ID, '_integrare_descuentos', true ) ?: array(),
                'show_price'  => (bool) get_post_meta( $p->ID, '_integrare_mostrar_precio', true ),
            );
        }

        wp_send_json_success( array(
            'products'    => $products,
            'total'       => $query->found_posts,
            'total_pages' => $query->max_num_pages,
            'current_page' => $paged,
        ) );
    }

    public function create_product() {
        $this->verify_admin();

        $title   = sanitize_text_field( $_POST['title'] ?? '' );
        $excerpt = sanitize_textarea_field( $_POST['excerpt'] ?? '' );
        $content = wp_kses_post( $_POST['content'] ?? '' );
        $status  = in_array( $_POST['status'] ?? '', array( 'publish', 'draft' ) ) ? $_POST['status'] : 'publish';

        if ( empty( $title ) ) {
            wp_send_json_error( array( 'message' => 'El nombre del producto es obligatorio.' ) );
        }

        $post_id = wp_insert_post( array(
            'post_type'    => 'farmacia_product',
            'post_title'   => $title,
            'post_excerpt' => $excerpt,
            'post_content' => $content,
            'post_status'  => $status,
        ) );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
        }

        // Save meta
        $this->save_product_meta( $post_id );

        // Category
        $cat_id = absint( $_POST['category_id'] ?? 0 );
        if ( $cat_id ) {
            wp_set_object_terms( $post_id, $cat_id, 'producto_categoria' );
        }

        // Thumbnail
        $thumb_id = absint( $_POST['thumbnail_id'] ?? 0 );
        if ( $thumb_id ) {
            set_post_thumbnail( $post_id, $thumb_id );
        }

        wp_send_json_success( array(
            'message'    => 'Producto creado exitosamente.',
            'product_id' => $post_id,
        ) );
    }

    public function update_product() {
        $this->verify_admin();

        $product_id = absint( $_POST['product_id'] ?? 0 );
        if ( ! $product_id || get_post_type( $product_id ) !== 'farmacia_product' ) {
            wp_send_json_error( array( 'message' => 'Producto no válido.' ) );
        }

        $title   = sanitize_text_field( $_POST['title'] ?? '' );
        $excerpt = sanitize_textarea_field( $_POST['excerpt'] ?? '' );
        $content = wp_kses_post( $_POST['content'] ?? '' );
        $status  = in_array( $_POST['status'] ?? '', array( 'publish', 'draft' ) ) ? $_POST['status'] : 'publish';

        if ( empty( $title ) ) {
            wp_send_json_error( array( 'message' => 'El nombre del producto es obligatorio.' ) );
        }

        wp_update_post( array(
            'ID'           => $product_id,
            'post_title'   => $title,
            'post_excerpt' => $excerpt,
            'post_content' => $content,
            'post_status'  => $status,
        ) );

        // Save meta
        $this->save_product_meta( $product_id );

        // Category
        $cat_id = absint( $_POST['category_id'] ?? 0 );
        if ( $cat_id ) {
            wp_set_object_terms( $product_id, $cat_id, 'producto_categoria' );
        } else {
            wp_set_object_terms( $product_id, array(), 'producto_categoria' );
        }

        // Thumbnail
        if ( isset( $_POST['thumbnail_id'] ) ) {
            $thumb_id = absint( $_POST['thumbnail_id'] );
            if ( $thumb_id ) {
                set_post_thumbnail( $product_id, $thumb_id );
            } else {
                delete_post_thumbnail( $product_id );
            }
        }

        wp_send_json_success( array( 'message' => 'Producto actualizado.' ) );
    }

    /**
     * Save product meta fields.
     */
    private function save_product_meta( $post_id ) {
        if ( isset( $_POST['sku'] ) ) {
            update_post_meta( $post_id, '_integrare_sku', sanitize_text_field( $_POST['sku'] ) );
        }
        if ( isset( $_POST['price'] ) ) {
            update_post_meta( $post_id, '_integrare_precio_unitario', floatval( $_POST['price'] ) );
        }
        if ( isset( $_POST['upb'] ) ) {
            update_post_meta( $post_id, '_integrare_unidades_caja', absint( $_POST['upb'] ) );
        }
        if ( isset( $_POST['stock'] ) ) {
            update_post_meta( $post_id, '_integrare_stock', absint( $_POST['stock'] ) );
        }

        // Discount tiers
        if ( isset( $_POST['discount_tiers'] ) ) {
            $tiers_raw = json_decode( stripslashes( $_POST['discount_tiers'] ), true );
            if ( is_array( $tiers_raw ) ) {
                $tiers = array();
                foreach ( $tiers_raw as $tier ) {
                    $tiers[] = array(
                        'min_cajas'     => absint( $tier['min_cajas'] ?? 1 ),
                        'descuento_pct' => floatval( $tier['descuento_pct'] ?? 0 ),
                    );
                }
                usort( $tiers, function( $a, $b ) {
                    return $a['min_cajas'] - $b['min_cajas'];
                });
                update_post_meta( $post_id, '_integrare_descuentos', $tiers );
            }
        }

        // Show price to non-logged-in users
        if ( isset( $_POST['show_price'] ) ) {
            update_post_meta( $post_id, '_integrare_mostrar_precio', $_POST['show_price'] === '1' ? '1' : '' );
        }
    }

    public function delete_product() {
        $this->verify_admin();

        $product_id = absint( $_POST['product_id'] ?? 0 );
        if ( ! $product_id || get_post_type( $product_id ) !== 'farmacia_product' ) {
            wp_send_json_error( array( 'message' => 'Producto no válido.' ) );
        }

        wp_trash_post( $product_id );

        wp_send_json_success( array( 'message' => 'Producto eliminado.' ) );
    }

    /* ═══════════════════════════════════════════════════════
       ORDERS
       ═══════════════════════════════════════════════════════ */

    public function get_orders() {
        $this->verify_admin();

        $status = sanitize_text_field( $_POST['status'] ?? '' );
        $paged  = max( 1, absint( $_POST['page'] ?? 1 ) );
        $limit  = 15;
        $offset = ( $paged - 1 ) * $limit;

        $orders = Integrare_Orders::get_all_orders( $status, $limit, $offset );
        $total  = Integrare_Orders::count_orders( $status );

        wp_send_json_success( array(
            'orders'      => $orders,
            'total'       => $total,
            'total_pages' => ceil( $total / $limit ),
            'current_page' => $paged,
            'counts'      => array(
                'all'       => Integrare_Orders::count_orders(),
                'pending'   => Integrare_Orders::count_orders( 'pending' ),
                'paid'      => Integrare_Orders::count_orders( 'paid' ),
                'completed' => Integrare_Orders::count_orders( 'completed' ),
                'cancelled' => Integrare_Orders::count_orders( 'cancelled' ),
            ),
        ) );
    }

    public function get_order() {
        $this->verify_admin();

        $order_id = absint( $_POST['order_id'] ?? 0 );
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => 'Pedido no válido.' ) );
        }

        $order = Integrare_Orders::get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => 'Pedido no encontrado.' ) );
        }

        // Add user info
        $user = get_user_by( 'ID', $order['user_id'] );
        $order['user_name']  = $user ? $user->display_name : 'N/A';
        $order['user_email'] = $user ? $user->user_email : '';
        $order['user_phone'] = $user ? get_user_meta( $user->ID, 'billing_phone', true ) : '';

        wp_send_json_success( array( 'order' => $order ) );
    }

    public function update_order_status() {
        $this->verify_admin();

        $order_id = absint( $_POST['order_id'] ?? 0 );
        $status   = sanitize_text_field( $_POST['status'] ?? '' );

        if ( ! $order_id || ! $status ) {
            wp_send_json_error( array( 'message' => 'Datos incompletos.' ) );
        }

        $result = Integrare_Orders::update_status( $order_id, $status );

        if ( $result === false ) {
            wp_send_json_error( array( 'message' => 'Estado no válido.' ) );
        }

        wp_send_json_success( array( 'message' => 'Estado actualizado.' ) );
    }

    public function update_order() {
        $this->verify_admin();

        $order_id = absint( $_POST['order_id'] ?? 0 );
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => 'Pedido no válido.' ) );
        }

        $data = array();

        if ( isset( $_POST['status'] ) ) {
            $data['status'] = sanitize_text_field( $_POST['status'] );
        }
        if ( isset( $_POST['notes'] ) ) {
            $data['notes'] = sanitize_textarea_field( $_POST['notes'] );
        }
        if ( isset( $_POST['payment_method'] ) ) {
            $data['payment_method'] = sanitize_text_field( $_POST['payment_method'] );
        }

        // Shipping fields
        $shipping_keys = array( 'shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_state', 'shipping_zip' );
        foreach ( $shipping_keys as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $data[ $key ] = sanitize_text_field( $_POST[ $key ] );
            }
        }

        $result = Integrare_Orders::update_order( $order_id, $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => 'Pedido actualizado.' ) );
    }

    public function delete_order() {
        $this->verify_admin();

        $order_id = absint( $_POST['order_id'] ?? 0 );
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => 'Pedido no válido.' ) );
        }

        $result = Integrare_Orders::delete_order( $order_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => 'Pedido eliminado.' ) );
    }

    /* ═══════════════════════════════════════════════════════
       USERS / CLIENTS
       ═══════════════════════════════════════════════════════ */

    public function get_users() {
        $this->verify_admin();
        global $wpdb;

        $search = sanitize_text_field( $_POST['search'] ?? '' );
        $paged  = max( 1, absint( $_POST['page'] ?? 1 ) );
        $limit  = 15;
        $offset = ( $paged - 1 ) * $limit;

        $orders_table = $wpdb->prefix . 'integrare_orders';

        $where = '';
        $params = array();

        if ( $search ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            $where = "WHERE (u.display_name LIKE %s OR u.user_email LIKE %s)";
            $params[] = $like;
            $params[] = $like;
        }

        // Count total
        $count_sql = "SELECT COUNT(DISTINCT u.ID)
                      FROM {$wpdb->users} u
                      LEFT JOIN {$orders_table} o ON u.ID = o.user_id
                      {$where}";
        $total = $search
            ? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$params ) )
            : (int) $wpdb->get_var( $count_sql );

        // Get users
        $params_full = $params;
        $params_full[] = $limit;
        $params_full[] = $offset;

        $sql = "SELECT u.ID, u.display_name, u.user_email, u.user_registered,
                       COUNT(o.id) as total_orders,
                       COALESCE(SUM(o.total), 0) as total_spent
                FROM {$wpdb->users} u
                LEFT JOIN {$orders_table} o ON u.ID = o.user_id
                {$where}
                GROUP BY u.ID
                ORDER BY total_orders DESC, u.user_registered DESC
                LIMIT %d OFFSET %d";

        $users = $search
            ? $wpdb->get_results( $wpdb->prepare( $sql, ...$params_full ), ARRAY_A )
            : $wpdb->get_results( $wpdb->prepare(
                str_replace( $where, '', $sql ),
                $limit, $offset
              ), ARRAY_A );

        // Add phone to each user
        foreach ( $users as &$u ) {
            $u['phone'] = get_user_meta( $u['ID'], 'billing_phone', true );
            $u['role']  = implode( ', ', get_userdata( $u['ID'] )->roles ?? array() );
        }

        wp_send_json_success( array(
            'users'       => $users,
            'total'       => $total,
            'total_pages' => ceil( $total / $limit ),
            'current_page' => $paged,
        ) );
    }

    /* ═══════════════════════════════════════════════════════
       CATEGORIES CRUD
       ═══════════════════════════════════════════════════════ */

    public function get_categories() {
        $this->verify_admin();

        $terms = get_terms( array(
            'taxonomy'   => 'producto_categoria',
            'hide_empty' => false,
        ) );

        if ( is_wp_error( $terms ) ) {
            wp_send_json_error( array( 'message' => 'Error al obtener categorías.' ) );
        }

        $cats = array();
        foreach ( $terms as $t ) {
            $cats[] = array(
                'id'    => $t->term_id,
                'name'  => $t->name,
                'slug'  => $t->slug,
                'count' => $t->count,
            );
        }

        wp_send_json_success( array( 'categories' => $cats ) );
    }

    public function create_category() {
        $this->verify_admin();

        $name = sanitize_text_field( $_POST['name'] ?? '' );
        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => 'El nombre es obligatorio.' ) );
        }

        $result = wp_insert_term( $name, 'producto_categoria' );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => 'Categoría creada.',
            'term_id' => $result['term_id'],
        ) );
    }

    public function update_category() {
        $this->verify_admin();

        $term_id = absint( $_POST['term_id'] ?? 0 );
        $name    = sanitize_text_field( $_POST['name'] ?? '' );

        if ( ! $term_id || empty( $name ) ) {
            wp_send_json_error( array( 'message' => 'Datos incompletos.' ) );
        }

        $result = wp_update_term( $term_id, 'producto_categoria', array( 'name' => $name ) );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => 'Categoría actualizada.' ) );
    }

    public function delete_category() {
        $this->verify_admin();

        $term_id = absint( $_POST['term_id'] ?? 0 );
        if ( ! $term_id ) {
            wp_send_json_error( array( 'message' => 'Categoría no válida.' ) );
        }

        $result = wp_delete_term( $term_id, 'producto_categoria' );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => 'Categoría eliminada.' ) );
    }

    /* ═══════════════════════════════════════════════════════
       IMAGE UPLOAD
       ═══════════════════════════════════════════════════════ */

    public function upload_image() {
        $this->verify_admin();

        if ( empty( $_FILES['image'] ) ) {
            wp_send_json_error( array( 'message' => 'No se envió ninguna imagen.' ) );
        }

        // Require the WordPress file handling functions
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $file = $_FILES['image'];

        // Validate file type
        $allowed = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
        if ( ! in_array( $file['type'], $allowed, true ) ) {
            wp_send_json_error( array( 'message' => 'Formato de imagen no soportado. Usa JPG, PNG, GIF o WebP.' ) );
        }

        // Use wp_handle_upload
        $upload = wp_handle_upload( $file, array( 'test_form' => false ) );

        if ( isset( $upload['error'] ) ) {
            wp_send_json_error( array( 'message' => $upload['error'] ) );
        }

        // Create attachment post
        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name( pathinfo( $upload['file'], PATHINFO_FILENAME ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment( $attachment, $upload['file'] );

        if ( is_wp_error( $attach_id ) ) {
            wp_send_json_error( array( 'message' => 'Error al guardar la imagen.' ) );
        }

        // Generate metadata (thumbnails, sizes, etc.)
        $metadata = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
        wp_update_attachment_metadata( $attach_id, $metadata );

        wp_send_json_success( array(
            'attachment_id' => $attach_id,
            'url'           => $upload['url'],
        ) );
    }
}
