<?php
/**
 * Plugin Name: Seasonal Animation
 * Description: Automatically adds seasonal effects (snow, leaves, etc.) with preview and crash protection features.
 * Version: 1.0.0
 * Author: Anushka Dahanayake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------------
// CRASH PROTECTION / SAFE MODE
// -----------------------------------------------------------------------------
// If the URL contains ?seasonal_safe_mode=1, we do NOT initialize the plugin logic.
// This allows an admin to access the site/admin to disable the plugin if it causes issues.
if ( isset( $_GET['seasonal_safe_mode'] ) && '1' === $_GET['seasonal_safe_mode'] ) {
	// Optionally log this usage or display a admin notice if needed, but for "Safe Mode"
	// we want to be as quiet and non-intrusive as possible to guarantee site load.
	return;
}

// Define Plugin Constants
define( 'SEASONAL_ANIMATION_VERSION', '1.0.0' );
define( 'SEASONAL_ANIMATION_PATH', plugin_dir_path( __FILE__ ) );
define( 'SEASONAL_ANIMATION_URL', plugin_dir_url( __FILE__ ) );

// Include Core Classes
require_once SEASONAL_ANIMATION_PATH . 'includes/class-admin.php';
require_once SEASONAL_ANIMATION_PATH . 'includes/class-frontend.php';

// Initialize Plugin
function seasonal_animation_init() {
	$admin    = new Seasonal_Animation_Admin();
	$frontend = new Seasonal_Animation_Frontend();

	$admin->init();
	$frontend->init();
}
add_action( 'plugins_loaded', 'seasonal_animation_init' );
