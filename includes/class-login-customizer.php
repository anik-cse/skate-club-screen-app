<?php

/**
 * Fired during plugin activation
 *
 * @link       https://example.com/skate-club-screen-app
 * @since      1.0.0
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines the functionality to customize the WordPress login page.
 *
 * @since      1.0.0
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 * @author     Mir M <mir@example.com>
 */
class Skate_Club_Login_Customizer {

	/**
	 * Enqueue custom styles for the login page.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_login_styles() {
		wp_enqueue_style( 'skate-club-login-css', SKATE_CLUB_PLUGIN_URL . 'public/assets/css/skate-club-login.css', array(), SKATE_CLUB_VERSION, 'all' );
	}

	/**
	 * Change the login logo URL to the site home page.
	 *
	 * @since    1.0.0
	 * @return   string    The site home page URL.
	 */
	public function change_login_logo_url() {
		return home_url();
	}

	/**
	 * Change the login logo title attribute.
	 *
	 * @since    1.0.0
	 * @return   string    The logo title.
	 */
	public function change_login_logo_title() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Redirect user after successful login.
	 *
	 * @since    1.0.0
	 * @param    string    $redirect_to    The redirect destination URL.
	 * @param    string    $request        The requested redirect destination URL sent as a parameter.
	 * @param    WP_User   $user           WP_User object if login was successful, false otherwise.
	 * @return   string    The redirect URL.
	 */
	public function redirect_after_login( $redirect_to, $request, $user ) {
		// Is there a user to check?
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			// Redirect to the Skate Club Sessions page
			return admin_url( 'admin.php?page=skate-club-sessions' );
		}

		return $redirect_to;
	}
}
