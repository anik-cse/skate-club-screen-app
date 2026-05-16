<?php
/**
 * Media Upload module class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes/modules
 */

class Skate_Club_Media_Upload {

	const ALLOWED_IMAGE_TYPES = array( 'image/jpeg', 'image/png', 'image/gif' );
	const ALLOWED_VIDEO_TYPES = array( 
		'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/webm', 'video/ogg', 'video/3gpp'
	);

	/**
	 * Upload media file.
	 *
	 * @since    1.0.0
	 * @param    array    $file         File from $_FILES.
	 * @param    int      $session_id   Session ID.
	 * @return   array    Response array.
	 */
	public static function upload_media( $file, $session_id ) {
		// Verify session is active
		if ( ! Skate_Club_Session_Manager::is_session_active( $session_id ) ) {
			return array(
				'success' => false,
				'message' => 'Session is not active',
			);
		}

		// Get upload size limits from settings
		$settings = get_option( 'skate_club_settings', array() );
		$max_image_size_mb = ! empty( $settings['max_image_size'] ) ? intval( $settings['max_image_size'] ) : 10;
		$max_video_size_mb = ! empty( $settings['max_video_size'] ) ? intval( $settings['max_video_size'] ) : 50;

		// Convert MB to bytes
		$max_image_size_bytes = $max_image_size_mb * 1048576; // 1MB = 1048576 bytes
		$max_video_size_bytes = $max_video_size_mb * 1048576;

		// Determine media type
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime = finfo_file( $finfo, $file['tmp_name'] );
		finfo_close( $finfo );

		$media_type = '';
		$max_size = 0;
		$allowed_types = array();

		if ( in_array( $mime, self::ALLOWED_IMAGE_TYPES, true ) ) {
			$media_type = 'photo';
			$max_size = $max_image_size_bytes;
			$allowed_types = self::ALLOWED_IMAGE_TYPES;
		} elseif ( in_array( $mime, self::ALLOWED_VIDEO_TYPES, true ) ) {
			$media_type = 'video';
			$max_size = $max_video_size_bytes;
			$allowed_types = self::ALLOWED_VIDEO_TYPES;
		} else {
			return array(
				'success' => false,
				'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, MP4, MOV, AVI, WEBM, MKV',
			);
		}

		// Validate file
		$validation = Skate_Club_Security::validate_file_upload( $file, $allowed_types, $max_size );

		if ( ! $validation['valid'] ) {
			return array(
				'success' => false,
				'message' => $validation['error'],
			);
		}

		// Create upload directory for session
		$upload_dir = wp_upload_dir();
		$session_dir = $upload_dir['basedir'] . '/skate-club/' . $session_id;

		if ( ! file_exists( $session_dir ) ) {
			wp_mkdir_p( $session_dir );
		}

		// Sanitize filename
		$filename = Skate_Club_Security::sanitize_filename( $file['name'] );
		$file_path = $session_dir . '/' . $filename;

		// Move uploaded file
		if ( ! move_uploaded_file( $file['tmp_name'], $file_path ) ) {
			return array(
				'success' => false,
				'message' => 'Failed to save file',
			);
		}

		// Get file URL
		$file_url = $upload_dir['baseurl'] . '/skate-club/' . $session_id . '/' . $filename;

		// Save to database
		$media_data = array(
			'session_id' => intval( $session_id ),
			'media_type' => $media_type,
			'file_path'  => $file_path,
			'file_name'  => $filename,
			'file_size'  => filesize( $file_path ),
			'mime_type'  => $mime,
			'status'     => 'pending',
		);

		$id = Skate_Club_Database::insert( 'media', $media_data );

		if ( $id === false ) {
			// Delete uploaded file on database error
			unlink( $file_path );

			return array(
				'success' => false,
				'message' => 'Failed to save media record',
			);
		}

		return array(
			'success' => true,
			'message' => 'Your upload is pending approval. Thank you!',
			'id'      => $id,
		);
	}

	/**
	 * Get pending media for approval.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID (optional).
	 * @return   array    Pending media items.
	 */
	public static function get_pending_media( $session_id = null ) {
		$where = array( 'status' => 'pending' );

		if ( $session_id !== null ) {
			$where['session_id'] = intval( $session_id );
		}

		$media = Skate_Club_Database::get_results(
			'media',
			$where,
			array(
				'order_by' => 'uploaded_at',
				'order'    => 'DESC',
			)
		);

		// Add URLs
		return self::add_media_urls( $media );
	}

	/**
	 * Get approved media for display.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   array    Approved media items.
	 */
	public static function get_approved_media( $session_id ) {
		// Check cache
		$cached = get_transient( 'skate_media_gallery_' . $session_id );
		if ( $cached !== false ) {
			return $cached;
		}

		$media = Skate_Club_Database::get_results(
			'media',
			array(
				'session_id' => intval( $session_id ),
				'status'     => 'approved',
			),
			array(
				'order_by' => 'approved_at',
				'order'    => 'DESC',
			)
		);

		$media = self::add_media_urls( $media );

		// Cache for 60 seconds
		set_transient( 'skate_media_gallery_' . $session_id, $media, 60 );

		return $media;
	}

	/**
	 * Approve media items.
	 *
	 * @since    1.0.0
	 * @param    array    $media_ids    Array of media IDs.
	 * @return   array    Response array.
	 */
	public static function approve_media( $media_ids ) {
		if ( empty( $media_ids ) || ! is_array( $media_ids ) ) {
			return array(
				'success' => false,
				'message' => 'No media IDs provided',
			);
		}

		$approved = 0;

		foreach ( $media_ids as $media_id ) {
			$result = Skate_Club_Database::update(
				'media',
				array(
					'status'      => 'approved',
					'approved_at' => current_time( 'mysql' ),
				),
				array( 'id' => intval( $media_id ) )
			);

			if ( $result !== false ) {
				$approved++;

				// Get session ID to clear cache
				$media = Skate_Club_Database::get_row( 'media', array( 'id' => intval( $media_id ) ) );
				if ( $media ) {
					delete_transient( 'skate_media_gallery_' . $media->session_id );
				}
			}
		}

		if ( $approved > 0 ) {
			return array(
				'success' => true,
				'message' => "{$approved} item(s) approved",
				'count'   => $approved,
			);
		}

		return array(
			'success' => false,
			'message' => 'Failed to approve media',
		);
	}

	/**
	 * Reject media items.
	 *
	 * @since    1.0.0
	 * @param    array    $media_ids    Array of media IDs.
	 * @return   array    Response array.
	 */
	public static function reject_media( $media_ids ) {
		if ( empty( $media_ids ) || ! is_array( $media_ids ) ) {
			return array(
				'success' => false,
				'message' => 'No media IDs provided',
			);
		}

		$rejected = 0;

		foreach ( $media_ids as $media_id ) {
			$result = Skate_Club_Database::update(
				'media',
				array( 'status' => 'rejected' ),
				array( 'id' => intval( $media_id ) )
			);

			if ( $result !== false ) {
				$rejected++;
			}
		}

		if ( $rejected > 0 ) {
			return array(
				'success' => true,
				'message' => "{$rejected} item(s) rejected",
				'count'   => $rejected,
			);
		}

		return array(
			'success' => false,
			'message' => 'Failed to reject media',
		);
	}

	/**
	 * Delete media item and file.
	 *
	 * @since    1.0.0
	 * @param    int      $media_id    Media ID.
	 * @return   bool     Success status.
	 */
	public static function delete_media( $media_id ) {
		// Get media record
		$media = Skate_Club_Database::get_row( 'media', array( 'id' => intval( $media_id ) ) );

		if ( ! $media ) {
			return false;
		}

		// Delete file
		if ( file_exists( $media->file_path ) ) {
			unlink( $media->file_path );
		}

		// Delete from database
		$result = Skate_Club_Database::delete( 'media', array( 'id' => intval( $media_id ) ) );

		if ( $result !== false ) {
			delete_transient( 'skate_media_gallery_' . $media->session_id );
			return true;
		}

		return false;
	}

	/**
	 * Add URLs to media objects.
	 *
	 * @since    1.0.0
	 * @param    array    $media    Media objects.
	 * @return   array    Media with URLs.
	 */
	private static function add_media_urls( $media ) {
		$upload_dir = wp_upload_dir();

		foreach ( $media as $item ) {
			// Extract session ID and filename from path
			$path_parts = explode( '/skate-club/', $item->file_path );
			if ( count( $path_parts ) === 2 ) {
				$item->url = $upload_dir['baseurl'] . '/skate-club/' . $path_parts[1];

				// For images, add thumbnail URL (same as original for now)
				if ( $item->media_type === 'photo' ) {
					$item->thumbnail_url = $item->url;
				}

				// For videos, could add poster/thumbnail generation here
				if ( $item->media_type === 'video' ) {
					$item->poster_url = ''; // Placeholder for video thumbnail
				}
			}
		}

		return $media;
	}

	/**
	 * Get media count by status.
	 *
	 * @since    1.0.0
	 * @param    int       $session_id    Session ID.
	 * @param    string    $status        Status (pending, approved, rejected).
	 * @return   int       Media count.
	 */
	public static function get_count( $session_id, $status = 'approved' ) {
		return Skate_Club_Database::get_count(
			'media',
			array(
				'session_id' => intval( $session_id ),
				'status'     => $status,
			)
		);
	}
}
