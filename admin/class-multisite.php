<?php
namespace ME_Accessibility\Admin;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite-Klasse für ME Accessibility.
 * 
 * Ermöglicht globale Netzwerk-Aktivierung und das optionale Überschreiben von Einstellungen auf Sub-Seiten.
 */
class Multisite {

	/**
	 * Konstruktor. Registriert Hooks für die Multisite-Optionen.
	 */
	public function __construct() {
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', [ $this, 'add_network_admin_menu' ] );
			add_action( 'network_admin_edit_me_save_network_settings', [ $this, 'save_network_settings' ] );
			add_filter( 'option_me_accessibility_settings', [ $this, 'filter_settings_by_network' ] );
			add_filter( 'option_me_accessibility_modules', [ $this, 'filter_modules_by_network' ] );
		}
	}

	/**
	 * Fügt die Einstellungsseite im WordPress Netzwerk-Dashboard hinzu.
	 */
	public function add_network_admin_menu() {
		add_submenu_page(
			'settings.php',
			__( 'ME Accessibility Netzwerk', 'me-accessibility' ),
			__( 'ME Accessibility', 'me-accessibility' ),
			'manage_network_options',
			'me-accessibility-network',
			[ $this, 'render_network_settings' ]
		);
	}

	/**
	 * Rendert die Einstellungen im Netzwerk-Dashboard.
	 */
	public function render_network_settings() {
		$global_settings = get_site_option( 'me_accessibility_network_settings', [] );
		$global_modules  = get_site_option( 'me_accessibility_network_modules', [] );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ME Accessibility - Globale Netzwerkeinstellungen', 'me-accessibility' ); ?></h1>
			<form method="post" action="<?php echo esc_url( network_admin_url( 'edit.php?action=me_save_network_settings' ) ); ?>">
				<?php wp_nonce_field( 'me_network_settings_action', 'me_network_nonce' ); ?>
				
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Lokale Überschreibungen erlauben', 'me-accessibility' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="allow_site_override" value="1" <?php checked( $global_settings['allow_site_override'] ?? 0, 1 ); ?> />
								<?php esc_html_e( 'Sub-Seiten dürfen die globalen Einstellungen für die Toolbar überschreiben.', 'me-accessibility' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Globaler Plugin-Name (White Label)', 'me-accessibility' ); ?></th>
						<td>
							<input type="text" name="plugin_name" class="regular-text" value="<?php echo esc_attr( $global_settings['plugin_name'] ?? '' ); ?>" />
						</td>
					</tr>
				</table>
				
				<h2><?php esc_html_e( 'Global aktivierte Module', 'me-accessibility' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Aktive Module', 'me-accessibility' ); ?></th>
						<td>
							<?php
							$mods = [
								'toolbar'   => __( 'Schwebende Toolbar', 'me-accessibility' ),
								'tts'       => __( 'Vorlesefunktion (Text to Speech)', 'me-accessibility' ),
								'scanner'   => __( 'WCAG-Scanner', 'me-accessibility' ),
								'helpers'   => __( 'EAA- & BITV-Hilfen', 'me-accessibility' ),
								'media'     => __( 'Mediathek-Optimierer', 'me-accessibility' ),
								'gdpr'      => __( 'Barrierefreier Cookie-Banner', 'me-accessibility' ),
								'statement' => __( 'Erklärung zur Barrierefreiheit', 'me-accessibility' ),
							];
							foreach ( $mods as $key => $label ) {
								$checked = isset( $global_modules[ $key ] ) ? (bool) $global_modules[ $key ] : true;
								echo '<label style="display:block; margin-bottom:5px;">';
								echo '<input type="checkbox" name="modules[' . esc_attr( $key ) . ']" value="1" ' . checked( $checked, true, false ) . ' />';
								echo ' ' . esc_html( $label ) . '</label>';
							}
							?>
						</td>
					</tr>
				</table>
				
				<?php submit_button( __( 'Netzwerkeinstellungen speichern', 'me-accessibility' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Speichert die globalen Netzwerkeinstellungen.
	 */
	public function save_network_settings() {
		check_admin_referer( 'me_network_settings_action', 'me_network_nonce' );

		if ( ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'Zugriff verweigert.', 'me-accessibility' ) );
		}

		$settings = [
			'allow_site_override' => ! empty( $_POST['allow_site_override'] ) ? 1 : 0,
			'plugin_name'         => ! empty( $_POST['plugin_name'] ) ? sanitize_text_field( $_POST['plugin_name'] ) : '',
		];

		$modules = [];
		if ( isset( $_POST['modules'] ) && is_array( $_POST['modules'] ) ) {
			foreach ( $_POST['modules'] as $key => $value ) {
				$modules[ sanitize_key( $key ) ] = true;
			}
		}

		update_site_option( 'me_accessibility_network_settings', $settings );
		update_site_option( 'me_accessibility_network_modules', $modules );

		wp_redirect( add_query_arg( [ 'page' => 'me-accessibility-network', 'updated' => 'true' ], network_url( 'admin/settings.php' ) ) );
		exit;
	}

	/**
	 * Filtert die Einzelseiten-Einstellungen basierend auf der Netzwerkkonfiguration.
	 *
	 * @param array $settings Lokale Einstellungen.
	 * @return array
	 */
	public function filter_settings_by_network( $settings ): array {
		$global_settings = get_site_option( 'me_accessibility_network_settings', [] );
		
		if ( empty( $global_settings['allow_site_override'] ) ) {
			// Falls Überschreibungen verboten sind, erzwingen wir die White-Label Angaben des Netzwerks.
			if ( ! empty( $global_settings['plugin_name'] ) ) {
				$settings['plugin_name'] = $global_settings['plugin_name'];
			}
		}
		
		return (array) $settings;
	}

	/**
	 * Filtert die aktivierten Module basierend auf der Netzwerkkonfiguration.
	 *
	 * @param array $modules Lokale Module.
	 * @return array
	 */
	public function filter_modules_by_network( $modules ): array {
		$global_settings = get_site_option( 'me_accessibility_network_settings', [] );
		$global_modules  = get_site_option( 'me_accessibility_network_modules', [] );

		if ( empty( $global_settings['allow_site_override'] ) && ! empty( $global_modules ) ) {
			// Wenn Überschreibungen deaktiviert sind, gelten ausschließlich die Netzwerk-Module.
			return $global_modules;
		}

		return (array) $modules;
	}
}
