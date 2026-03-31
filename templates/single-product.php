<?php
/**
 * Template: Single Product
 * Route: /tienda/producto/{slug}/
 * v2 — breadcrumb, related products, login gate mejorado
 */

if ( ! isset( $product ) ) return;
$p  = $product;
$dt = $discount_table ?? array();
$dp = $default_pricing ?? array();
$stock_boxes = $p['upb'] > 0 ? floor( $p['stock'] / $p['upb'] ) : 0;

// Related products (same category, excluding current)
$related = array();
if ( ! empty( $p['category'] ) ) {
    $cat_terms = get_terms( array(
        'taxonomy'   => 'producto_categoria',
        'hide_empty' => true,
        'name'       => $p['category'][0] ?? '',
    ) );
    if ( ! is_wp_error( $cat_terms ) && ! empty( $cat_terms ) ) {
        $related_posts = get_posts( array(
            'post_type'      => 'farmacia_product',
            'post_status'    => 'publish',
            'posts_per_page' => 4,
            'post__not_in'   => array( $p['id'] ),
            'tax_query'      => array( array(
                'taxonomy' => 'producto_categoria',
                'field'    => 'term_id',
                'terms'    => wp_list_pluck( $cat_terms, 'term_id' ),
            ) ),
        ) );
        foreach ( $related_posts as $rp ) {
            $rcats = wp_get_post_terms( $rp->ID, 'producto_categoria', array( 'fields' => 'names' ) );
            $related[] = array(
                'id'        => $rp->ID,
                'title'     => $rp->post_title,
                'slug'      => $rp->post_name,
                'thumbnail' => get_the_post_thumbnail_url( $rp->ID, 'full' ) ?: '',
                'sku'       => get_post_meta( $rp->ID, '_integrare_sku', true ),
                'price'     => (float) get_post_meta( $rp->ID, '_integrare_precio_unitario', true ),
                'stock'     => (int) get_post_meta( $rp->ID, '_integrare_stock', true ),
                'category'  => ! empty( $rcats ) ? $rcats[0] : '',
                'show_price' => (bool) get_post_meta( $rp->ID, '_integrare_mostrar_precio', true ),
            );
        }
    }
}
?>

<section class="int-product-detail">

    <!-- ── Breadcrumb ─────────────────────────────── -->
    <nav class="int-breadcrumb">
        <a href="<?php echo home_url('/tienda/'); ?>">Catálogo</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        <?php if ( ! empty( $p['category'] ) ) : ?>
            <a href="<?php echo home_url('/tienda/?categoria=' . sanitize_title($p['category'][0])); ?>"><?php echo esc_html($p['category'][0]); ?></a>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        <?php endif; ?>
        <span><?php echo esc_html($p['title']); ?></span>
    </nav>

    <div class="int-product-layout">
        <!-- ── Image ──────────────────────────────────── -->
        <div class="int-product-image-wrap">
            <?php if ( $p['thumbnail'] ) : ?>
                <img src="<?php echo esc_url( $p['thumbnail'] ); ?>"
                     alt="<?php echo esc_attr( $p['title'] ); ?>" class="int-product-image">
            <?php else : ?>
                <div class="int-product-placeholder">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ccc"
                         stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Info ───────────────────────────────────── -->
        <div class="int-product-info">
            <div class="int-product-meta-row">
                <?php if ( $p['sku'] ) : ?>
                    <span class="int-meta-tag"><?php echo esc_html( $p['sku'] ); ?></span>
                <?php endif; ?>
                <?php if ( ! empty( $p['category'] ) ) : ?>
                    <span class="int-meta-tag"><?php echo esc_html( implode( ', ', $p['category'] ) ); ?></span>
                <?php endif; ?>
                <?php if ( $p['stock'] > 0 ) : ?>
                    <span class="int-meta-tag int-meta-stock">
                        <span class="int-dot" style="width:6px;height:6px;display:inline-block;margin-right:4px;"></span>
                        Disponible
                    </span>
                <?php else : ?>
                    <span class="int-meta-tag int-meta-out">Agotado</span>
                <?php endif; ?>
            </div>

            <h1 class="int-product-title"><?php echo esc_html( $p['title'] ); ?></h1>

            <?php if ( $p['excerpt'] ) : ?>
                <p class="int-product-excerpt"><?php echo esc_html( $p['excerpt'] ); ?></p>
            <?php endif; ?>

            <?php if ( $is_logged_in || ! empty( $p['show_price'] ) ) : ?>

                <!-- ── Discount Table ─────────────────── -->
                <div class="int-discount-table-wrap">
                    <p class="int-section-label" style="margin-bottom:10px;">Precios por volumen</p>
                    <table class="int-discount-table" id="intDiscountTable">
                        <thead>
                            <tr>
                                <th>Volumen</th>
                                <th>Precio × Unidad</th>
                                <th>Ahorro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $dt as $i => $row ) : ?>
                            <tr data-tier="<?php echo $i; ?>"
                                class="<?php echo $i === 0 ? 'int-tier-active' : ''; ?>">
                                <td>≥ <?php echo $row['min_cajas']; ?> caja<?php echo $row['min_cajas'] > 1 ? 's' : ''; ?></td>
                                <td>$<?php echo number_format( $row['price_per_unit'], 2 ); ?></td>
                                <td>
                                    <?php if ( $row['discount_pct'] > 0 ) : ?>
                                        <span class="int-savings-badge"><?php echo $row['discount_pct']; ?>% OFF</span>
                                    <?php else : ?>
                                        <span class="int-no-savings">Precio base</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ── Calculator ─────────────────────── -->
                <div class="int-calculator">
                    <h3 class="int-section-label">Calcular pedido</h3>
                    <div class="int-calc-input-row">
                        <label for="intQtyInput">Cantidad de cajas:</label>
                        <div class="int-qty-control">
                            <button class="int-qty-btn" id="intQtyMinus" type="button">−</button>
                            <input type="number" id="intQtyInput" value="1" min="1"
                                   max="<?php echo $stock_boxes; ?>"
                                   data-product-id="<?php echo $p['id']; ?>"
                                   data-upb="<?php echo $p['upb']; ?>">
                            <button class="int-qty-btn" id="intQtyPlus" type="button">+</button>
                        </div>
                    </div>
                    <div class="int-calc-result" id="intCalcResult">
                        <div class="int-calc-message" id="intCalcMessage">
                            Recibirás <strong id="intTotalUnits"><?php echo $p['upb']; ?></strong> unidades
                            en <strong id="intTotalBoxes">1</strong> caja
                        </div>
                        <div class="int-calc-breakdown">
                            <div class="int-calc-row">
                                <span>Precio por unidad:</span>
                                <span id="intUnitPrice">$<?php echo number_format( $dp['discounted_price'] ?? $p['price'], 2 ); ?></span>
                            </div>
                            <div class="int-calc-row">
                                <span>Descuento aplicado:</span>
                                <span id="intDiscountPct"><?php echo $dp['discount_pct'] ?? 0; ?>%</span>
                            </div>
                            <div class="int-calc-row int-calc-total">
                                <span>Total a pagar:</span>
                                <span id="intLineTotal">$<?php echo number_format( $dp['line_total'] ?? $p['price'] * $p['upb'], 2 ); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php if ( $p['stock'] > 0 ) : ?>
                        <button class="int-btn int-btn-primary int-btn-full" id="intAddToCart"
                                data-product-id="<?php echo $p['id']; ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                            Agregar al Carrito
                        </button>
                    <?php else : ?>
                        <button class="int-btn int-btn-disabled int-btn-full" disabled>Producto Agotado</button>
                    <?php endif; ?>
                    <p class="int-stock-info">
                        Stock disponible: <strong><?php echo number_format( $p['stock'] ); ?></strong> unidades (<?php echo $stock_boxes; ?> cajas)
                    </p>
                </div>

            <?php else : ?>
                <!-- ── Login Gate ──────────────────────── -->
                <div class="int-login-gate">
                    <div class="int-login-gate-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <h3>Crea tu cuenta para ver precios</h3>
                    <p>El catálogo con precios es exclusivo para clientes registrados. El acceso es gratuito e inmediato.</p>
                    <a href="<?php echo home_url('/tienda/auth/?vista=registro'); ?>" class="int-btn int-btn-primary int-btn-lg">
                        Crear cuenta gratuita
                    </a>
                    <p style="margin-top:14px;font-size:13px;color:var(--int-text-secondary);">
                        ¿Ya tienes cuenta? <a href="<?php echo home_url('/tienda/auth/'); ?>" style="color:var(--int-accent);">Inicia sesión</a>
                    </p>
                    <div class="int-gate-wa">
                        <a href="https://wa.me/523314603933?text=Hola%2C+me+interesa+el+producto+<?php echo urlencode($p['title']); ?>" target="_blank">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                            ¿Prefieres consultar por WhatsApp?
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ── Description ─────────────────────── -->
            <?php if ( ! empty( $p['description'] ) ) : ?>
            <div class="int-product-description">
                <h3 class="int-section-label">Descripción</h3>
                <div class="int-product-content"><?php echo $p['description']; ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ── Related Products ───────────────────────────── -->
<?php if ( ! empty( $related ) ) : ?>
<section class="int-related">
    <div class="int-related-inner">
        <div class="int-section-eyebrow">Misma categoría</div>
        <h2 class="int-section-title" style="margin-bottom:32px;">También te puede interesar.</h2>
        <div class="int-related-grid">
            <?php foreach ( $related as $idx => $rel ) : ?>
            <a href="<?php echo home_url('/tienda/producto/' . $rel['slug'] . '/'); ?>"
               class="int-product-card" style="animation-delay:<?php echo $idx * 0.06; ?>s;">
                <div class="int-card-image">
                    <?php if ($rel['thumbnail']) : ?>
                        <img src="<?php echo esc_url($rel['thumbnail']); ?>" alt="<?php echo esc_attr($rel['title']); ?>" loading="lazy">
                    <?php else : ?>
                        <div class="int-card-placeholder">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="int-card-arrow">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                    </div>
                </div>
                <div class="int-card-body">
                    <div class="int-card-meta">
                        <?php if ($rel['sku']) : ?><span><?php echo esc_html($rel['sku']); ?></span><?php endif; ?>
                        <?php if ($rel['category']) : ?><span><?php echo esc_html($rel['category']); ?></span><?php endif; ?>
                    </div>
                    <div class="int-card-bottom">
                        <h3 class="int-card-title"><?php echo esc_html($rel['title']); ?></h3>
                        <?php if ($is_logged_in || !empty($rel['show_price'])) : ?>
                            <span class="int-card-price">$<?php echo number_format($rel['price'], 2); ?></span>
                        <?php else : ?>
                            <span class="int-card-price int-price-hidden">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M17 17H7a5 5 0 0 1 0-10"/></svg>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
