<?php
/**
 * API Keys view for Respira for WordPress Lite.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/admin/views
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get existing API keys.
$api_keys = Respira_Lite_Auth::list_api_keys( get_current_user_id() );
?>

<div class="wrap respira-lite-api-keys">
	<h1><?php esc_html_e( 'API Keys', 'respira-for-wordpress-lite' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Manage API keys for connecting AI coding assistants to your WordPress site.', 'respira-for-wordpress-lite' ); ?>
	</p>

	<!-- Security Warning -->
	<div class="notice notice-warning inline">
		<p>
			<strong><?php esc_html_e( 'Security Notice:', 'respira-for-wordpress-lite' ); ?></strong>
			<?php esc_html_e( 'Keep your API keys secure. Anyone with your API key can edit your WordPress content.', 'respira-for-wordpress-lite' ); ?>
		</p>
	</div>

	<!-- Generate New Key Form -->
	<div class="respira-lite-card">
		<h2><?php esc_html_e( 'Generate New API Key', 'respira-for-wordpress-lite' ); ?></h2>

		<form id="respira-lite-generate-key-form">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="key_name">
							<?php esc_html_e( 'Key Name', 'respira-for-wordpress-lite' ); ?>
						</label>
					</th>
					<td>
						<input type="text"
							   id="key_name"
							   name="key_name"
							   class="regular-text"
							   placeholder="<?php esc_attr_e( 'e.g., Cursor IDE, Claude Code', 'respira-for-wordpress-lite' ); ?>"
							   value="<?php esc_attr_e( 'Default Key', 'respira-for-wordpress-lite' ); ?>">
						<p class="description">
							<?php esc_html_e( 'Give this key a descriptive name to help you remember where it is used.', 'respira-for-wordpress-lite' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Generate API Key', 'respira-for-wordpress-lite' ); ?>
				</button>
			</p>
		</form>

		<!-- Generated Key Display (hidden by default) -->
		<div id="respira-lite-generated-key" style="display: none;">
			<div class="notice notice-success inline">
				<p><strong><?php esc_html_e( 'API Key Generated Successfully!', 'respira-for-wordpress-lite' ); ?></strong></p>
				<p><?php esc_html_e( 'Copy this key now. For security reasons, it will only be shown once.', 'respira-for-wordpress-lite' ); ?></p>
			</div>

			<div class="respira-lite-key-display">
				<input type="text"
					   id="respira-lite-key-value"
					   class="large-text code"
					   readonly>
				<button type="button" class="button button-secondary" id="respira-lite-copy-key">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Copy', 'respira-for-wordpress-lite' ); ?>
				</button>
			</div>

			<div class="respira-lite-key-instructions">
				<h3><?php esc_html_e( 'How to Use This Key', 'respira-for-wordpress-lite' ); ?></h3>
				<ol>
					<li><?php esc_html_e( 'Copy the API key above', 'respira-for-wordpress-lite' ); ?></li>
					<li><?php esc_html_e( 'Add it to your AI coding assistant configuration', 'respira-for-wordpress-lite' ); ?></li>
					<li>
						<?php
						printf(
							/* translators: %s: REST API URL */
							esc_html__( 'Use this REST API endpoint: %s', 'respira-for-wordpress-lite' ),
							'<code>' . esc_html( rest_url( RESPIRA_LITE_REST_NAMESPACE ) ) . '</code>'
						);
						?>
					</li>
				</ol>
			</div>
		</div>
	</div>

	<!-- Existing Keys -->
	<div class="respira-lite-card">
		<h2><?php esc_html_e( 'Your API Keys', 'respira-for-wordpress-lite' ); ?></h2>

		<?php if ( empty( $api_keys ) ) : ?>
			<p class="description">
				<?php esc_html_e( 'No API keys found. Generate one above to get started.', 'respira-for-wordpress-lite' ); ?>
			</p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="column-name">
							<?php esc_html_e( 'Key Name', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-created">
							<?php esc_html_e( 'Created', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-last-used">
							<?php esc_html_e( 'Last Used', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-status">
							<?php esc_html_e( 'Status', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-actions">
							<?php esc_html_e( 'Actions', 'respira-for-wordpress-lite' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $api_keys as $key ) : ?>
						<tr data-key-id="<?php echo esc_attr( $key->id ); ?>">
							<td class="column-name">
								<strong><?php echo esc_html( $key->name ); ?></strong>
							</td>
							<td class="column-created">
								<?php
								echo esc_html(
									wp_date(
										get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
										strtotime( $key->created_at )
									)
								);
								?>
							</td>
							<td class="column-last-used">
								<?php
								if ( $key->last_used ) {
									echo esc_html(
										wp_date(
											get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
											strtotime( $key->last_used )
										)
									);
								} else {
									esc_html_e( 'Never', 'respira-for-wordpress-lite' );
								}
								?>
							</td>
							<td class="column-status">
								<?php if ( $key->is_active ) : ?>
									<span class="respira-lite-status-active">
										<?php esc_html_e( 'Active', 'respira-for-wordpress-lite' ); ?>
									</span>
								<?php else : ?>
									<span class="respira-lite-status-inactive">
										<?php esc_html_e( 'Inactive', 'respira-for-wordpress-lite' ); ?>
									</span>
								<?php endif; ?>
							</td>
							<td class="column-actions">
								<button type="button"
										class="button button-small button-secondary respira-lite-revoke-key"
										data-key-id="<?php echo esc_attr( $key->id ); ?>">
									<?php esc_html_e( 'Revoke', 'respira-for-wordpress-lite' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<!-- Usage Instructions -->
	<div class="respira-lite-card">
		<h2><?php esc_html_e( 'Using Your API Key', 'respira-for-wordpress-lite' ); ?></h2>

		<h3><?php esc_html_e( 'For Cursor IDE', 'respira-for-wordpress-lite' ); ?></h3>
		<p><?php esc_html_e( 'Add this configuration to your Cursor settings:', 'respira-for-wordpress-lite' ); ?></p>
		<pre class="respira-lite-code-block"><code>{
  "respira": {
    "apiKey": "YOUR_API_KEY_HERE",
    "endpoint": "<?php echo esc_url( rest_url( RESPIRA_LITE_REST_NAMESPACE ) ); ?>"
  }
}</code></pre>

		<h3><?php esc_html_e( 'For Claude Code', 'respira-for-wordpress-lite' ); ?></h3>
		<p><?php esc_html_e( 'Add this to your .claude/config.json:', 'respira-for-wordpress-lite' ); ?></p>
		<pre class="respira-lite-code-block"><code>{
  "tools": {
    "respira": {
      "apiKey": "YOUR_API_KEY_HERE",
      "endpoint": "<?php echo esc_url( rest_url( RESPIRA_LITE_REST_NAMESPACE ) ); ?>"
    }
  }
}</code></pre>

		<h3><?php esc_html_e( 'REST API Endpoints', 'respira-for-wordpress-lite' ); ?></h3>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Endpoint', 'respira-for-wordpress-lite' ); ?></th>
					<th><?php esc_html_e( 'Method', 'respira-for-wordpress-lite' ); ?></th>
					<th><?php esc_html_e( 'Description', 'respira-for-wordpress-lite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>/context</code></td>
					<td>GET</td>
					<td><?php esc_html_e( 'Get site context and schema', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><code>/pages</code></td>
					<td>GET</td>
					<td><?php esc_html_e( 'List all pages', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><code>/pages/{id}</code></td>
					<td>GET</td>
					<td><?php esc_html_e( 'Get page content', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><code>/pages/{id}</code></td>
					<td>PUT</td>
					<td><?php esc_html_e( 'Update page content', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><code>/posts</code></td>
					<td>GET</td>
					<td><?php esc_html_e( 'List all posts', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><code>/posts/{id}</code></td>
					<td>GET</td>
					<td><?php esc_html_e( 'Get post content', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
				<tr>
					<td><code>/posts/{id}</code></td>
					<td>PUT</td>
					<td><?php esc_html_e( 'Update post content', 'respira-for-wordpress-lite' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
