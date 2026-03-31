<?php
/**
 * Plugin Name: Integrare Core
 * Description: Sistema B2B de distribución farmacéutica — productos, carrito, pedidos e inventario custom.
 * Version: 1.0.0
 * Author: Integrare
 * Text Domain: integrare-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Constants ──────────────────────────────────────────────
define( 'INTEGRARE_VERSION', '1.1.21' );
define( 'INTEGRARE_PATH', plugin_dir_path( __FILE__ ) );
define( 'INTEGRARE_URL', plugin_dir_url( __FILE__ ) );
define( 'INTEGRARE_DB_VERSION', '1.2.0' );

// ── Include Classes ────────────────────────────────────────
require_once INTEGRARE_PATH . 'includes/class-integrare-activator.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-cpt.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-metaboxes.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-pricing.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-cart.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-orders.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-ajax.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-payments.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-auth.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-front.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-admin-ajax.php';
require_once INTEGRARE_PATH . 'admin/class-integrare-admin.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-dolibarr.php';
require_once INTEGRARE_PATH . 'includes/class-integrare-sync.php';

// ── Activation / Deactivation ──────────────────────────────
register_activation_hook( __FILE__, array( 'Integrare_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Integrare_Activator', 'deactivate' ) );

// ── Bootstrap ──────────────────────────────────────────────
add_action( 'plugins_loaded', 'integrare_core_init' );

function integrare_core_init() {
    // Run DB upgrade if version changed
    if ( get_option( 'integrare_db_version' ) !== INTEGRARE_DB_VERSION ) {
        Integrare_Activator::activate();
    }

    // Flush rewrite rules when plugin version changes
    if ( get_option( 'integrare_version' ) !== INTEGRARE_VERSION ) {
        add_action( 'init', function() {
            flush_rewrite_rules();
        }, 99 );
        update_option( 'integrare_version', INTEGRARE_VERSION );
    }

    // CPT & Taxonomy
    new Integrare_CPT();

    // Metaboxes
    new Integrare_Metaboxes();

    // AJAX Handlers
    new Integrare_Ajax();

    // Frontend Router (standalone /tienda)
    new Integrare_Front();

    // Auth (login, register, password reset)
    new Integrare_Auth();

    // Admin AJAX (frontend dashboard)
    new Integrare_Admin_Ajax();

    // Dolibarr sync
    new Integrare_Sync();

    // Admin
    if ( is_admin() ) {
        new Integrare_Admin();
    }
}
