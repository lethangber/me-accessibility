<?php
namespace ME_Accessibility\Modules;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modul-Manager für ME Accessibility.
 * 
 * Verwaltet das Laden und Deaktivieren der verschiedenen Funktionsmodule.
 */
class ModuleManager {

	/**
	 * Liste aller registrierten Module.
	 *
	 * @var array
	 */
	private array $modules = [];

	/**
	 * Konstruktor. Registriert die Standard-Module.
	 */
	public function __construct() {
		$this->register_modules();
	}

	/**
	 * Registriert alle Kern-Module des Plugins.
	 */
	private function register_modules() {
		$this->modules = [
			'toolbar'   => \ME_Accessibility\Modules\Toolbar\ToolbarModule::class,
			'scanner'   => \ME_Accessibility\Modules\Scanner\ScannerModule::class,
			'helpers'   => \ME_Accessibility\Modules\Helpers\HelpersModule::class,
			'content'   => \ME_Accessibility\Modules\Content\ContentModule::class,
			'statement' => \ME_Accessibility\Modules\Statement\StatementModule::class,
		];
	}

	/**
	 * Initialisiert alle aktivierten Module.
	 */
	public function init_modules() {
		foreach ( $this->modules as $id => $class_name ) {
			if ( class_exists( $class_name ) ) {
				$module_instance = new $class_name();
				
				// Nur laden, wenn das Modul in den Einstellungen aktiv ist.
				if ( $module_instance->is_enabled() ) {
					$module_instance->init();
					// Instanz im Array speichern für späteren Zugriff.
					$this->modules[ $id ] = $module_instance;
				} else {
					unset( $this->modules[ $id ] );
				}
			}
		}
	}

	/**
	 * Holt ein registriertes und aktives Modul anhand seiner ID.
	 *
	 * @param string $id Die Modul-ID.
	 * @return BaseModule|null Das Modul oder null, falls nicht aktiv/vorhanden.
	 */
	public function get_module( string $id ) {
		return isset( $this->modules[ $id ] ) && $this->modules[ $id ] instanceof BaseModule 
			? $this->modules[ $id ] 
			: null;
	}

	/**
	 * Liefert alle derzeit aktiven Module zurück.
	 *
	 * @return array
	 */
	public function get_active_modules(): array {
		return $this->modules;
	}
}
