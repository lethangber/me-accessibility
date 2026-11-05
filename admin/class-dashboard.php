<?php
namespace ME_Accessibility\Admin;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard-Klasse für das WordPress Backend.
 * 
 * Verwaltet Menüeinträge, Asset-Enqueueing und das Laden des Admin-Haupttemplates.
 */
class Dashboard {

	/**
	 * Konstruktor. Registriert Hooks für das Admin-Menü, Backend-Assets und Footer-Entfernung.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_head', [ $this, 'remove_admin_footer' ] );
	}

	/**
	 * Entfernt den standardmäßigen WordPress-Footer auf allen Plugin-Administrationsseiten.
	 */
	public function remove_admin_footer() {
		$current_screen = get_current_screen();
		if ( $current_screen && ( 'toplevel_page_me-accessibility' === $current_screen->id || strpos( $current_screen->id, 'me-accessibility' ) !== false ) ) {
			add_filter( 'admin_footer_text', '__return_empty_string', 99 );
			add_filter( 'update_footer', '__return_empty_string', 99 );
		}
	}

	/**
	 * Fügt die Einstellungsseite und das Dashboard zum WordPress-Adminmenü hinzu.
	 */
	public function add_admin_menu() {
		$settings   = get_option( 'me_accessibility_settings', [] );
		$capability = ! empty( $settings['allowed_role'] ) ? $settings['allowed_role'] : 'manage_options';

		add_menu_page(
			__( 'ME Accessibility', 'me-accessibility' ),
			__( 'ME Accessibility', 'me-accessibility' ),
			$capability,
			'me-accessibility',
			[ $this, 'render_dashboard' ],
			'dashicons-universal-access',
			85
		);

		add_submenu_page(
			'me-accessibility',
			__( 'Dashboard', 'me-accessibility' ),
			__( 'Dashboard', 'me-accessibility' ),
			$capability,
			'me-accessibility',
			[ $this, 'render_dashboard' ]
		);

		add_submenu_page(
			'me-accessibility',
			__( 'Upgrade to Pro', 'me-accessibility' ),
			'<span style="color: #f59e0b; font-weight: bold;">' . __( 'Upgrade to Pro', 'me-accessibility' ) . '</span>',
			$capability,
			'me-accessibility-upgrade',
			[ $this, 'render_upgrade_page' ]
		);
	}

	/**
	 * Rendert die Upgrade-Seite für Pro.
	 */
	public function render_upgrade_page() {
		$template_file = ME_ACCESSIBILITY_PATH . 'templates/upgrade-pro.php';
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			echo '<div class="wrap"><h2>' . esc_html__( 'ME Accessibility Pro', 'me-accessibility' ) . '</h2>';
			echo '<p>' . esc_html__( 'Bitte besuchen Sie me-toolkit.com, um ME Accessibility Pro zu erwerben.', 'me-accessibility' ) . '</p></div>';
		}
	}

	/**
	 * Enqueues CSS und JS speziell für das WordPress-Backend des Plugins.
	 *
	 * @param string $hook_suffix Der aktuelle Seiten-Hook.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		// Assets nur auf unserer Plugin-Seite laden, um Konflikte zu minimieren.
		if ( 'toplevel_page_me-accessibility' !== $hook_suffix && 'me-accessibility_page_me-accessibility-upgrade' !== $hook_suffix ) {
			return;
		}

		// CSS laden.
		wp_enqueue_style(
			'me-accessibility-admin-css',
			ME_ACCESSIBILITY_URL . 'assets/css/admin-dashboard.css',
			[],
			ME_ACCESSIBILITY_VERSION . '.' . filemtime( ME_ACCESSIBILITY_PATH . 'assets/css/admin-dashboard.css' )
		);

		// Globale CSS-Variablen auch im Backend zur Verfügung stellen.
		$settings = get_option( 'me_accessibility_settings', [] );
		$accent_color = ! empty( $settings['accent_color'] ) ? sanitize_hex_color( $settings['accent_color'] ) : '#2563eb';
		$custom_css = "
			:root {
				--me-accent-color: {$accent_color};
				--me-accent-glow: " . $accent_color . "26; /* 15% opacity */
				--me-success-color: #10b981;
				--me-success-glow: rgba(16, 185, 129, 0.15);
				--me-error-color: #ef4444;
				--me-error-glow: rgba(239, 68, 68, 0.15);
				--me-warning-color: #f59e0b;
			}
		";
		wp_add_inline_style( 'me-accessibility-admin-css', $custom_css );

		// JS laden.
		wp_enqueue_script(
			'me-accessibility-admin-js',
			ME_ACCESSIBILITY_URL . 'assets/js/admin-dashboard.js',
			[ 'jquery', 'jquery-ui-sortable' ], // Nutzen jQuery im Admin-Bereich zwecks Kompatibilität mit Standard-WP
			ME_ACCESSIBILITY_VERSION . '.' . filemtime( ME_ACCESSIBILITY_PATH . 'assets/js/admin-dashboard.js' ),
			true
		);

		// Lokalisierte Variablen für AJAX-Scanner-Funktionen bereitstellen.
		wp_localize_script( 'me-accessibility-admin-js', 'meAccessibilityAdmin', [
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'scannerNonce' => wp_create_nonce( 'me_scanner_nonce' ),
			'translations' => [
				'scanning'       => __( 'Scanne Website...', 'me-accessibility' ),
				'scanCompleted'  => __( 'Scan erfolgreich abgeschlossen!', 'me-accessibility' ),
				'scanFailed'     => __( 'Scan fehlgeschlagen. Bitte erneut versuchen.', 'me-accessibility' ),
				'confirmHistory' => __( 'Möchten Sie den gesamten Scanverlauf wirklich löschen?', 'me-accessibility' ),
				'critical'       => __( 'Kritisch', 'me-accessibility' ),
				'warning'        => __( 'Warnung', 'me-accessibility' ),
				'notice'         => __( 'Hinweis', 'me-accessibility' ),
				'criticalLabel'  => __( 'Critical', 'me-accessibility' ),
				'warningLabel'   => __( 'Warning', 'me-accessibility' ),
				'noticeLabel'    => __( 'Notice', 'me-accessibility' ),
				'noIssuesFound'  => __( 'Keine Probleme mit diesem Schweregrad gefunden. 🎉', 'me-accessibility' ),
				'loadingIssues'  => __( 'Ergebnisse werden geladen...', 'me-accessibility' ),
				'applyingFixes'       => __( 'Korrigiere Fehler...', 'me-accessibility' ),
				'autofixSuccess'      => __( 'Auto-Fix erfolgreich abgeschlossen!', 'me-accessibility' ),
				'autofixError'        => __( 'Fehler beim Anwenden der Auto-Korrektur.', 'me-accessibility' ),
				'autofixNetworkError' => __( 'Netzwerkfehler während der Auto-Korrektur.', 'me-accessibility' ),
				'confirmRollback'     => __( 'Möchten Sie alle automatischen Barrierefreiheits-Korrekturen wirklich rückgängig machen? Dies stellt die ursprünglichen leeren Medien-Alternativtexte wieder her.', 'me-accessibility' ),
				'rollingBack'         => __( 'Rolle zurück...', 'me-accessibility' ),
				'rollbackSuccess'     => __( 'Rollback erfolgreich durchgeführt!', 'me-accessibility' ),
				'rollbackError'       => __( 'Fehler beim Rollback.', 'me-accessibility' ),
				'rollbackNetworkError'=> __( 'Netzwerkfehler während des Rollbacks.', 'me-accessibility' ),
				'confirmReset'        => __( 'Möchten Sie wirklich alle Einstellungen auf die Standardwerte zurücksetzen?', 'me-accessibility' ),
				'cacheCleared'        => __( 'Cache wurde erfolgreich geleert.', 'me-accessibility' ),
				'noSettingsFound'     => __( 'Keine passenden Einstellungen gefunden.', 'me-accessibility' ),
				'clearingCache'       => __( 'Cache leeren', 'me-accessibility' ) . '...',
				'loadingHistory'      => __( 'Historie wird geladen...', 'me-accessibility' ),
				'noHistoryFound'      => __( 'Keine älteren Versionen gefunden.', 'me-accessibility' ),
				'restoreConfirm'      => __( 'Möchten Sie diese Version wirklich im Editor wiederherstellen?', 'me-accessibility' ),
				'restoreSuccess'      => __( 'Version %s wurde in den Editor geladen. Bitte speichern Sie die Änderungen, um sie dauerhaft zu sichern.', 'me-accessibility' ),
				'restore'             => __( 'Wiederherstellen', 'me-accessibility' ),
			]
		] );
	}

	/**
	 * Rendert das Haupt-Dashboard-Template.
	 */
	public function render_dashboard() {
		// Template einbinden.
		$template_file = ME_ACCESSIBILITY_PATH . 'templates/admin-dashboard.php';
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Template nicht gefunden.', 'me-accessibility' ) . '</p></div>';
		}
	}
}
