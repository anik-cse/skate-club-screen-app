<?php
/**
 * Admin AJAX handler class.
 *
 * Handles all admin-only AJAX requests.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin
 */

class Skate_Club_Admin_Ajax {

	/**
	 * Create a new session.
	 *
	 * @since    1.0.0
	 */
	public function create_session() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$session_id = Skate_Club_Session_Manager::create_session( $_POST );

		if ( $session_id ) {
			wp_send_json_success( array(
				'session_id' => $session_id,
				'message'    => 'Session created successfully',
			) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to create session' ) );
		}
	}

	/**
	 * Activate a session.
	 *
	 * @since    1.0.0
	 */
	public function activate_session() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$session_id = intval( $_POST['session_id'] );

		$result = Skate_Club_Session_Manager::activate_session( $session_id );

		if ( $result ) {
			// Generate QR codes
			$qr_codes = Skate_Club_QR_Generator::generate_session_qr_codes( $session_id );

			wp_send_json_success( array(
				'message'  => 'Session activated',
				'qr_codes' => $qr_codes,
			) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to activate session' ) );
		}
	}

	/**
	 * Close a session.
	 *
	 * @since    1.0.0
	 */
	public function close_session() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$session_id = intval( $_POST['session_id'] );

		$result = Skate_Club_Session_Manager::close_session( $session_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Session closed. Frontend has been reset.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to close session' ) );
		}
	}

	/**
	 * Get sessions list.
	 *
	 * @since    1.0.0
	 */
	public function get_sessions() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$args = array(
			'status' => ! empty( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '',
			'limit'  => ! empty( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 20,
			'offset' => ! empty( $_GET['offset'] ) ? intval( $_GET['offset'] ) : 0,
		);

		$sessions = Skate_Club_Session_Manager::get_sessions( $args );

		// Add stats to each session
		foreach ( $sessions as $session ) {
			$session->stats = Skate_Club_Session_Manager::get_session_stats( $session->id );
		}

		wp_send_json_success( array( 'sessions' => $sessions ) );
	}

	/**
	 * Create voting list.
	 *
	 * @since    1.0.0
	 */
	public function create_voting_list() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$session_id = intval( $_POST['session_id'] );
		$songs = ! empty( $_POST['songs'] ) ? $_POST['songs'] : array();

		$result = Skate_Club_Song_Voting::create_voting_list( $session_id, $songs );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Get pending media.
	 *
	 * @since    1.0.0
	 */
	public function get_pending_media() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$session_id = ! empty( $_GET['session_id'] ) ? intval( $_GET['session_id'] ) : null;

		$media = Skate_Club_Media_Upload::get_pending_media( $session_id );

		wp_send_json_success( array( 'media' => $media ) );
	}

	/**
	 * Approve media.
	 *
	 * @since    1.0.0
	 */
	public function approve_media() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$media_ids = ! empty( $_POST['media_ids'] ) ? $_POST['media_ids'] : array();

		$result = Skate_Club_Media_Upload::approve_media( $media_ids );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Reject media.
	 *
	 * @since    1.0.0
	 */
	public function reject_media() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$media_ids = ! empty( $_POST['media_ids'] ) ? $_POST['media_ids'] : array();

		$result = Skate_Club_Media_Upload::reject_media( $media_ids );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Add spinner entry.
	 *
	 * @since    1.0.0
	 */
	public function add_spinner_entry() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$session_id = intval( $_POST['session_id'] );
		$name = sanitize_text_field( $_POST['participant_name'] );
		$group_name = ! empty( $_POST['group_name'] ) ? sanitize_text_field( $_POST['group_name'] ) : null;
		$color = ! empty( $_POST['color'] ) ? sanitize_text_field( $_POST['color'] ) : null;

		$result = Skate_Club_Spinner_Wheel::add_entry( $session_id, $name, $group_name, $color );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Remove spinner entry.
	 *
	 * @since    1.0.0
	 */
	public function remove_spinner_entry() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$entry_id = intval( $_POST['entry_id'] );

		$result = Skate_Club_Spinner_Wheel::remove_entry( $entry_id );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Bulk delete spinner entries.
	 *
	 * @since    1.0.1
	 */
	public function bulk_delete_spinner_entries() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$entry_ids = ! empty( $_POST['entry_ids'] ) ? $_POST['entry_ids'] : array();

		$result = Skate_Club_Spinner_Wheel::bulk_delete( $entry_ids );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Spin wheel.
	 *
	 * @since    1.0.0
	 */
	public function spin_wheel() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$session_id = intval( $_POST['session_id'] );
		$group_name = ! empty( $_POST['group_name'] ) ? sanitize_text_field( $_POST['group_name'] ) : null;

		$result = Skate_Club_Spinner_Wheel::spin_wheel( $session_id, $group_name );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Set active spinner group.
	 *
	 * @since    1.0.1
	 */
	public function set_active_spinner_group() {
		if ( ! Skate_Club_Security::is_admin() ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) wp_send_json_error( array( 'message' => 'Invalid token' ) );

		$session_id = intval( $_POST['session_id'] );
		$group_name = ! empty( $_POST['group_name'] ) ? sanitize_text_field( $_POST['group_name'] ) : null;

		$result = Skate_Club_Spinner_Wheel::set_active_group( $session_id, $group_name );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Active group updated' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to update active group' ) );
		}
	}

	/**
	 * Rename spinner group.
	 *
	 * @since    1.0.2
	 */
	public function rename_spinner_group() {
		if ( ! Skate_Club_Security::is_admin() ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) wp_send_json_error( array( 'message' => 'Invalid token' ) );

		$old_name = ! empty( $_POST['old_name'] ) ? sanitize_text_field( $_POST['old_name'] ) : null;
		$new_name = ! empty( $_POST['new_name'] ) ? sanitize_text_field( $_POST['new_name'] ) : null;

		$result = Skate_Club_Spinner_Wheel::rename_group( $old_name, $new_name );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Group renamed successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to rename group' ) );
		}
	}

	/**
	 * Update spinner entry.
	 *
	 * @since    1.0.2
	 */
	public function update_spinner_entry() {
		if ( ! Skate_Club_Security::is_admin() ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) wp_send_json_error( array( 'message' => 'Invalid token' ) );

		$entry_id = intval( $_POST['entry_id'] );
		$name = sanitize_text_field( $_POST['participant_name'] );
		$color = ! empty( $_POST['color'] ) ? sanitize_text_field( $_POST['color'] ) : null;

		$result = Skate_Club_Spinner_Wheel::update_entry( $entry_id, $name, $color );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Entry updated' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to update entry' ) );
		}
	}

	/**
	 * Close raffle.
	 *
	 * @since    1.0.0
	 */
	public function close_raffle() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$session_id = intval( $_POST['session_id'] );

		$result = Skate_Club_Raffle_Draw::close_raffle( $session_id );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Draw raffle winner.
	 *
	 * @since    1.0.0
	 */
	public function draw_raffle_winner() {
		$session_id = isset( $_POST['session_id'] ) ? intval( $_POST['session_id'] ) : 0;
		if ( ! $session_id ) {
			wp_send_json_error( array( 'message' => 'No session specified' ) );
		}

		// Verify nonce (Admin OR Public-Session-Specific)
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		$is_admin = Skate_Club_Security::is_admin();
		
		// 1. Try Admin Nonce
		$valid_admin = wp_verify_nonce( $nonce, 'skate_admin_action' );
		
		// 2. Try Public Session Nonce
		$valid_public = wp_verify_nonce( $nonce, 'skate_draw_raffle_' . $session_id );

		if ( ! $valid_admin && ! $valid_public ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		// If public nonce, ensure we are just drawing, not doing other admin stuff?
		// draw_winner is the only action here.
		
		// If Admin check failed but Public check passed, we are fine (Public Kiosk Mode).
		// If both failed, error.

		$result = Skate_Club_Raffle_Draw::draw_winner( $session_id );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Get song requests.
	 *
	 * @since    1.0.0
	 */
	public function get_song_requests() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		$session_id = ! empty( $_GET['session_id'] ) ? intval( $_GET['session_id'] ) : null;

		if ( ! $session_id ) {
			$active_session = Skate_Club_Session_Manager::get_active_session();
			$session_id = $active_session ? $active_session->id : null;
		}

		if ( ! $session_id ) {
			wp_send_json_error( array( 'message' => 'No session specified' ) );
		}

		$args = array(
			'limit'  => ! empty( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 50,
			'offset' => ! empty( $_GET['offset'] ) ? intval( $_GET['offset'] ) : 0,
		);

		$requests = Skate_Club_Song_Request::get_requests( $session_id, $args );
		$total = Skate_Club_Song_Request::get_count( $session_id );

		wp_send_json_success( array(
			'requests' => $requests,
			'total'    => $total,
		) );
	}

	/**
	 * Save settings.
	 *
	 * @since    1.0.0
	 */
	public function save_settings() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$settings = array(
			'club_name'                 => ! empty( $_POST['club_name'] ) ? sanitize_text_field( $_POST['club_name'] ) : '',
			'logo_url'                  => ! empty( $_POST['logo_url'] ) ? esc_url_raw( $_POST['logo_url'] ) : '',
			'default_session_duration'  => ! empty( $_POST['default_session_duration'] ) ? intval( $_POST['default_session_duration'] ) : 4,
			'screen_refresh_interval'   => ! empty( $_POST['screen_refresh_interval'] ) ? intval( $_POST['screen_refresh_interval'] ) : 5,
			'media_rotation_interval'   => ! empty( $_POST['media_rotation_interval'] ) ? intval( $_POST['media_rotation_interval'] ) : 5,
			'show_top_panel'            => ! empty( $_POST['show_top_panel'] ) ? 1 : 0,
			'spinner_arrow_position'    => ! empty( $_POST['spinner_arrow_position'] ) ? sanitize_text_field( $_POST['spinner_arrow_position'] ) : 'top',
			'spinner_wheel_label'       => ! empty( $_POST['spinner_wheel_label'] ) ? sanitize_text_field( $_POST['spinner_wheel_label'] ) : 'Participant',
			'song_requests_recent_limit'=> ! empty( $_POST['song_requests_recent_limit'] ) ? intval( $_POST['song_requests_recent_limit'] ) : 5,
			'song_rankings_limit'       => ! empty( $_POST['song_rankings_limit'] ) ? intval( $_POST['song_rankings_limit'] ) : 10,
			'max_image_size'            => ! empty( $_POST['max_image_size'] ) ? intval( $_POST['max_image_size'] ) : 10,
			'max_video_size'            => ! empty( $_POST['max_video_size'] ) ? intval( $_POST['max_video_size'] ) : 50,
		);

		$result = update_option( 'skate_club_settings', $settings );

		if ( $result !== false ) {
			wp_send_json_success( array( 'message' => 'Settings saved successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to save settings' ) );
		}
	}

	/**
	 * Export user data as CSV.
	 *
	 * @since    1.0.0
	 */
	public function export_user_data() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_die( 'Unauthorized' );
		}

		// Verify nonce
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'skate_admin_action' ) ) {
			wp_die( 'Invalid security token' );
		}

		$session_id = ! empty( $_GET['session_id'] ) ? intval( $_GET['session_id'] ) : 0;

		if ( ! $session_id ) {
			wp_die( 'Invalid session ID' );
		}

		// Get session
		$session = Skate_Club_Session_Manager::get_session( $session_id );
		if ( ! $session ) {
			wp_die( 'Session not found' );
		}

		// Get data
		$song_requests  = Skate_Club_Song_Request::get_requests( $session_id, array( 'limit' => 10000 ) );
		$raffle_entries = Skate_Club_Raffle_Draw::get_entries( $session_id, array( 'limit' => 10000 ) );

		// Consolidate unique users
		$users = array();

		foreach ( $song_requests as $request ) {
			$key = strtolower( trim( $request->email ) );
			if ( ! isset( $users[ $key ] ) ) {
				$users[ $key ] = array(
					'first_name'    => $request->first_name,
					'last_name'     => $request->last_name,
					'email'         => $request->email,
					'phone'         => $request->phone,
					'song_request'  => 'Yes',
					'raffle_entry'  => 'No',
					'first_seen'    => $request->submitted_at,
				);
			} else {
				$users[ $key ]['song_request'] = 'Yes';
			}
		}

		foreach ( $raffle_entries as $entry ) {
			$key = strtolower( trim( $entry->email ) );
			if ( ! isset( $users[ $key ] ) ) {
				$users[ $key ] = array(
					'first_name'    => $entry->first_name,
					'last_name'     => $entry->last_name,
					'email'         => $entry->email,
					'phone'         => $entry->phone,
					'song_request'  => 'No',
					'raffle_entry'  => 'Yes',
					'first_seen'    => $entry->submitted_at,
				);
			} else {
				$users[ $key ]['raffle_entry'] = 'Yes';
			}
		}

		// Prepare CSV
		$filename = 'user-data-' . sanitize_file_name( $session->session_name ) . '-' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		// CSV header
		fputcsv( $output, array(
			'First Name',
			'Last Name',
			'Email',
			'Phone',
			'Song Request',
			'Raffle Entry',
			'First Seen',
		) );

		// CSV rows
		foreach ( $users as $user ) {
			fputcsv( $output, array(
				$user['first_name'],
				$user['last_name'],
				$user['email'],
				$user['phone'],
				$user['song_request'],
				$user['raffle_entry'],
				date( 'Y-m-d H:i:s', strtotime( $user['first_seen'] ) ),
			) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Export song requests as CSV.
	 *
	 * @since    1.0.0
	 */
	public function export_song_requests() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_die( 'Unauthorized' );
		}

		// Verify nonce
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'skate_admin_action' ) ) {
			wp_die( 'Invalid security token' );
		}

		$session_id = ! empty( $_GET['session_id'] ) ? intval( $_GET['session_id'] ) : 0;

		if ( ! $session_id ) {
			wp_die( 'Invalid session ID' );
		}

		$session = Skate_Club_Session_Manager::get_session( $session_id );
		if ( ! $session ) {
			wp_die( 'Session not found' );
		}

		$song_requests = Skate_Club_Song_Request::get_requests( $session_id, array( 'limit' => 10000 ) );

		$filename = 'song-requests-' . sanitize_file_name( $session->session_name ) . '-' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		// CSV header
		fputcsv( $output, array( 'ID', 'Song Name', 'Artist', 'First Name', 'Last Name', 'Email', 'Phone', 'Submitted At' ) );

		// CSV rows
		foreach ( $song_requests as $request ) {
			fputcsv( $output, array(
				$request->id,
				$request->song_name,
				! empty( $request->artist ) ? $request->artist : '',
				$request->first_name,
				$request->last_name,
				$request->email,
				$request->phone,
				date( 'Y-m-d H:i:s', strtotime( $request->submitted_at ) ),
			) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Remove song request.
	 *
	 * @since    1.0.0
	 */
	public function remove_song_request() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$request_id = intval( $_POST['request_id'] );

		global $wpdb;
		$table_name = $wpdb->prefix . 'skate_song_requests';
		$result = $wpdb->delete( $table_name, array( 'id' => $request_id ), array( '%d' ) );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Song request removed' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to remove song request' ) );
		}
	}

	/**
	 * Bulk delete song requests.
	 *
	 * @since    1.0.0
	 */
	public function bulk_delete_requests() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$request_ids = ! empty( $_POST['request_ids'] ) ? $_POST['request_ids'] : array();

		if ( empty( $request_ids ) || ! is_array( $request_ids ) ) {
			wp_send_json_error( array( 'message' => 'No items selected' ) );
		}

		// Sanitize IDs
		$ids = array_map( 'intval', $request_ids );
		$ids_sql = implode( ',', $ids );

		global $wpdb;
		$table_name = $wpdb->prefix . 'skate_song_requests';
		
		// Use prepare ONLY if we weren't doing IN clause manually (WPDB doesn't have a canned IN preparer universally)
		// Since we cast to intval above, it IS safe.
		$result = $wpdb->query( "DELETE FROM $table_name WHERE id IN ($ids_sql)" );

		if ( $result !== false ) {
			wp_send_json_success( array( 'message' => 'Song requests deleted' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to delete song requests' ) );
		}
	}

	/**
	 * Bulk delete voting songs.
	 *
	 * @since    1.0.0
	 */
	public function bulk_delete_voting_songs() {
		if ( ! Skate_Club_Security::is_admin() ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) wp_send_json_error( array( 'message' => 'Invalid token' ) );

		$song_ids = ! empty( $_POST['song_ids'] ) ? $_POST['song_ids'] : array();
		if ( empty( $song_ids ) || ! is_array( $song_ids ) ) wp_send_json_error( array( 'message' => 'No items selected' ) );

		$ids = array_map( 'intval', $song_ids );
		$ids_sql = implode( ',', $ids );

		global $wpdb;
		$table_name = $wpdb->prefix . 'skate_voting_lists';
		
		// Clear cache for affected sessions (just grab one for simplicity or clear all relevant)
		// Better: select session_ids from deleted rows
		$sessions = $wpdb->get_col( "SELECT DISTINCT session_id FROM $table_name WHERE id IN ($ids_sql)" );
		foreach( $sessions as $sid ) {
			delete_transient( 'skate_rankings_' . $sid );
		}

		$result = $wpdb->query( "DELETE FROM $table_name WHERE id IN ($ids_sql)" );

		if ( $result !== false ) {
			wp_send_json_success( array( 'message' => 'Songs removed' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to remove songs' ) );
		}
	}

	/**
	 * Bulk delete raffle entries.
	 *
	 * @since    1.0.0
	 */
	public function bulk_delete_raffle_entries() {
		if ( ! Skate_Club_Security::is_admin() ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) wp_send_json_error( array( 'message' => 'Invalid token' ) );

		$entry_ids = ! empty( $_POST['entry_ids'] ) ? $_POST['entry_ids'] : array();
		if ( empty( $entry_ids ) || ! is_array( $entry_ids ) ) wp_send_json_error( array( 'message' => 'No items selected' ) );

		$ids = array_map( 'intval', $entry_ids );
		$ids_sql = implode( ',', $ids );

		global $wpdb;
		$table_name = $wpdb->prefix . 'skate_raffle_entries';
		
		$result = $wpdb->query( "DELETE FROM $table_name WHERE id IN ($ids_sql)" );

		if ( $result !== false ) {
			wp_send_json_success( array( 'message' => 'Raffle entries deleted' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to delete raffle entries' ) );
		}
	}

	/**
	 * Bulk delete media.
	 *
	 * @since    1.0.0
	 */
	public function bulk_delete_media() {
		if ( ! Skate_Club_Security::is_admin() ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) wp_send_json_error( array( 'message' => 'Invalid token' ) );

		$media_ids = ! empty( $_POST['media_ids'] ) ? $_POST['media_ids'] : array();
		if ( empty( $media_ids ) || ! is_array( $media_ids ) ) wp_send_json_error( array( 'message' => 'No items selected' ) );

		$deleted_count = 0;
		foreach ( $media_ids as $id ) {
			if ( Skate_Club_Media_Upload::delete_media( intval( $id ) ) ) {
				$deleted_count++;
			}
		}

		if ( $deleted_count > 0 ) {
			wp_send_json_success( array( 'message' => "{$deleted_count} media items deleted" ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to delete media' ) );
		}
	}

	/**
	 * Remove song from voting list.
	 *
	 * @since    1.0.0
	 */
	public function remove_voting_song() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$song_id = intval( $_POST['song_id'] );

		// Get session_id before deleting for cache clearing
		global $wpdb;
		$table_name = $wpdb->prefix . 'skate_voting_lists';
		$song = $wpdb->get_row( $wpdb->prepare( "SELECT session_id FROM {$table_name} WHERE id = %d", $song_id ) );

		$result = $wpdb->delete( $table_name, array( 'id' => $song_id ), array( '%d' ) );

		if ( $result ) {
			// Clear rankings cache if song was found
			if ( $song && $song->session_id ) {
				delete_transient( 'skate_rankings_' . $song->session_id );
			}
			wp_send_json_success( array( 'message' => 'Song removed from voting list' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to remove song' ) );
		}
	}

	/**
	 * Remove raffle entry.
	 *
	 * @since    1.0.0
	 */
	public function remove_raffle_entry() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$entry_id = intval( $_POST['entry_id'] );

		global $wpdb;
		$table_name = $wpdb->prefix . 'skate_raffle_entries';
		$result = $wpdb->delete( $table_name, array( 'id' => $entry_id ), array( '%d' ) );

		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Raffle entry removed' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to remove raffle entry' ) );
		}
	}

	/**
	 * Remove media item.
	 *
	 * @since    1.0.0
	 */
	public function remove_media() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'skate_admin_action' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		$media_id = intval( $_POST['media_id'] );

		if ( Skate_Club_Media_Upload::delete_media( $media_id ) ) {
			wp_send_json_success( array( 'message' => 'Media removed' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to remove media' ) );
		}
	}

	/**
	 * Export raffle entries as CSV.
	 *
	 * @since    1.0.0
	 */
	public function export_raffle_entries() {
		// Check admin permission
		if ( ! Skate_Club_Security::is_admin() ) {
			wp_die( 'Unauthorized' );
		}

		// Verify nonce
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'skate_admin_action' ) ) {
			wp_die( 'Invalid security token' );
		}

		$session_id = ! empty( $_GET['session_id'] ) ? intval( $_GET['session_id'] ) : 0;

		if ( ! $session_id ) {
			wp_die( 'Invalid session ID' );
		}

		$session = Skate_Club_Session_Manager::get_session( $session_id );
		if ( ! $session ) {
			wp_die( 'Session not found' );
		}

		$raffle_entries = Skate_Club_Raffle_Draw::get_entries( $session_id, array( 'limit' => 10000 ) );
		$raffle_status  = Skate_Club_Raffle_Draw::get_status( $session_id );

		$filename = 'raffle-entries-' . sanitize_file_name( $session->session_name ) . '-' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		// CSV header
		fputcsv( $output, array( 'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Submitted At', 'Winner' ) );

		// CSV rows
		foreach ( $raffle_entries as $entry ) {
			$is_winner = ( $raffle_status['winner'] && $raffle_status['winner']->id == $entry->id ) ? 'Yes' : 'No';

			fputcsv( $output, array(
				$entry->id,
				$entry->first_name,
				$entry->last_name,
				$entry->email,
				$entry->phone,
				date( 'Y-m-d H:i:s', strtotime( $entry->submitted_at ) ),
				$is_winner,
			) );
		}

		fclose( $output );
		exit;
	}
}
