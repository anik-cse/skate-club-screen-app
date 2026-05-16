<?php
/**
 * Plugin Name: Skate Club Interactive Screen Application
 * Plugin URI: https://example.com/skate-club-screen-app
 * Description: Interactive screen application for skate club with QR code-based user engagement, song requests, voting, media uploads, spinner wheel, and raffle draws.
 * Version: 1.1.0
 * Author: Mir M
 * Author URI: https://mirm.pro/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: skate-club-screen
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Load Composer autoloader.
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Current plugin version.
 */
define( 'SKATE_CLUB_VERSION', '1.1.0' );
define( 'SKATE_CLUB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SKATE_CLUB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SKATE_CLUB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_skate_club_screen_app() {
	require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-activator.php';
	Skate_Club_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_skate_club_screen_app() {
	require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-deactivator.php';
	Skate_Club_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_skate_club_screen_app' );
register_deactivation_hook( __FILE__, 'deactivate_skate_club_screen_app' );

/**
 * The core plugin class.
 */
require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-skate-club-core.php';

/**
 * Begins execution of the plugin.
 */
function run_skate_club_screen_app() {
	$plugin = new Skate_Club_Core();
	$plugin->run();
}
run_skate_club_screen_app();
