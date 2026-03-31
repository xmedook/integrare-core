<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Front {

    public function __construct() {
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'handle_route' ) );
    }

    /**
     * Register rewrite rules for /tienda sub-routes.
     */
    public static function add_rewrite_rules() {
        add_rewrite_rule(
            '^tienda/producto/([^/]+)/?$',
            'index.php?integrare_page=producto&integrare_slug=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^tienda/carrito/?$',
            'index.php?integrare_page=carrito',
            'top'
        );
        add_rewrite_rule(
            '^tienda/checkout/?$',
            'index.php?integrare_page=checkout',
            'top'
        );
        add_rewrite_rule(
            '^tienda/cuenta/?$',
            'index.php?integrare_page=cuenta',
            'top'
        );
        add_rewrite_rule(
            '^tienda/auth/?$',
            'index.php?integrare_page=auth',
            'top'
        );

        add_rewrite_rule(
            '^tienda/admin/?$',
            'index.php?integrare_page=admin',
            'top'
        );
        add_rewrite_rule(
            '^tienda/?$',
            'index.php?integrare_page=catalogo',
            'top'
        );
    }

    /**
     * Register custom query vars.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'integrare_page';
        $vars[] = 'integrare_slug';
        return $vars;
    }

    /**
     * Intercept the route and render standalone page.
     */
    public function handle_route() {
        $page = get_query_var( 'integrare_page' );

        if ( ! $page ) return;

        // Determine which template to load
        $template = '';
        $data     = array();

        switch ( $page ) {
            case 'catalogo':
                $template = 'catalog.php';
                $data     = $this->get_catalog_data();
                break;

            case 'producto':
                $slug = get_query_var( 'integrare_slug' );
                $template = 'single-product.php';
                $data     = $this->get_product_data( $slug );
                if ( ! $data ) {
                    status_header( 404 );
                    $template = 'catalog.php';
                    $data     = $this->get_catalog_data();
                }
                break;

            case 'carrito':
                $template = 'cart.php';
                $data     = $this->get_cart_data();
                break;

            case 'checkout':
                $template = 'checkout.php';
                $layout   = 'layout-checkout.php';
                $data     = $this->get_cart_data();
                break;

            case 'cuenta':
                $template = 'account.php';
                $data     = $this->get_account_data();
                break;

            case 'auth':
                // Redirect to catalog if already logged in
                if ( is_user_logged_in() ) {
                    wp_redirect( home_url( '/tienda/' ) );
                    exit;
                }
                $template = 'auth.php';
                $layout   = 'layout-auth.php';
                $data     = array();
                break;



            case 'admin':
                // Only administrators can access
                if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
                    wp_redirect( home_url( '/tienda/' ) );
                    exit;
                }
                $template = 'admin-dashboard.php';
                $layout   = 'layout-admin.php';
                $data     = array();
                break;

            default:
                return;
        }

        // Common data
        $data['is_logged_in'] = is_user_logged_in();
        $data['current_page'] = $page;
        $data['cart_count']   = $data['is_logged_in']
            ? Integrare_Cart::get_count( get_current_user_id() )
            : 0;
        $data['login_url']    = home_url( '/tienda/auth/' );
        $data['ajax_url']     = admin_url( 'admin-ajax.php' );
        $data['nonce']        = wp_create_nonce( 'integrare_nonce' );
        $data['categories']   = $this->get_categories();

        // ── Prevent server-side caching on /tienda ───────────
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }
        if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
            define( 'DONOTCACHEOBJECT', true );
        }
        nocache_headers();

        // ── Strip ALL theme & plugin assets ────────────────
        $this->isolate_frontend();

        // Render standalone page
        $this->render( $template, $data, $layout ?? 'layout.php' );
        exit;
    }

    /**
     * Strip all theme & plugin styles/scripts from wp_head/wp_footer.
     * Only the WP admin bar assets (for logged-in admins) are kept.
     */
    private function isolate_frontend() {
        // Remove all enqueued styles and scripts at the latest possible moment
        add_action( 'wp_enqueue_scripts', function() {
            global $wp_styles, $wp_scripts;

            // Whitelist: keep only jQuery
            $style_whitelist  = array();
            $script_whitelist = array( 'jquery', 'jquery-core', 'jquery-migrate' );

            // Dequeue all styles except whitelisted
            if ( isset( $wp_styles->queue ) ) {
                foreach ( $wp_styles->queue as $handle ) {
                    if ( ! in_array( $handle, $style_whitelist, true ) ) {
                        wp_dequeue_style( $handle );
                        wp_deregister_style( $handle );
                    }
                }
            }

            // Dequeue all scripts except whitelisted
            if ( isset( $wp_scripts->queue ) ) {
                foreach ( $wp_scripts->queue as $handle ) {
                    if ( ! in_array( $handle, $script_whitelist, true ) ) {
                        wp_dequeue_script( $handle );
                        wp_deregister_script( $handle );
                    }
                }
            }
        }, 9999 );

        // Remove common WP head clutter
        remove_action( 'wp_head', 'wp_generator' );
        remove_action( 'wp_head', 'wlwmanifest_link' );
        remove_action( 'wp_head', 'rsd_link' );
        remove_action( 'wp_head', 'wp_shortlink_wp_head' );
        remove_action( 'wp_head', 'rest_output_link_wp_head' );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_resource_hints', 2 );
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_action( 'wp_head', 'wp_block_library' );

        // Remove block library CSS
        add_action( 'wp_enqueue_scripts', function() {
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'wc-blocks-style' );
            wp_dequeue_style( 'global-styles' );
            wp_dequeue_style( 'classic-theme-styles' );
        }, 9998 );

        // Hide admin bar completely on /tienda
        show_admin_bar( false );
        add_filter( 'show_admin_bar', '__return_false' );
    }

    /**
     * Render the standalone page with layout.
     */
    private function render( $template_file, $data, $layout_file = 'layout.php' ) {
        // Extract data as variables for the template
        extract( $data );
        $template_path = INTEGRARE_PATH . 'templates/' . $template_file;
        $layout_path   = INTEGRARE_PATH . 'templates/' . $layout_file;

        // Buffer the template content
        ob_start();
        if ( file_exists( $template_path ) ) {
            include $template_path;
        }
        $content = ob_get_clean();

        // Render inside layout
        include $layout_path;
    }

    /**
     * Get catalog data (products list).
     */
    private function get_catalog_data() {
        $paged = max( 1, absint( $_GET['pag'] ?? 1 ) );
        $per_page = 12;
        $search   = sanitize_text_field( $_GET['buscar'] ?? '' );
        $cat_slug = sanitize_text_field( $_GET['categoria'] ?? '' );

        $args = array(
            'post_type'      => 'farmacia_product',
            'post_status'    => 'publish',
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
            $cats = wp_get_post_terms( $p->ID, 'producto_categoria', array( 'fields' => 'names' ) );
            $products[] = array(
                'id'        => $p->ID,
                'title'     => $p->post_title,
                'slug'      => $p->post_name,
                'excerpt'   => $p->post_excerpt,
                'thumbnail' => get_the_post_thumbnail_url( $p->ID, 'full' ) ?: '',
                'sku'       => get_post_meta( $p->ID, '_integrare_sku', true ),
                'price'     => (float) get_post_meta( $p->ID, '_integrare_precio_unitario', true ),
                'upb'       => (int) get_post_meta( $p->ID, '_integrare_unidades_caja', true ),
                'stock'     => (int) get_post_meta( $p->ID, '_integrare_stock', true ),
                'category'  => ! empty( $cats ) ? $cats[0] : '',
                'show_price' => (bool) get_post_meta( $p->ID, '_integrare_mostrar_precio', true ),
            );
        }

        return array(
            'products'     => $products,
            'total_pages'  => $query->max_num_pages,
            'current_page' => $paged,
            'total_found'  => $query->found_posts,
            'search'       => $search,
            'cat_filter'   => $cat_slug,
        );
    }

    /**
     * Get single product data by slug.
     */
    private function get_product_data( $slug ) {
        $posts = get_posts( array(
            'post_type'   => 'farmacia_product',
            'name'        => sanitize_title( $slug ),
            'numberposts' => 1,
            'post_status' => 'publish',
        ) );

        if ( empty( $posts ) ) return null;

        $p = $posts[0];

        return array(
            'product' => array(
                'id'           => $p->ID,
                'title'        => $p->post_title,
                'slug'         => $p->post_name,
                'description'  => apply_filters( 'the_content', $p->post_content ),
                'excerpt'      => $p->post_excerpt,
                'thumbnail'    => get_the_post_thumbnail_url( $p->ID, 'large' ) ?: '',
                'sku'          => get_post_meta( $p->ID, '_integrare_sku', true ),
                'price'        => (float) get_post_meta( $p->ID, '_integrare_precio_unitario', true ),
                'upb'          => (int) get_post_meta( $p->ID, '_integrare_unidades_caja', true ),
                'stock'        => (int) get_post_meta( $p->ID, '_integrare_stock', true ),
                'category'     => wp_get_post_terms( $p->ID, 'producto_categoria', array( 'fields' => 'names' ) ),
                'show_price'   => (bool) get_post_meta( $p->ID, '_integrare_mostrar_precio', true ),
            ),
            'discount_table' => Integrare_Pricing::get_discount_table( $p->ID ),
            'default_pricing' => Integrare_Pricing::calculate( $p->ID, 1 ),
        );
    }

    /**
     * Get cart data.
     */
    private function get_cart_data() {
        if ( ! is_user_logged_in() ) {
            return array( 'cart' => array( 'items' => array(), 'total' => 0, 'item_count' => 0 ) );
        }
        return array( 'cart' => Integrare_Cart::get_totals( get_current_user_id() ) );
    }

    /**
     * Get product categories.
     */
    private function get_categories() {
        $terms = get_terms( array(
            'taxonomy'   => 'producto_categoria',
            'hide_empty' => true,
        ) );
        if ( is_wp_error( $terms ) ) return array();

        $cats = array();
        foreach ( $terms as $t ) {
            $cats[] = array( 'slug' => $t->slug, 'name' => $t->name, 'count' => $t->count );
        }
        return $cats;
    }

    /**
     * Get account data (user info + orders).
     */
    private function get_account_data() {
        if ( ! is_user_logged_in() ) {
            return array( 'user' => null, 'orders' => array() );
        }

        $user       = wp_get_current_user();
        $raw_orders = Integrare_Orders::get_user_orders( $user->ID );

        // Enrich each order with its items
        $orders = array();
        foreach ( $raw_orders as $order ) {
            $full = Integrare_Orders::get_order( $order['id'] );
            if ( $full ) {
                $orders[] = $full;
            }
        }

        return array(
            'user'   => array(
                'id'    => $user->ID,
                'name'  => $user->display_name,
                'email' => $user->user_email,
                'phone' => get_user_meta( $user->ID, 'billing_phone', true ),
            ),
            'orders' => $orders,
        );
    }
}
