<?php
/**
 * OAuth Callback Handler — Standalone file.
 *
 * This file exists outside WordPress routing to avoid hosting
 * WAF / mod_security rules that block requests with encoded
 * slashes (%2F) in query strings (common in OAuth codes).
 */

// Bootstrap WordPress
require_once dirname( __FILE__ ) . '/../../../wp-load.php';

// Delegate to the auth class
if ( class_exists( 'Integrare_Auth' ) ) {
    Integrare_Auth::handle_oauth_callback();
}

// Fallback: if callback didn't redirect, go to auth page
wp_redirect( home_url( '/tienda/auth/?vista=login&oauth_error=callback_failed' ) );
exit;
