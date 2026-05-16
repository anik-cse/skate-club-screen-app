<?php
/**
 * Song Request module class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes/modules
 */

class Skate_Club_Song_Request {

	/**
	 * Submit a song request.
	 *
	 * @since    1.0.0
	 * @param    array    $data    Song request data.
	 * @return   array    Response array.
	 */
	public static function submit_request( $data ) {
		// Validate required fields
		$required = array( 'session_id', 'first_name', 'last_name', 'song_name' );

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

		// Sanitize data
		$request_data = array(
			'session_id'    => intval( $data['session_id'] ),
			'first_name'    => Skate_Club_Security::sanitize_text( $data['first_name'] ),
			'last_name'     => Skate_Club_Security::sanitize_text( $data['last_name'] ),
			'email'         => ! empty( $data['email'] ) ? Skate_Club_Security::sanitize_email( $data['email'] ) : '',
			'phone'         => ! empty( $data['phone'] ) ? Skate_Club_Security::sanitize_text( $data['phone'] ) : '',
			'date_of_birth' => ! empty( $data['dob'] ) ? sanitize_text_field( $data['dob'] ) : '',
			'song_name'     => Skate_Club_Security::sanitize_text( $data['song_name'] ),
		);

		// Insert into database
		$id = Skate_Club_Database::insert( 'song_requests', $request_data );

		if ( $id === false ) {
			return array(
				'success' => false,
				'message' => 'Failed to save song request',
			);
		}

		return array(
			'success' => true,
			'message' => 'Your song request has been submitted!',
			'id'      => $id,
		);
	}

	/**
	 * Get song requests for a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @param    array    $args          Query arguments.
	 * @return   array    Song requests.
	 */
	public static function get_requests( $session_id, $args = array() ) {
		$defaults = array(
			'limit'    => 50,
			'offset'   => 0,
			'order_by' => 'submitted_at',
			'order'    => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		return Skate_Club_Database::get_results(
			'song_requests',
			array( 'session_id' => $session_id ),
			$args
		);
	}

	/**
	 * Get recent song requests.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @param    int      $limit         Number of requests.
	 * @return   array    Recent requests.
	 */
	public static function get_recent( $session_id, $limit = 5 ) {
		return self::get_requests( $session_id, array( 'limit' => $limit ) );
	}

	/**
	 * Get total count of requests.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   int      Request count.
	 */
	public static function get_count( $session_id ) {
		return Skate_Club_Database::get_count( 'song_requests', array( 'session_id' => $session_id ) );
	}

	/**
	 * Delete a song request.
	 *
	 * @since    1.0.0
	 * @param    int      $request_id    Request ID.
	 * @return   bool     Success status.
	 */
	public static function delete_request( $request_id ) {
		return Skate_Club_Database::delete( 'song_requests', array( 'id' => $request_id ) ) !== false;
	}
}
