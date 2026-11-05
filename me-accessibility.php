<?php
/**
 * Plugin Name:       ME Accessibility
 * Plugin URI:        https://me-toolkit.com
 * Description:       Barrierefreiheits-Toolkit für die WCAG 2.1 AA, EAA und BITV 2.0 Optimierung. Bietet eine Barrierefreiheits-Toolbar, einen WCAG-Scanner und eine Barrierefreiheitserklärung.
 * Version:           3.1
 * Requires at least: 5.4
 * Requires PHP:      7.4
 * Author:            Me Toolkit
 * Author URI:        https://me-toolkit.com
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       me-accessibility
 * Domain Path:       /languages
 *
 * @package           ME_Accessibility
 */

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Đăng ký activation hook trước để WordPress có thể bắt được lỗi kích hoạt
register_activation_hook( __FILE__, function() {
	if ( defined( 'ME_ACCESSIBILITY_PRO_VERSION' ) ) {
		wp_die( 
			esc_html__( 'ME Accessibility Pro đang hoạt động. Vui lòng hủy kích hoạt bản Pro trước khi kích hoạt bản Free.', 'me-accessibility' ), 
			esc_html__( 'Lỗi kích hoạt plugin', 'me-accessibility' ), 
			[ 'back_link' => true ] 
		);
	}

	// Datenbanktabellen erstellen.
	\ME_Accessibility\Includes\Database::create_tables();
	
	// Standardoptionen für Module setzen, falls noch nicht vorhanden.
	$default_modules = [
		'toolbar'   => true,
		'tts'       => true,
		'scanner'   => true,
		'helpers'   => true,
		'media'     => true,
		'content'   => true,
		'gdpr'      => true,
		'statement' => true,
	];
	if ( ! get_option( 'me_accessibility_modules' ) ) {
		update_option( 'me_accessibility_modules', $default_modules );
	}
} );

// Thoát sớm nếu bản Pro đã được kích hoạt, hoặc đang được kích hoạt, tránh lỗi xung đột PHP Namespace.
if ( defined( 'ME_ACCESSIBILITY_PRO_VERSION' ) ) {
	return;
}
if ( isset( $_GET['action'] ) && 'activate' === $_GET['action'] && isset( $_GET['plugin'] ) && 'me-accessibility-pro/me-accessibility-pro.php' === $_GET['plugin'] ) {
	return;
}

// Definition globaler Konstanten für Pfade und Versionierung.
if ( ! defined( 'ME_ACCESSIBILITY_VERSION' ) ) {
	define( 'ME_ACCESSIBILITY_VERSION', '3.1' );
}
if ( ! defined( 'ME_ACCESSIBILITY_PATH' ) ) {
	define( 'ME_ACCESSIBILITY_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'ME_ACCESSIBILITY_URL' ) ) {
	define( 'ME_ACCESSIBILITY_URL', plugin_dir_url( __FILE__ ) );
}

// Autoloader einbinden.
require_once ME_ACCESSIBILITY_PATH . 'includes/class-autoloader.php';

// Plugin-Deaktivierung Hook.
register_deactivation_hook( __FILE__, function() {
	// Optional: temporäre Caches leeren.
	wp_cache_flush();
} );

// Einstellungs- und Dashboard-Links auf der WordPress-Plugin-Seite hinzufügen.
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {
	$dashboard_link = '<a href="' . esc_url( admin_url( 'admin.php?page=me-accessibility#overview' ) ) . '">' . esc_html__( 'Dashboard', 'me-accessibility' ) . '</a>';
	$settings_link  = '<a href="' . esc_url( admin_url( 'admin.php?page=me-accessibility#settings' ) ) . '">' . esc_html__( 'Einstellungen', 'me-accessibility' ) . '</a>';
	array_unshift( $links, $settings_link );
	array_unshift( $links, $dashboard_link );
	return $links;
} );

/**
 * Startet die zentrale Plugin-Instanz.
 */
function me_accessibility_init() {
	\ME_Accessibility\Includes\Plugin::get_instance();
}
add_action( 'plugins_loaded', 'me_accessibility_init' );
