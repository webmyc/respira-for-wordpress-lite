<?php
/**
 * Security validation functionality.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 */

/**
 * Security validation functionality.
 *
 * Validates content for security issues before saving to WordPress.
 *
 * @since 1.0.0
 */
class Respira_Lite_Security {

	/**
	 * Validate content for security issues.
	 *
	 * @since 1.0.0
	 * @param string $content The content to validate.
	 * @param bool   $strict  Whether to use strict validation (default: false).
	 * @return true|WP_Error True if valid, WP_Error if issues found.
	 */
	public static function validate_content( $content, $strict = false ) {
		$issues = array();

		// Check for XSS patterns.
		$xss_check = self::check_xss_patterns( $content );
		if ( ! empty( $xss_check ) ) {
			$issues = array_merge( $issues, $xss_check );
		}

		// Check for SQL injection patterns.
		$sql_check = self::check_sql_injection( $content );
		if ( ! empty( $sql_check ) ) {
			$issues = array_merge( $issues, $sql_check );
		}

		// Check for dangerous functions.
		$dangerous_functions = self::check_dangerous_functions( $content );
		if ( ! empty( $dangerous_functions ) ) {
			$issues = array_merge( $issues, $dangerous_functions );
		}

		// Check for external URLs (suspicious) - only in strict mode.
		if ( $strict ) {
			$external_urls = self::check_suspicious_urls( $content );
			if ( ! empty( $external_urls ) ) {
				$issues = array_merge( $issues, $external_urls );
			}
		}

		if ( ! empty( $issues ) ) {
			return new WP_Error(
				'respira_security_validation_failed',
				__( 'Content failed security validation. To bypass these checks, you can disable security validation in Respira settings or pass skip_security_check=true parameter.', 'respira-for-wordpress-lite' ),
				array(
					'status' => 400,
					'issues' => $issues,
				)
			);
		}

		return true;
	}

	/**
	 * Check for XSS patterns.
	 *
	 * @since  1.0.0
	 * @param  string $content Content to check.
	 * @return array  Issues found.
	 */
	private static function check_xss_patterns( $content ) {
		$issues = array();

		// Dangerous patterns.
		$patterns = array(
			'/<script[^>]*>.*?<\/script>/is'                    => 'Inline script tag detected',
			'/javascript:/i'                                    => 'JavaScript protocol detected',
			'/on\w+\s*=/i'                                      => 'Inline event handler detected (onclick, onload, etc.)',
			'/<iframe[^>]*>/i'                                  => 'iframe tag detected',
			'/<object[^>]*>/i'                                  => 'object tag detected',
			'/<embed[^>]*>/i'                                   => 'embed tag detected',
			'/eval\s*\(/i'                                      => 'eval() function detected',
			'/expression\s*\(/i'                                => 'CSS expression() detected',
			'/vbscript:/i'                                      => 'VBScript protocol detected',
			'/data:text\/html/i'                                => 'Data URI with HTML detected',
		);

		foreach ( $patterns as $pattern => $message ) {
			if ( preg_match( $pattern, $content ) ) {
				$issues[] = $message;
			}
		}

		return $issues;
	}

	/**
	 * Check for SQL injection patterns.
	 *
	 * @since  1.0.0
	 * @param  string $content Content to check.
	 * @return array  Issues found.
	 */
	private static function check_sql_injection( $content ) {
		$issues = array();

		$patterns = array(
			'/\bDROP\s+TABLE\b/i'          => 'DROP TABLE statement detected',
			'/\bDELETE\s+FROM\b/i'         => 'DELETE FROM statement detected',
			'/\bTRUNCATE\s+TABLE\b/i'      => 'TRUNCATE TABLE statement detected',
			'/\bUPDATE\s+.*\s+SET\b/i'     => 'UPDATE statement detected',
			'/\bINSERT\s+INTO\b/i'         => 'INSERT INTO statement detected',
			'/UNION\s+SELECT/i'            => 'UNION SELECT injection pattern detected',
			'/;\s*DROP\s+/i'               => 'SQL injection pattern detected',
		);

		foreach ( $patterns as $pattern => $message ) {
			if ( preg_match( $pattern, $content ) ) {
				$issues[] = $message;
			}
		}

		return $issues;
	}

	/**
	 * Check for dangerous PHP functions.
	 *
	 * @since  1.0.0
	 * @param  string $content Content to check.
	 * @return array  Issues found.
	 */
	private static function check_dangerous_functions( $content ) {
		$issues = array();

		$dangerous = array(
			'exec',
			'shell_exec',
			'system',
			'passthru',
			'proc_open',
			'popen',
			'curl_exec',
			'curl_multi_exec',
			'parse_ini_file',
			'show_source',
			'file_get_contents',
			'file_put_contents',
			'fopen',
			'readfile',
			'unlink',
			'rmdir',
		);

		foreach ( $dangerous as $function ) {
			if ( preg_match( '/\b' . preg_quote( $function, '/' ) . '\s*\(/i', $content ) ) {
				$issues[] = sprintf(
					/* translators: %s: function name */
					__( 'Dangerous function detected: %s()', 'respira-for-wordpress-lite' ),
					$function
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for suspicious external URLs.
	 *
	 * @since  1.0.0
	 * @param  string $content Content to check.
	 * @return array  Issues found.
	 */
	private static function check_suspicious_urls( $content ) {
		$issues = array();

		// Extract URLs.
		preg_match_all( '/(https?:\/\/[^\s<>"\']+)/i', $content, $matches );

		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $url ) {
				$url_host = wp_parse_url( $url, PHP_URL_HOST );

				// Only check for truly suspicious patterns, not all external URLs.
				$suspicious_patterns = array(
					'/\.tk$/i'       => 'Free TLD (.tk) often used for malicious sites',
					'/\.ml$/i'       => 'Free TLD (.ml) often used for malicious sites',
					'/\.ga$/i'       => 'Free TLD (.ga) often used for malicious sites',
					'/\.cf$/i'       => 'Free TLD (.cf) often used for malicious sites',
					'/\.gq$/i'       => 'Free TLD (.gq) often used for malicious sites',
					'/\d+\.\d+\.\d+\.\d+/' => 'Direct IP address URL (potential security risk)',
				);

				foreach ( $suspicious_patterns as $pattern => $reason ) {
					if ( preg_match( $pattern, $url_host ) ) {
						$issues[] = sprintf(
							/* translators: 1: URL, 2: reason */
							__( 'Suspicious URL detected: %1$s (Reason: %2$s)', 'respira-for-wordpress-lite' ),
							esc_url( $url ),
							$reason
						);
						break;
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Sanitize HTML content.
	 *
	 * @since 1.0.0
	 * @param string $content Content to sanitize.
	 * @return string Sanitized content.
	 */
	public static function sanitize_html( $content ) {
		// Allow specific HTML tags.
		$allowed_tags = wp_kses_allowed_html( 'post' );

		// Add additional safe tags for page builders.
		$allowed_tags['iframe'] = array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
			'class'           => true,
			'id'              => true,
		);

		return wp_kses( $content, $allowed_tags );
	}

	/**
	 * Validate WordPress coding standards.
	 *
	 * @since 1.0.0
	 * @param string $code Code to validate.
	 * @return array Validation results.
	 */
	public static function validate_wordpress_standards( $code ) {
		// This is a simplified version.
		// In production, you would integrate with PHP_CodeSniffer.
		$issues = array();

		// Check for proper escaping.
		if ( preg_match( '/echo\s+\$/', $code ) && ! preg_match( '/esc_html|esc_attr|esc_url/', $code ) ) {
			$issues[] = __( 'Direct echo of variable without escaping detected', 'respira-for-wordpress-lite' );
		}

		// Check for SQL queries without prepare.
		if ( preg_match( '/\$wpdb->(query|get_var|get_results)/', $code ) && ! preg_match( '/\$wpdb->prepare/', $code ) ) {
			$issues[] = __( 'Database query without prepare() detected', 'respira-for-wordpress-lite' );
		}

		return array(
			'valid'  => empty( $issues ),
			'issues' => $issues,
		);
	}
}
