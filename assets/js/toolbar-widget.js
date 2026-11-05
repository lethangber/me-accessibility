/**
 * Frontend Controller für das ME Accessibility Widget.
 * 
 * Programmiert in reinem ES6 JavaScript (Vanilla JS), um maximale Performance zu sichern.
 */
document.addEventListener('DOMContentLoaded', () => {
	'use strict';

	// ==========================================
	// 1. ZUSTANDS- UND DOM-VARIABLEN
	// ==========================================
	const trigger = document.getElementById('me-toolbar-trigger');
	const widget = document.getElementById('me-toolbar-widget');
	const closeBtn = document.getElementById('me-widget-close-btn');
	const resetBtn = document.getElementById('me-reset-all-btn');
	
	const readingLine = document.getElementById('me-reading-line');
	const maskTop = document.getElementById('me-reading-mask-top');
	const maskBottom = document.getElementById('me-reading-mask-bottom');

	// Aktueller Zustand des Widgets
	let fontScale = 1.0;
	let activeSettings = {};

	// ==========================================
	// 0. I18N / TRANSLATIONS APPLIER
	// ==========================================
	function applyTranslations() {
		if (typeof meAccessibilityToolbar === 'undefined' || !meAccessibilityToolbar.translations) {
			return;
		}
		const translations = meAccessibilityToolbar.translations;
		const lang = meAccessibilityToolbar.lang || 'de';
		const dir = meAccessibilityToolbar.dir || 'ltr';

		// Dynamically inject "Language" translation to avoid modifying all 20 JSON files
		const langTitles = {
			'de': 'Sprache', 'en': 'Language', 'fr': 'Langue', 'es': 'Idioma', 'it': 'Lingua',
			'nl': 'Taal', 'pl': 'Język', 'pt': 'Idioma', 'cs': 'Jazyk', 'da': 'Sprog',
			'sv': 'Språk', 'fi': 'Kieli', 'ro': 'Limbă', 'hu': 'Nyelv', 'sk': 'Jazyk',
			'sl': 'Jezik', 'hr': 'Jezik', 'el': 'Γλώσσα', 'bg': 'Език', 'vi': 'Ngôn ngữ'
		};
		if (translations.toolbar) {
			translations.toolbar.language = langTitles[lang] || 'Language';
		}

		// Apply html lang & dir
		if (widget) {
			widget.setAttribute('lang', lang);
			widget.setAttribute('dir', dir);
			if (dir === 'rtl') {
				widget.classList.add('me-rtl');
			}
		}

		// Text translations (data-i18n)
		const textElements = document.querySelectorAll('[data-i18n]');
		textElements.forEach(el => {
			const keyPath = el.getAttribute('data-i18n');
			const value = getTranslationValue(translations, keyPath);
			if (value) {
				const linkEl = el.querySelector('a');
				if (linkEl) {
					if (keyPath === 'toolbar.poweredBy') {
						const linkHTML = linkEl.outerHTML;
						el.innerHTML = value + ' ' + linkHTML;
					} else {
						el.textContent = value;
					}
				} else {
					el.textContent = value;
				}
			}
		});

		// Attribute translations (data-i18n-attr="attrName:keyPath")
		const attrElements = document.querySelectorAll('[data-i18n-attr]');
		attrElements.forEach(el => {
			const spec = el.getAttribute('data-i18n-attr');
			const parts = spec.split(':');
			if (parts.length === 2) {
				const attrName = parts[0];
				const keyPath = parts[1];
				const value = getTranslationValue(translations, keyPath);
				if (value) {
					el.setAttribute(attrName, value);
				}
			}
		});
	}

	function getTranslationValue(obj, path) {
		const parts = path.split('.');
		let current = obj;
		for (let part of parts) {
			if (current === null || current === undefined) {
				return undefined;
			}
			current = current[part];
		}
		return current;
	}

	applyTranslations();

	// ==========================================
	// 2. TOGGLE WIDGET (OEFFNEN / SCHLIESSEN)
	// ==========================================
	if (trigger && widget) {
		trigger.addEventListener('click', () => {
			const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
			if (isExpanded) {
				closeWidget();
			} else {
				openWidget();
			}
		});
	}

	if (closeBtn) {
		closeBtn.addEventListener('click', closeWidget);
	}

	function openWidget() {
		widget.classList.add('me-active');
		widget.setAttribute('aria-hidden', 'false');
		trigger.setAttribute('aria-expanded', 'true');
		
		// Tastaturfokus auf das erste interaktive Element setzen
		setTimeout(() => {
			const firstBtn = widget.querySelector('.me-profile-btn');
			if (firstBtn) firstBtn.focus();
		}, 100);

		document.addEventListener('keydown', handleKeydown);
	}

	function closeWidget() {
		widget.classList.remove('me-active');
		widget.setAttribute('aria-hidden', 'true');
		trigger.setAttribute('aria-expanded', 'false');
		trigger.focus();
		document.removeEventListener('keydown', handleKeydown);
	}

	// ==========================================
	// 3. ACCESSIBILITY: KEYBOARD NAVIGATION (ESCAPE & FOCUS TRAP)
	// ==========================================
	function handleKeydown(e) {
		// Escape schließt das Widget
		if (e.key === 'Escape') {
			closeWidget();
			return;
		}

		// Focus Trapping (Tab-Schleife)
		if (e.key === 'Tab') {
			const focusableElements = widget.querySelectorAll('button, [href], input, select, textarea, [tabindex="0"]');
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
	}

	// Aktiviert Tastaturfokus-Indikatoren, falls mit Tab-Taste navigiert wird.
	window.addEventListener('keydown', (e) => {
		if (e.key === 'Tab') {
			document.documentElement.classList.add('me-keyboard-active');
		}
	});
	window.addEventListener('mousedown', () => {
		document.documentElement.classList.remove('me-keyboard-active');
	});

	// ==========================================
	// 4. ACTION CONTROLLERS & STYLE MODIFIERS
	// ==========================================
	const actionButtons = document.querySelectorAll('.me-action-btn');
	actionButtons.forEach(btn => {
		btn.addEventListener('click', () => {
			const action = btn.getAttribute('data-action');
			toggleAction(action, btn);
		});
	});

	function toggleAction(action, buttonEl = null) {
		// Spezifische Behandlung für Skalierungsfunktionen.
		if (action === 'increase-font') {
			fontScale += 0.1;
			if (fontScale > 1.8) fontScale = 1.8;
			updateFontScale();
			return;
		}
		if (action === 'decrease-font') {
			fontScale -= 0.1;
			if (fontScale < 0.8) fontScale = 0.8;
			updateFontScale();
			return;
		}

		// Standard-Toggle für Klassen am HTML-Tag.
		const className = `me-${action}`;
		const isToggled = document.documentElement.classList.toggle(className);
		activeSettings[action] = isToggled;

		if (buttonEl) {
			buttonEl.classList.toggle('me-active', isToggled);
		} else {
			const targetBtn = widget.querySelector(`.me-action-btn[data-action="${action}"]`);
			if (targetBtn) targetBtn.classList.toggle('me-active', isToggled);
		}

		// Spezifische Aktionen (z.B. Lesehilfe, Lese-Maske)
		if (action === 'reading-guide') {
			readingLine.style.display = isToggled ? 'block' : 'none';
		}
		if (action === 'reading-mask') {
			maskTop.style.display = isToggled ? 'block' : 'none';
			maskBottom.style.display = isToggled ? 'block' : 'none';
		}

		saveState();
	}

	function updateFontScale() {
		document.documentElement.style.fontSize = `${fontScale * 100}%`;
		activeSettings['fontScale'] = fontScale;
		
		const incBtn = widget.querySelector('[data-action="increase-font"]');
		const decBtn = widget.querySelector('[data-action="decrease-font"]');
		if (incBtn) incBtn.classList.toggle('me-active', fontScale > 1.0);
		if (decBtn) decBtn.classList.toggle('me-active', fontScale < 1.0);
		
		saveState();
	}

	// ==========================================
	// 5. LESEHILFEN MOUSE TRACKER
	// ==========================================
	window.addEventListener('mousemove', (e) => {
		const clientY = e.clientY;

		// 1. Leselinie
		if (activeSettings['reading-guide'] && readingLine) {
			readingLine.style.top = `${clientY}px`;
		}

		// 2. Lesemaske
		if (activeSettings['reading-mask'] && maskTop && maskBottom) {
			const maskGap = 80; // Größe des Fensters, durch das man sieht
			const topHeight = clientY - (maskGap / 2);
			const bottomTop = clientY + (maskGap / 2);

			maskTop.style.height = `${Math.max(0, topHeight)}px`;
			maskBottom.style.top = `${bottomTop}px`;
			maskBottom.style.height = `calc(100vh - ${bottomTop}px)`;
		}
	});

	// ==========================================
	// 6. ACCESSIBILITY PROFILES
	// ==========================================
	const profileButtons = document.querySelectorAll('.me-profile-btn');
	profileButtons.forEach(btn => {
		btn.addEventListener('click', (e) => {
			// Pro-Feature Check für vordefinierte Profile
			if (typeof meAccessibilityToolbar !== 'undefined' && !meAccessibilityToolbar.isPro) {
				e.preventDefault();
				showUpgradeModal(btn.querySelector('h5').textContent);
				return;
			}

			const profile = btn.getAttribute('data-profile');
			const isActive = btn.classList.toggle('me-active');

			// Zuvor gesetzte Profile entfernen
			profileButtons.forEach(b => {
				if (b !== btn) b.classList.remove('me-active');
			});

			resetAllSettings(false); // Vorher alles sauber leeren

			if (isActive) {
				activateProfile(profile, btn);
			}
		});
	});

	function showUpgradeModal(profileName) {
		let modal = document.getElementById('me-upgrade-modal');
		if (!modal) {
			modal = document.createElement('div');
			modal.id = 'me-upgrade-modal';
			modal.className = 'me-upgrade-modal-overlay';
			modal.innerHTML = `
				<div class="me-upgrade-modal-box">
					<div class="me-upgrade-modal-header">
						<span class="dashicons dashicons-lock me-upgrade-lock-icon"></span>
						<h4>Premium-Funktion freischalten</h4>
						<button class="me-upgrade-modal-close" aria-label="Schließen">&times;</button>
					</div>
					<div class="me-upgrade-modal-body">
						<p>Das Barrierefreiheitsprofil <strong>"${profileName}"</strong> ist ein Premium-Feature von <strong>ME Accessibility Pro</strong>.</p>
						<p>Upgrade zu Pro, um alle vordefinierten Profile, den vollständigen WCAG-Scanner und automatisierte geplante Scans freizuschalten.</p>
					</div>
					<div class="me-upgrade-modal-footer">
						<a href="https://me-toolkit.com" target="_blank" rel="noopener" class="me-upgrade-btn">Jetzt Upgrade durchführen</a>
						<button class="me-upgrade-cancel-btn">Abbrechen</button>
					</div>
				</div>
			`;
			document.body.appendChild(modal);

			const style = document.createElement('style');
			style.textContent = `
				.me-upgrade-modal-overlay {
					position: fixed;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					background: rgba(15, 23, 42, 0.6);
					backdrop-filter: blur(4px);
					display: flex;
					align-items: center;
					justify-content: center;
					z-index: 999999;
					opacity: 0;
					transition: opacity 0.25s ease-in-out;
					pointer-events: none;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
				}
				.me-upgrade-modal-overlay.me-show {
					opacity: 1;
					pointer-events: auto;
				}
				.me-upgrade-modal-box {
					background: #ffffff;
					border-radius: 12px;
					width: 90%;
					max-width: 440px;
					padding: 24px;
					box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
					transform: scale(0.9);
					transition: transform 0.25s ease-in-out;
				}
				.me-upgrade-modal-overlay.me-show .me-upgrade-modal-box {
					transform: scale(1);
				}
				.me-upgrade-modal-header {
					text-align: center;
					position: relative;
					margin-bottom: 16px;
				}
				.me-upgrade-lock-icon {
					font-size: 40px;
					width: 40px;
					height: 40px;
					color: #2563eb;
					background: #eff6ff;
					padding: 12px;
					border-radius: 50%;
					display: inline-block;
					margin-bottom: 12px;
				}
				.me-upgrade-modal-header h4 {
					margin: 0;
					font-size: 18px;
					font-weight: 700;
					color: #0f172a;
				}
				.me-upgrade-modal-close {
					position: absolute;
					top: -10px;
					right: -10px;
					background: none;
					border: none;
					font-size: 24px;
					color: #94a3b8;
					cursor: pointer;
				}
				.me-upgrade-modal-close:hover {
					color: #475569;
				}
				.me-upgrade-modal-body {
					text-align: center;
					color: #475569;
					font-size: 14px;
					line-height: 1.5;
					margin-bottom: 24px;
				}
				.me-upgrade-modal-body p {
					margin: 0 0 12px 0;
				}
				.me-upgrade-modal-body p:last-child {
					margin: 0;
				}
				.me-upgrade-modal-footer {
					display: flex;
					flex-direction: column;
					gap: 8px;
				}
				.me-upgrade-btn {
					background: #2563eb;
					color: #ffffff;
					text-align: center;
					padding: 10px 16px;
					border-radius: 6px;
					font-size: 14px;
					font-weight: 600;
					text-decoration: none;
					transition: background 0.15s ease-in-out;
				}
				.me-upgrade-btn:hover {
					background: #1d4ed8;
					color: #ffffff;
				}
				.me-upgrade-cancel-btn {
					background: #f1f5f9;
					color: #475569;
					border: none;
					padding: 10px 16px;
					border-radius: 6px;
					font-size: 14px;
					font-weight: 600;
					cursor: pointer;
					transition: background 0.15s ease-in-out;
				}
				.me-upgrade-cancel-btn:hover {
					background: #e2e8f0;
				}
			`;
			document.head.appendChild(style);

			modal.querySelector('.me-upgrade-modal-close').addEventListener('click', () => {
				modal.classList.remove('me-show');
			});
			modal.querySelector('.me-upgrade-cancel-btn').addEventListener('click', () => {
				modal.classList.remove('me-show');
			});
			modal.addEventListener('click', (e) => {
				if (e.target === modal) {
					modal.classList.remove('me-show');
				}
			});
		}

		modal.querySelector('.me-upgrade-modal-body').innerHTML = `
			<p>Das Barrierefreiheitsprofil <strong>"${profileName}"</strong> ist ein Premium-Feature von <strong>ME Accessibility Pro</strong>.</p>
			<p>Upgrade zu Pro, um alle vordefinierten Profile, den vollständigen WCAG-Scanner und automatisierte geplante Scans freizuschalten.</p>
		`;

		setTimeout(() => {
			modal.classList.add('me-show');
		}, 10);
	}

	function activateProfile(profile, btnEl) {
		if (profile === 'adhd') {
			// ADHS-Freundlich: Lesehilfe und Animationen stoppen
			toggleAction('reading-guide');
			toggleAction('pause-animations');
		} else if (profile === 'vision') {
			// Sehbehinderung: Schrift vergrößern und hoher Kontrast
			fontScale = 1.3;
			updateFontScale();
			toggleAction('high-contrast');
		} else if (profile === 'keyboard') {
			// Tastaturfokus hervorheben
			document.documentElement.classList.add('me-keyboard-active');
			toggleAction('highlight-links');
		} else if (profile === 'cognitive') {
			// Gut lesbare Schrift & Link-Hervorhebung
			toggleAction('readable-font');
			toggleAction('highlight-links');
		}
		btnEl.classList.add('me-active');
	}

	// ==========================================
	// 7. RESET & LOCALSTORAGE SYNCRONISATION
	// ==========================================
	if (resetBtn) {
		resetBtn.addEventListener('click', () => {
			resetAllSettings(true);
		});
	}

	function resetAllSettings(withResetBtn = true) {
		// Klassen löschen
		const classesToRemove = Array.from(document.documentElement.classList).filter(c => c.startsWith('me-'));
		classesToRemove.forEach(c => document.documentElement.classList.remove(c));

		// Layout-Skalierung leeren
		document.documentElement.style.fontSize = '';
		fontScale = 1.0;

		// GUI Buttons anpassen
		actionButtons.forEach(btn => btn.classList.remove('me-active'));
		profileButtons.forEach(btn => btn.classList.remove('me-active'));

		// Lesehilfen ausblenden
		if (readingLine) readingLine.style.display = 'none';
		if (maskTop) maskTop.style.display = 'none';
		if (maskBottom) maskBottom.style.display = 'none';

		activeSettings = {};
		localStorage.removeItem('me_accessibility_state');

		// Custom Event auslösen für optionale Kopplung (z.B. TTS)
		window.dispatchEvent(new CustomEvent('me_accessibility_reset'));
	}

	function saveState() {
		localStorage.setItem('me_accessibility_state', JSON.stringify({
			fontScale: fontScale,
			settings: activeSettings
		}));
	}

	function loadState() {
		const stateStr = localStorage.getItem('me_accessibility_state');
		if (!stateStr) return;

		try {
			const state = JSON.parse(stateStr);
			if (state.fontScale) {
				fontScale = state.fontScale;
				updateFontScale();
			}
			if (state.settings) {
				activeSettings = state.settings;
				Object.keys(activeSettings).forEach(action => {
					if (activeSettings[action]) {
						const className = `me-${action}`;
						document.documentElement.classList.add(className);
						
						const targetBtn = widget.querySelector(`.me-action-btn[data-action="${action}"]`);
						if (targetBtn) targetBtn.classList.add('me-active');

						if (action === 'reading-guide' && readingLine) readingLine.style.display = 'block';
						if (action === 'reading-mask' && maskTop && maskBottom) {
							maskTop.style.display = 'block';
							maskBottom.style.display = 'block';
						}
					}
				});
			}
		} catch (e) {
			console.error('ME Accessibility State laden fehlgeschlagen', e);
		}
	}

	// ==========================================
	// 8. DYNAMIC MANUAL LANGUAGE SWITCHER HANDLER (Dropdown)
	// ==========================================
	const dropdownTrigger = document.getElementById('me-lang-dropdown-trigger');
	const dropdownList = document.getElementById('me-lang-dropdown-list');
	const dropdownOptions = document.querySelectorAll('.me-lang-dropdown-option');

	if (dropdownTrigger && dropdownList) {
		dropdownTrigger.addEventListener('click', (e) => {
			e.stopPropagation();
			const isExpanded = dropdownTrigger.getAttribute('aria-expanded') === 'true';
			if (isExpanded) {
				closeDropdown();
			} else {
				openDropdown();
			}
		});

		function openDropdown() {
			dropdownList.style.display = 'block';
			dropdownTrigger.setAttribute('aria-expanded', 'true');
		}

		function closeDropdown() {
			dropdownList.style.display = 'none';
			dropdownTrigger.setAttribute('aria-expanded', 'false');
		}

		// Close dropdown on click outside
		document.addEventListener('click', (e) => {
			if (!dropdownTrigger.contains(e.target) && !dropdownList.contains(e.target)) {
				closeDropdown();
			}
		});

		// Option selection
		dropdownOptions.forEach(opt => {
			opt.addEventListener('click', () => {
				const selectedLang = opt.getAttribute('data-lang');
				if (!selectedLang) return;

				// Save to cookie (lasts 30 days)
				const expires = new Date();
				expires.setTime(expires.getTime() + (30 * 24 * 60 * 60 * 1000));
				document.cookie = `me_accessibility_lang=${selectedLang};expires=${expires.toUTCString()};path=/`;

				// Reload page to apply locale
				const url = new URL(window.location.href);
				url.searchParams.set('me_lang', selectedLang);
				window.location.href = url.toString();
			});
		});
	}

	loadState();
});
