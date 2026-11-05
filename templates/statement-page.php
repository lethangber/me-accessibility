<?php
// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="me-statement-content" style="line-height: 1.6; max-width: 800px; margin: 0 auto; font-family: sans-serif;">
	<h1><?php printf( esc_html__( 'Erklärung zur Barrierefreiheit für %s', 'me-accessibility' ), esc_html( $site_name ) ); ?></h1>
	
	<p>
		<?php 
		printf(
			/* translators: 1: Name der Website, 2: URL */
			esc_html__( 'Als Website-Betreiber ist %1$s bestrebt, seine Website im Einklang mit den nationalen Rechtsvorschriften zur Umsetzung der Richtlinie (EU) 2016/2102 des Europäischen Parlaments und des Rates barrierefrei zugänglich zu machen. Diese Erklärung zur Barrierefreiheit gilt für die Website %2$s.', 'me-accessibility' ),
			'<strong>' . esc_html( $site_name ) . '</strong>',
			'<a href="' . esc_url( $site_url ) . '">' . esc_html( $site_url ) . '</a>'
		);
		?>
	</p>

	<h2><?php esc_html_e( 'Stand der Vereinbarkeit mit den Anforderungen', 'me-accessibility' ); ?></h2>
	<p>
		<?php
		printf(
			/* translators: %s: Name der Website */
			esc_html__( 'Diese Website ist wegen der folgenden Unvereinbarkeiten und Ausnahmen teilweise mit den Richtlinien zur Barrierefreiheit (WCAG 2.1 AA) sowie den gesetzlichen Vorgaben der BITV 2.0 (Barrierefreie-Informationstechnik-Verordnung) vereinbar.', 'me-accessibility' ),
			esc_html( $site_name )
		);
		?>
	</p>

	<h3><?php esc_html_e( 'Nicht barrierefreie Inhalte', 'me-accessibility' ); ?></h3>
	<ul>
		<li><?php esc_html_e( 'Einige ältere PDF-Dokumente sind nicht vollständig barrierefrei (fehlende Tags).', 'me-accessibility' ); ?></li>
		<li><?php esc_html_e( 'Für manche dekorativen Schmuckgrafiken fehlen optionale leere Alt-Texte.', 'me-accessibility' ); ?></li>
		<li><?php esc_html_e( 'Einige eingebettete Video-Inhalte besitzen noch keine vollständigen Untertitel.', 'me-accessibility' ); ?></li>
	</ul>

	<h2><?php esc_html_e( 'Erstellung dieser Erklärung zur Barrierefreiheit', 'me-accessibility' ); ?></h2>
	<p>
		<?php 
		printf(
			/* translators: %s: Aktuelles Datum */
			esc_html__( 'Diese Erklärung wurde am %s erstellt. Die Bewertung der Barrierefreiheit basiert auf einer Selbstbewertung mittels des integrierten WCAG Compliance Scanners.', 'me-accessibility' ),
			esc_html( $today )
		);
		?>
	</p>

	<h2><?php esc_html_e( 'Feedback und Kontaktangaben', 'me-accessibility' ); ?></h2>
	<p>
		<?php
		esc_html_e( 'Wenn Sie Mängel bei der Barrierefreiheit auf unserer Website feststellen oder Informationen über nicht barrierefreie Inhalte anfordern möchten, können Sie uns gerne kontaktieren:',
			'me-accessibility'
		);
		?>
	</p>
	<p>
		<strong><?php esc_html_e( 'E-Mail:', 'me-accessibility' ); ?></strong> 
		<a href="mailto:<?php echo esc_attr( $admin_email ); ?>"><?php echo esc_html( $admin_email ); ?></a>
	</p>

	<h2><?php esc_html_e( 'Durchsetzungsverfahren (Schlichtungsstelle)', 'me-accessibility' ); ?></h2>
	<p>
		<?php
		esc_html_e( 'Sollten Sie auf Ihre Kontaktaufnahme hin keine zufriedenstellende Antwort erhalten haben, können Sie sich an die zuständige Schlichtungsstelle für Barrierefreiheit wenden. Diese wird vermittelnd tätig, um eine einvernehmliche Lösung zu finden.',
			'me-accessibility'
		);
		?>
	</p>
</div>
