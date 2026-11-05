/**
 * ME Accessibility - AJAX License Validation Script
 * 
 * Manages modern, non-reloading license activation and deactivation
 * transitions, custom error handling, and visual state modifications.
 */

jQuery(document).ready(function($) {
	// Secure container and data checking
	if (typeof meProLicense === 'undefined') {
		return;
	}

	var $feedback = $('#me-license-ajax-feedback');
	var $activateBtn = $('#me-activate-license-btn');
	var $deactivateBtn = $('#me-deactivate-license-btn');
	var $inputField = $('#me-license-key-field');

	// Helper to show visual messages in SaaS styles
	function showFeedback(type, text) {
		$feedback.removeClass('me-success me-error me-loading')
			.addClass('me-' + type)
			.html('')
			.hide();

		if (type === 'loading') {
			$feedback.append('<div class="me-license-spinner"></div>');
		} else if (type === 'success') {
			$feedback.append('<span class="dashicons dashicons-yes-alt" style="color: #10b981;"></span>');
		} else if (type === 'error') {
			$feedback.append('<span class="dashicons dashicons-dismiss" style="color: #ef4444;"></span>');
		}

		$feedback.append('<span>' + text + '</span>').fadeIn(200);
	}

	// 1. ACTIVATE AJAX
	$activateBtn.on('click', function(e) {
		e.preventDefault();
		var licenseKey = $.trim($inputField.val());

		if (!licenseKey) {
			showFeedback('error', meProLicense.translations.error || 'Bitte geben Sie einen Lizenzschlüssel an.');
			return;
		}

		// UI Lock states
		$activateBtn.prop('disabled', true);
		$inputField.prop('disabled', true);
		showFeedback('loading', meProLicense.translations.activating || 'Aktiviere Lizenz...');

		$.ajax({
			url: meProLicense.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'me_activate_license',
				license_key: licenseKey,
				nonce: meProLicense.licenseNonce
			},
			success: function(response) {
				if (response.success) {
					showFeedback('success', response.data && response.data.message ? response.data.message : (meProLicense.translations.success || 'Erfolgreich abgeschlossen!'));
					
					// Smooth visual transition before reload to maximize dopamine effect
					setTimeout(function() {
						location.reload();
					}, 1000);
				} else {
					var errMsg = response.data && response.data.message ? response.data.message : 'Aktivierung fehlgeschlagen.';
					showFeedback('error', errMsg);
					$activateBtn.prop('disabled', false);
					$inputField.prop('disabled', false);
				}
			},
			error: function() {
				showFeedback('error', meProLicense.translations.error || 'Ein Fehler ist aufgetreten. Bitte erneut versuchen.');
				$activateBtn.prop('disabled', false);
				$inputField.prop('disabled', false);
			}
		});
	});

	// 2. DEACTIVATE AJAX
	$deactivateBtn.on('click', function(e) {
		e.preventDefault();

		if (!confirm('Möchten Sie diese Lizenz wirklich für dieses Projekt deaktivieren?')) {
			return;
		}

		$deactivateBtn.prop('disabled', true);
		showFeedback('loading', meProLicense.translations.deactivating || 'Deaktiviere Lizenz...');

		$.ajax({
			url: meProLicense.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'me_deactivate_license',
				nonce: meProLicense.licenseNonce
			},
			success: function(response) {
				if (response.success) {
					showFeedback('success', response.data && response.data.message ? response.data.message : 'Lizenz erfolgreich deaktiviert.');
					
					setTimeout(function() {
						location.reload();
					}, 1000);
				} else {
					var errMsg = response.data && response.data.message ? response.data.message : 'Deaktivierung fehlgeschlagen.';
					showFeedback('error', errMsg);
					$deactivateBtn.prop('disabled', false);
				}
			},
			error: function() {
				showFeedback('error', meProLicense.translations.error || 'Ein Fehler ist aufgetreten. Bitte erneut versuchen.');
				$deactivateBtn.prop('disabled', false);
			}
		});
	});
});
