<?php
/**
 * Gutenberg Intelligence Loader.
 *
 * Loads Gutenberg Intelligence addon if available.
 *
 * @package    Respira_For_WordPress
 * @subpackage Respira_For_WordPress/includes/page-builders/gutenberg-intelligence
 * @since      1.3.0
 */

/**
 * Gutenberg Intelligence loader class.
 *
 * @since 1.3.0
 */
class Respira_Gutenberg_Intelligence_Loader {

	/**
	 * Check if Gutenberg Intelligence is available.
	 *
	 * @since 1.3.0
	 * @return bool True if Gutenberg Intelligence is available.
	 */
	public static function is_available() {
		$loader_file = RESPIRA_PLUGIN_DIR . 'includes/gutenberg-intelligence/class-gutenberg-intelligence-loader.php';
		return file_exists( $loader_file );
	}

	/**
	 * Load Gutenberg Intelligence.
	 *
	 * @since 1.3.0
	 * @return void
	 */
	public static function load() {
		$intelligence_dir = RESPIRA_PLUGIN_DIR . 'includes/gutenberg-intelligence/';

		// Load core Gutenberg Intelligence classes.
		$classes = array(
			'class-gutenberg-block-registry.php',
			'class-gutenberg-block-schema.php',
			'class-gutenberg-validator.php',
		);

		foreach ( $classes as $class_file ) {
			$file_path = $intelligence_dir . $class_file;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}

		// Load data files.
		$data_files = array(
			'gutenberg-patterns.php',
		);

		foreach ( $data_files as $data_file ) {
			$file_path = $intelligence_dir . $data_file;
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}
	}
}

