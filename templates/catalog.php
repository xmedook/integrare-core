<?php
/**
 * Template: Catalog (Product Grid)
 * Route: /tienda/
 */
?>

<!-- ── Hero Section ─────────────────────────────────── -->
<section class="int-hero">
    <div class="int-hero-badge">
        <span class="int-dot"></span>
        Catálogo actualizado
    </div>
    <h1 class="int-hero-title">Distribuidora Farmacéutica B2B</h1>
    <p class="int-hero-subtitle">Consumibles médicos just-in-time para profesionales de la salud</p>

    <form class="int-hero-search" action="<?php echo home_url( '/tienda/' ); ?>" method="GET">
        <input type="text" name="buscar" placeholder="Buscar productos por nombre, SKU o categoría..."
               value="<?php echo esc_attr( $search ?? '' ); ?>" class="int-hero-search-input">
        <button type="submit" class="int-hero-search-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </button>
    </form>
</section>

<!-- ── Category Filters ─────────────────────────────── -->
<?php if ( ! empty( $categories ) ) : ?>
<section class="int-filters">
    <a href="<?php echo home_url( '/tienda/' ); ?>"
       class="int-filter-chip <?php echo empty( $cat_filter ) ? 'active' : ''; ?>">
        <svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Todos
    </a>
    <?php
    $icons = array(
        '<svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="10" x2="15" y2="10"/><line x1="12" y1="7" x2="12" y2="13"/></svg>',
        '<svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v6l3-3"/><path d="M12 8L9 5"/><line x1="12" y1="8" x2="12" y2="22"/><circle cx="12" cy="19" r="2"/></svg>',
        '<svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2H6a2 2 0 0 0-2 2v16l4-3 4 3 4-3 4 3V4a2 2 0 0 0-2-2z"/><line x1="9" y1="10" x2="15" y2="10"/><line x1="12" y1="7" x2="12" y2="13"/></svg>',
        '<svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6l3 7H6L9 3z"/><path d="M6 10v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-8"/></svg>',
        '<svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
        '<svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2h8a2 2 0 0 1 2 2v2a6 6 0 0 1-12 0V4a2 2 0 0 1 2-2z"/><path d="M10 12v8a2 2 0 0 0 4 0v-8"/><line x1="6" y1="22" x2="18" y2="22"/></svg>',
        '<svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c-4.97 0-9-2.69-9-6v-4c0-3.31 4.03-6 9-6s9 2.69 9 6v4c0 3.31-4.03 6-9 6z"/><path d="M12 6v16"/></svg>',
        '<svg class="int-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8 2 5 5.58 5 10c0 3.07 1.86 5.69 4.5 7L12 22l2.5-5c2.64-1.31 4.5-3.93 4.5-7 0-4.42-3-8-7-8z"/></svg>',
    );
    foreach ( $categories as $i => $cat ) :
        $icon = $icons[ $i % count( $icons ) ];
    ?>
    <a href="<?php echo home_url( '/tienda/?categoria=' . $cat['slug'] ); ?>"
       class="int-filter-chip <?php echo $cat_filter === $cat['slug'] ? 'active' : ''; ?>">
        <?php echo $icon . ' ' . esc_html( $cat['name'] ); ?>
    </a>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- ── Product Grid ─────────────────────────────────── -->
<section class="int-product-grid">
    <?php if ( empty( $products ) ) : ?>
        <div class="int-empty-state">
            <p>No se encontraron productos<?php echo $search ? ' para "' . esc_html( $search ) . '"' : ''; ?>.</p>
            <?php if ( $search ) : ?>
                <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-btn int-btn-outline">Ver todos los productos</a>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <?php foreach ( $products as $idx => $prod ) : ?>
        <a href="<?php echo home_url( '/tienda/producto/' . $prod['slug'] . '/' ); ?>"
           class="int-product-card" style="animation-delay: <?php echo $idx * 0.06; ?>s;">

            <div class="int-card-image">
                <?php if ( $prod['thumbnail'] ) : ?>
                    <img src="<?php echo esc_url( $prod['thumbnail'] ); ?>"
                         alt="<?php echo esc_attr( $prod['title'] ); ?>" loading="lazy">
                <?php else : ?>
                    <div class="int-card-placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc"
                             stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>
                <?php endif; ?>
                <div class="int-card-arrow">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="7" y1="17" x2="17" y2="7"/>
                        <polyline points="7 7 17 7 17 17"/>
                    </svg>
                </div>
                <?php if ( $prod['stock'] <= 0 ) : ?>
                    <span class="int-card-badge int-badge-out">Agotado</span>
                <?php endif; ?>
            </div>

            <div class="int-card-body">
                <div class="int-card-meta">
                    <?php if ( $prod['sku'] ) : ?>
                        <span><?php echo esc_html( $prod['sku'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( $prod['category'] ) : ?>
                        <span><?php echo esc_html( $prod['category'] ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="int-card-bottom">
                    <h3 class="int-card-title"><?php echo esc_html( $prod['title'] ); ?></h3>
                    <?php if ( $is_logged_in || ! empty( $prod['show_price'] ) ) : ?>
                        <span class="int-card-price">$<?php echo number_format( $prod['price'], 2 ); ?></span>
                    <?php else : ?>
                        <span class="int-card-price int-price-hidden">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="1" y1="1" x2="23" y2="23"/>
                                <path d="M17 17H7a5 5 0 0 1 0-10"/>
                            </svg>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<!-- ── Show More / Login CTA ────────────────────────── -->
<?php if ( ! $is_logged_in ) : ?>
<section class="int-cta-section">
    <div class="int-cta-card">
        <h3>Inicia sesión para ver precios y comprar</h3>
        <p>Los precios están disponibles exclusivamente para clientes registrados.</p>
        <a href="<?php echo home_url( '/tienda/auth/' ); ?>" class="int-btn int-btn-primary">Iniciar Sesión</a>
        <p style="margin-top:12px;margin-bottom:0;"><a href="<?php echo home_url( '/tienda/auth/?vista=registro' ); ?>" style="color:var(--int-accent);font-size:14px;">Crear una cuenta nueva</a></p>
    </div>
</section>
<?php endif; ?>

<?php if ( $total_pages > 1 && $current_page < $total_pages ) : ?>
<section class="int-show-more">
    <a href="<?php echo home_url( '/tienda/?pag=' . ( $current_page + 1 ) . ( $cat_filter ? '&categoria=' . $cat_filter : '' ) . ( $search ? '&buscar=' . urlencode( $search ) : '' ) ); ?>"
       class="int-btn int-btn-show-more">
        Mostrar más
    </a>
</section>
<?php endif; ?>
