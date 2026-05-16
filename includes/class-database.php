<?php
/**
 * Database operations class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

class Skate_Club_Database {

	/**
	 * Get WordPress database object.
	 *
	 * @since    1.0.0
	 * @return   object    WPDB instance.
	 */
	private static function get_db() {
		global $wpdb;
		return $wpdb;
	}

	/**
	 * Get table name with prefix.
	 *
	 * @since    1.0.0
	 * @param    string    $table    Table name without prefix.
	 * @return   string    Full table name with prefix.
	 */
	public static function get_table_name( $table ) {
		$wpdb = self::get_db();
		return $wpdb->prefix . 'skate_' . $table;
	}

	/**
	 * Insert a new record.
	 *
	 * @since    1.0.0
	 * @param    string    $table    Table name.
	 * @param    array     $data     Data to insert.
	 * @return   int|false Insert ID or false on failure.
	 */
	public static function insert( $table, $data ) {
		$wpdb = self::get_db();
		$table_name = self::get_table_name( $table );

		$result = $wpdb->insert( $table_name, $data );

		if ( $result === false ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update records.
	 *
	 * @since    1.0.0
	 * @param    string    $table    Table name.
	 * @param    array     $data     Data to update.
	 * @param    array     $where    WHERE clause.
	 * @return   int|false Number of rows updated or false on error.
	 */
	public static function update( $table, $data, $where ) {
		$wpdb = self::get_db();
		$table_name = self::get_table_name( $table );

		return $wpdb->update( $table_name, $data, $where );
	}

	/**
	 * Delete records.
	 *
	 * @since    1.0.0
	 * @param    string    $table    Table name.
	 * @param    array     $where    WHERE clause.
	 * @return   int|false Number of rows deleted or false on error.
	 */
	public static function delete( $table, $where ) {
		$wpdb = self::get_db();
		$table_name = self::get_table_name( $table );

		return $wpdb->delete( $table_name, $where );
	}

	/**
	 * Get a single row.
	 *
	 * @since    1.0.0
	 * @param    string    $table    Table name.
	 * @param    array     $where    WHERE clause.
	 * @return   object|null Row object or null if not found.
	 */
	public static function get_row( $table, $where ) {
		$wpdb = self::get_db();
		$table_name = self::get_table_name( $table );

		$where_clause = self::build_where_clause( $where );

		$query = $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE {$where_clause['sql']}",
			$where_clause['values']
		);

		return $wpdb->get_row( $query );
	}

	/**
	 * Get multiple rows.
	 *
	 * @since    1.0.0
	 * @param    string    $table      Table name.
	 * @param    array     $where      WHERE clause (optional).
	 * @param    array     $args       Additional arguments (order_by, order, limit, offset).
	 * @return   array     Array of row objects.
	 */
	public static function get_results( $table, $where = array(), $args = array() ) {
		$wpdb = self::get_db();
		$table_name = self::get_table_name( $table );

		$query = "SELECT * FROM {$table_name}";

		$prepare_values = array();

		if ( ! empty( $where ) ) {
			$where_clause = self::build_where_clause( $where );
			$query .= " WHERE {$where_clause['sql']}";
			$prepare_values = array_merge( $prepare_values, $where_clause['values'] );
		}

		// ORDER BY
		if ( ! empty( $args['order_by'] ) ) {
			$order = ! empty( $args['order'] ) ? strtoupper( $args['order'] ) : 'ASC';
			$query .= " ORDER BY {$args['order_by']} {$order}";
		}

		// LIMIT and OFFSET
		if ( ! empty( $args['limit'] ) ) {
			$query .= " LIMIT %d";
			$prepare_values[] = $args['limit'];

			if ( ! empty( $args['offset'] ) ) {
				$query .= " OFFSET %d";
				$prepare_values[] = $args['offset'];
			}
		}

		if ( ! empty( $prepare_values ) ) {
			$query = $wpdb->prepare( $query, $prepare_values );
		}

		return $wpdb->get_results( $query );
	}

	/**
	 * Get count of rows.
	 *
	 * @since    1.0.0
	 * @param    string    $table    Table name.
	 * @param    array     $where    WHERE clause (optional).
	 * @return   int       Row count.
	 */
	public static function get_count( $table, $where = array() ) {
		$wpdb = self::get_db();
		$table_name = self::get_table_name( $table );

		$query = "SELECT COUNT(*) FROM {$table_name}";

		if ( ! empty( $where ) ) {
			$where_clause = self::build_where_clause( $where );
			$query .= " WHERE {$where_clause['sql']}";
			$query = $wpdb->prepare( $query, $where_clause['values'] );
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Build WHERE clause from array.
	 *
	 * @since    1.0.0
	 * @param    array     $where    WHERE conditions.
	 * @return   array     SQL string and values.
	 */
	private static function build_where_clause( $where ) {
		$conditions = array();
		$values = array();

		foreach ( $where as $column => $value ) {
			if ( is_null( $value ) ) {
				$conditions[] = "{$column} IS NULL";
			} else {
				$conditions[] = "{$column} = %s";
				$values[] = $value;
			}
		}

		return array(
			'sql'    => implode( ' AND ', $conditions ),
			'values' => $values,
		);
	}

	/**
	 * Execute custom query.
	 *
	 * @since    1.0.0
	 * @param    string    $query    SQL query.
	 * @param    array     $args     Arguments for prepare (optional).
	 * @return   mixed     Query results.
	 */
	public static function query( $query, $args = array() ) {
		$wpdb = self::get_db();

		if ( ! empty( $args ) ) {
			$query = $wpdb->prepare( $query, $args );
		}

		return $wpdb->get_results( $query );
	}

	/**
	 * Get last inserted ID.
	 *
	 * @since    1.0.0
	 * @return   int    Last insert ID.
	 */
	public static function insert_id() {
		$wpdb = self::get_db();
		return $wpdb->insert_id;
	}

	/**
	 * Get last error.
	 *
	 * @since    1.0.0
	 * @return   string    Last error message.
	 */
	public static function last_error() {
		$wpdb = self::get_db();
		return $wpdb->last_error;
	}
}
