<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Integrare_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
    }

    /**
     * Add settings page under Settings → Integrare.
     */
    public function add_settings_page() {
        add_options_page(
            'Integrare',
            'Integrare',
            'manage_options',
            'integrare-settings',
            array( $this, 'render_settings' )
        );
    }

    /**
     * Settings page.
     */
    public function render_settings() {
        include INTEGRARE_PATH . 'admin/views/admin-settings.php';
    }
}
