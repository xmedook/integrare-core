<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrare — Panel de Administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=PT+Serif:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo INTEGRARE_URL; ?>assets/css/integrare-admin.css?v=<?php echo filemtime( INTEGRARE_PATH . 'assets/css/integrare-admin.css' ); ?>">
    <?php wp_head(); ?>
</head>
<body class="integrare-admin-body">
    <script>
        var integrareData = {
            ajaxUrl: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
            nonce: '<?php echo esc_js( wp_create_nonce( 'integrare_nonce' ) ); ?>',
            isLoggedIn: true,
            baseUrl: '<?php echo esc_js( home_url( '/tienda/' ) ); ?>',
            adminUrl: '<?php echo esc_js( home_url( '/tienda/admin/' ) ); ?>',
            pluginUrl: '<?php echo esc_js( INTEGRARE_URL ); ?>'
        };
    </script>

    <?php echo $content; ?>

    <script src="<?php echo includes_url( 'js/jquery/jquery.min.js' ); ?>"></script>
    <script src="<?php echo INTEGRARE_URL; ?>assets/js/integrare-admin.js?v=<?php echo filemtime( INTEGRARE_PATH . 'assets/js/integrare-admin.js' ); ?>"></script>
    <?php wp_footer(); ?>
</body>
</html>
