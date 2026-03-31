<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Auth {

    public function __construct() {
        // Registration (guests only)
        add_action( 'wp_ajax_nopriv_integrare_register', array( $this, 'handle_register' ) );

        // Login (guests only)
        add_action( 'wp_ajax_nopriv_integrare_login', array( $this, 'handle_login' ) );

        // Logout (logged-in only)
        add_action( 'wp_ajax_integrare_logout', array( $this, 'handle_logout' ) );

        // Forgot password (guests only)
        add_action( 'wp_ajax_nopriv_integrare_forgot_password', array( $this, 'handle_forgot_password' ) );

        // Reset password (guests only)
        add_action( 'wp_ajax_nopriv_integrare_reset_password', array( $this, 'handle_reset_password' ) );

        // Google OAuth via popup (guests only)
        add_action( 'wp_ajax_nopriv_integrare_google_login', array( __CLASS__, 'handle_google_login' ) );

        // OAuth callback via REST API (kept for Microsoft)
        add_action( 'rest_api_init', array( __CLASS__, 'register_oauth_route' ) );
    }

    /**
     * Register REST API route for OAuth callback.
     */
    public static function register_oauth_route() {
        register_rest_route( 'integrare/v1', '/oauth-callback', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'handle_oauth_callback' ),
            'permission_callback' => '__return_true',
        ) );
    }

    // ── Registration ──────────────────────────────────────────
    public function handle_register() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        // Honeypot anti-spam
        if ( ! empty( $_POST['website_url'] ) ) {
            wp_send_json_error( array( 'message' => 'Solicitud no válida.' ) );
        }

        // Rate limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $rate_key = 'integrare_reg_' . md5( $ip );
        $attempts = (int) get_transient( $rate_key );
        if ( $attempts >= 5 ) {
            wp_send_json_error( array( 'message' => 'Demasiados intentos. Espera unos minutos.' ) );
        }
        set_transient( $rate_key, $attempts + 1, 300 ); // 5 min window

        $first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
        $last_name  = sanitize_text_field( $_POST['last_name'] ?? '' );
        $email      = sanitize_email( $_POST['email'] ?? '' );
        $password   = $_POST['password'] ?? '';
        $password2  = $_POST['password_confirm'] ?? '';

        // Validations
        if ( strlen( $first_name ) < 2 ) {
            wp_send_json_error( array( 'message' => 'El nombre debe tener al menos 2 caracteres.', 'field' => 'first_name' ) );
        }
        if ( strlen( $last_name ) < 2 ) {
            wp_send_json_error( array( 'message' => 'El apellido debe tener al menos 2 caracteres.', 'field' => 'last_name' ) );
        }
        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Ingresa un correo electrónico válido.', 'field' => 'email' ) );
        }
        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => 'Este correo ya está registrado. ¿Quieres iniciar sesión?', 'field' => 'email' ) );
        }
        if ( strlen( $password ) < 8 ) {
            wp_send_json_error( array( 'message' => 'La contraseña debe tener al menos 8 caracteres.', 'field' => 'password' ) );
        }
        if ( ! preg_match( '/[A-Z]/', $password ) ) {
            wp_send_json_error( array( 'message' => 'La contraseña debe contener al menos una letra mayúscula.', 'field' => 'password' ) );
        }
        if ( ! preg_match( '/[0-9]/', $password ) ) {
            wp_send_json_error( array( 'message' => 'La contraseña debe contener al menos un número.', 'field' => 'password' ) );
        }
        if ( $password !== $password2 ) {
            wp_send_json_error( array( 'message' => 'Las contraseñas no coinciden.', 'field' => 'password_confirm' ) );
        }

        // Create user
        $username = sanitize_user( strtolower( $first_name . '.' . $last_name ) );
        // Ensure unique username
        $base_username = $username;
        $counter = 1;
        while ( username_exists( $username ) ) {
            $username = $base_username . $counter;
            $counter++;
        }

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        }

        // Update user data
        wp_update_user( array(
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
            'role'         => 'subscriber',
        ) );

        // Send welcome email
        $this->send_welcome_email( $user_id, $first_name, $email );

        // Auto-login after registration
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        wp_send_json_success( array(
            'message'  => '¡Bienvenido a Integrare, ' . esc_html( $first_name ) . '!',
            'redirect' => home_url( '/tienda/' ),
        ) );
    }

    // ── Login ─────────────────────────────────────────────────
    public function handle_login() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        // Rate limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $rate_key = 'integrare_login_' . md5( $ip );
        $attempts = (int) get_transient( $rate_key );
        if ( $attempts >= 10 ) {
            wp_send_json_error( array( 'message' => 'Demasiados intentos. Espera unos minutos.' ) );
        }
        set_transient( $rate_key, $attempts + 1, 300 );

        $email    = sanitize_email( $_POST['email'] ?? '' );
        $password = $_POST['password'] ?? '';
        $remember = ! empty( $_POST['remember'] );

        if ( ! $email || ! $password ) {
            wp_send_json_error( array( 'message' => 'Completa todos los campos.' ) );
        }

        // WP uses username for login, but we allow email
        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            wp_send_json_error( array( 'message' => 'Correo o contraseña incorrectos.' ) );
        }

        $creds = array(
            'user_login'    => $user->user_login,
            'user_password' => $password,
            'remember'      => $remember,
        );

        $signed = wp_signon( $creds, is_ssl() );

        if ( is_wp_error( $signed ) ) {
            wp_send_json_error( array( 'message' => 'Correo o contraseña incorrectos.' ) );
        }

        // Reset rate limit on success
        delete_transient( $rate_key );

        wp_send_json_success( array(
            'message'  => '¡Bienvenido de vuelta, ' . esc_html( $signed->display_name ) . '!',
            'redirect' => home_url( '/tienda/' ),
        ) );
    }

    // ── Logout ────────────────────────────────────────────────
    public function handle_logout() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        wp_logout();

        wp_send_json_success( array(
            'message'  => 'Sesión cerrada correctamente.',
            'redirect' => home_url( '/tienda/' ),
        ) );
    }

    // ── Forgot Password ───────────────────────────────────────
    public function handle_forgot_password() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        $email = sanitize_email( $_POST['email'] ?? '' );

        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => 'Ingresa un correo electrónico válido.' ) );
        }

        $user = get_user_by( 'email', $email );

        // Always show success (prevent email enumeration)
        if ( ! $user ) {
            wp_send_json_success( array(
                'message' => 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.',
            ) );
            return;
        }

        $key = get_password_reset_key( $user );

        if ( is_wp_error( $key ) ) {
            wp_send_json_error( array( 'message' => 'Error al generar el enlace. Intenta de nuevo.' ) );
        }

        $reset_url = home_url( '/tienda/auth/?vista=reset&key=' . $key . '&login=' . rawurlencode( $user->user_login ) );

        $subject = 'Restablecer tu contraseña — Integrare';
        $body = $this->get_reset_email_html( $user->display_name, $reset_url );
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        wp_mail( $email, $subject, $body, $headers );

        wp_send_json_success( array(
            'message' => 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.',
        ) );
    }

    // ── Reset Password ────────────────────────────────────────
    public function handle_reset_password() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        $login    = sanitize_text_field( $_POST['login'] ?? '' );
        $key      = sanitize_text_field( $_POST['key'] ?? '' );
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirm'] ?? '';

        if ( ! $login || ! $key ) {
            wp_send_json_error( array( 'message' => 'Enlace de restablecimiento inválido.' ) );
        }

        if ( strlen( $password ) < 8 ) {
            wp_send_json_error( array( 'message' => 'La contraseña debe tener al menos 8 caracteres.' ) );
        }
        if ( ! preg_match( '/[A-Z]/', $password ) ) {
            wp_send_json_error( array( 'message' => 'La contraseña debe contener al menos una letra mayúscula.' ) );
        }
        if ( ! preg_match( '/[0-9]/', $password ) ) {
            wp_send_json_error( array( 'message' => 'La contraseña debe contener al menos un número.' ) );
        }
        if ( $password !== $password2 ) {
            wp_send_json_error( array( 'message' => 'Las contraseñas no coinciden.' ) );
        }

        $user = check_password_reset_key( $key, $login );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => 'El enlace ha expirado o es inválido. Solicita uno nuevo.' ) );
        }

        reset_password( $user, $password );

        wp_send_json_success( array(
            'message'  => '¡Contraseña actualizada! Ya puedes iniciar sesión.',
            'redirect' => home_url( '/tienda/auth/' ),
        ) );
    }

    // ── Welcome Email ─────────────────────────────────────────
    private function send_welcome_email( $user_id, $name, $email ) {
        $subject = '¡Bienvenido a Integrare!';
        $body    = $this->get_welcome_email_html( $name );
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        wp_mail( $email, $subject, $body, $headers );
    }

    private function get_welcome_email_html( $name ) {
        $catalog_url = home_url( '/tienda/' );
        return '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#F4F4F4;font-family:Inter,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif;">
            <div style="max-width:560px;margin:40px auto;background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <div style="background:#4b5e73;padding:32px 40px;text-align:center;">
                    <img src="https://integrare.mx/wp-content/uploads/2025/11/integrare-solo.svg" alt="Integrare" height="36" style="filter:brightness(10);">
                </div>
                <div style="padding:40px;">
                    <h1 style="font-family:Georgia,serif;font-size:24px;color:#4b5e73;margin:0 0 16px;">¡Hola, ' . esc_html( $name ) . '!</h1>
                    <p style="font-size:15px;color:#333;line-height:1.6;margin:0 0 16px;">
                        Bienvenido/a a <strong>Integrare</strong>, tu distribuidora farmacéutica B2B de confianza.
                    </p>
                    <p style="font-size:15px;color:#333;line-height:1.6;margin:0 0 16px;">
                        Ahora tienes acceso completo a nuestro catálogo de consumibles médicos con precios especiales por volumen.
                        Explora nuestros productos y realiza tus pedidos de manera rápida y sencilla.
                    </p>
                    <div style="text-align:center;margin:32px 0;">
                        <a href="' . esc_url( $catalog_url ) . '"
                           style="display:inline-block;padding:14px 32px;background:#4b5e73;color:#FFFFFF;text-decoration:none;border-radius:8px;font-size:14px;font-weight:500;">
                            Explorar Catálogo
                        </a>
                    </div>
                    <hr style="border:none;border-top:1px solid #E5E5E5;margin:24px 0;">
                    <p style="font-size:13px;color:#888;line-height:1.6;margin:0;">
                        ¿Necesitas ayuda? Contáctanos en
                        <a href="mailto:ventas@integrare.mx" style="color:#4b5e73;">ventas@integrare.mx</a>
                    </p>
                </div>
            </div>
        </body>
        </html>';
    }

    private function get_reset_email_html( $name, $reset_url ) {
        return '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#F4F4F4;font-family:Inter,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif;">
            <div style="max-width:560px;margin:40px auto;background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                <div style="background:#4b5e73;padding:32px 40px;text-align:center;">
                    <img src="https://integrare.mx/wp-content/uploads/2025/11/integrare-solo.svg" alt="Integrare" height="36" style="filter:brightness(10);">
                </div>
                <div style="padding:40px;">
                    <h1 style="font-family:Georgia,serif;font-size:24px;color:#4b5e73;margin:0 0 16px;">Restablecer contraseña</h1>
                    <p style="font-size:15px;color:#333;line-height:1.6;margin:0 0 16px;">
                        Hola <strong>' . esc_html( $name ) . '</strong>, recibimos una solicitud para restablecer tu contraseña.
                    </p>
                    <p style="font-size:15px;color:#333;line-height:1.6;margin:0 0 16px;">
                        Haz clic en el botón de abajo para crear una nueva contraseña. Este enlace expira en 24 horas.
                    </p>
                    <div style="text-align:center;margin:32px 0;">
                        <a href="' . esc_url( $reset_url ) . '"
                           style="display:inline-block;padding:14px 32px;background:#4b5e73;color:#FFFFFF;text-decoration:none;border-radius:8px;font-size:14px;font-weight:500;">
                            Restablecer Contraseña
                        </a>
                    </div>
                    <p style="font-size:13px;color:#888;line-height:1.6;margin:0;">
                        Si no solicitaste este cambio, puedes ignorar este correo de manera segura.
                    </p>
                </div>
            </div>
        </body>
        </html>';
    }

    // ══════════════════════════════════════════════════════════
    //  OAuth — Google
    // ══════════════════════════════════════════════════════════

    /**
     * Build Google OAuth authorization URL.
     */
    public static function get_google_auth_url() {
        $client_id    = get_option( 'integrare_google_client_id', '' );
        $redirect_uri = rest_url( 'integrare/v1/oauth-callback' );

        if ( ! $client_id ) return '';

        $state = bin2hex( random_bytes( 16 ) );
        set_transient( 'integrare_oauth_state_' . $state, 'google', 600 );

        $params = array(
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'prompt'        => 'select_account',
        );

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( $params );
    }

    /**
     * Build Microsoft OAuth authorization URL.
     */
    public static function get_microsoft_auth_url() {
        $client_id = get_option( 'integrare_microsoft_client_id', '' );
        $tenant    = get_option( 'integrare_microsoft_tenant_id', 'common' ) ?: 'common';
        $redirect_uri = rest_url( 'integrare/v1/oauth-callback' );

        if ( ! $client_id ) return '';

        $state = bin2hex( random_bytes( 16 ) );
        set_transient( 'integrare_oauth_state_' . $state, 'microsoft', 600 );

        $params = array(
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'openid email profile User.Read',
            'state'         => $state,
            'prompt'        => 'select_account',
        );

        return 'https://login.microsoftonline.com/' . $tenant . '/oauth2/v2.0/authorize?' . http_build_query( $params );
    }

    /**
     * Handle OAuth callback via REST API.
     * Exchanges authorization code for user info and logs in or creates user.
     */
    public static function handle_oauth_callback( $request = null ) {
        $code  = sanitize_text_field( $_GET['code'] ?? '' );
        $state = sanitize_text_field( $_GET['state'] ?? '' );

        if ( ! $code || ! $state ) {
            error_log( '[Integrare OAuth] Missing code or state. code=' . ( $code ? 'present' : 'empty' ) . ' state=' . ( $state ? 'present' : 'empty' ) );
            wp_redirect( home_url( '/tienda/auth/?vista=login&oauth_error=missing_params' ) );
            exit;
        }

        // Determine provider from state
        $provider = get_transient( 'integrare_oauth_state_' . $state );
        delete_transient( 'integrare_oauth_state_' . $state );

        if ( ! $provider ) {
            error_log( '[Integrare OAuth] Invalid or expired state token: ' . $state );
            wp_redirect( home_url( '/tienda/auth/?vista=login&oauth_error=invalid_state' ) );
            exit;
        }

        error_log( '[Integrare OAuth] Provider: ' . $provider . ', exchanging code...' );

        $user_info = null;

        if ( $provider === 'google' ) {
            $user_info = self::exchange_google_code( $code );
        } elseif ( $provider === 'microsoft' ) {
            $user_info = self::exchange_microsoft_code( $code );
        }

        if ( ! $user_info || empty( $user_info['email'] ) ) {
            error_log( '[Integrare OAuth] Code exchange failed for provider: ' . $provider );
            wp_redirect( home_url( '/tienda/auth/?vista=login&oauth_error=exchange_failed' ) );
            exit;
        }

        error_log( '[Integrare OAuth] Got user info: ' . $user_info['email'] );

        // Try to find existing user by email
        $user = get_user_by( 'email', $user_info['email'] );

        if ( $user ) {
            // Log in existing user
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, true );
            error_log( '[Integrare OAuth] Logged in existing user: ' . $user->ID );
            wp_redirect( home_url( '/tienda/' ) );
            exit;
        }

        // Create new user
        $first_name = sanitize_text_field( $user_info['first_name'] ?? '' );
        $last_name  = sanitize_text_field( $user_info['last_name'] ?? '' );
        $email      = sanitize_email( $user_info['email'] );

        $username = sanitize_user( strtolower( $first_name . '.' . $last_name ) );
        if ( ! $username ) $username = sanitize_user( strstr( $email, '@', true ) );

        $base_username = $username;
        $counter = 1;
        while ( username_exists( $username ) ) {
            $username = $base_username . $counter;
            $counter++;
        }

        $random_password = wp_generate_password( 24, true, true );
        $user_id = wp_create_user( $username, $random_password, $email );

        if ( is_wp_error( $user_id ) ) {
            error_log( '[Integrare OAuth] Failed to create user: ' . $user_id->get_error_message() );
            wp_redirect( home_url( '/tienda/auth/?vista=login&oauth_error=create_failed' ) );
            exit;
        }

        wp_update_user( array(
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => trim( $first_name . ' ' . $last_name ) ?: $username,
            'role'         => 'subscriber',
        ) );

        // Mark as OAuth user
        update_user_meta( $user_id, '_integrare_oauth_provider', $provider );

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );
        error_log( '[Integrare OAuth] Created and logged in new user: ' . $user_id );
        wp_redirect( home_url( '/tienda/' ) );
        exit;
    }

    /**
     * Handle Google OAuth login via popup (GIS) flow.
     * Receives authorization code from JavaScript and exchanges it server-side.
     */
    public static function handle_google_login() {
        check_ajax_referer( 'integrare_nonce', 'nonce' );

        $code = sanitize_text_field( $_POST['code'] ?? '' );

        if ( ! $code ) {
            wp_send_json_error( array( 'message' => 'Código de autorización no proporcionado.' ) );
        }

        $client_id     = get_option( 'integrare_google_client_id', '' );
        $client_secret = get_option( 'integrare_google_client_secret', '' );

        if ( ! $client_id || ! $client_secret ) {
            wp_send_json_error( array( 'message' => 'OAuth no configurado.' ) );
        }

        // Exchange code for tokens via popup flow (redirect_uri = 'postmessage')
        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code'          => $code,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => 'postmessage',
                'grant_type'    => 'authorization_code',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( '[Integrare OAuth] Token exchange failed: ' . $response->get_error_message() );
            wp_send_json_error( array( 'message' => 'Error al conectar con Google.' ) );
        }

        $token_data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $token_data['access_token'] ) ) {
            error_log( '[Integrare OAuth] No access_token in response: ' . wp_remote_retrieve_body( $response ) );
            wp_send_json_error( array( 'message' => 'Error de autenticación con Google.' ) );
        }

        // Get user info with access token
        $user_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v2/userinfo', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token_data['access_token'],
            ),
        ) );

        if ( is_wp_error( $user_response ) ) {
            wp_send_json_error( array( 'message' => 'Error al obtener datos del usuario.' ) );
        }

        $user_info = json_decode( wp_remote_retrieve_body( $user_response ), true );

        if ( empty( $user_info['email'] ) ) {
            wp_send_json_error( array( 'message' => 'No se pudo obtener el correo electrónico.' ) );
        }

        // Find existing user by email
        $user = get_user_by( 'email', $user_info['email'] );

        if ( $user ) {
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, true );
            error_log( '[Integrare OAuth] Logged in existing user: ' . $user->ID );
            wp_send_json_success( array( 'redirect' => home_url( '/tienda/' ) ) );
        }

        // Create new user
        $first_name = sanitize_text_field( $user_info['given_name'] ?? '' );
        $last_name  = sanitize_text_field( $user_info['family_name'] ?? '' );
        $email      = sanitize_email( $user_info['email'] );

        $username = sanitize_user( strtolower( $first_name . '.' . $last_name ) );
        if ( ! $username ) $username = sanitize_user( strstr( $email, '@', true ) );

        $base_username = $username;
        $counter = 1;
        while ( username_exists( $username ) ) {
            $username = $base_username . $counter;
            $counter++;
        }

        $random_password = wp_generate_password( 24, true, true );
        $user_id = wp_create_user( $username, $random_password, $email );

        if ( is_wp_error( $user_id ) ) {
            error_log( '[Integrare OAuth] Failed to create user: ' . $user_id->get_error_message() );
            wp_send_json_error( array( 'message' => 'Error al crear la cuenta.' ) );
        }

        wp_update_user( array(
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => trim( $first_name . ' ' . $last_name ) ?: $username,
            'role'         => 'subscriber',
        ) );

        update_user_meta( $user_id, '_integrare_oauth_provider', 'google' );

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );
        error_log( '[Integrare OAuth] Created and logged in new user: ' . $user_id );
        wp_send_json_success( array( 'redirect' => home_url( '/tienda/' ) ) );
    }

    /**
     * Exchange Google auth code for user info.
     */
    private static function exchange_google_code( $code ) {
        $client_id     = get_option( 'integrare_google_client_id', '' );
        $client_secret = get_option( 'integrare_google_client_secret', '' );
        $redirect_uri  = rest_url( 'integrare/v1/oauth-callback' );

        // Exchange code for access token
        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code'          => $code,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => $redirect_uri,
                'grant_type'    => 'authorization_code',
            ),
        ) );

        if ( is_wp_error( $response ) ) return null;

        $token_data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $token_data['access_token'] ) ) return null;

        // Get user info
        $user_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v2/userinfo', array(
            'headers' => array( 'Authorization' => 'Bearer ' . $token_data['access_token'] ),
        ) );

        if ( is_wp_error( $user_response ) ) return null;

        $user_data = json_decode( wp_remote_retrieve_body( $user_response ), true );
        if ( empty( $user_data['email'] ) ) return null;

        return array(
            'email'      => $user_data['email'],
            'first_name' => $user_data['given_name'] ?? '',
            'last_name'  => $user_data['family_name'] ?? '',
        );
    }

    /**
     * Exchange Microsoft auth code for user info.
     */
    private static function exchange_microsoft_code( $code ) {
        $client_id     = get_option( 'integrare_microsoft_client_id', '' );
        $client_secret = get_option( 'integrare_microsoft_client_secret', '' );
        $tenant        = get_option( 'integrare_microsoft_tenant_id', 'common' ) ?: 'common';
        $redirect_uri  = rest_url( 'integrare/v1/oauth-callback' );

        // Exchange code for access token
        $response = wp_remote_post( 'https://login.microsoftonline.com/' . $tenant . '/oauth2/v2.0/token', array(
            'body' => array(
                'code'          => $code,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => $redirect_uri,
                'grant_type'    => 'authorization_code',
                'scope'         => 'openid email profile User.Read',
            ),
        ) );

        if ( is_wp_error( $response ) ) return null;

        $token_data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $token_data['access_token'] ) ) return null;

        // Get user info from Microsoft Graph
        $user_response = wp_remote_get( 'https://graph.microsoft.com/v1.0/me', array(
            'headers' => array( 'Authorization' => 'Bearer ' . $token_data['access_token'] ),
        ) );

        if ( is_wp_error( $user_response ) ) return null;

        $user_data = json_decode( wp_remote_retrieve_body( $user_response ), true );
        $email = $user_data['mail'] ?? $user_data['userPrincipalName'] ?? '';
        if ( ! $email ) return null;

        return array(
            'email'      => $email,
            'first_name' => $user_data['givenName'] ?? '',
            'last_name'  => $user_data['surname'] ?? '',
        );
    }
}

