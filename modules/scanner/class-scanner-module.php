<?php
namespace ME_Accessibility\Modules\Scanner;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ME_Accessibility\Modules\BaseModule;
use ME_Accessibility\Includes\Database;

/**
 * Modul-Klasse für den integrierten WCAG 2.1 Konformitäts-Scanner.
 */
class ScannerModule extends BaseModule {

	/**
	 * Eindeutige Modul-ID.
	 *
	 * @return string
	 */
	public function get_module_id(): string {
		return 'scanner';
	}

	/**
	 * Lesbarer Name des Moduls.
	 *
	 * @return string
	 */
	public function get_module_name(): string {
		return __( 'WCAG Compliance Scanner', 'me-accessibility' );
	}

	/**
	 * Initialisiert das Modul und registriert die AJAX-Endpunkte für das Backend.
	 */
	public function init() {
		add_action( 'wp_ajax_me_start_wcag_scan', [ $this, 'ajax_start_scan' ] );
		add_action( 'wp_ajax_me_complete_wcag_scan', [ $this, 'ajax_complete_scan' ] );
		add_action( 'wp_ajax_me_clear_scan_history', [ $this, 'ajax_clear_history' ] );
		add_action( 'wp_ajax_me_get_scan_details', [ $this, 'ajax_get_scan_details' ] );
		add_action( 'admin_init', [ $this, 'handle_csv_export' ] );
	}

	/**
	 * AJAX-Handler: Startet einen neuen Scanvorgang und führt die Analyse durch.
	 */
	public function ajax_start_scan() {
		check_ajax_referer( 'me_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Zugriff verweigert.' ] );
		}

		// 1. Neuen Scan-Eintrag mit Status 'running' anlegen
		$scan_id = Database::save_scan( 0, 100, 'running' );

		// 2. Homepage-HTML holen und parsen
		$url = home_url( '/' );
		$response = wp_remote_get( $url, [ 'timeout' => 15, 'sslverify' => false ] );

		if ( is_wp_error( $response ) ) {
			// Scan fehlschlagen lassen
			Database::update_scan( $scan_id, 0, 0, 'failed', [ 'error' => $response->get_error_message() ] );
			wp_send_json_error( [ 'message' => 'Konnte Homepage nicht abrufen.' ] );
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			Database::update_scan( $scan_id, 0, 0, 'failed', [ 'error' => 'Leerer HTML-Inhalt' ] );
			wp_send_json_error( [ 'message' => 'Leerer HTML-Inhalt empfangen.' ] );
		}

		// 3. HTML-DOM-Analysator ausführen
		$issues = $this->analyze_html_compliance( $html, $url );

		// 4. Probleme in der Datenbank protokollieren
		$critical_count = 0;
		$warning_count  = 0;
		$notice_count   = 0;

		foreach ( $issues as $issue ) {
			Database::save_issue(
				$scan_id,
				$url,
				$issue['type'],
				$issue['selector'],
				$issue['severity'],
				$issue['message'],
				$issue['code']
			);

			if ( 'critical' === $issue['severity'] ) {
				$critical_count++;
			} elseif ( 'warning' === $issue['severity'] ) {
				$warning_count++;
			} else {
				$notice_count++;
			}
		}

		// 5. Score berechnen (Basis 100, Abzüge je Schweregrad)
		$score = 100 - ( $critical_count * 8 ) - ( $warning_count * 3 ) - ( $notice_count * 1 );
		$score = max( 15, min( 100, $score ) ); // Eingrenzung zwischen 15 und 100

		// Details-Array abspeichern
		$details = [
			'critical' => $critical_count,
			'warning'  => $warning_count,
			'notice'   => $notice_count,
		];

		// Scan-Eintrag aktualisieren
		Database::update_scan( $scan_id, 1, $score, 'completed', $details );

		wp_send_json_success( [
			'scan_id' => $scan_id,
			'score'   => $score,
		] );
	}

	/**
	 * AJAX-Handler: Schließt den Scan ab (Simulierte Verzögerung für visuelle Balken).
	 */
	public function ajax_complete_scan() {
		check_ajax_referer( 'me_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * AJAX-Handler: Löscht die gesamte Scan-Historie.
	 */
	public function ajax_clear_history() {
		check_ajax_referer( 'me_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		Database::clear_history();
		wp_send_json_success();
	}

	public function ajax_get_scan_details() {
		check_ajax_referer( 'me_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Zugriff verweigert.', 'me-accessibility' ) ] );
		}

		$scan_id = isset( $_POST['scan_id'] ) ? intval( $_POST['scan_id'] ) : 0;
		if ( ! $scan_id ) {
			wp_send_json_error( [ 'message' => __( 'Ungültige Scan-ID.', 'me-accessibility' ) ] );
		}

		$issues = Database::get_issues( $scan_id );

		// Translate database values before sending JSON response
		foreach ( $issues as &$issue ) {
			// Translate severity label
			$issue['severity_label'] = \ME_Accessibility\Includes\Language::translate_string( $issue['severity'], $issue['severity'], 'me-accessibility' );

			// Translate issue type label
			$issue['issue_type_label'] = \ME_Accessibility\Includes\Language::translate_string( $issue['issue_type'], $issue['issue_type'], 'me-accessibility' );

			// Translate dynamic message
			$message = $issue['message'];
			if ( preg_match( '/^Falsche Überschriften-Hierarchie: Auf ein (h\d) folgt direkt ein (h\d)\. Zwischenüberschriften sollten logisch verschachtelt sein\.$/', $message, $matches ) ) {
				$format = \ME_Accessibility\Includes\Language::translate_string(
					'Falsche Überschriften-Hierarchie: Auf ein %s folgt direkt ein %s. Zwischenüberschriften sollten logisch verschachtelt sein.',
					'Falsche Überschriften-Hierarchie: Auf ein %s folgt direkt ein %s. Zwischenüberschriften sollten logisch verschachtelt sein.',
					'me-accessibility'
				);
				$message = sprintf( $format, $matches[1], $matches[2] );
			} else {
				$message = \ME_Accessibility\Includes\Language::translate_string( $message, $message, 'me-accessibility' );
			}
			$issue['message'] = $message;
		}
		unset( $issue );
		
		wp_send_json_success( [
			'issues' => $issues
		] );
	}

	/**
	 * Führt eine server-seitige DOM-Analyse des HTML-Dokuments durch.
	 *
	 * @param string $html Der rohe HTML-Code.
	 * @param string $url  Die gescannte URL.
	 * @return array       Liste der gefundenen Probleme.
	 */
	private function analyze_html_compliance( string $html, string $url ): array {
		$issues = [];

		// Fehlerunterdrückung für unvollständiges HTML
		libxml_use_internal_errors( true );
		$dom = new \DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		libxml_clear_errors();

		// A. Prüfung: Fehlendes lang-Attribut im html-Tag
		$html_tags = $dom->getElementsByTagName( 'html' );
		if ( $html_tags->length > 0 ) {
			$html_tag = $html_tags->item( 0 );
			if ( ! $html_tag->hasAttribute( 'lang' ) ) {
				$issues[] = [
					'type'     => 'missing_lang',
					'selector' => 'html',
					'severity' => 'critical',
					'message'  => 'Das <html>-Element besitzt kein gültiges lang-Attribut. Screenreader können die Sprache der Seite nicht korrekt vorlesen.',
					'code'     => '<html>',
				];
			}
		}

		// B. Prüfung: Fehlende ALT-Attribute bei Bildern
		$images = $dom->getElementsByTagName( 'img' );
		foreach ( $images as $img ) {
			if ( ! $img->hasAttribute( 'alt' ) ) {
				$src = $img->getAttribute( 'src' );
				$issues[] = [
					'type'     => 'missing_alt',
					'selector' => 'img[src="' . esc_attr( $src ) . '"]',
					'severity' => 'critical',
					'message'  => 'Dem Bild fehlt ein alt-Attribut zur Beschreibung des Bildinhalts.',
					'code'     => $dom->saveHTML( $img ),
				];
			}
		}

		// C. Prüfung: Leere Links (a-Tags ohne Text/ARIA-Label)
		$links = $dom->getElementsByTagName( 'a' );
		foreach ( $links as $link ) {
			$text = trim( $link->nodeValue );
			if ( empty( $text ) && ! $link->hasAttribute( 'aria-label' ) && ! $link->hasAttribute( 'title' ) ) {
				$href = $link->getAttribute( 'href' );
				$issues[] = [
					'type'     => 'empty_link',
					'selector' => 'a[href="' . esc_attr( $href ) . '"]',
					'severity' => 'warning',
					'message'  => 'Dieser Link ist leer (enthält weder Text noch ein ARIA-Label). Tastaturnutzer hören hier kein Ziel.',
					'code'     => $dom->saveHTML( $link ),
				];
			}
		}

		// D. Prüfung: Formularfelder ohne Beschriftung (input ohne Label/ARIA-Label)
		$inputs = $dom->getElementsByTagName( 'input' );
		foreach ( $inputs as $input ) {
			$type = $input->getAttribute( 'type' );
			if ( in_array( $type, [ 'text', 'email', 'search', 'tel', 'url' ] ) ) {
				if ( ! $input->hasAttribute( 'id' ) && ! $input->hasAttribute( 'aria-label' ) && ! $input->hasAttribute( 'placeholder' ) ) {
					$issues[] = [
						'type'     => 'missing_label',
						'selector' => 'input[type="' . esc_attr( $type ) . '"]',
						'severity' => 'warning',
						'message'  => 'Das Formularfeld besitzt keine Beschriftung (label) und kein ARIA-Label.',
						'code'     => $dom->saveHTML( $input ),
					];
				}
			}
		}

		// E. Prüfung: Überschriften-Hierarchie (Heading Jumps)
		$headings = [];
		$xpath = new \DOMXPath( $dom );
		$nodes = $xpath->query( '//h1|//h2|//h3|//h4|//h5|//h6' );
		
		$last_level = 0;
		foreach ( $nodes as $node ) {
			$current_level = intval( substr( $node->tagName, 1 ) );
			if ( $last_level > 0 && $current_level > ( $last_level + 1 ) ) {
				$issues[] = [
					'type'     => 'heading_jump',
					'selector' => $node->tagName,
					'severity' => 'notice',
					'message'  => sprintf( 'Falsche Überschriften-Hierarchie: Auf ein %s folgt direkt ein %s. Zwischenüberschriften sollten logisch verschachtelt sein.', 'h' . $last_level, 'h' . $current_level ),
					'code'     => $dom->saveHTML( $node ),
				];
			}
			$last_level = $current_level;
		}

		return $issues;
	}

	/**
	 * Behandelt den Export gefundener Fehlerberichte als CSV-Datei.
	 */
	public function handle_csv_export() {
		if ( isset( $_GET['action'] ) && 'me_export_csv' === $_GET['action'] ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Zugriff verweigert.', 'me-accessibility' ) );
			}

			if ( ! \ME_Accessibility\Includes\License::has_feature( 'advanced_scanner' ) ) {
				wp_die( esc_html__( 'Der CSV-Export ist ein Premium-Feature von ME Accessibility Pro. Bitte aktivieren Sie eine gültige Pro-Lizenz.', 'me-accessibility' ) );
			}

			$scan_id = isset( $_GET['scan_id'] ) ? intval( $_GET['scan_id'] ) : 0;
			if ( ! $scan_id ) {
				wp_die( esc_html__( 'Ungültige Scan-ID.', 'me-accessibility' ) );
			}

			$issues = Database::get_issues( $scan_id );

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=me-accessibility-issues-scan-' . $scan_id . '.csv' );

			$output = fopen( 'php://output', 'w' );
			
			// UTF-8 BOM für Excel-Kompatibilität hinzufügen.
			fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

			// Header-Zeile schreiben
			fputcsv( $output, [ 'ID', 'URL', 'Fehler-Typ', 'CSS-Selektor', 'Schweregrad', 'Beschreibung', 'Betroffener Code' ] );

			// Daten schreiben
			foreach ( $issues as $issue ) {
				fputcsv( $output, [
					$issue['id'],
					$issue['url'],
					$issue['issue_type'],
					$issue['selector'],
					$issue['severity'],
					$issue['message'],
					$issue['code_snippet']
				] );
			}

			fclose( $output );
			exit;
		}
	}
}
