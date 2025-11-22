<?php
/**
 * Admin header partial for Respira for WordPress Lite.
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

<div class="respira-lite-header">
	<div class="respira-lite-header-content">
		<div class="respira-lite-header-logo">
			<img src="<?php echo esc_url( RESPIRA_LITE_PLUGIN_URL . 'admin/assets/images/respira-logo.png' ); ?>" alt="<?php esc_attr_e( 'Respira Logo', 'respira-for-wordpress-lite' ); ?>" class="respira-lite-logo">
			<span class="respira-lite-header-title"><?php esc_html_e( 'Respira for WordPress Lite', 'respira-for-wordpress-lite' ); ?></span>
		</div>
	</div>
</div>
