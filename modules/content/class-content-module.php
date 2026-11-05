<?php
namespace ME_Accessibility\Modules\Content;

// Direkten Zugriff verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ME_Accessibility\Modules\BaseModule;

/**
 * Modul-Klasse für redaktionelle Inhaltsprüfungen (Gutenberg, Elementor, Classic Editor).
 */
class ContentModule extends BaseModule {

	/**
	 * Eindeutige Modul-ID.
	 *
	 * @return string
	 */
	public function get_module_id(): string {
		return 'content';
	}

	/**
	 * Lesbarer Name des Moduls.
	 *
	 * @return string
	 */
	public function get_module_name(): string {
		return __( 'Editor Inhalts-Checker', 'me-accessibility' );
	}

	/**
	 * Initialisiert das Modul.
	 */
	public function init() {
		add_action( 'add_meta_boxes', [ $this, 'register_content_metabox' ] );
		add_action( 'save_post', [ $this, 'save_content_meta_data' ] );
	}

	/**
	 * Registriert eine barrierefreie Meta-Box im Editor (Gutenberg & Classic).
	 */
	public function register_content_metabox() {
		$screens = [ 'post', 'page' ];
		foreach ( $screens as $screen ) {
			add_meta_box(
				'me-accessibility-content-checker',
				__( 'ME Accessibility - Inhalts-Checker', 'me-accessibility' ),
				[ $this, 'render_content_metabox_html' ],
				$screen,
				'side',
				'high'
			);
		}
	}

	/**
	 * Rendert das HTML für die redaktionelle Checkliste.
	 *
	 * @param \WP_Post $post Das aktuelle Beitrags-Objekt.
	 */
	public function render_content_metabox_html( $post ) {
		$disabled = get_post_meta( $post->ID, '_me_accessibility_disabled', true );
		?>
		<div class="me-content-checker-box">
			<style>
				.me-checklist { list-style: none; padding: 0; margin: 15px 0 0 0; }
				.me-checklist li { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 10px; font-size: 13px; line-height: 1.4; }
				.me-checklist input[type="checkbox"] { margin-top: 2px; }
				.me-live-score { display: flex; align-items: center; justify-content: space-between; background: #f1f5f9; padding: 10px; border-radius: 6px; font-weight: 700; font-size: 14px; margin-top: 15px; }
				.me-score-val { color: #10b981; }
			</style>

			<label>
				<input type="checkbox" name="me_accessibility_disabled_field" value="1" <?php checked( $disabled, '1' ); ?> />
				<strong><?php esc_html_e( 'Auf dieser Seite deaktivieren', 'me-accessibility' ); ?></strong>
			</label>
			<p class="description"><?php esc_html_e( 'Verhindert das Laden der Toolbar auf diesem speziellen Beitrag.', 'me-accessibility' ); ?></p>

			<hr style="border: 0; border-top: 1px solid #cbd5e1; margin: 15px 0;" />

			<h4><?php esc_html_e( 'WCAG 2.1 AA Redaktions-Checkliste', 'me-accessibility' ); ?></h4>
			
			<ul class="me-checklist">
				<li>
					<input type="checkbox" class="me-check-item" checked />
					<span><?php esc_html_e( 'Bilder haben beschreibende Alt-Texte', 'me-accessibility' ); ?></span>
				</li>
				<li>
					<input type="checkbox" class="me-check-item" />
					<span><?php esc_html_e( 'Überschriften folgen einer logischen Hierarchie (H1->H2->H3)', 'me-accessibility' ); ?></span>
				</li>
				<li>
					<input type="checkbox" class="me-check-item" />
					<span><?php esc_html_e( 'Linktexte sind aussagekräftig (kein "hier klicken")', 'me-accessibility' ); ?></span>
				</li>
				<li>
					<input type="checkbox" class="me-check-item" />
					<span><?php esc_html_e( 'Tabellen besitzen th-Überschriften und caption', 'me-accessibility' ); ?></span>
				</li>
				<li>
					<input type="checkbox" class="me-check-item" checked />
					<span><?php esc_html_e( 'Farbkontrast des Texts ist ausreichend (> 4.5:1)', 'me-accessibility' ); ?></span>
				</li>
			</ul>

			<div class="me-live-score">
				<span><?php esc_html_e( 'Inhalts-Score:', 'me-accessibility' ); ?></span>
				<span class="me-score-val" id="me-live-score-val">60%</span>
			</div>

			<script>
				jQuery(document).ready(function($) {
					function updateLiveScore() {
						var total = $('.me-check-item').length;
						var checked = $('.me-check-item:checked').length;
						var score = Math.round((checked / total) * 100);
						
						$('#me-live-score-val').text(score + '%');
						if (score > 80) {
							$('#me-live-score-val').css('color', '#10b981');
						} else if (score > 50) {
							$('#me-live-score-val').css('color', '#f59e0b');
						} else {
							$('#me-live-score-val').css('color', '#ef4444');
						}
					}

					$('.me-check-item').on('change', updateLiveScore);
					updateLiveScore();
				});
			</script>
		</div>
		<?php
	}

	/**
	 * Speichert die Meta-Box-Einstellungen (z.B. Deaktivierung pro Seite).
	 *
	 * @param int $post_id Die ID des Beitrags.
	 */
	public function save_content_meta_data( $post_id ) {
		// Sicherheits- und Berechtigungsprüfungen
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$is_disabled = isset( $_POST['me_accessibility_disabled_field'] ) ? '1' : '0';
		update_post_meta( $post_id, '_me_accessibility_disabled', $is_disabled );
	}
}
