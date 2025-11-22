<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.0
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since 1.0.0
 */
class Respira_Lite_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Performs cleanup tasks during deactivation.
	 * Note: Does not delete data - that's handled by uninstall.php
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();

		// Clear any transients.
		delete_transient( 'respira_lite_site_info_cache' );

		// Clear scheduled cron jobs.
		wp_clear_scheduled_hook( 'respira_lite_cleanup_logs' );
	}
}
