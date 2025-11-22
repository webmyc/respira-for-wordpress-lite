<?php
/**
 * Admin footer partial for Respira for WordPress Lite.
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/admin/views
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="respira-lite-footer">
	<div class="respira-lite-footer-content">
		<div class="respira-lite-footer-links">
			<a href="https://respira.press" target="_blank" rel="noopener noreferrer">
				<span class="dashicons dashicons-admin-home"></span>
				<?php esc_html_e( 'Respira.press', 'respira-for-wordpress-lite' ); ?>
			</a>
			<span class="respira-lite-footer-separator">|</span>
			<a href="https://respira.press/support" target="_blank" rel="noopener noreferrer">
				<span class="dashicons dashicons-sos"></span>
				<?php esc_html_e( 'Support', 'respira-for-wordpress-lite' ); ?>
			</a>
			<span class="respira-lite-footer-separator">|</span>
			<a href="https://respira.press/affiliate" target="_blank" rel="noopener noreferrer">
				<span class="dashicons dashicons-groups"></span>
				<?php esc_html_e( 'Become an Affiliate', 'respira-for-wordpress-lite' ); ?>
			</a>
		</div>
		<div class="respira-lite-footer-copyright">
			<?php
			printf(
				/* translators: %s: Current year */
				esc_html__( '© %s Respira. All rights reserved.', 'respira-for-wordpress-lite' ),
				esc_html( gmdate( 'Y' ) )
			);
			?>
		</div>
	</div>
</div>
