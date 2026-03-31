<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin view: Configuracion — Pasarelas de pago y OAuth.
 */

$saved = false;

// Handle form submission
if ( isset( $_POST['integrare_save_settings'] ) && check_admin_referer( 'integrare_settings_save' ) ) {
    // Stripe
    update_option( 'integrare_stripe_mode', sanitize_text_field( $_POST['stripe_mode'] ?? 'test' ) );
    update_option( 'integrare_stripe_pk_live', sanitize_text_field( $_POST['stripe_pk_live'] ?? '' ) );
    update_option( 'integrare_stripe_sk_live', sanitize_text_field( $_POST['stripe_sk_live'] ?? '' ) );
    update_option( 'integrare_stripe_pk_test', sanitize_text_field( $_POST['stripe_pk_test'] ?? '' ) );
    update_option( 'integrare_stripe_sk_test', sanitize_text_field( $_POST['stripe_sk_test'] ?? '' ) );

    // PayPal
    update_option( 'integrare_paypal_mode', sanitize_text_field( $_POST['paypal_mode'] ?? 'sandbox' ) );
    update_option( 'integrare_paypal_client_live', sanitize_text_field( $_POST['paypal_client_live'] ?? '' ) );
    update_option( 'integrare_paypal_secret_live', sanitize_text_field( $_POST['paypal_secret_live'] ?? '' ) );
    update_option( 'integrare_paypal_client_test', sanitize_text_field( $_POST['paypal_client_test'] ?? '' ) );
    update_option( 'integrare_paypal_secret_test', sanitize_text_field( $_POST['paypal_secret_test'] ?? '' ) );

    // Google OAuth
    update_option( 'integrare_google_oauth_enabled', isset( $_POST['google_oauth_enabled'] ) ? '1' : '0' );
    update_option( 'integrare_google_client_id', sanitize_text_field( $_POST['google_client_id'] ?? '' ) );
    update_option( 'integrare_google_client_secret', sanitize_text_field( $_POST['google_client_secret'] ?? '' ) );

    // Microsoft OAuth
    update_option( 'integrare_microsoft_oauth_enabled', isset( $_POST['microsoft_oauth_enabled'] ) ? '1' : '0' );
    update_option( 'integrare_microsoft_client_id', sanitize_text_field( $_POST['microsoft_client_id'] ?? '' ) );
    update_option( 'integrare_microsoft_client_secret', sanitize_text_field( $_POST['microsoft_client_secret'] ?? '' ) );
    update_option( 'integrare_microsoft_tenant_id', sanitize_text_field( $_POST['microsoft_tenant_id'] ?? '' ) );

    $saved = true;
}

// Read current values — Stripe
$stripe_mode     = get_option( 'integrare_stripe_mode', 'test' );
$stripe_pk_live  = get_option( 'integrare_stripe_pk_live', '' );
$stripe_sk_live  = get_option( 'integrare_stripe_sk_live', '' );
$stripe_pk_test  = get_option( 'integrare_stripe_pk_test', '' );
$stripe_sk_test  = get_option( 'integrare_stripe_sk_test', '' );

// Read current values — PayPal
$paypal_mode        = get_option( 'integrare_paypal_mode', 'sandbox' );
$paypal_client_live = get_option( 'integrare_paypal_client_live', '' );
$paypal_secret_live = get_option( 'integrare_paypal_secret_live', '' );
$paypal_client_test = get_option( 'integrare_paypal_client_test', '' );
$paypal_secret_test = get_option( 'integrare_paypal_secret_test', '' );

// Read current values — Google OAuth
$google_oauth_enabled  = get_option( 'integrare_google_oauth_enabled', '0' );
$google_client_id      = get_option( 'integrare_google_client_id', '' );
$google_client_secret  = get_option( 'integrare_google_client_secret', '' );

// Read current values — Microsoft OAuth
$microsoft_oauth_enabled  = get_option( 'integrare_microsoft_oauth_enabled', '0' );
$microsoft_client_id      = get_option( 'integrare_microsoft_client_id', '' );
$microsoft_client_secret  = get_option( 'integrare_microsoft_client_secret', '' );
$microsoft_tenant_id      = get_option( 'integrare_microsoft_tenant_id', '' );
?>

<div class="wrap" style="max-width:800px;">
    <h1 style="margin-bottom:24px;">Integrare — Configuracion</h1>

    <?php if ( $saved ) : ?>
        <div class="notice notice-success is-dismissible"><p><strong>Configuracion guardada correctamente.</strong></p></div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field( 'integrare_settings_save' ); ?>

        <!-- ── STRIPE ─────────────────────────────────────── -->
        <div style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:24px; margin-bottom:24px;">
            <h2 style="margin:0 0 4px; font-size:18px; display:flex; align-items:center; gap:8px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#635BFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Stripe
            </h2>
            <p style="color:#666; margin:0 0 16px; font-size:13px;">Acepta pagos con tarjeta de credito/debito.</p>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="stripe_mode">Modo</label></th>
                    <td>
                        <select name="stripe_mode" id="stripe_mode">
                            <option value="test" <?php selected( $stripe_mode, 'test' ); ?>>Test (pruebas)</option>
                            <option value="live" <?php selected( $stripe_mode, 'live' ); ?>>Live (produccion)</option>
                        </select>
                    </td>
                </tr>
            </table>

            <fieldset style="border:1px solid #e8e8e8; border-radius:6px; padding:16px; margin-bottom:12px;">
                <legend style="font-weight:600; padding:0 8px; font-size:13px; color:#666;">Claves de Test</legend>
                <table class="form-table" role="presentation" style="margin:0;">
                    <tr>
                        <th scope="row"><label for="stripe_pk_test">Publishable Key</label></th>
                        <td><input type="text" name="stripe_pk_test" id="stripe_pk_test" class="regular-text" value="<?php echo esc_attr( $stripe_pk_test ); ?>" placeholder="pk_test_..."></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="stripe_sk_test">Secret Key</label></th>
                        <td><input type="password" name="stripe_sk_test" id="stripe_sk_test" class="regular-text" value="<?php echo esc_attr( $stripe_sk_test ); ?>" placeholder="sk_test_..."></td>
                    </tr>
                </table>
            </fieldset>

            <fieldset style="border:1px solid #e8e8e8; border-radius:6px; padding:16px;">
                <legend style="font-weight:600; padding:0 8px; font-size:13px; color:#666;">Claves de Produccion</legend>
                <table class="form-table" role="presentation" style="margin:0;">
                    <tr>
                        <th scope="row"><label for="stripe_pk_live">Publishable Key</label></th>
                        <td><input type="text" name="stripe_pk_live" id="stripe_pk_live" class="regular-text" value="<?php echo esc_attr( $stripe_pk_live ); ?>" placeholder="pk_live_..."></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="stripe_sk_live">Secret Key</label></th>
                        <td><input type="password" name="stripe_sk_live" id="stripe_sk_live" class="regular-text" value="<?php echo esc_attr( $stripe_sk_live ); ?>" placeholder="sk_live_..."></td>
                    </tr>
                </table>
            </fieldset>
        </div>

        <!-- ── PAYPAL ─────────────────────────────────────── -->
        <div style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:24px; margin-bottom:24px;">
            <h2 style="margin:0 0 4px; font-size:18px; display:flex; align-items:center; gap:8px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#003087" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 100 20 10 10 0 000-20z"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                PayPal
            </h2>
            <p style="color:#666; margin:0 0 16px; font-size:13px;">Acepta pagos con cuenta PayPal.</p>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="paypal_mode">Modo</label></th>
                    <td>
                        <select name="paypal_mode" id="paypal_mode">
                            <option value="sandbox" <?php selected( $paypal_mode, 'sandbox' ); ?>>Sandbox (pruebas)</option>
                            <option value="live" <?php selected( $paypal_mode, 'live' ); ?>>Live (produccion)</option>
                        </select>
                    </td>
                </tr>
            </table>

            <fieldset style="border:1px solid #e8e8e8; border-radius:6px; padding:16px; margin-bottom:12px;">
                <legend style="font-weight:600; padding:0 8px; font-size:13px; color:#666;">Claves de Sandbox</legend>
                <table class="form-table" role="presentation" style="margin:0;">
                    <tr>
                        <th scope="row"><label for="paypal_client_test">Client ID</label></th>
                        <td><input type="text" name="paypal_client_test" id="paypal_client_test" class="regular-text" value="<?php echo esc_attr( $paypal_client_test ); ?>" placeholder="Client ID de Sandbox"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="paypal_secret_test">Secret</label></th>
                        <td><input type="password" name="paypal_secret_test" id="paypal_secret_test" class="regular-text" value="<?php echo esc_attr( $paypal_secret_test ); ?>" placeholder="Secret de Sandbox"></td>
                    </tr>
                </table>
            </fieldset>

            <fieldset style="border:1px solid #e8e8e8; border-radius:6px; padding:16px;">
                <legend style="font-weight:600; padding:0 8px; font-size:13px; color:#666;">Claves de Produccion</legend>
                <table class="form-table" role="presentation" style="margin:0;">
                    <tr>
                        <th scope="row"><label for="paypal_client_live">Client ID</label></th>
                        <td><input type="text" name="paypal_client_live" id="paypal_client_live" class="regular-text" value="<?php echo esc_attr( $paypal_client_live ); ?>" placeholder="Client ID de Produccion"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="paypal_secret_live">Secret</label></th>
                        <td><input type="password" name="paypal_secret_live" id="paypal_secret_live" class="regular-text" value="<?php echo esc_attr( $paypal_secret_live ); ?>" placeholder="Secret de Produccion"></td>
                    </tr>
                </table>
            </fieldset>
        </div>

        <!-- ── GOOGLE OAUTH ───────────────────────────────── -->
        <div style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:24px; margin-bottom:24px;">
            <h2 style="margin:0 0 4px; font-size:18px; display:flex; align-items:center; gap:8px;">
                <svg width="24" height="24" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google OAuth
            </h2>
            <p style="color:#666; margin:0 0 16px; font-size:13px;">Permite a los usuarios iniciar sesion con su cuenta de Google.</p>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="google_oauth_enabled">Activar</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="google_oauth_enabled" id="google_oauth_enabled" value="1" <?php checked( $google_oauth_enabled, '1' ); ?>>
                            Habilitar inicio de sesion con Google
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="google_client_id">Client ID</label></th>
                    <td><input type="text" name="google_client_id" id="google_client_id" class="regular-text" value="<?php echo esc_attr( $google_client_id ); ?>" placeholder="xxxx.apps.googleusercontent.com"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="google_client_secret">Client Secret</label></th>
                    <td><input type="password" name="google_client_secret" id="google_client_secret" class="regular-text" value="<?php echo esc_attr( $google_client_secret ); ?>" placeholder="GOCSPX-..."></td>
                </tr>
            </table>
        </div>

        <!-- ── MICROSOFT OAUTH ────────────────────────────── -->
        <div style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:24px; margin-bottom:24px;">
            <h2 style="margin:0 0 4px; font-size:18px; display:flex; align-items:center; gap:8px;">
                <svg width="24" height="24" viewBox="0 0 23 23">
                    <rect x="1" y="1" width="10" height="10" fill="#F25022"/>
                    <rect x="12" y="1" width="10" height="10" fill="#7FBA00"/>
                    <rect x="1" y="12" width="10" height="10" fill="#00A4EF"/>
                    <rect x="12" y="12" width="10" height="10" fill="#FFB900"/>
                </svg>
                Microsoft OAuth
            </h2>
            <p style="color:#666; margin:0 0 16px; font-size:13px;">Permite a los usuarios iniciar sesion con su cuenta de Microsoft.</p>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="microsoft_oauth_enabled">Activar</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="microsoft_oauth_enabled" id="microsoft_oauth_enabled" value="1" <?php checked( $microsoft_oauth_enabled, '1' ); ?>>
                            Habilitar inicio de sesion con Microsoft
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="microsoft_client_id">Application (Client) ID</label></th>
                    <td><input type="text" name="microsoft_client_id" id="microsoft_client_id" class="regular-text" value="<?php echo esc_attr( $microsoft_client_id ); ?>" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="microsoft_client_secret">Client Secret</label></th>
                    <td><input type="password" name="microsoft_client_secret" id="microsoft_client_secret" class="regular-text" value="<?php echo esc_attr( $microsoft_client_secret ); ?>" placeholder="Secret value"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="microsoft_tenant_id">Tenant ID</label></th>
                    <td>
                        <input type="text" name="microsoft_tenant_id" id="microsoft_tenant_id" class="regular-text" value="<?php echo esc_attr( $microsoft_tenant_id ); ?>" placeholder="common">
                        <p class="description">Usa <code>common</code> para cuentas personales y de trabajo, o un tenant ID especifico.</p>
                    </td>
                </tr>
            </table>

            <p style="color:#999; font-size:12px; margin:12px 0 0;">
                URI de redireccion: <code><?php echo rest_url( 'integrare/v1/oauth-callback' ); ?></code>
            </p>
        </div>

        <p class="submit">
            <button type="submit" name="integrare_save_settings" class="button button-primary button-hero">
                Guardar Configuracion
            </button>
        </p>
    </form>
</div>
