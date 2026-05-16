<?php
/**
 * Public display handler class.
 *
 * Handles the main screen display.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/public
 */

class Skate_Club_Public_Display {

	/**
	 * Handle screen display requests.
	 *
	 * @since    1.0.0
	 */
	public function handle_screen_display() {
		$screen_display = get_query_var( 'skate_screen_display' );
		$fullview = get_query_var( 'skate_fullview' );

		if ( $screen_display ) {
			// Hide admin bar
			show_admin_bar( false );

			// Load template
			include SKATE_CLUB_PLUGIN_DIR . 'templates/screen-display.php';
			exit;
		}

		if ( $fullview ) {
			// Hide admin bar
			show_admin_bar( false );

			// Load appropriate fullview template
			$template_map = array(
				'songs'   => 'fullview-songs.php',
				'spinner' => 'fullview-spinner.php',
				'raffle'  => 'fullview-raffle.php',
				'gallery' => 'fullview-gallery.php',
			);

			if ( isset( $template_map[ $fullview ] ) ) {
				include SKATE_CLUB_PLUGIN_DIR . 'templates/' . $template_map[ $fullview ];
				exit;
			}
		}
	}

	/**
	 * Enqueue public styles.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		// Only on screen display, fullview or forms
		if ( get_query_var( 'skate_screen_display' ) || get_query_var( 'skate_form' ) || get_query_var( 'skate_fullview' ) ) {
			wp_enqueue_style(
				'skate-club-public',
				SKATE_CLUB_PLUGIN_URL . 'public/assets/css/screen-display.css',
				array(),
				SKATE_CLUB_VERSION
			);

			wp_enqueue_style(
				'skate-club-forms',
				SKATE_CLUB_PLUGIN_URL . 'public/assets/css/forms.css',
				array(),
				SKATE_CLUB_VERSION
			);
		}
	}

	/**
	 * Enqueue public scripts.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// Screen display
		if ( get_query_var( 'skate_screen_display' ) ) {
			wp_enqueue_script(
				'skate-club-screen-display',
				SKATE_CLUB_PLUGIN_URL . 'public/assets/js/screen-display.js',
				array( 'jquery' ),
				SKATE_CLUB_VERSION,
				true
			);

			wp_localize_script(
				'skate-club-screen-display',
				'skateClubScreen',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				)
			);
		}

		// Forms
		if ( get_query_var( 'skate_form' ) ) {
			wp_enqueue_script(
				'skate-club-form-handler',
				SKATE_CLUB_PLUGIN_URL . 'public/assets/js/form-handler.js',
				array( 'jquery' ),
				SKATE_CLUB_VERSION,
				true
			);

			wp_enqueue_script(
				'skate-club-fingerprint',
				SKATE_CLUB_PLUGIN_URL . 'public/assets/js/fingerprint.js',
				array(),
				SKATE_CLUB_VERSION,
				true
			);

			wp_localize_script(
				'skate-club-form-handler',
				'skateClubForm',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}
}
