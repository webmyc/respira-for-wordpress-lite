<?php
/**
 * Audit logging functionality for Lite version.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.0
 */

/**
 * Audit logging class.
 *
 * Logs API actions with 3-day retention for Lite version.
 *
 * @since 1.0.0
 */
class Respira_Lite_Audit {

	/**
	 * Log an API action.
	 *
	 * @since 1.0.0
	 * @param string $action    The action performed (e.g., 'page_updated', 'page_created').
	 * @param int    $object_id The object ID (page, post, etc.).
	 * @param string $object_type The object type (page, post, etc.).
	 * @param int    $user_id   The user ID who performed the action.
	 * @param array  $metadata  Optional. Additional metadata.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function log( $action, $object_id, $object_type, $user_id, $metadata = array() ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'respira_lite_audit_log';

		$result = $wpdb->insert(
			$table_name,
			array(
				'action'      => $action,
				'object_id'   => $object_id,
				'object_type' => $object_type,
				'user_id'     => $user_id,
				'metadata'    => wp_json_encode( $metadata ),
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%d', '%s', '%s' )
		);

		if ( false === $result ) {
			return new WP_Error(
				'respira_lite_audit_log_failed',
				__( 'Failed to log action.', 'respira-for-wordpress-lite' )
			);
		}

		// Cleanup old logs after inserting.
		self::cleanup_old_logs();

		return true;
	}

	/**
	 * Get audit logs.
	 *
	 * @since 1.0.0
	 * @param array $args Query arguments (limit, offset, object_type, action, user_id).
	 * @return array Array of log entries.
	 */
	public static function get_logs( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'limit'       => 50,
			'offset'      => 0,
			'object_type' => null,
			'action'      => null,
			'user_id'     => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$table_name = $wpdb->prefix . 'respira_lite_audit_log';
		$where      = array( '1=1' );
		$values     = array();

		if ( ! empty( $args['object_type'] ) ) {
			$where[]  = 'object_type = %s';
			$values[] = $args['object_type'];
		}

		if ( ! empty( $args['action'] ) ) {
			$where[]  = 'action = %s';
			$values[] = $args['action'];
		}

		if ( ! empty( $args['user_id'] ) ) {
			$where[]  = 'user_id = %d';
			$values[] = $args['user_id'];
		}

		$where_clause = implode( ' AND ', $where );

		$query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";

		$values[] = $args['limit'];
		$values[] = $args['offset'];

		$prepared_query = $wpdb->prepare( $query, $values );

		$logs = $wpdb->get_results( $prepared_query );

		return $logs ? $logs : array();
	}

	/**
	 * Cleanup old audit logs (older than retention period).
	 *
	 * Lite version retains logs for 3 days only.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function cleanup_old_logs() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'respira_lite_audit_log';
		$retention_days = RESPIRA_LITE_AUDIT_RETENTION_DAYS;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
				$retention_days
			)
		);
	}

	/**
	 * Clean up all audit logs (for uninstall).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function cleanup_all() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'respira_lite_audit_log';
		$wpdb->query( "TRUNCATE TABLE {$table_name}" );
	}
}
