<?php
/**
 * Plugin activation: create subscriber table and secure storage dir.
 *
 * @package Pinster_Download_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Pinster_DM_Installer
 */
class Pinster_DM_Installer {

	/**
	 * Subscriber table name (without prefix).
	 *
	 * @var string
	 */
	const TABLE_SUBSCRIBERS = 'pinster_dm_subscribers';

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		self::create_tables();
		self::ensure_secure_dir();
		update_option( 'pinster_dm_rewrite_flush', 1 );
	}

	/**
	 * Create custom tables.
	 */
	public static function create_tables() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_SUBSCRIBERS;
		$charset = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(120) NULL,
			email varchar(255) NOT NULL,
			template_id bigint(20) unsigned NOT NULL,
			consent tinyint(1) unsigned NOT NULL DEFAULT 0,
			ip varchar(45) NULL,
			user_agent varchar(255) NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY template_id (template_id),
			KEY email (email(100))
		) $charset;";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Ensure secure storage directory exists and is protected.
	 *
	 * @return string Path to secure dir.
	 */
	public static function ensure_secure_dir() {
		$dir = self::get_secure_base_dir();
		if ( ! wp_mkdir_p( $dir ) ) {
			return $dir;
		}
		$htaccess = $dir . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $htaccess, "Deny from all\n" );
		}
		$index = $dir . 'index.php';
		if ( ! file_exists( $index ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
		return $dir;
	}

	/**
	 * Get base path for secure file storage (not web-accessible).
	 *
	 * @return string
	 */
	public static function get_secure_base_dir() {
		return trailingslashit( WP_CONTENT_DIR ) . 'pinster-secure/';
	}

	/**
	 * Get secure file path for a template (template-specific subdir).
	 *
	 * @param int $template_id Post ID.
	 * @return string
	 */
	public static function get_secure_dir_for_template( $template_id ) {
		return self::get_secure_base_dir() . 'template-' . absint( $template_id ) . '/';
	}
}
