<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrare — Distribuidora Farmacéutica B2B</title>
    <meta name="description" content="Consumibles especializados en hemodiálisis para clínicas, hospitales y distribuidores. Disponibilidad inmediata, sin inventario propio.">
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
                    <a href="<?php echo home_url( '/tienda/auth/?vista=registro' ); ?>" class="int-nav-link int-nav-link-cta">Crear cuenta</a>
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
                <?php if ( ! $is_logged_in ) : ?>
                <a href="https://wa.me/523314603933?text=Hola%2C+me+interesa+el+cat%C3%A1logo+de+Integrare" target="_blank" class="int-whatsapp-btn" aria-label="WhatsApp">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                </a>
                <?php endif; ?>
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

    <!-- ── Footer ─────────────────────────────────────── -->
    <footer class="int-footer">
        <div class="int-footer-inner">
            <div class="int-footer-brand">
                <img src="https://integrare.mx/wp-content/uploads/2025/11/integrare-solo.svg" alt="Integrare" height="22" class="int-footer-logo">
                <p class="int-footer-tagline">Proveedor just-in-time de consumibles médicos especializados para unidades de hemodiálisis.</p>
                <a href="https://wa.me/523314603933?text=Hola%2C+me+interesa+el+cat%C3%A1logo+de+Integrare" target="_blank" class="int-footer-wa">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                    +52 33 1460 3933
                </a>
            </div>

            <div class="int-footer-col">
                <h4 class="int-footer-col-title">Catálogo</h4>
                <ul class="int-footer-links">
                    <li><a href="<?php echo home_url('/tienda/'); ?>">Todos los productos</a></li>
                    <?php foreach ( array_slice($categories ?? [], 0, 4) as $cat ) : ?>
                    <li><a href="<?php echo home_url('/tienda/?categoria=' . $cat['slug']); ?>"><?php echo esc_html($cat['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="int-footer-col">
                <h4 class="int-footer-col-title">Mi cuenta</h4>
                <ul class="int-footer-links">
                    <?php if ( $is_logged_in ) : ?>
                    <li><a href="<?php echo home_url('/tienda/cuenta/'); ?>">Mi perfil</a></li>
                    <li><a href="<?php echo home_url('/tienda/carrito/'); ?>">Carrito</a></li>
                    <?php else : ?>
                    <li><a href="<?php echo home_url('/tienda/auth/?vista=registro'); ?>">Crear cuenta</a></li>
                    <li><a href="<?php echo home_url('/tienda/auth/'); ?>">Iniciar sesión</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="int-footer-col">
                <h4 class="int-footer-col-title">Contacto</h4>
                <ul class="int-footer-links int-footer-contact">
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Zapopan, Jalisco. CP 45138
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <a href="mailto:ventas@integrare.mx">ventas@integrare.mx</a>
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                        <a href="https://wa.me/523314603933" target="_blank">+52 33 1460 3933</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="int-footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> Integrare Medical. Todos los derechos reservados.</span>
            <div class="int-footer-bottom-links">
                <a href="<?php echo get_privacy_policy_url(); ?>">Aviso de privacidad</a>
            </div>
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
    (function() {
        var logoutLink = document.getElementById('intLogoutLink');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                var xhr = new XMLHttpRequest();
                xhr.open('POST', integrareData.ajaxUrl);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() { window.location.href = integrareData.baseUrl; };
                xhr.send('action=integrare_logout&nonce=' + encodeURIComponent(integrareData.nonce));
            });
        }
    })();
    </script>
    <?php wp_footer(); ?>
</body>
</html>
