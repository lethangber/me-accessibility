<?php
namespace ME_Accessibility\Modules\Toolbar;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ME_Accessibility\Modules\BaseModule;
use ME_Accessibility\Includes\Plugin;

/**
 * Modul-Klasse für die schwebende Barrierefreiheits-Toolbar.
 */
class ToolbarModule extends BaseModule {

	/**
	 * Eindeutige Modul-ID.
	 *
	 * @return string
	 */
	public function get_module_id(): string {
		return 'toolbar';
	}

	/**
	 * Lesbarer Name des Moduls.
	 *
	 * @return string
	 */
	public function get_module_name(): string {
		return __( 'Accessibility Toolbar', 'me-accessibility' );
	}

	/**
	 * Initialisiert das Modul.
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_footer', [ $this, 'render_widget' ] );
	}

	/**
	 * Lädt CSS und JS für das Widget.
	 */
	public function enqueue_assets() {
		// Assets nur laden, wenn das Plugin nicht global für diese Anfrage blockiert ist.
		if ( Plugin::get_instance()->is_plugin_disabled_for_current_request() ) {
			return;
		}

		// Widget Stylesheet laden.
		wp_enqueue_style(
			'me-accessibility-toolbar-css',
			ME_ACCESSIBILITY_URL . 'assets/css/toolbar-widget.css',
			[],
			ME_ACCESSIBILITY_VERSION . '.' . filemtime( ME_ACCESSIBILITY_PATH . 'assets/css/toolbar-widget.css' )
		);

		// Widget Script laden.
		wp_enqueue_script(
			'me-accessibility-toolbar-js',
			ME_ACCESSIBILITY_URL . 'assets/js/toolbar-widget.js',
			[],
			ME_ACCESSIBILITY_VERSION . '.' . filemtime( ME_ACCESSIBILITY_PATH . 'assets/js/toolbar-widget.js' ),
			true
		);

		// Einstellungen für das JavaScript bereitstellen.
		$settings = get_option( 'me_accessibility_settings', [] );
		$current_lang = \ME_Accessibility\Includes\Language::get_current_language();
		$translations = \ME_Accessibility\Includes\Language::get_translations( $current_lang );
		$is_rtl = ( ! empty( $settings['language']['rtl_support'] ) && isset( $translations['dir'] ) && 'rtl' === $translations['dir'] ) ? true : false;

		wp_localize_script( 'me-accessibility-toolbar-js', 'meAccessibilityToolbar', [
			'position'     => $settings['toolbar']['position'] ?? 'right-bottom',
			'theme'        => $settings['toolbar']['theme'] ?? 'light',
			'lang'         => $current_lang,
			'dir'          => $is_rtl ? 'rtl' : 'ltr',
			'translations' => $translations,
			'branding'     => ! empty( $settings['hide_branding'] ) ? 'hide' : 'show',
			'isPro'        => \ME_Accessibility\Includes\License::has_feature( 'pro_features' ) ? 1 : 0,
		] );
	}

	/**
	 * Rendert die Toolbar im Footer der Seite.
	 */
	public function render_widget() {
		if ( Plugin::get_instance()->is_plugin_disabled_for_current_request() ) {
			return;
		}

		$settings = get_option( 'me_accessibility_settings', [] );
		$theme    = $settings['toolbar']['theme'] ?? 'light';
		$position = $settings['toolbar']['position'] ?? 'right-bottom';
		$trigger_type = $settings['toolbar']['trigger_type'] ?? 'button';

		// Template laden.
		$template_file = ME_ACCESSIBILITY_PATH . 'templates/toolbar-widget.php';
		if ( file_exists( $template_file ) ) {
			include $template_file;
		}
	}
}
