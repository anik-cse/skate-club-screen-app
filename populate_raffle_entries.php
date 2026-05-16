<?php
// Define WP_USE_THEMES to false, we don't need the theme loading
define( 'WP_USE_THEMES', false );

// Load WordPress Core
// Adjust path as necessary - assuming this file is in wp-content/plugins/skate-club-screen-app/
if ( file_exists( dirname( __FILE__ ) . '/../../../wp-load.php' ) ) {
	require_once dirname( __FILE__ ) . '/../../../wp-load.php';
} else {
	die( 'Could not find wp-load.php. Please ensure this script is in the correct plugin directory.' );
}

// Check permissions - strictly developers only (or check capability)
if ( ! current_user_can( 'manage_options' ) ) {
	// Optional: die('Access Denied'); 
	// For local dev convenience, we might skip this or ensure we are logged in.
	echo "Note: Running as unauthenticated or non-admin user if not logged in.<br>";
}

// Get Active Session
$session = Skate_Club_Session_Manager::get_active_session();

if ( ! $session ) {
	die( 'No active session found. Please activate a session in the Session Manager first.' );
}

echo "Found Active Session: " . esc_html( $session->session_name ) . " (ID: " . $session->id . ")<br>";
echo "Starting population of 50 test entries...<br><hr>";

$count = 0;
for ( $i = 1; $i <= 50; $i ++ ) {
	$fake_data = array(
		'session_id' => $session->id,
		'first_name' => 'Skater',
		'last_name'  => 'Test' . $i,
		'email'      => 'skater' . $i . '@example.com',
		'phone'      => '555-01' . str_pad( $i, 2, '0', STR_PAD_LEFT ),
	);

	// Direct DB Insert to bypass potential duplicates if re-running, 
	// OR use the safe submit_entry method to test logic.
	// Using submit_entry is better to ensure validation passes, 
	// but might fail if "skater1@example.com" already exists in this session.
	// We'll try submit, and if it fails (duplicate), we'll skip.
	
	$response = Skate_Club_Raffle_Draw::submit_entry( $fake_data );

	if ( $response['success'] ) {
		echo "Entry $i: Success (ID: " . $response['id'] . ")<br>";
		$count++;
	} else {
		echo "Entry $i: Failed - " . $response['message'] . "<br>";
	}
}

echo "<hr>Done! Successfully added $count fake entries.";
