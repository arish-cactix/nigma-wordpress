<?php // phpcs:ignore
/**
 * Database helper class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Helper class
 */
class Db {

	/**
	 * Fetches all database tables.
	 *
	 * @param bool $apply_tb_filter if false returns all db tables.
	 *
	 * @uses $wpdb global
	 *
	 * @return array
	 */
	public static function get_all_database_tables( $apply_tb_filter = true ) {
		global $wpdb;

		$db_name = self::get_db_name();
		if ( empty( $db_name ) && defined( 'DB_NAME' ) ) {
			$db_name = DB_NAME;
		}
		if ( empty( $db_name ) ) {
			return array();
		}

		$db_name           = esc_sql( $db_name );
		$black_list_tables = false === $apply_tb_filter ? array() : self::get_tables_exclusions();
		$in_part           = implode( ', ', array_fill( 0, count( $black_list_tables ), '%s' ) );
		if ( empty( $black_list_tables ) ) {
			$in_part           = '%s';
			$black_list_tables = array( ' ' );
		}

		$query = "SELECT table_name as table_name, table_type as table_type FROM information_schema.tables WHERE table_schema = '$db_name' AND table_name NOT IN($in_part) ORDER BY table_type = 'VIEW'";
		//@phpcs:ignore
		$tables = $wpdb->get_results( $wpdb->prepare( $query, $black_list_tables ) ); // db call ok; no-cache ok.

		if ( empty( $tables ) ) {
			return array();
		}

		array_walk(
			$tables,
			function ( &$row ) {
				$row = array(
					'name'    => $row->table_name,
					'is_view' => 'VIEW' === $row->table_type,
				);
			}
		);

		return $tables;
	}

	/**
	 * Get the list of tables for exclusion based on user settings.
	 *
	 * @uses $wpdb global
	 * @return array - list of tables
	 */
	public static function get_tables_exclusions() {

		$snap_tables_excluded = get_site_option(
			'snapshot_excluded_tables',
			false
		);

		$add_filter_array = array();
		if ( false === $snap_tables_excluded ) {
			global $wpdb;
			array_push(
				$add_filter_array,
				$wpdb->prefix . 'defender_lockout_log'
			);
			$snap_tables_excluded = array();
		}

		return array_merge( $snap_tables_excluded, $add_filter_array );
	}

	/**
	 * Get the bulk selection classes for list of tables.
	 *
	 * @param array $tables_list list of db tables.
	 *
	 * @uses $wpdb global
	 * @return array - list of tables with filtering classes
	 */
	public static function bulk_selection_classes( $tables_list ) {

		$tb_with_classes = array();
		global $wpdb;

		$core_db_list = array(
			$wpdb->base_prefix . 'options',
			$wpdb->base_prefix . 'users',
			$wpdb->base_prefix . 'usermeta',
			$wpdb->base_prefix . 'posts',
			$wpdb->base_prefix . 'postmeta',
			$wpdb->base_prefix . 'terms',
			$wpdb->base_prefix . 'termmeta',
			$wpdb->base_prefix . 'term_relationships',
			$wpdb->base_prefix . 'term_taxonomy',
			$wpdb->base_prefix . 'comments',
			$wpdb->base_prefix . 'commentmeta',
			$wpdb->base_prefix . 'links',
		);

		if ( function_exists( 'get_sites' ) ) {
			$sites_ids = get_sites( array( 'fields' => 'ids' ) );
			array_shift( $sites_ids );
		}
		foreach ( $tables_list as $key => $tb_name ) {
			$cls = '';
			if ( in_array( $tb_name, $core_db_list, true ) ) {
				$cls .= 'core ';
			}

			preg_match( '/[0-9]/', $tb_name, $matches );
			$cls .= empty( $matches ) ? '' : 'site_' . $wpdb->prefix . $matches[0] . '_';

			$tb_with_classes[] = array(
				'name'    => $tb_name,
				'classes' => $cls,
			);
		}

		return $tb_with_classes;
	}

	/**
	 * Get the database name for multisites included.
	 *
	 * @return string
	 */
	public static function get_db_name() {

		global $wpdb;

		$db_class = get_class( $wpdb );

		if ( 'm_wpdb' === $db_class ) {

			$test_sql   = 'SELECT ID FROM ' . $wpdb->prefix . 'posts LIMIT 1';
			$query_data = $wpdb->analyze_query( $test_sql );
			if ( isset( $query_data['dataset'] ) ) {

				global $db_servers;
				if ( isset( $db_servers[ $query_data['dataset'] ][0]['name'] ) ) {
					return $db_servers[ $query_data['dataset'] ][0]['name'];
				}
			}
		} else {
			return DB_NAME;
		}
	}

	/**
	 * Better addslashes for SQL queries.
	 * Taken from phpMyAdmin.
	 *
	 * @param string $a_string The string to be addslashed.
	 * @param bool   $is_like If it's like arg.
	 *
	 * @return string
	 */
	public static function sql_addslashes( $a_string = '', $is_like = false ) {
		if ( $is_like ) {
			$a_string = str_replace( '\\', '\\\\\\\\', $a_string );
		} else {
			$a_string = str_replace( '\\', '\\\\', $a_string );
		}

		return str_replace( '\'', '\\\'', $a_string );
	}

	/**
	 * Add backquotes to tables and db-names in
	 * SQL queries. Taken from phpMyAdmin.
	 *
	 * @param string $name The string to be backquoted.
	 *
	 * @return string
	 */
	public static function backquote( $name ) {
		if ( ! empty( $name ) && '*' !== $name ) {
			return '`' . $name . '`';
		}
		return $name;
	}
}