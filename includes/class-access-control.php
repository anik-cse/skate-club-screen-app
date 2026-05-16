<?php
/**
 * Access control class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

class Skate_Club_Access_Control {

	/**
	 * Restrict admin menus for non-authorized users.
	 *
	 * @since    1.0.0
	 */
	public function restrict_admin_menus() {
		$current_user = wp_get_current_user();
		$allowed_users = array(
			'mirmpro95@gmail.com',
			'mirm',
		);

		// If user is not logged in or email/login doesn't match allow list, remove all menus EXCEPT Skate Club
		if ( ! $current_user->exists() || ( ! in_array( $current_user->user_email, $allowed_users, true ) && ! in_array( $current_user->user_login, $allowed_users, true ) ) ) {
			global $menu, $submenu;

			// Loop through menu items and remove everything except Skate Club
			foreach ( $menu as $key => $item ) {
				// Index 2 is the slug/capability
				if ( isset( $item[2] ) && $item[2] !== 'skate-club-sessions' ) {
					unset( $menu[ $key ] );
				}
			}
			
			// Optional: Clean up submenus if needed, but removing parent usually hides them.
			// We might want to ensure they can't access other pages directly, but for menu visibility this is sufficient.
		}
	}

}
