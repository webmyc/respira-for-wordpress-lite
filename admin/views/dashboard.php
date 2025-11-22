<?php
/**
 * Dashboard view for Respira for WordPress Lite.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/admin/views
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get usage data.
$usage       = Respira_Lite_Usage_Limiter::get_usage();
$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'dashboard', 'upgrade_cta' );

// Calculate usage percentage.
$usage_percentage = $usage['limit'] > 0 ? ( $usage['count'] / $usage['limit'] ) * 100 : 0;

// Determine progress bar color.
$progress_color = 'green';
if ( $usage_percentage >= 90 ) {
	$progress_color = 'red';
} elseif ( $usage_percentage >= 70 ) {
	$progress_color = 'orange';
}

// Format reset date.
$reset_date_formatted = gmdate( 'F j, Y', $usage['reset_timestamp'] );

// Include header.
require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/header.php';
?>

<div class="wrap respira-lite-dashboard">
	<h1><?php esc_html_e( 'Respira for WordPress Lite', 'respira-for-wordpress-lite' ); ?></h1>

	<div class="respira-lite-grid">
		<!-- Usage Stats Card -->
		<div class="respira-lite-card respira-lite-usage-card">
			<h2><?php esc_html_e( 'Monthly Usage', 'respira-for-wordpress-lite' ); ?></h2>

			<div class="respira-lite-usage-stats">
				<div class="respira-lite-usage-number">
					<span class="respira-lite-count"><?php echo esc_html( $usage['count'] ); ?></span>
					<span class="respira-lite-separator">/</span>
					<span class="respira-lite-limit"><?php echo esc_html( $usage['limit'] ); ?></span>
				</div>
				<p class="respira-lite-usage-label">
					<?php esc_html_e( 'Edits Used This Month', 'respira-for-wordpress-lite' ); ?>
				</p>
			</div>

			<div class="respira-lite-progress-bar">
				<div class="respira-lite-progress-fill respira-lite-progress-<?php echo esc_attr( $progress_color ); ?>"
					 style="width: <?php echo esc_attr( min( 100, $usage_percentage ) ); ?>%">
				</div>
			</div>

			<div class="respira-lite-usage-meta">
				<div class="respira-lite-usage-remaining">
					<strong><?php echo esc_html( $usage['remaining'] ); ?></strong>
					<?php esc_html_e( 'edits remaining', 'respira-for-wordpress-lite' ); ?>
				</div>
				<div class="respira-lite-usage-reset">
					<?php
					printf(
						/* translators: %s: Reset date */
						esc_html__( 'Resets on %s', 'respira-for-wordpress-lite' ),
						'<strong>' . esc_html( $reset_date_formatted ) . '</strong>'
					);
					?>
				</div>
			</div>

			<?php if ( $usage['is_exhausted'] ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<strong><?php esc_html_e( 'Monthly limit reached!', 'respira-for-wordpress-lite' ); ?></strong>
						<?php esc_html_e( 'Upgrade to the full version for unlimited edits.', 'respira-for-wordpress-lite' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Quick Start Card -->
		<div class="respira-lite-card respira-lite-quick-start-card">
			<h2><?php esc_html_e( 'Quick Start Guide', 'respira-for-wordpress-lite' ); ?></h2>

			<ol class="respira-lite-steps">
				<li>
					<strong><?php esc_html_e( 'Generate an API Key', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Go to the API Keys page and generate a new key for your AI coding assistant.', 'respira-for-wordpress-lite' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=respira-lite-api-keys' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Generate API Key', 'respira-for-wordpress-lite' ); ?>
					</a>
				</li>
				<li>
					<strong><?php esc_html_e( 'Configure Your AI Assistant', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Add the API key to your Cursor or Claude Code configuration.', 'respira-for-wordpress-lite' ); ?></p>
				</li>
				<li>
					<strong><?php esc_html_e( 'Start Editing', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Use natural language to edit your WordPress pages and posts through Gutenberg.', 'respira-for-wordpress-lite' ); ?></p>
				</li>
			</ol>

			<div class="respira-lite-docs-link">
				<a href="https://respira.press/docs" target="_blank" class="button button-secondary">
					<span class="dashicons dashicons-book"></span>
					<?php esc_html_e( 'View Documentation', 'respira-for-wordpress-lite' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- Upgrade CTA -->
	<div class="respira-lite-card respira-lite-upgrade-card">
		<div class="respira-lite-upgrade-content">
			<div class="respira-lite-upgrade-header">
				<h2><?php esc_html_e( 'Unlock the Full Power of Respira', 'respira-for-wordpress-lite' ); ?></h2>
				<p class="respira-lite-upgrade-subtitle">
					<?php esc_html_e( 'Upgrade to the full version and supercharge your WordPress workflow', 'respira-for-wordpress-lite' ); ?>
				</p>
			</div>

			<div class="respira-lite-upgrade-features">
				<div class="respira-lite-feature-grid">
					<div class="respira-lite-feature">
						<span class="dashicons dashicons-yes-alt"></span>
						<div>
							<strong><?php esc_html_e( 'Unlimited Edits', 'respira-for-wordpress-lite' ); ?></strong>
							<p><?php esc_html_e( 'No monthly limits - edit as much as you need', 'respira-for-wordpress-lite' ); ?></p>
						</div>
					</div>

					<div class="respira-lite-feature">
						<span class="dashicons dashicons-yes-alt"></span>
						<div>
							<strong><?php esc_html_e( 'Duplicate Before Edit', 'respira-for-wordpress-lite' ); ?></strong>
							<p><?php esc_html_e( 'Safely experiment without affecting live content', 'respira-for-wordpress-lite' ); ?></p>
						</div>
					</div>

					<div class="respira-lite-feature">
						<span class="dashicons dashicons-yes-alt"></span>
						<div>
							<strong><?php esc_html_e( 'All Page Builders', 'respira-for-wordpress-lite' ); ?></strong>
							<p><?php esc_html_e( 'Elementor, Bricks, Oxygen, and more (Lite: Gutenberg only)', 'respira-for-wordpress-lite' ); ?></p>
						</div>
					</div>

					<div class="respira-lite-feature">
						<span class="dashicons dashicons-yes-alt"></span>
						<div>
							<strong><?php esc_html_e( 'Advanced Analysis', 'respira-for-wordpress-lite' ); ?></strong>
							<p><?php esc_html_e( 'SEO analysis, accessibility checks, and performance insights', 'respira-for-wordpress-lite' ); ?></p>
						</div>
					</div>

					<div class="respira-lite-feature">
						<span class="dashicons dashicons-yes-alt"></span>
						<div>
							<strong><?php esc_html_e( 'Extended Audit Logs', 'respira-for-wordpress-lite' ); ?></strong>
							<p><?php esc_html_e( '90-day retention vs. 3-day in Lite', 'respira-for-wordpress-lite' ); ?></p>
						</div>
					</div>

					<div class="respira-lite-feature">
						<span class="dashicons dashicons-yes-alt"></span>
						<div>
							<strong><?php esc_html_e( 'Priority Support', 'respira-for-wordpress-lite' ); ?></strong>
							<p><?php esc_html_e( 'Get help from our team when you need it', 'respira-for-wordpress-lite' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="respira-lite-upgrade-cta">
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-hero" target="_blank">
					<?php esc_html_e( 'Upgrade to Full Version Now', 'respira-for-wordpress-lite' ); ?>
				</a>
				<p class="respira-lite-money-back">
					<?php esc_html_e( '30-day money-back guarantee', 'respira-for-wordpress-lite' ); ?>
				</p>
			</div>
		</div>
	</div>

	<!-- System Info Card -->
	<div class="respira-lite-card respira-lite-system-info-card">
		<h2><?php esc_html_e( 'System Information', 'respira-for-wordpress-lite' ); ?></h2>

		<table class="widefat striped">
			<tbody>
				<tr>
					<td><strong><?php esc_html_e( 'Plugin Version:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td><?php echo esc_html( RESPIRA_LITE_VERSION ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'WordPress Version:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'PHP Version:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td><?php echo esc_html( PHP_VERSION ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'REST API Endpoint:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td><code><?php echo esc_html( rest_url( RESPIRA_LITE_REST_NAMESPACE ) ); ?></code></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<?php
// Include footer.
require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/footer.php';
?>
