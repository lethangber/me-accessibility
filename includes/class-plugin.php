<?php
namespace ME_Accessibility\Includes;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ME_Accessibility\Admin\Settings;
use ME_Accessibility\Modules\ModuleManager;

/**
 * Zentraler Plugin-Loader für ME Accessibility.
 * 
 * Steuert die Initialisierung aller Komponenten und Module.
 */
class Plugin {

	/**
	 * Die einzige Instanz dieser Klasse.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Instanz des Modul-Managers.
	 *
	 * @var ModuleManager|null
	 */
	private ?ModuleManager $module_manager = null;

	/**
	 * Instanz der Einstellungsverwaltung.
	 *
	 * @var Settings|null
	 */
	private ?Settings $settings = null;

	/**
	 * Liefert die Singleton-Instanz der Klasse.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Privater Konstruktor zur Einhaltung des Singleton-Musters.
	 */
	private function __construct() {
		$this->bootstrap();
	}

	/**
	 * Startet das Laden des Plugins.
	 */
	private function bootstrap() {
		// Übersetzungsdateien laden.
		add_action( 'init', [ $this, 'load_textdomain' ] );

		// Dynamischer Übersetzungs-Filter für das Backend & Frontend.
		add_filter( 'gettext', [ \ME_Accessibility\Includes\Language::class, 'translate_string' ], 10, 3 );

		// Settings-Klasse initialisieren.
		if ( is_admin() || wp_doing_ajax() ) {
			$this->settings = new \ME_Accessibility\Admin\Settings();
			new \ME_Accessibility\Admin\Dashboard();
		}

		// Modul-Manager instanziieren und Module laden.
		$this->module_manager = new ModuleManager();
		$this->module_manager->init_modules();

		// Multisite-Support einbinden.
		if ( is_multisite() ) {
			new \ME_Accessibility\Admin\Multisite();
		}

		// Globale Hooks registrieren.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_global_assets' ] );
	}

	/**
	 * Lädt das Textdomain-Sprachpaket.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'me-accessibility',
			false,
			dirname( plugin_basename( ME_ACCESSIBILITY_PATH ) ) . '/languages'
		);
	}

	/**
	 * Lädt globale CSS- und JS-Ressourcen für das Frontend.
	 */
	public function enqueue_global_assets() {
		// Nur laden, wenn nicht explizit deaktiviert (z. B. auf Mobilgeräten oder für bestimmte Rollen).
		if ( $this->is_plugin_disabled_for_current_request() ) {
			return;
		}

		// Generische CSS-Variablen injizieren (z. B. für White-Label oder globale Akzentfarben).
		$settings = get_option( 'me_accessibility_settings', [] );
		$accent_color = ! empty( $settings['accent_color'] ) ? sanitize_hex_color( $settings['accent_color'] ) : '#2563eb';
		
		$custom_css = "
			:root {
				--me-accent-color: {$accent_color};
				--me-font-family: inherit;
			}
		";

		// Zusätzliches Benutzer-CSS einfügen, falls in den Einstellungen hinterlegt.
		if ( ! empty( $settings['custom_css'] ) ) {
			$custom_css .= wp_strip_all_tags( $settings['custom_css'] );
		}

		wp_register_style( 'me-accessibility-global', false );
		wp_enqueue_style( 'me-accessibility-global' );
		wp_add_inline_style( 'me-accessibility-global', $custom_css );

		// Benutzer-JS einfügen, falls vorhanden.
		if ( ! empty( $settings['custom_js'] ) ) {
			wp_add_inline_script( 'me-accessibility-global', wp_strip_all_tags( $settings['custom_js'] ) );
		}
	}

	/**
	 * Prüft, ob das Plugin für die aktuelle Seite oder den Benutzer deaktiviert sein soll.
	 *
	 * @return bool True, wenn das Plugin deaktiviert werden soll.
	 */
	public function is_plugin_disabled_for_current_request(): bool {
		$settings = get_option( 'me_accessibility_settings', [] );

		// 1. Auf Mobilgeräten deaktivieren (falls konfiguriert).
		if ( ! empty( $settings['disable_mobile'] ) && wp_is_mobile() ) {
			return true;
		}

		// 2. Für angemeldete Benutzer deaktivieren (falls konfiguriert).
		if ( ! empty( $settings['disable_logged_in'] ) && is_user_logged_in() ) {
			return true;
		}

		// 3. Deaktivierung pro Seite (Meta-Check).
		if ( is_singular() ) {
			$page_disabled = get_post_meta( get_the_ID(), '_me_accessibility_disabled', true );
			if ( '1' === $page_disabled ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gibt den Modul-Manager zurück.
	 *
	 * @return ModuleManager
	 */
	public function get_module_manager(): ModuleManager {
		return $this->module_manager;
	}
}
