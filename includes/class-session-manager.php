<?php
/**
 * Session management class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

class Skate_Club_Session_Manager {

	/**
	 * Create a new session.
	 *
	 * @since    1.0.0
	 * @param    array    $data    Session data.
	 * @return   int|false Session ID or false on failure.
	 */
	public static function create_session( $data ) {
		// Handle both old format (session_date) and new format (start_time/end_time)
		$start_time = ! empty( $data['start_time'] ) ? $data['start_time'] : ( ! empty( $data['session_date'] ) ? $data['session_date'] : '' );
		$end_time = ! empty( $data['end_time'] ) ? $data['end_time'] : null;

		// Prepare settings
		$settings = ! empty( $data['settings'] ) ? $data['settings'] : array();
		if ( $end_time ) {
			$settings['end_time'] = sanitize_text_field( $end_time );
		}

		$session_data = array(
			'session_name' => sanitize_text_field( $data['session_name'] ),
			'session_date' => sanitize_text_field( $start_time ),
			'status'       => 'draft',
			'settings'     => ! empty( $settings ) ? wp_json_encode( $settings ) : null,
		);

		return Skate_Club_Database::insert( 'sessions', $session_data );
	}

	/**
	 * Get a session by ID.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   object|null Session object or null.
	 */
	public static function get_session( $session_id ) {
		$session = Skate_Club_Database::get_row( 'sessions', array( 'id' => $session_id ) );

		if ( $session && ! empty( $session->settings ) ) {
			$session->settings = json_decode( $session->settings, true );
		}

		return $session;
	}

	/**
	 * Get active session.
	 *
	 * @since    1.0.0
	 * @return   object|null Active session or null.
	 */
	public static function get_active_session() {
		// Check transient first for performance
		$cached = get_transient( 'skate_active_session' );
		if ( $cached !== false ) {
			return $cached;
		}

		$session = Skate_Club_Database::get_row( 'sessions', array( 'status' => 'active' ) );

		if ( $session ) {
			if ( ! empty( $session->settings ) ) {
				$session->settings = json_decode( $session->settings, true );
			}
			set_transient( 'skate_active_session', $session, 5 * MINUTE_IN_SECONDS );
		}

		return $session;
	}

	/**
	 * Get all sessions.
	 *
	 * @since    1.0.0
	 * @param    array    $args    Query arguments.
	 * @return   array    Array of sessions.
	 */
	public static function get_sessions( $args = array() ) {
		$where = array();

		if ( ! empty( $args['status'] ) ) {
			$where['status'] = $args['status'];
		}

		// Default sorting
		$query_args = array(
			'order_by' => ! empty( $args['orderby'] ) ? $args['orderby'] : 'session_date',
			'order'    => ! empty( $args['order'] ) ? $args['order'] : 'DESC',
		);

		if ( ! empty( $args['limit'] ) ) {
			$query_args['limit'] = $args['limit'];
		}

		if ( ! empty( $args['offset'] ) ) {
			$query_args['offset'] = $args['offset'];
		}

		$sessions = Skate_Club_Database::get_results( 'sessions', $where, $query_args );

		// Decode settings JSON
		foreach ( $sessions as $session ) {
			if ( ! empty( $session->settings ) ) {
				$session->settings = json_decode( $session->settings, true );
			}
		}

		return $sessions;
	}

	/**
	 * Activate a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   bool     Success status.
	 */
	public static function activate_session( $session_id ) {
		// First, deactivate any currently active session
		Skate_Club_Database::update(
			'sessions',
			array( 'status' => 'closed', 'closed_at' => current_time( 'mysql' ) ),
			array( 'status' => 'active' )
		);

		// Activate the new session
		$result = Skate_Club_Database::update(
			'sessions',
			array( 'status' => 'active', 'activated_at' => current_time( 'mysql' ) ),
			array( 'id' => $session_id )
		);

		if ( $result !== false ) {
			// Clear active session transient
			delete_transient( 'skate_active_session' );
			return true;
		}

		return false;
	}

	/**
	 * Close a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   bool     Success status.
	 */
	public static function close_session( $session_id ) {
		$result = Skate_Club_Database::update(
			'sessions',
			array( 'status' => 'closed', 'closed_at' => current_time( 'mysql' ) ),
			array( 'id' => $session_id )
		);

		if ( $result !== false ) {
			// Clear active session transient
			delete_transient( 'skate_active_session' );

			// Clear all session-related transients
			self::clear_session_cache( $session_id );

			return true;
		}

		return false;
	}

	/**
	 * Update session settings.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @param    array    $settings      Settings array.
	 * @return   bool     Success status.
	 */
	public static function update_settings( $session_id, $settings ) {
		$result = Skate_Club_Database::update(
			'sessions',
			array( 'settings' => wp_json_encode( $settings ) ),
			array( 'id' => $session_id )
		);

		if ( $result !== false ) {
			delete_transient( 'skate_active_session' );
			return true;
		}

		return false;
	}

	/**
	 * Delete a session and all associated data.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   bool     Success status.
	 */
	public static function delete_session( $session_id ) {
		// Delete from all related tables (will cascade via foreign keys in MySQL)
		// But we'll manually delete for compatibility

		Skate_Club_Database::delete( 'raffle_winners', array( 'session_id' => $session_id ) );
		Skate_Club_Database::delete( 'raffle_entries', array( 'session_id' => $session_id ) );
		Skate_Club_Database::delete( 'spinner_entries', array( 'session_id' => $session_id ) );

		// Delete media files
		$media_items = Skate_Club_Database::get_results( 'media', array( 'session_id' => $session_id ) );
		foreach ( $media_items as $media ) {
			if ( file_exists( $media->file_path ) ) {
				unlink( $media->file_path );
			}
		}
		Skate_Club_Database::delete( 'media', array( 'session_id' => $session_id ) );

		Skate_Club_Database::delete( 'votes', array( 'session_id' => $session_id ) );
		Skate_Club_Database::delete( 'voting_lists', array( 'session_id' => $session_id ) );
		Skate_Club_Database::delete( 'song_requests', array( 'session_id' => $session_id ) );

		// Finally delete the session itself
		$result = Skate_Club_Database::delete( 'sessions', array( 'id' => $session_id ) );

		if ( $result !== false ) {
			self::clear_session_cache( $session_id );
			return true;
		}

		return false;
	}

	/**
	 * Get session statistics.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   array    Statistics array.
	 */
	public static function get_session_stats( $session_id ) {
		return array(
			'song_requests'  => Skate_Club_Database::get_count( 'song_requests', array( 'session_id' => $session_id ) ),
			'votes'          => Skate_Club_Database::get_count( 'votes', array( 'session_id' => $session_id ) ),
			'media_uploads'  => Skate_Club_Database::get_count( 'media', array( 'session_id' => $session_id ) ),
			'raffle_entries' => Skate_Club_Database::get_count( 'raffle_entries', array( 'session_id' => $session_id ) ),
			'spinner_entries' => Skate_Club_Database::get_count( 'spinner_entries', array( 'session_id' => $session_id ) ),
		);
	}

	/**
	 * Clear all cached data for a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 */
	public static function clear_session_cache( $session_id ) {
		delete_transient( 'skate_rankings_' . $session_id );
		delete_transient( 'skate_media_gallery_' . $session_id );
		delete_transient( 'skate_active_session' );
	}

	/**
	 * Check if session is active.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   bool     True if active, false otherwise.
	 */
	public static function is_session_active( $session_id ) {
		$session = self::get_session( $session_id );
		return $session && $session->status === 'active';
	}
}
