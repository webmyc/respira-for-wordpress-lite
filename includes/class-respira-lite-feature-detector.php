<?php
/**
 * Feature Detection for Respira Lite.
 *
 * Inspired by wp-ai-client patterns: Check capabilities before execution
 * to ensure features are supported before attempting to use them.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.1
 */

/**
 * Feature Detection class.
 *
 * Provides methods to check if features are supported before execution,
 * following wp-ai-client patterns for provider-agnostic feature detection.
 *
 * @since 1.0.1
 */
class Respira_Lite_Feature_Detector {

	/**
	 * Check if page builder is supported.
	 *
	 * @since 1.0.1
	 * @param string $builder Builder name (e.g., 'gutenberg', 'elementor').
	 * @return bool True if builder is supported, false otherwise.
	 */
	public static function is_builder_supported( $builder ) {
		// Lite version only supports Gutenberg.
		if ( 'gutenberg' === $builder ) {
			return true;
		}

		// All other builders require full version.
		return false;
	}

	/**
	 * Check if content update operation is supported.
	 *
	 * @since 1.0.1
	 * @param string $operation Operation type (e.g., 'update_page', 'update_post').
	 * @return bool|WP_Error True if supported, WP_Error if not supported.
	 */
	public static function is_operation_supported( $operation ) {
		$supported_operations = array(
			'update_page',
			'update_post',
			'get_page',
			'get_post',
			'list_pages',
			'list_posts',
			'upload_media',
		);

		if ( in_array( $operation, $supported_operations, true ) ) {
			return true;
		}

		return new WP_Error(
			'respira_operation_not_supported',
			/* translators: %s: Operation name */
			sprintf( __( 'Operation "%s" is not supported in Respira Lite. Upgrade to the full version for advanced features.', 'respira-for-wordpress-lite' ), $operation ),
			array(
				'operation' => $operation,
				'upgrade_url' => Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'feature_detection' ),
			)
		);
	}

	/**
	 * Check if analysis feature is supported.
	 *
	 * @since 1.0.1
	 * @param string $analysis_type Analysis type (e.g., 'seo', 'performance', 'aeo').
	 * @return WP_Error Always returns error in Lite version (analysis is pro feature).
	 */
	public static function is_analysis_supported( $analysis_type ) {
		return new WP_Error(
			'respira_analysis_not_available',
			/* translators: %s: Analysis type */
			sprintf( __( 'Analysis features are not available in Respira Lite. Upgrade to the full version for SEO, performance, and AEO analysis.', 'respira-for-wordpress-lite' ), $analysis_type ),
			array(
				'analysis_type' => $analysis_type,
				'upgrade_url' => Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'analysis_feature' ),
			)
		);
	}

	/**
	 * Check if usage limit allows operation.
	 *
	 * @since 1.0.1
	 * @return bool|WP_Error True if usage available, WP_Error if limit reached.
	 */
	public static function is_usage_available() {
		$usage = Respira_Lite_Usage_Limiter::get_usage();

		if ( $usage['is_exhausted'] ) {
			return new WP_Error(
				'respira_usage_limit_reached',
				__( 'Monthly edit limit reached. Upgrade to the full version for unlimited edits.', 'respira-for-wordpress-lite' ),
				array(
					'usage' => $usage,
					'upgrade_url' => Respira_Lite_Usage_Limiter::get_upgrade_url( 'api', 'usage_limit' ),
				)
			);
		}

		return true;
	}

	/**
	 * Get list of supported features.
	 *
	 * @since 1.0.1
	 * @return array Array of supported feature names.
	 */
	public static function get_supported_features() {
		return array(
			'gutenberg_builder',
			'page_management',
			'post_management',
			'media_upload',
			'basic_audit_log',
		);
	}

	/**
	 * Get list of unsupported features (pro features).
	 *
	 * @since 1.0.1
	 * @return array Array of unsupported feature names with descriptions.
	 */
	public static function get_unsupported_features() {
		return array(
			'elementor_builder' => __( 'Elementor page builder support', 'respira-for-wordpress-lite' ),
			'divi_builder' => __( 'Divi page builder support', 'respira-for-wordpress-lite' ),
			'bricks_builder' => __( 'Bricks page builder support', 'respira-for-wordpress-lite' ),
			'oxygen_builder' => __( 'Oxygen page builder support', 'respira-for-wordpress-lite' ),
			'seo_analysis' => __( 'SEO analysis and recommendations', 'respira-for-wordpress-lite' ),
			'performance_analysis' => __( 'Performance analysis and optimization', 'respira-for-wordpress-lite' ),
			'aeo_analysis' => __( 'AI Engine Optimization analysis', 'respira-for-wordpress-lite' ),
			'duplicate_before_edit' => __( 'Automatic duplicate creation before edits', 'respira-for-wordpress-lite' ),
			'extended_audit_log' => __( 'Extended audit log retention (90 days)', 'respira-for-wordpress-lite' ),
		);
	}
}

