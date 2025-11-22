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

// Include header.
require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/header.php';
?>

<div class="wrap respira-lite-api-keys">
	<h1><?php esc_html_e( 'API Keys', 'respira-for-wordpress-lite' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Manage API keys for connecting AI coding assistants to your WordPress site.', 'respira-for-wordpress-lite' ); ?>
	</p>

	<!-- Get API Key Notice -->
	<div class="respira-lite-card respira-lite-get-key-card">
		<div class="respira-lite-get-key-content">
			<div class="respira-lite-get-key-header">
				<span class="dashicons dashicons-admin-network"></span>
				<h2><?php esc_html_e( 'Get Your API Key', 'respira-for-wordpress-lite' ); ?></h2>
			</div>
			<p class="respira-lite-get-key-description">
				<?php esc_html_e( 'To use Respira for WordPress Lite, you need to get an API key from your Respira account.', 'respira-for-wordpress-lite' ); ?>
			</p>

			<div class="respira-lite-get-key-steps">
				<ol>
					<li>
						<strong><?php esc_html_e( 'Sign in to your Respira account', 'respira-for-wordpress-lite' ); ?></strong>
						<p><?php esc_html_e( 'If you don\'t have an account yet, you can create one for free.', 'respira-for-wordpress-lite' ); ?></p>
					</li>
					<li>
						<strong><?php esc_html_e( 'Navigate to API Keys', 'respira-for-wordpress-lite' ); ?></strong>
						<p><?php esc_html_e( 'Go to your account settings and find the API Keys section.', 'respira-for-wordpress-lite' ); ?></p>
					</li>
					<li>
						<strong><?php esc_html_e( 'Generate a new key', 'respira-for-wordpress-lite' ); ?></strong>
						<p><?php esc_html_e( 'Create a new API key for this WordPress site.', 'respira-for-wordpress-lite' ); ?></p>
					</li>
					<li>
						<strong><?php esc_html_e( 'Copy and use', 'respira-for-wordpress-lite' ); ?></strong>
						<p><?php esc_html_e( 'Copy the key and use it in your AI assistant configuration below.', 'respira-for-wordpress-lite' ); ?></p>
					</li>
				</ol>
			</div>

			<div class="respira-lite-get-key-cta">
				<a href="https://respira.press/login" class="button button-primary button-hero" target="_blank" rel="noopener noreferrer">
					<span class="dashicons dashicons-external"></span>
					<?php esc_html_e( 'Get API Key from Respira.press', 'respira-for-wordpress-lite' ); ?>
				</a>
				<p class="respira-lite-get-key-help">
					<?php
					printf(
						/* translators: %s: Support URL */
						esc_html__( 'Need help? %s', 'respira-for-wordpress-lite' ),
						'<a href="https://respira.press/support" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Contact Support', 'respira-for-wordpress-lite' ) . '</a>'
					);
					?>
				</p>
			</div>
		</div>
	</div>

	<!-- Security Warning -->
	<div class="notice notice-warning inline">
		<p>
			<strong><?php esc_html_e( 'Security Notice:', 'respira-for-wordpress-lite' ); ?></strong>
			<?php esc_html_e( 'Keep your API keys secure. Anyone with your API key can edit your WordPress content.', 'respira-for-wordpress-lite' ); ?>
		</p>
	</div>

	<!-- Existing Keys -->
	<div class="respira-lite-card">
		<h2><?php esc_html_e( 'Your API Keys', 'respira-for-wordpress-lite' ); ?></h2>

		<?php if ( empty( $api_keys ) ) : ?>
			<p class="description">
				<?php esc_html_e( 'No API keys found. Get your API key from Respira.press to get started.', 'respira-for-wordpress-lite' ); ?>
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
		<h2><?php esc_html_e( 'Using Your API Key & MCP Server', 'respira-for-wordpress-lite' ); ?></h2>

		<h3><?php esc_html_e( 'For MCP (Model Context Protocol)', 'respira-for-wordpress-lite' ); ?></h3>
		<p><?php esc_html_e( 'Use Respira as an MCP server to give your AI assistant direct access to your WordPress content. Add this to your MCP configuration:', 'respira-for-wordpress-lite' ); ?></p>
		<pre class="respira-lite-code-block"><code>{
  "mcpServers": {
    "respira": {
      "command": "node",
      "args": ["path/to/respira-mcp-server"],
      "env": {
        "RESPIRA_API_KEY": "YOUR_API_KEY_HERE",
        "RESPIRA_ENDPOINT": "<?php echo esc_url( rest_url( RESPIRA_LITE_REST_NAMESPACE ) ); ?>"
      }
    }
  }
}</code></pre>

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

<?php
// Include footer.
require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/footer.php';
?>
