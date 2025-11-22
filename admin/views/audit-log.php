<?php
/**
 * Audit Log view for Respira for WordPress Lite.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/admin/views
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get filter parameters.
$current_page   = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$filter_type    = isset( $_GET['filter_type'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_type'] ) ) : '';
$filter_action  = isset( $_GET['filter_action'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_action'] ) ) : '';
$per_page       = 50;

// Build query args.
$query_args = array(
	'limit'  => $per_page,
	'offset' => ( $current_page - 1 ) * $per_page,
);

if ( ! empty( $filter_type ) ) {
	$query_args['object_type'] = $filter_type;
}

if ( ! empty( $filter_action ) ) {
	$query_args['action'] = $filter_action;
}

// Get audit logs.
$logs        = Respira_Lite_Audit::get_logs( $query_args );
$upgrade_url = Respira_Lite_Usage_Limiter::get_upgrade_url( 'audit_log', 'upgrade_cta' );

// Get unique object types and actions for filters.
global $wpdb;
$table_name     = $wpdb->prefix . 'respira_lite_audit_log';
$object_types   = $wpdb->get_col( "SELECT DISTINCT object_type FROM {$table_name} ORDER BY object_type" );
$action_types   = $wpdb->get_col( "SELECT DISTINCT action FROM {$table_name} ORDER BY action" );

// Include header.
require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/header.php';
?>

<div class="wrap respira-lite-audit-log">
	<h1><?php esc_html_e( 'Audit Log', 'respira-for-wordpress-lite' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'View all API activity and content changes made through Respira.', 'respira-for-wordpress-lite' ); ?>
	</p>

	<!-- Retention Notice -->
	<div class="notice notice-info inline">
		<p>
			<strong><?php esc_html_e( 'Lite Version:', 'respira-for-wordpress-lite' ); ?></strong>
			<?php
			printf(
				/* translators: %d: Retention days */
				esc_html__( 'Audit logs are retained for %d days. Upgrade to the full version for 90-day retention and advanced filtering.', 'respira-for-wordpress-lite' ),
				RESPIRA_LITE_AUDIT_RETENTION_DAYS
			);
			?>
			<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank">
				<?php esc_html_e( 'Learn more', 'respira-for-wordpress-lite' ); ?>
			</a>
		</p>
	</div>

	<!-- Filters -->
	<div class="respira-lite-card respira-lite-filters">
		<form method="get" action="">
			<input type="hidden" name="page" value="respira-lite-audit-log">

			<div class="respira-lite-filter-group">
				<label for="filter_type">
					<?php esc_html_e( 'Object Type:', 'respira-for-wordpress-lite' ); ?>
				</label>
				<select name="filter_type" id="filter_type">
					<option value=""><?php esc_html_e( 'All Types', 'respira-for-wordpress-lite' ); ?></option>
					<?php foreach ( $object_types as $type ) : ?>
						<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $filter_type, $type ); ?>>
							<?php echo esc_html( ucfirst( $type ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<label for="filter_action">
					<?php esc_html_e( 'Action:', 'respira-for-wordpress-lite' ); ?>
				</label>
				<select name="filter_action" id="filter_action">
					<option value=""><?php esc_html_e( 'All Actions', 'respira-for-wordpress-lite' ); ?></option>
					<?php foreach ( $action_types as $action ) : ?>
						<option value="<?php echo esc_attr( $action ); ?>" <?php selected( $filter_action, $action ); ?>>
							<?php echo esc_html( ucwords( str_replace( '_', ' ', $action ) ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<button type="submit" class="button button-secondary">
					<?php esc_html_e( 'Filter', 'respira-for-wordpress-lite' ); ?>
				</button>

				<?php if ( ! empty( $filter_type ) || ! empty( $filter_action ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=respira-lite-audit-log' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Clear Filters', 'respira-for-wordpress-lite' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</form>
	</div>

	<!-- Audit Log Table -->
	<div class="respira-lite-card">
		<?php if ( empty( $logs ) ) : ?>
			<p class="description">
				<?php esc_html_e( 'No audit log entries found.', 'respira-for-wordpress-lite' ); ?>
			</p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="column-date" style="width: 180px;">
							<?php esc_html_e( 'Date/Time', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-action" style="width: 150px;">
							<?php esc_html_e( 'Action', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-type" style="width: 100px;">
							<?php esc_html_e( 'Object Type', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-object" style="width: 200px;">
							<?php esc_html_e( 'Object', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-user" style="width: 150px;">
							<?php esc_html_e( 'User', 'respira-for-wordpress-lite' ); ?>
						</th>
						<th scope="col" class="column-metadata">
							<?php esc_html_e( 'Details', 'respira-for-wordpress-lite' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $logs as $log ) : ?>
						<?php
						$user_data = get_userdata( $log->user_id );
						$metadata  = json_decode( $log->metadata, true );

						// Get object title.
						$object_title = '';
						if ( 'page' === $log->object_type || 'post' === $log->object_type ) {
							$post = get_post( $log->object_id );
							if ( $post ) {
								$object_title = $post->post_title;
							}
						}
						?>
						<tr>
							<td class="column-date">
								<?php
								echo esc_html(
									wp_date(
										get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
										strtotime( $log->created_at )
									)
								);
								?>
							</td>
							<td class="column-action">
								<span class="respira-lite-action-badge respira-lite-action-<?php echo esc_attr( str_replace( '_', '-', $log->action ) ); ?>">
									<?php echo esc_html( ucwords( str_replace( '_', ' ', $log->action ) ) ); ?>
								</span>
							</td>
							<td class="column-type">
								<?php echo esc_html( ucfirst( $log->object_type ) ); ?>
							</td>
							<td class="column-object">
								<?php if ( ! empty( $object_title ) ) : ?>
									<strong><?php echo esc_html( $object_title ); ?></strong>
									<br>
									<span class="description">ID: <?php echo esc_html( $log->object_id ); ?></span>
								<?php else : ?>
									<span class="description">ID: <?php echo esc_html( $log->object_id ); ?></span>
								<?php endif; ?>
							</td>
							<td class="column-user">
								<?php if ( $user_data ) : ?>
									<?php echo esc_html( $user_data->display_name ); ?>
									<br>
									<span class="description"><?php echo esc_html( $user_data->user_login ); ?></span>
								<?php else : ?>
									<span class="description"><?php esc_html_e( 'Unknown', 'respira-for-wordpress-lite' ); ?></span>
								<?php endif; ?>
							</td>
							<td class="column-metadata">
								<?php if ( ! empty( $metadata ) ) : ?>
									<details>
										<summary><?php esc_html_e( 'View Details', 'respira-for-wordpress-lite' ); ?></summary>
										<pre class="respira-lite-metadata"><?php echo esc_html( wp_json_encode( $metadata, JSON_PRETTY_PRINT ) ); ?></pre>
									</details>
								<?php else : ?>
									<span class="description"><?php esc_html_e( 'No additional details', 'respira-for-wordpress-lite' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php
			// Get total count for pagination.
			$total_query = "SELECT COUNT(*) FROM {$table_name} WHERE 1=1";
			$where_parts = array();

			if ( ! empty( $filter_type ) ) {
				$where_parts[] = $wpdb->prepare( 'object_type = %s', $filter_type );
			}

			if ( ! empty( $filter_action ) ) {
				$where_parts[] = $wpdb->prepare( 'action = %s', $filter_action );
			}

			if ( ! empty( $where_parts ) ) {
				$total_query .= ' AND ' . implode( ' AND ', $where_parts );
			}

			$total_items = $wpdb->get_var( $total_query );
			$total_pages = ceil( $total_items / $per_page );

			if ( $total_pages > 1 ) :
				?>
				<div class="tablenav bottom">
					<div class="tablenav-pages">
						<span class="displaying-num">
							<?php
							printf(
								/* translators: %s: Total items */
								esc_html( _n( '%s item', '%s items', $total_items, 'respira-for-wordpress-lite' ) ),
								number_format_i18n( $total_items )
							);
							?>
						</span>
						<?php
						$page_links = paginate_links(
							array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'prev_text' => __( '&laquo;', 'respira-for-wordpress-lite' ),
								'next_text' => __( '&raquo;', 'respira-for-wordpress-lite' ),
								'total'     => $total_pages,
								'current'   => $current_page,
							)
						);

						if ( $page_links ) {
							echo '<span class="pagination-links">' . $page_links . '</span>';
						}
						?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<!-- Full Version Features -->
	<div class="respira-lite-card respira-lite-pro-features-card">
		<h2><?php esc_html_e( 'Full Version Audit Log Features', 'respira-for-wordpress-lite' ); ?></h2>

		<div class="respira-lite-pro-features-grid">
			<div class="respira-lite-pro-feature">
				<span class="dashicons dashicons-calendar-alt"></span>
				<div>
					<strong><?php esc_html_e( '90-Day Retention', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Keep audit logs for 90 days instead of just 3 days', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-feature">
				<span class="dashicons dashicons-download"></span>
				<div>
					<strong><?php esc_html_e( 'Export to CSV', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Export audit logs for compliance and reporting', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-feature">
				<span class="dashicons dashicons-search"></span>
				<div>
					<strong><?php esc_html_e( 'Advanced Filtering', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Filter by date range, user, IP address, and more', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-feature">
				<span class="dashicons dashicons-backup"></span>
				<div>
					<strong><?php esc_html_e( 'Content Snapshots', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'View before/after content for every change', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-feature">
				<span class="dashicons dashicons-email"></span>
				<div>
					<strong><?php esc_html_e( 'Email Notifications', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Get notified when specific actions occur', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>

			<div class="respira-lite-pro-feature">
				<span class="dashicons dashicons-chart-line"></span>
				<div>
					<strong><?php esc_html_e( 'Activity Reports', 'respira-for-wordpress-lite' ); ?></strong>
					<p><?php esc_html_e( 'Generate reports on API usage and content changes', 'respira-for-wordpress-lite' ); ?></p>
				</div>
			</div>
		</div>

		<div class="respira-lite-upgrade-cta">
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-hero respira-lite-upgrade-button" target="_blank">
				<?php esc_html_e( 'Upgrade to Full Version', 'respira-for-wordpress-lite' ); ?>
			</a>
		</div>
	</div>
</div>

<?php
// Include footer.
require_once RESPIRA_LITE_PLUGIN_DIR . 'admin/views/footer.php';
?>
