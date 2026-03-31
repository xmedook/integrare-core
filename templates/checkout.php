<?php
/**
 * Template: Checkout (Shopify-style)
 * Route: /tienda/checkout/
 */

if ( ! $is_logged_in ) : ?>
<section class="int-checkout-gate">
    <div class="int-checkout-gate-card">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--int-accent)"
             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
        </svg>
        <h2>Inicia sesión para completar tu pedido</h2>
        <p>Necesitas una cuenta activa para realizar compras.</p>
        <a href="<?php echo esc_url( $login_url ); ?>" class="int-btn int-btn-primary int-btn-lg">Iniciar Sesión</a>
    </div>
</section>
<?php return; endif; ?>

<?php if ( empty( $cart['items'] ) ) : ?>
<section class="int-checkout-gate">
    <div class="int-checkout-gate-card">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#999"
             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
        <h2>Tu carrito está vacío</h2>
        <p>Agrega productos para poder continuar con tu pedido.</p>
        <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-btn int-btn-primary int-btn-lg">Ver Catálogo</a>
    </div>
</section>
<?php return; endif; ?>

<!-- ── Checkout Layout (two columns) ──────────────── -->
<div class="int-checkout-wrap">
    <!-- LEFT: Form -->
    <div class="int-checkout-main">
        <nav class="int-checkout-breadcrumb">
            <a href="<?php echo home_url( '/tienda/' ); ?>">Tienda</a>
            <span>›</span>
            <a href="<?php echo home_url( '/tienda/carrito/' ); ?>">Carrito</a>
            <span>›</span>
            <span class="active">Checkout</span>
        </nav>

        <!-- Shipping / Contact -->
        <section class="int-checkout-section">
            <h2>Información de contacto</h2>
            <div class="int-checkout-contact-card">
                <div class="int-checkout-avatar">
                    <?php echo strtoupper( substr( wp_get_current_user()->display_name, 0, 1 ) ); ?>
                </div>
                <div class="int-checkout-contact-info">
                    <strong><?php echo esc_html( wp_get_current_user()->display_name ); ?></strong>
                    <span><?php echo esc_html( wp_get_current_user()->user_email ); ?></span>
                </div>
            </div>
        </section>

        <section class="int-checkout-section">
            <h2>Dirección de envío</h2>
            <div class="int-checkout-fields">
                <div class="int-field-row">
                    <div class="int-field-group">
                        <label for="intShipName">Nombre completo</label>
                        <input type="text" id="intShipName" class="int-input"
                               value="<?php echo esc_attr( wp_get_current_user()->display_name ); ?>"
                               placeholder="Nombre y apellido">
                    </div>
                    <div class="int-field-group">
                        <label for="intShipPhone">Teléfono</label>
                        <input type="tel" id="intShipPhone" class="int-input" placeholder="(55) 1234-5678">
                    </div>
                </div>
                <div class="int-field-group">
                    <label for="intShipAddress">Dirección</label>
                    <input type="text" id="intShipAddress" class="int-input" placeholder="Calle, número">
                </div>
                <div class="int-field-row int-field-row-3">
                    <div class="int-field-group">
                        <label for="intShipCity">Ciudad</label>
                        <input type="text" id="intShipCity" class="int-input" placeholder="Ciudad">
                    </div>
                    <div class="int-field-group">
                        <label for="intShipState">Estado</label>
                        <input type="text" id="intShipState" class="int-input" placeholder="Estado">
                    </div>
                    <div class="int-field-group">
                        <label for="intShipZip">C.P.</label>
                        <input type="text" id="intShipZip" class="int-input" placeholder="00000">
                    </div>
                </div>
            </div>
        </section>

        <section class="int-checkout-section">
            <h2>Notas del pedido <span class="int-optional">(opcional)</span></h2>
            <textarea id="intOrderNotes" rows="3" class="int-input int-textarea"
                      placeholder="Instrucciones especiales, horario de entrega preferido..."></textarea>
        </section>

        <?php
        $stripe_on = Integrare_Payments::stripe_enabled();
        $paypal_on = Integrare_Payments::paypal_enabled();
        $has_payments = $stripe_on || $paypal_on;
        ?>

        <?php if ( $has_payments ) : ?>
        <section class="int-checkout-section">
            <h2>Método de pago</h2>
            <div class="int-payment-methods">
                <?php if ( $stripe_on ) : ?>
                <label class="int-payment-option active" data-method="stripe">
                    <input type="radio" name="payment_method" value="stripe" checked>
                    <div class="int-payment-option-content">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        <span>Tarjeta de crédito/débito</span>
                    </div>
                    <span class="int-payment-badge">Stripe</span>
                </label>
                <?php endif; ?>
                <?php if ( $paypal_on ) : ?>
                <label class="int-payment-option <?php echo ! $stripe_on ? 'active' : ''; ?>" data-method="paypal">
                    <input type="radio" name="payment_method" value="paypal" <?php echo ! $stripe_on ? 'checked' : ''; ?>>
                    <div class="int-payment-option-content">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M7.5 8h4c1.5 0 2.5 1 2.5 2.5S13 13 11.5 13H9l-1 4"/></svg>
                        <span>PayPal</span>
                    </div>
                    <span class="int-payment-badge">PayPal</span>
                </label>
                <?php endif; ?>
            </div>
            <!-- PayPal button container (hidden until PayPal is selected + order created) -->
            <div id="intPaypalButtonContainer" style="display:none; margin-top:16px;"></div>
        </section>
        <?php endif; ?>

        <button class="int-btn int-btn-primary int-btn-full int-btn-lg" id="intPlaceOrder">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
            <?php echo $has_payments ? 'Pagar y Confirmar' : 'Confirmar Pedido'; ?>
        </button>

        <p class="int-checkout-disclaimer">
            Al confirmar, aceptas los términos de compra B2B. Tu pedido será procesado y te notificaremos por email.
        </p>
    </div>

    <!-- RIGHT: Order Summary -->
    <aside class="int-checkout-sidebar">
        <div class="int-checkout-sidebar-inner">
            <h3>Resumen del pedido <span class="int-checkout-count">(<?php echo $cart['item_count']; ?>)</span></h3>

            <div class="int-checkout-items">
                <?php foreach ( $cart['items'] as $item ) :
                    $pr = $item['pricing'];
                    $thumb = $item['thumbnail'];
                ?>
                <div class="int-checkout-item">
                    <div class="int-checkout-item-img">
                        <?php if ( $thumb ) : ?>
                            <img src="<?php echo esc_url( $thumb ); ?>" alt="">
                        <?php else : ?>
                            <div class="int-checkout-item-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4l-9-5.19"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
                        <?php endif; ?>
                        <span class="int-checkout-item-qty"><?php echo $item['quantity']; ?></span>
                    </div>
                    <div class="int-checkout-item-detail">
                        <span class="int-checkout-item-name"><?php echo esc_html( $item['title'] ); ?></span>
                        <span class="int-checkout-item-meta">
                            <?php echo $item['quantity']; ?> caja<?php echo $item['quantity'] > 1 ? 's' : ''; ?>
                            <?php if ( $pr['discount_pct'] > 0 ) : ?>
                                <span class="int-checkout-discount">-<?php echo $pr['discount_pct']; ?>%</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="int-checkout-item-price">
                        $<?php echo number_format( $pr['line_total'], 2 ); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="int-checkout-summary-lines">
                <div class="int-checkout-line">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format( $cart['total'], 2 ); ?></span>
                </div>
                <div class="int-checkout-line">
                    <span>Envío</span>
                    <span class="int-checkout-shipping">Por confirmar</span>
                </div>
            </div>

            <div class="int-checkout-total-line">
                <span>Total</span>
                <span class="int-checkout-total-amount">$<?php echo number_format( $cart['total'], 2 ); ?> <small>MXN</small></span>
            </div>
        </div>
    </aside>
</div>

<!-- ── Order Confirmation (hidden, shown via JS) ── -->
<div class="int-order-confirmation" id="intOrderConfirmation" style="display:none;">
    <div class="int-confirmation-card">
        <div class="int-confirmation-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#22c55e"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <h2>¡Pedido Confirmado!</h2>
        <p>Tu pedido <strong id="intOrderId"></strong> ha sido registrado exitosamente.</p>
        <p class="int-confirmation-sub">Recibirás una notificación por email cuando tu pedido esté listo para entrega.</p>
        <div class="int-confirmation-actions">
            <a href="<?php echo home_url( '/tienda/cuenta/' ); ?>" class="int-btn int-btn-primary int-btn-lg">Ir a Mi Cuenta</a>
            <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-btn int-btn-outline">Seguir Comprando</a>
        </div>
    </div>
</div>
