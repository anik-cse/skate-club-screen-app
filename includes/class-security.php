<?php
/**
 * Security utilities class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

class Skate_Club_Security {

	/**
	 * Salt for session hash generation.
	 *
	 * @var string
	 */
	const SESSION_SALT = 'skate_club_secure_session_2024';

	/**
	 * Generate secure session hash for public URLs.
	 *
	 * @since    1.0.0
	 * @param    int       $session_id    Session ID.
	 * @return   string    Session hash.
	 */
	public static function generate_session_hash( $session_id ) {
		$hash = md5( self::SESSION_SALT . $session_id );

		// Store mapping in transient (24 hours)
		set_transient( 'skate_session_hash_' . $hash, $session_id, DAY_IN_SECONDS );

		return $hash;
	}

	/**
	 * Verify and decode session hash to get session ID.
	 *
	 * @since    1.0.0
	 * @param    string    $hash    Session hash from URL.
	 * @return   int|false Session ID if valid, false otherwise.
	 */
	public static function verify_session_hash( $hash ) {
		// Validate hash format (32 char MD5)
		if ( ! preg_match( '/^[a-f0-9]{32}$/i', $hash ) ) {
			return false;
		}

		// Get session ID from transient
		$session_id = get_transient( 'skate_session_hash_' . $hash );

		if ( $session_id === false ) {
			// Try to regenerate hash for all active sessions
			global $wpdb;
			$table = $wpdb->prefix . 'skate_sessions';
			$sessions = $wpdb->get_results( "SELECT id FROM {$table} WHERE status IN ('draft', 'active')" );

			foreach ( $sessions as $session ) {
				$expected_hash = md5( self::SESSION_SALT . $session->id );
				if ( $expected_hash === $hash ) {
					// Restore transient
					set_transient( 'skate_session_hash_' . $hash, $session->id, DAY_IN_SECONDS );
					return intval( $session->id );
				}
			}

			return false;
		}

		return intval( $session_id );
	}

	/**
	 * Verify nonce for AJAX requests.
	 *
	 * @since    1.0.0
	 * @param    string    $action    Nonce action.
	 * @param    string    $nonce     Nonce value.
	 * @return   bool      True if valid, false otherwise.
	 */
	public static function verify_nonce( $action, $nonce ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Create nonce for form/AJAX.
	 *
	 * @since    1.0.0
	 * @param    string    $action    Nonce action.
	 * @return   string    Nonce value.
	 */
	public static function create_nonce( $action ) {
		return wp_create_nonce( $action );
	}

	/**
	 * Check if user is admin.
	 *
	 * @since    1.0.0
	 * @return   bool    True if admin, false otherwise.
	 */
	public static function is_admin() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Sanitize text field.
	 *
	 * @since    1.0.0
	 * @param    string    $value    Value to sanitize.
	 * @return   string    Sanitized value.
	 */
	public static function sanitize_text( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize email.
	 *
	 * @since    1.0.0
	 * @param    string    $email    Email to sanitize.
	 * @return   string    Sanitized email.
	 */
	public static function sanitize_email( $email ) {
		return sanitize_email( $email );
	}

	/**
	 * Validate email format.
	 *
	 * @since    1.0.0
	 * @param    string    $email    Email to validate.
	 * @return   bool      True if valid, false otherwise.
	 */
	public static function is_valid_email( $email ) {
		return is_email( $email );
	}

	/**
	 * Validate phone number format.
	 *
	 * @since    1.0.0
	 * @param    string    $phone    Phone number to validate.
	 * @return   bool      True if valid, false otherwise.
	 */
	public static function is_valid_phone( $phone ) {
		// Remove common characters
		$cleaned = preg_replace( '/[^0-9+\-\s()]/', '', $phone );

		// Must have at least 7 digits
		$digits = preg_replace( '/[^0-9]/', '', $cleaned );

		return strlen( $digits ) >= 7 && strlen( $digits ) <= 15;
	}

	/**
	 * Validate date format (YYYY-MM-DD).
	 *
	 * @since    1.0.0
	 * @param    string    $date    Date to validate.
	 * @return   bool      True if valid, false otherwise.
	 */
	public static function is_valid_date( $date ) {
		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Create identifier hash from email and phone.
	 *
	 * @since    1.0.0
	 * @param    string    $email    Email address.
	 * @param    string    $phone    Phone number.
	 * @return   string    Hash identifier.
	 */
	public static function create_identifier( $email, $phone ) {
		$email = strtolower( trim( $email ) );
		$phone = preg_replace( '/[^0-9]/', '', $phone ); // Remove non-digits

		return hash( 'sha256', $email . $phone );
	}

	/**
	 * Check rate limit for IP address.
	 *
	 * @since    1.0.0
	 * @param    string    $action    Action identifier.
	 * @param    int       $limit     Maximum attempts.
	 * @param    int       $window    Time window in seconds.
	 * @return   bool      True if allowed, false if rate limited.
	 */
	public static function check_rate_limit( $action, $limit = 10, $window = 60 ) {
		$ip = self::get_client_ip();
		$key = 'skate_rate_limit_' . md5( $ip . $action );

		$attempts = get_transient( $key );

		if ( $attempts === false ) {
			set_transient( $key, 1, $window );
			return true;
		}

		if ( $attempts >= $limit ) {
			return false;
		}

		set_transient( $key, $attempts + 1, $window );
		return true;
	}

	/**
	 * Get client IP address.
	 *
	 * @since    1.0.0
	 * @return   string    IP address.
	 */
	public static function get_client_ip() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
	}

	/**
	 * Validate file upload.
	 *
	 * @since    1.0.0
	 * @param    array     $file          File array from $_FILES.
	 * @param    array     $allowed_types Allowed MIME types.
	 * @param    int       $max_size      Max file size in bytes.
	 * @return   array     ['valid' => bool, 'error' => string|null]
	 */
	public static function validate_file_upload( $file, $allowed_types, $max_size ) {
		// Check for upload errors
		if ( ! empty( $file['error'] ) ) {
			return array(
				'valid' => false,
				'error' => 'File upload error: ' . $file['error'],
			);
		}

		// Check file size
		if ( $file['size'] > $max_size ) {
			$max_mb = round( $max_size / 1024 / 1024, 2 );
			return array(
				'valid' => false,
				'error' => "File size exceeds {$max_mb}MB limit",
			);
		}

		// Check MIME type
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime = finfo_file( $finfo, $file['tmp_name'] );
		finfo_close( $finfo );

		if ( ! in_array( $mime, $allowed_types, true ) ) {
			return array(
				'valid' => false,
				'error' => 'Invalid file type',
			);
		}

		// Check file extension
		$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		$allowed_extensions = array();
		foreach ( $allowed_types as $type ) {
			if ( strpos( $type, 'image/jpeg' ) !== false ) {
				$allowed_extensions[] = 'jpg';
				$allowed_extensions[] = 'jpeg';
			} elseif ( strpos( $type, 'image/png' ) !== false ) {
				$allowed_extensions[] = 'png';
			} elseif ( strpos( $type, 'image/gif' ) !== false ) {
				$allowed_extensions[] = 'gif';
			} elseif ( strpos( $type, 'video/mp4' ) !== false ) {
				$allowed_extensions[] = 'mp4';
			} elseif ( strpos( $type, 'video/quicktime' ) !== false ) {
				$allowed_extensions[] = 'mov';
			}
		}

		if ( ! in_array( $ext, $allowed_extensions, true ) ) {
			return array(
				'valid' => false,
				'error' => 'Invalid file extension',
			);
		}

		return array( 'valid' => true );
	}

	/**
	 * Sanitize filename.
	 *
	 * @since    1.0.0
	 * @param    string    $filename    Original filename.
	 * @return   string    Sanitized filename.
	 */
	public static function sanitize_filename( $filename ) {
		// Remove special characters, keep extension
		$info = pathinfo( $filename );
		$name = preg_replace( '/[^a-zA-Z0-9_\-]/', '_', $info['filename'] );
		$ext = ! empty( $info['extension'] ) ? '.' . $info['extension'] : '';

		return $name . '_' . time() . $ext;
	}
}
