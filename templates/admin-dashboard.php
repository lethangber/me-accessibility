<?php
// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Einstellungen laden
$settings = get_option( 'me_accessibility_settings', [] );
$modules  = get_option( 'me_accessibility_modules', [] );

// Standardwerte setzen
$accent_color = ! empty( $settings['accent_color'] ) ? $settings['accent_color'] : '#2563eb';

// Letzte Scans laden
$recent_scans = \ME_Accessibility\Includes\Database::get_scans( 5 );
$latest_scan = ! empty( $recent_scans ) ? $recent_scans[0] : null;
$score = $latest_scan ? intval( $latest_scan['score'] ) : 100;
$issues_count = 0;
if ( $latest_scan ) {
	$scan_issues = \ME_Accessibility\Includes\Database::get_issues( $latest_scan['id'] );
	$issues_count = count( $scan_issues );
}

$has_white_label = \ME_Accessibility\Includes\License::has_feature( 'white_label' );
// (Systemstatus-Abschnitt entfernt)
?>
<div class="wrap me-accessibility-wrap" style="--me-accent-color: <?php echo esc_attr( $accent_color ); ?>;">
	<header class="me-admin-header">
		<div class="me-logo-area">
			<span class="dashicons dashicons-universal-access me-header-icon"></span>
			<div>
				<h1 class="me-title"><?php esc_html_e( 'ME Accessibility', 'me-accessibility' ); ?></h1>
				<p class="me-tagline"><?php esc_html_e( 'Barrierefreiheits-Toolkit für WCAG und BITV-Optimierung', 'me-accessibility' ); ?></p>
			</div>
		</div>
		
		<div class="me-agency-badge">
			<span>ME Accessibility &nbsp;&middot;&nbsp; <a href="https://me-toolkit.com" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline;text-underline-offset:3px;">Me Toolkit</a></span>
			<span class="me-badge-version">v<?php echo esc_html( ME_ACCESSIBILITY_VERSION ); ?> &nbsp;&middot;&nbsp; PHP 7.4+ &nbsp;&middot;&nbsp; WP 5.4+</span>
		</div>
	</header>

	<h2 class="nav-tab-wrapper me-tabs">
		<a href="#overview" class="nav-tab nav-tab-active" data-tab="overview"><?php esc_html_e( 'Übersicht', 'me-accessibility' ); ?></a>
		<a href="#modules" class="nav-tab" data-tab="modules"><?php esc_html_e( 'Module', 'me-accessibility' ); ?></a>
		<a href="#scanner" class="nav-tab" data-tab="scanner"><?php esc_html_e( 'WCAG-Scanner', 'me-accessibility' ); ?></a>
		<a href="#statement" class="nav-tab" data-tab="statement"><?php esc_html_e( 'Erklärung', 'me-accessibility' ); ?></a>
		<a href="#settings" class="nav-tab" data-tab="settings"><?php esc_html_e( 'Einstellungen', 'me-accessibility' ); ?></a>
		<a href="#im-export" class="nav-tab" data-tab="im-export"><?php esc_html_e( 'Import/Export', 'me-accessibility' ); ?></a>
	</h2>

	<!-- TAB CONTROLLER -->
	<form method="post" action="options.php" class="me-settings-form">
		<?php settings_fields( 'me_accessibility_settings_group' ); ?>
		
		<!-- TAB 1: ÜBERSICHT -->
		<div id="tab-overview" class="me-tab-content me-active-content">
			<div class="me-grid">
				<!-- CARD: SCORE -->
				<div class="me-card me-card-score">
					<h3><?php esc_html_e( 'Barrierefreiheits-Score', 'me-accessibility' ); ?></h3>
					<div class="me-score-circle" style="background: conic-gradient(var(--me-accent-color) <?php echo esc_attr( $score * 3.6 ); ?>deg, #e2e8f0 0deg);">
						<div class="me-score-inner">
							<span><?php echo esc_html( $score ); ?>/100</span>
						</div>
					</div>
					<p class="me-card-desc"><?php esc_html_e( 'Basierend auf dem letzten durchgeführten WCAG-Scan.', 'me-accessibility' ); ?></p>
				</div>

				<!-- CARD: STATUS -->
				<div class="me-card">
					<h3><?php esc_html_e( 'Letzter Scan-Status', 'me-accessibility' ); ?></h3>
					<div class="me-stat-number"><?php echo esc_html( $issues_count ); ?></div>
					<p class="me-stat-label"><?php esc_html_e( 'Gefundene WCAG-Probleme', 'me-accessibility' ); ?></p>
					<?php if ( $latest_scan ) : ?>
						<p class="me-card-desc">
							<?php esc_html_e( 'Scandatum:', 'me-accessibility' ); ?> <strong><?php echo esc_html( $latest_scan['scan_date'] ); ?></strong><br>
							<?php esc_html_e( 'Gescannte Seiten:', 'me-accessibility' ); ?> <strong><?php echo esc_html( $latest_scan['pages_scanned'] ); ?></strong>
						</p>
					<?php else : ?>
						<p class="me-card-desc"><?php esc_html_e( 'Noch kein Scan durchgeführt.', 'me-accessibility' ); ?></p>
					<?php endif; ?>
					<button type="button" class="button button-primary me-trigger-scan-btn"><?php esc_html_e( 'Jetzt scannen', 'me-accessibility' ); ?></button>
				</div>

				<!-- CARD: QUICK ACTIONS -->
				<div class="me-card">
					<h3><?php esc_html_e( 'Schnellstart-Funktionen', 'me-accessibility' ); ?></h3>
					<ul class="me-quick-actions">
						<li>
							<span class="dashicons dashicons-media-text"></span>
							<a href="#statement" class="me-tab-trigger" data-tab="statement"><?php esc_html_e( 'Barrierefreiheitserklärung generieren', 'me-accessibility' ); ?></a>
						</li>
						<li>
							<span class="dashicons dashicons-images-alt"></span>
							<a href="<?php echo esc_url( admin_url( 'upload.php?page=me-media-accessibility' ) ); ?>"><?php esc_html_e( 'Mediathek ALT-Texte bearbeiten', 'me-accessibility' ); ?></a>
						</li>
						<li>
							<span class="dashicons dashicons-privacy"></span>
							<a href="#gdpr" class="me-tab-trigger" data-tab="modules"><?php esc_html_e( 'Cookie & DSGVO Einstellungen', 'me-accessibility' ); ?></a>
						</li>
					</ul>
				</div>
				</div>
			</div>
		</div>

		<!-- TAB 2: MODULE -->
		<div id="tab-modules" class="me-tab-content">
			<div class="me-card">
				<h3><?php esc_html_e( 'Verfügbare Barrierefreiheits-Module', 'me-accessibility' ); ?></h3>
				<p class="me-section-desc"><?php esc_html_e( 'Aktivieren oder deaktivieren Sie die modular aufgebauten Features nach Bedarf.', 'me-accessibility' ); ?></p>
				
				<div class="me-modules-list">
					<?php
					$available_modules = [
						'toolbar'   => [
							'title' => __( 'Schwebende Accessibility-Toolbar', 'me-accessibility' ),
							'desc'  => __( 'Fügt ein schwebendes Widget auf Ihrer Website hinzu, über das Benutzer Schriftgrößen, Kontraste und Lesehilfen steuern können.', 'me-accessibility' ),
						],
						'tts'       => [
							'title' => __( 'Text-to-Speech (Vorlesefunktion)', 'me-accessibility' ),
							'desc'  => __( 'Erlaubt Benutzern das direkte Vorlesen von Seiteninhalten über die native SpeechSynthesis-Schnittstelle des Webbrowsers.', 'me-accessibility' ),
						],
						'scanner'   => [
							'title' => __( 'WCAG Konformitäts-Scanner', 'me-accessibility' ),
							'desc'  => __( 'Analysiert Ihre WordPress-Seiten auf WCAG 2.1 Konformität (fehlende Alternativtexte, Heading-Struktur, leere Links).', 'me-accessibility' ),
						],
						'helpers'   => [
							'title' => __( 'Barrierefreiheits-Hilfen (EAA / BITV)', 'me-accessibility' ),
							'desc'  => __( 'Ergänzt automatisch Tastatur-Fokusrahmen, Skip-to-Content Links und korrigiert gängige ARIA-Attribute im DOM.', 'me-accessibility' ),
						],
						'media'     => [
							'title' => __( 'Medien-Optimierung (Alt-Texte)', 'me-accessibility' ),
							'desc'  => __( 'Echtzeit-Prüfung unbeschrifteter Bilder sowie ein Bulk-Editor für Medien zur barrierefreien Beschriftung.', 'me-accessibility' ),
						],
						'content'   => [
							'title' => __( 'Editor Inhalts-Checker', 'me-accessibility' ),
							'desc'  => __( 'Zeigt Live-Tipps im Block-Editor & Page-Builder bezüglich Kontrasten, Hierarchien und Lesbarkeit.', 'me-accessibility' ),
						],
						'gdpr'      => [
							'title' => __( 'DSGVO Cookie-Einwilligung', 'me-accessibility' ),
							'desc'  => __( 'Ein barrierefreies Cookie-Einwilligungs-Modal mit Tastatursteuerung und Focus-Trapping.', 'me-accessibility' ),
						],
						'statement' => [
							'title' => __( 'Barrierefreiheitserklärung', 'me-accessibility' ),
							'desc'  => __( 'Erzeugt einen Generator und Platzhalter für Ihre rechtliche Barrierefreiheitserklärung (BITV 2.0 konform).', 'me-accessibility' ),
						],
						'autofix'   => [
							'title' => __( 'One Click Auto Fix', 'me-accessibility' ),
							'desc'  => __( 'Behebt automatisch häufige Barrierefreiheitsfehler auf Ihrer Website und Ihren Page-Buildern mit einem Klick.', 'me-accessibility' ),
						],
					];

					$pro_modules = [ 'media', 'gdpr', 'tts', 'autofix' ];
					$is_license_valid = \ME_Accessibility\Includes\License::is_pro_active();

					foreach ( $available_modules as $mod_key => $mod_data ) :
						$is_pro = in_array( $mod_key, $pro_modules, true );
						$is_locked = $is_pro && ! $is_license_valid;
						$is_checked = isset( $modules[ $mod_key ] ) ? (bool) $modules[ $mod_key ] : true;
						
						if ( $is_locked ) {
							$is_checked = false;
						}
						?>
						<div class="me-module-item <?php echo $is_locked ? 'me-module-locked' : ''; ?>" style="<?php echo $is_locked ? 'background: #fafafa; border-color: #e2e8f0; opacity: 0.85;' : ''; ?>">
							<div class="me-module-info">
								<h4>
									<?php echo esc_html( $mod_data['title'] ); ?>
									<?php if ( $is_pro ) : ?>
										<span class="me-pro-badge" style="background: <?php echo $is_license_valid ? '#10b981' : '#f59e0b'; ?>; color: #fff; padding: 2px 6px; font-size: 10px; border-radius: 4px; margin-left: 6px; vertical-align: middle; font-weight: bold; text-transform: uppercase;">
											<?php echo $is_license_valid ? 'Pro Active' : 'Pro'; ?>
										</span>
									<?php endif; ?>
								</h4>
								<p><?php echo esc_html( $mod_data['desc'] ); ?></p>
								<?php if ( $is_locked ) : ?>
									<p style="margin: 5px 0 0 0; font-size: 11px; color: #f59e0b; font-weight: 500;">
										<?php esc_html_e( 'Dieses Feature erfordert eine aktive ME Accessibility Pro Lizenz.', 'me-accessibility' ); ?>
									</p>
								<?php endif; ?>
							</div>
							<div class="me-module-toggle">
								<label class="me-switch" style="<?php echo $is_locked ? 'cursor: not-allowed; opacity: 0.6;' : ''; ?>">
									<input type="checkbox" name="me_accessibility_modules[<?php echo esc_attr( $mod_key ); ?>]" value="1" <?php checked( $is_checked, true ); ?> <?php echo $is_locked ? 'disabled' : ''; ?> />
									<span class="me-slider round"></span>
								</label>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<?php submit_button(); ?>
			</div>
		</div>


		<!-- TAB 4: SCANNER -->
		<div id="tab-scanner" class="me-tab-content">
			<div class="me-card">
				<h3><?php esc_html_e( 'WCAG 2.1 Compliance Scanner', 'me-accessibility' ); ?></h3>
				<p><?php esc_html_e( 'Dieser Scanner durchsucht Ihren Blog und analysiert das ausgegebene HTML auf Barrierefreiheitsprobleme.', 'me-accessibility' ); ?></p>
				
				<div class="me-scan-action-box">
					<button type="button" class="button button-primary button-large me-trigger-scan-btn"><?php esc_html_e( 'Vollständigen Website-Scan starten', 'me-accessibility' ); ?></button>
					<button type="button" class="button me-clear-history-btn"><?php esc_html_e( 'Verlauf löschen', 'me-accessibility' ); ?></button>
				</div>

				<div class="me-scanner-progress" style="display: none;">
					<div class="me-progress-bar-container">
						<div class="me-progress-bar" style="width: 0%;"></div>
					</div>
					<p class="me-progress-status"><?php esc_html_e( 'Bereite Scan vor...', 'me-accessibility' ); ?></p>
				</div>

				<div class="me-scanner-results-area">
					<h3><?php esc_html_e( 'Scan-Historie', 'me-accessibility' ); ?></h3>
					<?php if ( ! empty( $recent_scans ) ) : ?>
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Datum', 'me-accessibility' ); ?></th>
									<th><?php esc_html_e( 'Gescannte Seiten', 'me-accessibility' ); ?></th>
									<th><?php esc_html_e( 'Gesamt-Score', 'me-accessibility' ); ?></th>
									<th><?php esc_html_e( 'Status', 'me-accessibility' ); ?></th>
									<th><?php esc_html_e( 'Aktionen', 'me-accessibility' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $recent_scans as $scan ) : ?>
									<tr>
										<td><?php echo esc_html( $scan['scan_date'] ); ?></td>
										<td><?php echo esc_html( $scan['pages_scanned'] ); ?></td>
										<td>
											<strong class="me-score-text <?php echo intval( $scan['score'] ) > 85 ? 'me-good' : ( intval( $scan['score'] ) > 60 ? 'me-average' : 'me-bad' ); ?>">
												<?php echo esc_html( $scan['score'] ); ?>/100
											</strong>
										</td>
										<td><span class="me-badge me-badge-<?php echo esc_attr( $scan['status'] ); ?>"><?php echo esc_html( $scan['status'] ); ?></span></td>
										<td>
											<button type="button" class="button button-small button-primary me-view-details-btn" data-scan-id="<?php echo esc_attr( $scan['id'] ); ?>"><?php esc_html_e( 'Details', 'me-accessibility' ); ?></button>
											<a href="<?php echo esc_url( admin_url( 'admin.php?action=me_export_csv&scan_id=' . $scan['id'] ) ); ?>" class="button button-small"><?php esc_html_e( 'CSV-Export', 'me-accessibility' ); ?></a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p><?php esc_html_e( 'Es liegen noch keine Scanberichte vor.', 'me-accessibility' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<!-- CARD: ONE CLICK AUTO FIX -->
			<div class="me-card me-card-autofix" style="margin-top: 20px; position: relative; overflow: hidden;">
				<?php
				$is_license_valid = \ME_Accessibility\Includes\License::is_pro_active();
				$autofix_enabled = ! empty( $settings['autofix']['enabled'] );
				$autofix_log = get_option( 'me_accessibility_autofix_rollback_log', [] );
				$autofix_count = count( $autofix_log );
				?>
				<h3>
					<?php esc_html_e( 'One Click Auto Fix', 'me-accessibility' ); ?>
					<span class="me-pro-badge" style="background: <?php echo $is_license_valid ? '#10b981' : '#f59e0b'; ?>; color: #fff; padding: 2px 6px; font-size: 10px; border-radius: 4px; margin-left: 6px; vertical-align: middle; font-weight: bold; text-transform: uppercase;">
						<?php echo $is_license_valid ? 'Pro' : 'Locked'; ?>
					</span>
				</h3>
				
				<?php if ( ! $is_license_valid ) : ?>
					<p class="me-card-desc"><?php esc_html_e( 'Beheben Sie automatisch häufige Barrierefreiheitsfehler (fehlende ARIA-Labels, unbeschriftete Bilder, Fokus-Stile, Skip-Links) über alle gängigen Page-Builder hinweg mit einem Klick.', 'me-accessibility' ); ?></p>
					<div style="margin-top: 15px; background: #fffbeb; border: 1px solid #fef3c7; padding: 12px; border-radius: 8px; font-size: 12px; color: #b45309;">
						<span class="dashicons dashicons-lock" style="font-size: 16px; vertical-align: middle; margin-right: 4px;"></span>
						<?php esc_html_e( 'Dieses Feature erfordert eine aktive ME Accessibility Pro Lizenz.', 'me-accessibility' ); ?>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=me-accessibility-upgrade' ) ); ?>" class="button button-secondary" style="margin-top: 15px; width: 100%; text-align: center; justify-content: center;"><?php esc_html_e( 'Upgrade auf Pro', 'me-accessibility' ); ?></a>
				<?php else : ?>
					<p class="me-card-desc"><?php esc_html_e( 'Analysiert und korrigiert automatisch typische Barrierefreiheitsmängel auf Elementor, Gutenberg, Divi, Bricks und mehr.', 'me-accessibility' ); ?></p>
					
					<div class="me-autofix-status-box" style="margin: 15px 0; padding: 12px; border-radius: 8px; font-size: 13px; <?php echo $autofix_enabled ? 'background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;' : 'background: #f8fafc; border: 1px solid #e2e8f0; color: #475569;'; ?>">
						<?php if ( $autofix_enabled ) : ?>
							<span class="dashicons dashicons-yes" style="color: #10b981; vertical-align: middle; margin-right: 4px; font-weight: bold;"></span>
							<strong><?php esc_html_e( 'Auto-Fix Aktiviert', 'me-accessibility' ); ?></strong>
							<br><span style="font-size: 11px;"><?php printf( __( '%d Medien-Alternativtexte generiert + Echtzeit-ARIA/CSS Layer aktiv.', 'me-accessibility' ), $autofix_count ); ?></span>
						<?php else : ?>
							<span class="dashicons dashicons-warning" style="color: #64748b; vertical-align: middle; margin-right: 4px;"></span>
							<strong><?php esc_html_e( 'Auto-Fix Inaktiv', 'me-accessibility' ); ?></strong>
							<br><span style="font-size: 11px;"><?php esc_html_e( 'Keine automatischen Korrekturen aktiv.', 'me-accessibility' ); ?></span>
						<?php endif; ?>
					</div>

					<div class="me-autofix-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
						<?php if ( ! $autofix_enabled ) : ?>
							<button type="button" class="button button-primary me-trigger-autofix-btn" style="flex: 1; text-align: center; justify-content: center;"><?php esc_html_e( 'Auto-Fix starten', 'me-accessibility' ); ?></button>
						<?php else : ?>
							<button type="button" class="button button-secondary me-trigger-rollback-btn" style="flex: 1; text-align: center; justify-content: center; color: #b91c1c; border-color: #fca5a5;"><?php esc_html_e( 'Rollback (Rückgängig)', 'me-accessibility' ); ?></button>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="me-card" style="margin-top: 20px;">
				<h3>
					<?php esc_html_e( 'Automatische Scans planen', 'me-accessibility' ); ?>
					<?php if ( ! \ME_Accessibility\Includes\License::has_feature( 'advanced_scanner' ) ) : ?>
						<span class="me-badge me-badge-pro" style="background:#2563eb;color:#fff;padding:2px 6px;border-radius:4px;font-size:10px;margin-left:5px;vertical-align:middle;font-weight:600;">PRO</span>
					<?php endif; ?>
				</h3>
				<p><?php esc_html_e( 'Planen Sie wöchentliche oder monatliche automatische Scans Ihrer Website. Berichte werden im Verlauf gespeichert.', 'me-accessibility' ); ?></p>
				
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Scan-Intervall', 'me-accessibility' ); ?></th>
						<td>
							<select name="me_accessibility_settings[scanner][interval]" <?php disabled( \ME_Accessibility\Includes\License::has_feature( 'advanced_scanner' ), false ); ?>>
								<option value="none"><?php esc_html_e( 'Deaktiviert', 'me-accessibility' ); ?></option>
								<option value="weekly" <?php selected( $settings['scanner']['interval'] ?? '', 'weekly' ); ?>><?php esc_html_e( 'Wöchentlich', 'me-accessibility' ); ?></option>
								<option value="monthly" <?php selected( $settings['scanner']['interval'] ?? '', 'monthly' ); ?>><?php esc_html_e( 'Monatlich', 'me-accessibility' ); ?></option>
							</select>
							<?php if ( ! \ME_Accessibility\Includes\License::has_feature( 'advanced_scanner' ) ) : ?>
								<p style="color:#d32f2f;font-weight:600;margin-top:5px;"><?php esc_html_e( 'Dieses Feature erfordert eine aktive ME Accessibility Pro Lizenz.', 'me-accessibility' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				</table>
				
				<?php submit_button( __( 'Einstellungen speichern', 'me-accessibility' ) ); ?>
			</div>
		</div>

		<!-- TAB 5: ERWEITERTE EINSTELLUNGEN -->
		<?php
		$languages_list = [
			'de' => 'Deutsch', 'en' => 'English', 'fr' => 'Français', 'es' => 'Español', 'it' => 'Italiano',
			'nl' => 'Nederlands', 'pl' => 'Polski', 'pt' => 'Português', 'cs' => 'Čeština', 'da' => 'Dansk',
			'sv' => 'Svenska', 'fi' => 'Suomi', 'ro' => 'Română', 'hu' => 'Magyar', 'sk' => 'Slovenčina',
			'sl' => 'Slovenščina', 'hr' => 'Hrvatski', 'el' => 'Ελληνικά', 'bg' => 'Български', 'vi' => 'Tiếng Việt'
		];
		$resp_defaults = [
			'desktop' => [ 'offset_bottom' => 20, 'offset_side' => 20, 'icon_size' => 56 ],
			'tablet'  => [ 'offset_bottom' => 16, 'offset_side' => 16, 'icon_size' => 50 ],
			'mobile'  => [ 'offset_bottom' => 12, 'offset_side' => 12, 'icon_size' => 44 ],
		];
		$resp_saved = $settings['toolbar']['responsive'] ?? [];
		foreach ( [ 'desktop', 'tablet', 'mobile' ] as $dev ) {
			$resp_saved[ $dev ] = wp_parse_args( $resp_saved[ $dev ] ?? [], $resp_defaults[ $dev ] );
		}
		?>
		<div id="tab-settings" class="me-tab-content">
			<div class="me-settings-search-wrapper">
				<span class="dashicons dashicons-search"></span>
				<input type="text" id="me-settings-search" placeholder="<?php esc_attr_e( 'Einstellungen durchsuchen...', 'me-accessibility' ); ?>" />
			</div>

			<div class="me-settings-layout">
				<!-- Sub-Navigation Sidebar -->
				<aside class="me-settings-sidebar">
					<nav class="me-settings-nav">
						<a href="#general" class="me-settings-nav-item active" data-subtab="general">
							<span class="dashicons dashicons-admin-generic"></span>
							<?php esc_html_e( 'Allgemein', 'me-accessibility' ); ?>
						</a>
						<a href="#design" class="me-settings-nav-item" data-subtab="design">
							<span class="dashicons dashicons-art"></span>
							<?php esc_html_e( 'Widget-Design', 'me-accessibility' ); ?>
						</a>
						<a href="#advanced" class="me-settings-nav-item" data-subtab="advanced">
							<span class="dashicons dashicons-admin-tools"></span>
							<?php esc_html_e( 'Erweitert', 'me-accessibility' ); ?>
						</a>
					</nav>
					
					<div class="me-settings-sidebar-actions">
						<?php submit_button( __( 'Einstellungen speichern', 'me-accessibility' ), 'primary me-settings-save-btn', 'submit', false, [ 'id' => 'me-settings-save-main-btn' ] ); ?>
						<button type="button" class="button button-link me-settings-reset-btn" id="me-settings-reset-btn">
							<span class="dashicons dashicons-reload"></span>
							<?php esc_html_e( 'Standardwerte wiederherstellen', 'me-accessibility' ); ?>
						</button>
						<div class="me-settings-autosave-indicator" style="display:none;">
							<span class="me-dot-green"></span>
							<span class="me-autosave-text"><?php esc_html_e( 'Speichern...', 'me-accessibility' ); ?></span>
						</div>
					</div>
				</aside>

				<!-- Main Configuration Area -->
				<div class="me-settings-content">
					<!-- PANEL 1: GENERAL -->
					<div class="me-settings-panel active" id="me-subtab-general">
						<h3 class="me-settings-panel-title"><?php esc_html_e( 'Allgemein', 'me-accessibility' ); ?></h3>
						
						<!-- Card: Language & Access -->
						<div class="me-settings-card" data-search-keys="sprachmodus sprache erzwingen tts sprachausgabe rtl tastaturlayout">
							<div class="me-settings-card-header">
								<h4><?php esc_html_e( 'Sprache &amp; Barrierefreiheit (EAA &amp; BITV 2.0)', 'me-accessibility' ); ?></h4>
							</div>
							<div class="me-settings-card-body">
								<!-- Default lang -->
								<div class="me-settings-field">
									<label for="me-lang-default"><?php esc_html_e( 'Standard-Sprachmodus', 'me-accessibility' ); ?></label>
									<div class="me-settings-control">
										<select id="me-lang-default" name="me_accessibility_settings[language][default_lang]">
											<option value="auto" <?php selected( $settings['language']['default_lang'] ?? 'auto', 'auto' ); ?>><?php esc_html_e( 'Automatische Erkennung (Standard)', 'me-accessibility' ); ?></option>
											<?php
											foreach ( $languages_list as $code => $name ) {
												echo '<option value="' . esc_attr( $code ) . '" ' . selected( $settings['language']['default_lang'] ?? '', $code, false ) . '>' . esc_html( $name ) . '</option>';
											}
											?>
										</select>
										<p class="description"><?php esc_html_e( 'Im Automodus wird die Sprache aus dem HTML lang-Attribut der aktuellen Seite gelesen (unterstützt WPML, Polylang und TranslatePress).', 'me-accessibility' ); ?></p>
									</div>
								</div>

								<!-- Force lang -->
								<div class="me-settings-field">
									<label for="me-lang-force"><?php esc_html_e( 'Sprache erzwingen', 'me-accessibility' ); ?></label>
									<div class="me-settings-control">
										<select id="me-lang-force" name="me_accessibility_settings[language][force_lang]">
											<option value="" <?php selected( $settings['language']['force_lang'] ?? '', '' ); ?>><?php esc_html_e( 'Nicht erzwingen (Erkennung aktiv)', 'me-accessibility' ); ?></option>
											<?php
											foreach ( $languages_list as $code => $name ) {
												echo '<option value="' . esc_attr( $code ) . '" ' . selected( $settings['language']['force_lang'] ?? '', $code, false ) . '>' . esc_html( $name ) . '</option>';
											}
											?>
										</select>
										<p class="description"><?php esc_html_e( 'Überschreibt alle automatischen Erkennungen und erzwingt das Widget in dieser ausgewählten Sprache.', 'me-accessibility' ); ?></p>
									</div>
								</div>

								<!-- Automatic TTS detection -->
								<div class="me-settings-field me-settings-field-toggle">
									<div class="me-settings-field-label-wrap">
										<span class="me-field-label"><?php esc_html_e( 'Automatische Sprachausgabe (TTS) Spracherkennung', 'me-accessibility' ); ?></span>
										<span class="description"><?php esc_html_e( 'Das Vorlese-System (TTS) erkennt die Seitensprache automatisch und lädt die passende Landessprache für das Audiosystem.', 'me-accessibility' ); ?></span>
									</div>
									<div class="me-settings-control">
										<label class="me-switch">
											<input type="checkbox" name="me_accessibility_settings[language][tts_multilang]" value="1" <?php checked( $settings['language']['tts_multilang'] ?? 1, 1 ); ?> />
											<span class="me-slider round"></span>
										</label>
									</div>
								</div>

								<!-- RTL Support -->
								<div class="me-settings-field me-settings-field-toggle">
									<div class="me-settings-field-label-wrap">
										<span class="me-field-label"><?php esc_html_e( 'RTL (Rechts nach Links) Unterstützung', 'me-accessibility' ); ?></span>
										<span class="description"><?php esc_html_e( 'Aktiviert das automatische RTL-Layout-Widget, wenn eine rechts-nach-links geschriebene Sprache erkannt wird.', 'me-accessibility' ); ?></span>
									</div>
									<div class="me-settings-control">
										<label class="me-switch">
											<input type="checkbox" name="me_accessibility_settings[language][rtl_support]" value="1" <?php checked( $settings['language']['rtl_support'] ?? 1, 1 ); ?> />
											<span class="me-slider round"></span>
										</label>
									</div>
								</div>
							</div>
						</div>

						<!-- Card: Special Exclusions -->
						<div class="me-settings-card" data-search-keys="ausschluss mobilgeräte logged in angemeldete benutzer rolle dashboard">
							<div class="me-settings-card-header">
								<h4><?php esc_html_e( 'Spezielle Ausschlusskriterien & Performance', 'me-accessibility' ); ?></h4>
							</div>
							<div class="me-settings-card-body">
								<!-- Allowed user role -->
								<div class="me-settings-field">
									<label for="me-allowed-role"><?php esc_html_e( 'Erlaubte Benutzerrolle für Dashboard', 'me-accessibility' ); ?></label>
									<div class="me-settings-control">
										<select id="me-allowed-role" name="me_accessibility_settings[allowed_role]">
											<option value="manage_options" <?php selected( $settings['allowed_role'] ?? '', 'manage_options' ); ?>><?php esc_html_e( 'Administrator (manage_options)', 'me-accessibility' ); ?></option>
											<option value="edit_pages" <?php selected( $settings['allowed_role'] ?? '', 'edit_pages' ); ?>><?php esc_html_e( 'Redakteur (edit_pages)', 'me-accessibility' ); ?></option>
										</select>
									</div>
								</div>

								<!-- Disable for logged in users -->
								<div class="me-settings-field me-settings-field-toggle">
									<div class="me-settings-field-label-wrap">
										<span class="me-field-label"><?php esc_html_e( 'Für angemeldete Benutzer deaktivieren', 'me-accessibility' ); ?></span>
										<span class="description"><?php esc_html_e( 'Die Toolbar für Administratoren und Redakteure verbergen.', 'me-accessibility' ); ?></span>
									</div>
									<div class="me-settings-control">
										<label class="me-switch">
											<input type="checkbox" name="me_accessibility_settings[disable_logged_in]" value="1" <?php checked( $settings['disable_logged_in'] ?? 0, 1 ); ?> />
											<span class="me-slider round"></span>
										</label>
									</div>
								</div>

								<!-- Disable on mobile -->
								<div class="me-settings-field me-settings-field-toggle">
									<div class="me-settings-field-label-wrap">
										<span class="me-field-label"><?php esc_html_e( 'Auf Mobilgeräten deaktivieren', 'me-accessibility' ); ?></span>
										<span class="description"><?php esc_html_e( 'Toolbar und Overlays auf Handys / Tablets nicht laden.', 'me-accessibility' ); ?></span>
									</div>
									<div class="me-settings-control">
										<label class="me-switch">
											<input type="checkbox" name="me_accessibility_settings[disable_mobile]" value="1" <?php checked( $settings['disable_mobile'] ?? 0, 1 ); ?> />
											<span class="me-slider round"></span>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- PANEL 2: WIDGET DESIGN -->
					<div class="me-settings-panel" id="me-subtab-design">
						<div class="me-design-layout-split">
							<!-- Left Column: Controls -->
							<div class="me-design-controls">
								<h3 class="me-settings-panel-title"><?php esc_html_e( 'Widget-Design', 'me-accessibility' ); ?></h3>

								<!-- Card: Branding -->
								<div class="me-settings-card" data-search-keys="branding farbe akzentfarbe white label pro">
									<div class="me-settings-card-header">
										<h4><?php esc_html_e( 'Branding', 'me-accessibility' ); ?></h4>
									</div>
									<div class="me-settings-card-body">
										<div class="me-settings-field">
											<label><?php esc_html_e( 'Akzentfarbe der Website-Vorderseite', 'me-accessibility' ); ?></label>
											<div class="me-settings-control">
												<input type="color" name="me_accessibility_settings[accent_color]" id="me-setting-accent-color" value="<?php echo esc_attr( $accent_color ); ?>" />
												<p class="description"><?php esc_html_e( 'Definiert die primäre CSS-Farbe für die Toolbar und barrierefreie Overlays.', 'me-accessibility' ); ?></p>
											</div>
										</div>

										<div class="me-settings-field me-settings-field-toggle">
											<div class="me-settings-field-label-wrap">
												<span class="me-field-label">
													<?php esc_html_e( 'Branding im Frontend ausblenden', 'me-accessibility' ); ?>
													<?php if ( ! $has_white_label ) : ?>
														<span class="me-pro-badge-pill">PRO</span>
													<?php endif; ?>
												</span>
												<span class="description"><?php esc_html_e( 'ME Accessibility-Branding im Frontend-Widget und der Toolbar ausblenden.', 'me-accessibility' ); ?></span>
											</div>
											<div class="me-settings-control">
												<label class="me-switch <?php echo ! $has_white_label ? 'me-switch-disabled' : ''; ?>">
													<input type="checkbox" name="me_accessibility_settings[hide_branding]" value="1" <?php checked( $settings['hide_branding'] ?? 0, 1 ); ?> <?php disabled( $has_white_label, false ); ?> />
													<span class="me-slider round"></span>
												</label>
												<?php if ( ! $has_white_label ) : ?>
													<p class="description me-pro-locked-desc" style="color:#d32f2f;font-weight:600;"><?php esc_html_e( 'Dieses Feature erfordert eine active ME Accessibility Pro Lizenz.', 'me-accessibility' ); ?></p>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>

								<!-- Card: Trigger Button -->
								<div class="me-settings-card" data-search-keys="trigger button icon type type eye gear wheelchair custom svg">
									<div class="me-settings-card-header">
										<h4><?php esc_html_e( 'Trigger-Button', 'me-accessibility' ); ?></h4>
									</div>
									<div class="me-settings-card-body">
										<div class="me-settings-field">
											<label for="me-toolbar-icon-type"><?php esc_html_e( 'Trigger-Icon', 'me-accessibility' ); ?></label>
											<div class="me-settings-control">
												<select name="me_accessibility_settings[toolbar][icon_type]" id="me-toolbar-icon-type">
													<option value="default" <?php selected( $settings['toolbar']['icon_type'] ?? 'default', 'default' ); ?>><?php esc_html_e( 'Standard (Barrierefreiheit)', 'me-accessibility' ); ?></option>
													<option value="eye" <?php selected( $settings['toolbar']['icon_type'] ?? 'default', 'eye' ); ?>><?php esc_html_e( 'Auge (Sehbehinderung)', 'me-accessibility' ); ?></option>
													<option value="wheelchair" <?php selected( $settings['toolbar']['icon_type'] ?? 'default', 'wheelchair' ); ?>><?php esc_html_e( 'Rollstuhl', 'me-accessibility' ); ?></option>
													<option value="gear" <?php selected( $settings['toolbar']['icon_type'] ?? 'default', 'gear' ); ?>><?php esc_html_e( 'Zahnrad (Einstellungen)', 'me-accessibility' ); ?></option>
													<option value="custom" <?php selected( $settings['toolbar']['icon_type'] ?? 'default', 'custom' ); ?>><?php esc_html_e( 'Benutzerdefiniertes SVG', 'me-accessibility' ); ?></option>
												</select>
											</div>
										</div>

										<div class="me-settings-field" id="me-custom-svg-row" style="<?php echo ( $settings['toolbar']['icon_type'] ?? 'default' ) === 'custom' ? '' : 'display: none;'; ?>">
											<label><?php esc_html_e( 'Benutzerdefiniertes SVG', 'me-accessibility' ); ?></label>
											<div class="me-settings-control">
												<textarea name="me_accessibility_settings[toolbar][custom_svg]" class="large-text" rows="4" placeholder="<?php echo esc_attr( '<svg ...>...</svg>' ); ?>"><?php echo esc_textarea( $settings['toolbar']['custom_svg'] ?? '' ); ?></textarea>
												<p class="description"><?php esc_html_e( 'Fügen Sie Ihren eigenen SVG-Code für das Trigger-Icon ein. Muss ein gültiges SVG-Element sein.', 'me-accessibility' ); ?></p>
											</div>
										</div>

										<div class="me-settings-field">
											<label for="me-trigger-style"><?php esc_html_e( 'Trigger-Stil', 'me-accessibility' ); ?></label>
											<div class="me-settings-control">
												<select id="me-trigger-style" name="me_accessibility_settings[toolbar][trigger_type]">
													<option value="button" <?php selected( $settings['toolbar']['trigger_type'] ?? '', 'button' ); ?>><?php esc_html_e( 'Nur Symbolschaltfläche', 'me-accessibility' ); ?></option>
													<option value="badge" <?php selected( $settings['toolbar']['trigger_type'] ?? '', 'badge' ); ?>><?php esc_html_e( 'Symbol + Barrierefreiheit-Text', 'me-accessibility' ); ?></option>
												</select>
											</div>
										</div>
									</div>
								</div>

								<!-- Card: Toolbar Layout -->
								<div class="me-settings-card" data-search-keys="toolbar layout color scheme theme theme light dark accessible blue">
									<div class="me-settings-card-header">
										<h4><?php esc_html_e( 'Toolbar-Layout', 'me-accessibility' ); ?></h4>
									</div>
									<div class="me-settings-card-body">
										<div class="me-settings-field">
											<label for="me-toolbar-theme"><?php esc_html_e( 'Farbschema der Toolbar', 'me-accessibility' ); ?></label>
											<div class="me-settings-control">
												<select id="me-toolbar-theme" name="me_accessibility_settings[toolbar][theme]">
													<option value="light" <?php selected( $settings['toolbar']['theme'] ?? '', 'light' ); ?>><?php esc_html_e( 'Hell (Standard)', 'me-accessibility' ); ?></option>
													<option value="dark" <?php selected( $settings['toolbar']['theme'] ?? '', 'dark' ); ?>><?php esc_html_e( 'Dunkel (Modern)', 'me-accessibility' ); ?></option>
													<option value="accessible-blue" <?php selected( $settings['toolbar']['theme'] ?? '', 'accessible-blue' ); ?>><?php esc_html_e( 'Barrierefreies Blau', 'me-accessibility' ); ?></option>
												</select>
											</div>
										</div>
									</div>
								</div>

								<!-- Card: Device Positioning -->
								<div class="me-settings-card" data-search-keys="positionierung größe offset bottom side offset icon size position">
									<div class="me-settings-card-header">
										<h4><?php esc_html_e( 'Geräte-Positionierung', 'me-accessibility' ); ?></h4>
									</div>
									<div class="me-settings-card-body">
										<div class="me-settings-field">
											<label for="me-toolbar-position"><?php esc_html_e( 'Widget-Position', 'me-accessibility' ); ?></label>
											<div class="me-settings-control">
												<select id="me-toolbar-position" name="me_accessibility_settings[toolbar][position]">
													<option value="right-bottom" <?php selected( $settings['toolbar']['position'] ?? '', 'right-bottom' ); ?>><?php esc_html_e( 'Rechts unten', 'me-accessibility' ); ?></option>
													<option value="left-bottom" <?php selected( $settings['toolbar']['position'] ?? '', 'left-bottom' ); ?>><?php esc_html_e( 'Links unten', 'me-accessibility' ); ?></option>
													<option value="right-center" <?php selected( $settings['toolbar']['position'] ?? '', 'right-center' ); ?>><?php esc_html_e( 'Rechts mitte', 'me-accessibility' ); ?></option>
													<option value="left-center" <?php selected( $settings['toolbar']['position'] ?? '', 'left-center' ); ?>><?php esc_html_e( 'Links mitte', 'me-accessibility' ); ?></option>
												</select>
											</div>
										</div>

										<div class="me-device-slider-tabs">
											<div class="me-device-tabs">
												<button type="button" class="me-device-tab me-device-tab-active" data-device="desktop">
													<span class="dashicons dashicons-desktop"></span>
													<?php esc_html_e( 'Desktop', 'me-accessibility' ); ?>
												</button>
												<button type="button" class="me-device-tab" data-device="tablet">
													<span class="dashicons dashicons-tablet"></span>
													<?php esc_html_e( 'Tablet', 'me-accessibility' ); ?>
												</button>
												<button type="button" class="me-device-tab" data-device="mobile">
													<span class="dashicons dashicons-smartphone"></span>
													<?php esc_html_e( 'Mobil', 'me-accessibility' ); ?>
												</button>
											</div>

											<?php foreach ( [ 'desktop', 'tablet', 'mobile' ] as $dev ) :
												$r = $resp_saved[ $dev ];
											?>
											<div class="me-device-panel" id="me-panel-<?php echo esc_attr( $dev ); ?>" style="<?php echo $dev === 'desktop' ? '' : 'display:none;'; ?>">
												<!-- Icon Size -->
												<div class="me-slider-row">
													<label>
														<?php esc_html_e( 'Icon-Größe (px)', 'me-accessibility' ); ?>
														<output class="me-slider-output" id="me-out-<?php echo esc_attr( $dev ); ?>-icon_size"><?php echo esc_html( $r['icon_size'] ); ?></output>
													</label>
													<input type="range"
														class="me-range-slider"
														id="me-slider-<?php echo esc_attr( $dev ); ?>-icon_size"
														name="me_accessibility_settings[toolbar][responsive][<?php echo esc_attr( $dev ); ?>][icon_size]"
														min="24" max="96" step="2"
														value="<?php echo esc_attr( $r['icon_size'] ); ?>"
														data-device="<?php echo esc_attr( $dev ); ?>"
														data-prop="icon_size"
														data-output="me-out-<?php echo esc_attr( $dev ); ?>-icon_size"
													/>
												</div>
												<!-- Bottom Offset -->
												<div class="me-slider-row">
													<label>
														<?php esc_html_e( 'Abstand unten (px)', 'me-accessibility' ); ?>
														<output class="me-slider-output" id="me-out-<?php echo esc_attr( $dev ); ?>-offset_bottom"><?php echo esc_html( $r['offset_bottom'] ); ?></output>
													</label>
													<input type="range"
														class="me-range-slider"
														id="me-slider-<?php echo esc_attr( $dev ); ?>-offset_bottom"
														name="me_accessibility_settings[toolbar][responsive][<?php echo esc_attr( $dev ); ?>][offset_bottom]"
														min="0" max="200" step="1"
														value="<?php echo esc_attr( $r['offset_bottom'] ); ?>"
														data-device="<?php echo esc_attr( $dev ); ?>"
														data-prop="offset_bottom"
														data-output="me-out-<?php echo esc_attr( $dev ); ?>-offset_bottom"
													/>
												</div>
												<!-- Side Offset -->
												<div class="me-slider-row">
													<label>
														<?php esc_html_e( 'Abstand seitlich (px)', 'me-accessibility' ); ?>
														<output class="me-slider-output" id="me-out-<?php echo esc_attr( $dev ); ?>-offset_side"><?php echo esc_html( $r['offset_side'] ); ?></output>
													</label>
													<input type="range"
														class="me-range-slider"
														id="me-slider-<?php echo esc_attr( $dev ); ?>-offset_side"
														name="me_accessibility_settings[toolbar][responsive][<?php echo esc_attr( $dev ); ?>][offset_side]"
														min="0" max="200" step="1"
														value="<?php echo esc_attr( $r['offset_side'] ); ?>"
														data-device="<?php echo esc_attr( $dev ); ?>"
														data-prop="offset_side"
														data-output="me-out-<?php echo esc_attr( $dev ); ?>-offset_side"
													/>
												</div>
											</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							</div>

							<!-- Right Column: Live Preview Frame -->
							<div class="me-design-preview">
								<div class="me-preview-sticky-wrap">
									<h4><?php esc_html_e( 'Live-Vorschau', 'me-accessibility' ); ?></h4>
									<p class="me-section-desc"><?php esc_html_e( 'Zeigt, wie der Trigger-Button auf Ihrer Website erscheint.', 'me-accessibility' ); ?></p>
									
									<div class="me-preview-device-frame" id="me-preview-device-frame">
										<div class="me-preview-browser-bar">
											<span class="me-preview-dot"></span>
											<span class="me-preview-dot"></span>
											<span class="me-preview-dot"></span>
											<span class="me-preview-url"><?php echo esc_html( home_url( '/' ) ); ?></span>
										</div>
										<div class="me-preview-screen" id="me-preview-screen">
											<div class="me-preview-content-mock">
												<div class="me-preview-line me-preview-line-h"></div>
												<div class="me-preview-line me-preview-line-m"></div>
												<div class="me-preview-line me-preview-line-s"></div>
												<div class="me-preview-line me-preview-line-m"></div>
												<div class="me-preview-line me-preview-line-s"></div>
											</div>
											<div class="me-preview-trigger-btn" id="me-preview-trigger-btn" style="background: <?php echo esc_attr( $settings['accent_color'] ?? '#2563eb' ); ?>;">
												<svg id="me-preview-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
													<circle cx="12" cy="12" r="10"></circle>
													<path d="M12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm-4 6V8h8v6M10 20v-6h4v6"></path>
												</svg>
												<span id="me-preview-trigger-text" style="display:none; margin-left: 6px; font-weight: 600; font-size: 13px; color: #ffffff;"><?php esc_html_e( 'Barrierefreiheit', 'me-accessibility' ); ?></span>
											</div>
										</div>
									</div>
									
									<div class="me-preview-device-switcher">
										<button type="button" class="me-preview-size-btn me-preview-size-active" data-preview-size="desktop"><?php esc_html_e( 'Desktop', 'me-accessibility' ); ?></button>
										<button type="button" class="me-preview-size-btn" data-preview-size="tablet"><?php esc_html_e( 'Tablet', 'me-accessibility' ); ?></button>
										<button type="button" class="me-preview-size-btn" data-preview-size="mobile"><?php esc_html_e( 'Mobil', 'me-accessibility' ); ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- PANEL 3: ADVANCED -->
					<div class="me-settings-panel" id="me-subtab-advanced">
						<h3 class="me-settings-panel-title"><?php esc_html_e( 'Erweitert', 'me-accessibility' ); ?></h3>

						<!-- Card: Custom Code -->
						<div class="me-settings-card" data-search-keys="custom css custom js javascript pro code anpassungen styling">
							<div class="me-settings-card-header">
								<h4><?php esc_html_e( 'Benutzerdefiniertes CSS &amp; JavaScript', 'me-accessibility' ); ?></h4>
							</div>
							<div class="me-settings-card-body">
								<!-- Custom CSS -->
								<div class="me-settings-field">
									<label>
										<?php esc_html_e( 'Benutzerdefiniertes CSS', 'me-accessibility' ); ?>
										<?php if ( ! $has_white_label ) : ?>
											<span class="me-pro-badge-pill">PRO</span>
										<?php endif; ?>
									</label>
									<div class="me-settings-control">
										<textarea name="me_accessibility_settings[custom_css]" class="large-text code" rows="5" <?php disabled( $has_white_label, false ); ?> placeholder="<?php echo ! $has_white_label ? esc_attr__( 'Dieses Feature erfordert eine active ME Accessibility Pro Lizenz.', 'me-accessibility' ) : ''; ?>"><?php echo esc_textarea( $settings['custom_css'] ?? '' ); ?></textarea>
										<p class="description"><?php esc_html_e( 'Wird direkt im Frontend-Dokumentkopf geladen. Bestens geeignet zur Feinjustierung von Overlays.', 'me-accessibility' ); ?></p>
									</div>
								</div>

								<!-- Custom JS -->
								<div class="me-settings-field">
									<label>
										<?php esc_html_e( 'Benutzerdefiniertes JavaScript', 'me-accessibility' ); ?>
										<?php if ( ! $has_white_label ) : ?>
											<span class="me-pro-badge-pill">PRO</span>
										<?php endif; ?>
									</label>
									<div class="me-settings-control">
										<textarea name="me_accessibility_settings[custom_js]" class="large-text code" rows="5" <?php disabled( $has_white_label, false ); ?> placeholder="<?php echo ! $has_white_label ? esc_attr__( 'Dieses Feature erfordert eine active ME Accessibility Pro Lizenz.', 'me-accessibility' ) : ''; ?>"><?php echo esc_textarea( $settings['custom_js'] ?? '' ); ?></textarea>
										<p class="description"><?php esc_html_e( 'Wird asynchron am Ende der Seite geladen.', 'me-accessibility' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<!-- Card: Cache management -->
						<div class="me-settings-card" data-search-keys="cache verwalten leeren performance geschwindigkeit">
							<div class="me-settings-card-header">
								<h4><?php esc_html_e( 'Cache-Verwaltung', 'me-accessibility' ); ?></h4>
							</div>
							<div class="me-settings-card-body">
								<div class="me-settings-field">
									<label><?php esc_html_e( 'Cache leeren', 'me-accessibility' ); ?></label>
									<div class="me-settings-control">
										<button type="button" class="button button-secondary me-clear-cache-btn" id="me-clear-cache-btn">
											<span class="dashicons dashicons-trash" style="margin-top:4px;"></span>
											<?php esc_html_e( 'Cache leeren', 'me-accessibility' ); ?>
										</button>
										<p class="description"><?php esc_html_e( 'Leert den transienten Lizenzcache und den WordPress Objekt-Cache.', 'me-accessibility' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<!-- Card: Developer info -->
						<div class="me-settings-card" data-search-keys="developer entwickler debug modus systemstatus support">
							<div class="me-settings-card-header">
								<h4><?php esc_html_e( 'Debug-Modus und Entwickler-Tools', 'me-accessibility' ); ?></h4>
							</div>
							<div class="me-settings-card-body">
								<div class="me-settings-field">
									<label><?php esc_html_e( 'Zukünftige Entwickler-Optionen', 'me-accessibility' ); ?></label>
									<div class="me-settings-control">
										<div class="me-dev-info-box">
											<p><strong><?php esc_html_e( 'Systemstatus:', 'me-accessibility' ); ?></strong></p>
											<ul>
												<?php 
												$sys = \ME_Accessibility\Admin\Settings::get_system_status();
												foreach($sys as $k => $v) {
													echo '<li><code>' . esc_html($k) . ': ' . esc_html($v) . '</code></li>';
												}
												?>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
<!-- TAB 5: BARRIEREFREIHEITSERKLÄRUNG GENERATOR -->
		<?php
		// Default content sections
		$default_sections = [
			[ 'id' => 'introduction', 'name' => __( 'Einleitung', 'me-accessibility' ), 'enabled' => true ],
			[ 'id' => 'conformance_status', 'name' => __( 'Stand der Vereinbarkeit', 'me-accessibility' ), 'enabled' => true ],
			[ 'id' => 'feedback', 'name' => __( 'Feedback', 'me-accessibility' ), 'enabled' => true ],
			[ 'id' => 'compatibility', 'name' => __( 'Kompatibilität', 'me-accessibility' ), 'enabled' => true ],
			[ 'id' => 'technical_information', 'name' => __( 'Technische Informationen', 'me-accessibility' ), 'enabled' => true ],
			[ 'id' => 'limitations', 'name' => __( 'Bekannte Einschränkungen', 'me-accessibility' ), 'enabled' => true ],
			[ 'id' => 'assessment_approach', 'name' => __( 'Prüfung der Barrierefreiheit', 'me-accessibility' ), 'enabled' => true ],
			[ 'id' => 'contact_information', 'name' => __( 'Kontaktangaben', 'me-accessibility' ), 'enabled' => true ],
		];
		$saved_sections = ! empty( $settings['statement']['sections'] ) ? $settings['statement']['sections'] : $default_sections;
		$sections_json = wp_json_encode( $saved_sections );
		
		$last_updated = ! empty( $settings['statement']['last_updated'] ) ? $settings['statement']['last_updated'] : date_i18n( 'M d, Y' );
		$version = ! empty( $settings['statement']['version'] ) ? $settings['statement']['version'] : '1.0';
		$next_review = date_i18n( 'M d, Y', strtotime( $last_updated . ' +1 year' ) );
		?>
		<div id="tab-statement" class="me-tab-content">
			<!-- Hidden fields for settings -->
			<input type="hidden" name="me_accessibility_settings[statement][sections_json]" id="me-statement-sections-input" value="<?php echo esc_attr( $sections_json ); ?>" />
			<input type="hidden" name="me_accessibility_settings[statement][last_updated]" id="me-statement-last-updated-input" value="<?php echo esc_attr( $last_updated ); ?>" />
			<input type="hidden" name="me_accessibility_settings[statement][version]" id="me-statement-version-input" value="<?php echo esc_attr( $version ); ?>" />

			<div class="me-statement-grid-container-new">
				<!-- LEFT SIDEBAR: STATEMENT BUILDER -->
				<div class="me-statement-sidebar-card-new">
					<div class="me-statement-sidebar-header">
						<h3 class="me-statement-sidebar-title"><?php esc_html_e( 'Erstellungs-Assistent', 'me-accessibility' ); ?></h3>
						<p class="me-statement-sidebar-desc"><?php esc_html_e( 'Erstellen und verwalten Sie Ihre Barrierefreiheitserklärung', 'me-accessibility' ); ?></p>
					</div>

					<div class="me-statement-sidebar-group-new">
						<div class="me-statement-toggle-row-new">
							<span class="me-statement-toggle-label-new"><?php esc_html_e( 'Erklärung aktivieren', 'me-accessibility' ); ?></span>
							<label class="me-switch-new">
								<input type="checkbox" name="me_accessibility_settings[statement][show]" value="1" <?php checked( $settings['statement']['show'] ?? 0, 1 ); ?> />
								<span class="me-slider-new round"></span>
							</label>
						</div>
					</div>

					<div class="me-statement-sidebar-group-new">
						<label class="me-statement-field-title-new" for="me-statement-lang"><?php esc_html_e( 'Sprache auswählen', 'me-accessibility' ); ?></label>
						<select id="me-statement-lang" class="me-statement-select-new">
							<option value="bg"><?php esc_html_e( 'Български', 'me-accessibility' ); ?></option>
							<option value="cs"><?php esc_html_e( 'Čeština', 'me-accessibility' ); ?></option>
							<option value="da"><?php esc_html_e( 'Dansk', 'me-accessibility' ); ?></option>
							<option value="de"><?php esc_html_e( 'Deutsch', 'me-accessibility' ); ?></option>
							<option value="el"><?php esc_html_e( 'Ελληνικά', 'me-accessibility' ); ?></option>
							<option value="en" selected><?php esc_html_e( 'English', 'me-accessibility' ); ?></option>
							<option value="es"><?php esc_html_e( 'Español', 'me-accessibility' ); ?></option>
							<option value="fi"><?php esc_html_e( 'Suomi', 'me-accessibility' ); ?></option>
							<option value="fr"><?php esc_html_e( 'Français', 'me-accessibility' ); ?></option>
							<option value="hr"><?php esc_html_e( 'Hrvatski', 'me-accessibility' ); ?></option>
							<option value="hu"><?php esc_html_e( 'Magyar', 'me-accessibility' ); ?></option>
							<option value="it"><?php esc_html_e( 'Italiano', 'me-accessibility' ); ?></option>
							<option value="nl"><?php esc_html_e( 'Nederlands', 'me-accessibility' ); ?></option>
							<option value="pl"><?php esc_html_e( 'Polski', 'me-accessibility' ); ?></option>
							<option value="pt"><?php esc_html_e( 'Português', 'me-accessibility' ); ?></option>
							<option value="ro"><?php esc_html_e( 'Română', 'me-accessibility' ); ?></option>
							<option value="sk"><?php esc_html_e( 'Slovenčina', 'me-accessibility' ); ?></option>
							<option value="sl"><?php esc_html_e( 'Slovenščina', 'me-accessibility' ); ?></option>
							<option value="sv"><?php esc_html_e( 'Svenska', 'me-accessibility' ); ?></option>
							<option value="vi"><?php esc_html_e( 'Tiếng Việt', 'me-accessibility' ); ?></option>
						</select>
					</div>

					<div class="me-statement-sidebar-group-new">
						<label class="me-statement-field-title-new" for="me-statement-company-name"><?php esc_html_e( 'Firmenname', 'me-accessibility' ); ?></label>
						<input type="text" id="me-statement-company-name" name="me_accessibility_settings[statement][company]" class="me-statement-input-new" value="<?php echo esc_attr( $settings['statement']['company'] ?? '' ); ?>" placeholder="Company Inc." />
					</div>

					<div class="me-statement-sidebar-group-new">
						<label class="me-statement-field-title-new" for="me-statement-email"><?php esc_html_e( 'Geschäftliche E-Mail-Adresse', 'me-accessibility' ); ?></label>
						<input type="email" id="me-statement-email" name="me_accessibility_settings[statement][email]" class="me-statement-input-new" value="<?php echo esc_attr( $settings['statement']['email'] ?? '' ); ?>" placeholder="info@company.com" />
					</div>

					<div class="me-statement-sidebar-group-new">
						<label class="me-statement-field-title-new" for="me-statement-url"><?php esc_html_e( 'Website', 'me-accessibility' ); ?></label>
						<input type="url" id="me-statement-url" name="me_accessibility_settings[statement][url]" class="me-statement-input-new" value="<?php echo esc_attr( $settings['statement']['url'] ?? '' ); ?>" placeholder="www.company.com" />
					</div>

					<!-- Hidden consent check to satisfy legacy JS check if needed -->
					<input type="checkbox" id="me-statement-consent-chk" checked style="display:none;" />

					<!-- STATEMENT INFORMATION BOX -->
					<div class="me-statement-info-box-new">
						<h4 class="me-statement-info-title-new"><?php esc_html_e( 'Erklärungsinformationen', 'me-accessibility' ); ?></h4>
						<ul class="me-statement-info-list-new">
							<li>
								<span class="me-info-label-new"><?php esc_html_e( 'Zuletzt aktualisiert', 'me-accessibility' ); ?></span>
								<span class="me-info-value-new" id="me-info-val-updated"><?php echo esc_html( $last_updated ); ?></span>
							</li>
							<li>
								<span class="me-info-label-new"><?php esc_html_e( 'Version', 'me-accessibility' ); ?></span>
								<span class="me-info-value-new" id="me-info-val-version"><?php echo esc_html( $version ); ?></span>
							</li>
							<li>
								<span class="me-info-label-new"><?php esc_html_e( 'Nächste Überprüfung', 'me-accessibility' ); ?></span>
								<span class="me-info-value-new" id="me-info-val-next"><?php echo esc_html( $next_review ); ?></span>
							</li>
						</ul>
						<a href="#" class="me-view-history-link-new" id="me-view-history-btn">
							<span class="dashicons dashicons-backup"></span>
							<span><?php esc_html_e( 'Erklärungsverlauf anzeigen', 'me-accessibility' ); ?></span>
							<span class="dashicons dashicons-arrow-right-alt2 me-history-chevron"></span>
						</a>
					</div>

					<!-- QUICK ACTIONS PANEL -->
					<div class="me-quick-actions-box-new">
						<h4 class="me-quick-actions-title-new"><?php esc_html_e( 'Schnellaktionen', 'me-accessibility' ); ?></h4>
						<div class="me-quick-actions-grid-new">
							<button type="button" class="me-quick-action-btn-new me-regenerate-primary-btn" id="me-action-regenerate">
								<span class="dashicons dashicons-admin-customizer"></span>
								<span><?php esc_html_e( 'Neu generieren', 'me-accessibility' ); ?></span>
							</button>
							<button type="button" class="me-quick-action-btn-new" id="me-action-pdf">
								<span class="dashicons dashicons-pdf"></span>
								<span><?php esc_html_e( 'PDF exportieren', 'me-accessibility' ); ?></span>
							</button>
						</div>
					</div>

					<!-- DISCLAIMER BOX -->
					<div class="me-statement-sidebar-note" style="margin-top: 15px; border-left-color: #f59e0b;">
						<span class="dashicons dashicons-warning" style="color:#f59e0b; font-size: 16px; vertical-align: middle; margin-right: 4px;"></span>
						<?php esc_html_e( 'Haftungsausschluss: Die generierte Erklärung stellt keine Rechtsberatung dar. Für die Richtigkeit wird keine Haftung übernommen.', 'me-accessibility' ); ?>
					</div>
				</div>

				<!-- RIGHT PANEL: WORKSPACE -->
				<div class="me-statement-workspace-card-new">
					<!-- WORKSPACE TABS -->
					<div class="me-statement-workspace-header-new">
						<div class="me-statement-workspace-tabs-new">
							<a href="#" class="me-workspace-tab-btn-new active" data-workspace-tab="editor">
								<span class="dashicons dashicons-edit"></span>
								<?php esc_html_e( 'Visueller Editor', 'me-accessibility' ); ?>
							</a>
							<a href="#" class="me-workspace-tab-btn-new" data-workspace-tab="template">
								<span class="dashicons dashicons-layout"></span>
								<?php esc_html_e( 'Vorlage', 'me-accessibility' ); ?>
							</a>
						</div>
						<div class="me-statement-workspace-indicators-new">
							<span class="me-autosave-indicator-new"><span class="me-dot-green"></span> <?php esc_html_e( 'Auto-Speichern: EIN', 'me-accessibility' ); ?></span>
							<span class="me-last-saved-new"><?php esc_html_e( 'Zuletzt gespeichert', 'me-accessibility' ); ?>: <span id="me-last-saved-time"><?php echo date_i18n( 'H:i' ); ?></span></span>
							<span class="dashicons dashicons-update me-sync-icon-new" id="me-sync-indicator-btn" style="cursor:pointer;"></span>
						</div>
					</div>

					<!-- TAB VIEW: VISUAL EDITOR -->
					<div class="me-workspace-tab-content-new active" id="me-workspace-view-editor">
						<div class="me-workspace-editor-layout-new" style="grid-template-columns: 1fr;">
							<!-- WYSIWYG EDITOR -->
							<div class="me-workspace-editor-area-new" style="border-right: none; padding: 24px;">
								<div class="me-statement-editor-container-new">
									<?php
									$statement_text = $settings['statement']['text'] ?? '';
									wp_editor( $statement_text, 'me_statement_editor', [
										'textarea_name' => 'me_accessibility_settings[statement][text]',
										'media_buttons' => false,
										'textarea_rows' => 28,
										'tinymce'       => [
											'toolbar1' => 'formatselect | bold italic underline strikethrough | blockquote bullist numlist | link unlink | removeformat'
										],
										'quicktags'     => false
									] );
									?>
								</div>
							</div>
						</div>
					</div>

					<!-- TAB VIEW: TEMPLATE -->
					<div class="me-workspace-tab-content-new" id="me-workspace-view-template" style="display:none;">
						<div class="me-templates-grid-new">
							<div class="me-template-card-new active" data-template-style="standard">
								<div class="me-template-preview-mock">
									<div class="me-mock-header"></div>
									<div class="me-mock-body">
										<div class="me-mock-line"></div>
										<div class="me-mock-line"></div>
										<div class="me-mock-line"></div>
									</div>
								</div>
								<h5 class="me-template-card-title"><?php esc_html_e( 'Standard WCAG 2.1 (Empfohlen)', 'me-accessibility' ); ?></h5>
								<p class="me-template-card-desc"><?php esc_html_e( 'Vollständige, rechtssichere Erklärung basierend auf WCAG 2.1 AA und BITV 2.0 Richtlinien.', 'me-accessibility' ); ?></p>
							</div>
							<div class="me-template-card-new" data-template-style="simple">
								<div class="me-template-preview-mock simple">
									<div class="me-mock-header"></div>
									<div class="me-mock-body">
										<div class="me-mock-line"></div>
										<div class="me-mock-line"></div>
									</div>
								</div>
								<h5 class="me-template-card-title"><?php esc_html_e( 'Kompakte Fassung', 'me-accessibility' ); ?></h5>
								<p class="me-template-card-desc"><?php esc_html_e( 'Kurze und übersichtliche Zusammenfassung der Barrierefreiheit auf einer Seite.', 'me-accessibility' ); ?></p>
							</div>
						</div>
					</div>

					<!-- WORKSPACE FOOTER -->
					<div class="me-statement-workspace-footer-new">
						<div class="me-editor-stats-new">
							<span class="me-stat-words-new"><?php esc_html_e( 'Wortanzahl', 'me-accessibility' ); ?>: <span id="me-editor-word-count">0</span></span>
							<span class="me-stat-time-new"><?php esc_html_e( 'Lesezeit', 'me-accessibility' ); ?>: <span id="me-editor-reading-time">0</span> <?php esc_html_e( 'Min.', 'me-accessibility' ); ?></span>
						</div>
						<div class="me-workspace-actions-new">
							<?php submit_button( __( 'Änderungen speichern', 'me-accessibility' ), 'primary me-workspace-save-btn', 'submit', false, [ 'id' => 'me-workspace-save-btn' ] ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

	<!-- TAB 6: IMPORT & EXPORT -->
	<div id="tab-im-export" class="me-tab-content">
		<div class="me-card">
			<h3><?php esc_html_e( 'Einstellungen exportieren', 'me-accessibility' ); ?></h3>
			<p><?php esc_html_e( 'Laden Sie die Konfiguration dieser Website als JSON-Datei herunter, um sie bequem auf einer anderen Agentur-Website zu importieren.', 'me-accessibility' ); ?></p>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=me-accessibility&me_action=export_settings' ), 'me_export_settings_nonce', 'nonce' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Exportdatei herunterladen', 'me-accessibility' ); ?></a>
		</div>

		<div class="me-card">
			<h3><?php esc_html_e( 'Einstellungen importieren', 'me-accessibility' ); ?></h3>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'me_import_settings_nonce', 'me_import_nonce' ); ?>
				<p>
					<input type="file" name="import_file" accept=".json" required />
				</p>
				<p>
					<button type="submit" name="me_import_settings" class="button button-secondary"><?php esc_html_e( 'Konfiguration einspielen', 'me-accessibility' ); ?></button>
				</p>
			</form>
		</div>
	</div>

	<!-- MODAL FOR SCAN DETAILS -->
	<div id="me-scan-details-modal" class="me-admin-modal-overlay" style="display: none;">
		<div class="me-admin-modal-container">
			<header class="me-admin-modal-header">
				<h3><?php esc_html_e( 'Detaillierter WCAG-Scanbericht', 'me-accessibility' ); ?></h3>
				<button type="button" class="me-admin-modal-close">&times;</button>
			</header>
			<div class="me-admin-modal-content">
				<div class="me-scan-details-summary">
					<div class="me-summary-badge me-badge-critical"><?php esc_html_e( 'Kritisch', 'me-accessibility' ); ?>: <span id="me-critical-count">0</span></div>
					<div class="me-summary-badge me-badge-warning"><?php esc_html_e( 'Warnung', 'me-accessibility' ); ?>: <span id="me-warning-count">0</span></div>
					<div class="me-summary-badge me-badge-notice"><?php esc_html_e( 'Hinweis', 'me-accessibility' ); ?>: <span id="me-notice-count">0</span></div>
				</div>
				<div class="me-scan-details-filter-bar">
					<button type="button" class="button button-small me-filter-btn active" data-filter="all"><?php esc_html_e( 'Alle', 'me-accessibility' ); ?></button>
					<button type="button" class="button button-small me-filter-btn" data-filter="critical"><?php esc_html_e( 'Kritisch', 'me-accessibility' ); ?></button>
					<button type="button" class="button button-small me-filter-btn" data-filter="warning"><?php esc_html_e( 'Warnung', 'me-accessibility' ); ?></button>
					<button type="button" class="button button-small me-filter-btn" data-filter="notice"><?php esc_html_e( 'Hinweis', 'me-accessibility' ); ?></button>
				</div>
				<div class="me-scan-issues-wrapper">
					<div id="me-scan-issues-loading" style="display: none; padding: 30px; text-align: center;">
						<span class="spinner is-active" style="float: none; margin: 0 auto 10px;"></span>
						<p><?php esc_html_e( 'Ergebnisse werden geladen...', 'me-accessibility' ); ?></p>
					</div>
					<table class="wp-list-table widefat fixed striped me-scan-issues-table">
						<thead>
							<tr>
								<th style="width: 120px;"><?php esc_html_e( 'Schweregrad', 'me-accessibility' ); ?></th>
								<th style="width: 150px;"><?php esc_html_e( 'Fehlertyp', 'me-accessibility' ); ?></th>
								<th><?php esc_html_e( 'Beschreibung / CSS-Selektor & Code', 'me-accessibility' ); ?></th>
							</tr>
						</thead>
						<tbody id="me-scan-issues-body">
							<!-- Wird dynamisch befüllt -->
						</tbody>
					</table>
				</div>
			</div>
			<footer class="me-admin-modal-footer" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
				<div class="me-modal-autofix-actions-area">
					<?php if ( $is_license_valid ) : ?>
						<?php if ( ! $autofix_enabled ) : ?>
							<button type="button" class="button button-primary me-trigger-autofix-btn" style="display: inline-flex; align-items: center; gap: 6px;"><span class="dashicons dashicons-admin-tools" style="font-size:16px; width:16px; height:16px; margin: 0;"></span><?php esc_html_e( 'Auto-Fix starten', 'me-accessibility' ); ?></button>
						<?php else : ?>
							<button type="button" class="button button-secondary me-trigger-rollback-btn" style="display: inline-flex; align-items: center; gap: 6px; color: #b91c1c; border-color: #fca5a5;"><span class="dashicons dashicons-undo" style="font-size:16px; width:16px; height:16px; margin: 0;"></span><?php esc_html_e( 'Rollback (Rückgängig)', 'me-accessibility' ); ?></button>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				<button type="button" class="button button-secondary me-admin-modal-close-btn"><?php esc_html_e( 'Schließen', 'me-accessibility' ); ?></button>
			</footer>
		</div>
	</div>

	<!-- MODAL FOR STATEMENT HISTORY -->
	<div id="me-statement-history-modal" class="me-admin-modal-overlay" style="display: none;">
		<div class="me-admin-modal-container" style="max-width: 650px;">
			<header class="me-admin-modal-header">
				<h3><?php esc_html_e( 'Erklärungsverlauf', 'me-accessibility' ); ?></h3>
				<button type="button" class="me-admin-modal-close">&times;</button>
			</header>
			<div class="me-admin-modal-content">
				<div id="me-statement-history-loading" style="display: none; padding: 30px; text-align: center;">
					<span class="spinner is-active" style="float: none; margin: 0 auto 10px;"></span>
					<p><?php esc_html_e( 'Historie wird geladen...', 'me-accessibility' ); ?></p>
				</div>
				<div id="me-statement-history-list-wrapper">
					<table class="wp-list-table widefat fixed striped" style="border:none; box-shadow:none; margin:0;">
						<thead>
							<tr>
								<th style="width: 80px;"><?php esc_html_e( 'Version', 'me-accessibility' ); ?></th>
								<th><?php esc_html_e( 'Datum', 'me-accessibility' ); ?></th>
								<th style="width: 150px; text-align: right;"><?php esc_html_e( 'Aktionen', 'me-accessibility' ); ?></th>
							</tr>
						</thead>
						<tbody id="me-statement-history-tbody">
							<!-- Wird dynamisch befüllt -->
						</tbody>
					</table>
				</div>
			</div>
			<footer class="me-admin-modal-footer">
				<button type="button" class="button button-secondary me-admin-modal-close-btn"><?php esc_html_e( 'Schließen', 'me-accessibility' ); ?></button>
			</footer>
		</div>
	</div>
</div>
