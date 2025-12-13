<?php
/**
 * The core plugin class.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/includes
 * @since      1.0.0
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since 1.0.0
 */
class Respira_Lite_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Respira_Lite_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version     = RESPIRA_LITE_VERSION;
		$this->plugin_name = 'respira-for-wordpress-lite';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_api_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-loader.php';

		/**
		 * The class responsible for defining internationalization functionality.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-i18n.php';

		/**
		 * The class responsible for authentication.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-auth.php';

		/**
		 * The class responsible for REST API endpoints.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-api.php';

		/**
		 * The class responsible for security validation.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-security.php';

		/**
		 * The class responsible for site context extraction.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-context.php';

		/**
		 * The class responsible for content filtering and script preservation.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-content-filter.php';

		/**
		 * The class responsible for usage limiting.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-usage-limiter.php';

		/**
		 * The class responsible for audit logging.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-audit.php';

		/**
		 * The class responsible for feature detection (inspired by wp-ai-client patterns).
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'includes/class-respira-lite-feature-detector.php';

		/**
		 * The class responsible for defining all actions in the admin area.
		 */
		require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/class-respira-lite-admin.php';

		$this->loader = new Respira_Lite_Loader();

		// Initialize content filter for script preservation.
		Respira_Lite_Content_Filter::init();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new Respira_Lite_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Respira_Lite_Admin( $this->get_plugin_name(), $this->get_version() );

		// Register AJAX handlers early on admin_init.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_ajax_handlers' );

		// Enqueue styles and scripts.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add admin menu.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add upgrade notice.
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_upgrade_notice' );
	}

	/**
	 * Register all of the hooks related to the REST API functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_api_hooks() {
		$plugin_api = new Respira_Lite_API();

		$this->loader->add_action( 'rest_api_init', $plugin_api, 'register_routes' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it.
	 *
	 * @since  1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks.
	 *
	 * @since  1.0.0
	 * @return Respira_Lite_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Clean up old audit log entries.
	 *
	 * This is called by the daily cron job to remove audit log entries
	 * older than the retention period defined by RESPIRA_LITE_AUDIT_RETENTION_DAYS.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function cleanup_old_logs(): void {
		global $wpdb;

		$table      = $wpdb->prefix . 'respira_lite_audit_log';
		$days       = RESPIRA_LITE_AUDIT_RETENTION_DAYS;
		$date_limit = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE created_at < %s",
				$date_limit
			)
		);
	}
}
