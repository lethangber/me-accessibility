<?php
namespace ME_Accessibility\Includes;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zentraler Gatekeeper für ME Accessibility Lizenzen und Features.
 *
 * Verwaltet die Abfrage, ob ein bestimmtes Premium-Feature freigeschaltet ist.
 * Da das Free-Plugin sauber bleiben soll, koppeln wir dies über WordPress-Filter.
 */
class License {

	/**
	 * Prüft, ob ein bestimmtes Feature für diese Website freigeschaltet ist.
	 *
	 * @param string $feature_id Die ID des abzufragenden Features.
	 * @return bool True, wenn das Feature freigeschaltet ist, andernfalls False.
	 */
	public static function has_feature( string $feature_id ): bool {
		return false;
	}

	/**
	 * Prüft, ob eine aktive Lizenz vorliegt (Pro-Status aktiv).
	 *
	 * @return bool True, wenn eine aktive Lizenz vorliegt.
	 */
	public static function is_pro_active(): bool {
		return false;
	}
}
