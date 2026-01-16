<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Seasonal_Animation
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete the plugin options from the database
delete_option( 'seasonal_animation_settings' );
