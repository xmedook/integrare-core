<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTTP client for Dolibarr REST API.
 */
class Integrare_Dolibarr {

    private $url;
    private $key;

    public function __construct() {
        $this->url = rtrim( get_option( 'integrare_dolibarr_url', 'https://admin.integrare.mx/api/index.php' ), '/' );
        $this->key = get_option( 'integrare_dolibarr_key', '9eee8597c1c4891ac6dae523cc3d06f68df096fc' );
    }

    /**
     * Make an HTTP request to the Dolibarr API.
     *
     * @param string $method   GET, POST, PUT, DELETE
     * @param string $endpoint e.g. /thirdparties
     * @param array  $body     Request body (for POST/PUT)
     * @return array|WP_Error  Decoded response body or WP_Error
     */
    public function request( $method, $endpoint, $body = array() ) {
        $url  = $this->url . $endpoint;
        $args = array(
            'method'  => strtoupper( $method ),
            'headers' => array(
                'DOLAPIKEY'    => $this->key,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ),
            'timeout' => 15,
        );

        if ( ! empty( $body ) && in_array( $args['method'], array( 'POST', 'PUT' ), true ) ) {
            $args['body'] = wp_json_encode( $body );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            $this->log_error( $endpoint, $response->get_error_message() );
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $raw  = wp_remote_retrieve_body( $response );
        $data = json_decode( $raw, true );

        if ( $code < 200 || $code >= 300 ) {
            $message = isset( $data['error']['message'] ) ? $data['error']['message'] : "HTTP {$code}: {$raw}";
            $this->log_error( $endpoint, $message );
            return new WP_Error( 'dolibarr_api_error', $message, array( 'status' => $code ) );
        }

        return $data;
    }

    /**
     * Find a thirdparty by email.
     *
     * @param string $email
     * @return array|null|WP_Error  First match or null if not found.
     */
    public function get_thirdparty_by_email( $email ) {
        $filter   = 't.email:=:"' . rawurlencode( $email ) . '"';
        $endpoint = '/thirdparties?sqlfilters=' . rawurlencode( 't.email:=:"' . $email . '"' );
        $result   = $this->request( 'GET', $endpoint );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        if ( empty( $result ) ) {
            return null;
        }

        return is_array( $result ) ? $result[0] : null;
    }

    /**
     * Create a new thirdparty (client).
     *
     * @param array $data  Keys: name, email, phone
     * @return array|WP_Error
     */
    public function create_thirdparty( $data ) {
        $body = array(
            'name'       => sanitize_text_field( $data['name'] ?? '' ),
            'email'      => sanitize_email( $data['email'] ?? '' ),
            'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
            'client'     => 1,
            'country_id' => 156, // Mexico
        );

        return $this->request( 'POST', '/thirdparties', $body );
    }

    /**
     * Update an existing thirdparty.
     *
     * @param int   $id
     * @param array $data
     * @return array|WP_Error
     */
    public function update_thirdparty( $id, $data ) {
        return $this->request( 'PUT', '/thirdparties/' . absint( $id ), $data );
    }

    /**
     * Create a product in Dolibarr.
     *
     * @param array $data  Keys: ref, label, price, stock_reel
     * @return array|WP_Error
     */
    public function create_product( $data ) {
        $body = array(
            'ref'        => sanitize_text_field( $data['ref'] ?? '' ),
            'label'      => sanitize_text_field( $data['label'] ?? '' ),
            'price'      => floatval( $data['price'] ?? 0 ),
            'stock_reel' => floatval( $data['stock_reel'] ?? 0 ),
            'type'       => 0, // 0 = product
        );

        return $this->request( 'POST', '/products', $body );
    }

    /**
     * Update a product in Dolibarr.
     *
     * @param int   $id
     * @param array $data
     * @return array|WP_Error
     */
    public function update_product( $id, $data ) {
        return $this->request( 'PUT', '/products/' . absint( $id ), $data );
    }

    /**
     * Create an order in Dolibarr.
     *
     * @param array $data  Keys: socid, lines[]
     * @return array|WP_Error
     */
    public function create_order( $data ) {
        return $this->request( 'POST', '/orders', $data );
    }

    /**
     * Validate (confirm) an order in Dolibarr.
     *
     * @param int $id  Dolibarr order ID
     * @return array|WP_Error
     */
    public function validate_order( $id ) {
        return $this->request( 'POST', '/orders/' . absint( $id ) . '/validate', array() );
    }

    /**
     * Store error in wp_options (keeps last 50 entries).
     *
     * @param string $context  Endpoint or description
     * @param string $error    Error message
     */
    public function log_error( $context, $error ) {
        $log = get_option( 'integrare_dolibarr_log', array() );

        if ( ! is_array( $log ) ) {
            $log = array();
        }

        array_unshift( $log, array(
            'time'    => current_time( 'mysql' ),
            'context' => $context,
            'error'   => $error,
        ) );

        // Keep only the last 50 entries
        $log = array_slice( $log, 0, 50 );

        update_option( 'integrare_dolibarr_log', $log, false );
    }
}
