<?php
/**
 * Usage limiter for Lite version.
 *
 * Tracks and limits the number of edits per month.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.0
 */

/**
 * Usage limiter class.
 *
 * Implements a monthly edit counter with obfuscated storage.
 *
 * @since 1.0.0
 */
class Respira_Lite_Usage_Limiter {

	/**
	 * Get the obfuscated option name for edit count.
	 *
	 * Uses WordPress AUTH_SALT to create a unique, hard-to-guess option name
	 * that looks like system cache data.
	 *
	 * @since 1.0.0
	 * @return string The obfuscated option name.
	 */
	private static function get_count_option_name() {
		$salt = defined( 'AUTH_SALT' ) && AUTH_SALT ? AUTH_SALT : 'respira_lite_default_salt';
		$hash = substr( md5( $salt . 'edit_usage_v1' ), 0, 8 );
		return 'respira_sys_' . $hash;
	}

	/**
	 * Get the obfuscated option name for last reset timestamp.
	 *
	 * @since 1.0.0
	 * @return string The obfuscated option name.
	 */
	private static function get_reset_option_name() {
		$salt = defined( 'AUTH_SALT' ) && AUTH_SALT ? AUTH_SALT : 'respira_lite_default_salt';
		$hash = substr( md5( $salt . 'reset_time_v1' ), 0, 8 );
		return 'respira_cache_' . $hash;
	}

	/**
	 * Get current usage data.
	 *
	 * @since 1.0.0
	 * @return array Array with 'count', 'limit', 'reset_date', 'remaining'.
	 */
	public static function get_usage() {
		self::maybe_reset_monthly_counter();

		$count        = (int) get_option( self::get_count_option_name(), 0 );
		$limit        = RESPIRA_LITE_MONTHLY_LIMIT;
		$reset_ts     = (int) get_option( self::get_reset_option_name(), time() );
		$reset_date   = gmdate( 'Y-m-d H:i:s', $reset_ts );
		$remaining    = max( 0, $limit - $count );
		$is_exhausted = $count >= $limit;

		return array(
			'count'        => $count,
			'limit'        => $limit,
			'remaining'    => $remaining,
			'reset_date'   => $reset_date,
			'reset_timestamp' => $reset_ts,
			'is_exhausted' => $is_exhausted,
		);
	}

	/**
	 * Check if usage limit has been reached.
	 *
	 * @since 1.0.0
	 * @return bool True if limit reached, false otherwise.
	 */
	public static function is_limit_reached() {
		$usage = self::get_usage();
		return $usage['is_exhausted'];
	}

	/**
	 * Increment the edit counter.
	 *
	 * @since 1.0.0
	 * @return bool True on success, false on failure.
	 */
	public static function increment() {
		self::maybe_reset_monthly_counter();

		$option_name = self::get_count_option_name();
		$count       = (int) get_option( $option_name, 0 );
		$new_count   = $count + 1;

		return update_option( $option_name, $new_count );
	}

	/**
	 * Reset the monthly counter if needed.
	 *
	 * Checks if we've passed into a new month and resets the counter.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function maybe_reset_monthly_counter() {
		$reset_option = self::get_reset_option_name();
		$last_reset   = (int) get_option( $reset_option, 0 );
		$current_time = time();

		// If no last reset, initialize it.
		if ( 0 === $last_reset ) {
			$next_reset = self::calculate_next_reset( $current_time );
			update_option( $reset_option, $next_reset );
			update_option( self::get_count_option_name(), 0 );
			return;
		}

		// Check if we've passed the reset time.
		if ( $current_time >= $last_reset ) {
			// Reset counter.
			update_option( self::get_count_option_name(), 0 );
			// Set next reset time (one month from now).
			$next_reset = self::calculate_next_reset( $current_time );
			update_option( $reset_option, $next_reset );
		}
	}

	/**
	 * Calculate the next reset timestamp (one month from given time).
	 *
	 * @since 1.0.0
	 * @param int $from_time Timestamp to calculate from.
	 * @return int Next reset timestamp.
	 */
	private static function calculate_next_reset( $from_time ) {
		$date = new DateTime( '@' . $from_time );
		$date->modify( '+1 month' );
		return $date->getTimestamp();
	}

	/**
	 * Get upgrade URL with UTM parameters.
	 *
	 * @since 1.0.0
	 * @param string $location Where the upgrade link is shown (dashboard, api, plugins_page, settings).
	 * @param string $action What triggered the upgrade prompt (upgrade_cta, limit_reached, builder_upgrade, feature_upgrade).
	 * @return string The upgrade URL with UTM parameters.
	 */
	public static function get_upgrade_url( $location = 'dashboard', $action = 'upgrade_cta' ) {
		$base_url = 'https://respira.press';
		$params   = array(
			'utm_source'   => 'lite',
			'utm_medium'   => sanitize_key( $location ),
			'utm_campaign' => sanitize_key( $action ),
		);

		return add_query_arg( $params, $base_url );
	}

	/**
	 * Get limit reached error for API responses.
	 *
	 * @since 1.0.0
	 * @return WP_Error Error object with upgrade information.
	 */
	public static function get_limit_reached_error() {
		$usage      = self::get_usage();
		$upgrade_url = self::get_upgrade_url( 'api', 'limit_reached' );

		return new WP_Error(
			'respira_lite_limit_reached',
			sprintf(
				/* translators: 1: Current count, 2: Monthly limit, 3: Reset date */
				__( 'Monthly edit limit reached (%1$d/%2$d). Resets on %3$s. Upgrade for unlimited edits: %4$s', 'respira-for-wordpress-lite' ),
				$usage['count'],
				$usage['limit'],
				$usage['reset_date'],
				$upgrade_url
			),
			array(
				'status'       => 429,
				'usage'        => $usage,
				'upgrade_url'  => $upgrade_url,
			)
		);
	}

	/**
	 * Clean up usage data (for uninstall).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function cleanup() {
		delete_option( self::get_count_option_name() );
		delete_option( self::get_reset_option_name() );
	}
}
