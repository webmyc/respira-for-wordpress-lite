<?php
/**
 * Plugin Name: Respira for WordPress Lite
 * Plugin URI: https://respira.press
 * Description: Free version - Connect AI coding assistants like Cursor and Claude Code to edit your WordPress site with Gutenberg. Limited to 30 edits/month.
 * Version: 1.0.1
 * Author: Respira
 * Author URI: https://respira.press
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: respira-for-wordpress-lite
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Respira_For_WordPress_Lite
 */

declare(strict_types=1);

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
if ( ! defined( 'RESPIRA_LITE_VERSION' ) ) {
	define( 'RESPIRA_LITE_VERSION', '1.0.1' );
}

/**
 * Plugin directory path.
 */
if ( ! defined( 'RESPIRA_LITE_PLUGIN_DIR' ) ) {
	define( 'RESPIRA_LITE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Plugin directory URL.
 */
if ( ! defined( 'RESPIRA_LITE_PLUGIN_URL' ) ) {
	define( 'RESPIRA_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Plugin basename.
 */
if ( ! defined( 'RESPIRA_LITE_PLUGIN_BASENAME' ) ) {
	define( 'RESPIRA_LITE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * REST API namespace.
 */
if ( ! defined( 'RESPIRA_LITE_REST_NAMESPACE' ) ) {
	define( 'RESPIRA_LITE_REST_NAMESPACE', 'respira-lite/v1' );
}

/**
 * Monthly edit limit for Lite version.
 */
if ( ! defined( 'RESPIRA_LITE_MONTHLY_LIMIT' ) ) {
	define( 'RESPIRA_LITE_MONTHLY_LIMIT', 30 );
}

/**
 * Audit log retention days for Lite version.
 */
if ( ! defined( 'RESPIRA_LITE_AUDIT_RETENTION_DAYS' ) ) {
	define( 'RESPIRA_LITE_AUDIT_RETENTION_DAYS', 3 );
}

/**
 * Plugin file constant.
 */
if ( ! defined( 'RESPIRA_LITE_PLUGIN_FILE' ) ) {
	define( 'RESPIRA_LITE_PLUGIN_FILE', __FILE__ );
}

/**
 * Maximum API keys allowed per user in Lite version.
 */
if ( ! defined( 'RESPIRA_LITE_MAX_API_KEYS' ) ) {
	define( 'RESPIRA_LITE_MAX_API_KEYS', 3 );
}

/**
 * Boolean constant to identify this is the Lite version.
 */
if ( ! defined( 'RESPIRA_LITE' ) ) {
	define( 'RESPIRA_LITE', true );
}

/**
 * The code that runs during plugin activation.
 */
if ( ! function_exists( 'activate_respira_for_wordpress_lite' ) ) {
	function activate_respira_for_wordpress_lite() {
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-activator.php';
		Respira_Lite_Activator::activate();
	}
}

/**
 * The code that runs during plugin deactivation.
 */
if ( ! function_exists( 'deactivate_respira_for_wordpress_lite' ) ) {
	function deactivate_respira_for_wordpress_lite() {
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-deactivator.php';
		Respira_Lite_Deactivator::deactivate();
	}
}

register_activation_hook( __FILE__, 'activate_respira_for_wordpress_lite' );
register_deactivation_hook( __FILE__, 'deactivate_respira_for_wordpress_lite' );

/**
 * The core plugin class.
 */
require RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-core.php';

/**
 * Register cron hook for audit log cleanup.
 */
add_action( 'respira_lite_cleanup_logs', array( 'Respira_Lite_Core', 'cleanup_old_logs' ) );

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'run_respira_for_wordpress_lite' ) ) {
	function run_respira_for_wordpress_lite() {
		$plugin = new Respira_Lite_Core();
		$plugin->run();
	}
}

run_respira_for_wordpress_lite();

/**
 * Add Settings and Upgrade links to plugins page.
 *
 * @since 1.0.0
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
if ( ! function_exists( 'respira_lite_settings_link' ) ) {
	function respira_lite_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=respira-lite' ),
			esc_html__( 'Settings', 'respira-for-wordpress-lite' )
		);
		array_unshift( $links, $settings_link );

		$upgrade_link = sprintf(
			'<a href="%s" target="_blank" style="color: #2271b1; font-weight: bold;">%s</a>',
			'https://respira.press?utm_source=lite&utm_medium=plugins_page&utm_campaign=upgrade',
			esc_html__( 'Upgrade to Full Version', 'respira-for-wordpress-lite' )
		);
		$links[] = $upgrade_link;

		return $links;
	}
}
add_filter( 'plugin_action_links_' . RESPIRA_LITE_PLUGIN_BASENAME, 'respira_lite_settings_link' );
