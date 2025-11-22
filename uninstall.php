<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

/**
 * Delete plugin options.
 */
$options_to_delete = array(
	'respira_lite_version',
	'respira_lite_activated_at',
	'respira_lite_security_validation',
);

foreach ( $options_to_delete as $option ) {
	delete_option( $option );
}

/**
 * Delete usage limiter options (obfuscated).
 */
// We need to recreate the obfuscated option names to delete them.
$salt = defined( 'AUTH_SALT' ) && AUTH_SALT ? AUTH_SALT : 'respira_lite_default_salt';

// Edit count option.
$count_hash = substr( md5( $salt . 'edit_usage_v1' ), 0, 8 );
delete_option( 'respira_sys_' . $count_hash );

// Reset timestamp option.
$reset_hash = substr( md5( $salt . 'reset_time_v1' ), 0, 8 );
delete_option( 'respira_cache_' . $reset_hash );

/**
 * Delete transients.
 */
delete_transient( 'respira_lite_site_info_cache' );

/**
 * Drop plugin tables.
 *
 * Only delete tables if the user explicitly chooses to delete all data.
 * WordPress doesn't provide a built-in way to ask this during uninstall,
 * so we'll always clean up to ensure a fresh start if reinstalled.
 */

// Drop API keys table.
$table_name = $wpdb->prefix . 'respira_lite_api_keys';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Drop audit log table.
$table_name = $wpdb->prefix . 'respira_lite_audit_log';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

/**
 * Clear any scheduled cron jobs.
 */
wp_clear_scheduled_hook( 'respira_lite_cleanup_audit_logs' );
