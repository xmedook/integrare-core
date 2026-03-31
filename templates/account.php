<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php if ( ! $user ) : ?>
    <!-- ── Login Gate ── -->
    <div class="int-checkout-gate">
        <div class="int-checkout-gate-card">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <h2>Inicia Sesión</h2>
            <p>Necesitas una cuenta para acceder a tu perfil y pedidos.</p>
            <a href="<?php echo esc_url( home_url( '/tienda/auth/' ) ); ?>" class="int-btn int-btn-primary">Iniciar Sesión</a>
        </div>
    </div>
<?php else : ?>

<div class="int-account">

    <!-- ── Profile Header ── -->
    <div class="int-account-header">
        <div class="int-account-avatar">
            <?php echo strtoupper( substr( $user['name'], 0, 1 ) ); ?>
        </div>
        <div class="int-account-user-info">
            <h1><?php echo esc_html( $user['name'] ); ?></h1>
            <p><?php echo esc_html( $user['email'] ); ?></p>
        </div>
        <a href="<?php echo esc_url( home_url( '/tienda/' ) ); ?>" class="int-btn int-btn-outline int-btn-sm">
            ← Volver al Catálogo
        </a>
    </div>

    <!-- ── Tabs ── -->
    <div class="int-account-tabs">
        <button class="int-account-tab active" data-tab="orders">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            Mis Pedidos
        </button>
        <button class="int-account-tab" data-tab="profile">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Mi Perfil
        </button>
    </div>

    <!-- ── Orders Tab ── -->
    <div class="int-account-panel active" id="intTabOrders" data-panel="orders">

        <?php if ( empty( $orders ) ) : ?>
            <div class="int-account-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
                </svg>
                <h3>Aún no tienes pedidos</h3>
                <p>Cuando realices tu primer pedido aparecerá aquí.</p>
                <a href="<?php echo home_url( '/tienda/' ); ?>" class="int-btn int-btn-primary">Explorar Catálogo</a>
            </div>
        <?php else : ?>
            <div class="int-orders-list">
                <?php foreach ( $orders as $order ) :
                    $status_map = array(
                        'pending'   => array( 'label' => 'Pendiente', 'class' => 'pending' ),
                        'paid'      => array( 'label' => 'Pagado', 'class' => 'paid' ),
                        'completed' => array( 'label' => 'Completado', 'class' => 'completed' ),
                        'cancelled' => array( 'label' => 'Cancelado', 'class' => 'cancelled' ),
                    );
                    $st = $status_map[ $order['status'] ] ?? $status_map['pending'];

                    $payment_labels = array(
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                    );
                    $payment_label = $payment_labels[ $order['payment_method'] ?? '' ] ?? 'Por definir';

                    $has_shipping = ! empty( $order['shipping_name'] ) || ! empty( $order['shipping_address'] );
                    $has_items    = ! empty( $order['items'] );
                ?>
                    <div class="int-order-card">
                        <!-- ── Order Header (clickable) ── -->
                        <div class="int-order-card-header int-order-toggle" data-order="<?php echo esc_attr( $order['id'] ); ?>">
                            <div class="int-order-card-id">
                                <strong>Pedido #<?php echo esc_html( $order['id'] ); ?></strong>
                                <span class="int-order-date"><?php echo date( 'd/m/Y H:i', strtotime( $order['created_at'] ) ); ?></span>
                            </div>
                            <div class="int-order-header-right">
                                <span class="int-order-status int-status-<?php echo $st['class']; ?>">
                                    <?php echo $st['label']; ?>
                                </span>
                                <span class="int-order-card-total-inline">$<?php echo number_format( $order['total'], 2 ); ?> MXN</span>
                                <svg class="int-order-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </div>
                        </div>

                        <!-- ── Order Details (expandable) ── -->
                        <div class="int-order-card-details" id="intOrderDetails-<?php echo esc_attr( $order['id'] ); ?>">
                            <div class="int-order-details-inner">

                                <?php if ( $has_items ) : ?>
                                <!-- Products Table -->
                                <div class="int-order-section">
                                    <h4>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
                                        </svg>
                                        Productos
                                    </h4>
                                    <div class="int-order-items-table-wrap">
                                        <table class="int-order-items-table">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Unit.</th>
                                                    <th>Desc.</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ( $order['items'] as $item ) : ?>
                                                <tr>
                                                    <td class="int-item-name"><?php echo esc_html( $item['product_name'] ?? 'Producto eliminado' ); ?></td>
                                                    <td>
                                                        <?php echo esc_html( $item['quantity_boxes'] ); ?> caja<?php echo $item['quantity_boxes'] > 1 ? 's' : ''; ?>
                                                        <span class="int-item-units">(<?php echo esc_html( $item['units_per_box'] ); ?> uds/caja)</span>
                                                    </td>
                                                    <td>$<?php echo number_format( $item['unit_price'], 2 ); ?></td>
                                                    <td>
                                                        <?php if ( floatval( $item['discount_pct'] ) > 0 ) : ?>
                                                            <span class="int-discount-badge">-<?php echo number_format( $item['discount_pct'], 0 ); ?>%</span>
                                                        <?php else : ?>
                                                            <span class="int-no-discount">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="int-item-total">$<?php echo number_format( $item['line_total'], 2 ); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Info Grid: Payment + Shipping -->
                                <div class="int-order-info-grid">
                                    <!-- Payment Method -->
                                    <div class="int-order-section int-order-info-card">
                                        <h4>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                                            </svg>
                                            Método de Pago
                                        </h4>
                                        <p class="int-order-info-value"><?php echo esc_html( $payment_label ); ?></p>
                                    </div>

                                    <!-- Shipping Info -->
                                    <?php if ( $has_shipping ) : ?>
                                    <div class="int-order-section int-order-info-card">
                                        <h4>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                                            </svg>
                                            Datos de Envío
                                        </h4>
                                        <div class="int-shipping-details">
                                            <?php if ( ! empty( $order['shipping_name'] ) ) : ?>
                                                <p><strong><?php echo esc_html( $order['shipping_name'] ); ?></strong></p>
                                            <?php endif; ?>
                                            <?php if ( ! empty( $order['shipping_phone'] ) ) : ?>
                                                <p>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                                                    <?php echo esc_html( $order['shipping_phone'] ); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php
                                            $address_parts = array_filter( array(
                                                $order['shipping_address'] ?? '',
                                                $order['shipping_city'] ?? '',
                                                $order['shipping_state'] ?? '',
                                                $order['shipping_zip'] ?? '',
                                            ) );
                                            if ( ! empty( $address_parts ) ) : ?>
                                                <p>
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                    <?php echo esc_html( implode( ', ', $address_parts ) ); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ( ! empty( $order['notes'] ) ) : ?>
                                <!-- Notes -->
                                <div class="int-order-section">
                                    <h4>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                                        </svg>
                                        Notas
                                    </h4>
                                    <p class="int-order-notes-text"><?php echo esc_html( $order['notes'] ); ?></p>
                                </div>
                                <?php endif; ?>

                                <!-- Total Footer -->
                                <div class="int-order-total-footer">
                                    <span>Total del Pedido</span>
                                    <strong>$<?php echo number_format( $order['total'], 2 ); ?> MXN</strong>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Profile Tab ── -->
    <div class="int-account-panel" id="intTabProfile" data-panel="profile">
        <div class="int-profile-form">
            <h3>Información Personal</h3>
            <div class="int-checkout-fields">
                <div class="int-field-row">
                    <div class="int-field-group">
                        <label>Nombre</label>
                        <input type="text" class="int-input" id="intProfileName" value="<?php echo esc_attr( $user['name'] ); ?>">
                    </div>
                    <div class="int-field-group">
                        <label>Email</label>
                        <input type="email" class="int-input" id="intProfileEmail" value="<?php echo esc_attr( $user['email'] ); ?>" disabled>
                    </div>
                </div>
                <div class="int-field-row">
                    <div class="int-field-group">
                        <label>Teléfono</label>
                        <input type="tel" class="int-input" id="intProfilePhone" value="<?php echo esc_attr( $user['phone'] ); ?>">
                    </div>
                </div>
            </div>

            <h3 style="margin-top: 32px;">Cambiar Contraseña</h3>
            <div class="int-checkout-fields">
                <div class="int-field-row">
                    <div class="int-field-group">
                        <label>Nueva Contraseña</label>
                        <input type="password" class="int-input" id="intProfilePass" placeholder="Dejar vacío para no cambiar">
                    </div>
                    <div class="int-field-group">
                        <label>Confirmar Contraseña</label>
                        <input type="password" class="int-input" id="intProfilePassConfirm" placeholder="Repetir nueva contraseña">
                    </div>
                </div>
            </div>

            <button class="int-btn int-btn-primary" id="intSaveProfile" style="margin-top: 24px;">
                Guardar Cambios
            </button>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Tab switching ──
    document.querySelectorAll('.int-account-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = this.getAttribute('data-tab');
            document.querySelectorAll('.int-account-tab').forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
            document.querySelectorAll('.int-account-panel').forEach(function (p) { p.classList.remove('active'); });
            document.querySelector('[data-panel="' + target + '"]').classList.add('active');
        });
    });

    // ── Order expand/collapse ──
    document.querySelectorAll('.int-order-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var orderId = this.getAttribute('data-order');
            var details = document.getElementById('intOrderDetails-' + orderId);
            var card    = this.closest('.int-order-card');

            if (card.classList.contains('expanded')) {
                // Collapse
                details.style.maxHeight = details.scrollHeight + 'px';
                requestAnimationFrame(function () {
                    details.style.maxHeight = '0';
                });
                card.classList.remove('expanded');
            } else {
                // Expand
                card.classList.add('expanded');
                details.style.maxHeight = details.scrollHeight + 'px';
                details.addEventListener('transitionend', function handler() {
                    if (card.classList.contains('expanded')) {
                        details.style.maxHeight = 'none';
                    }
                    details.removeEventListener('transitionend', handler);
                });
            }
        });
    });

    // ── Save profile ──
    var saveBtn = document.getElementById('intSaveProfile');
    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            var pass = document.getElementById('intProfilePass').value;
            var passConfirm = document.getElementById('intProfilePassConfirm').value;

            if (pass && pass !== passConfirm) {
                alert('Las contraseñas no coinciden.');
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="int-spinner"></span> Guardando...';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', integrareData.ajaxUrl);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Guardar Cambios';
                try {
                    var res = JSON.parse(xhr.responseText);
                    alert(res.success ? 'Perfil actualizado correctamente.' : (res.data.message || 'Error al actualizar.'));
                } catch (e) {
                    alert('Error de conexión.');
                }
            };
            xhr.onerror = function () {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Guardar Cambios';
                alert('Error de conexión.');
            };

            var params = 'action=integrare_update_profile'
                + '&nonce=' + encodeURIComponent(integrareData.nonce)
                + '&name=' + encodeURIComponent(document.getElementById('intProfileName').value)
                + '&phone=' + encodeURIComponent(document.getElementById('intProfilePhone').value)
                + '&password=' + encodeURIComponent(pass);
            xhr.send(params);
        });
    }
});
</script>

<?php endif; ?>
