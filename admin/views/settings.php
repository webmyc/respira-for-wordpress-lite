<?php
/**
 * Settings view for Respira for WordPress Lite.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/admin/views
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get current settings.
$security_validation = get_option( 'respira_lite_security_validation', true );
$upgrade_url         = Respira_Lite_Usage_Limiter::get_upgrade_url( 'settings', 'upgrade_cta' );

// Include header.
require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/header.php';
?>

<div class="wrap respira-lite-settings">
	<h1><?php esc_html_e( 'Settings', 'respira-for-wordpress-lite' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Configure Respira for WordPress Lite settings.', 'respira-for-wordpress-lite' ); ?>
	</p>

	<!-- Settings Form -->
	<form id="respira-lite-settings-form">
		<div class="respira-lite-card">
			<h2><?php esc_html_e( 'Security Settings', 'respira-for-wordpress-lite' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Content Validation', 'respira-for-wordpress-lite' ); ?>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox"
									   name="security_validation"
									   id="security_validation"
									   value="1"
									   <?php checked( $security_validation, true ); ?>>
								<?php esc_html_e( 'Enable security validation for content updates', 'respira-for-wordpress-lite' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Validates content updates to block malicious code, script injections, and suspicious patterns.', 'respira-for-wordpress-lite' ); ?>
								<strong><?php esc_html_e( 'Recommended: Keep this enabled.', 'respira-for-wordpress-lite' ); ?></strong>
							</p>
						</fieldset>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Save Settings', 'respira-for-wordpress-lite' ); ?>
				</button>
			</p>
		</div>
	</form>

	<!-- Full Version Features Notice -->
	<div class="respira-lite-card respira-lite-pro-settings-card">
		<h2><?php esc_html_e( 'Full Version Settings', 'respira-for-wordpress-lite' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Unlock advanced settings with the full version of Respira:', 'respira-for-wordpress-lite' ); ?>
		</p>

		<div class="respira-lite-pro-settings-list">
			<div class="respira-lite-pro-setting-item">
				<span class="dashicons dashicons-lock"></span>
				<div>
					<strong><?php esc_html_e( 'Page Builder Support', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Enable support for Elementor, Bricks, Oxygen, and other popular page builders.', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-setting-item">
				<span class="dashicons dashicons-lock"></span>
				<div>
					<strong><?php esc_html_e( 'Duplicate Before Edit', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Automatically create backups before making changes to ensure you can always revert.', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-setting-item">
				<span class="dashicons dashicons-lock"></span>
				<div>
					<strong><?php esc_html_e( 'Custom AI Prompts', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Define custom prompts and templates for your AI assistant.', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-setting-item">
				<span class="dashicons dashicons-lock"></span>
				<div>
					<strong><?php esc_html_e( 'Team Collaboration', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Manage multiple API keys for different team members with granular permissions.', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-setting-item">
				<span class="dashicons dashicons-lock"></span>
				<div>
					<strong><?php esc_html_e( 'Advanced Logging', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Extended audit log retention (90 days) with detailed activity reports and export capabilities.', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-setting-item">
				<span class="dashicons dashicons-lock"></span>
				<div>
					<strong><?php esc_html_e( 'SEO & Accessibility Analysis', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Automatic SEO checks and accessibility validation for all content updates.', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-setting-item">
				<span class="dashicons dashicons-lock"></span>
				<div>
					<strong><?php esc_html_e( 'Performance Monitoring', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Track content update performance and optimize for faster page loads.', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-setting-item">
				<span class="dashicons dashicons-lock"></span>
				<div>
					<strong><?php esc_html_e( 'Webhook Integration', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Send notifications to external services when content is updated via the API.', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>
		</div>

		<div class="respira-lite-upgrade-cta">
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-hero respira-lite-upgrade-button" target="_blank">
				<?php esc_html_e( 'Upgrade to Full Version', 'respira-for-wordpress-lite' ); ?>
			</a>
		</div>
	</div>

	<!-- Current Configuration -->
	<div class="respira-lite-card">
		<h2><?php esc_html_e( 'Current Configuration', 'respira-for-wordpress-lite' ); ?></h2>

		<table class="widefat striped">
			<tbody>
				<tr>
					<td><strong><?php esc_html_e( 'Version:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td><?php esc_html_e( 'Lite', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Monthly Edit Limit:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td><?php echo esc_html( RESPIRA_LITE_MONTHLY_LIMIT ); ?> <?php esc_html_e( 'edits', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Audit Log Retention:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td><?php echo esc_html( RESPIRA_LITE_AUDIT_RETENTION_DAYS ); ?> <?php esc_html_e( 'days', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Supported Builders:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td><?php esc_html_e( 'Gutenberg only', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Security Validation:', 'respira-for-wordpress-lite' ); ?></strong></td>
					<td>
						<?php if ( $security_validation ) : ?>
							<span class="respira-lite-status-active"><?php esc_html_e( 'Enabled', 'respira-for-wordpress-lite' ); ?></span>
						<?php else : ?>
							<span class="respira-lite-status-inactive"><?php esc_html_e( 'Disabled', 'respira-for-wordpress-lite' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<?php
// Include footer.
require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/footer.php';
?>
