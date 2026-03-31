<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticación — Integrare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=PT+Serif:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo INTEGRARE_URL; ?>assets/css/integrare-front.css?v=<?php echo filemtime( INTEGRARE_PATH . 'assets/css/integrare-front.css' ); ?>">
    <style>html { margin-top: 0 !important; } #wpadminbar { display: none !important; }</style>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <?php show_admin_bar( false ); wp_head(); ?>
</head>
<body class="integrare-body int-auth-body">

    <!-- ── Content (no header, no footer) ──────────── -->
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
            googleClientId: '<?php echo esc_js( get_option( "integrare_google_client_id", "" ) ); ?>'
        };
    </script>
    <script src="<?php echo includes_url( 'js/jquery/jquery.min.js' ); ?>"></script>
    <script src="<?php echo INTEGRARE_URL; ?>assets/js/integrare-front.js?v=<?php echo filemtime( INTEGRARE_PATH . 'assets/js/integrare-front.js' ); ?>"></script>
    <?php wp_footer(); ?>
</body>
</html>
