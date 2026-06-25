<?php // phpcs:ignore
/**
 * Snapshot models: Fetching Zipstream of tables model
 *
 * Holds information for fetching the backup zipstream of tables from the plugin to the service.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Backup\Zipstream;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Db;
use WPMUDEV\Snapshot4\Helper\Codec;
use WPMUDEV\Snapshot4\Helper\Connection;
use WPMUDEV\Snapshot4\Helper\Dumper;
use WPMUDEV\Snapshot4\Helper\Pattern;

/**
 * Fetching Zipstream of tables model class
 */
class Tables extends Model {

	const STATEMENT_DELIMITER = 'end_snapshot_statement';

	/**
	 * Constructor
	 *
	 * @param int $db_chunk The chunk size in db rows that we're splitting the db export into.
	 */
	public function __construct( $db_chunk ) {
		$this->populate( $db_chunk );
	}

	/**
	 * Initializes the data
	 *
	 * @param int $db_chunk The chunk size in db rows that we're splitting the db export into.
	 */
	public function populate( $db_chunk ) {
		$this->set_data(
			array(
				'db_chunk' => $db_chunk,
			)
		);
	}

	/**
	 * Returns a name for the zipstream based on the current time.
	 *
	 * @return string
	 */
	public function name_zipstream() {
		$zipstream_name = date( 'tables-YmdGis', time() ) . '.zip'; // phpcs:ignore

		return apply_filters( 'snapshot_custom_table_zip_name', $zipstream_name );
	}

	/**
	 * Get all rows.
	 *
	 * @param string $quoted_table Quoted table.
	 * @param int    $start Start.
	 * @param int    $chunk Chunk size.
	 *
	 * @return Object
	 */
	public function get_rows( $quoted_table, $start, $chunk ) {
		global $wpdb;
		//phpcs:ignore
		$rows = $wpdb->get_results( $wpdb->prepare( esc_sql( 'SELECT * FROM ' . $quoted_table ) . ' LIMIT %d, %d', $start, $chunk ), ARRAY_A ); // db call ok; no-cache ok.
		return $rows;
	}

	/**
	 * Get total rows count.
	 *
	 * @param string $table Table name.
	 *
	 * @return Object
	 */
	public function get_rows_count( $table ) {
		global $wpdb;

		//phpcs:ignore
		$count = $wpdb->get_row( esc_sql( "SELECT count(*) as total_rows FROM `{$table}`;" ) ); // db call ok; no-cache ok.
		return $count;
	}

	/**
	 * Exports given table up until the timelimit expires.
	 *
	 * Taken partially from phpMyAdmin and partially from
	 * Alain Wolf, Zurich - Switzerland
	 * Website: http://restkultur.ch/personal/wolf/scripts/db_backup/
	 * Modified by Scott Merrill (http://www.skippy.net/)
	 * to use the WordPress $wpdb object
	 *
	 * @param string $table The db table to be exported.
	 * @param int    $chunk The amount of rows to be exported at this step.
	 * @param int    $table_rows The number of total rows of the table.
	 * @param int    $start The initial exported row's position.
	 *
	 * @return array
	 */
	public function backup_table( $table, $chunk, $table_rows, $start = 0 ) {
		$db_model        = new Model\Database();
		$result          = array();
		$result['table'] = $table;
		$quoted_table    = Db::backquote( $table );
		$temp_sql_file   = self::get_temp_sql_filename();

		global $wpdb;

		$total_rows = 0;
		// Use of esc_sql() instead of $wpdb->prepare() because of backticks in query.
		//phpcs:ignore
		$table_structure = $wpdb->get_results( esc_sql( "DESCRIBE `{$table}`" ) ); // db call ok; no-cache ok.

		if ( ! $table_structure ) {
			// @TODO: Log the error and fail the process. Maybe add comment in zipstream.

			return false;
		} else {
			// We should get the names of the existing columns,
			// in case we will later restore to a table whose columns have been modified.
			// eg. when a plugin creates a new column in a table and the older backup doesn't contain that column.
			$insert_into_columns = '(';
			foreach ( $table_structure as $table_columns ) {
				$insert_into_columns .= Db::backquote( $table_columns->Field ) . ', '; // phpcs:ignore
			}
			$insert_into_columns  = trim( $insert_into_columns, ', ' );
			$insert_into_columns .= ')';
			$insert_into_columns  = ' ' . $insert_into_columns;
		}

		if ( 0 === $start ) {
			// Make sure that identifiers get backticked, so we can safely do the RegEx replace in modify_create_view_query().
			//phpcs:ignore
			$wpdb->query( 'SET SESSION SQL_QUOTE_SHOW_CREATE=1;' ); // db call ok; no-cache ok.
			// Use of esc_sql() instead of $wpdb->prepare() because of backticks in query.
			//phpcs:ignore
			$table_create = $wpdb->get_row( esc_sql( "SHOW CREATE TABLE `{$table}`" ), ARRAY_A ); // db call ok; no-cache ok.

			if ( isset( $table_create['Create Table'] ) ) {
				$create_table_str = str_replace(
					'CREATE TABLE ' . $quoted_table . ' (',
					'CREATE TABLE IF NOT EXISTS ' . $quoted_table . ' (',
					$table_create['Create Table']
				);

				$drop_result = '' .
				/* translators: %s - DB table name */
				'# ' . sprintf( __( 'Snapshot table export for %s', 'snapshot' ), $table ) . "\n" .
				"DROP TABLE IF EXISTS {$quoted_table};" .
				$db_model->get_statement_delimiter() .
				$create_table_str . ';' .
				$db_model->get_statement_delimiter();

				file_put_contents( $temp_sql_file, $drop_result, FILE_APPEND ); // phpcs:ignore
			} elseif ( isset( $table_create['Create View'] ) ) {
				/* translators: %s - DB view name */
				$query = '# ' . sprintf( __( 'Snapshot view export for %s', 'snapshot' ), $table ) . "\n" .
					self::modify_create_view_query( $table_create['Create View'] ) .
					$db_model->get_statement_delimiter();

				file_put_contents( $temp_sql_file, $query, FILE_APPEND ); // phpcs:ignore

				$result['current_row'] = 0;
				$result['done']        = true;
				return $result;
			}//end if
		}//end if

		$table_data = $this->get_rows( $quoted_table, $start, $chunk );

		$entries = 'INSERT INTO ' . $quoted_table . $insert_into_columns . ' VALUES (';

		$search  = array( "\x00", "\x0a", "\x0d", "\x1a" );
		$replace = array( '\0', '\n', '\r', '\Z' );

		if ( $table_data ) {
			$statements = '';
			foreach ( $table_data as $row ) {

				$values = array();
				foreach ( $row as $value ) {

					if ( null === $value ) {
						$values[] = 'NULL';
					} else {
						$values[] = "'" . str_replace( $search, $replace, Db::sql_addslashes( $value ) ) . "'";
					}
				}

				$statements .= " \n" . $entries . implode( ', ', $values ) . ');' . $db_model->get_statement_delimiter();
				++$total_rows;
			}

			// Unset the table data since we're done extracting the SQL queries.
			$table_data = null;

			file_put_contents( $temp_sql_file, $statements, FILE_APPEND );  // phpcs:ignore

			$result['current_row'] = $total_rows + $start;
			if ( $result['current_row'] < $table_rows ) {
				// We still have to export part of the table at hand.
				$result['done'] = false;

				return $result;
			}
			// We are done exporting the table at hand.
			$result['done'] = true;

			return $result;
		}//end if

		// We are done exporting the table at hand, because it was empty.
		$result['current_row'] = 0;
		$result['done']        = true;
		return $result;
	}

	/**
	 * Dumps the database using 'MySQL Dump'
	 *
	 * @param string $table The database table to be exported.
	 * @param int    $limit The amount of rows to be exported at this step.
	 * @param int    $table_rows Total number of rows in the table.
	 * @param int    $offset The initial exported row's position.
	 *
	 * @return array
	 */
	public function dump( $table, $limit, $table_rows, $offset = 0 ) {
		$file = self::get_temp_sql_filename();

		$options = array(
			'offset' => $offset,
			'limit'  => $limit,
			'total'  => $table_rows,
			'table'  => $table,
		);

		// Initialize Dumper.
		$dumper = new Dumper( Connection::instance() );
		$result = $dumper->dump( $file, $options );

		return $result;
	}

	/**
	 * Modifies CREATE VIEW query.
	 *
	 * @param string $query CREATE VIEW query.
	 * @return string
	 */
	private static function modify_create_view_query( $query ) {
		$query = preg_replace( '/^CREATE\s+/', 'CREATE OR REPLACE ', $query );

		$query = preg_replace( '/DEFINER\s*=\s*`[^`]+`@`[^`]+`/u', 'DEFINER=CURRENT_USER', $query );

		return $query;
	}

	/**
	 * Returns temp sql filename, where the table contents will be saved in each step.
	 *
	 * @return string Full path to log file
	 */
	public static function get_temp_sql_filename() {
		$name = 'snapshot';

		// Conceal filename.
		$hash  = hash_hmac( 'sha1', $name, sha1( DB_PASSWORD ) );
		$name .= '-' . $hash;

		$dir = path_join( wp_upload_dir()['basedir'], 'snapshot-backups' );
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$filename = path_join( $dir, sanitize_file_name( $name ) . '-temp.txt' );
		return $filename;
	}

	/**
	 * Clears the temp sql file.
	 *
	 * @return bool
	 */
	public function clear_temp_sql_file() {
		$file = self::get_temp_sql_filename();

		if ( ! file_exists( $file ) ) {
			return true;
		}

		if ( ! unlink( $file ) ) { //phpcs:ignore
			return false;
		}

		return true;
	}
}