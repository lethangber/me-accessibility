<?php
// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name = get_bloginfo( 'name' );
?>
<div class="me-modal-overlay" id="me-cookie-overlay" role="dialog" aria-modal="true" aria-labelledby="me-cookie-title" aria-describedby="me-cookie-desc" style="--me-accent-color: <?php echo esc_attr( $accent_color ); ?>;">
	<div class="me-modal-box">
		<header class="me-modal-header">
			<h2 id="me-cookie-title"><?php esc_html_e( 'Ihre Cookie- & Privatsphäre-Einstellungen', 'me-accessibility' ); ?></h2>
		</header>
		<div class="me-modal-body">
			<p id="me-cookie-desc">
				<?php 
				printf( 
					/* translators: %s: Name der Website */
					esc_html__( 'Wir verwenden Cookies auf %s, um die Website-Nutzererfahrung zu verbessern, Statistiken zu messen und barrierefreie Funktionen wie Sprachausgaben bereitzustellen. Sie können der Nutzung aller Cookies zustimmen oder diese individuell konfigurieren.', 'me-accessibility' ),
					'<strong>' . esc_html( $site_name ) . '</strong>'
				); 
				?>
			</p>
			
			<div class="me-cookie-options">
				<label class="me-cookie-checkbox-label">
					<input type="checkbox" id="me-cookie-necessary" checked disabled aria-disabled="true" />
					<span><strong><?php esc_html_e( 'Notwendige Cookies', 'me-accessibility' ); ?></strong> (<?php esc_html_e( 'Immer aktiv, für grundlegende Kernfunktionen wie Barrierefreiheitsparameter.', 'me-accessibility' ); ?>)</span>
				</label>
				
				<label class="me-cookie-checkbox-label">
					<input type="checkbox" id="me-cookie-analytics" checked />
					<span><strong><?php esc_html_e( 'Statistiken & Analysen', 'me-accessibility' ); ?></strong> (<?php esc_html_e( 'Hilft uns, Probleme auf der Website schneller zu beheben.', 'me-accessibility' ); ?>)</span>
				</label>
			</div>
		</div>
		<footer class="me-modal-footer">
			<button type="button" class="me-btn me-btn-secondary" id="me-cookie-btn-selected"><?php esc_html_e( 'Auswahl speichern', 'me-accessibility' ); ?></button>
			<button type="button" class="me-btn me-btn-primary" id="me-cookie-btn-accept"><?php esc_html_e( 'Alle akzeptieren', 'me-accessibility' ); ?></button>
		</footer>
	</div>
</div>
