<?php
namespace ME_Accessibility\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language Utility Class for ME Accessibility.
 *
 * Automatically detects the current active language and retrieves translation data.
 */
class Language {

	/**
	 * List of supported 2-letter languages in our assets/js/i18n/ folder.
	 *
	 * @var array
	 */
	public static array $supported_languages = [
		'de', 'en', 'fr', 'es', 'it', 'nl', 'pl', 'pt', 'cs', 'da', 
		'sv', 'fi', 'ro', 'hu', 'sk', 'sl', 'hr', 'el', 'bg', 'vi'
	];

	/**
	 * Detects the current active language code (for frontend and backend).
	 *
	 * @return string 2-letter language code (e.g. 'de', 'en').
	 */
	public static function get_current_language(): string {
		$settings = get_option( 'me_accessibility_settings', [] );

		// 0. Manual language switch (query parameter or cookie) - ONLY on the frontend.
		if ( ! is_admin() ) {
			if ( isset( $_GET['me_lang'] ) && in_array( $_GET['me_lang'], self::$supported_languages, true ) ) {
				$lang = sanitize_text_field( $_GET['me_lang'] );
				setcookie( 'me_accessibility_lang', $lang, time() + ( 30 * 86400 ), '/' );
				return $lang;
			}
			if ( isset( $_COOKIE['me_accessibility_lang'] ) && in_array( $_COOKIE['me_accessibility_lang'], self::$supported_languages, true ) ) {
				return $_COOKIE['me_accessibility_lang'];
			}
		}

		// 1. Force language settings (highest override).
		if ( ! empty( $settings['language']['force_lang'] ) && in_array( $settings['language']['force_lang'], self::$supported_languages, true ) ) {
			return $settings['language']['force_lang'];
		}

		// 2. Detect via TranslatePress.
		if ( function_exists( 'trp_get_current_language' ) ) {
			$lang = trp_get_current_language();
			$lang = self::normalize_language_code( $lang );
			if ( in_array( $lang, self::$supported_languages, true ) ) {
				return $lang;
			}
		}

		// 3. Detect via Polylang.
		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
			$lang = self::normalize_language_code( $lang );
			if ( in_array( $lang, self::$supported_languages, true ) ) {
				return $lang;
			}
		}

		// 4. Detect via WPML.
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$lang = ICL_LANGUAGE_CODE;
			$lang = self::normalize_language_code( $lang );
			if ( in_array( $lang, self::$supported_languages, true ) ) {
				return $lang;
			}
		}

		// 5. Fallback to native WordPress locale.
		$locale = get_locale();
		$lang = self::normalize_language_code( $locale );
		if ( in_array( $lang, self::$supported_languages, true ) ) {
			return $lang;
		}

		// 6. Configured default language (if not 'auto').
		$default_lang = ! empty( $settings['language']['default_lang'] ) ? $settings['language']['default_lang'] : 'auto';
		if ( 'auto' !== $default_lang && in_array( $default_lang, self::$supported_languages, true ) ) {
			return $default_lang;
		}

		// Hard fallback based on site locale.
		return ( strpos( strtolower( $locale ), 'de' ) === 0 ) ? 'de' : 'en';
	}

	/**
	 * Normalizes a language code or locale string to a 2-letter lowercase code.
	 *
	 * @param string $code Raw locale or language code.
	 * @return string Normalized 2-letter language code.
	 */
	public static function normalize_language_code( string $code ): string {
		$code = strtolower( trim( $code ) );
		$code = str_replace( '_', '-', $code );
		$parts = explode( '-', $code );
		return $parts[0];
	}

	/**
	 * Returns translation array for the specified or current language.
	 *
	 * @param string|null $lang Specific language code or null for current detected language.
	 * @return array Translation keys and values.
	 */
	public static function get_translations( ?string $lang = null ): array {
		if ( null === $lang ) {
			$lang = self::get_current_language();
		}

		$file = ME_ACCESSIBILITY_PATH . 'assets/js/i18n/' . $lang . '.json';
		if ( file_exists( $file ) ) {
			$content = file_get_contents( $file );
			$data = json_decode( $content, true );
			if ( is_array( $data ) ) {
				return $data;
			}
		}

		// Fallback to English.
		$en_file = ME_ACCESSIBILITY_PATH . 'assets/js/i18n/en.json';
		if ( file_exists( $en_file ) ) {
			return json_decode( file_get_contents( $en_file ), true ) ?: [];
		}

		return [];
	}

	/**
	 * Hooks into WordPress translation filter to translate backend strings dynamically.
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Original text.
	 * @param string $domain      Translation domain.
	 * @return string             Translated string.
	 */
	public static function translate_string( string $translation, string $text, string $domain ): string {
		// Sowohl das Free- als auch das Pro-Plugin unterstützen dynamische Übersetzungen
		if ( 'me-accessibility' !== $domain && 'me-accessibility-pro' !== $domain ) {
			return $translation;
		}

		$lang = self::get_current_language();
		if ( 'de' === $lang ) {
			return $translation; // Source-Texte sind standardmäßig auf Deutsch verfasst
		}

		static $dictionaries = [];
		if ( ! isset( $dictionaries[ $lang ] ) ) {
			$dictionaries[ $lang ] = self::load_admin_dictionary( $lang );
		}

		if ( isset( $dictionaries[ $lang ][ $text ] ) ) {
			return $dictionaries[ $lang ][ $text ];
		}

		// Automatischer Fallback auf Englisch für internationale Administratoren.
		if ( 'en' !== $lang ) {
			if ( ! isset( $dictionaries['en'] ) ) {
				$dictionaries['en'] = self::load_admin_dictionary( 'en' );
			}
			if ( isset( $dictionaries['en'][ $text ] ) ) {
				return $dictionaries['en'][ $text ];
			}
		}

		return $translation;
	}

	/**
	 * Loads the admin dictionary for a specific language from JSON.
	 *
	 * @param string $lang Language code.
	 * @return array Translation dictionary.
	 */
	private static function load_admin_dictionary( string $lang ): array {
		$file = ME_ACCESSIBILITY_PATH . 'languages/admin-' . $lang . '.json';
		if ( file_exists( $file ) ) {
			$content = file_get_contents( $file );
			$data = json_decode( $content, true );
			if ( is_array( $data ) ) {
				return $data;
			}
		}
		return [];
	}
}
