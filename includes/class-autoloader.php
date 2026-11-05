<?php
namespace ME_Accessibility\Includes;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoloader-Klasse für ME Accessibility.
 * 
 * Lädt Klassen im Namespace ME_Accessibility automatisch nach WordPress-Konvention.
 */
if ( ! class_exists( 'ME_Accessibility\Includes\Autoloader' ) ) {
	class Autoloader {

		/**
		 * Registriert den Autoloader bei SPL.
		 */
		public static function register() {
			spl_autoload_register( [ __CLASS__, 'autoload' ] );
		}

		/**
		 * Führt das Laden der Klassendatei basierend auf dem Namespace durch.
		 *
		 * @param string $class Die vollqualifizierte Klasse, die geladen werden soll.
		 */
		public static function autoload( $class ) {
			// Namespace-Prefix prüfen.
			$prefix = 'ME_Accessibility\\';
			$len    = strlen( $prefix );

			if ( strncmp( $prefix, $class, $len ) !== 0 ) {
				return; // Nicht unser Namespace.
			}

			// Den relativen Klassennamen extrahieren.
			$relative_class = substr( $class, $len );

			// In Namespace-Pfade zerlegen.
			$parts = explode( '\\', $relative_class );

			// Der letzte Teil ist der Klassenname selbst.
			$class_name = array_pop( $parts );

			// Pfad aufbauen: alle Zwischenordner in Kleinbuchstaben.
			$sub_path = '';
			if ( ! empty( $parts ) ) {
				$sub_path = implode( '/', array_map( 'strtolower', $parts ) ) . '/';
			}

			// Verschiedene Dateinamen-Varianten prüfen (z. B. class-module-manager.php und class-modulemanager.php).
			$variants = [];

			// Variante A: Bindestriche bei CamelCase-Übergängen einfügen (z. B. ModuleManager -> module-manager)
			$hyphenated = preg_replace( '/([a-z0-9])([A-Z])/', '$1-$2', $class_name );
			$hyphenated = preg_replace( '/([A-Z]+)([A-Z][a-z])/', '$1-$2', $hyphenated );
			$variants[] = 'class-' . strtolower( str_replace( '_', '-', $hyphenated ) ) . '.php';

			// Variante B: Einfacher Kleinschreibungs-Fallback
			$variants[] = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

			// Nach der ersten existierenden Datei suchen und laden.
			foreach ( $variants as $file_name ) {
				$file = plugin_dir_path( dirname( __FILE__ ) ) . $sub_path . $file_name;
				if ( file_exists( $file ) ) {
					require_once $file;
					break;
				}
			}
		}
	}

	// Sofort registrieren.
	Autoloader::register();
}
