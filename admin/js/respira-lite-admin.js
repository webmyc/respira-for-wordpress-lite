/**
 * Respira for WordPress Lite - Admin JavaScript
 *
 * @package    Respira_For_WordPress_Lite
 * @subpackage Respira_For_WordPress_Lite/admin/js
 * @since      1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Main admin object
	 */
	const RespiraLiteAdmin = {

		/**
		 * Initialize the admin scripts
		 */
		init: function() {
			this.bindEvents();
			this.initTooltips();
		},

		/**
		 * Bind event listeners
		 */
		bindEvents: function() {
			// API Keys page events
			$('#respira-lite-generate-key-form').on('submit', this.handleGenerateAPIKey.bind(this));
			$('#respira-lite-copy-key').on('click', this.handleCopyKey.bind(this));
			$('.respira-lite-revoke-key').on('click', this.handleRevokeKey.bind(this));

			// Settings page events
			$('#respira-lite-settings-form').on('submit', this.handleSaveSettings.bind(this));

			// Dismiss upgrade notice
			$(document).on('click', '#respira-lite-upgrade-notice .notice-dismiss', this.handleDismissNotice.bind(this));
		},

		/**
		 * Initialize tooltips (if needed)
		 */
		initTooltips: function() {
			// Add any tooltip initialization here if needed in the future
		},

		/**
		 * Handle API key generation form submission
		 *
		 * @param {Event} e - Submit event
		 */
		handleGenerateAPIKey: function(e) {
			e.preventDefault();

			const $form = $(e.currentTarget);
			const $button = $form.find('button[type="submit"]');
			const buttonText = $button.text();
			const keyName = $('#key_name').val().trim();

			// Validate key name
			if (!keyName) {
				this.showMessage('Please enter a name for your API key.', 'error');
				$('#key_name').focus();
				return;
			}

			// Add loading state
			this.setLoadingState($button, true, 'Generating...');

			// Send AJAX request
			$.ajax({
				url: respiraLiteAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'respira_lite_generate_api_key',
					nonce: respiraLiteAdmin.nonce,
					key_name: keyName
				},
				success: (response) => {
					if (response.success) {
						// Display the generated key
						$('#respira-lite-key-value').val(response.data.api_key);
						$('#respira-lite-generated-key').slideDown(300);

						// Reset form
						$form[0].reset();
						$('#key_name').val('Default Key');

						// Show success message
						this.showMessage('API key generated successfully! Copy it now - it will only be shown once.', 'success');

						// Reload page after 3 seconds to show new key in table
						setTimeout(() => {
							window.location.reload();
						}, 3000);
					} else {
						this.showMessage(response.data.message || 'Failed to generate API key.', 'error');
					}
				},
				error: (xhr, status, error) => {
					this.showMessage('An error occurred while generating the API key. Please try again.', 'error');
					this.logError('Generate API Key Error:', error, xhr);
				},
				complete: () => {
					this.setLoadingState($button, false, buttonText);
				}
			});
		},

		/**
		 * Handle copy to clipboard
		 *
		 * @param {Event} e - Click event
		 */
		handleCopyKey: function(e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const $input = $('#respira-lite-key-value');
			const originalHtml = $button.html();

			// Select and copy
			$input.select();

			try {
				// Try modern clipboard API first
				if (navigator.clipboard && window.isSecureContext) {
					navigator.clipboard.writeText($input.val())
						.then(() => {
							this.showCopySuccess($button, originalHtml);
						})
						.catch(() => {
							// Fallback to execCommand
							this.fallbackCopy($input, $button, originalHtml);
						});
				} else {
					// Fallback to execCommand
					this.fallbackCopy($input, $button, originalHtml);
				}
			} catch (err) {
				this.showMessage(respiraLiteAdmin.strings.copyFailed || 'Failed to copy', 'error');
				this.logError('Copy Error:', err);
			}
		},

		/**
		 * Fallback copy method using execCommand
		 *
		 * @param {jQuery} $input - Input element
		 * @param {jQuery} $button - Button element
		 * @param {string} originalHtml - Original button HTML
		 */
		fallbackCopy: function($input, $button, originalHtml) {
			const success = document.execCommand('copy');

			if (success) {
				this.showCopySuccess($button, originalHtml);
			} else {
				this.showMessage(respiraLiteAdmin.strings.copyFailed || 'Failed to copy', 'error');
			}
		},

		/**
		 * Show copy success feedback
		 *
		 * @param {jQuery} $button - Button element
		 * @param {string} originalHtml - Original button HTML
		 */
		showCopySuccess: function($button, originalHtml) {
			const copiedText = respiraLiteAdmin.strings.copied || 'Copied!';
			$button.html('<span class="dashicons dashicons-yes"></span> ' + copiedText);
			$button.addClass('button-primary');

			// Reset button after 2 seconds
			setTimeout(() => {
				$button.html(originalHtml);
				$button.removeClass('button-primary');
			}, 2000);
		},

		/**
		 * Handle API key revocation
		 *
		 * @param {Event} e - Click event
		 */
		handleRevokeKey: function(e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const keyId = $button.data('key-id');
			const $row = $button.closest('tr');

			// Confirm action
			const confirmMessage = respiraLiteAdmin.strings.confirmRevoke ||
				'Are you sure you want to revoke this API key? This action cannot be undone.';

			if (!confirm(confirmMessage)) {
				return;
			}

			// Add loading state
			$button.prop('disabled', true).text('Revoking...');

			// Send AJAX request
			$.ajax({
				url: respiraLiteAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'respira_lite_revoke_api_key',
					nonce: respiraLiteAdmin.nonce,
					key_id: keyId
				},
				success: (response) => {
					if (response.success) {
						// Remove row with animation
						$row.fadeOut(400, function() {
							$(this).remove();

							// Check if table is now empty
							const $tbody = $('.wp-list-table tbody');
							if ($tbody.find('tr').length === 0) {
								// Reload to show "no keys" message
								window.location.reload();
							}
						});

						this.showMessage('API key revoked successfully.', 'success');
					} else {
						this.showMessage(response.data.message || 'Failed to revoke API key.', 'error');
						$button.prop('disabled', false).text('Revoke');
					}
				},
				error: (xhr, status, error) => {
					this.showMessage('An error occurred while revoking the API key. Please try again.', 'error');
					this.logError('Revoke API Key Error:', error, xhr);
					$button.prop('disabled', false).text('Revoke');
				}
			});
		},

		/**
		 * Handle settings form submission
		 *
		 * @param {Event} e - Submit event
		 */
		handleSaveSettings: function(e) {
			e.preventDefault();

			const $form = $(e.currentTarget);
			const $button = $form.find('button[type="submit"]');
			const buttonText = $button.text();
			const securityValidation = $('#security_validation').is(':checked') ? 1 : 0;

			// Add loading state
			this.setLoadingState($button, true, 'Saving...');

			// Send AJAX request
			$.ajax({
				url: respiraLiteAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'respira_lite_save_settings',
					nonce: respiraLiteAdmin.nonce,
					security_validation: securityValidation
				},
				success: (response) => {
					if (response.success) {
						// Show success notice at the top
						this.showAdminNotice(
							response.data.message || 'Settings saved successfully.',
							'success'
						);

						// Auto-scroll to top to see the message
						$('html, body').animate({ scrollTop: 0 }, 300);
					} else {
						this.showMessage(response.data.message || 'Failed to save settings.', 'error');
					}
				},
				error: (xhr, status, error) => {
					this.showMessage('An error occurred while saving settings. Please try again.', 'error');
					this.logError('Save Settings Error:', error, xhr);
				},
				complete: () => {
					this.setLoadingState($button, false, buttonText);
				}
			});
		},

		/**
		 * Handle upgrade notice dismissal
		 *
		 * @param {Event} e - Click event
		 */
		handleDismissNotice: function(e) {
			// Send AJAX to save dismissal preference
			$.ajax({
				url: respiraLiteAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'respira_lite_dismiss_upgrade_notice',
					nonce: respiraLiteAdmin.nonce
				}
			});
		},

		/**
		 * Set loading state on button
		 *
		 * @param {jQuery} $button - Button element
		 * @param {boolean} loading - Loading state
		 * @param {string} loadingText - Text to show during loading
		 */
		setLoadingState: function($button, loading, loadingText) {
			if (loading) {
				$button.prop('disabled', true)
					.addClass('respira-lite-loading')
					.text(loadingText || 'Loading...');
			} else {
				$button.prop('disabled', false)
					.removeClass('respira-lite-loading');
			}
		},

		/**
		 * Show admin notice (WordPress style)
		 *
		 * @param {string} message - Message text
		 * @param {string} type - Notice type (success, error, warning, info)
		 */
		showAdminNotice: function(message, type) {
			type = type || 'info';

			const $notice = $('<div>')
				.addClass('notice notice-' + type + ' is-dismissible')
				.html('<p><strong>' + this.escapeHtml(message) + '</strong></p>')
				.hide();

			// Insert after h1
			const $heading = $('.respira-lite-settings h1, .respira-lite-api-keys h1, .respira-lite-dashboard h1, .respira-lite-audit-log h1');

			if ($heading.length) {
				$heading.after($notice);
			} else {
				$('.wrap').prepend($notice);
			}

			// Slide down
			$notice.slideDown(300);

			// Make dismissible
			this.makeNoticeDismissible($notice);

			// Auto-dismiss success messages after 5 seconds
			if (type === 'success') {
				setTimeout(() => {
					$notice.fadeOut(300, function() {
						$(this).remove();
					});
				}, 5000);
			}
		},

		/**
		 * Show inline message
		 *
		 * @param {string} message - Message text
		 * @param {string} type - Message type (success, error, warning)
		 */
		showMessage: function(message, type) {
			type = type || 'info';

			const alertClass = 'respira-lite-alert-' + type;
			const $alert = $('<div>')
				.addClass(alertClass)
				.html('<strong>' + this.escapeHtml(message) + '</strong>')
				.hide();

			// Find the best place to insert the message
			const $form = $('#respira-lite-generate-key-form, #respira-lite-settings-form');

			if ($form.length) {
				$form.before($alert);
			} else {
				$('.respira-lite-card').first().prepend($alert);
			}

			// Slide down
			$alert.slideDown(300);

			// Auto-dismiss after 5 seconds
			setTimeout(() => {
				$alert.slideUp(300, function() {
					$(this).remove();
				});
			}, 5000);
		},

		/**
		 * Make notice dismissible (WordPress compatibility)
		 *
		 * @param {jQuery} $notice - Notice element
		 */
		makeNoticeDismissible: function($notice) {
			const $button = $('<button>')
				.attr('type', 'button')
				.addClass('notice-dismiss')
				.html('<span class="screen-reader-text">Dismiss this notice.</span>');

			$button.on('click', function() {
				$notice.fadeOut(300, function() {
					$(this).remove();
				});
			});

			$notice.append($button);
		},

		/**
		 * Escape HTML to prevent XSS
		 *
		 * @param {string} text - Text to escape
		 * @return {string} Escaped text
		 */
		escapeHtml: function(text) {
			const map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
		},

		/**
		 * Log error to console (only in development)
		 *
		 * @param {string} context - Error context
		 * @param {*} error - Error object or message
		 * @param {*} xhr - XHR object (optional)
		 */
		logError: function(context, error, xhr) {
			// Only log in development (when script debug is enabled)
			if (typeof window.console !== 'undefined' && typeof window.console.error === 'function') {
				// Check if we're in debug mode (you can set a global var in PHP)
				if (window.SCRIPT_DEBUG || window.WP_DEBUG) {
					console.error('[Respira Lite]', context, error);
					if (xhr) {
						console.error('[Respira Lite] XHR:', xhr);
					}
				}
			}
		},

		/**
		 * Show loading overlay on element
		 *
		 * @param {jQuery} $element - Element to show loading on
		 */
		showLoading: function($element) {
			$element.addClass('respira-lite-loading');
		},

		/**
		 * Hide loading overlay on element
		 *
		 * @param {jQuery} $element - Element to hide loading on
		 */
		hideLoading: function($element) {
			$element.removeClass('respira-lite-loading');
		},

		/**
		 * Validate form fields
		 *
		 * @param {jQuery} $form - Form element
		 * @return {boolean} True if valid
		 */
		validateForm: function($form) {
			let isValid = true;
			const $requiredFields = $form.find('[required]');

			$requiredFields.each(function() {
				const $field = $(this);
				const value = $field.val().trim();

				if (!value) {
					isValid = false;
					$field.addClass('error');
					$field.one('input change', function() {
						$(this).removeClass('error');
					});
				}
			});

			return isValid;
		},

		/**
		 * Confirm action with custom dialog
		 *
		 * @param {string} message - Confirmation message
		 * @param {Function} callback - Callback if confirmed
		 */
		confirm: function(message, callback) {
			if (confirm(message)) {
				callback();
			}
		},

		/**
		 * Smooth scroll to element
		 *
		 * @param {jQuery} $element - Element to scroll to
		 * @param {number} offset - Offset from top (default: 100)
		 */
		scrollTo: function($element, offset) {
			offset = offset || 100;

			if ($element.length) {
				$('html, body').animate({
					scrollTop: $element.offset().top - offset
				}, 400);
			}
		},

		/**
		 * Format number with thousands separator
		 *
		 * @param {number} num - Number to format
		 * @return {string} Formatted number
		 */
		formatNumber: function(num) {
			return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		},

		/**
		 * Debounce function
		 *
		 * @param {Function} func - Function to debounce
		 * @param {number} wait - Wait time in ms
		 * @return {Function} Debounced function
		 */
		debounce: function(func, wait) {
			let timeout;
			return function executedFunction(...args) {
				const later = () => {
					clearTimeout(timeout);
					func(...args);
				};
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
			};
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		RespiraLiteAdmin.init();
	});

	/**
	 * Expose to global scope for external access if needed
	 */
	window.RespiraLiteAdmin = RespiraLiteAdmin;

})(jQuery);
