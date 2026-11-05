/**
 * JS-Steuerung für das barrierefreie DSGVO Cookie-Consent-Modal.
 * 
 * Sichert Focus-Trapping (Tastaturfokus bleibt innerhalb des Modals) 
 * und ermöglicht Screenreader-optimierte Dialogsteuerungen.
 */
document.addEventListener('DOMContentLoaded', () => {
	'use strict';

	const overlay = document.getElementById('me-cookie-overlay');
	if (!overlay) return;

	const btnAccept = document.getElementById('me-cookie-btn-accept');
	const btnSelected = document.getElementById('me-cookie-btn-selected');
	const checkboxAnalytics = document.getElementById('me-cookie-analytics');

	// ==========================================
	// 1. FOCUS TRAPPING LOGIK (WCAG 2.1 AA)
	// ==========================================
	function trapFocus(e) {
		if (e.key !== 'Tab') return;

		const focusableElements = overlay.querySelectorAll('button, input[type="checkbox"]');
		const firstElement = focusableElements[0];
		const lastElement = focusableElements[focusableElements.length - 1];

		if (e.shiftKey) { // Shift + Tab
			if (document.activeElement === firstElement) {
				lastElement.focus();
				e.preventDefault();
			}
		} else { // Tab
			if (document.activeElement === lastElement) {
				firstElement.focus();
				e.preventDefault();
			}
		}
	}

	// Modal-Fokus beim Laden aktivieren
	setTimeout(() => {
		const firstCheckbox = document.getElementById('me-cookie-analytics');
		if (firstCheckbox) firstCheckbox.focus();
	}, 150);

	document.addEventListener('keydown', trapFocus);

	// Escape-Taste schließt oder lehnt standardmäßig ab
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && overlay.style.display !== 'none') {
			setConsentCookie('denied');
			closeModal();
		}
	});

	// ==========================================
	// 2. EINWILLIGUNGS-LOGIK (COOKIES SETZEN)
	// ==========================================
	if (btnAccept) {
		btnAccept.addEventListener('click', () => {
			setConsentCookie('accepted');
			closeModal();
		});
	}

	if (btnSelected) {
		btnSelected.addEventListener('click', () => {
			const isAnalyticsAccepted = checkboxAnalytics && checkboxAnalytics.checked;
			const consentValue = isAnalyticsAccepted ? 'accepted_analytics' : 'accepted_necessary';
			setConsentCookie(consentValue);
			closeModal();
		});
	}

	/**
	 * Hilfsfunktion zum Setzen eines 100% DSGVO-konformen Cookies.
	 *
	 * @param {string} value Der Einwilligungswert.
	 */
	function setConsentCookie(value) {
		const date = new Date();
		date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000)); // 1 Jahr gültig
		const expires = "; expires=" + date.toUTCString();
		document.cookie = "me_cookie_consent=" + value + expires + "; path=/; SameSite=Lax; Secure";
	}

	function closeModal() {
		overlay.style.display = 'none';
		document.removeEventListener('keydown', trapFocus);

		// Fokus auf die Hauptseite zurücksetzen
		const skipLink = document.getElementById('me-skip-link');
		if (skipLink) {
			skipLink.focus();
		} else {
			const mainContent = document.querySelector('h1');
			if (mainContent) mainContent.focus();
		}
	}
});
