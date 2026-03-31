<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Payment gateway integrations (Stripe + PayPal).
 * Uses wp_remote_post() — no external SDKs needed.
 */
class Integrare_Payments {

    /* ─── Option keys ──────────────────────────────── */
    const OPT_STRIPE_MODE       = 'integrare_stripe_mode';
    const OPT_STRIPE_PK_LIVE    = 'integrare_stripe_pk_live';
    const OPT_STRIPE_SK_LIVE    = 'integrare_stripe_sk_live';
    const OPT_STRIPE_PK_TEST    = 'integrare_stripe_pk_test';
    const OPT_STRIPE_SK_TEST    = 'integrare_stripe_sk_test';

    const OPT_PAYPAL_MODE       = 'integrare_paypal_mode';
    const OPT_PAYPAL_CLIENT_LIVE = 'integrare_paypal_client_live';
    const OPT_PAYPAL_SECRET_LIVE = 'integrare_paypal_secret_live';
    const OPT_PAYPAL_CLIENT_TEST = 'integrare_paypal_client_test';
    const OPT_PAYPAL_SECRET_TEST = 'integrare_paypal_secret_test';

    /* ─── Helpers ───────────────────────────────────── */

    public static function get_stripe_mode() {
        return get_option( self::OPT_STRIPE_MODE, 'test' );
    }

    public static function get_stripe_pk() {
        $mode = self::get_stripe_mode();
        return get_option( $mode === 'live' ? self::OPT_STRIPE_PK_LIVE : self::OPT_STRIPE_PK_TEST, '' );
    }

    public static function get_stripe_sk() {
        $mode = self::get_stripe_mode();
        return get_option( $mode === 'live' ? self::OPT_STRIPE_SK_LIVE : self::OPT_STRIPE_SK_TEST, '' );
    }

    public static function stripe_enabled() {
        return ! empty( self::get_stripe_pk() ) && ! empty( self::get_stripe_sk() );
    }

    public static function get_paypal_mode() {
        return get_option( self::OPT_PAYPAL_MODE, 'sandbox' );
    }

    public static function get_paypal_client() {
        $mode = self::get_paypal_mode();
        return get_option( $mode === 'live' ? self::OPT_PAYPAL_CLIENT_LIVE : self::OPT_PAYPAL_CLIENT_TEST, '' );
    }

    public static function get_paypal_secret() {
        $mode = self::get_paypal_mode();
        return get_option( $mode === 'live' ? self::OPT_PAYPAL_SECRET_LIVE : self::OPT_PAYPAL_SECRET_TEST, '' );
    }

    public static function paypal_enabled() {
        return ! empty( self::get_paypal_client() ) && ! empty( self::get_paypal_secret() );
    }

    public static function get_paypal_base_url() {
        return self::get_paypal_mode() === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /* ─── STRIPE ────────────────────────────────────── */

    /**
     * Create a Stripe Checkout Session.
     *
     * @param int    $order_id   Integrare order ID.
     * @param float  $total      Total in MXN.
     * @param string $desc       Line item description.
     * @return array|WP_Error    { id, url }
     */
    public static function create_stripe_session( $order_id, $total, $desc = 'Pedido Integrare' ) {
        $sk = self::get_stripe_sk();
        if ( ! $sk ) {
            return new \WP_Error( 'stripe_not_configured', 'Stripe no está configurado.' );
        }

        $amount = intval( round( $total * 100 ) ); // Stripe expects cents

        $body = array(
            'payment_method_types[]' => 'card',
            'line_items[0][price_data][currency]'     => 'mxn',
            'line_items[0][price_data][product_data][name]' => $desc . ' #' . $order_id,
            'line_items[0][price_data][unit_amount]'  => $amount,
            'line_items[0][quantity]'                  => 1,
            'mode'        => 'payment',
            'success_url' => home_url( '/tienda/checkout/?payment_status=success&order_id=' . $order_id . '&session_id={CHECKOUT_SESSION_ID}' ),
            'cancel_url'  => home_url( '/tienda/checkout/?payment_status=cancelled&order_id=' . $order_id ),
            'metadata[order_id]' => $order_id,
        );

        $response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $sk . ':' ),
            ),
            'body'    => $body,
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $data['error'] ) ) {
            return new \WP_Error( 'stripe_error', $data['error']['message'] ?? 'Error de Stripe.' );
        }

        return array(
            'id'  => $data['id'],
            'url' => $data['url'],
        );
    }

    /**
     * Verify a Stripe Checkout Session was paid.
     */
    public static function verify_stripe_session( $session_id ) {
        $sk = self::get_stripe_sk();
        if ( ! $sk ) return false;

        $response = wp_remote_get( 'https://api.stripe.com/v1/checkout/sessions/' . $session_id, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $sk . ':' ),
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) return false;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        return ( ! empty( $data['payment_status'] ) && $data['payment_status'] === 'paid' );
    }

    /* ─── PAYPAL ────────────────────────────────────── */

    /**
     * Get a PayPal access token.
     */
    private static function get_paypal_token() {
        $client = self::get_paypal_client();
        $secret = self::get_paypal_secret();
        if ( ! $client || ! $secret ) return null;

        $response = wp_remote_post( self::get_paypal_base_url() . '/v1/oauth2/token', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $client . ':' . $secret ),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body'    => 'grant_type=client_credentials',
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) return null;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        return $data['access_token'] ?? null;
    }

    /**
     * Create a PayPal order.
     *
     * @param int   $order_id Integrare order ID.
     * @param float $total    Total in MXN.
     * @return array|WP_Error { id, status }
     */
    public static function create_paypal_order( $order_id, $total ) {
        $token = self::get_paypal_token();
        if ( ! $token ) {
            return new \WP_Error( 'paypal_not_configured', 'PayPal no está configurado.' );
        }

        $body = array(
            'intent' => 'CAPTURE',
            'purchase_units' => array(
                array(
                    'reference_id' => strval( $order_id ),
                    'description'  => 'Pedido Integrare #' . $order_id,
                    'amount' => array(
                        'currency_code' => 'MXN',
                        'value'         => number_format( $total, 2, '.', '' ),
                    ),
                ),
            ),
        );

        $response = wp_remote_post( self::get_paypal_base_url() . '/v2/checkout/orders', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( $body ),
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) return $response;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $data['id'] ) ) {
            $msg = $data['details'][0]['description'] ?? $data['message'] ?? 'Error de PayPal.';
            return new \WP_Error( 'paypal_error', $msg );
        }

        return array(
            'id'     => $data['id'],
            'status' => $data['status'],
        );
    }

    /**
     * Capture (finalize) a PayPal order after buyer approval.
     */
    public static function capture_paypal_order( $paypal_order_id ) {
        $token = self::get_paypal_token();
        if ( ! $token ) return new \WP_Error( 'paypal_not_configured', 'PayPal no está configurado.' );

        $response = wp_remote_post( self::get_paypal_base_url() . '/v2/checkout/orders/' . $paypal_order_id . '/capture', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => '{}',
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) return $response;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! empty( $data['status'] ) && $data['status'] === 'COMPLETED' ) {
            return array( 'status' => 'COMPLETED', 'id' => $data['id'] );
        }

        $msg = $data['details'][0]['description'] ?? $data['message'] ?? 'No se pudo capturar el pago.';
        return new \WP_Error( 'paypal_capture_error', $msg );
    }
}
