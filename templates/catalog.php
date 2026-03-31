<?php
/**
 * Template: Catalog — Landing + Product Grid
 * Route: /tienda/
 */
?>

<!-- ══════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════ -->
<section class="int-landing-hero">
    <div class="int-landing-hero-inner">
        <div class="int-landing-hero-badge">
            <span class="int-dot"></span>
            Distribuidora B2B · Especialistas en Hemodiálisis
        </div>
        <h1 class="int-landing-hero-title">
            Equipo médico<br>especializado,<br><em>directo a tu clínica.</em>
        </h1>
        <p class="int-landing-hero-subtitle">
            Consumibles de hemodiálisis y soluciones de infusión para clínicas, hospitales y distribuidores. Precios por volumen, inventario disponible, entrega confiable.
        </p>
        <div class="int-landing-hero-actions">
            <a href="#int-catalogo" class="int-btn int-btn-primary int-btn-lg">Ver catálogo</a>
            <?php if ( ! $is_logged_in ) : ?>
                <a href="<?php echo home_url('/tienda/auth/?vista=registro'); ?>" class="int-btn int-btn-outline int-btn-lg">Crear cuenta</a>
            <?php else : ?>
                <a href="<?php echo home_url('/tienda/cuenta/'); ?>" class="int-btn int-btn-outline int-btn-lg">Mi cuenta</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="int-landing-hero-visual">
        <div class="int-landing-hero-card">
            <div class="int-hero-card-label">Categorías disponibles</div>
            <?php foreach ( array_slice($categories, 0, 4) as $cat ) : ?>
            <div class="int-hero-cat-row">
                <span class="int-hero-cat-dot"></span>
                <span><?php echo esc_html($cat['name']); ?></span>
                <span class="int-hero-cat-count"><?php echo $cat['count']; ?> productos</span>
            </div>
            <?php endforeach; ?>
            <div class="int-hero-card-footer">
                <span class="int-dot"></span> Catálogo en crecimiento continuo
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     TRUST BAR
════════════════════════════════════════════════════ -->
<section class="int-trust-bar">
    <div class="int-trust-bar-inner">
        <div class="int-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Proveedores certificados</span>
        </div>
        <div class="int-trust-divider"></div>
        <div class="int-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Entrega just-in-time</span>
        </div>
        <div class="int-trust-divider"></div>
        <div class="int-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Exclusivo para profesionales</span>
        </div>
        <div class="int-trust-divider"></div>
        <div class="int-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Precios por volumen</span>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     ESPECIALIDAD
════════════════════════════════════════════════════ -->
<section class="int-specialty">
    <div class="int-specialty-inner">
        <div class="int-specialty-text">
            <div class="int-section-eyebrow">Especialización</div>
            <h2 class="int-section-title">Enfocados en<br>hemodiálisis y<br>cuidado renal.</h2>
            <p class="int-section-body">
                Somos distribuidores especializados en insumos para unidades de hemodiálisis. Trabajamos con clínicas de diálisis, hospitales y otros distribuidores que requieren productos de calidad con disponibilidad constante.
            </p>
            <ul class="int-specialty-list">
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Kits y catéteres para hemodiálisis
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Soluciones de infusión e irrigación
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Consumibles CAPD y hemofiltración
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Catálogo en expansión continua
                </li>
            </ul>
        </div>
        <div class="int-specialty-clients">
            <div class="int-section-eyebrow">¿Quiénes son nuestros clientes?</div>
            <div class="int-client-cards">
                <div class="int-client-card">
                    <div class="int-client-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div>
                        <strong>Clínicas de hemodiálisis</strong>
                        <p>Unidades especializadas con consumo frecuente y predecible</p>
                    </div>
                </div>
                <div class="int-client-card">
                    <div class="int-client-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
                    </div>
                    <div>
                        <strong>Hospitales</strong>
                        <p>Áreas de nefrología y terapia intensiva con requerimientos específicos</p>
                    </div>
                </div>
                <div class="int-client-card">
                    <div class="int-client-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <div>
                        <strong>Otros distribuidores</strong>
                        <p>Mayoristas y distribuidores regionales interesados en ampliar su catálogo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     CÓMO FUNCIONA
════════════════════════════════════════════════════ -->
<section class="int-how">
    <div class="int-how-inner">
        <div class="int-section-eyebrow" style="text-align:center;">Proceso</div>
        <h2 class="int-section-title" style="text-align:center;">Así de sencillo.</h2>
        <div class="int-how-steps">
            <div class="int-how-step">
                <div class="int-how-step-num">01</div>
                <h3>Solicita acceso</h3>
                <p>Crea tu cuenta como cliente registrado. Revisamos tu perfil y te damos acceso al catálogo completo con precios.</p>
            </div>
            <div class="int-how-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </div>
            <div class="int-how-step">
                <div class="int-how-step-num">02</div>
                <h3>Cotiza por volumen</h3>
                <p>Explora el catálogo, selecciona cantidades y calcula automáticamente tus precios con descuentos por volumen.</p>
            </div>
            <div class="int-how-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </div>
            <div class="int-how-step">
                <div class="int-how-step-num">03</div>
                <h3>Recibe en tu unidad</h3>
                <p>Confirmamos tu pedido y coordinamos la entrega directamente a tu clínica u hospital con tiempos acordados.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     CATÁLOGO
════════════════════════════════════════════════════ -->
<section class="int-catalog-section" id="int-catalogo">
    <div class="int-catalog-header">
        <div>
            <div class="int-section-eyebrow">Catálogo</div>
            <h2 class="int-section-title" style="margin-bottom:0;">Productos disponibles.</h2>
        </div>
        <form class="int-catalog-search" action="<?php echo home_url('/tienda/'); ?>" method="GET">
            <input type="text" name="buscar" placeholder="Buscar por nombre o SKU..."
                   value="<?php echo esc_attr($search ?? ''); ?>" class="int-hero-search-input">
            <button type="submit" class="int-hero-search-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </form>
    </div>

    <!-- Filtros de categoría -->
    <?php if ( ! empty($categories) ) : ?>
    <div class="int-filters">
        <a href="<?php echo home_url('/tienda/'); ?>"
           class="int-filter-chip <?php echo empty($cat_filter) ? 'active' : ''; ?>">
            <svg class="int-filter-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Todos
        </a>
        <?php foreach ($categories as $cat) : ?>
        <a href="<?php echo home_url('/tienda/?categoria=' . $cat['slug']); ?>"
           class="int-filter-chip <?php echo $cat_filter === $cat['slug'] ? 'active' : ''; ?>">
            <?php echo esc_html($cat['name']); ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Grid de productos -->
    <div class="int-product-grid">
        <?php if ( empty($products) ) : ?>
            <div class="int-empty-state">
                <p>No se encontraron productos<?php echo $search ? ' para "' . esc_html($search) . '"' : ''; ?>.</p>
                <?php if ($search) : ?>
                    <a href="<?php echo home_url('/tienda/'); ?>" class="int-btn int-btn-outline" style="margin-top:16px;">Ver todos los productos</a>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <?php foreach ($products as $idx => $prod) : ?>
            <a href="<?php echo home_url('/tienda/producto/' . $prod['slug'] . '/'); ?>"
               class="int-product-card" style="animation-delay: <?php echo $idx * 0.06; ?>s;">
                <div class="int-card-image">
                    <?php if ($prod['thumbnail']) : ?>
                        <img src="<?php echo esc_url($prod['thumbnail']); ?>"
                             alt="<?php echo esc_attr($prod['title']); ?>" loading="lazy">
                    <?php else : ?>
                        <div class="int-card-placeholder">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div class="int-card-arrow">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>
                        </svg>
                    </div>
                    <?php if ($prod['stock'] <= 0) : ?>
                        <span class="int-card-badge int-badge-out">Agotado</span>
                    <?php endif; ?>
                </div>
                <div class="int-card-body">
                    <div class="int-card-meta">
                        <?php if ($prod['sku']) : ?><span><?php echo esc_html($prod['sku']); ?></span><?php endif; ?>
                        <?php if ($prod['category']) : ?><span><?php echo esc_html($prod['category']); ?></span><?php endif; ?>
                    </div>
                    <div class="int-card-bottom">
                        <h3 class="int-card-title"><?php echo esc_html($prod['title']); ?></h3>
                        <?php if ($is_logged_in || !empty($prod['show_price'])) : ?>
                            <span class="int-card-price">$<?php echo number_format($prod['price'], 2); ?></span>
                        <?php else : ?>
                            <span class="int-card-price int-price-hidden">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M17 17H7a5 5 0 0 1 0-10"/></svg>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1 && $current_page < $total_pages) : ?>
    <div class="int-show-more">
        <a href="<?php echo home_url('/tienda/?pag=' . ($current_page + 1) . ($cat_filter ? '&categoria=' . $cat_filter : '') . ($search ? '&buscar=' . urlencode($search) : '')); ?>"
           class="int-btn int-btn-show-more">Mostrar más</a>
    </div>
    <?php endif; ?>
</section>

<!-- ══════════════════════════════════════════════════
     CTA FINAL (solo no logueados)
════════════════════════════════════════════════════ -->
<?php if ( ! $is_logged_in ) : ?>
<section class="int-landing-cta">
    <div class="int-landing-cta-inner">
        <h2>¿Listo para solicitar acceso?</h2>
        <p>Los precios y la disponibilidad son exclusivos para clientes registrados. El proceso es rápido.</p>
        <div class="int-landing-cta-actions">
            <a href="<?php echo home_url('/tienda/auth/?vista=registro'); ?>" class="int-btn int-btn-primary int-btn-lg">Crear cuenta gratuita</a>
            <a href="<?php echo home_url('/tienda/auth/'); ?>" class="int-btn int-btn-outline int-btn-lg" style="color:#fff;border-color:rgba(255,255,255,0.4);">Ya tengo cuenta</a>
        </div>
    </div>
</section>
<?php endif; ?>
