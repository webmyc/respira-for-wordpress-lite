<?php
/**
 * Site context extraction functionality.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 */

/**
 * Site context extraction functionality.
 *
 * Extracts information about the WordPress site, theme, plugins,
 * and page builders to provide context to the AI.
 *
 * @since 1.0.0
 */
class Respira_Lite_Context {

	/**
	 * Get comprehensive site information.
	 *
	 * @since 1.0.0
	 * @return array Site information array.
	 */
	public static function get_site_info() {
		// Check transient first.
		$cached = get_transient( 'respira_site_context' );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$theme = wp_get_theme();

		$page_builder = self::detect_page_builder();

		$info = array(
			'wordpress_version' => get_bloginfo( 'version' ),
			'php_version'       => phpversion(),
			'mysql_version'     => $wpdb->db_version(),
			'site_url'          => get_site_url(),
			'home_url'          => get_home_url(),
			'site_name'         => get_bloginfo( 'name' ),
			'site_description'  => get_bloginfo( 'description' ),
			'language'          => get_bloginfo( 'language' ),
			'is_multisite'      => is_multisite(),
			'active_theme'      => array(
				'name'        => $theme->get( 'Name' ),
				'version'     => $theme->get( 'Version' ),
				'author'      => $theme->get( 'Author' ),
				'template'    => $theme->get_template(),
				'stylesheet'  => $theme->get_stylesheet(),
				'description' => $theme->get( 'Description' ),
			),
			'active_plugins'    => self::get_active_plugins(),
			'custom_post_types' => self::get_custom_post_types(),
			'taxonomies'        => self::get_taxonomies(),
			'database_tables'   => self::get_database_tables(),
			'page_builder'      => $page_builder,
		);

		// Add Divi-specific context if Divi is detected.
		if ( $page_builder && 'Divi' === $page_builder['name'] ) {
			$info['divi_context'] = self::get_divi_context();
		}

		// Cache for 1 hour.
		set_transient( 'respira_site_context', $info, HOUR_IN_SECONDS );

		return $info;
	}

	/**
	 * Get active plugins information.
	 *
	 * @since  1.0.0
	 * @return array Active plugins array.
	 */
	private static function get_active_plugins() {
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_info   = array();

		foreach ( $active_plugins as $plugin ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$plugins_info[] = array(
				'name'        => $plugin_data['Name'],
				'version'     => $plugin_data['Version'],
				'author'      => $plugin_data['Author'],
				'description' => $plugin_data['Description'],
				'file'        => $plugin,
			);
		}

		return $plugins_info;
	}

	/**
	 * Get custom post types.
	 *
	 * @since  1.0.0
	 * @return array Custom post types array.
	 */
	private static function get_custom_post_types() {
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		$cpts = array();

		foreach ( $post_types as $post_type ) {
			$cpts[] = array(
				'name'        => $post_type->name,
				'label'       => $post_type->label,
				'description' => $post_type->description,
				'supports'    => get_all_post_type_supports( $post_type->name ),
			);
		}

		return $cpts;
	}

	/**
	 * Get taxonomies.
	 *
	 * @since  1.0.0
	 * @return array Taxonomies array.
	 */
	private static function get_taxonomies() {
		$taxonomies = get_taxonomies(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		$taxes = array();

		foreach ( $taxonomies as $taxonomy ) {
			$taxes[] = array(
				'name'          => $taxonomy->name,
				'label'         => $taxonomy->label,
				'description'   => $taxonomy->description,
				'object_types'  => $taxonomy->object_type,
				'hierarchical'  => $taxonomy->hierarchical,
			);
		}

		return $taxes;
	}

	/**
	 * Get database tables.
	 *
	 * @since  1.0.0
	 * @return array Database tables array.
	 */
	private static function get_database_tables() {
		global $wpdb;

		$tables = $wpdb->get_col( 'SHOW TABLES' );

		return array_map(
			function( $table ) use ( $wpdb ) {
				return str_replace( $wpdb->prefix, '', $table );
			},
			$tables
		);
	}

	/**
	 * Detect active page builder.
	 *
	 * @since  1.0.0
	 * @return array|null Page builder info or null.
	 */
	private static function detect_page_builder() {
		$builder = Respira_Builder_Interface::detect_builder();

		if ( $builder ) {
			return array(
				'name'    => $builder->get_name(),
				'version' => $builder->get_version(),
			);
		}

		return null;
	}

	/**
	 * Get theme documentation.
	 *
	 * @since 1.0.0
	 * @return array Theme documentation array.
	 */
	public static function get_theme_docs() {
		$theme = wp_get_theme();

		$docs = array(
			'theme_name'        => $theme->get( 'Name' ),
			'theme_version'     => $theme->get( 'Version' ),
			'theme_uri'         => $theme->get( 'ThemeURI' ),
			'author_uri'        => $theme->get( 'AuthorURI' ),
			'readme'            => self::get_theme_readme(),
			'template_files'    => self::get_theme_template_files(),
			'custom_functions'  => self::get_theme_functions(),
		);

		return $docs;
	}

	/**
	 * Get theme readme content.
	 *
	 * @since  1.0.0
	 * @return string|null Readme content or null.
	 */
	private static function get_theme_readme() {
		$theme_dir = get_stylesheet_directory();
		$readme_files = array( 'README.md', 'readme.txt', 'README.txt', 'readme.md' );

		foreach ( $readme_files as $file ) {
			$path = $theme_dir . '/' . $file;
			if ( file_exists( $path ) ) {
				return file_get_contents( $path );
			}
		}

		return null;
	}

	/**
	 * Get theme template files.
	 *
	 * @since  1.0.0
	 * @return array Template files array.
	 */
	private static function get_theme_template_files() {
		$theme_dir = get_stylesheet_directory();
		$files     = array();

		$templates = array(
			'index.php',
			'header.php',
			'footer.php',
			'sidebar.php',
			'single.php',
			'page.php',
			'archive.php',
			'functions.php',
		);

		foreach ( $templates as $template ) {
			if ( file_exists( $theme_dir . '/' . $template ) ) {
				$files[] = $template;
			}
		}

		return $files;
	}

	/**
	 * Get theme functions (from functions.php).
	 *
	 * @since  1.0.0
	 * @return array Custom functions array.
	 */
	private static function get_theme_functions() {
		$theme_dir     = get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return array();
		}

		$content = file_get_contents( $functions_file );

		// Extract function names using regex.
		preg_match_all( '/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/', $content, $matches );

		return $matches[1] ?? array();
	}

	/**
	 * Get page builder information.
	 *
	 * @since 1.0.0
	 * @return array Page builder information array.
	 */
	public static function get_builder_info() {
		// Check transient first.
		$cached = get_transient( 'respira_builder_info' );
		if ( false !== $cached ) {
			return $cached;
		}

		// Get all available builders.
		$builder_classes = array(
			'Respira_Builder_Gutenberg',
			'Respira_Builder_Divi',
			'Respira_Builder_Elementor',
			'Respira_Builder_WPBakery',
			'Respira_Builder_Oxygen',
			'Respira_Builder_Bricks',
			'Respira_Builder_Beaver',
			'Respira_Builder_Brizy',
			'Respira_Builder_Visual_Composer',
			'Respira_Builder_Thrive',
		);

		$builders_info = array();
		$active_builder = null;

		foreach ( $builder_classes as $builder_class ) {
			if ( ! class_exists( $builder_class ) ) {
				continue;
			}

			$builder = new $builder_class();
			$is_detected = $builder->detect();
			$intelligence_available = $builder->is_intelligence_available();

			$builder_info = array(
				'name'                  => $builder->get_name(),
				'version'              => $builder->get_version(),
				'detected'              => $is_detected,
				'intelligence_available' => $intelligence_available,
				'intelligence_active'   => $intelligence_available && $is_detected,
			);

			// Get module/widget/block count if available.
			if ( $is_detected ) {
				$modules = $builder->get_available_modules();
				$builder_info['module_count'] = count( $modules );
				$builder_info['documentation'] = $builder->get_documentation();

				if ( ! $active_builder ) {
					$active_builder = $builder;
				}
			}

			$builders_info[ $builder->get_name() ] = $builder_info;
		}

		// Get theme information.
		$theme_info = Respira_Theme_Detector::get_theme_info();

		$info = array(
			'builders'      => $builders_info,
			'active_builder' => $active_builder ? array(
				'name'    => $active_builder->get_name(),
				'version' => $active_builder->get_version(),
			) : null,
			'theme'         => $theme_info,
		);

		// Cache for 1 hour.
		set_transient( 'respira_builder_info', $info, HOUR_IN_SECONDS );

		return $info;
	}

	/**
	 * Get Divi-specific context information.
	 *
	 * @since 1.0.0
	 * @return array Divi context array.
	 */
	private static function get_divi_context() {
		$context = array(
			'builder_name'    => 'Divi',
			'builder_version' => defined( 'ET_BUILDER_PLUGIN_VERSION' ) ? ET_BUILDER_PLUGIN_VERSION : 'unknown',
			'is_theme'        => ( 'Divi' === wp_get_theme()->get( 'Name' ) ),
			'is_plugin'       => defined( 'ET_BUILDER_PLUGIN_VERSION' ),
		);

		// Get module information if registry is available.
		if ( class_exists( 'Respira_Divi_Module_Registry' ) ) {
			$all_modules = Respira_Divi_Module_Registry::get_all_modules();
			$context['modules'] = array(
				'total'     => count( $all_modules ),
				'available' => array_column( $all_modules, 'name' ),
			);
		}

		return $context;
	}

	/**
	 * Get Divi module information.
	 *
	 * @since 1.0.0
	 * @param Respira_Builder_Interface $builder Builder instance.
	 * @return array Module information.
	 */
	private static function get_divi_module_info( $builder ) {
		$modules = $builder->get_available_modules();

		return array(
			'count'   => count( $modules ),
			'modules' => array_slice( $modules, 0, 20 ), // Limit to first 20 for performance.
		);
	}

	/**
	 * Get Divi patterns information.
	 *
	 * @since 1.0.0
	 * @return array Patterns information.
	 */
	private static function get_divi_patterns_info() {
		$patterns_file = RESPIRA_LITE_PLUGIN_DIR . 'includes/divi-intelligence/divi-patterns.php';
		if ( file_exists( $patterns_file ) ) {
			require_once $patterns_file;
			if ( function_exists( 'respira_get_divi_patterns' ) ) {
				$patterns = respira_get_divi_patterns();
				return array(
					'count'    => count( $patterns ),
					'patterns' => array_keys( $patterns ),
				);
			}
		}

		return array(
			'count'    => 0,
			'patterns' => array(),
		);
	}
}
