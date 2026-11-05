/**
 * Clientseitige Smart Reading Text-to-Speech (TTS) Engine.
 *
 * Verwendet ausschließlich die native window.speechSynthesis API.
 * DSGVO-konform: Kein externer Datentransfer.
 *
 * Strategie zur Textextraktion (3 Stufen):
 *  1. Semantische Block-Elemente via querySelector (schnell, kein getComputedStyle-Loop)
 *  2. Alle sichtbaren Element-Knoten mit Text als Fallback
 *  3. Nuklearer Fallback: document.body.innerText in Sätze aufteilen
 */
document.addEventListener('DOMContentLoaded', () => {
	'use strict';

	// ==========================================
	// 1. DOM-REFERENZEN & GRUNDVARIABLEN
	// ==========================================
	const ttsSection  = document.getElementById('me-tts-player-section');
	const playBtn     = document.getElementById('me-tts-btn-play');
	const pauseBtn    = document.getElementById('me-tts-btn-pause');
	const stopBtn     = document.getElementById('me-tts-btn-stop');
	const voiceSelect = document.getElementById('me-tts-voice-select');
	const rateInput   = document.getElementById('me-tts-rate');

	const synth = window.speechSynthesis;

	if (!synth) {
		console.warn('[ME-TTS] SpeechSynthesis wird von diesem Browser nicht unterstützt.');
		return;
	}

	// TTS wird unterstützt → Sektion einblenden
	if (ttsSection) {
		ttsSection.style.display = 'block';
	}

	let voices            = [];
	let textBlocks        = [];
	let currentBlockIndex = 0;
	let currentUtterance  = null;
	let isPaused          = false;
	let isPlaying         = false;

	// ==========================================
	// 2. STIMMEN-STEUERUNG
	// ==========================================
	function populateVoices() {
		// Pro-Feature Check für Stimmen und Geschwindigkeiten
		if (typeof meAccessibilityToolbar !== 'undefined' && !meAccessibilityToolbar.isPro) {
			if (voiceSelect) {
				voiceSelect.innerHTML = `<option value="">System-Standardstimme</option>`;
				voiceSelect.disabled = true;
			}
			if (rateInput) {
				rateInput.value = 1.0;
				rateInput.disabled = true;
				rateInput.style.opacity = '0.5';
				rateInput.style.pointerEvents = 'none';
			}
			// Wir holen uns die echten Stimmen im Hintergrund für Standardvorlesung, füllen aber die UI nicht
			const available = synth.getVoices();
			if (available && available.length > 0) {
				voices = available;
			}
			return;
		}

		const available = synth.getVoices();
		if (!available || available.length === 0) return;
		voices = available;

		if (!voiceSelect) return;
		voiceSelect.innerHTML = '';

		let pageLang = 'de';
		if (document.documentElement.lang) {
			pageLang = document.documentElement.lang.substring(0, 2).toLowerCase();
		} else if (typeof meAccessibilityToolbar !== 'undefined' && meAccessibilityToolbar.lang) {
			pageLang = meAccessibilityToolbar.lang;
		}

		const supportedLangs = [
			'de', 'en', 'fr', 'es', 'it', 'nl', 'pl', 'pt', 'cs', 'da', 
			'sv', 'fi', 'ro', 'hu', 'sk', 'sl', 'hr', 'el', 'bg', 'vi'
		];
		let defaultSelected = false;

		voices.forEach(voice => {
			const langCode = voice.lang.substring(0, 2).toLowerCase();
			if (!supportedLangs.includes(langCode)) return;

			const option = document.createElement('option');
			option.value       = voice.name;
			option.textContent = `${voice.name} (${voice.lang})`;

			if (!defaultSelected && langCode === pageLang) {
				option.selected  = true;
				defaultSelected  = true;
			}
			voiceSelect.appendChild(option);
		});

		// Fallback: If no voice perfectly matches the page language, select English as fallback, or the first option.
		if (!defaultSelected && voiceSelect.children.length > 0) {
			let fallbackOption = Array.from(voiceSelect.children).find(opt => {
				const voiceObj = voices.find(v => v.name === opt.value);
				return voiceObj && voiceObj.lang.substring(0, 2).toLowerCase() === 'en';
			});
			if (!fallbackOption) {
				fallbackOption = voiceSelect.children[0];
			}
			if (fallbackOption) {
				fallbackOption.selected = true;
				defaultSelected = true;
			}
		}

		// Falls keine passende Stimme: alle anzeigen (max. 20)
		if (voiceSelect.children.length === 0) {
			voices.slice(0, 20).forEach(voice => {
				const option = document.createElement('option');
				option.value       = voice.name;
				option.textContent = `${voice.name} (${voice.lang})`;
				voiceSelect.appendChild(option);
			});
		}
	}

	populateVoices();
	if (speechSynthesis.onvoiceschanged !== undefined) {
		speechSynthesis.onvoiceschanged = populateVoices;
	}

	// ==========================================
	// 3. TEXTEXTRAKTION – 3-STUFEN-STRATEGIE
	// ==========================================

	/**
	 * Stufe 1: Semantische Block-Selektoren.
	 * Schnell, kein DOM-Traversal, kein getComputedStyle.
	 */
	function extractBySelectorStrategy() {
		const blocks = [];
		// Elemente, die keinen Wrapper mit Text-Duplikat haben
		const BLOCK_SELECTOR = [
			'main p', 'article p', '.entry-content p', '.post-content p', '.page-content p',
			'main h1', 'main h2', 'main h3', 'main h4', 'main h5', 'main h6',
			'article h1', 'article h2', 'article h3', 'article h4', 'article h5', 'article h6',
			'.entry-content h1', '.entry-content h2', '.entry-content h3',
			'main li', 'article li', '.entry-content li',
			'main blockquote', 'article blockquote',
			// Fallback: jedes sichtbare p auf der Seite
			'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
		].join(', ');

		// Ausschluss-Prüfung ohne getComputedStyle
		function shouldExclude(el) {
			if (!el) return true;
			// Aria-hidden
			if (el.getAttribute('aria-hidden') === 'true') return true;
			// Bekannte UI-Widget-Container
			if (el.closest('#me-toolbar-widget, #wpadminbar, .admin-bar, [role="toolbar"]')) return true;
			// Inline display:none / visibility:hidden über Attribut-Style
			const inlineStyle = el.style;
			if (inlineStyle.display === 'none' || inlineStyle.visibility === 'hidden') return true;
			// hidden-Attribut
			if (el.hidden) return true;
			return false;
		}

		const seen = new Set();
		document.querySelectorAll(BLOCK_SELECTOR).forEach(el => {
			if (seen.has(el)) return;
			if (shouldExclude(el)) return;
			const text = (el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim();
			if (text.length >= 3) {
				seen.add(el);
				blocks.push({ element: el, text });
			}
		});

		return blocks;
	}

	/**
	 * Stufe 2: Alle sichtbaren Elemente mit `innerText` (Fallback).
	 */
	function extractByAllVisibleElements() {
		const blocks  = [];
		const allEls  = document.body.querySelectorAll('*');
		const seen    = new Set();

		const SKIP_TAGS = new Set(['SCRIPT', 'STYLE', 'NOSCRIPT', 'SVG', 'IFRAME', 'IMG', 'INPUT', 'SELECT', 'TEXTAREA', 'BUTTON', 'META', 'LINK', 'HEAD']);

		allEls.forEach(el => {
			if (SKIP_TAGS.has(el.tagName)) return;
			if (el.closest('#me-toolbar-widget, #wpadminbar, .admin-bar')) return;
			if (el.getAttribute('aria-hidden') === 'true') return;
			if (el.hidden) return;
			if (el.style.display === 'none' || el.style.visibility === 'hidden') return;

			// Nur direkte Text-Kinder vorhanden? → Kein weiterer Abstieg
			const hasBlockChild = Array.from(el.children).some(c => {
				const t = c.tagName;
				return ['P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'LI', 'BLOCKQUOTE', 'DIV', 'SECTION', 'ARTICLE'].includes(t);
			});
			if (hasBlockChild) return; // Kinder übernehmen den Text

			const text = (el.innerText || '').replace(/\s+/g, ' ').trim();
			if (text.length >= 10 && !seen.has(text)) {
				seen.add(text);
				blocks.push({ element: el, text });
			}
		});

		return blocks;
	}

	/**
	 * Stufe 3: Nuklearer Fallback – ganzen Body-Text in Sätze aufteilen.
	 * Kein Element-Highlighting möglich, aber Lesen funktioniert garantiert.
	 */
	function extractByBodyText() {
		const bodyText = (document.body.innerText || document.body.textContent || '').replace(/\s+/g, ' ').trim();
		if (!bodyText) return [];

		// Sätze aufteilen (Punkt, Ausrufe-, Fragezeichen als Trenner)
		const sentences = bodyText
			.split(/(?<=[.!?])\s+/)
			.map(s => s.trim())
			.filter(s => s.length >= 3);

		console.warn('[ME-TTS] Nuklearer Fallback aktiv – lese Body-Text als Sätze vor (' + sentences.length + ' Sätze).');

		// Dummy-Element: gesamte Seite
		return sentences.map(text => ({ element: document.body, text }));
	}

	/**
	 * Hauptfunktion: Wählt die beste verfügbare Strategie.
	 */
	function extractTextBlocks() {
		textBlocks        = [];
		currentBlockIndex = 0;

		// Stufe 1: Semantische Selektoren
		let blocks = extractBySelectorStrategy();
		console.log('[ME-TTS] Stufe 1 (Selector): ' + blocks.length + ' Blöcke gefunden.');

		// Stufe 2: Alle sichtbaren Elemente
		if (blocks.length < 3) {
			blocks = extractByAllVisibleElements();
			console.log('[ME-TTS] Stufe 2 (AllElements): ' + blocks.length + ' Blöcke gefunden.');
		}

		// Stufe 3: Body-Text als Sätze
		if (blocks.length < 3) {
			blocks = extractByBodyText();
			console.log('[ME-TTS] Stufe 3 (BodyText): ' + blocks.length + ' Sätze gefunden.');
		}

		textBlocks = blocks;
	}

	// ==========================================
	// 4. ABSPIEL-LOGIK (PLAY, PAUSE, STOP)
	// ==========================================
	if (playBtn)  playBtn.addEventListener('click', startReading);
	if (pauseBtn) pauseBtn.addEventListener('click', pauseReading);
	if (stopBtn)  stopBtn.addEventListener('click', stopReading);

	function startReading() {
		// Fortsetzen bei Pause
		if (isPaused) {
			synth.resume();
			isPaused  = false;
			isPlaying = true;
			togglePlayUI(true);
			return;
		}

		// Laufende Ausgabe abbrechen & UI zurücksetzen
		synth.cancel();
		clearAllHighlights();

		// Stimmenliste ggf. nachladen (asynchrone Browser-API)
		if (!voices || voices.length === 0) {
			populateVoices();
		}

		// Texte extrahieren
		extractTextBlocks();

		if (textBlocks.length === 0) {
			console.error('[ME-TTS] Keine vorlesbaren Texte gefunden – Abbruch.');
			alert((typeof meAccessibilityTTS !== 'undefined' && meAccessibilityTTS.noTextFound)
				? meAccessibilityTTS.noTextFound
				: 'Kein lesbarer Text auf dieser Seite gefunden.');
			return;
		}

		isPlaying         = true;
		currentBlockIndex = 0;
		togglePlayUI(true);

		// Chrome-Bug: kurze Pause vor dem ersten speak() nötig
		setTimeout(readNextBlock, 50);
	}

	function readNextBlock() {
		if (!isPlaying || currentBlockIndex >= textBlocks.length) {
			stopReading();
			return;
		}

		const block             = textBlocks[currentBlockIndex];
		const selectedVoiceName = voiceSelect ? voiceSelect.value : '';
		const rate              = rateInput ? (parseFloat(rateInput.value) || 1.0) : 1.0;

		clearAllHighlights();

		// Element hervorheben & in Sicht scrollen (nur wenn nicht document.body)
		if (block.element && block.element !== document.body) {
			block.element.classList.add('me-tts-highlight');
			block.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}

		currentUtterance = new SpeechSynthesisUtterance(block.text);

		// Stimme zuweisen
		const activeVoice = voices.find(v => v.name === selectedVoiceName);
		if (activeVoice) {
			currentUtterance.voice = activeVoice;
			currentUtterance.lang  = activeVoice.lang;
		}

		currentUtterance.rate = rate;

		currentUtterance.onend = () => {
			currentBlockIndex++;
			readNextBlock();
		};

		currentUtterance.onerror = (e) => {
			// 'interrupted' tritt normal bei cancel() auf – ignorieren
			if (e.error === 'interrupted' || e.error === 'canceled') return;
			console.error('[ME-TTS] Utterance-Fehler:', e.error);
			// Fehlerhafte Blöcke überspringen, nicht abbrechen
			currentBlockIndex++;
			readNextBlock();
		};

		synth.speak(currentUtterance);
	}

	function pauseReading() {
		if (synth.speaking && !synth.paused) {
			synth.pause();
			isPaused  = true;
			isPlaying = false;
			togglePlayUI(false);
		}
	}

	function stopReading() {
		synth.cancel();
		isPaused          = false;
		isPlaying         = false;
		currentBlockIndex = 0;
		clearAllHighlights();
		togglePlayUI(false);
	}

	// ==========================================
	// 5. HELPER & UI-SYNCHRONISATION
	// ==========================================
	function clearAllHighlights() {
		document.querySelectorAll('.me-tts-highlight').forEach(el => {
			el.classList.remove('me-tts-highlight');
		});
	}

	function togglePlayUI(playing) {
		if (!playBtn || !pauseBtn) return;
		if (playing) {
			playBtn.style.display  = 'none';
			pauseBtn.style.display = 'flex';
		} else {
			playBtn.style.display  = 'flex';
			pauseBtn.style.display = 'none';
		}
	}

	// Stoppt die Sprachausgabe beim Widget-Reset oder Schließen
	window.addEventListener('me_accessibility_reset', stopReading);

	const closeWidgetBtn = document.getElementById('me-widget-close-btn');
	if (closeWidgetBtn) {
		closeWidgetBtn.addEventListener('click', stopReading);
	}
});
