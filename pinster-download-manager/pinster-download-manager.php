<?php
/**
 * Plugin Name: Pinster Download Manager
 * Plugin URI: https://example.com/pinster-download-manager
 * Description: Manages resume template downloads: custom post type, file uploads, and download tracking.
 * Version: 1.0.0
 * Author: Pinster
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pinster-download-manager
 * Requires at least: 5.9
 * Requires PHP: 7.4
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PINSTER_DM_VERSION', '1.1.0' );
define( 'PINSTER_DM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PINSTER_DM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PINSTER_DM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Minimum PHP and WordPress versions.
if ( version_compare( PHP_VERSION, '7.4', '<' ) || version_compare( get_bloginfo( 'version' ), '5.9', '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>';
			esc_html_e( 'Pinster Download Manager requires PHP 7.4+ and WordPress 5.9+.', 'pinster-download-manager' );
			echo '</p></div>';
		}
	);
	return;
}

require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-pinster-download-manager.php';
require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-installer.php';
require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-subscribers.php';
require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-secure-storage.php';
require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-email-sender.php';
require_once PINSTER_DM_PLUGIN_DIR . 'includes/class-gated-download.php';

register_activation_hook( __FILE__, array( 'Pinster_DM_Installer', 'activate' ) );

add_action( 'init', function () {
	// DB upgrades.
	$db_version = get_option( 'pinster_dm_db_version', '' );
	if ( PINSTER_DM_VERSION !== $db_version ) {
		Pinster_DM_Installer::create_tables();
		update_option( 'pinster_dm_db_version', PINSTER_DM_VERSION );
	}
}, 20 );

add_action( 'init', function () {
	if ( get_option( 'pinster_dm_rewrite_flush', 0 ) ) {
		flush_rewrite_rules();
		delete_option( 'pinster_dm_rewrite_flush' );
	}
}, 999 );

/**
 * Boot the plugin.
 *
 * @return Pinster_Download_Manager|null Instance or null if not loaded.
 */
function pinster_dm_plugin() {
	return Pinster_Download_Manager::instance();
}

add_action( 'plugins_loaded', 'pinster_dm_plugin', 5 );
