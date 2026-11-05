<?php
namespace ME_Accessibility\Includes;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Datenbank-Klasse für ME Accessibility.
 * 
 * Verwaltet Schemata und Abfragen für den WCAG-Scanner.
 */
class Database {

	/**
	 * Erstellt die benutzerdefinierten Datenbanktabellen für Scan-Historie und gefundene Probleme.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Tabelle 1: me_accessibility_scans (Übersicht der Scan-Vorgänge)
		$table_scans = $wpdb->prefix . 'me_accessibility_scans';
		$sql_scans = "CREATE TABLE $table_scans (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			scan_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			pages_scanned int(11) DEFAULT 0 NOT NULL,
			score int(11) DEFAULT 100 NOT NULL,
			status varchar(50) DEFAULT 'running' NOT NULL,
			details longtext DEFAULT '' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		// Tabelle 2: me_accessibility_issues (Einzelne gefundene Probleme)
		$table_issues = $wpdb->prefix . 'me_accessibility_issues';
		$sql_issues = "CREATE TABLE $table_issues (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			scan_id bigint(20) NOT NULL,
			url varchar(255) DEFAULT '' NOT NULL,
			issue_type varchar(100) DEFAULT '' NOT NULL,
			selector varchar(255) DEFAULT '' NOT NULL,
			severity varchar(50) DEFAULT 'warning' NOT NULL,
			message text DEFAULT '' NOT NULL,
			code_snippet text DEFAULT '' NOT NULL,
			is_resolved tinyint(1) DEFAULT 0 NOT NULL,
			PRIMARY KEY  (id),
			KEY scan_id (scan_id)
		) $charset_collate;";

		dbDelta( $sql_scans );
		dbDelta( $sql_issues );
	}

	/**
	 * Speichert einen neuen Scan-Eintrag.
	 *
	 * @param int    $pages_scanned Anzahl gescannter Seiten.
	 * @param int    $score         Barrierefreiheits-Score (0-100).
	 * @param string $status        Status ('completed', 'failed', 'running').
	 * @param array  $details       Zusätzliche JSON-Details.
	 * @return int                  Die ID des neu erstellten Scans.
	 */
	public static function save_scan( $pages_scanned, $score, $status, $details = [] ) {
		global $wpdb;
		$table = $wpdb->prefix . 'me_accessibility_scans';

		$wpdb->insert(
			$table,
			[
				'scan_date'     => current_time( 'mysql' ),
				'pages_scanned' => intval( $pages_scanned ),
				'score'         => intval( $score ),
				'status'        => sanitize_text_field( $status ),
				'details'       => wp_json_encode( $details ),
			],
			[ '%s', '%d', '%d', '%s', '%s' ]
		);

		return $wpdb->insert_id;
	}

	/**
	 * Aktualisiert den Status und die Ergebnisse eines Scans.
	 *
	 * @param int    $scan_id       Die Scan-ID.
	 * @param int    $pages_scanned Anzahl gescannter Seiten.
	 * @param int    $score         Barrierefreiheits-Score.
	 * @param string $status        Status.
	 * @param array  $details       JSON-Details.
	 */
	public static function update_scan( $scan_id, $pages_scanned, $score, $status, $details = [] ) {
		global $wpdb;
		$table = $wpdb->prefix . 'me_accessibility_scans';

		$wpdb->update(
			$table,
			[
				'pages_scanned' => intval( $pages_scanned ),
				'score'         => intval( $score ),
				'status'        => sanitize_text_field( $status ),
				'details'       => wp_json_encode( $details ),
			],
			[ 'id' => intval( $scan_id ) ],
			[ '%d', '%d', '%s', '%s' ],
			[ '%d' ]
		);
	}

	/**
	 * Speichert ein einzelnes Barrierefreiheits-Problem.
	 *
	 * @param int    $scan_id      Zugehörige Scan-ID.
	 * @param string $url          Betroffene URL.
	 * @param string $issue_type   Art des Fehlers.
	 * @param string $selector     CSS-Selektor.
	 * @param string $severity     Schweregrad ('critical', 'warning', 'notice').
	 * @param string $message      Erklärung.
	 * @param string $code_snippet Betroffener HTML-Code.
	 * @return int                 Die ID des erstellten Issues.
	 */
	public static function save_issue( $scan_id, $url, $issue_type, $selector, $severity, $message, $code_snippet = '' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'me_accessibility_issues';

		$wpdb->insert(
			$table,
			[
				'scan_id'      => intval( $scan_id ),
				'url'          => esc_url_raw( $url ),
				'issue_type'   => sanitize_text_field( $issue_type ),
				'selector'     => sanitize_text_field( $selector ),
				'severity'     => sanitize_text_field( $severity ),
				'message'      => sanitize_textarea_field( $message ),
				'code_snippet' => sanitize_textarea_field( $code_snippet ),
				'is_resolved'  => 0,
			],
			[ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d' ]
		);

		return $wpdb->insert_id;
	}

	/**
	 * Holt die letzten Scans.
	 *
	 * @param int $limit  Anzahl der Einträge.
	 * @param int $offset Versatz für Paginierung.
	 * @return array      Liste der Scans.
	 */
	public static function get_scans( $limit = 10, $offset = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'me_accessibility_scans';

		$query = $wpdb->prepare(
			"SELECT * FROM $table ORDER BY id DESC LIMIT %d OFFSET %d",
			intval( $limit ),
			intval( $offset )
		);

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Holt alle erkannten Probleme für einen bestimmten Scan.
	 *
	 * @param int $scan_id Die Scan-ID.
	 * @return array       Liste der gefundenen Probleme.
	 */
	public static function get_issues( $scan_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'me_accessibility_issues';

		$query = $wpdb->prepare(
			"SELECT * FROM $table WHERE scan_id = %d ORDER BY id ASC",
			intval( $scan_id )
		);

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Löscht die gesamte Scan- und Fehlerhistorie.
	 */
	public static function clear_history() {
		global $wpdb;
		$table_scans  = $wpdb->prefix . 'me_accessibility_scans';
		$table_issues = $wpdb->prefix . 'me_accessibility_issues';

		$wpdb->query( "TRUNCATE TABLE $table_scans" );
		$wpdb->query( "TRUNCATE TABLE $table_issues" );
	}
}
