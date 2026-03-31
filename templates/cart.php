<?php
/**
 * Template: Cart
 * Route: /tienda/carrito/
 */

if ( ! $is_logged_in ) : ?>
<section class="int-auth-required">
    <div class="int-login-gate">
        <div class="int-login-gate-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
        </div>
        <h2>Inicia sesión para ver tu carrito</h2>
        <p>Necesitas estar registrado para agregar productos y realizar pedidos.</p>
        <a href="<?php echo esc_url( $login_url ); ?>" class="int-btn int-btn-primary">Iniciar Sesión</a>
    </div>
</section>
<?php return; endif; ?>

<section class="int-cart-page">
    <div class="int-page-header">
        <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
            Seguir comprando
        </a>
        <h1 class="int-page-title">Tu Carrito</h1>
    </div>

    <div id="intCartContainer">
        <?php if ( empty( $cart['items'] ) ) : ?>
            <div class="int-empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ccc"
                     stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <h3>Tu carrito está vacío</h3>
                <p>Explora nuestro catálogo y agrega productos.</p>
                <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-btn int-btn-primary">Ver Catálogo</a>
            </div>
        <?php else : ?>
            <div class="int-cart-layout">
                <!-- ── Cart Items ─────────────────── -->
                <div class="int-cart-items">
                    <?php foreach ( $cart['items'] as $item ) :
                        $pr = $item['pricing'];
                    ?>
                    <div class="int-cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                        <div class="int-cart-item-image">
                            <?php if ( $item['thumbnail'] ) : ?>
                                <img src="<?php echo esc_url( $item['thumbnail'] ); ?>"
                                     alt="<?php echo esc_attr( $item['title'] ); ?>">
                            <?php else : ?>
                                <div class="int-card-placeholder-sm"></div>
                            <?php endif; ?>
                        </div>
                        <div class="int-cart-item-info">
                            <h3 class="int-cart-item-title">
                                <a href="<?php echo home_url( '/tienda/producto/' . $item['slug'] . '/' ); ?>">
                                    <?php echo esc_html( $item['title'] ); ?>
                                </a>
                            </h3>
                            <p class="int-cart-item-meta">
                                <?php echo $pr['total_units']; ?> unidades · <?php echo $pr['discount_pct']; ?>% desc.
                                · $<?php echo number_format( $pr['discounted_price'], 2 ); ?>/ud
                            </p>
                        </div>
                        <div class="int-cart-item-qty">
                            <div class="int-qty-control int-qty-sm">
                                <button class="int-qty-btn int-cart-qty-btn" data-action="decrease"
                                        data-product-id="<?php echo $item['product_id']; ?>">−</button>
                                <input type="number" value="<?php echo $item['quantity']; ?>" min="1"
                                       class="int-cart-qty-input" data-product-id="<?php echo $item['product_id']; ?>">
                                <button class="int-qty-btn int-cart-qty-btn" data-action="increase"
                                        data-product-id="<?php echo $item['product_id']; ?>">+</button>
                            </div>
                            <span class="int-qty-label"><?php echo $item['quantity']; ?> caja<?php echo $item['quantity'] > 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="int-cart-item-total">
                            <strong>$<?php echo number_format( $pr['line_total'], 2 ); ?></strong>
                        </div>
                        <button class="int-cart-remove" data-product-id="<?php echo $item['product_id']; ?>"
                                aria-label="Eliminar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- ── Cart Summary ───────────────── -->
                <div class="int-cart-summary">
                    <h3>Resumen del Pedido</h3>
                    <div class="int-summary-row">
                        <span>Productos (<?php echo $cart['total_lines']; ?>)</span>
                        <span><?php echo $cart['item_count']; ?> cajas</span>
                    </div>
                    <div class="int-summary-row int-summary-total">
                        <span>Total</span>
                        <span>$<?php echo number_format( $cart['total'], 2 ); ?></span>
                    </div>
                    <a href="<?php echo home_url( '/tienda/checkout/' ); ?>"
                       class="int-btn int-btn-primary int-btn-full">
                        Proceder al Pago
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
