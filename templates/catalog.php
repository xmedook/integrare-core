<?php
/**
 * Template: Catalog — Landing + Product Grid
 * Route: /tienda/
 * v2 — contenido comercial real
 */
?>

<!-- ══════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════ -->
<section class="int-landing-hero">
    <div class="int-landing-hero-inner">
        <div class="int-landing-hero-badge">
            <span class="int-dot"></span>
            Distribuidora B2B · Zapopan, Jalisco
        </div>
        <h1 class="int-landing-hero-title">
            Tu inventario médico,<br><em>sin ocupar<br>tu almacén.</em>
        </h1>
        <p class="int-landing-hero-subtitle">
            Consumibles especializados en hemodiálisis disponibles cuando los necesitas. Sin inventario propio, sin esperas. Nos encargamos de tener en stock lo que tu clínica u hospital prefiere no almacenar.
        </p>
        <div class="int-landing-hero-actions">
            <a href="<?php echo home_url('/tienda/auth/?vista=registro'); ?>" class="int-btn int-btn-primary int-btn-lg">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                Crear cuenta y ver precios
            </a>
            <a href="https://wa.me/523314603933?text=Hola%2C+me+interesa+conocer+el+cat%C3%A1logo+de+Integrare" target="_blank" class="int-btn int-btn-outline int-btn-lg">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                Consultar por WhatsApp
            </a>
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
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span>Disponibilidad inmediata</span>
        </div>
        <div class="int-trust-divider"></div>
        <div class="int-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><line x1="16" y1="21" x2="16" y2="7"/><line x1="8" y1="21" x2="8" y2="7"/></svg>
            <span>Sin inventario propio necesario</span>
        </div>
        <div class="int-trust-divider"></div>
        <div class="int-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Exclusivo para profesionales</span>
        </div>
        <div class="int-trust-divider"></div>
        <div class="int-trust-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>Bodega en Zapopan, Jalisco</span>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════
     PROPUESTA DE VALOR
════════════════════════════════════════════════════ -->
<section class="int-specialty">
    <div class="int-specialty-inner">
        <div class="int-specialty-text">
            <div class="int-section-eyebrow">Nuestra especialidad</div>
            <h2 class="int-section-title">Proveedor just-in-time<br>para tu unidad<br>de hemodiálisis.</h2>
            <p class="int-section-body">
                Nos especializamos en tener en stock los consumibles que hospitales y clínicas prefieren no almacenar. Cuando los necesitas, están disponibles. Sin comprometer espacio, sin capital inmovilizado.
            </p>
            <ul class="int-specialty-list">
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Kits y catéteres para hemodiálisis
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Soluciones de infusión e irrigación PISA
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Consumibles CAPD y hemofiltración
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Catálogo en expansión según demanda
                </li>
            </ul>
        </div>
        <div class="int-specialty-clients">
            <div class="int-section-eyebrow">¿A quién atendemos?</div>
            <div class="int-client-cards">
                <div class="int-client-card">
                    <div class="int-client-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div>
                        <strong>Clínicas de hemodiálisis</strong>
                        <p>Unidades con consumo frecuente que requieren reabastecimiento ágil y confiable</p>
                    </div>
                </div>
                <div class="int-client-card">
                    <div class="int-client-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
                    </div>
                    <div>
                        <strong>Hospitales</strong>
                        <p>Áreas de nefrología y terapia intensiva que buscan proveedores especializados</p>
                    </div>
                </div>
                <div class="int-client-card">
                    <div class="int-client-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <div>
                        <strong>Otros distribuidores</strong>
                        <p>Mayoristas que quieren ampliar su catálogo con productos especializados</p>
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
                <h3>Crea tu cuenta</h3>
                <p>Regístrate en minutos. El acceso es inmediato — sin aprobación manual. Al entrar ya puedes ver precios y disponibilidad.</p>
            </div>
            <div class="int-how-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </div>
            <div class="int-how-step">
                <div class="int-how-step-num">02</div>
                <h3>Haz tu pedido</h3>
                <p>Elige productos, calcula tu precio por volumen y paga en línea con tarjeta. Para pedidos grandes o crédito, te contactamos directamente.</p>
            </div>
            <div class="int-how-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </div>
            <div class="int-how-step">
                <div class="int-how-step-num">03</div>
                <h3>Retira en bodega</h3>
                <p>Retira tu pedido en nuestra bodega en Zapopan, Jalisco (CP 45138). Próximamente entregas en zona metropolitana de Guadalajara.</p>
            </div>
        </div>
        <div class="int-how-payments">
            <span class="int-how-pay-label">Formas de pago:</span>
            <span class="int-how-pay-chip">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                Tarjeta de crédito/débito
            </span>
            <span class="int-how-pay-chip">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Transferencia / OC
            </span>
            <span class="int-how-pay-chip">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Crédito (previa solicitud)
            </span>
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

    <div class="int-product-grid">
        <?php if ( empty($products) ) : ?>
            <div class="int-empty-state">
                <p>No se encontraron productos<?php echo $search ? ' para "' . esc_html($search) . '"' : ''; ?>.</p>
                <?php if ($search) : ?>
                    <a href="<?php echo home_url('/tienda/'); ?>" class="int-btn int-btn-outline" style="margin-top:16px;">Ver todos</a>
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
     CTA FINAL
════════════════════════════════════════════════════ -->
<?php if ( ! $is_logged_in ) : ?>
<section class="int-landing-cta">
    <div class="int-landing-cta-inner">
        <h2>¿Listo para ver precios?</h2>
        <p>Crea tu cuenta en minutos — acceso inmediato, sin aprobación. Para pedidos por volumen o crédito, también podemos hablar directamente.</p>
        <div class="int-landing-cta-actions">
            <a href="<?php echo home_url('/tienda/auth/?vista=registro'); ?>" class="int-btn int-btn-primary int-btn-lg">Crear cuenta gratuita</a>
            <a href="https://wa.me/523314603933?text=Hola%2C+me+interesa+conocer+el+cat%C3%A1logo+de+Integrare" target="_blank" class="int-btn int-btn-outline int-btn-lg" style="color:#fff;border-color:rgba(255,255,255,0.4);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                Hablar por WhatsApp
            </a>
        </div>
    </div>
</section>
<?php endif; ?>
