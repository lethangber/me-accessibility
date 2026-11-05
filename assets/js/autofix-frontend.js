/**
 * Frontend Barrierefreiheits-Engine für ME Accessibility.
 * 
 * Wendet in Echtzeit (Runtime) sichere WCAG 2.1 AA-konforme Korrekturen
 * auf das DOM an, ohne die Datenbank oder Page-Builder-Daten dauerhaft zu verändern.
 * 
 * Unterstützte Page-Builder:
 * - Elementor, Bricks, Divi, WPBakery, Breakdance, Oxygen, Spectra, Gutenberg.
 */
(function() {
	'use strict';

	// Initialisierung nach Laden des DOM.
	document.addEventListener('DOMContentLoaded', function() {
		// 1. Skip-Link (Schnellspring-Link) injizieren
		injectSkipLink();

		// 2. Aktive Page-Builder und Themes erkennen
		const detectedBuilders = detectActiveBuilders();

		// 3. Allgemeine (globale) Korrekturen vornehmen
		applyGlobalFixes();

		// 4. Page-Builder spezifische Optimierungen ausführen
		applyBuilderSpecificFixes(detectedBuilders);
	});

	/**
	 * Injiziert einen barrierefreien "Skip to Content"-Link am Anfang des Bodys.
	 * Ermöglicht Tastaturnutzern das Überspringen des Hauptmenüs.
	 */
	function injectSkipLink() {
		// Prüfen, ob bereits ein solcher Link existiert.
		if (document.querySelector('.me-skip-link') || document.getElementById('skip-to-content')) {
			return;
		}

		// Ziel-Container für den Hauptinhalt suchen.
		let mainContent = document.querySelector('main') || document.getElementById('content') || document.querySelector('.site-content') || document.querySelector('article');
		if (!mainContent) {
			// Falls kein Standard-Tag vorhanden, suchen wir das erste sinnvolle div.
			mainContent = document.querySelector('.entry-content') || document.body;
		}

		// Eine ID für das Sprungziel setzen, falls nicht vorhanden.
		if (mainContent && !mainContent.id) {
			mainContent.id = 'me-main-content';
		}

		const targetId = mainContent ? mainContent.id : 'me-main-content';
		const skipText = window.meAutofixData ? window.meAutofixData.skipToContentText : 'Direkt zum Hauptinhalt springen';

		const skipLink = document.createElement('a');
		skipLink.href = '#' + targetId;
		skipLink.className = 'me-skip-link';
		skipLink.textContent = skipText;

		document.body.insertBefore(skipLink, document.body.firstChild);
	}

	/**
	 * Scannt die CSS-Klassen des Dokuments, um aktive Page-Builder zu identifizieren.
	 * 
	 * @return {Object} Liste der erkannten Builder.
	 */
	function detectActiveBuilders() {
		const builders = {
			elementor: false,
			bricks: false,
			wpbakery: false,
			divi: false,
			breakdance: false,
			oxygen: false,
			gutenberg: false
		};

		const bodyClasses = document.body.className;

		// Elementor-Prüfung.
		if (bodyClasses.indexOf('elementor') !== -1 || document.querySelector('.elementor-widget')) {
			builders.elementor = true;
		}
		// Bricks-Prüfung.
		if (bodyClasses.indexOf('bricks') !== -1 || document.querySelector('[data-bricks-element]')) {
			builders.bricks = true;
		}
		// WPBakery-Prüfung.
		if (bodyClasses.indexOf('wpb-js-composer') !== -1 || document.querySelector('.vc_row')) {
			builders.wpbakery = true;
		}
		// Divi-Prüfung.
		if (bodyClasses.indexOf('et_divi_theme') !== -1 || document.querySelector('.et_pb_section')) {
			builders.divi = true;
		}
		// Breakdance-Prüfung.
		if (bodyClasses.indexOf('breakdance') !== -1 || document.querySelector('.breakdance')) {
			builders.breakdance = true;
		}
		// Oxygen-Prüfung.
		if (bodyClasses.indexOf('oxygen') !== -1 || document.querySelector('.ct-section')) {
			builders.oxygen = true;
		}
		// Gutenberg-Prüfung.
		if (document.querySelector('.wp-block-group') || document.querySelector('.wp-block-columns')) {
			builders.gutenberg = true;
		}

		return builders;
	}

	/**
	 * Wendet allgemeine Optimierungen auf Standard-HTML-Elemente an.
	 */
	function applyGlobalFixes() {
		// A. ARIA-Labels für leere Buttons, Menü-Toggles, Links hinzufügen.
		const buttons = document.querySelectorAll('button, .btn, .button, a[role="button"]');
		buttons.forEach(btn => {
			const text = btn.textContent.trim();
			// Wenn der Button keinen Text besitzt, aber ein Icon hat (z. B. Suchlupe).
			if (text === '' && !btn.hasAttribute('aria-label') && !btn.hasAttribute('title')) {
				const isSearch = btn.className.indexOf('search') !== -1 || btn.querySelector('.fa-search, .dashicons-search');
				const isClose = btn.className.indexOf('close') !== -1 || btn.querySelector('.fa-times, .dashicons-no-alt');
				const isMenu = btn.className.indexOf('menu') !== -1 || btn.querySelector('.fa-bars, .dashicons-menu');

				if (isSearch) {
					btn.setAttribute('aria-label', 'Suche starten');
				} else if (isClose) {
					btn.setAttribute('aria-label', 'Schließen');
				} else if (isMenu) {
					btn.setAttribute('aria-label', 'Menü öffnen');
				} else {
					btn.setAttribute('aria-label', 'Aktion ausführen');
				}
			}
		});

		// B. Leere Text-Links (z.B. Social Icon Links) mit Screenreader-Hilfstext versehen.
		const links = document.querySelectorAll('a');
		links.forEach(link => {
			const text = link.textContent.trim();
			if (text === '' && !link.hasAttribute('aria-label') && !link.hasAttribute('title')) {
				// Prüfen, ob ein Icon eingebettet ist, um das Social Media Ziel zu erraten.
				const html = link.innerHTML.toLowerCase();
				let targetText = '';

				if (html.indexOf('facebook') !== -1) targetText = 'Facebook Profil';
				else if (html.indexOf('twitter') !== -1 || html.indexOf('x-twitter') !== -1) targetText = 'Twitter Profil';
				else if (html.indexOf('instagram') !== -1) targetText = 'Instagram Profil';
				else if (html.indexOf('youtube') !== -1) targetText = 'YouTube Kanal';
				else if (html.indexOf('linkedin') !== -1) targetText = 'LinkedIn Profil';
				else if (html.indexOf('pinterest') !== -1) targetText = 'Pinterest Profil';
				
				if (targetText !== '') {
					link.setAttribute('aria-label', targetText);
				} else {
					// Fallback: Einen Screenreader-spezifischen Hilfstext injizieren.
					const srText = document.createElement('span');
					srText.className = 'me-sr-only';
					srText.textContent = 'Link aufrufen';
					link.appendChild(srText);
				}
			}
		});

		// C. Formular-Labels: Inputs ohne Label über Placeholder verknüpfen.
		const inputs = document.querySelectorAll('input[type="text"], input[type="search"], input[type="email"], input[type="tel"]');
		inputs.forEach(input => {
			if (!input.hasAttribute('aria-label') && !input.hasAttribute('aria-labelledby')) {
				// Suchen nach zugeordnetem Label über ID.
				let hasLabel = false;
				if (input.id) {
					hasLabel = document.querySelector('label[for="' + input.id + '"]') !== null;
				}
				
				// Falls kein Label vorhanden, den Platzhalter (Placeholder) als ARIA-Label verwenden.
				if (!hasLabel && input.hasAttribute('placeholder')) {
					input.setAttribute('aria-label', input.getAttribute('placeholder'));
				}
			}
		});

		// D. Nicht-semantische Buttons (Divs mit Klick-Event) zugänglich machen.
		const divButtons = document.querySelectorAll('div[onclick], span[onclick], div.button-click-action');
		divButtons.forEach(div => {
			if (!div.hasAttribute('role')) {
				div.setAttribute('role', 'button');
			}
			if (!div.hasAttribute('tabindex')) {
				div.setAttribute('tabindex', '0'); // Tastaturfokus ermöglichen.
			}
			
			// Event-Listener hinzufügen, damit "Leertaste" und "Enter" den Klick auslösen.
			div.addEventListener('keydown', function(e) {
				if (e.key === ' ' || e.key === 'Enter') {
					e.preventDefault();
					div.click();
				}
			});
		});

		// E. Mobile Touch-Targets vergrößern.
		if (window.innerWidth <= 768) {
			const touchElements = document.querySelectorAll('a, button, input[type="submit"], .me-lang-btn');
			touchElements.forEach(el => {
				const rect = el.getBoundingClientRect();
				if (rect.width < 44 || rect.height < 44) {
					el.classList.add('me-touch-target-adjusted');
				}
			});
		}
	}

	/**
	 * Führt Page-Builder spezifische DOM-Korrekturen aus.
	 * 
	 * @param {Object} builders Liste der erkannten Page-Builder.
	 */
	function applyBuilderSpecificFixes(builders) {
		// 1. ELEMENTOR OPTIMIERUNGEN
		if (builders.elementor) {
			// A. Elementor Hamburger-Menü Toggle Barrierefreiheit.
			const elMenuToggles = document.querySelectorAll('.elementor-menu-toggle');
			elMenuToggles.forEach(toggle => {
				if (!toggle.hasAttribute('aria-label')) {
					toggle.setAttribute('aria-label', 'Menü umschalten');
				}
				if (!toggle.hasAttribute('role')) {
					toggle.setAttribute('role', 'button');
				}
				toggle.setAttribute('tabindex', '0');
			});

			// B. Elementor Icon Box Links korrigieren.
			const elIconBoxes = document.querySelectorAll('.elementor-icon-box-wrapper');
			elIconBoxes.forEach(box => {
				const link = box.querySelector('a');
				if (link && !link.hasAttribute('aria-label')) {
					const title = box.querySelector('.elementor-icon-box-title');
					if (title) {
						link.setAttribute('aria-label', title.textContent.trim());
					}
				}
			});
		}

		// 2. DIVI OPTIMIERUNGEN
		if (builders.divi) {
			// A. Slider Navigation Barrierefreiheit.
			const diviSliders = document.querySelectorAll('.et_pb_slider');
			diviSliders.forEach(slider => {
				const controls = slider.querySelectorAll('.et-pb-controllers a');
				controls.forEach((ctrl, index) => {
					ctrl.setAttribute('role', 'button');
					ctrl.setAttribute('aria-label', 'Gehe zu Slide ' + (index + 1));
				});
				
				const arrows = slider.querySelectorAll('.et-pb-arrow-prev, .et-pb-arrow-next');
				arrows.forEach(arrow => {
					arrow.setAttribute('role', 'button');
					arrow.setAttribute('tabindex', '0');
					if (arrow.classList.contains('et-pb-arrow-prev')) {
						arrow.setAttribute('aria-label', 'Vorheriger Slide');
					} else {
						arrow.setAttribute('aria-label', 'Nächster Slide');
					}
				});
			});
		}

		// 3. WPBAKERY OPTIMIERUNGEN
		if (builders.wpbakery) {
			// A. WPBakery Akkordeons und Tabs barrierefrei machen (Tastaturbedienung).
			const wpbTabs = document.querySelectorAll('.vc_tta-tabs-list li a');
			wpbTabs.forEach(tab => {
				tab.setAttribute('role', 'tab');
				tab.setAttribute('tabindex', '0');
			});

			const wpbAccordions = document.querySelectorAll('.vc_tta-panel-title a');
			wpbAccordions.forEach(acc => {
				acc.setAttribute('role', 'button');
				acc.setAttribute('tabindex', '0');
			});
		}

		// 4. BRICKS BUILDER OPTIMIERUNGEN
		if (builders.bricks) {
			// Bricks spezifische Toggles und ARIA-Attribute optimieren.
			const bricksToggles = document.querySelectorAll('[data-bricks-toggle]');
			bricksToggles.forEach(toggle => {
				if (!toggle.hasAttribute('aria-expanded')) {
					toggle.setAttribute('aria-expanded', 'false');
				}
				toggle.setAttribute('role', 'button');
				toggle.setAttribute('tabindex', '0');
			});
		}

		// 5. GUTENBERG / BLOCK-BUILDER OPTIMIERUNGEN
		if (builders.gutenberg) {
			// A. Gutenberg Navigations-Blöcke.
			const gNavs = document.querySelectorAll('.wp-block-navigation');
			gNavs.forEach(nav => {
				if (!nav.hasAttribute('role')) {
					nav.setAttribute('role', 'navigation');
				}
				// ARIA-Label vergeben, falls keins vorhanden.
				if (!nav.hasAttribute('aria-label')) {
					nav.setAttribute('aria-label', 'Hauptnavigation');
				}
			});
		}
	}
})();
