<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package    Skate_Club_Screen
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$table_prefix = $wpdb->prefix;

// Drop all custom tables
$tables = array(
	"{$table_prefix}skate_raffle_winners",
	"{$table_prefix}skate_raffle_entries",
	"{$table_prefix}skate_spinner_entries",
	"{$table_prefix}skate_media",
	"{$table_prefix}skate_votes",
	"{$table_prefix}skate_voting_lists",
	"{$table_prefix}skate_song_requests",
	"{$table_prefix}skate_sessions",
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// Delete options
delete_option( 'skate_club_db_version' );
delete_option( 'skate_club_activated' );

// Delete all transients
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_skate_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_skate_%'" );

// Optionally delete uploaded files (commented out for safety)
/*
$upload_dir = wp_upload_dir();
$skate_dir = $upload_dir['basedir'] . '/skate-club';
if ( file_exists( $skate_dir ) ) {
	// Delete directory recursively
	array_map( 'unlink', glob( "$skate_dir/*.*" ) );
	rmdir( $skate_dir );
}
*/
