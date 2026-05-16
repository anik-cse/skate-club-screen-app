<?php
/**
 * Spinner Wheel module class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes/modules
 */

class Skate_Club_Spinner_Wheel {

	/**
	 * Professional color palette for wheel segments (Material Design inspired).
	 *
	 * @var array
	 */
	private static $colors = array(
		// Blues
		'#2196F3', '#1976D2', '#1565C0', '#0D47A1', '#03A9F4', '#0288D1', '#0277BD', '#01579B',
		'#00BCD4', '#0097A7', '#00838F', '#006064', '#3F51B5', '#303F9F', '#283593', '#1A237E',
		// Greens
		'#4CAF50', '#388E3C', '#2E7D32', '#1B5E20', '#8BC34A', '#689F38', '#558B2F', '#33691E',
		'#009688', '#00796B', '#00695C', '#004D40', '#66BB6A', '#43A047', '#2E7D32', '#1B5E20',
		// Purples & Pinks
		'#9C27B0', '#7B1FA2', '#6A1B9A', '#4A148C', '#673AB7', '#5E35B1', '#512DA8', '#311B92',
		'#E91E63', '#C2185B', '#AD1457', '#880E4F', '#F06292', '#EC407A', '#D81B60', '#C2185B',
		// Oranges & Reds
		'#FF5722', '#E64A19', '#D84315', '#BF360C', '#FF9800', '#F57C00', '#EF6C00', '#E65100',
		'#FF6F00', '#FF8F00', '#FFA000', '#FFB300', '#F44336', '#D32F2F', '#C62828', '#B71C1C',
		// Teals & Cyans
		'#00ACC1', '#00838F', '#006064', '#0097A7', '#26C6DA', '#00BCD4', '#00ACC1', '#0097A7',
		'#26A69A', '#009688', '#00897B', '#00796B', '#80CBC4', '#4DB6AC', '#26A69A', '#00897B',
		// Indigos & Deep Purples
		'#3F51B5', '#303F9F', '#283593', '#1A237E', '#5C6BC0', '#3F51B5', '#3949AB', '#303F9F',
		'#7E57C2', '#673AB7', '#5E35B1', '#512DA8', '#9575CD', '#7E57C2', '#673AB7', '#5E35B1',
		// Amber & Yellows
		'#FFC107', '#FFB300', '#FFA000', '#FF8F00', '#FFCA28', '#FFC107', '#FFB300', '#FFA000',
		'#FFD54F', '#FFCA28', '#FFC107', '#FFB300', '#FF6F00', '#F57F17', '#F9A825', '#F57F17',
	);

	/**
	 * Add entry to spinner wheel.
	 *
	 * @since    1.0.0
	 * @param    int       $session_id         Session ID.
	 * @param    string    $participant_name   Participant name.
	 * @param    string    $color              Hex color (optional).
	 * @return   array     Response array.
	 */
	public static function add_entry( $session_id, $participant_name, $group_name = null, $color = null ) {
		if ( empty( $participant_name ) ) {
			return array(
				'success' => false,
				'message' => 'Participant name is required',
			);
		}

		// Auto-assign color if not provided
		if ( empty( $color ) ) {
			// Count entries in context (session or global group)
			$existing_count = self::get_entry_count( $session_id, $group_name );
			$color = self::$colors[ $existing_count % count( self::$colors ) ];
		}

		// Validate color format
		if ( ! preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid color format',
			);
		}

		$data = array(
			'session_id'       => intval( $session_id ),
			'participant_name' => Skate_Club_Security::sanitize_text( $participant_name ),
			'group_name'       => ! empty( $group_name ) ? Skate_Club_Security::sanitize_text( $group_name ) : null,
			'color'            => strtoupper( $color ),
			'display_order'    => self::get_entry_count( $session_id, $group_name ),
		);

		$id = Skate_Club_Database::insert( 'spinner_entries', $data );

		if ( $id === false ) {
			return array(
				'success' => false,
				'message' => 'Failed to add entry',
			);
		}

		return array(
			'success'  => true,
			'message'  => 'Entry added successfully',
			'entry_id' => $id,
			'color'    => $color,
		);
	}

	/**
	 * Remove entry from spinner wheel.
	 *
	 * @since    1.0.0
	 * @param    int      $entry_id    Entry ID.
	 * @return   array    Response array.
	 */
	public static function remove_entry( $entry_id ) {
		$result = Skate_Club_Database::delete( 'spinner_entries', array( 'id' => intval( $entry_id ) ) );

		if ( $result !== false ) {
			return array(
				'success' => true,
				'message' => 'Entry removed',
			);
		}

		return array(
			'success' => false,
			'message' => 'Failed to remove entry',
		);
	}

	/**
	 * Bulk remove entries.
	 *
	 * @since    1.0.1
	 * @param    array    $entry_ids    Array of Entry IDs.
	 * @return   array    Response array.
	 */
	public static function bulk_delete( $entry_ids ) {
		// Sanitize IDs
		$ids = array_map( 'intval', $entry_ids );
		$ids = array_filter( $ids ); // Remove zeros

		if ( empty( $ids ) ) {
			return array(
				'success' => false,
				'message' => 'No valid IDs provided',
			);
		}

		global $wpdb;
		$table = Skate_Club_Database::get_table_name( 'spinner_entries' );
		$ids_sql = implode( ',', $ids );

		$result = $wpdb->query( "DELETE FROM $table WHERE id IN ($ids_sql)" );

		if ( $result !== false ) {
			return array(
				'success' => true,
				'message' => count( $ids ) . ' entries deleted.',
			);
		}

		return array(
			'success' => false,
			'message' => 'Failed to delete entries',
		);
	}

	/**
	 * Get all spinner entries for a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @param    string   $group_name    Optional group filter.
	 * @return   array    Spinner entries.
	 */
	public static function get_entries( $session_id, $group_name = null ) {
		global $wpdb;
		$table = Skate_Club_Database::get_table_name( 'spinner_entries' );

		if ( ! empty( $group_name ) ) {
			// Specific Group: Ignore session_id, filter by group only
			$query = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE group_name = %s ORDER BY display_order ASC", 
				$group_name
			);
		} else {
			// All Groups (or No Group Selected):
			// 1. Ungrouped in THIS session
			// 2. OR Any Grouped entry (Global)
			$query = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE (session_id = %d AND (group_name IS NULL OR group_name = '')) OR (group_name IS NOT NULL AND group_name != '') ORDER BY display_order ASC",
				$session_id
			);
		}
		
		return $wpdb->get_results( $query );
	}

	/**
	 * Get entry count.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   int      Entry count.
	 */
	public static function get_entry_count( $session_id, $group_name = null ) {
		global $wpdb;
		$table = Skate_Club_Database::get_table_name( 'spinner_entries' );

		if ( ! empty( $group_name ) ) {
			$query = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE group_name = %s", $group_name );
		} else {
			$query = $wpdb->prepare( 
				"SELECT COUNT(*) FROM {$table} WHERE (session_id = %d AND (group_name IS NULL OR group_name = '')) OR (group_name IS NOT NULL AND group_name != '')", 
				$session_id 
			);
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Spin the wheel and select a winner.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @param    string   $group_name    Optional group filter.
	 * @return   array    Response array with winner.
	 */
	public static function spin_wheel( $session_id, $group_name = null ) {
		$entries = self::get_entries( $session_id, $group_name );

		if ( empty( $entries ) ) {
			return array(
				'success' => false,
				'message' => 'No entries in spinner wheel',
			);
		}

		// Get last winner to avoid consecutive duplicates
		$last_winner = self::get_last_winner( $session_id, $group_name );

		// Filter out last winner if there are multiple entries
		if ( $last_winner && count( $entries ) > 1 ) {
			$entries = array_filter( $entries, function( $entry ) use ( $last_winner ) {
				return $entry->id != $last_winner->id;
			});
			$entries = array_values( $entries ); // Re-index array
		}

		if ( empty( $entries ) ) {
			return array(
				'success' => false,
				'message' => 'Not enough entries to spin',
			);
		}

		// Randomly select winner
		$winner_index = random_int( 0, count( $entries ) - 1 );
		$winner = $entries[ $winner_index ];

		// Update last_won_at timestamp
		Skate_Club_Database::update(
			'spinner_entries',
			array( 'last_won_at' => current_time( 'mysql' ) ),
			array( 'id' => $winner->id )
		);

		// Calculate rotation for animation
		$total_entries = count( self::get_entries( $session_id, $group_name ) );
		$degrees_per_segment = 360 / $total_entries;
		$base_rotation = ( $winner->display_order * $degrees_per_segment ) + ( $degrees_per_segment / 2 );

		// Add 5-8 full rotations for effect
		$full_rotations = random_int( 5, 8 ) * 360;
		$total_rotation = $full_rotations + $base_rotation;

		return array(
			'success'  => true,
			'winner'   => array(
				'id'       => $winner->id,
				'name'     => $winner->participant_name,
				'color'    => $winner->color,
				'rotation' => $total_rotation,
			),
			'message'  => 'Winner selected: ' . $winner->participant_name,
		);
	}

	/**
	 * Get last winner for a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @param    string   $group_name    Optional group filter.
	 * @return   object|null Last winner entry.
	 */
	private static function get_last_winner( $session_id, $group_name = null ) {
		global $wpdb;

		$table = Skate_Club_Database::get_table_name( 'spinner_entries' );

		$args = array();

		if ( ! empty( $group_name ) ) {
			$sql = "SELECT * FROM {$table} WHERE group_name = %s AND last_won_at IS NOT NULL";
			$args[] = $group_name;
		} else {
			$sql = "SELECT * FROM {$table} WHERE ((session_id = %d AND (group_name IS NULL OR group_name = '')) OR (group_name IS NOT NULL AND group_name != '')) AND last_won_at IS NOT NULL";
			$args[] = $session_id;
		}

		$sql .= " ORDER BY last_won_at DESC LIMIT 1";

		$query = $wpdb->prepare( $sql, $args );

		return $wpdb->get_row( $query );
	}

	/**
	 * Update entry color.
	 *
	 * @since    1.0.0
	 * @param    int       $entry_id    Entry ID.
	 * @param    string    $color       Hex color.
	 * @return   bool      Success status.
	 */
	public static function update_color( $entry_id, $color ) {
		// Validate color format
		if ( ! preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
			return false;
		}

		$result = Skate_Club_Database::update(
			'spinner_entries',
			array( 'color' => strtoupper( $color ) ),
			array( 'id' => intval( $entry_id ) )
		);

		return $result !== false;
	}

	/**
	 * Clear all entries for a session.
	 *
	 * @since    1.0.0
	 * @param    int      $session_id    Session ID.
	 * @return   bool     Success status.
	 */
	public static function clear_entries( $session_id, $group_name = null ) {
		if ( ! empty( $group_name ) ) {
			$where = array( 'group_name' => $group_name );
		} else {
			$where = array( 'session_id' => intval( $session_id ) );
		}
		$result = Skate_Club_Database::delete( 'spinner_entries', $where );
		return $result !== false;
	}
	
	/**
	 * Get all unique groups for a session.
	 *
	 * @since    1.0.1
	 * @param    int      $session_id    Session ID.
	 * @return   array    Array of group names.
	 */
	public static function get_groups( $session_id ) {
		global $wpdb;
		$table = Skate_Club_Database::get_table_name( 'spinner_entries' );
		
		// Global Groups: Fetch all distinct group names regardless of session
		$query = "SELECT DISTINCT group_name FROM {$table} WHERE group_name IS NOT NULL AND group_name != '' ORDER BY group_name ASC";
		
		return $wpdb->get_col( $query );
	}

	/**
	 * Set active group for session.
	 * 
	 * @since 1.0.1
	 */
	public static function set_active_group( $session_id, $group_name ) {
		if ( empty( $group_name ) || $group_name === 'all' ) {
			delete_option( 'skate_spinner_active_group_' . intval( $session_id ) );
		} else {
			update_option( 'skate_spinner_active_group_' . intval( $session_id ), $group_name );
		}
		return true;
	}

	/**
	 * Get active group for session.
	 * 
	 * @since 1.0.1
	 */
	public static function get_active_group( $session_id ) {
		return get_option( 'skate_spinner_active_group_' . intval( $session_id ) );
	}

	/**
	 * Rename a spinner group globally.
	 *
	 * @since    1.0.2
	 * @param    string   $old_name    Old group name.
	 * @param    string   $new_name    New group name.
	 * @return   bool     Success status.
	 */
	public static function rename_group( $old_name, $new_name ) {
		if ( empty( $old_name ) || empty( $new_name ) ) {
			return false;
		}

		$old_name = Skate_Club_Security::sanitize_text( $old_name );
		$new_name = Skate_Club_Security::sanitize_text( $new_name );

		if ( $old_name === $new_name ) {
			return true; // Nothing to do
		}

		global $wpdb;
		$table = Skate_Club_Database::get_table_name( 'spinner_entries' );

		$result = $wpdb->update(
			$table,
			array( 'group_name' => $new_name ),
			array( 'group_name' => $old_name )
		);

		return $result !== false;
	}

	/**
	 * Update entry details.
	 *
	 * @since    1.0.2
	 * @param    int      $entry_id          Entry ID.
	 * @param    string   $participant_name  Participant name.
	 * @param    string   $color             Hex color (optional).
	 * @return   bool     Success status.
	 */
	public static function update_entry( $entry_id, $participant_name, $color = null ) {
		$data = array(
			'participant_name' => Skate_Club_Security::sanitize_text( $participant_name ),
		);

		if ( ! empty( $color ) ) {
			if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
				$data['color'] = strtoupper( $color );
			}
		}

		$result = Skate_Club_Database::update(
			'spinner_entries',
			$data,
			array( 'id' => intval( $entry_id ) )
		);

		return $result !== false;
	}
}
