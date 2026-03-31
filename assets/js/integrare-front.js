/**
 * Integrare Core — Frontend JavaScript
 * AJAX price calculator, cart operations, and UI interactions.
 */
(function ($) {
    'use strict';

    var data = window.integrareData || {};

    // ── Utilities ────────────────────────────────────────────
    function formatCurrency(n) {
        return '$' + parseFloat(n).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function showToast(message, isError) {
        var $toast = $('#intToast');
        $toast.text(message)
            .toggleClass('error', !!isError)
            .addClass('visible');
        clearTimeout($toast.data('timer'));
        $toast.data('timer', setTimeout(function () {
            $toast.removeClass('visible');
        }, 3000));
    }

    function ajax(action, params, callback) {
        params.action = 'integrare_' + action;
        params.nonce = data.nonce;
        $.post(data.ajaxUrl, params, function (res) {
            if (res.success) {
                callback(res.data);
            } else {
                showToast(res.data ? res.data.message : 'Error desconocido', true);
            }
        }).fail(function () {
            showToast('Error de conexión. Intenta de nuevo.', true);
        });
    }

    // ── Search Overlay ──────────────────────────────────────
    $(document).on('click', '.int-search-toggle', function (e) {
        e.preventDefault();
        $('#intSearchOverlay').addClass('active');
        setTimeout(function () {
            $('#intSearchOverlay .int-search-input').focus();
        }, 100);
    });

    $(document).on('click', '.int-search-close', function () {
        $('#intSearchOverlay').removeClass('active');
    });

    $(document).on('click', '.int-search-overlay', function (e) {
        if ($(e.target).hasClass('int-search-overlay')) {
            $(this).removeClass('active');
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $('#intSearchOverlay').removeClass('active');
        }
    });

    // ── Price Calculator (Product Page) ─────────────────────
    var $qtyInput = $('#intQtyInput');
    if ($qtyInput.length) {
        var productId = $qtyInput.data('product-id');
        var upb = parseInt($qtyInput.data('upb')) || 1;
        var calcTimer = null;

        function updatePriceCalc() {
            var qty = parseInt($qtyInput.val()) || 1;
            if (qty < 1) { qty = 1; $qtyInput.val(1); }

            // Optimistic local update
            $('#intTotalBoxes').text(qty);
            $('#intTotalUnits').text(qty * upb);

            // AJAX call
            clearTimeout(calcTimer);
            calcTimer = setTimeout(function () {
                ajax('calculate_price', {
                    product_id: productId,
                    quantity: qty
                }, function (result) {
                    $('#intTotalUnits').text(result.total_units);
                    $('#intTotalBoxes').text(result.quantity_boxes);
                    $('#intUnitPrice').text(formatCurrency(result.discounted_price));
                    $('#intDiscountPct').text(result.discount_pct + '%');
                    $('#intLineTotal').text(formatCurrency(result.line_total));

                    // Update tier highlighting
                    $('#intDiscountTable tr').removeClass('int-tier-active');
                    $('#intDiscountTable tr[data-tier="' + result.tier_index + '"]')
                        .addClass('int-tier-active');

                    // Update message
                    var boxWord = qty > 1 ? 'cajas' : 'caja';
                    $('#intCalcMessage').html(
                        'Recibirás <strong>' + result.total_units + '</strong> unidades en <strong>' + qty + '</strong> ' + boxWord
                    );
                });
            }, 250);
        }

        $qtyInput.on('input change', updatePriceCalc);

        $('#intQtyMinus').on('click', function () {
            var v = parseInt($qtyInput.val()) || 1;
            if (v > 1) { $qtyInput.val(v - 1).trigger('change'); }
        });

        $('#intQtyPlus').on('click', function () {
            var v = parseInt($qtyInput.val()) || 1;
            var max = parseInt($qtyInput.attr('max')) || 9999;
            if (v < max) { $qtyInput.val(v + 1).trigger('change'); }
        });
    }

    // ── Add to Cart (Product Page) ──────────────────────────
    $(document).on('click', '#intAddToCart', function (e) {
        e.preventDefault();
        if (!data.isLoggedIn) {
            window.location.href = data.loginUrl;
            return;
        }

        var $btn = $(this);
        var pid = $btn.data('product-id');
        var qty = parseInt($('#intQtyInput').val()) || 1;

        $btn.prop('disabled', true).html('<span class="int-spinner"></span> Agregando...');

        ajax('cart_add', { product_id: pid, quantity: qty }, function (result) {
            showToast(result.message);
            updateCartBadge(result.cart_count);
            $btn.prop('disabled', false).text('Agregar al Carrito');

            // Fetch full cart and open drawer
            ajax('cart_get', {}, function (cart) {
                renderCartDrawer(cart);
                openCartDrawer();
            });
        });
    });

    function updateCartBadge(count) {
        var $badge = $('.int-cart-badge');
        if (count > 0) {
            if ($badge.length) {
                $badge.text(count);
            } else {
                $('.int-cart-toggle').append('<span class="int-cart-badge" id="intCartBadge">' + count + '</span>');
            }
        } else if ($badge.length) {
            $badge.remove();
        }
    }

    // ── Cart Drawer ────────────────────────────────────────
    function openCartDrawer() {
        $('#intCartDrawerOverlay').addClass('active');
        $('#intCartDrawer').addClass('active');
        $('body').css('overflow', 'hidden');
    }

    function closeCartDrawer() {
        $('#intCartDrawerOverlay').removeClass('active');
        $('#intCartDrawer').removeClass('active');
        $('body').css('overflow', '');
    }

    $(document).on('click', '#intCartDrawerClose, #intCartDrawerOverlay', closeCartDrawer);

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCartDrawer();
        }
    });

    // Toggle drawer from cart icon in header
    $(document).on('click', '.int-cart-toggle', function (e) {
        e.preventDefault();
        if ($('#intCartDrawer').hasClass('active')) {
            closeCartDrawer();
        } else {
            if (!data.isLoggedIn) {
                window.location.href = data.loginUrl;
                return;
            }
            // Fetch cart and open
            ajax('cart_get', {}, function (cart) {
                renderCartDrawer(cart);
                openCartDrawer();
            });
        }
    });

    function renderCartDrawer(cart) {
        var $body = $('#intCartDrawerBody');
        var $footer = $('#intCartDrawerFooter');

        if (!cart || !cart.items || cart.items.length === 0) {
            $body.html('<p class="int-cart-drawer-empty">Tu carrito está vacío.</p>');
            $footer.hide();
            return;
        }

        var html = '';
        cart.items.forEach(function (item) {
            var imgHtml = item.thumbnail
                ? '<img src="' + item.thumbnail + '" alt="">'
                : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#ccc;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4l-9-5.19"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>';

            html += '<div class="int-drawer-item">';
            html += '  <div class="int-drawer-item-img">' + imgHtml + '</div>';
            html += '  <div class="int-drawer-item-info">';
            html += '    <div class="int-drawer-item-name">' + item.title + '</div>';
            html += '    <div class="int-drawer-item-meta">' + item.quantity + (item.quantity > 1 ? ' cajas' : ' caja') + '</div>';
            html += '  </div>';
            html += '  <div class="int-drawer-item-price">' + formatCurrency(item.pricing.line_total) + '</div>';
            html += '</div>';
        });

        $body.html(html);
        $('#intCartDrawerTotal').text(formatCurrency(cart.total));
        $footer.show();
    }

    // ── Cart Page Operations ────────────────────────────────
    // Quantity buttons
    $(document).on('click', '.int-cart-qty-btn', function () {
        var $btn = $(this);
        var pid = $btn.data('product-id');
        var action = $btn.data('action');
        var $input = $btn.closest('.int-qty-control').find('.int-cart-qty-input');
        var val = parseInt($input.val()) || 1;

        if (action === 'decrease') {
            val = Math.max(1, val - 1);
        } else {
            val = val + 1;
        }

        $input.val(val);
        updateCartItem(pid, val);
    });

    // Direct input change
    $(document).on('change', '.int-cart-qty-input', function () {
        var pid = $(this).data('product-id');
        var val = Math.max(1, parseInt($(this).val()) || 1);
        $(this).val(val);
        updateCartItem(pid, val);
    });

    // Remove item
    $(document).on('click', '.int-cart-remove', function () {
        var pid = $(this).data('product-id');
        var $item = $(this).closest('.int-cart-item');

        $item.css({ opacity: 0.5, pointerEvents: 'none' });

        ajax('cart_remove', { product_id: pid }, function (result) {
            $item.slideUp(300, function () { $(this).remove(); });
            updateCartSummary(result.cart);
            updateCartBadge(result.cart.item_count);
            showToast(result.message);

            // If cart is now empty, reload to show empty state
            if (result.cart.items.length === 0) {
                setTimeout(function () { location.reload(); }, 300);
            }
        });
    });

    var updateTimer = null;
    function updateCartItem(pid, qty) {
        clearTimeout(updateTimer);
        updateTimer = setTimeout(function () {
            ajax('cart_update', { product_id: pid, quantity: qty }, function (result) {
                updateCartSummary(result.cart);
                updateCartBadge(result.cart.item_count);

                // Update each cart item display
                result.cart.items.forEach(function (item) {
                    var $row = $('.int-cart-item[data-product-id="' + item.product_id + '"]');
                    if ($row.length) {
                        var pr = item.pricing;
                        $row.find('.int-cart-item-total strong').text(formatCurrency(pr.line_total));
                        $row.find('.int-cart-item-meta').text(
                            pr.total_units + ' unidades · ' + pr.discount_pct + '% desc. · ' + formatCurrency(pr.discounted_price) + '/ud'
                        );
                        var boxWord = item.quantity > 1 ? 'cajas' : 'caja';
                        $row.find('.int-qty-label').text(item.quantity + ' ' + boxWord);
                    }
                });
            });
        }, 300);
    }

    function updateCartSummary(cart) {
        var $summary = $('.int-cart-summary');
        if (!$summary.length) return;

        $summary.find('.int-summary-row:first span:last').text(cart.item_count + ' cajas');
        $summary.find('.int-summary-row:first span:first').text('Productos (' + cart.total_lines + ')');
        $summary.find('.int-summary-total span:last').text(formatCurrency(cart.total));
    }

    // ── Payment Method Toggle ────────────────────────────────
    $(document).on('change', 'input[name="payment_method"]', function () {
        $('.int-payment-option').removeClass('active');
        $(this).closest('.int-payment-option').addClass('active');
        $('#intPaypalButtonContainer').hide().empty();
    });

    // ── Checkout (with or without payment) ──────────────────
    $(document).on('click', '#intPlaceOrder', function (e) {
        e.preventDefault();

        var $btn = $(this);
        var notes = $('#intOrderNotes').val() || '';
        var shipping = {
            name: $('#intShipName').val() || '',
            phone: $('#intShipPhone').val() || '',
            address: $('#intShipAddress').val() || '',
            city: $('#intShipCity').val() || '',
            state: $('#intShipState').val() || '',
            zip: $('#intShipZip').val() || ''
        };

        var $paymentRadio = $('input[name="payment_method"]:checked');
        var paymentMethod = $paymentRadio.length ? $paymentRadio.val() : null;

        $btn.prop('disabled', true).html('<span class="int-spinner"></span> Procesando...');

        // ── No payment gateways (legacy flow) ──────────────
        if (!paymentMethod) {
            ajax('checkout', { notes: notes, shipping: JSON.stringify(shipping) }, function (result) {
                $('.int-checkout-wrap').fadeOut(300, function () {
                    $('#intOrderId').text('#' + result.order_id);
                    $('#intOrderConfirmation').fadeIn(300);
                });
                updateCartBadge(0);
            });
            return;
        }

        // ── Payment gateway flow ────────────────────────────
        ajax('create_payment', {
            payment_method: paymentMethod,
            notes: notes,
            shipping: JSON.stringify(shipping)
        }, function (result) {

            if (result.gateway === 'stripe') {
                // Redirect to Stripe Checkout
                if (typeof Stripe !== 'undefined' && integrareData.stripePk) {
                    var stripe = Stripe(integrareData.stripePk);
                    stripe.redirectToCheckout({ sessionId: result.session_id }).then(function (r) {
                        if (r.error) {
                            alert(r.error.message);
                            $btn.prop('disabled', false).text('Pagar y Confirmar');
                        }
                    });
                } else {
                    // Fallback: redirect to Stripe URL directly
                    window.location.href = result.url;
                }

            } else if (result.gateway === 'paypal') {
                // Render PayPal buttons inline
                $btn.hide();
                $('#intPaypalButtonContainer').show();

                if (typeof paypal !== 'undefined') {
                    paypal.Buttons({
                        createOrder: function () {
                            return result.paypal_order_id;
                        },
                        onApprove: function (data) {
                            $('#intPaypalButtonContainer').html('<div style="text-align:center;padding:20px;"><span class="int-spinner"></span> Verificando pago...</div>');

                            ajax('verify_payment', {
                                gateway: 'paypal',
                                order_id: result.order_id,
                                paypal_order_id: data.orderID
                            }, function () {
                                $('.int-checkout-wrap').fadeOut(300, function () {
                                    $('#intOrderId').text('#' + result.order_id);
                                    $('#intOrderConfirmation').fadeIn(300);
                                });
                                updateCartBadge(0);
                            });
                        },
                        onCancel: function () {
                            $btn.show().prop('disabled', false).text('Pagar y Confirmar');
                            $('#intPaypalButtonContainer').hide().empty();
                        },
                        onError: function (err) {
                            alert('Error de PayPal: ' + err);
                            $btn.show().prop('disabled', false).text('Pagar y Confirmar');
                            $('#intPaypalButtonContainer').hide().empty();
                        }
                    }).render('#intPaypalButtonContainer');
                } else {
                    alert('Error: PayPal SDK no cargó correctamente.');
                    $btn.show().prop('disabled', false).text('Pagar y Confirmar');
                }
            }
        });
    });

    // ── Stripe Return Handler ──────────────────────────────
    // When Stripe redirects back with ?payment_status=success
    (function () {
        var params = new URLSearchParams(window.location.search);
        if (params.get('payment_status') === 'success' && params.get('session_id')) {
            ajax('verify_payment', {
                gateway: 'stripe',
                order_id: params.get('order_id'),
                session_id: params.get('session_id')
            }, function (result) {
                $('.int-checkout-wrap').hide();
                $('#intOrderId').text('#' + result.order_id);
                $('#intOrderConfirmation').show();
                updateCartBadge(0);
            });
        }
    })();

})(jQuery);
