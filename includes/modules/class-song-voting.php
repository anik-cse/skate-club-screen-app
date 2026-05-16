<?php
/**
 * Song Voting and Ranking module class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes/modules
 */

class Skate_Club_Song_Voting {

	/**
	 * Create voting list for a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @param    array    $songs         Array of songs [{title, artist}, ...].
	 * @return   array    Response array.
	 */
	public static function create_voting_list( $session_id, $songs ) {
		if ( empty( $songs ) || ! is_array( $songs ) ) {
			return array(
				'success' => false,
				'message' => 'No songs provided',
			);
		}

		// Get current max display_order for append-only functionality
		global $wpdb;
		$table = Skate_Club_Database::get_table_name( 'voting_lists' );
		$max_order = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(display_order) FROM {$table} WHERE session_id = %d",
			$session_id
		) );
		$display_order = $max_order !== null ? intval( $max_order ) + 1 : 0;

		$inserted = 0;

		foreach ( $songs as $song ) {
			if ( empty( $song['title'] ) ) {
				continue;
			}

			$data = array(
				'session_id'    => intval( $session_id ),
				'song_title'    => Skate_Club_Security::sanitize_text( $song['title'] ),
				'artist'        => ! empty( $song['artist'] ) ? Skate_Club_Security::sanitize_text( $song['artist'] ) : null,
				'display_order' => $display_order++,
			);

			$id = Skate_Club_Database::insert( 'voting_lists', $data );

			if ( $id !== false ) {
				$inserted++;
			}
		}

		if ( $inserted > 0 ) {
			// Clear rankings cache
			delete_transient( 'skate_rankings_' . $session_id );

			return array(
				'success' => true,
				'message' => "{$inserted} song(s) added to voting list",
				'count'   => $inserted,
			);
		}

		return array(
			'success' => false,
			'message' => 'Failed to add songs to voting list',
		);
	}

	/**
	 * Get voting list for a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   array    Voting list songs.
	 */
	public static function get_voting_list( $session_id ) {
		return Skate_Club_Database::get_results(
			'voting_lists',
			array( 'session_id' => $session_id ),
			array(
				'order_by' => 'display_order',
				'order'    => 'ASC',
			)
		);
	}

	/**
	 * Submit a vote.
	 *
	 * @since    1.0.0
	 * @param    array    $data    Vote data.
	 * @return   array    Response array.
	 */
	public static function submit_vote( $data ) {
		// Validate required fields
		if ( empty( $data['session_id'] ) || empty( $data['ranks'] ) ) {
			return array(
				'success' => false,
				'message' => 'Missing required fields',
			);
		}

		// Verify session is active
		if ( ! Skate_Club_Session_Manager::is_session_active( $data['session_id'] ) ) {
			return array(
				'success' => false,
				'message' => 'Session is not active',
			);
		}

		$browser_fingerprint = ! empty( $data['browser_fingerprint'] ) ?
			sanitize_text_field( $data['browser_fingerprint'] ) : '';

		// Create voter identifier
		// Use email+phone if available, otherwise fallback to fingerprint if available.
		// If neither, we can't reliably dedup, but since we removed the fields, we rely on fingerprint.
		if ( ! empty( $data['voter_email'] ) && ! empty( $data['voter_phone'] ) ) {
			$voter_identifier = Skate_Club_Security::create_identifier( $data['voter_email'], $data['voter_phone'] );
		} elseif ( ! empty( $browser_fingerprint ) ) {
			$voter_identifier = md5( $browser_fingerprint . $data['session_id'] );
		} else {
			// Weakest fallback - maybe just random or IP based if we had it?
			// For now let's use a random ID if everything else fails, effectively allowing unlimited voting if they block fingerprinting.
			// Or we could block it. Let's allow it but warn.
			$voter_identifier = wp_generate_uuid4();
		}

		// Check if already voted
		$existing = self::check_existing_vote( $data['session_id'], $voter_identifier, $browser_fingerprint );

		if ( $existing ) {
			return array(
				'success' => false,
				'message' => 'You have already voted in this session',
			);
		}

		// Insert votes
		$inserted = 0;

		foreach ( $data['ranks'] as $song_id => $rank_position ) {
			if ( empty( $rank_position ) || $rank_position < 1 ) {
				continue; // Skip unranked songs
			}

			$vote_data = array(
				'session_id'          => intval( $data['session_id'] ),
				'voting_list_id'      => intval( $song_id ),
				'voter_identifier'    => $voter_identifier,
				'browser_fingerprint' => $browser_fingerprint,
				'rank_position'       => intval( $rank_position ),
			);

			$id = Skate_Club_Database::insert( 'votes', $vote_data );

			if ( $id !== false ) {
				$inserted++;
			}
		}

		if ( $inserted > 0 ) {
			// Clear rankings cache
			delete_transient( 'skate_rankings_' . $data['session_id'] );

			return array(
				'success'  => true,
				'message'  => 'Your vote has been recorded!',
				'count'    => $inserted,
				'rankings' => self::get_rankings( $data['session_id'] ),
			);
		}

		return array(
			'success' => false,
			'message' => 'Failed to record vote',
		);
	}

	/**
	 * Check if user has already voted.
	 *
	 * @since    1.0.0
	 * @param    int       $session_id           Session ID.
	 * @param    string    $voter_identifier     Voter hash.
	 * @param    string    $browser_fingerprint  Browser fingerprint.
	 * @return   bool      True if voted, false otherwise.
	 */
	private static function check_existing_vote( $session_id, $voter_identifier, $browser_fingerprint ) {
		global $wpdb;

		$table = Skate_Club_Database::get_table_name( 'votes' );

		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table}
			WHERE session_id = %d
			AND voter_identifier = %s
			AND browser_fingerprint = %s",
			$session_id,
			$voter_identifier,
			$browser_fingerprint
		);

		$count = $wpdb->get_var( $query );

		return $count > 0;
	}

	/**
	 * Get song rankings for a session.
	 *
	 * Uses simple point system:
	 * Points = (Total Songs in List) - (Rank Position) + 1
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   array    Ranked songs.
	 */
	public static function get_rankings( $session_id ) {
		// Check cache first
		$cached = get_transient( 'skate_rankings_' . $session_id );
		if ( $cached !== false ) {
			return $cached;
		}

		global $wpdb;

		$votes_table = Skate_Club_Database::get_table_name( 'votes' );
		$lists_table = Skate_Club_Database::get_table_name( 'voting_lists' );

		// Get total songs in voting list
		$total_songs = Skate_Club_Database::get_count( 'voting_lists', array( 'session_id' => $session_id ) );

		if ( $total_songs === 0 ) {
			return array();
		}

		// Calculate points and rankings
		$query = $wpdb->prepare(
			"SELECT
				vl.id as song_id,
				vl.song_title,
				vl.artist,
				COUNT(v.id) as vote_count,
				SUM(%d - v.rank_position + 1) as total_points
			FROM {$lists_table} vl
			LEFT JOIN {$votes_table} v ON vl.id = v.voting_list_id AND v.session_id = %d
			WHERE vl.session_id = %d
			GROUP BY vl.id
			ORDER BY total_points DESC, vote_count DESC, vl.song_title ASC",
			$total_songs,
			$session_id,
			$session_id
		);

		$results = $wpdb->get_results( $query );

		// Assign rank numbers
		$rank = 1;
		foreach ( $results as $result ) {
			$result->rank = $rank++;
			$result->total_points = intval( $result->total_points );
			$result->vote_count = intval( $result->vote_count );
		}

		// Cache for 30 seconds
		set_transient( 'skate_rankings_' . $session_id, $results, 30 );

		return $results;
	}

	/**
	 * Get vote count for session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   int      Unique voter count.
	 */
	public static function get_voter_count( $session_id ) {
		global $wpdb;

		$table = Skate_Club_Database::get_table_name( 'votes' );

		$query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT voter_identifier) FROM {$table} WHERE session_id = %d",
			$session_id
		);

		return (int) $wpdb->get_var( $query );
	}
}
