<?php
// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = get_option( 'me_accessibility_settings', [] );
$accent_color = ! empty( $settings['accent_color'] ) ? $settings['accent_color'] : '#2563eb';
?>

<div class="wrap me-licensing-container" style="--me-accent-color: <?php echo esc_attr( $accent_color ); ?>;">
	<!-- HERO SECTION -->
	<div class="me-licensing-hero" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: #ffffff; padding: 40px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.2);">
		<div class="me-licensing-hero-content" style="max-width: 60%;">
			<span class="me-licensing-badge" style="background: rgba(255,255,255,0.2); color: #ffffff; padding: 4px 10px; font-size: 11px; border-radius: 9999px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e( 'Upgrade Verfügbar', 'me-accessibility' ); ?></span>
			<h2 style="font-size: 28px; font-weight: 800; margin: 12px 0 8px 0; color: #fff; line-height: 1.2;"><?php esc_html_e( 'Bringen Sie Ihre Website auf Barrierefreiheit Pro', 'me-accessibility' ); ?></h2>
			<p style="font-size: 15px; opacity: 0.95; line-height: 1.5; margin: 0;"><?php esc_html_e( 'Schalten Sie den automatisierten WCAG-Scanner, One-Click Auto Fix, erweiterte Text-to-Speech Vorlesefunktion, DSGVO-Cookie-Einwilligung und Alt-Text Bulk Editor sofort frei.', 'me-accessibility' ); ?></p>
		</div>
		<div class="me-licensing-hero-art" style="text-align: right; display: flex; align-items: center; gap: 10px;">
			<span class="dashicons dashicons-lock" style="font-size: 80px; width: 80px; height: 80px; opacity: 0.3; color: #fff;"></span>
		</div>
	</div>

	<!-- MAIN LAYOUT GRID -->
	<div class="me-licensing-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
		<!-- FEATURE COMPARISON CARD -->
		<div class="me-licensing-card" style="background: #ffffff; padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
			<h3 style="margin-top: 0; font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 20px;"><?php esc_html_e( 'Funktionsvergleich: Free vs. Pro', 'me-accessibility' ); ?></h3>
			
			<table class="wp-list-table widefat fixed striped" style="width: 100%; border-collapse: collapse; border: none; box-shadow: none;">
				<thead>
					<tr style="border-bottom: 2px solid #e2e8f0;">
						<th style="font-weight: 700; padding: 12px; text-align: left;"><?php esc_html_e( 'Funktion', 'me-accessibility' ); ?></th>
						<th style="font-weight: 700; padding: 12px; text-align: center; width: 100px;"><?php esc_html_e( 'Free', 'me-accessibility' ); ?></th>
						<th style="font-weight: 700; padding: 12px; text-align: center; width: 100px; color: #2563eb;"><?php esc_html_e( 'Pro', 'me-accessibility' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'Schwebende Accessibility-Toolbar', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #10b981;"><span class="dashicons dashicons-yes"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'Tastatur-Lesehilfen & Fokus (EAA & BITV)', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #10b981;"><span class="dashicons dashicons-yes"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'Barrierefreiheitserklärung Generator', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #10b981;"><span class="dashicons dashicons-yes"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'Manueller WCAG-Scanner', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #10b981;"><span class="dashicons dashicons-yes"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'Text-to-Speech Vorlesefunktion (TTS)', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #ef4444;"><span class="dashicons dashicons-no-alt"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'One Click Auto-Fix (Fehlerkorrektur)', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #ef4444;"><span class="dashicons dashicons-no-alt"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'DSGVO Cookie-Einwilligung', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #ef4444;"><span class="dashicons dashicons-no-alt"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'Automatische Scans planen (Cron)', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #ef4444;"><span class="dashicons dashicons-no-alt"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'Bulk Alt-Text Editor & Echtzeitprüfung', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #ef4444;"><span class="dashicons dashicons-no-alt"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'White Labeling (Branding ausblenden)', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #ef4444;"><span class="dashicons dashicons-no-alt"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
						<td style="padding: 12px; font-weight: 500;"><?php esc_html_e( 'Benutzerdefiniertes CSS & Javascript', 'me-accessibility' ); ?></td>
						<td style="text-align: center; color: #ef4444;"><span class="dashicons dashicons-no-alt"></span></td>
						<td style="text-align: center; color: #10b981; font-weight: bold;"><span class="dashicons dashicons-yes"></span></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- SIDEBAR CALL-TO-ACTION -->
		<div class="me-licensing-sidebar-card" style="background: #f8fafc; padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
			<div>
				<h3 style="margin-top: 0; font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 16px;"><?php esc_html_e( 'Unlock Premium', 'me-accessibility' ); ?></h3>
				<p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 20px;">
					<?php esc_html_e( 'Erhalten Sie Zugriff auf alle Pro-Features, professionellen Support und automatische Updates, um Ihre Website vollständig barrierefrei zu machen.', 'me-accessibility' ); ?>
				</p>
				
				<div style="background: #fff; border: 1px solid #cbd5e1; padding: 16px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
					<span style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;"><?php esc_html_e( 'Startet ab', 'me-accessibility' ); ?></span>
					<div style="font-size: 32px; font-weight: 800; color: #0f172a; margin: 4px 0;"><?php esc_html_e( '€49', 'me-accessibility' ); ?></div>
					<span style="font-size: 11px; color: #64748b;"><?php esc_html_e( 'Einmalige Zahlung / Lebenslange Lizenz', 'me-accessibility' ); ?></span>
				</div>
			</div>

			<div>
				<a href="https://me-toolkit.com/" target="_blank" rel="noopener" style="display: block; background: #2563eb; color: #fff; text-align: center; padding: 12px; border-radius: 8px; font-weight: 700; text-decoration: none; margin-bottom: 12px; transition: background 0.15s ease;">
					<?php esc_html_e( 'Pro-Lizenz erwerben →', 'me-accessibility' ); ?>
				</a>
				
				<div style="text-align: center; font-size: 12px; color: #64748b;">
					<span class="dashicons dashicons-shield-alt" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span> 30-Tage Geld-zurück-Garantie
				</div>
			</div>
		</div>
	</div>
</div>
