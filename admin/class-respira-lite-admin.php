<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/admin
 * @since      1.0.0
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for admin area.
 *
 * @since 1.0.0
 */
class Respira_Lite_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		// Only enqueue on plugin pages.
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'respira-lite' ) === false ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			RESPIRA_LITE_PLUGIN_URL . 'admin/css/respira-lite-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		// Only enqueue on plugin pages.
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'respira-lite' ) === false ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			RESPIRA_LITE_PLUGIN_URL . 'admin/js/respira-lite-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		// Localize script for AJAX.
		wp_localize_script(
			$this->plugin_name,
			'respiraLiteAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'respira_lite_admin_nonce' ),
				'strings' => array(
					'confirmRevoke' => __( 'Are you sure you want to revoke this API key? This action cannot be undone.', 'respira-for-wordpress-lite' ),
					'copied'        => __( 'Copied!', 'respira-for-wordpress-lite' ),
					'copyFailed'    => __( 'Failed to copy', 'respira-for-wordpress-lite' ),
				),
			)
		);
	}

	/**
	 * Register AJAX handlers.
	 *
	 * @since 1.0.0
	 */
	public function register_ajax_handlers() {
		add_action( 'wp_ajax_respira_lite_generate_api_key', array( $this, 'ajax_generate_api_key' ) );
		add_action( 'wp_ajax_respira_lite_revoke_api_key', array( $this, 'ajax_revoke_api_key' ) );
		add_action( 'wp_ajax_respira_lite_save_settings', array( $this, 'ajax_save_settings' ) );
	}

	/**
	 * Add plugin admin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_admin_menu() {
		// Add main menu page.
		add_menu_page(
			__( 'Respira Lite', 'respira-for-wordpress-lite' ),
			__( 'Respira Lite', 'respira-for-wordpress-lite' ),
			'manage_options',
			'respira-lite',
			array( $this, 'display_dashboard_page' ),
			'dashicons-admin-tools',
			30
		);

		// Add Dashboard submenu (default).
		add_submenu_page(
			'respira-lite',
			__( 'Dashboard', 'respira-for-wordpress-lite' ),
			__( 'Dashboard', 'respira-for-wordpress-lite' ),
			'manage_options',
			'respira-lite',
			array( $this, 'display_dashboard_page' )
		);

		// Add API Keys submenu.
		add_submenu_page(
			'respira-lite',
			__( 'API Keys', 'respira-for-wordpress-lite' ),
			__( 'API Keys', 'respira-for-wordpress-lite' ),
			'manage_options',
			'respira-lite-api-keys',
			array( $this, 'display_api_keys_page' )
		);

		// Add Settings submenu.
		add_submenu_page(
			'respira-lite',
			__( 'Settings', 'respira-for-wordpress-lite' ),
			__( 'Settings', 'respira-for-wordpress-lite' ),
			'manage_options',
			'respira-lite-settings',
			array( $this, 'display_settings_page' )
		);

		// Add Audit Log submenu.
		add_submenu_page(
			'respira-lite',
			__( 'Audit Log', 'respira-for-wordpress-lite' ),
			__( 'Audit Log', 'respira-for-wordpress-lite' ),
			'manage_options',
			'respira-lite-audit-log',
			array( $this, 'display_audit_log_page' )
		);
	}

	/**
	 * Display the dashboard page.
	 *
	 * @since 1.0.0
	 */
	public function display_dashboard_page() {
		require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Display the API keys page.
	 *
	 * @since 1.0.0
	 */
	public function display_api_keys_page() {
		require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/api-keys.php';
	}

	/**
	 * Display the settings page.
	 *
	 * @since 1.0.0
	 */
	public function display_settings_page() {
		require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Display the audit log page.
	 *
	 * @since 1.0.0
	 */
	public function display_audit_log_page() {
		require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/audit-log.php';
	}

	/**
	 * Display upgrade notice.
	 *
	 * @since 1.0.0
	 */
	public function display_upgrade_notice() {
		// Only show on plugin pages.
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'respira-lite' ) === false ) {
			return;
		}

		// Check if notice was dismissed.
		$dismissed = get_user_meta( get_current_user_id(), 'respira_lite_upgrade_notice_dismissed', true );
		if ( $dismissed ) {
			return;
		}

		$usage       = Respira_Lite_Usage_Limiter::get_usage();
		$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'admin_notice', 'upgrade_cta' );

		?>
		<div class="notice notice-info is-dismissible" id="respira-lite-upgrade-notice">
			<p>
				<strong><?php esc_html_e( 'Respira Lite:', 'respira-for-wordpress-lite' ); ?></strong>
				<?php
				printf(
					/* translators: 1: Current count, 2: Monthly limit */
					esc_html__( 'You have used %1$d of %2$d monthly edits.', 'respira-for-wordpress-lite' ),
					esc_html( $usage['count'] ),
					esc_html( $usage['limit'] )
				);
				?>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank">
					<?php esc_html_e( 'Upgrade to Pro for unlimited edits, all page builders, and advanced features.', 'respira-for-wordpress-lite' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX handler for generating API key.
	 *
	 * @since 1.0.0
	 */
	public function ajax_generate_api_key() {
		// Verify nonce.
		check_ajax_referer( 'respira_lite_admin_nonce', 'nonce' );

		// Verify permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to generate API keys.', 'respira-for-wordpress-lite' ),
				)
			);
		}

		$key_name = isset( $_POST['key_name'] ) ? sanitize_text_field( wp_unslash( $_POST['key_name'] ) ) : __( 'Default Key', 'respira-for-wordpress-lite' );

		$api_key = Respira_Lite_Auth::generate_api_key( get_current_user_id(), $key_name );

		if ( is_wp_error( $api_key ) ) {
			wp_send_json_error(
				array(
					'message' => $api_key->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'API key generated successfully.', 'respira-for-wordpress-lite' ),
				'api_key' => $api_key,
			)
		);
	}

	/**
	 * AJAX handler for revoking API key.
	 *
	 * @since 1.0.0
	 */
	public function ajax_revoke_api_key() {
		// Verify nonce.
		check_ajax_referer( 'respira_lite_admin_nonce', 'nonce' );

		// Verify permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to revoke API keys.', 'respira-for-wordpress-lite' ),
				)
			);
		}

		$key_id = isset( $_POST['key_id'] ) ? intval( $_POST['key_id'] ) : 0;

		if ( ! $key_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid key ID.', 'respira-for-wordpress-lite' ),
				)
			);
		}

		$result = Respira_Lite_Auth::revoke_api_key( $key_id, get_current_user_id() );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'API key revoked successfully.', 'respira-for-wordpress-lite' ),
			)
		);
	}

	/**
	 * AJAX handler for saving settings.
	 *
	 * @since 1.0.0
	 */
	public function ajax_save_settings() {
		// Verify nonce.
		check_ajax_referer( 'respira_lite_admin_nonce', 'nonce' );

		// Verify permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to save settings.', 'respira-for-wordpress-lite' ),
				)
			);
		}

		$security_validation = isset( $_POST['security_validation'] ) ? (bool) $_POST['security_validation'] : false;

		update_option( 'respira_lite_security_validation', $security_validation );

		wp_send_json_success(
			array(
				'message' => __( 'Settings saved successfully.', 'respira-for-wordpress-lite' ),
			)
		);
	}
}
