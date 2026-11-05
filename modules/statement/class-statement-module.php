<?php
namespace ME_Accessibility\Modules\Statement;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ME_Accessibility\Modules\BaseModule;

/**
 * Modul-Klasse für die rechtssichere Barrierefreiheitserklärung (Barrierefreiheitserklärung / BITV 2.0).
 */
class StatementModule extends BaseModule {

	/**
	 * Eindeutige Modul-ID.
	 *
	 * @return string
	 */
	public function get_module_id(): string {
		return 'statement';
	}

	/**
	 * Lesbarer Name des Moduls.
	 *
	 * @return string
	 */
	public function get_module_name(): string {
		return __( 'Barrierefreiheitserklärung', 'me-accessibility' );
	}

	/**
	 * Initialisiert das Modul und registriert den Shortcode.
	 */
	public function init() {
		add_shortcode( 'me_accessibility_statement', [ $this, 'render_statement_shortcode' ] );
		add_action( 'template_redirect', [ $this, 'handle_statement_preview' ] );
	}

	/**
	 * Render and capture the statement preview page when query var is set.
	 */
	public function handle_statement_preview() {
		if ( isset( $_GET['me_preview_statement'] ) ) {
			$settings = get_option( 'me_accessibility_settings', [] );
			$statement_text = $settings['statement']['text'] ?? '';
			
			if ( empty( $statement_text ) ) {
				$statement_text = $this->render_statement_shortcode();
			}
			
			header( 'Content-Type: text/html; charset=utf-8' );
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title><?php esc_html_e( 'Erklärung zur Barrierefreiheit', 'me-accessibility' ); ?></title>
				<style>
					body {
						font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
						background-color: #f8fafc;
						color: #1e293b;
						padding: 40px 20px;
						margin: 0;
						line-height: 1.6;
					}
					.preview-container {
						max-width: 800px;
						margin: 0 auto;
						background: #ffffff;
						padding: 40px;
						border-radius: 12px;
						box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
					}
					h1 {
						font-size: 28px;
						font-weight: 800;
						color: #0f172a;
						margin-bottom: 24px;
						border-bottom: 2px solid #e2e8f0;
						padding-bottom: 12px;
					}
					h2 {
						font-size: 20px;
						font-weight: 700;
						color: #1e293b;
						margin-top: 32px;
						margin-bottom: 16px;
					}
					h3 {
						font-size: 16px;
						font-weight: 600;
						color: #334155;
						margin-top: 24px;
					}
					a {
						color: #2563eb;
						text-decoration: none;
					}
					a:hover {
						text-decoration: underline;
					}
					kbd {
						background: #f1f5f9;
						border: 1px solid #cbd5e1;
						border-radius: 4px;
						padding: 2px 6px;
						font-size: 12px;
						font-family: monospace;
					}
					.back-to-admin {
						display: inline-flex;
						align-items: center;
						gap: 6px;
						margin-bottom: 20px;
						color: #64748b;
						text-decoration: none;
						font-size: 14px;
						font-weight: 500;
					}
					.back-to-admin:hover {
						color: #2563eb;
					}
					@media print {
						body {
							background: none;
							padding: 0;
							color: #000000;
						}
						.preview-container {
							box-shadow: none;
							padding: 0;
							max-width: 100%;
						}
						.back-to-admin {
							display: none;
						}
					}
				</style>
			</head>
			<body>
				<div class="preview-container">
					<?php if ( current_user_can( 'manage_options' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=me-accessibility#statement' ) ); ?>" class="back-to-admin">&larr; <?php esc_html_e( 'Zurück zum Dashboard', 'me-accessibility' ); ?></a>
					<?php endif; ?>
					<div class="me-statement-content">
						<?php echo wp_kses_post( $statement_text ); ?>
					</div>
				</div>
			</body>
			</html>
			<?php
			exit;
		}
	}

	/**
	 * Rendert die Barrierefreiheitserklärung per Shortcode.
	 *
	 * @return string Das gerenderte HTML.
	 */
	public function render_statement_shortcode(): string {
		$settings = get_option( 'me_accessibility_settings', [] );

		// Falls die Anzeige global deaktiviert ist, leeren String ausgeben.
		if ( isset( $settings['statement']['show'] ) && ! $settings['statement']['show'] ) {
			return '';
		}

		// Falls ein benutzerdefinierter Text vorhanden ist, diesen ausgeben.
		if ( ! empty( $settings['statement']['text'] ) ) {
			return '<div class="me-statement-content" style="line-height: 1.6; max-width: 800px; margin: 0 auto; font-family: sans-serif;">' 
				. wp_kses_post( $settings['statement']['text'] ) 
				. '</div>';
		}

		// Platzhalter-Daten sammeln (Fallback)
		$site_url   = home_url();
		$site_name  = get_bloginfo( 'name' );
		$admin_email = get_option( 'admin_email' );
		$today       = date_i18n( get_option( 'date_format' ) );

		ob_start();
		
		// Template laden
		$template_file = ME_ACCESSIBILITY_PATH . 'templates/statement-page.php';
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			echo '<p>' . esc_html__( 'Erklärung zur Barrierefreiheit konnte nicht geladen werden.', 'me-accessibility' ) . '</p>';
		}

		return ob_get_clean();
	}
}
