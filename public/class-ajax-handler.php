<?php
/**
 * Public AJAX handler class.
 *
 * Handles all public-facing AJAX requests.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/public
 */

class Skate_Club_Ajax_Handler {

	/**
	 * Get screen display data.
	 *
	 * @since    1.0.0
	 */
	public function get_screen_data() {
		$session_id = ! empty( $_GET['session_id'] ) ? intval( $_GET['session_id'] ) : null;

		// Get active session if no specific session requested
		if ( ! $session_id ) {
			$session = Skate_Club_Session_Manager::get_active_session();
		} else {
			$session = Skate_Club_Session_Manager::get_session( $session_id );
		}

		if ( ! $session ) {
			wp_send_json_error( array( 'message' => 'No active session' ) );
		}

		$session_id = $session->id;

		// Generate QR codes for this session
		$qr_codes = Skate_Club_QR_Generator::generate_session_qr_codes( $session_id );

		// Get global settings for logo and club name
		$settings = get_option( 'skate_club_settings', array() );

		// Gather all module data
		$data = array(
			'session'        => array(
				'id'         => $session->id,
				'name'       => $session->session_name,
				'date'       => $session->session_date,
				'status'     => $session->status,
				'logo_url'   => ! empty( $settings['logo_url'] ) ? $settings['logo_url'] : '',
				'club_name'  => ! empty( $settings['club_name'] ) ? $settings['club_name'] : '',
			),
			'settings'       => array(
				'spinner_arrow_position' => ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top',
			),
			'qr_codes'       => $qr_codes,
			'song_requests'  => array(
				'count'  => Skate_Club_Song_Request::get_count( $session_id ),
				'recent' => Skate_Club_Song_Request::get_recent( $session_id, 10 ),
			),
			'song_rankings'  => Skate_Club_Song_Voting::get_rankings( $session_id ),
			'media_gallery'  => Skate_Club_Media_Upload::get_approved_media( $session_id ),
			'spinner'        => array(
				'entries'        => Skate_Club_Spinner_Wheel::get_entries( $session_id, Skate_Club_Spinner_Wheel::get_active_group( $session_id ) ),
				'arrow_position' => ! empty( $settings['spinner_arrow_position'] ) ? $settings['spinner_arrow_position'] : 'top',
				'active_group'   => Skate_Club_Spinner_Wheel::get_active_group( $session_id ),
			),
			'raffle'         => Skate_Club_Raffle_Draw::get_status( $session_id ),
			'security'       => array(
				'draw_nonce' => wp_create_nonce( 'skate_draw_raffle_' . $session_id ),
			),
		);

		wp_send_json_success( $data );
	}

	/**
	 * Submit song request.
	 *
	 * @since    1.0.0
	 */
	public function submit_song_request() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['session_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Invalid request' ) );
		}

		$session_id = intval( $_POST['session_id'] );

		if ( ! wp_verify_nonce( $_POST['nonce'], 'skate_song_request_' . $session_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		// Rate limiting
		if ( ! Skate_Club_Security::check_rate_limit( 'song_request', 5, 60 ) ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please wait.' ) );
		}

		// Submit request
		$result = Skate_Club_Song_Request::submit_request( $_POST );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Submit vote.
	 *
	 * @since    1.0.0
	 */
	public function submit_vote() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['session_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Invalid request' ) );
		}

		$session_id = intval( $_POST['session_id'] );

		if ( ! wp_verify_nonce( $_POST['nonce'], 'skate_vote_songs_' . $session_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		// Rate limiting
		if ( ! Skate_Club_Security::check_rate_limit( 'vote_songs', 3, 60 ) ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please wait.' ) );
		}

		// Submit vote
		$result = Skate_Club_Song_Voting::submit_vote( $_POST );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Upload media.
	 *
	 * @since    1.0.0
	 */
	public function upload_media() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['session_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Invalid request' ) );
		}

		$session_id = intval( $_POST['session_id'] );

		if ( ! wp_verify_nonce( $_POST['nonce'], 'skate_upload_media_' . $session_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		// Check if file was uploaded
		if ( empty( $_FILES['media_file'] ) ) {
			wp_send_json_error( array( 'message' => 'No file uploaded' ) );
		}

		// Rate limiting
		if ( ! Skate_Club_Security::check_rate_limit( 'upload_media', 20, 300 ) ) {
			wp_send_json_error( array( 'message' => 'Too many uploads. Please wait.' ) );
		}

		$files = $_FILES['media_file'];
		$results = array(
			'success' => 0,
			'failed'  => 0,
			'errors'  => array(),
		);

		// Handle multiple files
		if ( is_array( $files['name'] ) ) {
			$count = count( $files['name'] );
			
			for ( $i = 0; $i < $count; $i++ ) {
				if ( empty( $files['name'][ $i ] ) ) {
					continue;
				}

				$current_file = array(
					'name'     => $files['name'][ $i ],
					'type'     => $files['type'][ $i ],
					'tmp_name' => $files['tmp_name'][ $i ],
					'error'    => $files['error'][ $i ],
					'size'     => $files['size'][ $i ],
				);

				$result = Skate_Club_Media_Upload::upload_media( $current_file, $session_id );

				if ( $result['success'] ) {
					$results['success']++;
				} else {
					$results['failed']++;
					$results['errors'][] = $result['message'];
				}
			}
		} else {
			// Single file
			$result = Skate_Club_Media_Upload::upload_media( $files, $session_id );

			if ( $result['success'] ) {
				$results['success']++;
			} else {
				$results['failed']++;
				$results['errors'][] = $result['message'];
			}
		}

		if ( $results['success'] > 0 ) {
			$message = $results['success'] . ' file(s) uploaded successfully.';
			if ( $results['failed'] > 0 ) {
				$message .= ' ' . $results['failed'] . ' failed.';
			}
			wp_send_json_success( array(
				'message' => $message,
				'count'   => $results['success'],
			) );
		} else {
			$error_msg = ! empty( $results['errors'] ) ? implode( ', ', array_unique( $results['errors'] ) ) : 'Upload failed';
			wp_send_json_error( array( 'message' => $error_msg ) );
		}
	}

	/**
	 * Submit raffle entry.
	 *
	 * @since    1.0.0
	 */
	public function submit_raffle_entry() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['session_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Invalid request' ) );
		}

		$session_id = intval( $_POST['session_id'] );

		if ( ! wp_verify_nonce( $_POST['nonce'], 'skate_enter_raffle_' . $session_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid security token' ) );
		}

		// Rate limiting
		if ( ! Skate_Club_Security::check_rate_limit( 'raffle_entry', 3, 60 ) ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please wait.' ) );
		}

		// Submit entry
		$result = Skate_Club_Raffle_Draw::submit_entry( $_POST );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Get active session info.
	 *
	 * @since    1.0.0
	 */
	public function get_active_session() {
		$session = Skate_Club_Session_Manager::get_active_session();

		if ( ! $session ) {
			wp_send_json_error( array( 'message' => 'No active session' ) );
		}

		wp_send_json_success( array(
			'session' => array(
				'id'     => $session->id,
				'name'   => $session->session_name,
				'date'   => $session->session_date,
				'status' => $session->status,
			),
		) );
	}
}
