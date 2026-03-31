/**
 * Integrare Admin Dashboard — SPA Controller
 * Hash-based routing: #dashboard, #products, #orders, #clients, #categories
 */
(function () {
    'use strict';

    const D = integrareData;
    const $ = (s, p) => (p || document).querySelector(s);
    const $$ = (s, p) => [...(p || document).querySelectorAll(s)];
    const content = $('#iadmContent');

    /* ── State ──────────────────────────────────────── */
    let currentSection = 'dashboard';
    let categories = [];

    /* ── AJAX Helper ────────────────────────────────── */
    function ajax(action, data = {}) {
        return new Promise((resolve, reject) => {
            const fd = new FormData();
            fd.append('action', action);
            fd.append('nonce', D.nonce);
            for (const k in data) fd.append(k, data[k]);
            fetch(D.ajaxUrl, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(r => r.success ? resolve(r.data) : reject(r.data))
                .catch(reject);
        });
    }

    /* ── Toast ──────────────────────────────────────── */
    function toast(msg, type = '') {
        const t = $('#iadmToast');
        t.textContent = msg;
        t.className = 'iadm-toast show ' + type;
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.className = 'iadm-toast', 3000);
    }

    /* ── Format Helpers ─────────────────────────────── */
    function money(n) { return '$' + parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function fmtDate(d) {
        if (!d) return '—';
        const dt = new Date(d);
        return dt.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    function statusLabel(s) {
        const map = { pending: 'Pendiente', paid: 'Pagado', completed: 'Completado', cancelled: 'Cancelado' };
        return `<span class="iadm-badge ${s}">${map[s] || s}</span>`;
    }

    /* ── Modal Helpers ──────────────────────────────── */
    function openModal(id) { $('#' + id).classList.add('active'); }
    function closeModal(id) { $('#' + id).classList.remove('active'); }

    document.addEventListener('click', e => {
        if (e.target.matches('[data-close-modal]') || e.target.closest('[data-close-modal]')) {
            const overlay = e.target.closest('.iadm-modal-overlay');
            if (overlay) overlay.classList.remove('active');
        }
        if (e.target.classList.contains('iadm-modal-overlay')) {
            e.target.classList.remove('active');
        }
    });

    /* ── Pagination Helper ──────────────────────────── */
    function renderPagination(current, total, onPage) {
        if (total <= 1) return '';
        let html = '<div class="iadm-pagination">';
        html += `<button class="iadm-page-btn" ${current <= 1 ? 'disabled' : ''} data-page="${current - 1}">&lsaquo;</button>`;
        const start = Math.max(1, current - 2);
        const end = Math.min(total, current + 2);
        for (let i = start; i <= end; i++) {
            html += `<button class="iadm-page-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        html += `<button class="iadm-page-btn" ${current >= total ? 'disabled' : ''} data-page="${current + 1}">&rsaquo;</button>`;
        html += `<span class="iadm-page-info">${current} de ${total}</span>`;
        html += '</div>';
        return html;
    }

    /* ═══════════════════════════════════════════════════
       SECTION: DASHBOARD
       ═══════════════════════════════════════════════════ */
    async function renderDashboard() {
        content.innerHTML = '<div class="iadm-loading"><div class="iadm-spinner"></div><p>Cargando...</p></div>';
        try {
            const d = await ajax('integrare_admin_get_stats');
            let html = '<div class="iadm-kpi-grid">';
            html += kpi('Ingresos del Mes', money(d.month_revenue), 'accent', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>');
            html += kpi('Pedidos Hoy', d.orders_today, 'info', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>');
            html += kpi('Productos', d.total_products, 'success', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>');
            html += kpi('Clientes', d.total_clients, 'warning', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>');
            html += kpi('Pendientes', d.pending_orders, 'danger', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>');
            html += kpi('Stock Bajo', d.low_stock_count, 'warning', '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>');
            html += '</div>';

            // Recent orders
            html += '<div class="iadm-grid-2">';
            html += '<div class="iadm-card">';
            html += '<div class="iadm-card-header"><h3 class="iadm-card-title">Pedidos Recientes</h3></div>';
            html += '<div class="iadm-card-body"><div class="iadm-table-wrap"><table class="iadm-table"><thead><tr>';
            html += '<th>#</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th>';
            html += '</tr></thead><tbody>';
            if (d.recent_orders && d.recent_orders.length) {
                d.recent_orders.forEach(o => {
                    html += `<tr>
                        <td class="iadm-cell-title">${o.id}</td>
                        <td>${o.display_name || 'N/A'}<br><span class="iadm-cell-muted">${o.user_email || ''}</span></td>
                        <td class="iadm-cell-title">${money(o.total)}</td>
                        <td>${statusLabel(o.status)}</td>
                        <td class="iadm-cell-muted">${fmtDate(o.created_at)}</td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="5" style="text-align:center;padding:30px;color:var(--iadm-text-muted)">Sin pedidos aún</td></tr>';
            }
            html += '</tbody></table></div></div></div>';

            // Low stock
            html += '<div class="iadm-card">';
            html += '<div class="iadm-card-header"><h3 class="iadm-card-title">Stock Bajo (menos de 50)</h3></div>';
            html += '<div class="iadm-card-body"><div class="iadm-table-wrap"><table class="iadm-table"><thead><tr>';
            html += '<th>Producto</th><th>SKU</th><th>Stock</th>';
            html += '</tr></thead><tbody>';
            if (d.low_stock_products && d.low_stock_products.length) {
                d.low_stock_products.forEach(p => {
                    html += `<tr>
                        <td class="iadm-cell-title">${p.post_title}</td>
                        <td class="iadm-cell-muted">${p.sku || '—'}</td>
                        <td><span class="iadm-badge cancelled">${p.stock}</span></td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="3" style="text-align:center;padding:30px;color:var(--iadm-text-muted)">Todo el stock está bien</td></tr>';
            }
            html += '</tbody></table></div></div></div>';
            html += '</div>';

            content.innerHTML = html;
        } catch (e) {
            content.innerHTML = '<div class="iadm-empty"><p>Error al cargar el dashboard.</p></div>';
            console.error(e);
        }
    }

    function kpi(label, value, color, icon) {
        return `<div class="iadm-kpi-card">
            <div class="iadm-kpi-icon ${color}">${icon}</div>
            <div class="iadm-kpi-body">
                <div class="iadm-kpi-label">${label}</div>
                <div class="iadm-kpi-value">${value}</div>
            </div>
        </div>`;
    }

    /* ═══════════════════════════════════════════════════
       SECTION: PRODUCTS
       ═══════════════════════════════════════════════════ */
    let productsPage = 1;
    let productsSearch = '';

    async function renderProducts(page = 1) {
        productsPage = page;
        content.innerHTML = '<div class="iadm-loading"><div class="iadm-spinner"></div><p>Cargando productos...</p></div>';
        try {
            const [prodData, catData] = await Promise.all([
                ajax('integrare_admin_get_products', { page, search: productsSearch }),
                ajax('integrare_admin_get_categories')
            ]);
            categories = catData.categories || [];

            let html = '<div class="iadm-section-header">';
            html += '<div class="iadm-search-bar">';
            html += `<input type="text" class="iadm-search-input" id="iadmProductSearch" placeholder="Buscar productos..." value="${escHtml(productsSearch)}">`;
            html += `<select class="iadm-filter-select" id="iadmProductCatFilter"><option value="">Todas las categorías</option>`;
            categories.forEach(c => { html += `<option value="${c.slug}">${c.name}</option>`; });
            html += '</select></div>';
            html += '<button class="iadm-btn iadm-btn-primary" id="iadmNewProduct"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Nuevo Producto</button>';
            html += '</div>';

            html += '<div class="iadm-card"><div class="iadm-card-body"><div class="iadm-table-wrap"><table class="iadm-table"><thead><tr>';
            html += '<th>Producto</th><th>SKU</th><th>Precio</th><th>U/Caja</th><th>Stock</th><th>Estado</th><th>Acciones</th>';
            html += '</tr></thead><tbody>';

            if (prodData.products.length) {
                prodData.products.forEach(p => {
                    const thumbHtml = p.thumbnail
                        ? `<img src="${p.thumbnail}" class="iadm-thumb" alt="">`
                        : `<div class="iadm-thumb" style="display:flex;align-items:center;justify-content:center;color:var(--iadm-text-muted);font-size:10px">?</div>`;
                    html += `<tr>
                        <td><div class="iadm-product-cell">${thumbHtml}<div><div class="iadm-cell-title">${escHtml(p.title)}</div><div class="iadm-cell-muted">${p.categories.map(c => c.name).join(', ') || 'Sin categoría'}</div></div></div></td>
                        <td class="iadm-cell-muted">${p.sku || '—'}</td>
                        <td class="iadm-cell-title">${money(p.price)}</td>
                        <td>${p.upb || '—'}</td>
                        <td>${p.stock}</td>
                        <td><span class="iadm-badge ${p.status}">${p.status === 'publish' ? 'Activo' : 'Borrador'}</span></td>
                        <td><div class="iadm-actions">
                            <button class="iadm-btn iadm-btn-ghost iadm-btn-sm" data-edit-product="${p.id}">Editar</button>
                            <button class="iadm-btn iadm-btn-danger iadm-btn-sm" data-delete-product="${p.id}" data-name="${escHtml(p.title)}">Eliminar</button>
                        </div></td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="7"><div class="iadm-empty"><p>No se encontraron productos.</p></div></td></tr>';
            }
            html += '</tbody></table></div>';
            html += renderPagination(prodData.current_page, prodData.total_pages, 'products');
            html += '</div></div>';

            content.innerHTML = html;

            // Bind events
            bindProductEvents(prodData);
        } catch (e) {
            content.innerHTML = '<div class="iadm-empty"><p>Error al cargar productos.</p></div>';
            console.error(e);
        }
    }

    function bindProductEvents(prodData) {
        // Search
        const searchInput = $('#iadmProductSearch');
        let searchTimer;
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    productsSearch = searchInput.value;
                    renderProducts(1);
                }, 400);
            });
        }

        // Category filter
        const catFilter = $('#iadmProductCatFilter');
        if (catFilter) {
            catFilter.addEventListener('change', () => renderProducts(1));
        }

        // New product
        const newBtn = $('#iadmNewProduct');
        if (newBtn) {
            newBtn.addEventListener('click', () => openProductModal());
        }

        // Edit buttons
        $$('[data-edit-product]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.editProduct;
                const p = prodData.products.find(x => String(x.id) === String(id));
                if (p) openProductModal(p);
            });
        });

        // Delete buttons
        $$('[data-delete-product]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const name = btn.dataset.name;
                if (!confirm(`¿Eliminar "${name}"?`)) return;
                try {
                    await ajax('integrare_admin_delete_product', { product_id: btn.dataset.deleteProduct });
                    toast('Producto eliminado.', 'success');
                    renderProducts(productsPage);
                } catch (e) {
                    toast(e.message || 'Error al eliminar.', 'error');
                }
            });
        });

        // Pagination
        $$('.iadm-pagination .iadm-page-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const p = parseInt(btn.dataset.page);
                if (p > 0) renderProducts(p);
            });
        });
    }

    function openProductModal(product = null) {
        const isEdit = !!product;
        $('#iadmProductModalTitle').textContent = isEdit ? 'Editar Producto' : 'Nuevo Producto';
        $('#iadmProductId').value = isEdit ? product.id : '';
        $('#iadmProductTitle').value = isEdit ? product.title : '';
        $('#iadmProductSku').value = isEdit ? (product.sku || '') : '';
        $('#iadmProductPrice').value = isEdit ? product.price : '';
        $('#iadmProductUpb').value = isEdit ? product.upb : '';
        $('#iadmProductStock').value = isEdit ? product.stock : '';
        $('#iadmProductStatus').value = isEdit ? product.status : 'publish';
        $('#iadmProductExcerpt').value = isEdit ? (product.excerpt || '') : '';
        $('#iadmProductShowPrice').checked = isEdit ? !!product.show_price : false;

        // Populate image
        const preview = $('#iadmImagePreview');
        const removeBtn = $('#iadmImageRemove');
        const thumbInput = $('#iadmProductThumbId');
        if (isEdit && product.thumbnail && product.thumbnail_id) {
            preview.innerHTML = `<img src="${product.thumbnail}" alt="">`;
            preview.classList.add('has-image');
            thumbInput.value = product.thumbnail_id;
            removeBtn.style.display = 'inline-block';
        } else {
            preview.innerHTML = `
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span>Haz clic o arrastra una imagen</span>
            `;
            preview.classList.remove('has-image');
            thumbInput.value = '';
            removeBtn.style.display = 'none';
        }
        // Reset file input
        $('#iadmImageFile').value = '';

        // Populate category select
        const catSelect = $('#iadmProductCategory');
        catSelect.innerHTML = '<option value="">— Sin categoría —</option>';
        categories.forEach(c => {
            const selected = isEdit && product.categories.some(pc => pc.id === c.id) ? 'selected' : '';
            catSelect.innerHTML += `<option value="${c.id}" ${selected}>${c.name}</option>`;
        });

        // Populate discount tiers
        const tiersContainer = $('#iadmDiscountTiers');
        tiersContainer.innerHTML = '';
        if (isEdit && product.discounts && product.discounts.length) {
            product.discounts.forEach(t => addTierRow(t.min_cajas, t.descuento_pct));
        } else {
            addTierRow(1, 0);
        }

        openModal('iadmProductModal');
    }

    function addTierRow(min = 1, pct = 0) {
        const container = $('#iadmDiscountTiers');
        const row = document.createElement('div');
        row.className = 'iadm-tier-row';
        row.innerHTML = `
            <div class="iadm-tier-field"><label>Mín. Cajas</label><input type="number" class="tier-min" value="${min}" min="1"></div>
            <div class="iadm-tier-field"><label>Descuento %</label><input type="number" class="tier-pct" value="${pct}" min="0" max="100" step="0.5"></div>
            <button type="button" class="iadm-tier-remove" title="Eliminar">&times;</button>
        `;
        container.appendChild(row);
        row.querySelector('.iadm-tier-remove').addEventListener('click', () => row.remove());
    }

    // Add tier button
    document.addEventListener('click', e => {
        if (e.target.id === 'iadmAddTier' || e.target.closest('#iadmAddTier')) {
            addTierRow();
        }
    });

    // ── Image Upload Logic ─────────────────────────── */
    async function handleImageUpload(file) {
        if (!file || !file.type.startsWith('image/')) {
            toast('Selecciona un archivo de imagen válido.', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            toast('La imagen es muy grande (máx. 5MB).', 'error');
            return;
        }

        const preview = $('#iadmImagePreview');
        const removeBtn = $('#iadmImageRemove');
        const thumbInput = $('#iadmProductThumbId');

        // Show local preview immediately
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" alt="">`;
            preview.classList.add('has-image');
        };
        reader.readAsDataURL(file);

        // Upload to server
        preview.classList.add('uploading');
        try {
            const fd = new FormData();
            fd.append('action', 'integrare_admin_upload_image');
            fd.append('nonce', D.nonce);
            fd.append('image', file);

            const resp = await fetch(D.ajaxUrl, { method: 'POST', body: fd });
            const json = await resp.json();

            if (json.success) {
                thumbInput.value = json.data.attachment_id;
                preview.innerHTML = `<img src="${json.data.url}" alt="">`;
                preview.classList.add('has-image');
                removeBtn.style.display = 'inline-block';
                toast('Imagen subida.', 'success');
            } else {
                toast(json.data?.message || 'Error al subir la imagen.', 'error');
                // Revert preview
                if (!thumbInput.value) {
                    preview.innerHTML = `
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Haz clic o arrastra una imagen</span>
                    `;
                    preview.classList.remove('has-image');
                }
            }
        } catch (e) {
            toast('Error de red al subir la imagen.', 'error');
        } finally {
            preview.classList.remove('uploading');
        }
    }

    // Click to select image
    document.addEventListener('click', e => {
        const uploadZone = e.target.closest('#iadmImagePreview');
        if (uploadZone) {
            $('#iadmImageFile').click();
        }
    });

    // File input change
    document.addEventListener('change', e => {
        if (e.target.id === 'iadmImageFile') {
            if (e.target.files.length) {
                handleImageUpload(e.target.files[0]);
            }
        }
    });

    // Drag & drop
    document.addEventListener('dragover', e => {
        const uploadZone = e.target.closest('#iadmImageUpload');
        if (uploadZone) {
            e.preventDefault();
            uploadZone.classList.add('drag-over');
        }
    });
    document.addEventListener('dragleave', e => {
        const uploadZone = e.target.closest('#iadmImageUpload');
        if (uploadZone) {
            uploadZone.classList.remove('drag-over');
        }
    });
    document.addEventListener('drop', e => {
        const uploadZone = e.target.closest('#iadmImageUpload');
        if (uploadZone) {
            e.preventDefault();
            uploadZone.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                handleImageUpload(e.dataTransfer.files[0]);
            }
        }
    });

    // Remove image
    document.addEventListener('click', e => {
        if (e.target.id === 'iadmImageRemove' || e.target.closest('#iadmImageRemove')) {
            const preview = $('#iadmImagePreview');
            preview.innerHTML = `
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span>Haz clic o arrastra una imagen</span>
            `;
            preview.classList.remove('has-image');
            $('#iadmProductThumbId').value = '';
            $('#iadmImageFile').value = '';
            $('#iadmImageRemove').style.display = 'none';
        }
    });

    // Save product
    $('#iadmSaveProduct').addEventListener('click', async () => {
        const id = $('#iadmProductId').value;
        const isEdit = !!id;
        const action = isEdit ? 'integrare_admin_update_product' : 'integrare_admin_create_product';

        // Collect tiers
        const tiers = [];
        $$('#iadmDiscountTiers .iadm-tier-row').forEach(row => {
            const min = parseInt(row.querySelector('.tier-min').value) || 1;
            const pct = parseFloat(row.querySelector('.tier-pct').value) || 0;
            if (pct > 0) tiers.push({ min_cajas: min, descuento_pct: pct });
        });

        const data = {
            title: $('#iadmProductTitle').value,
            sku: $('#iadmProductSku').value,
            price: $('#iadmProductPrice').value,
            upb: $('#iadmProductUpb').value,
            stock: $('#iadmProductStock').value,
            status: $('#iadmProductStatus').value,
            excerpt: $('#iadmProductExcerpt').value,
            category_id: $('#iadmProductCategory').value,
            discount_tiers: JSON.stringify(tiers),
            thumbnail_id: $('#iadmProductThumbId').value || '0',
            show_price: $('#iadmProductShowPrice').checked ? '1' : '0',
        };
        if (isEdit) data.product_id = id;

        try {
            await ajax(action, data);
            toast(isEdit ? 'Producto actualizado.' : 'Producto creado.', 'success');
            closeModal('iadmProductModal');
            renderProducts(productsPage);
        } catch (e) {
            toast(e.message || 'Error al guardar.', 'error');
        }
    });

    /* ═══════════════════════════════════════════════════
       SECTION: ORDERS
       ═══════════════════════════════════════════════════ */
    let ordersPage = 1;
    let ordersStatus = '';
    let currentOrderId = null;

    async function renderOrders(page = 1) {
        ordersPage = page;
        content.innerHTML = '<div class="iadm-loading"><div class="iadm-spinner"></div><p>Cargando pedidos...</p></div>';
        try {
            const d = await ajax('integrare_admin_get_orders', { page, status: ordersStatus });

            // Filter tabs
            let html = '<div class="iadm-filter-tabs">';
            const tabs = [
                { key: '', label: 'Todos', count: d.counts.all },
                { key: 'pending', label: 'Pendientes', count: d.counts.pending },
                { key: 'paid', label: 'Pagados', count: d.counts.paid },
                { key: 'completed', label: 'Completados', count: d.counts.completed },
                { key: 'cancelled', label: 'Cancelados', count: d.counts.cancelled },
            ];
            tabs.forEach(t => {
                html += `<button class="iadm-filter-tab ${ordersStatus === t.key ? 'active' : ''}" data-order-status="${t.key}">${t.label} <span class="count">(${t.count})</span></button>`;
            });
            html += '</div>';

            html += '<div class="iadm-card"><div class="iadm-card-body"><div class="iadm-table-wrap"><table class="iadm-table"><thead><tr>';
            html += '<th>#</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Acciones</th>';
            html += '</tr></thead><tbody>';

            if (d.orders && d.orders.length) {
                d.orders.forEach(o => {
                    html += `<tr>
                        <td class="iadm-cell-title">${o.id}</td>
                        <td>${escHtml(o.display_name || 'N/A')}<br><span class="iadm-cell-muted">${escHtml(o.user_email || '')}</span></td>
                        <td class="iadm-cell-title">${money(o.total)}</td>
                        <td>${statusLabel(o.status)}</td>
                        <td class="iadm-cell-muted">${fmtDate(o.created_at)}</td>
                        <td><div class="iadm-actions">
                            <button class="iadm-btn iadm-btn-ghost iadm-btn-sm" data-edit-order="${o.id}">Editar</button>
                            <button class="iadm-btn iadm-btn-danger iadm-btn-sm" data-delete-order="${o.id}">Eliminar</button>
                        </div></td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="6"><div class="iadm-empty"><p>No hay pedidos.</p></div></td></tr>';
            }
            html += '</tbody></table></div>';
            html += renderPagination(d.current_page, d.total_pages, 'orders');
            html += '</div></div>';

            content.innerHTML = html;

            // Bind order events
            $$('.iadm-filter-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    ordersStatus = tab.dataset.orderStatus;
                    renderOrders(1);
                });
            });
            $$('[data-edit-order]').forEach(btn => {
                btn.addEventListener('click', () => openOrderModal(btn.dataset.editOrder));
            });
            $$('[data-delete-order]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm(`¿Eliminar el pedido #${btn.dataset.deleteOrder}? Esta acción no se puede deshacer.`)) return;
                    try {
                        await ajax('integrare_admin_delete_order', { order_id: btn.dataset.deleteOrder });
                        toast('Pedido eliminado.', 'success');
                        renderOrders(ordersPage);
                    } catch (e) {
                        toast(e.message || 'Error al eliminar.', 'error');
                    }
                });
            });
            $$('.iadm-pagination .iadm-page-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const p = parseInt(btn.dataset.page);
                    if (p > 0) renderOrders(p);
                });
            });
        } catch (e) {
            content.innerHTML = '<div class="iadm-empty"><p>Error al cargar pedidos.</p></div>';
            console.error(e);
        }
    }

    async function openOrderModal(orderId) {
        currentOrderId = orderId;
        const detail = $('#iadmOrderDetail');
        detail.innerHTML = '<div class="iadm-loading"><div class="iadm-spinner"></div></div>';
        openModal('iadmOrderModal');
        try {
            const d = await ajax('integrare_admin_get_order', { order_id: orderId });
            const o = d.order;
            $('#iadmOrderModalTitle').textContent = `Pedido #${o.id}`;

            let html = '<div class="iadm-order-info">';
            html += infoItem('Cliente', escHtml(o.user_name));
            html += infoItem('Email', escHtml(o.user_email));
            html += infoItem('Teléfono', escHtml(o.user_phone || '—'));
            html += infoItem('Total', money(o.total));
            html += infoItem('Fecha', fmtDate(o.created_at));
            html += infoItem('Método de Pago', escHtml(o.payment_method || '—'));
            html += '</div>';

            // Editable fields
            html += '<div class="iadm-form-grid" style="margin-top:20px">';
            html += '<div class="iadm-form-group">';
            html += '<label for="iadmOrderStatus">Estado</label>';
            html += '<select id="iadmOrderStatus" class="iadm-filter-select">';
            const statuses = [
                { value: 'pending', label: 'Pendiente' },
                { value: 'paid', label: 'Pagado' },
                { value: 'completed', label: 'Completado' },
                { value: 'cancelled', label: 'Cancelado' },
            ];
            statuses.forEach(s => {
                html += `<option value="${s.value}" ${o.status === s.value ? 'selected' : ''}>${s.label}</option>`;
            });
            html += '</select></div>';
            html += '<div class="iadm-form-group iadm-col-span-2">';
            html += '<label for="iadmOrderNotes">Notas</label>';
            html += `<textarea id="iadmOrderNotes" rows="3" placeholder="Notas del pedido...">${escHtml(o.notes || '')}</textarea>`;
            html += '</div></div>';

            // Shipping address section
            html += '<h4 class="iadm-order-items-title" style="margin-top:20px">Dirección de Envío</h4>';
            html += '<div class="iadm-form-grid">';
            html += `<div class="iadm-form-group"><label>Nombre</label><input type="text" id="iadmShipName" value="${escHtml(o.shipping_name || '')}" placeholder="Nombre completo"></div>`;
            html += `<div class="iadm-form-group"><label>Teléfono</label><input type="text" id="iadmShipPhone" value="${escHtml(o.shipping_phone || '')}" placeholder="Teléfono"></div>`;
            html += `<div class="iadm-form-group iadm-col-span-2"><label>Dirección</label><input type="text" id="iadmShipAddress" value="${escHtml(o.shipping_address || '')}" placeholder="Calle, número"></div>`;
            html += `<div class="iadm-form-group"><label>Ciudad</label><input type="text" id="iadmShipCity" value="${escHtml(o.shipping_city || '')}" placeholder="Ciudad"></div>`;
            html += `<div class="iadm-form-group"><label>Estado</label><input type="text" id="iadmShipState" value="${escHtml(o.shipping_state || '')}" placeholder="Estado"></div>`;
            html += `<div class="iadm-form-group"><label>C.P.</label><input type="text" id="iadmShipZip" value="${escHtml(o.shipping_zip || '')}" placeholder="00000"></div>`;
            html += '</div>';

            if (o.items && o.items.length) {
                html += '<h4 class="iadm-order-items-title">Artículos del Pedido</h4>';
                html += '<div class="iadm-table-wrap"><table class="iadm-table"><thead><tr>';
                html += '<th>Producto</th><th>Cajas</th><th>U/Caja</th><th>Precio Unit.</th><th>Desc. %</th><th>Subtotal</th>';
                html += '</tr></thead><tbody>';
                o.items.forEach(it => {
                    html += `<tr>
                        <td class="iadm-cell-title">${escHtml(it.product_name || 'Producto #' + it.product_id)}</td>
                        <td>${it.quantity_boxes}</td>
                        <td>${it.units_per_box}</td>
                        <td>${money(it.unit_price)}</td>
                        <td>${parseFloat(it.discount_pct || 0).toFixed(1)}%</td>
                        <td class="iadm-cell-title">${money(it.line_total)}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
            }
            detail.innerHTML = html;
        } catch (e) {
            detail.innerHTML = '<div class="iadm-empty"><p>Error al cargar el pedido.</p></div>';
        }
    }

    // Save order handler
    document.addEventListener('click', async e => {
        if (e.target.id === 'iadmSaveOrder' || e.target.closest('#iadmSaveOrder')) {
            if (!currentOrderId) return;
            const statusEl = $('#iadmOrderStatus');
            const notesEl = $('#iadmOrderNotes');
            if (!statusEl) return;

            try {
                await ajax('integrare_admin_update_order', {
                    order_id: currentOrderId,
                    status: statusEl.value,
                    notes: notesEl ? notesEl.value : '',
                    shipping_name: ($('#iadmShipName') || {}).value || '',
                    shipping_phone: ($('#iadmShipPhone') || {}).value || '',
                    shipping_address: ($('#iadmShipAddress') || {}).value || '',
                    shipping_city: ($('#iadmShipCity') || {}).value || '',
                    shipping_state: ($('#iadmShipState') || {}).value || '',
                    shipping_zip: ($('#iadmShipZip') || {}).value || '',
                });
                toast('Pedido actualizado.', 'success');
                closeModal('iadmOrderModal');
                renderOrders(ordersPage);
            } catch (e) {
                toast(e.message || 'Error al actualizar.', 'error');
            }
        }
    });

    // Delete order from modal handler
    document.addEventListener('click', async e => {
        if (e.target.id === 'iadmDeleteOrder' || e.target.closest('#iadmDeleteOrder')) {
            if (!currentOrderId) return;
            if (!confirm(`¿Eliminar el pedido #${currentOrderId}? Esta acción no se puede deshacer.`)) return;

            try {
                await ajax('integrare_admin_delete_order', { order_id: currentOrderId });
                toast('Pedido eliminado.', 'success');
                closeModal('iadmOrderModal');
                currentOrderId = null;
                renderOrders(ordersPage);
            } catch (e) {
                toast(e.message || 'Error al eliminar.', 'error');
            }
        }
    });

    function infoItem(label, value) {
        return `<div class="iadm-order-info-item"><span class="iadm-order-info-label">${label}</span><span class="iadm-order-info-value">${value}</span></div>`;
    }

    /* ═══════════════════════════════════════════════════
       SECTION: CLIENTS
       ═══════════════════════════════════════════════════ */
    let clientsPage = 1;
    let clientsSearch = '';

    async function renderClients(page = 1) {
        clientsPage = page;
        content.innerHTML = '<div class="iadm-loading"><div class="iadm-spinner"></div><p>Cargando clientes...</p></div>';
        try {
            const d = await ajax('integrare_admin_get_users', { page, search: clientsSearch });

            let html = '<div class="iadm-section-header">';
            html += '<div class="iadm-search-bar">';
            html += `<input type="text" class="iadm-search-input" id="iadmClientSearch" placeholder="Buscar clientes..." value="${escHtml(clientsSearch)}">`;
            html += '</div></div>';

            html += '<div class="iadm-card"><div class="iadm-card-body"><div class="iadm-table-wrap"><table class="iadm-table"><thead><tr>';
            html += '<th>Nombre</th><th>Email</th><th>Teléfono</th><th>Pedidos</th><th>Total Gastado</th><th>Registro</th>';
            html += '</tr></thead><tbody>';

            if (d.users.length) {
                d.users.forEach(u => {
                    html += `<tr>
                        <td class="iadm-cell-title">${escHtml(u.display_name)}</td>
                        <td>${escHtml(u.user_email)}</td>
                        <td class="iadm-cell-muted">${u.phone || '—'}</td>
                        <td>${u.total_orders}</td>
                        <td class="iadm-cell-title">${money(u.total_spent)}</td>
                        <td class="iadm-cell-muted">${fmtDate(u.user_registered)}</td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="6"><div class="iadm-empty"><p>No se encontraron clientes.</p></div></td></tr>';
            }
            html += '</tbody></table></div>';
            html += renderPagination(d.current_page, d.total_pages, 'clients');
            html += '</div></div>';

            content.innerHTML = html;

            // Search
            const searchInput = $('#iadmClientSearch');
            let timer;
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        clientsSearch = searchInput.value;
                        renderClients(1);
                    }, 400);
                });
            }
            // Pagination
            $$('.iadm-pagination .iadm-page-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const p = parseInt(btn.dataset.page);
                    if (p > 0) renderClients(p);
                });
            });
        } catch (e) {
            content.innerHTML = '<div class="iadm-empty"><p>Error al cargar clientes.</p></div>';
            console.error(e);
        }
    }

    /* ═══════════════════════════════════════════════════
       SECTION: CATEGORIES
       ═══════════════════════════════════════════════════ */
    async function renderCategories() {
        content.innerHTML = '<div class="iadm-loading"><div class="iadm-spinner"></div><p>Cargando categorías...</p></div>';
        try {
            const d = await ajax('integrare_admin_get_categories');
            categories = d.categories || [];

            let html = '<div class="iadm-section-header">';
            html += '<h2>Categorías de Productos</h2>';
            html += '<button class="iadm-btn iadm-btn-primary" id="iadmNewCategory"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Nueva Categoría</button>';
            html += '</div>';

            html += '<div class="iadm-card"><div class="iadm-card-body"><div class="iadm-table-wrap"><table class="iadm-table"><thead><tr>';
            html += '<th>Nombre</th><th>Slug</th><th>Productos</th><th>Acciones</th>';
            html += '</tr></thead><tbody>';

            if (categories.length) {
                categories.forEach(c => {
                    html += `<tr>
                        <td class="iadm-cell-title">${escHtml(c.name)}</td>
                        <td class="iadm-cell-muted">${c.slug}</td>
                        <td>${c.count}</td>
                        <td><div class="iadm-actions">
                            <button class="iadm-btn iadm-btn-ghost iadm-btn-sm" data-edit-cat="${c.id}" data-name="${escHtml(c.name)}">Editar</button>
                            <button class="iadm-btn iadm-btn-danger iadm-btn-sm" data-delete-cat="${c.id}" data-name="${escHtml(c.name)}">Eliminar</button>
                        </div></td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="4"><div class="iadm-empty"><p>No hay categorías.</p></div></td></tr>';
            }
            html += '</tbody></table></div></div></div>';

            content.innerHTML = html;

            // Bind
            $('#iadmNewCategory').addEventListener('click', () => openCategoryModal());
            $$('[data-edit-cat]').forEach(btn => {
                btn.addEventListener('click', () => openCategoryModal(btn.dataset.editCat, btn.dataset.name));
            });
            $$('[data-delete-cat]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm(`¿Eliminar categoría "${btn.dataset.name}"?`)) return;
                    try {
                        await ajax('integrare_admin_delete_category', { term_id: btn.dataset.deleteCat });
                        toast('Categoría eliminada.', 'success');
                        renderCategories();
                    } catch (e) {
                        toast(e.message || 'Error.', 'error');
                    }
                });
            });
        } catch (e) {
            content.innerHTML = '<div class="iadm-empty"><p>Error al cargar categorías.</p></div>';
            console.error(e);
        }
    }

    function openCategoryModal(id, name) {
        const isEdit = !!id;
        $('#iadmCategoryModalTitle').textContent = isEdit ? 'Editar Categoría' : 'Nueva Categoría';
        $('#iadmCategoryId').value = id || '';
        $('#iadmCategoryName').value = name || '';
        openModal('iadmCategoryModal');
    }

    $('#iadmSaveCategory').addEventListener('click', async () => {
        const id = $('#iadmCategoryId').value;
        const name = $('#iadmCategoryName').value.trim();
        if (!name) return toast('Ingresa un nombre.', 'error');
        const isEdit = !!id;
        const action = isEdit ? 'integrare_admin_update_category' : 'integrare_admin_create_category';
        const data = { name };
        if (isEdit) data.term_id = id;
        try {
            await ajax(action, data);
            toast(isEdit ? 'Categoría actualizada.' : 'Categoría creada.', 'success');
            closeModal('iadmCategoryModal');
            renderCategories();
        } catch (e) {
            toast(e.message || 'Error.', 'error');
        }
    });

    /* ═══════════════════════════════════════════════════
       ROUTER
       ═══════════════════════════════════════════════════ */
    const sections = { dashboard: renderDashboard, products: renderProducts, orders: renderOrders, clients: renderClients, categories: renderCategories };
    const titles = { dashboard: 'Dashboard', products: 'Productos', orders: 'Pedidos', clients: 'Clientes', categories: 'Categorías' };

    function navigate(hash) {
        const section = (hash || '').replace('#', '') || 'dashboard';
        if (!sections[section]) return navigate('#dashboard');
        currentSection = section;

        // Update nav
        $$('.iadm-nav-link').forEach(l => l.classList.toggle('active', l.dataset.section === section));

        // Update title
        const title = $('#iadmPageTitle');
        if (title) title.textContent = titles[section] || section;

        // Close mobile sidebar
        const sidebar = $('#iadmSidebar');
        if (sidebar) sidebar.classList.remove('open');

        // Render
        sections[section]();
    }

    window.addEventListener('hashchange', () => navigate(location.hash));

    /* ── Sidebar Navigation ─────────────────────────── */
    $$('.iadm-nav-link[data-section]').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const section = link.dataset.section;
            location.hash = section;
        });
    });

    /* ── Mobile Menu Toggle ─────────────────────────── */
    const menuToggle = $('#iadmMenuToggle');
    const sidebarClose = $('#iadmSidebarClose');
    const sidebar = $('#iadmSidebar');

    if (menuToggle) menuToggle.addEventListener('click', () => sidebar.classList.add('open'));
    if (sidebarClose) sidebarClose.addEventListener('click', () => sidebar.classList.remove('open'));

    /* ── Escape HTML Helper ─────────────────────────── */
    function escHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /* ── Init ───────────────────────────────────────── */
    navigate(location.hash || '#dashboard');

    /* ── User Dropdown ─────────────────────────────── */
    const userTrigger = $('#iadmUserTrigger');
    const dropdownMenu = $('#iadmDropdownMenu');

    if (userTrigger && dropdownMenu) {
        userTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdownMenu.classList.toggle('open');
            userTrigger.setAttribute('aria-expanded', isOpen);
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#iadmUserDropdown')) {
                dropdownMenu.classList.remove('open');
                userTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && dropdownMenu.classList.contains('open')) {
                dropdownMenu.classList.remove('open');
                userTrigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ── Logout ─────────────────────────────────────── */
    const logoutBtn = $('#iadmLogoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', D.ajaxUrl);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = () => {
                window.location.href = D.baseUrl;
            };
            xhr.send('action=integrare_logout&nonce=' + encodeURIComponent(D.nonce));
        });
    }

})();
