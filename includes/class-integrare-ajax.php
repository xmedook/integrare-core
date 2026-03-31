<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Ajax {

    public function __construct() {
        // Price calculation (available to both logged-in and guest for display)
        add_action( 'wp_ajax_integrare_calculate_price', array( $this, 'calculate_price' ) );
        add_action( 'wp_ajax_nopriv_integrare_calculate_price', array( $this, 'calculate_price' ) );

        // Cart operations (logged-in only)
        add_action( 'wp_ajax_integrare_cart_add', array( $this, 'cart_add' ) );
        add_action( 'wp_ajax_integrare_cart_update', array( $this, 'cart_update' ) );
        add_action( 'wp_ajax_integrare_cart_remove', array( $this, 'cart_remove' ) );
        add_action( 'wp_ajax_integrare_cart_get', array( $this, 'cart_get' ) );

        // Checkout (logged-in only)
        add_action( 'wp_ajax_integrare_checkout', array( $this, 'checkout' ) );

        // Profile update (logged-in only)
        add_action( 'wp_ajax_integrare_update_profile', array( $this, 'update_profile' ) );

        // Payment handlers (logged-in only)
        add_action( 'wp_ajax_integrare_create_payment', array( $this, 'create_payment' ) );
        add_action( 'wp_ajax_integrare_verify_payment', array( $this, 'verify_payment' ) );
    }

    /**
     * Calculate price for a product + quantity.
     */
    public function calculate_price() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        $product_id = absint( $_POST['product_id'] ?? 0 );
        $quantity   = absint( $_POST['quantity'] ?? 1 );

        if ( ! $product_id ) {
            wp_send_json_error( array( 'message' => 'Producto no válido.' ) );
        }

        $result = Integrare_Pricing::calculate( $product_id, $quantity );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Add item to cart.
     */
    public function cart_add() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesión.' ) );
        }

        $product_id = absint( $_POST['product_id'] ?? 0 );
        $quantity   = max( 1, absint( $_POST['quantity'] ?? 1 ) );

        if ( ! $product_id || get_post_type( $product_id ) !== 'farmacia_product' ) {
            wp_send_json_error( array( 'message' => 'Producto no válido.' ) );
        }

        $result = Integrare_Cart::add_item( get_current_user_id(), $product_id, $quantity );

        if ( $result === false ) {
            wp_send_json_error( array( 'message' => 'Error al agregar al carrito.' ) );
        }

        $count = Integrare_Cart::get_count( get_current_user_id() );

        wp_send_json_success( array(
            'message'    => 'Producto agregado al carrito.',
            'cart_count' => $count,
        ) );
    }

    /**
     * Update cart item quantity.
     */
    public function cart_update() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesión.' ) );
        }

        $product_id = absint( $_POST['product_id'] ?? 0 );
        $quantity   = absint( $_POST['quantity'] ?? 0 );

        Integrare_Cart::update_item( get_current_user_id(), $product_id, $quantity );

        $cart = Integrare_Cart::get_totals( get_current_user_id() );

        wp_send_json_success( array(
            'message'    => 'Carrito actualizado.',
            'cart'       => $cart,
        ) );
    }

    /**
     * Remove item from cart.
     */
    public function cart_remove() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesión.' ) );
        }

        $product_id = absint( $_POST['product_id'] ?? 0 );

        Integrare_Cart::remove_item( get_current_user_id(), $product_id );

        $cart = Integrare_Cart::get_totals( get_current_user_id() );

        wp_send_json_success( array(
            'message'    => 'Producto eliminado.',
            'cart'       => $cart,
        ) );
    }

    /**
     * Get full cart.
     */
    public function cart_get() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesión.' ) );
        }

        $cart = Integrare_Cart::get_totals( get_current_user_id() );
        wp_send_json_success( $cart );
    }

    /**
     * Process checkout.
     */
    public function checkout() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesión.' ) );
        }

        $notes    = sanitize_textarea_field( $_POST['notes'] ?? '' );
        $shipping = json_decode( stripslashes( $_POST['shipping'] ?? '{}' ), true );

        $order_id = Integrare_Orders::create_order( get_current_user_id(), $notes );

        // Save shipping info in dedicated columns
        if ( ! is_wp_error( $order_id ) && ! empty( $shipping ) ) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'integrare_orders',
                array(
                    'shipping_name'    => sanitize_text_field( $shipping['name'] ?? '' ),
                    'shipping_phone'   => sanitize_text_field( $shipping['phone'] ?? '' ),
                    'shipping_address' => sanitize_text_field( $shipping['address'] ?? '' ),
                    'shipping_city'    => sanitize_text_field( $shipping['city'] ?? '' ),
                    'shipping_state'   => sanitize_text_field( $shipping['state'] ?? '' ),
                    'shipping_zip'     => sanitize_text_field( $shipping['zip'] ?? '' ),
                ),
                array( 'id' => $order_id ),
                array( '%s', '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
        }

        if ( is_wp_error( $order_id ) ) {
            wp_send_json_error( array( 'message' => $order_id->get_error_message() ) );
        }

        $order = Integrare_Orders::get_order( $order_id );

        wp_send_json_success( array(
            'message'  => '¡Pedido realizado con éxito!',
            'order_id' => $order_id,
            'order'    => $order,
        ) );
    }

    /**
     * Update user profile.
     */
    public function update_profile() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesión.' ) );
        }

        $user_id  = get_current_user_id();
        $name     = sanitize_text_field( $_POST['name'] ?? '' );
        $phone    = sanitize_text_field( $_POST['phone'] ?? '' );
        $password = $_POST['password'] ?? '';

        if ( $name ) {
            wp_update_user( array( 'ID' => $user_id, 'display_name' => $name ) );
        }

        if ( $phone ) {
            update_user_meta( $user_id, 'billing_phone', $phone );
        }

        if ( $password && strlen( $password ) >= 6 ) {
            wp_set_password( $password, $user_id );
        }

        wp_send_json_success( array( 'message' => 'Perfil actualizado.' ) );
    }

    /**
     * Create order + initiate payment (Stripe or PayPal).
     */
    public function create_payment() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesión.' ) );
        }

        $method   = sanitize_text_field( $_POST['payment_method'] ?? '' );
        $notes    = sanitize_textarea_field( $_POST['notes'] ?? '' );
        $shipping = json_decode( stripslashes( $_POST['shipping'] ?? '{}' ), true );

        if ( ! in_array( $method, array( 'stripe', 'paypal' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Método de pago no válido.' ) );
        }

        // Create the order first (status = pending)
        $order_id = Integrare_Orders::create_order( get_current_user_id(), $notes );

        if ( is_wp_error( $order_id ) ) {
            wp_send_json_error( array( 'message' => $order_id->get_error_message() ) );
        }

        // Save shipping info in dedicated columns
        if ( ! empty( $shipping ) ) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'integrare_orders',
                array(
                    'shipping_name'    => sanitize_text_field( $shipping['name'] ?? '' ),
                    'shipping_phone'   => sanitize_text_field( $shipping['phone'] ?? '' ),
                    'shipping_address' => sanitize_text_field( $shipping['address'] ?? '' ),
                    'shipping_city'    => sanitize_text_field( $shipping['city'] ?? '' ),
                    'shipping_state'   => sanitize_text_field( $shipping['state'] ?? '' ),
                    'shipping_zip'     => sanitize_text_field( $shipping['zip'] ?? '' ),
                ),
                array( 'id' => $order_id ),
                array( '%s', '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
        }

        // Get order total
        $order = Integrare_Orders::get_order( $order_id );
        $total = floatval( $order['total'] ?? 0 );

        if ( $total <= 0 ) {
            wp_send_json_error( array( 'message' => 'El total del pedido es inválido.' ) );
        }

        // Save payment method on the order
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'integrare_orders',
            array( 'payment_method' => $method ),
            array( 'id' => $order_id ),
            array( '%s' ),
            array( '%d' )
        );

        // Initiate payment
        if ( $method === 'stripe' ) {
            $result = Integrare_Payments::create_stripe_session( $order_id, $total );

            if ( is_wp_error( $result ) ) {
                wp_send_json_error( array( 'message' => $result->get_error_message() ) );
            }

            wp_send_json_success( array(
                'gateway'    => 'stripe',
                'order_id'   => $order_id,
                'session_id' => $result['id'],
                'url'        => $result['url'],
            ) );

        } else { // paypal
            $result = Integrare_Payments::create_paypal_order( $order_id, $total );

            if ( is_wp_error( $result ) ) {
                wp_send_json_error( array( 'message' => $result->get_error_message() ) );
            }

            wp_send_json_success( array(
                'gateway'         => 'paypal',
                'order_id'        => $order_id,
                'paypal_order_id' => $result['id'],
            ) );
        }
    }

    /**
     * Verify that a payment was completed (Stripe redirect or PayPal capture).
     */
    public function verify_payment() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Debes iniciar sesión.' ) );
        }

        $gateway  = sanitize_text_field( $_POST['gateway'] ?? '' );
        $order_id = absint( $_POST['order_id'] ?? 0 );

        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => 'Pedido no encontrado.' ) );
        }

        $paid = false;

        if ( $gateway === 'stripe' ) {
            $session_id = sanitize_text_field( $_POST['session_id'] ?? '' );
            $paid = Integrare_Payments::verify_stripe_session( $session_id );

        } elseif ( $gateway === 'paypal' ) {
            $paypal_order_id = sanitize_text_field( $_POST['paypal_order_id'] ?? '' );
            $capture = Integrare_Payments::capture_paypal_order( $paypal_order_id );

            if ( ! is_wp_error( $capture ) && $capture['status'] === 'COMPLETED' ) {
                $paid = true;
            }
        }

        if ( $paid ) {
            Integrare_Orders::update_status( $order_id, 'paid' );
            wp_send_json_success( array(
                'message'  => '¡Pago completado!',
                'order_id' => $order_id,
            ) );
        }

        wp_send_json_error( array( 'message' => 'El pago no pudo ser verificado.' ) );
    }
}
