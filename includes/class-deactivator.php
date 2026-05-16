<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

class Skate_Club_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Flushes rewrite rules and performs cleanup.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();

		// Remove activation flag
		delete_option( 'skate_club_activated' );

		// Note: We don't delete database tables or uploaded files
		// This preserves historical data in case of reactivation
	}
}
