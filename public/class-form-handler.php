<?php
/**
 * Form handler class.
 *
 * Handles form display routing.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/public
 */

class Skate_Club_Form_Handler {

	/**
	 * Handle form requests.
	 *
	 * @since    1.0.0
	 */
	public function handle_forms() {
		$form_type = get_query_var( 'skate_form' );

		if ( ! $form_type ) {
			return;
		}

		// Hide admin bar
		show_admin_bar( false );

		// Prevent caching so new sessions appear immediately
		nocache_headers();

		// Map form types to templates
		$form_templates = array(
			'song_request' => 'song-request-form.php',
			'vote_songs'   => 'song-voting-form.php',
			'upload_media' => 'media-upload-form.php',
			'enter_raffle' => 'raffle-entry-form.php',
		);

		if ( isset( $form_templates[ $form_type ] ) ) {
			$template_path = SKATE_CLUB_PLUGIN_DIR . 'templates/forms/' . $form_templates[ $form_type ];

			if ( file_exists( $template_path ) ) {
				include $template_path;
				exit;
			}
		}

		// Form not found
		wp_die( 'Invalid form' );
	}
}
