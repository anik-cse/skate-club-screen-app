<?php
/**
 * Raffle Draw module class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes/modules
 */

class Skate_Club_Raffle_Draw {

	/**
	 * Submit raffle entry.
	 *
	 * @since    1.0.0
	 * @param    array    $data    Entry data.
	 * @return   array    Response array.
	 */
	public static function submit_entry( $data ) {
		// Validate required fields
		$required = array( 'session_id', 'first_name', 'last_name' );

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return array(
					'success' => false,
					'message' => 'All fields are required',
				);
			}
		}

		// Verify session is active
		if ( ! Skate_Club_Session_Manager::is_session_active( $data['session_id'] ) ) {
			return array(
				'success' => false,
				'message' => 'Session is not active',
			);
		}

		// Check if raffle is still open
		if ( ! self::is_raffle_open( $data['session_id'] ) ) {
			return array(
				'success' => false,
				'message' => 'Raffle entries are closed',
			);
		}

		// Create entry identifier
		// Use email+phone if available, otherwise use name (less secure but required if info hidden)
		if ( ! empty( $data['email'] ) && ! empty( $data['phone'] ) ) {
			$entry_identifier = Skate_Club_Security::create_identifier( $data['email'], $data['phone'] );
		} else {
			// Fallback identifier using name + session + timestamp component to allow duplicates or just name?
			// If we want to allow multiple entries per person if they don't provide unique info, we can just use a unique ID.
			// But usually raffle wants one per person.
			// Let's use name as identifier for now, implying if you use same name you are same person.
			$entry_identifier = md5( $data['first_name'] . $data['last_name'] . $data['session_id'] );
		}

		// Check if already entered
		$existing = Skate_Club_Database::get_row(
			'raffle_entries',
			array(
				'session_id'       => intval( $data['session_id'] ),
				'entry_identifier' => $entry_identifier,
			)
		);

		if ( $existing ) {
			return array(
				'success' => false,
				'message' => 'You have already entered this raffle',
			);
		}

		// Sanitize and insert entry
		$entry_data = array(
			'session_id'       => intval( $data['session_id'] ),
			'first_name'       => Skate_Club_Security::sanitize_text( $data['first_name'] ),
			'last_name'        => Skate_Club_Security::sanitize_text( $data['last_name'] ),
			'email'            => ! empty( $data['email'] ) ? Skate_Club_Security::sanitize_email( $data['email'] ) : '',
			'phone'            => ! empty( $data['phone'] ) ? Skate_Club_Security::sanitize_text( $data['phone'] ) : '',
			'entry_identifier' => $entry_identifier,
		);

		$id = Skate_Club_Database::insert( 'raffle_entries', $entry_data );

		if ( $id === false ) {
			return array(
				'success' => false,
				'message' => 'Failed to save raffle entry',
			);
		}

		return array(
			'success' => true,
			'message' => "You're entered in the raffle! Good luck!",
			'id'      => $id,
		);
	}

	/**
	 * Get raffle entries for a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @param    array    $args          Query arguments.
	 * @return   array    Raffle entries.
	 */
	public static function get_entries( $session_id, $args = array() ) {
		$defaults = array(
			'limit'    => 100,
			'offset'   => 0,
			'order_by' => 'submitted_at',
			'order'    => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		return Skate_Club_Database::get_results(
			'raffle_entries',
			array( 'session_id' => intval( $session_id ) ),
			$args
		);
	}

	/**
	 * Get raffle entry count.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   int      Entry count.
	 */
	public static function get_entry_count( $session_id ) {
		return Skate_Club_Database::get_count( 'raffle_entries', array( 'session_id' => intval( $session_id ) ) );
	}

	/**
	 * Check if raffle is open for entries.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   bool     True if open (always true now - multiple winners allowed).
	 */
	public static function is_raffle_open( $session_id ) {
		// Raffle is always open for entries - multiple winners can be drawn
		return true;
	}

	/**
	 * Close raffle (prevent new entries).
	 *
	 * This is implicit - once a winner is drawn, raffle is closed.
	 * We can also add a session setting for explicit closure.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   array    Response array.
	 */
	public static function close_raffle( $session_id ) {
		$entry_count = self::get_entry_count( $session_id );

		return array(
			'success'     => true,
			'message'     => "Raffle closed. {$entry_count} total entries.",
			'entry_count' => $entry_count,
		);
	}

	/**
	 * Draw a raffle winner.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   array    Response array with winner.
	 */
	public static function draw_winner( $session_id ) {
		// Get all entries
		$entries = self::get_entries( $session_id, array( 'limit' => 10000 ) );

		if ( empty( $entries ) ) {
			return array(
				'success' => false,
				'message' => 'No entries in raffle',
			);
		}

		// Randomly select winner
		$winner_index = random_int( 0, count( $entries ) - 1 );
		$winner_entry = $entries[ $winner_index ];

		// Save winner
		$winner_data = array(
			'session_id'      => intval( $session_id ),
			'raffle_entry_id' => $winner_entry->id,
		);

		$winner_id = Skate_Club_Database::insert( 'raffle_winners', $winner_data );

		if ( $winner_id === false ) {
			return array(
				'success' => false,
				'message' => 'Failed to save winner',
			);
		}

		return array(
			'success' => true,
			'message' => 'Winner selected!',
			'winner'  => array(
				'id'         => $winner_entry->id,
				'first_name' => $winner_entry->first_name,
				'last_name'  => $winner_entry->last_name,
				'email'      => $winner_entry->email,
				'phone'      => $winner_entry->phone,
			),
		);
	}

	/**
	 * Get raffle winner for a session (latest winner).
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   object|null Winner data or null.
	 */
	public static function get_winner( $session_id ) {
		global $wpdb;

		$winners_table = Skate_Club_Database::get_table_name( 'raffle_winners' );
		$entries_table = Skate_Club_Database::get_table_name( 'raffle_entries' );

		$query = $wpdb->prepare(
			"SELECT e.*, w.selected_at
			FROM {$winners_table} w
			INNER JOIN {$entries_table} e ON w.raffle_entry_id = e.id
			WHERE w.session_id = %d
			ORDER BY w.selected_at DESC
			LIMIT 1",
			$session_id
		);

		return $wpdb->get_row( $query );
	}

	/**
	 * Get raffle status.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   array    Raffle status information.
	 */
	public static function get_status( $session_id ) {
		$winner = self::get_winner( $session_id );
		$entry_count = self::get_entry_count( $session_id );
		$entries = self::get_entries( $session_id, array( 'limit' => 1000 ) );

		return array(
			'is_open'     => true, // Always open for entries
			'entry_count' => $entry_count,
			'winner'      => $winner,
			'entries'     => $entries,
		);
	}

	/**
	 * Delete raffle winner (to allow redraw).
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   bool     Success status.
	 */
	public static function reset_winner( $session_id ) {
		$result = Skate_Club_Database::delete( 'raffle_winners', array( 'session_id' => intval( $session_id ) ) );
		return $result !== false;
	}
}
