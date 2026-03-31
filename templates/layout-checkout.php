<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Integrare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo INTEGRARE_URL; ?>assets/css/integrare-front.css?v=<?php echo filemtime( INTEGRARE_PATH . 'assets/css/integrare-front.css' ); ?>">
    <style>html { margin-top: 0 !important; } #wpadminbar { display: none !important; }</style>
    <?php show_admin_bar( false ); wp_head(); ?>
</head>
<body class="integrare-body int-checkout-body">

    <!-- ── Checkout Header (minimal) ───────────────────── -->
    <header class="int-checkout-header">
        <div class="int-checkout-header-inner">
            <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-checkout-logo">
                <img src="https://integrare.mx/wp-content/uploads/2025/11/integrare-solo.svg" alt="Integrare" height="28">
            </a>
            <div class="int-checkout-secure">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                Compra segura
            </div>
        </div>
    </header>

    <!-- ── Content ─────────────────────────────────── -->
    <?php echo $content; ?>

    <!-- ── Toast Notification ──────────────────────── -->
    <div class="int-toast" id="intToast"></div>

    <script>
        var integrareData = {
            ajaxUrl: '<?php echo esc_js( $ajax_url ); ?>',
            nonce: '<?php echo esc_js( $nonce ); ?>',
            isLoggedIn: <?php echo $is_logged_in ? 'true' : 'false'; ?>,
            loginUrl: '<?php echo esc_js( $login_url ); ?>',
            baseUrl: '<?php echo esc_js( home_url( '/tienda/' ) ); ?>',
            stripePk: '<?php echo esc_js( Integrare_Payments::get_stripe_pk() ); ?>',
            paypalClientId: '<?php echo esc_js( Integrare_Payments::get_paypal_client() ); ?>'
        };
    </script>
    <script src="<?php echo includes_url( 'js/jquery/jquery.min.js' ); ?>"></script>
    <script src="<?php echo INTEGRARE_URL; ?>assets/js/integrare-front.js?v=<?php echo filemtime( INTEGRARE_PATH . 'assets/js/integrare-front.js' ); ?>"></script>
    <?php if ( Integrare_Payments::stripe_enabled() ) : ?>
    <script src="https://js.stripe.com/v3/"></script>
    <?php endif; ?>
    <?php if ( Integrare_Payments::paypal_enabled() ) : ?>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo esc_attr( Integrare_Payments::get_paypal_client() ); ?>&currency=MXN"></script>
    <?php endif; ?>
    <?php wp_footer(); ?>
</body>
</html>
