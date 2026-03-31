<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrare — Distribuidora Farmacéutica B2B</title>
    <meta name="description" content="Distribuidora de consumibles médicos B2B. Productos farmacéuticos al mejor precio por volumen.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=PT+Serif:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo INTEGRARE_URL; ?>assets/css/integrare-front.css?v=<?php echo filemtime( INTEGRARE_PATH . 'assets/css/integrare-front.css' ); ?>">
    <?php wp_head(); ?>
</head>
<body class="integrare-body">

    <!-- ── Navigation ─────────────────────────────────── -->
    <header class="int-header">
        <div class="int-header-inner">
            <div class="int-logo-pill">
                <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-logo" aria-label="Integrare Home">
                    <img src="https://integrare.mx/wp-content/uploads/2025/11/integrare-solo.svg" alt="Integrare" height="25">
                </a>
            </div>

            <nav class="int-nav-pill">
                <a href="<?php echo home_url( '/tienda/' ); ?>"
                   class="int-nav-link <?php echo $current_page === 'catalogo' ? 'active' : ''; ?>">Catálogo</a>
                <?php if ( ! $is_logged_in ) : ?>
                    <a href="<?php echo home_url( '/tienda/auth/' ); ?>" class="int-nav-link">Iniciar Sesión</a>
                <?php else : ?>
                    <a href="<?php echo home_url( '/tienda/cuenta/' ); ?>"
                       class="int-nav-link <?php echo $current_page === 'cuenta' ? 'active' : ''; ?>">Mi Cuenta</a>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <a href="<?php echo home_url( '/tienda/admin/' ); ?>"
                           class="int-nav-link <?php echo $current_page === 'admin' ? 'active' : ''; ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                            Admin</a>
                    <?php endif; ?>
                    <a href="#" class="int-nav-link" id="intLogoutLink">Cerrar Sesión</a>
                <?php endif; ?>
            </nav>

            <div class="int-header-actions">
                <a href="<?php echo home_url( '/tienda/carrito/' ); ?>" class="int-cart-toggle" aria-label="Carrito">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                    <?php if ( $cart_count > 0 ) : ?>
                        <span class="int-cart-badge" id="intCartBadge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <button class="int-search-toggle" aria-label="Buscar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- ── Search Overlay ─────────────────────────────── -->
    <div class="int-search-overlay" id="intSearchOverlay">
        <div class="int-search-box">
            <form action="<?php echo home_url( '/tienda/' ); ?>" method="GET">
                <input type="text" name="buscar" placeholder="Buscar productos..."
                       class="int-search-input" value="<?php echo esc_attr( $search ?? '' ); ?>" autofocus>
                <button type="submit" class="int-search-submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
            </form>
            <button class="int-search-close" aria-label="Cerrar">&times;</button>
        </div>
    </div>

    <!-- ── Main Content ───────────────────────────────── -->
    <main class="int-main">
        <?php echo $content; ?>
    </main>

    <footer class="int-footer">
        <div class="int-footer-simple">
            <span>&copy; <?php echo date('Y'); ?> Integrare. Todos los derechos reservados.</span>
            <a href="mailto:ventas@integrare.mx">ventas@integrare.mx</a>
        </div>
    </footer>

    <!-- ── Cart Drawer ──────────────────────────────────── -->
    <div class="int-cart-drawer-overlay" id="intCartDrawerOverlay"></div>
    <aside class="int-cart-drawer" id="intCartDrawer">
        <div class="int-cart-drawer-header">
            <h3>Tu Carrito</h3>
            <button class="int-cart-drawer-close" id="intCartDrawerClose" aria-label="Cerrar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="int-cart-drawer-body" id="intCartDrawerBody">
            <p class="int-cart-drawer-empty">Tu carrito está vacío.</p>
        </div>
        <div class="int-cart-drawer-footer" id="intCartDrawerFooter" style="display:none;">
            <div class="int-cart-drawer-total">
                <span>Total</span>
                <strong id="intCartDrawerTotal">$0.00</strong>
            </div>
            <div class="int-cart-drawer-actions">
                <a href="<?php echo home_url( '/tienda/carrito/' ); ?>" class="int-btn int-btn-outline int-btn-block">Ver Carrito</a>
                <a href="<?php echo home_url( '/tienda/checkout/' ); ?>" class="int-btn int-btn-primary int-btn-block">Finalizar Compra</a>
            </div>
        </div>
    </aside>

    <!-- ── Toast Notification ─────────────────────────── -->
    <div class="int-toast" id="intToast"></div>

    <script>
        var integrareData = {
            ajaxUrl: '<?php echo esc_js( $ajax_url ); ?>',
            nonce: '<?php echo esc_js( $nonce ); ?>',
            isLoggedIn: <?php echo $is_logged_in ? 'true' : 'false'; ?>,
            loginUrl: '<?php echo esc_js( $login_url ); ?>',
            baseUrl: '<?php echo esc_js( home_url( '/tienda/' ) ); ?>'
        };
    </script>
    <script src="<?php echo includes_url( 'js/jquery/jquery.min.js' ); ?>"></script>
    <script src="<?php echo INTEGRARE_URL; ?>assets/js/integrare-front.js?v=<?php echo filemtime( INTEGRARE_PATH . 'assets/js/integrare-front.js' ); ?>"></script>
    <script>
    // Logout handler
    (function() {
        var logoutLink = document.getElementById('intLogoutLink');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                var xhr = new XMLHttpRequest();
                xhr.open('POST', integrareData.ajaxUrl);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    window.location.href = integrareData.baseUrl;
                };
                xhr.send('action=integrare_logout&nonce=' + encodeURIComponent(integrareData.nonce));
            });
        }
    })();
    </script>
    <?php wp_footer(); ?>
</body>
</html>
