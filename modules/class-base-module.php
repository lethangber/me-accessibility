<?php
namespace ME_Accessibility\Modules;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstrakte Basisklasse für alle Module.
 * 
 * Jedes Modul muss diese Klasse erweitern und die erforderlichen Methoden implementieren.
 */
abstract class BaseModule {

	/**
	 * Liefert die eindeutige ID des Moduls (z.B. 'toolbar', 'tts').
	 *
	 * @return string
	 */
	abstract public function get_module_id(): string;

	/**
	 * Liefert den lesbaren Namen des Moduls.
	 *
	 * @return string
	 */
	abstract public function get_module_name(): string;

	/**
	 * Initialisiert das Modul (registriert Hooks, Filter usw.).
	 */
	abstract public function init();

	/**
	 * Prüft, ob das Modul in den Einstellungen aktiviert ist.
	 *
	 * @return bool True, wenn aktiviert.
	 */
	public function is_enabled(): bool {
		$modules_status = get_option( 'me_accessibility_modules', [] );
		$module_id = $this->get_module_id();

		// Premium-Module (Pro) prüfen.
		$pro_modules = [ 'media', 'gdpr', 'tts', 'autofix' ];
		if ( in_array( $module_id, $pro_modules, true ) ) {
			if ( ! \ME_Accessibility\Includes\License::is_pro_active() ) {
				return false;
			}
		}

		// Standardmäßig sind Module aktiv, falls nicht explizit anders gesetzt.
		return isset( $modules_status[ $module_id ] ) ? (bool) $modules_status[ $module_id ] : true;
	}

	/**
	 * Holt die spezifischen Einstellungen für dieses Modul.
	 *
	 * @return array
	 */
	protected function get_settings(): array {
		$all_settings = get_option( 'me_accessibility_settings', [] );
		$module_id = $this->get_module_id();

		return isset( $all_settings[ $module_id ] ) && is_array( $all_settings[ $module_id ] ) 
			? $all_settings[ $module_id ] 
			: [];
	}
}
