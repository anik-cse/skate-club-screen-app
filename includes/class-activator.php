<?php
/**
 * Fired during plugin activation.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

class Skate_Club_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Creates database tables and sets up initial configuration.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::create_tables();
		self::upgrade_database();
		self::create_upload_directory();
		self::add_rewrite_rules();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Set activation flag
		add_option( 'skate_club_activated', true );
	}

	/**
	 * Create custom database tables.
	 *
	 * @since    1.0.0
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_prefix = $wpdb->prefix;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Sessions table
		$sql[] = "CREATE TABLE {$table_prefix}skate_sessions (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_name VARCHAR(255) NOT NULL,
			session_date DATETIME NOT NULL,
			status ENUM('draft', 'active', 'closed') DEFAULT 'draft',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			activated_at DATETIME NULL,
			closed_at DATETIME NULL,
			settings TEXT NULL COMMENT 'JSON: logo_url, club_name, custom_colors',
			PRIMARY KEY (id),
			KEY status (status),
			KEY session_date (session_date)
		) $charset_collate;";

		// Song requests table
		$sql[] = "CREATE TABLE {$table_prefix}skate_song_requests (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id BIGINT(20) UNSIGNED NOT NULL,
			first_name VARCHAR(100) NOT NULL,
			last_name VARCHAR(100) NOT NULL,
			email VARCHAR(255) NOT NULL,
			phone VARCHAR(20) NOT NULL,
			date_of_birth DATE NOT NULL,
			song_name VARCHAR(255) NOT NULL,
			submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY submitted_at (submitted_at)
		) $charset_collate;";

		// Voting lists table
		$sql[] = "CREATE TABLE {$table_prefix}skate_voting_lists (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id BIGINT(20) UNSIGNED NOT NULL,
			song_title VARCHAR(255) NOT NULL,
			artist VARCHAR(255) NULL,
			display_order INT DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY display_order (display_order)
		) $charset_collate;";

		// Votes table
		$sql[] = "CREATE TABLE {$table_prefix}skate_votes (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id BIGINT(20) UNSIGNED NOT NULL,
			voting_list_id BIGINT(20) UNSIGNED NOT NULL,
			voter_identifier VARCHAR(255) NOT NULL COMMENT 'hash of email/phone',
			browser_fingerprint VARCHAR(255) NOT NULL,
			rank_position INT NOT NULL COMMENT '1=first choice, 2=second, etc',
			voted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY voting_list_id (voting_list_id),
			KEY voter_identifier (voter_identifier),
			UNIQUE KEY unique_vote (session_id, voting_list_id, voter_identifier)
		) $charset_collate;";

		// Media table
		$sql[] = "CREATE TABLE {$table_prefix}skate_media (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id BIGINT(20) UNSIGNED NOT NULL,
			media_type ENUM('photo', 'video') NOT NULL,
			file_path VARCHAR(500) NOT NULL,
			file_name VARCHAR(255) NOT NULL,
			file_size BIGINT(20) NOT NULL COMMENT 'in bytes',
			mime_type VARCHAR(100) NOT NULL,
			status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
			uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			approved_at DATETIME NULL,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY status (status),
			KEY uploaded_at (uploaded_at)
		) $charset_collate;";

		// Spinner entries table
		$sql[] = "CREATE TABLE {$table_prefix}skate_spinner_entries (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id BIGINT(20) UNSIGNED NOT NULL,
			participant_name VARCHAR(255) NOT NULL,
			group_name VARCHAR(100) NULL,
			color VARCHAR(7) NOT NULL COMMENT 'hex color code',
			display_order INT DEFAULT 0,
			last_won_at DATETIME NULL COMMENT 'for preventing consecutive duplicates',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY group_name (group_name),
			KEY display_order (display_order)
		) $charset_collate;";

		// Raffle entries table
		$sql[] = "CREATE TABLE {$table_prefix}skate_raffle_entries (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id BIGINT(20) UNSIGNED NOT NULL,
			first_name VARCHAR(100) NOT NULL,
			last_name VARCHAR(100) NOT NULL,
			email VARCHAR(255) NOT NULL,
			phone VARCHAR(20) NOT NULL,
			entry_identifier VARCHAR(255) NOT NULL COMMENT 'hash of email/phone',
			submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY entry_identifier (entry_identifier),
			UNIQUE KEY unique_entry (session_id, entry_identifier)
		) $charset_collate;";

		// Raffle winners table
		$sql[] = "CREATE TABLE {$table_prefix}skate_raffle_winners (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id BIGINT(20) UNSIGNED NOT NULL,
			raffle_entry_id BIGINT(20) UNSIGNED NOT NULL,
			selected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY selected_at (selected_at)
		) $charset_collate;";

		// Execute table creation
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}

		// Store database version
		add_option( 'skate_club_db_version', '1.0.0' );
	}

	/**
	 * Upgrade database structure for existing installations.
	 *
	 * @since    1.0.0
	 */
	private static function upgrade_database() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'skate_raffle_winners';

		// Check if unique constraint exists and remove it
		$indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name} WHERE Key_name = 'unique_winner_per_session'" );

		if ( ! empty( $indexes ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} DROP INDEX unique_winner_per_session" );
		}

		// Add selected_at index if it doesn't exist
		$indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name} WHERE Key_name = 'selected_at'" );

		if ( empty( $indexes ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX selected_at (selected_at)" );
		}

		// Upgrade spinner entries table
		$spinner_table = $wpdb->prefix . 'skate_spinner_entries';
		$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$spinner_table}' AND column_name = 'group_name'" );

		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$spinner_table} ADD group_name VARCHAR(100) NULL AFTER participant_name" );
			$wpdb->query( "ALTER TABLE {$spinner_table} ADD INDEX group_name (group_name)" );
		}
	}

	/**
	 * Create upload directory for media files.
	 *
	 * @since    1.0.0
	 */
	private static function create_upload_directory() {
		$upload_dir = wp_upload_dir();
		$skate_dir = $upload_dir['basedir'] . '/skate-club';

		if ( ! file_exists( $skate_dir ) ) {
			wp_mkdir_p( $skate_dir );

			// Create .htaccess for security
			$htaccess_content = "Options -Indexes\n";
			file_put_contents( $skate_dir . '/.htaccess', $htaccess_content );
		}
	}

	/**
	 * Add custom rewrite rules.
	 *
	 * @since    1.0.0
	 */
	private static function add_rewrite_rules() {
		// Screen display
		add_rewrite_rule( '^skate-club-screen-display/?$', 'index.php?skate_screen_display=1', 'top' );

		// Form endpoints with skate-club prefix
		add_rewrite_rule( '^skate-club-submit-song-request/?$', 'index.php?skate_form=song_request', 'top' );
		add_rewrite_rule( '^skate-club-vote-songs/?$', 'index.php?skate_form=vote_songs', 'top' );
		add_rewrite_rule( '^skate-club-upload-media/?$', 'index.php?skate_form=upload_media', 'top' );
		add_rewrite_rule( '^skate-club-enter-raffle/?$', 'index.php?skate_form=enter_raffle', 'top' );

		// Add query vars
		add_filter( 'query_vars', function( $vars ) {
			$vars[] = 'skate_screen_display';
			$vars[] = 'skate_form';
			return $vars;
		});
	}
}
