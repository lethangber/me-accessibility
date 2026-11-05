<?php
/**
 * Deinstallations-Skript für ME Accessibility.
 * 
 * Wird aufgerufen, wenn das Plugin vom Administrator gelöscht wird.
 * Entfernt rückstandslos alle Optionen und benutzerdefinierten Datenbanktabellen.
 *
 * @package ME_Accessibility
 */

// Falls WordPress diesen Aufruf nicht triggert, abbrechen.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// 1. Optionen aus der Tabelle wp_options löschen.
delete_option( 'me_accessibility_settings' );
delete_option( 'me_accessibility_modules' );
delete_site_option( 'me_accessibility_network_settings' );
delete_site_option( 'me_accessibility_network_modules' );

// 2. Benutzerdefinierte Tabellen aus der Datenbank entfernen.
$table_scans  = $wpdb->prefix . 'me_accessibility_scans';
$table_issues = $wpdb->prefix . 'me_accessibility_issues';

$wpdb->query( "DROP TABLE IF EXISTS $table_scans" );
$wpdb->query( "DROP TABLE IF EXISTS $table_issues" );

// 3. WordPress-Objekt-Cache leeren.
wp_cache_flush();
