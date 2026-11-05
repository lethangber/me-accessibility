<?php
namespace ME_Accessibility\Admin;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings-Klasse für ME Accessibility.
 * 
 * Verwaltet globale Einstellungen, White-Labeling, Rollenrechte sowie Import/Export.
 */
class Settings {

	/**
	 * Konstruktor. Registriert Hooks für Einstellungen und Administrations-Seiten.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_init', [ $this, 'handle_import_export' ] );
		add_action( 'wp_ajax_me_clear_cache', [ $this, 'ajax_clear_cache' ] );
		add_action( 'wp_ajax_me_get_statement_history', [ $this, 'ajax_get_statement_history' ] );
	}

	/**
	 * AJAX-Handler zum Leeren des Caches.
	 */
	public function ajax_clear_cache() {
		check_ajax_referer( 'me_scanner_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unzureichende Berechtigungen.', 'me-accessibility' ) ] );
		}
		wp_cache_flush();
		wp_send_json_success( [ 'message' => __( 'Cache wurde erfolgreich geleert.', 'me-accessibility' ) ] );
	}

	/**
	 * AJAX-Handler zum Laden des Erklärungsverlaufs.
	 */
	public function ajax_get_statement_history() {
		check_ajax_referer( 'me_scanner_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unzureichende Berechtigungen.', 'me-accessibility' ) ] );
		}

		$history = get_option( 'me_accessibility_statement_history', [] );
		wp_send_json_success( [ 'history' => $history ] );
	}

	/**
	 * Registriert die Plugin-Einstellungen über die WordPress Settings API.
	 */
	public function register_settings() {
		// Globale Optionen registrieren
		register_setting( 'me_accessibility_settings_group', 'me_accessibility_settings', [
			'sanitize_callback' => [ $this, 'sanitize_settings' ]
		] );

		register_setting( 'me_accessibility_settings_group', 'me_accessibility_modules', [
			'sanitize_callback' => [ $this, 'sanitize_modules' ]
		] );
	}

	public function sanitize_settings( $input ): array {
		$output = [];

		// Erscheinungsbild & Branding-Einstellungen bereinigen (Pro-Feature Sperre).
		if ( \ME_Accessibility\Includes\License::has_feature( 'white_label' ) ) {
			$output['hide_branding'] = ! empty( $input['hide_branding'] ) ? 1 : 0;
			$output['custom_css']    = ! empty( $input['custom_css'] ) ? wp_strip_all_tags( $input['custom_css'] ) : '';
			$output['custom_js']     = ! empty( $input['custom_js'] ) ? wp_strip_all_tags( $input['custom_js'] ) : '';
		} else {
			$output['hide_branding'] = 0;
			$output['custom_css']    = '';
			$output['custom_js']     = '';
		}
		
		$output['accent_color']   = ! empty( $input['accent_color'] ) ? sanitize_hex_color( $input['accent_color'] ) : '#2563eb';
		
		// Erweiterte Einstellungen.
		$output['disable_mobile']   = ! empty( $input['disable_mobile'] ) ? 1 : 0;
		$output['disable_logged_in'] = ! empty( $input['disable_logged_in'] ) ? 1 : 0;

		// Rollenrechte (standardmäßig administrator).
		$output['allowed_role']    = ! empty( $input['allowed_role'] ) ? sanitize_text_field( $input['allowed_role'] ) : 'manage_options';

		// Multilanguage settings.
		if ( isset( $input['language'] ) && is_array( $input['language'] ) ) {
			$output['language'] = [
				'default_lang'  => sanitize_text_field( $input['language']['default_lang'] ?? 'auto' ),
				'force_lang'    => sanitize_text_field( $input['language']['force_lang'] ?? '' ),
				'tts_multilang' => ! empty( $input['language']['tts_multilang'] ) ? 1 : 0,
				'rtl_support'   => ! empty( $input['language']['rtl_support'] ) ? 1 : 0,
			];
		} else {
			$output['language'] = [
				'default_lang'  => 'auto',
				'force_lang'    => '',
				'tts_multilang' => 1,
				'rtl_support'   => 1,
			];
		}

		// Modul-spezifische Einstellungen sichern.
		if ( isset( $input['toolbar'] ) && is_array( $input['toolbar'] ) ) {
			$custom_svg = isset( $input['toolbar']['custom_svg'] ) ? trim( $input['toolbar']['custom_svg'] ) : '';
			if ( ! current_user_can( 'unfiltered_html' ) ) {
				$custom_svg = wp_kses( $custom_svg, self::get_allowed_svg_tags() );
			}

			// Sanitize per-device responsive settings.
			$responsive = [];
			$devices    = [ 'desktop', 'tablet', 'mobile' ];
			$defaults   = [
				'desktop' => [ 'offset_bottom' => 20, 'offset_side' => 20, 'icon_size' => 56 ],
				'tablet'  => [ 'offset_bottom' => 16, 'offset_side' => 16, 'icon_size' => 50 ],
				'mobile'  => [ 'offset_bottom' => 12, 'offset_side' => 12, 'icon_size' => 44 ],
			];
			foreach ( $devices as $device ) {
				$dev_input = $input['toolbar']['responsive'][ $device ] ?? [];
				$responsive[ $device ] = [
					'offset_bottom' => isset( $dev_input['offset_bottom'] ) ? intval( $dev_input['offset_bottom'] ) : $defaults[ $device ]['offset_bottom'],
					'offset_side'   => isset( $dev_input['offset_side'] )   ? intval( $dev_input['offset_side'] )   : $defaults[ $device ]['offset_side'],
					'icon_size'     => isset( $dev_input['icon_size'] )      ? intval( $dev_input['icon_size'] )      : $defaults[ $device ]['icon_size'],
				];
			}

			$output['toolbar'] = [
				'position'      => sanitize_text_field( $input['toolbar']['position'] ?? 'right-bottom' ),
				'theme'         => sanitize_text_field( $input['toolbar']['theme'] ?? 'light' ),
				'trigger_type'  => sanitize_text_field( $input['toolbar']['trigger_type'] ?? 'button' ),
				// Kept for backwards-compat (desktop values shadow these).
				'offset_bottom' => $responsive['desktop']['offset_bottom'],
				'offset_side'   => $responsive['desktop']['offset_side'],
				'icon_type'     => sanitize_text_field( $input['toolbar']['icon_type'] ?? 'default' ),
				'custom_svg'    => $custom_svg,
				'responsive'    => $responsive,
			];
		}

		if ( isset( $input['tts'] ) && is_array( $input['tts'] ) ) {
			$output['tts'] = [
				'default_voice' => sanitize_text_field( $input['tts']['default_voice'] ?? 'de-DE' ),
				'default_speed' => floatval( $input['tts']['default_speed'] ?? 1.0 ),
			];
		}

		if ( isset( $input['statement'] ) && is_array( $input['statement'] ) ) {
			$old_settings = get_option( 'me_accessibility_settings', [] );
			$old_text = $old_settings['statement']['text'] ?? '';
			$new_text = wp_kses_post( $input['statement']['text'] ?? '' );
			
			$last_updated = $old_settings['statement']['last_updated'] ?? '';
			$version = $old_settings['statement']['version'] ?? '1.0';
			
			if ( $old_text !== $new_text || empty( $last_updated ) ) {
				$last_updated = date_i18n( 'M d, Y' );
				if ( ! empty( $old_text ) && $old_text !== $new_text ) {
					$history = get_option( 'me_accessibility_statement_history', [] );
					if ( ! is_array( $history ) ) {
						$history = [];
					}
					if ( count( $history ) >= 10 ) {
						array_pop( $history );
					}
					array_unshift( $history, [
						'version'      => $version,
						'last_updated' => $old_settings['statement']['last_updated'] ?? date_i18n( 'M d, Y' ),
						'text'         => $old_text,
						'date'         => current_time( 'mysql' ),
					] );
					update_option( 'me_accessibility_statement_history', $history );

					$version = number_format( floatval( $version ) + 0.1, 1 );
				}
			}

			$sections = [];
			if ( ! empty( $input['statement']['sections_json'] ) ) {
				$sections = json_decode( stripslashes( $input['statement']['sections_json'] ), true );
			}

			$output['statement'] = [
				'show'         => ! empty( $input['statement']['show'] ) ? 1 : 0,
				'company'      => sanitize_text_field( $input['statement']['company'] ?? '' ),
				'email'        => sanitize_email( $input['statement']['email'] ?? '' ),
				'url'          => esc_url_raw( $input['statement']['url'] ?? '' ),
				'text'         => $new_text,
				'last_updated' => sanitize_text_field( $last_updated ),
				'version'      => sanitize_text_field( $version ),
				'sections'     => $sections,
			];
		}

		// Geplante Scans (Pro-Feature Sperre).
		if ( \ME_Accessibility\Includes\License::has_feature( 'advanced_scanner' ) && isset( $input['scanner'] ) && is_array( $input['scanner'] ) ) {
			$output['scanner'] = [
				'interval' => sanitize_text_field( $input['scanner']['interval'] ?? 'none' ),
			];
		} else {
			$output['scanner'] = [
				'interval' => 'none',
			];
		}

		// Auto-Fix-Status erhalten (wird primär über AJAX-Endpunkte gesteuert).
		$old_settings = get_option( 'me_accessibility_settings', [] );
		$output['autofix'] = [
			'enabled'      => ! empty( $input['autofix']['enabled'] ) ? 1 : ( ! empty( $old_settings['autofix']['enabled'] ) ? 1 : 0 ),
			'applied_time' => ! empty( $input['autofix']['applied_time'] ) ? sanitize_text_field( $input['autofix']['applied_time'] ) : ( ! empty( $old_settings['autofix']['applied_time'] ) ? sanitize_text_field( $old_settings['autofix']['applied_time'] ) : '' ),
		];

		return $output;
	}

	/**
	 * Bereinigt die Moduleinstellungen (Aktiv/Inaktiv).
	 *
	 * @param array $input Eingehende Formulardaten.
	 * @return array       Bereinigte Formulardaten.
	 */
	public function sanitize_modules( $input ): array {
		$output = [];
		$available_modules = [ 'toolbar', 'tts', 'scanner', 'helpers', 'media', 'content', 'gdpr', 'statement', 'autofix' ];

		foreach ( $available_modules as $mod ) {
			$output[ $mod ] = ! empty( $input[ $mod ] ) ? true : false;
		}

		return $output;
	}

	/**
	 * Behandelt den JSON-basierten Import und Export von Einstellungen.
	 */
	public function handle_import_export() {
		// Sicherheitsüberprüfung für den Export
		if ( isset( $_GET['me_action'] ) && 'export_settings' === $_GET['me_action'] ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Unzureichende Berechtigungen.', 'me-accessibility' ) );
			}

			check_admin_referer( 'me_export_settings_nonce', 'nonce' );

			$data = [
				'settings' => get_option( 'me_accessibility_settings', [] ),
				'modules'  => get_option( 'me_accessibility_modules', [] ),
				'exported' => current_time( 'mysql' ),
				'source'   => home_url(),
			];

			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=me-accessibility-settings.json' );
			echo wp_json_encode( $data );
			exit;
		}

		// Sicherheitsüberprüfung für den Import
		if ( isset( $_POST['me_import_settings'] ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'Unzureichende Berechtigungen.', 'me-accessibility' ) );
			}

			check_admin_referer( 'me_import_settings_nonce', 'me_import_nonce' );

			if ( ! empty( $_FILES['import_file']['tmp_name'] ) ) {
				$file_content = file_get_contents( sanitize_text_field( $_FILES['import_file']['tmp_name'] ) );
				$data = json_decode( $file_content, true );

				if ( is_array( $data ) && isset( $data['settings'] ) && isset( $data['modules'] ) ) {
					update_option( 'me_accessibility_settings', $data['settings'] );
					update_option( 'me_accessibility_modules', $data['modules'] );
					
					add_settings_error(
						'me_accessibility_messages',
						'me_import_success',
						__( 'Einstellungen erfolgreich importiert!', 'me-accessibility' ),
						'updated'
					);
				} else {
					add_settings_error(
						'me_accessibility_messages',
						'me_import_error',
						__( 'Ungültige JSON-Datei.', 'me-accessibility' ),
						'error'
					);
				}
			}
		}
	}

	/**
	 * Holt den Status der Systemressourcen für den System-Status-Bereich.
	 *
	 * @return array
	 */
	public static function get_system_status(): array {
		global $wpdb;
		return [
			'php_version'        => PHP_VERSION,
			'wp_version'         => get_bloginfo( 'version' ),
			'wp_memory_limit'    => WP_MEMORY_LIMIT,
			'mysql_version'      => $wpdb->db_version(),
			'max_execution_time' => ini_get( 'max_execution_time' ) . 's',
			'upload_max_filesize'=> ini_get( 'upload_max_filesize' ),
		];
	}

	/**
	 * Hilfsmethode, die erlaubte HTML-Tags für SVGs definiert.
	 *
	 * @return array Erlaubte Tags und Attribute für wp_kses.
	 */
	private static function get_allowed_svg_tags(): array {
		return [
			'svg' => [
				'xmlns'           => true,
				'viewbox'         => true,
				'width'           => true,
				'height'          => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'class'           => true,
				'id'              => true,
				'style'           => true,
			],
			'path' => [
				'd'            => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			],
			'circle' => [
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			],
			'rect' => [
				'x'            => true,
				'y'            => true,
				'width'        => true,
				'height'       => true,
				'rx'           => true,
				'ry'           => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			],
			'polygon' => [
				'points'       => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			],
			'polyline' => [
				'points'       => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			],
			'g' => [
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			]
		];
	}
}
