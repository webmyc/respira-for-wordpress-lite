<?php
/**
 * Fired during plugin activation.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.0
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class Respira_Lite_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Creates database tables and sets up default options.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();

		// Set activation timestamp.
		add_option( 'respira_lite_activated_at', current_time( 'mysql' ) );

		// Schedule daily cron job for audit log cleanup.
		if ( ! wp_next_scheduled( 'respira_lite_cleanup_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'respira_lite_cleanup_logs' );
		}

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Create database tables.
	 *
	 * @since 1.0.0
	 */
	private static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		// API Keys table.
		$table_name = $wpdb->prefix . 'respira_lite_api_keys';
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			api_key varchar(255) NOT NULL,
			user_id bigint(20) NOT NULL,
			name varchar(255) DEFAULT NULL,
			permissions text DEFAULT NULL,
			last_used datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			is_active tinyint(1) DEFAULT 1,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY is_active (is_active)
		) $charset_collate;";

		dbDelta( $sql );

		// Audit Log table.
		$table_name = $wpdb->prefix . 'respira_lite_audit_log';
		$sql        = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			action varchar(100) NOT NULL,
			object_id bigint(20) NOT NULL,
			object_type varchar(50) NOT NULL,
			user_id bigint(20) NOT NULL,
			metadata text DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY action (action),
			KEY object_id (object_id),
			KEY object_type (object_type),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Set default options.
	 *
	 * @since 1.0.0
	 */
	private static function set_default_options() {
		// Set default security validation setting.
		add_option( 'respira_lite_security_validation', '1' );

		// Set plugin version.
		add_option( 'respira_lite_version', RESPIRA_LITE_VERSION );
	}
}
