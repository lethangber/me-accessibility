<?php
// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$accent_color  = ! empty( $settings['accent_color'] ) ? $settings['accent_color'] : '#2563eb';
$icon_type     = ! empty( $settings['toolbar']['icon_type'] ) ? $settings['toolbar']['icon_type'] : 'default';
$custom_svg    = ! empty( $settings['toolbar']['custom_svg'] ) ? $settings['toolbar']['custom_svg'] : '';

// Responsive defaults.
$responsive_defaults = [
	'desktop' => [ 'offset_bottom' => 20, 'offset_side' => 20, 'icon_size' => 56 ],
	'tablet'  => [ 'offset_bottom' => 16, 'offset_side' => 16, 'icon_size' => 50 ],
	'mobile'  => [ 'offset_bottom' => 12, 'offset_side' => 12, 'icon_size' => 44 ],
];
$resp = ! empty( $settings['toolbar']['responsive'] ) ? $settings['toolbar']['responsive'] : [];
foreach ( [ 'desktop', 'tablet', 'mobile' ] as $dev ) {
	$resp[ $dev ] = wp_parse_args( $resp[ $dev ] ?? [], $responsive_defaults[ $dev ] );
}

// Backwards-compat: honour old flat keys if no responsive data saved yet.
if ( empty( $settings['toolbar']['responsive'] ) ) {
	$resp['desktop']['offset_bottom'] = ! empty( $settings['toolbar']['offset_bottom'] ) ? intval( $settings['toolbar']['offset_bottom'] ) : 20;
	$resp['desktop']['offset_side']   = ! empty( $settings['toolbar']['offset_side'] ) ? intval( $settings['toolbar']['offset_side'] ) : 20;
}



// Helper function to render selected SVG icon
$render_icon = function( $class = 'me-trigger-svg' ) use ( $icon_type, $custom_svg ) {
	if ( 'custom' === $icon_type && ! empty( $custom_svg ) ) {
		// Output raw sanitized SVG code
		echo $custom_svg;
	} else {
		switch ( $icon_type ) {
			case 'eye':
				?>
				<svg class="<?php echo esc_attr( $class ); ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
					<circle cx="12" cy="12" r="3"></circle>
				</svg>
				<?php
				break;
			case 'wheelchair':
				?>
				<svg class="<?php echo esc_attr( $class ); ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="4" r="2"></circle>
					<path d="M10 13l2-1.5 1 2.5 3-1.5M9.5 8.5L9 14l3.5 2.5 4-2.5"></path>
					<path d="M16 17a4 4 0 1 1-8 0"></path>
				</svg>
				<?php
				break;
			case 'gear':
				?>
				<svg class="<?php echo esc_attr( $class ); ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="12" r="3"></circle>
					<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
				</svg>
				<?php
				break;
			case 'default':
			default:
				?>
				<svg class="<?php echo esc_attr( $class ); ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="12" r="10"></circle>
					<path d="M12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm-4 6V8h8v6M10 20v-6h4v6"></path>
				</svg>
				<?php
				break;
		}
	}
};
?>
<!-- ME ACCESSIBILITY: RESPONSIVE TRIGGER CSS VARIABLES -->
<style id="me-trigger-responsive-vars">
:root {
	--me-trigger-size: <?php echo intval( $resp['desktop']['icon_size'] ); ?>px;
	--me-trigger-offset-bottom: <?php echo intval( $resp['desktop']['offset_bottom'] ); ?>px;
	--me-trigger-offset-side: <?php echo intval( $resp['desktop']['offset_side'] ); ?>px;
}
@media (max-width: 1024px) {
	:root {
		--me-trigger-size: <?php echo intval( $resp['tablet']['icon_size'] ); ?>px;
		--me-trigger-offset-bottom: <?php echo intval( $resp['tablet']['offset_bottom'] ); ?>px;
		--me-trigger-offset-side: <?php echo intval( $resp['tablet']['offset_side'] ); ?>px;
	}
}
@media (max-width: 768px) {
	:root {
		--me-trigger-size: <?php echo intval( $resp['mobile']['icon_size'] ); ?>px;
		--me-trigger-offset-bottom: <?php echo intval( $resp['mobile']['offset_bottom'] ); ?>px;
		--me-trigger-offset-side: <?php echo intval( $resp['mobile']['offset_side'] ); ?>px;
	}
}
</style>
<!-- ACCESSIBILITY TRIGGER BUTTON -->
<button class="me-toolbar-trigger me-position-<?php echo esc_attr( $position ); ?> me-trigger-<?php echo esc_attr( $trigger_type ); ?>" aria-label="<?php esc_attr_e( 'Barrierefreiheit-Menü öffnen', 'me-accessibility' ); ?>" aria-haspopup="dialog" aria-expanded="false" id="me-toolbar-trigger" data-i18n-attr="aria-label:toolbar.open">
	<?php $render_icon( 'me-trigger-svg' ); ?>
	<?php if ( 'badge' === $trigger_type ) : ?>
		<span class="me-trigger-text" data-i18n="toolbar.title"><?php esc_html_e( 'Barrierefreiheit', 'me-accessibility' ); ?></span>
	<?php endif; ?>
</button>

<!-- FLOATING WIDGET CONTAINER -->
<div class="me-toolbar-widget me-theme-<?php echo esc_attr( $theme ); ?> me-position-<?php echo esc_attr( $position ); ?>" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Barrierefreiheit-Optionen', 'me-accessibility' ); ?>" aria-hidden="true" id="me-toolbar-widget" style="--me-accent-color: <?php echo esc_attr( $accent_color ); ?>;" data-i18n-attr="aria-label:toolbar.title">
	<!-- WIDGET HEADER -->
	<header class="me-widget-header">
		<div class="me-widget-title-area">
			<?php $render_icon( 'me-header-svg' ); ?>
			<h3 data-i18n="toolbar.title"><?php esc_html_e( 'Barrierefreiheit', 'me-accessibility' ); ?></h3>
		</div>
		<button class="me-widget-close" aria-label="<?php esc_attr_e( 'Menü schließen', 'me-accessibility' ); ?>" id="me-widget-close-btn" data-i18n-attr="aria-label:toolbar.close">&times;</button>
	</header>

	<!-- WIDGET CONTENT PANEL -->
	<div class="me-widget-body">
		<!-- LANGUAGE SWITCHER SECTION -->
		<section class="me-widget-section me-lang-section">
			<h4 data-i18n="toolbar.language"><?php esc_html_e( 'Sprache', 'me-accessibility' ); ?></h4>
			
			<div class="me-lang-dropdown" id="me-lang-dropdown-wrapper">
				<button class="me-lang-dropdown-trigger" id="me-lang-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false">
					<?php
					$languages_list = [
						'de' => [ 'name' => 'Deutsch', 'flag' => 'de' ],
						'en' => [ 'name' => 'English', 'flag' => 'gb' ],
						'fr' => [ 'name' => 'Français', 'flag' => 'fr' ],
						'es' => [ 'name' => 'Español', 'flag' => 'es' ],
						'it' => [ 'name' => 'Italiano', 'flag' => 'it' ],
						'nl' => [ 'name' => 'Nederlands', 'flag' => 'nl' ],
						'pl' => [ 'name' => 'Polski', 'flag' => 'pl' ],
						'pt' => [ 'name' => 'Português', 'flag' => 'pt' ],
						'cs' => [ 'name' => 'Čeština', 'flag' => 'cz' ],
						'da' => [ 'name' => 'Dansk', 'flag' => 'dk' ],
						'sv' => [ 'name' => 'Svenska', 'flag' => 'se' ],
						'fi' => [ 'name' => 'Suomi', 'flag' => 'fi' ],
						'ro' => [ 'name' => 'Română', 'flag' => 'ro' ],
						'hu' => [ 'name' => 'Magyar', 'flag' => 'hu' ],
						'sk' => [ 'name' => 'Slovenčina', 'flag' => 'sk' ],
						'sl' => [ 'name' => 'Slovenščina', 'flag' => 'si' ],
						'hr' => [ 'name' => 'Hrvatski', 'flag' => 'hr' ],
						'el' => [ 'name' => 'Ελληνικά', 'flag' => 'gr' ],
						'bg' => [ 'name' => 'Български', 'flag' => 'bg' ],
						'vi' => [ 'name' => 'Tiếng Việt', 'flag' => 'vn' ]
					];
					$current_lang = \ME_Accessibility\Includes\Language::get_current_language();
					$selected_info = $languages_list[$current_lang] ?? $languages_list['en'];
					?>
					<img src="https://flagcdn.com/w40/<?php echo esc_attr( $selected_info['flag'] ); ?>.png" alt="" class="me-dropdown-selected-flag" id="me-selected-flag-img" />
					<span class="me-dropdown-selected-name" id="me-selected-lang-name"><?php echo esc_html( $selected_info['name'] ); ?></span>
					<span class="me-dropdown-arrow">▼</span>
				</button>
				<ul class="me-lang-dropdown-list" id="me-lang-dropdown-list" role="listbox" style="display: none;">
					<?php foreach ( $languages_list as $code => $info ) : 
						$active_class = ( $current_lang === $code ) ? ' me-active' : '';
						?>
						<li class="me-lang-dropdown-option<?php echo esc_attr( $active_class ); ?>" data-lang="<?php echo esc_attr( $code ); ?>" role="option" aria-selected="<?php echo ( $current_lang === $code ) ? 'true' : 'false'; ?>">
							<img src="https://flagcdn.com/w40/<?php echo esc_attr( $info['flag'] ); ?>.png" alt="" class="me-option-flag" />
							<span class="me-option-name"><?php echo esc_html( $info['name'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>

		<!-- GROUP 1: ACCESSIBILITY PROFILES -->
		<section class="me-widget-section">
			<h4 data-i18n="profiles.title"><?php esc_html_e( 'Barrierefreiheitsprofile', 'me-accessibility' ); ?></h4>
			<div class="me-profiles-grid">
				<button class="me-profile-btn" data-profile="adhd">
					<h5 data-i18n="profiles.adhd"><?php esc_html_e( 'ADHS-Freundlich', 'me-accessibility' ); ?></h5>
					<p data-i18n="profiles.adhdDesc"><?php esc_html_e( 'Reduziert Ablenkungen, fokusiert Inhalte.', 'me-accessibility' ); ?></p>
				</button>
				<button class="me-profile-btn" data-profile="vision">
					<h5 data-i18n="profiles.vision"><?php esc_html_e( 'Sehbehinderung', 'me-accessibility' ); ?></h5>
					<p data-i18n="profiles.visionDesc"><?php esc_html_e( 'Erhöht Schriftgröße und Kontraste.', 'me-accessibility' ); ?></p>
				</button>
				<button class="me-profile-btn" data-profile="keyboard">
					<h5 data-i18n="profiles.keyboard"><?php esc_html_e( 'Tastaturbedienung', 'me-accessibility' ); ?></h5>
					<p data-i18n="profiles.keyboardDesc"><?php esc_html_e( 'Hervorhebung interaktiver Elemente.', 'me-accessibility' ); ?></p>
				</button>
				<button class="me-profile-btn" data-profile="cognitive">
					<h5 data-i18n="profiles.cognitive"><?php esc_html_e( 'Kognitiv', 'me-accessibility' ); ?></h5>
					<p data-i18n="profiles.cognitiveDesc"><?php esc_html_e( 'Vereinfacht Lese- und Schrifthilfen.', 'me-accessibility' ); ?></p>
				</button>
			</div>
		</section>

		<!-- GROUP 2: CONTENT & FONTS -->
		<section class="me-widget-section">
			<h4 data-i18n="content.title"><?php esc_html_e( 'Schrift & Inhalt', 'me-accessibility' ); ?></h4>
			<div class="me-actions-grid">
				<button class="me-action-btn" data-action="increase-font" aria-label="<?php esc_attr_e( 'Schriftgröße vergrößern', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:content.increaseFont">
					<span>A+</span>
					<label data-i18n="content.increaseFont"><?php esc_html_e( 'Text vergrößern', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="decrease-font" aria-label="<?php esc_attr_e( 'Schriftgröße verkleinern', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:content.decreaseFont">
					<span>A-</span>
					<label data-i18n="content.decreaseFont"><?php esc_html_e( 'Text verkleinern', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="readable-font" aria-label="<?php esc_attr_e( 'Gut lesbare Schriftart', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:content.readableFont">
					<span class="dashicons dashicons-editor-paragraph"></span>
					<label data-i18n="content.readableFont"><?php esc_html_e( 'Lesbare Schrift', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="dyslexia-font" aria-label="<?php esc_attr_e( 'Schriftart für Legasthenie', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:content.dyslexiaFont">
					<span class="dashicons dashicons-editor-spellcheck"></span>
					<label data-i18n="content.dyslexiaFont"><?php esc_html_e( 'Legasthenie', 'me-accessibility' ); ?></label>
				</button>
			</div>
		</section>

		<!-- GROUP 3: CONTRASTS & COLORS -->
		<section class="me-widget-section">
			<h4 data-i18n="colors.title"><?php esc_html_e( 'Farbe & Kontraste', 'me-accessibility' ); ?></h4>
			<div class="me-actions-grid">
				<button class="me-action-btn" data-action="grayscale" aria-label="<?php esc_attr_e( 'Graustufenmodus', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:colors.grayscale">
					<span class="dashicons dashicons-art"></span>
					<label data-i18n="colors.grayscale"><?php esc_html_e( 'Graustufen', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="high-contrast" aria-label="<?php esc_attr_e( 'Hoher Kontrast', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:colors.highContrast">
					<span class="dashicons dashicons-visibility"></span>
					<label data-i18n="colors.highContrast"><?php esc_html_e( 'Hoher Kontrast', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="negative-contrast" aria-label="<?php esc_attr_e( 'Negativkontrast', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:colors.negativeContrast">
					<span class="dashicons dashicons-image-rotate"></span>
					<label data-i18n="colors.negativeContrast"><?php esc_html_e( 'Invertieren', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="dark-mode" aria-label="<?php esc_attr_e( 'Dunkelmodus', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:colors.darkMode">
					<span class="dashicons dashicons-lightbulb"></span>
					<label data-i18n="colors.darkMode"><?php esc_html_e( 'Dunkelmodus', 'me-accessibility' ); ?></label>
				</button>
			</div>
		</section>

		<!-- GROUP 4: READING AID -->
		<section class="me-widget-section">
			<h4 data-i18n="navigation.title"><?php esc_html_e( 'Navigations- & Lesehilfen', 'me-accessibility' ); ?></h4>
			<div class="me-actions-grid">
				<button class="me-action-btn" data-action="reading-guide" aria-label="<?php esc_attr_e( 'Lesehilfe einblenden', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:navigation.readingGuide">
					<span class="dashicons dashicons-editor-justify"></span>
					<label data-i18n="navigation.readingGuide"><?php esc_html_e( 'Lesebalken', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="reading-mask" aria-label="<?php esc_attr_e( 'Lesemaske einblenden', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:navigation.readingMask">
					<span class="dashicons dashicons-editor-table"></span>
					<label data-i18n="navigation.readingMask"><?php esc_html_e( 'Lesemaske', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="cursor-enlarge" aria-label="<?php esc_attr_e( 'Mauszeiger vergrößern', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:navigation.cursorEnlarge">
					<span class="dashicons dashicons-location-alt"></span>
					<label data-i18n="navigation.cursorEnlarge"><?php esc_html_e( 'Riesencursor', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="highlight-links" aria-label="<?php esc_attr_e( 'Links hervorheben', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:navigation.highlightLinks">
					<span class="dashicons dashicons-admin-links"></span>
					<label data-i18n="navigation.highlightLinks"><?php esc_html_e( 'Links markieren', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="highlight-headings" aria-label="<?php esc_attr_e( 'Überschriften hervorheben', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:navigation.highlightHeadings">
					<span class="dashicons dashicons-editor-heading"></span>
					<label data-i18n="navigation.highlightHeadings"><?php esc_html_e( 'Überschriften', 'me-accessibility' ); ?></label>
				</button>
				<button class="me-action-btn" data-action="pause-animations" aria-label="<?php esc_attr_e( 'Animationen stoppen', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:navigation.pauseAnimations">
					<span class="dashicons dashicons-controls-pause"></span>
					<label data-i18n="navigation.pauseAnimations"><?php esc_html_e( 'Animationen', 'me-accessibility' ); ?></label>
				</button>
			</div>
		</section>

		<!-- NATIVE BROWSER SPEECH PLAYER -->
		<section class="me-widget-section me-tts-player-section" style="display: none;" id="me-tts-player-section">
			<h4 data-i18n="tts.title"><?php esc_html_e( 'Sprachausgabe (Text to Speech)', 'me-accessibility' ); ?></h4>
			<div class="me-tts-player-box">
				<div class="me-tts-controls">
					<button id="me-tts-btn-play" class="me-tts-control-btn" aria-label="<?php esc_attr_e( 'Vorlesen abspielen', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:tts.play">
						<svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M8 5v14l11-7z"/></svg>
					</button>
					<button id="me-tts-btn-pause" class="me-tts-control-btn" aria-label="<?php esc_attr_e( 'Vorlesen pausieren', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:tts.pause" style="display: none;">
						<svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
					</button>
					<button id="me-tts-btn-stop" class="me-tts-control-btn" aria-label="<?php esc_attr_e( 'Vorlesen stoppen', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:tts.stop">
						<svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M6 6h12v12H6z"/></svg>
					</button>
				</div>
				<div class="me-tts-params">
					<select id="me-tts-voice-select" aria-label="<?php esc_attr_e( 'Stimme auswählen', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:tts.selectVoice"></select>
					<div class="me-tts-range-box">
						<label data-i18n="tts.speed"><?php esc_html_e( 'Tempo', 'me-accessibility' ); ?></label>
						<input type="range" id="me-tts-rate" min="0.5" max="2" step="0.1" value="1.0" aria-label="<?php esc_attr_e( 'Geschwindigkeit', 'me-accessibility' ); ?>" data-i18n-attr="aria-label:tts.speed" />
					</div>
				</div>
			</div>
		</section>
	</div>

	<!-- WIDGET FOOTER -->
	<footer class="me-widget-footer">
		<button class="me-reset-all-btn" id="me-reset-all-btn" data-i18n="toolbar.resetAll"><?php esc_html_e( 'Alle Einstellungen zurücksetzen', 'me-accessibility' ); ?></button>
		<?php if ( empty( $settings['hide_branding'] ) ) : ?>
			<div class="me-widget-branding">
				<span data-i18n="toolbar.poweredBy"><?php esc_html_e( 'Powered by', 'me-accessibility' ); ?></span>
				<a href="https://me-toolkit.com" target="_blank" rel="noopener noreferrer" class="me-branding-link">ME Accessibility</a>
			</div>
		<?php endif; ?>
	</footer>
</div>

<!-- READING LINE ELEMENT -->
<div class="me-reading-line" id="me-reading-line" style="display: none;"></div>

<!-- READING MASK ELEMENTS -->
<div class="me-reading-mask me-reading-mask-top" id="me-reading-mask-top" style="display: none;"></div>
<div class="me-reading-mask me-reading-mask-bottom" id="me-reading-mask-bottom" style="display: none;"></div>
