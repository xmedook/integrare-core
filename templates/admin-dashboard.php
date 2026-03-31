<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- ── Admin Dashboard Shell ──────────────────────────── -->
<div class="iadm-wrapper">

    <!-- ── Sidebar ────────────────────────────────────── -->
    <aside class="iadm-sidebar" id="iadmSidebar">
        <div class="iadm-sidebar-header">
            <a href="<?php echo home_url( '/tienda/' ); ?>" class="iadm-logo">
                <img src="https://integrare.mx/wp-content/uploads/2025/11/integrare-solo.svg" alt="Integrare" height="28">
            </a>
            <button class="iadm-sidebar-close" id="iadmSidebarClose" aria-label="Cerrar menú">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <nav class="iadm-nav">
            <a href="#dashboard" class="iadm-nav-link active" data-section="dashboard">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="#products" class="iadm-nav-link" data-section="products">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                <span>Productos</span>
            </a>
            <a href="#orders" class="iadm-nav-link" data-section="orders">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>Pedidos</span>
            </a>
            <a href="#clients" class="iadm-nav-link" data-section="clients">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                <span>Clientes</span>
            </a>
            <a href="#categories" class="iadm-nav-link" data-section="categories">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                <span>Categorías</span>
            </a>
        </nav>

        <div class="iadm-sidebar-footer">
            <a href="<?php echo home_url( '/tienda/' ); ?>" class="iadm-nav-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <span>Volver a la Tienda</span>
            </a>
        </div>
    </aside>

    <!-- ── Main Content ───────────────────────────────── -->
    <main class="iadm-main">
        <header class="iadm-topbar">
            <button class="iadm-menu-toggle" id="iadmMenuToggle" aria-label="Menú">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <h1 class="iadm-page-title" id="iadmPageTitle">Dashboard</h1>
            <div class="iadm-topbar-right">
                <div class="iadm-user-dropdown" id="iadmUserDropdown">
                    <button class="iadm-user-trigger" id="iadmUserTrigger" aria-haspopup="true" aria-expanded="false">
                        <span class="iadm-user-name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
                        <div class="iadm-user-avatar">
                            <?php echo substr( wp_get_current_user()->display_name, 0, 1 ); ?>
                        </div>
                        <svg class="iadm-user-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="iadm-dropdown-menu" id="iadmDropdownMenu">
                        <a href="<?php echo home_url( '/tienda/' ); ?>" class="iadm-dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Ir a la Tienda</span>
                        </a>
                        <div class="iadm-dropdown-divider"></div>
                        <button class="iadm-dropdown-item iadm-dropdown-logout" id="iadmLogoutBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            <span>Cerrar Sesión</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="iadm-content" id="iadmContent">
            <!-- Content is loaded dynamically by JS -->
            <div class="iadm-loading">
                <div class="iadm-spinner"></div>
                <p>Cargando...</p>
            </div>
        </div>
    </main>
</div>

<!-- ── Product Modal ──────────────────────────────────── -->
<div class="iadm-modal-overlay" id="iadmProductModal">
    <div class="iadm-modal iadm-modal-lg">
        <div class="iadm-modal-header">
            <h2 id="iadmProductModalTitle">Nuevo Producto</h2>
            <button class="iadm-modal-close" data-close-modal aria-label="Cerrar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="iadm-modal-body">
            <form id="iadmProductForm">
                <input type="hidden" name="product_id" id="iadmProductId" value="">
                <div class="iadm-form-grid">
                    <div class="iadm-form-group iadm-col-span-2">
                        <label for="iadmProductTitle">Nombre del Producto *</label>
                        <input type="text" id="iadmProductTitle" name="title" required placeholder="Ej: Ibuprofeno 400mg">
                    </div>
                    <div class="iadm-form-group">
                        <label for="iadmProductSku">SKU / Código</label>
                        <input type="text" id="iadmProductSku" name="sku" placeholder="Ej: IBU-400-12">
                    </div>
                    <div class="iadm-form-group">
                        <label for="iadmProductCategory">Categoría</label>
                        <select id="iadmProductCategory" name="category_id">
                            <option value="">— Sin categoría —</option>
                        </select>
                    </div>
                    <div class="iadm-form-group">
                        <label for="iadmProductPrice">Precio por Unidad ($)</label>
                        <input type="number" id="iadmProductPrice" name="price" step="0.01" min="0" placeholder="10.00">
                    </div>
                    <div class="iadm-form-group">
                        <label for="iadmProductUpb">Unidades por Caja</label>
                        <input type="number" id="iadmProductUpb" name="upb" step="1" min="1" placeholder="12">
                    </div>
                    <div class="iadm-form-group">
                        <label for="iadmProductStock">Stock (unidades)</label>
                        <input type="number" id="iadmProductStock" name="stock" step="1" min="0" placeholder="1000">
                    </div>
                    <div class="iadm-form-group">
                        <label for="iadmProductStatus">Estado</label>
                        <select id="iadmProductStatus" name="status">
                            <option value="publish">Publicado</option>
                            <option value="draft">Borrador</option>
                        </select>
                    </div>
                    <div class="iadm-form-group iadm-col-span-2">
                        <div class="iadm-toggle-row">
                            <label class="iadm-toggle">
                                <input type="checkbox" id="iadmProductShowPrice">
                                <span class="iadm-toggle-slider"></span>
                            </label>
                            <span class="iadm-toggle-label">Mostrar precio a usuarios sin cuenta</span>
                        </div>
                    </div>
                    <div class="iadm-form-group iadm-col-span-2">
                        <label for="iadmProductExcerpt">Descripción Corta</label>
                        <textarea id="iadmProductExcerpt" name="excerpt" rows="2" placeholder="Breve descripción del producto..."></textarea>
                    </div>
                    <div class="iadm-form-group iadm-col-span-2">
                        <label>Imagen del Producto</label>
                        <input type="hidden" id="iadmProductThumbId" name="thumbnail_id" value="">
                        <div class="iadm-image-upload" id="iadmImageUpload">
                            <div class="iadm-image-preview" id="iadmImagePreview">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                                <span>Haz clic o arrastra una imagen</span>
                            </div>
                            <input type="file" id="iadmImageFile" accept="image/*" style="display:none">
                            <button type="button" class="iadm-btn-text iadm-image-remove" id="iadmImageRemove" style="display:none">Eliminar imagen</button>
                        </div>
                    </div>
                    <div class="iadm-form-group iadm-col-span-2">
                        <label>Descuentos por Volumen</label>
                        <div class="iadm-discount-tiers" id="iadmDiscountTiers">
                            <div class="iadm-tier-row">
                                <div class="iadm-tier-field">
                                    <label>Mín. Cajas</label>
                                    <input type="number" class="tier-min" value="1" min="1">
                                </div>
                                <div class="iadm-tier-field">
                                    <label>Descuento %</label>
                                    <input type="number" class="tier-pct" value="0" min="0" max="100" step="0.5">
                                </div>
                                <button type="button" class="iadm-tier-remove" title="Eliminar">&times;</button>
                            </div>
                        </div>
                        <button type="button" class="iadm-btn-text" id="iadmAddTier">+ Agregar nivel de descuento</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="iadm-modal-footer">
            <button type="button" class="iadm-btn iadm-btn-ghost" data-close-modal>Cancelar</button>
            <button type="button" class="iadm-btn iadm-btn-primary" id="iadmSaveProduct">Guardar Producto</button>
        </div>
    </div>
</div>

<!-- ── Order Edit Modal ──────────────────────────────── -->
<div class="iadm-modal-overlay" id="iadmOrderModal">
    <div class="iadm-modal iadm-modal-md">
        <div class="iadm-modal-header">
            <h2 id="iadmOrderModalTitle">Detalle del Pedido</h2>
            <button class="iadm-modal-close" data-close-modal aria-label="Cerrar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="iadm-modal-body" id="iadmOrderDetail">
            <div class="iadm-loading"><div class="iadm-spinner"></div></div>
        </div>
        <div class="iadm-modal-footer">
            <button type="button" class="iadm-btn iadm-btn-danger" id="iadmDeleteOrder" style="margin-right:auto">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                Eliminar
            </button>
            <button type="button" class="iadm-btn iadm-btn-ghost" data-close-modal>Cancelar</button>
            <button type="button" class="iadm-btn iadm-btn-primary" id="iadmSaveOrder">Guardar Cambios</button>
        </div>
    </div>
</div>

<!-- ── Category Modal ─────────────────────────────────── -->
<div class="iadm-modal-overlay" id="iadmCategoryModal">
    <div class="iadm-modal iadm-modal-sm">
        <div class="iadm-modal-header">
            <h2 id="iadmCategoryModalTitle">Nueva Categoría</h2>
            <button class="iadm-modal-close" data-close-modal aria-label="Cerrar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="iadm-modal-body">
            <form id="iadmCategoryForm">
                <input type="hidden" name="term_id" id="iadmCategoryId" value="">
                <div class="iadm-form-group">
                    <label for="iadmCategoryName">Nombre de la Categoría *</label>
                    <input type="text" id="iadmCategoryName" name="name" required placeholder="Ej: Analgésicos">
                </div>
            </form>
        </div>
        <div class="iadm-modal-footer">
            <button type="button" class="iadm-btn iadm-btn-ghost" data-close-modal>Cancelar</button>
            <button type="button" class="iadm-btn iadm-btn-primary" id="iadmSaveCategory">Guardar</button>
        </div>
    </div>
</div>

<!-- ── Toast ──────────────────────────────────────────── -->
<div class="iadm-toast" id="iadmToast"></div>
