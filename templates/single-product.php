<?php
/**
 * Template: Single Product
 * Route: /tienda/producto/{slug}/
 */

if ( ! isset( $product ) ) return;
$p  = $product;
$dt = $discount_table ?? array();
$dp = $default_pricing ?? array();
$stock_boxes = $p['upb'] > 0 ? floor( $p['stock'] / $p['upb'] ) : 0;
?>

<section class="int-product-detail">
    <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
        </svg>
        Volver al catálogo
    </a>

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
            </div>

            <h1 class="int-product-title"><?php echo esc_html( $p['title'] ); ?></h1>

            <?php if ( $p['excerpt'] ) : ?>
                <p class="int-product-excerpt"><?php echo esc_html( $p['excerpt'] ); ?></p>
            <?php endif; ?>

            <?php if ( $is_logged_in || ! empty( $p['show_price'] ) ) : ?>

                <!-- ── Discount Table ─────────────────── -->
                <div class="int-discount-table-wrap">

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
                    <h3 class="int-section-label">Calcular Pedido</h3>

                    <div class="int-calc-input-row">
                        <label for="intQtyInput">Cantidad de Cajas:</label>
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
                            en <strong id="intTotalBoxes">1</strong> caja<?php echo 1 > 1 ? 's' : ''; ?>
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
                            Agregar al Carrito
                        </button>
                    <?php else : ?>
                        <button class="int-btn int-btn-disabled int-btn-full" disabled>
                            Producto Agotado
                        </button>
                    <?php endif; ?>

                    <p class="int-stock-info">
                        Stock disponible: <strong><?php echo number_format( $p['stock'] ); ?></strong> unidades
                        (<?php echo $stock_boxes; ?> cajas)
                    </p>
                </div>

            <?php else : ?>
                <!-- ── Login Gate ──────────────────────── -->
                <div class="int-login-gate">
                    <div class="int-login-gate-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <h3>Inicia sesión para ver precios</h3>
                    <p>Los precios y la compra están disponibles solo para clientes registrados.</p>
                    <a href="<?php echo home_url( '/tienda/auth/' ); ?>" class="int-btn int-btn-primary">
                        Iniciar Sesión
                    </a>
                    <p style="margin-top:10px;font-size:13px;color:var(--int-text-secondary);">
                        <a href="<?php echo home_url( '/tienda/auth/?vista=registro' ); ?>" style="color:var(--int-accent);">Crear una cuenta</a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- ── Description ─────────────────────── -->
            <?php if ( ! empty( $p['description'] ) ) : ?>
            <div class="int-product-description">
                <h3 class="int-section-label">Descripción</h3>
                <div class="int-product-content">
                    <?php echo $p['description']; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
