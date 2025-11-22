<?php
/**
 * Authentication and authorization functionality for Lite version.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.0
 */

/**
 * Authentication and authorization functionality.
 *
 * Handles API key generation, validation, and rate limiting for Lite version.
 * No license checks required for Lite version.
 *
 * @since 1.0.0
 */
class Respira_Lite_Auth {

	/**
	 * Generate a new API key.
	 *
	 * @since 1.0.0
	 * @param int    $user_id  The user ID who owns this key.
	 * @param string $name     Optional. A friendly name for the key.
	 * @param array  $permissions Optional. Array of permissions for this key.
	 * @return string|WP_Error The generated API key or WP_Error on failure.
	 */
	public static function generate_api_key( $user_id, $name = null, $permissions = array() ) {
		global $wpdb;

		// Verify user has required capability.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! user_can( $user, 'manage_options' ) ) {
			return new WP_Error(
				'respira_lite_insufficient_permissions',
				__( 'User does not have permission to generate API keys.', 'respira-for-wordpress-lite' )
			);
		}

		// Check existing key count - Lite version allows max 3 keys.
		$table_name     = $wpdb->prefix . 'respira_lite_api_keys';
		$existing_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND is_active = 1",
				$user_id
			)
		);

		if ( $existing_count >= RESPIRA_LITE_MAX_API_KEYS ) {
			return new WP_Error(
				'respira_lite_max_keys_reached',
				sprintf(
					/* translators: %d: Maximum number of API keys allowed */
					__( 'Maximum API key limit reached (%d keys). Delete an existing key to create a new one.', 'respira-for-wordpress-lite' ),
					RESPIRA_LITE_MAX_API_KEYS
				),
				array( 'status' => 403 )
			);
		}

		// Generate UUID v4 format key.
		$api_key = 'respira_lite_' . self::generate_uuid();

		// Hash the key for storage.
		$hashed_key = wp_hash_password( $api_key );

		// Default permissions for Lite version.
		if ( empty( $permissions ) ) {
			$permissions = array(
				'read_pages',
				'write_pages',
				'read_posts',
				'write_posts',
				'read_context',
				'upload_media',
			);
		}

		// Insert into database.
		$result = $wpdb->insert(
			$table_name,
			array(
				'api_key'    => $hashed_key,
				'user_id'    => $user_id,
				'name'       => $name ? $name : __( 'Default Key', 'respira-for-wordpress-lite' ),
				'permissions' => wp_json_encode( $permissions ),
				'created_at' => current_time( 'mysql' ),
				'is_active'  => 1,
			),
			array( '%s', '%d', '%s', '%s', '%s', '%d' )
		);

		if ( false === $result ) {
			return new WP_Error(
				'respira_lite_key_generation_failed',
				__( 'Failed to generate API key. Database error.', 'respira-for-wordpress-lite' )
			);
		}

		return $api_key;
	}

	/**
	 * Validate an API key.
	 *
	 * @since 1.0.0
	 * @param string $api_key The API key to validate.
	 * @return object|false The key record if valid, false otherwise.
	 */
	public static function validate_api_key( $api_key ) {
		global $wpdb;

		// Basic format check.
		if ( 0 !== strpos( $api_key, 'respira_lite_' ) ) {
			return false;
		}

		$table_name = $wpdb->prefix . 'respira_lite_api_keys';

		// Get all active keys.
		$keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE is_active = %d",
				1
			)
		);

		if ( ! $keys ) {
			return false;
		}

		// Check each key with wp_check_password.
		foreach ( $keys as $key_record ) {
			if ( wp_check_password( $api_key, $key_record->api_key ) ) {
				// Update last used timestamp.
				$wpdb->update(
					$table_name,
					array( 'last_used' => current_time( 'mysql' ) ),
					array( 'id' => $key_record->id ),
					array( '%s' ),
					array( '%d' )
				);

				return $key_record;
			}
		}

		return false;
	}

	/**
	 * Revoke an API key.
	 *
	 * @since 1.0.0
	 * @param int $key_id The key ID to revoke.
	 * @param int $user_id The user ID (for verification).
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function revoke_api_key( $key_id, $user_id ) {
		global $wpdb;

		// Verify user has required capability.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! user_can( $user, 'manage_options' ) ) {
			return new WP_Error(
				'respira_lite_insufficient_permissions',
				__( 'User does not have permission to revoke API keys.', 'respira-for-wordpress-lite' )
			);
		}

		$table_name = $wpdb->prefix . 'respira_lite_api_keys';

		// Verify the key belongs to this user or user is admin.
		$key = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d",
				$key_id
			)
		);

		if ( ! $key ) {
			return new WP_Error(
				'respira_lite_key_not_found',
				__( 'API key not found.', 'respira-for-wordpress-lite' )
			);
		}

		if ( $key->user_id !== $user_id && ! current_user_can( 'administrator' ) ) {
			return new WP_Error(
				'respira_lite_insufficient_permissions',
				__( 'You do not have permission to revoke this API key.', 'respira-for-wordpress-lite' )
			);
		}

		// Delete the key.
		$result = $wpdb->delete(
			$table_name,
			array( 'id' => $key_id ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new WP_Error(
				'respira_lite_key_revocation_failed',
				__( 'Failed to revoke API key. Database error.', 'respira-for-wordpress-lite' )
			);
		}

		return true;
	}

	/**
	 * List all API keys for a user.
	 *
	 * @since 1.0.0
	 * @param int $user_id The user ID.
	 * @return array Array of API key records (without the actual keys).
	 */
	public static function list_api_keys( $user_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'respira_lite_api_keys';

		$keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_id, name, last_used, created_at, is_active FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
				$user_id
			)
		);

		return $keys ? $keys : array();
	}

	/**
	 * Generate a UUID v4.
	 *
	 * @since 1.0.0
	 * @return string UUID v4 string.
	 */
	private static function generate_uuid() {
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0x0fff ) | 0x4000,
			wp_rand( 0, 0x3fff ) | 0x8000,
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff )
		);
	}
}
