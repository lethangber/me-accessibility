<?php
namespace ME_Accessibility\Modules\Helpers;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ME_Accessibility\Modules\BaseModule;
use ME_Accessibility\Includes\Plugin;

/**
 * Modul-Klasse für automatische Barrierefreiheits-Hilfen (Skip-Links, Fokusringe, ARIA).
 */
class HelpersModule extends BaseModule {

	/**
	 * Eindeutige Modul-ID.
	 *
	 * @return string
	 */
	public function get_module_id(): string {
		return 'helpers';
	}

	/**
	 * Lesbarer Name des Moduls.
	 *
	 * @return string
	 */
	public function get_module_name(): string {
		return __( 'Accessibility Helpers (EAA / BITV)', 'me-accessibility' );
	}

	/**
	 * Initialisiert das Modul.
	 */
	public function init() {
		// Skip-to-content Link injecten
		add_action( 'wp_body_open', [ $this, 'inject_skip_link' ] );
		add_action( 'wp_footer', [ $this, 'inject_skip_link_fallback' ] );

		// Globale Fokusring-Stilverbesserungen ausgeben
		add_action( 'wp_head', [ $this, 'inject_focus_styles' ] );
	}

	/**
	 * Injiziert einen barrierefreien Skip-to-Content Link direkt am Body-Anfang.
	 */
	public function inject_skip_link() {
		if ( Plugin::get_instance()->is_plugin_disabled_for_current_request() ) {
			return;
		}

		// Verhindert doppelte Injektion über Fallback-Hooks
		if ( defined( 'ME_SKIP_LINK_INJECTED' ) ) {
			return;
		}
		define( 'ME_SKIP_LINK_INJECTED', true );

		echo '
		<a href="#content" class="me-skip-link" id="me-skip-link">
			' . esc_html__( 'Direkt zum Hauptinhalt springen', 'me-accessibility' ) . '
		</a>';
	}

	/**
	 * Fallback-Injektion für ältere Themes, die wp_body_open() nicht unterstützen.
	 */
	public function inject_skip_link_fallback() {
		if ( ! defined( 'ME_SKIP_LINK_INJECTED' ) ) {
			// Falls noch nicht geladen, per JavaScript an den Anfang des body prependen.
			$this->inject_skip_link();
			?>
			<script>
				document.addEventListener("DOMContentLoaded", function() {
					var skipLink = document.getElementById("me-skip-link");
					if (skipLink) {
						document.body.insertBefore(skipLink, document.body.firstChild);
					}
				});
			</script>
			<?php
		}
	}

	/**
	 * Gibt optimiertes CSS für Tastatur-Fokus-Rahmen und Skip-Links aus.
	 */
	public function inject_focus_styles() {
		if ( Plugin::get_instance()->is_plugin_disabled_for_current_request() ) {
			return;
		}

		// CSS-Eigenschaften
		$settings = get_option( 'me_accessibility_settings', [] );
		$accent_color = ! empty( $settings['accent_color'] ) ? sanitize_hex_color( $settings['accent_color'] ) : '#2563eb';

		?>
		<style id="me-accessibility-helpers-css">
			/* Skip-to-Content Link Styling */
			.me-skip-link {
				position: absolute;
				top: -100px;
				left: 50%;
				transform: translateX(-50%);
				background: <?php echo esc_attr( $accent_color ); ?>;
				color: #ffffff !important;
				padding: 12px 24px;
				border-radius: 0 0 8px 8px;
				z-index: 9999999;
				text-decoration: none !important;
				font-weight: 700;
				font-size: 14px;
				box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
				transition: top 0.3s ease;
			}
			.me-skip-link:focus {
				top: 0;
				outline: 3px solid #ffffff;
				outline-offset: -3px;
			}

			/* Tastatur-Fokusring Erhöhung laut BITV 2.0 */
			*:focus-visible {
				outline: 3px solid <?php echo esc_attr( $accent_color ); ?> !important;
				outline-offset: 2px !important;
				box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.8) !important;
			}
		</style>
		<?php
	}
}
