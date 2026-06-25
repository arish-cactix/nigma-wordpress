<?php // phpcs:ignore
/**
 * Dblist exchange between plugin and service
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Db;

/**
 * Dblist exchange task class
 */
class Dblist extends Task {

	const ERROR_EMPTY_DB      = 'snapshot_empty_db';
	const WARNING_EMPTY_TABLE = 'snapshot_empty_table';

	const ERR_STRING_REQUEST_PARAMS = 'Request for DB list was not successful';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'ex_rt'       => 'intval',
		'tables_left' => self::class . '::validate_tables',
	);

	/**
	 * All db tables.
	 *
	 * @var array
	 */
	private static $all_tables = array();

	/**
	 * Checks tables param against the actual db tables.
	 *
	 * @param array $tables tables left to be iterated.
	 *
	 * @return array|false
	 */
	public static function validate_tables( $tables ) {
		if ( empty( self::$all_tables ) ) {
			self::$all_tables = Db::get_all_database_tables();
		}

		foreach ( $tables as $table ) {
			if ( ! in_array( $table, array_column( self::$all_tables, 'name' ), true ) ) {
				// We can't go through with the db iteration.
				return false;
			}
		}

		return $tables;
	}

	/**
	 * Runs over the site's db tables and returns all info to the controller.
	 *
	 * @param array $args Info about what time the file iteration started and its timelimit.
	 */
	public function apply( $args = array() ) {
		$model = $args['model'];

		$this->get_tables( $model );

		if ( empty( $model->get( 'tables_left' ) ) ) {
			// So we are done. Say so.
			$model->set( 'is_done', true );
		}
	}

	/**
	 * Runs over the site's db tables and returns all info to the controller.
	 *
	 * @param object $model Model\Backup\Dblist instance.
	 */
	public function get_tables( $model ) {
		$new_tables = array();
		$tables     = $model->get( 'tables_left' );

		$model->set( 'db_name', Db::get_db_name() );
		if ( empty( $tables ) ) {
			$tables = empty( self::$all_tables ) ? Db::get_all_database_tables() : self::$all_tables;
			if ( ! empty( $tables ) ) {
				$filtered_tables = apply_filters( 'snapshot_tables_for_backup', array_column( $tables, 'name' ) );
				foreach ( $tables as $table ) {
					if ( in_array( $table['name'], $filtered_tables, true ) ) {
						$new_tables[] = $table;
					}
				}
			}
		} else {
			$all_tables = empty( self::$all_tables ) ? Db::get_all_database_tables() : self::$all_tables;
			foreach ( $all_tables as $db_table ) {
				if ( in_array( $db_table['name'], $tables, true ) ) {
					$new_tables[] = $db_table;
				}
			}
		}
		$exclusions          = Db::get_tables_exclusions();
		$are_tables_excluded = ! empty( $exclusions );
		$model->set( 'excluded_tables', $exclusions );
		$model->set( 'tables_excluded', $are_tables_excluded );

		$tables = $new_tables;

		if ( empty( $tables ) && ! $are_tables_excluded ) {
			// Something went wrong with retrieving db tables. - Lets show an ERROR in the log and return error in service.
			$this->add_error(
				self::ERROR_EMPTY_DB,
				__( 'Empty db - Snapshot faced an issue when trying to get the db\'s tables.', 'snapshot' )
			);
			return false;
		}

		while ( ! empty( $tables ) ) {
			$item  = array();
			$table = array_pop( $tables );

			$item['name'] = $table['name'];

			if ( $table['is_view'] ) {
				$item['checksum'] = $this->get_view_checksum( $table['name'] );
				$item['size']     = 0;
			} else {
				$item['checksum'] = $this->get_table_checksum( $table['name'] );
				if ( null === $item['checksum'] ) {
					// Something went wrong with getting the table's checksum. - Lets show an ERROR in the log.
					$this->add_error(
						self::WARNING_EMPTY_TABLE,
						/* translators: %s - table name */
						sprintf( __( 'Unreachable table %s: Snapshot faced an issue when trying to get the table\'s checksum.', 'snapshot' ), $table['name'] )
					);
					return false;
				}

				$item['size'] = $this->get_table_size( $table['name'] );
			}

			$model->add( 'tables', $item );
			$model->set( 'tables_left', array_column( $tables, 'name' ) );

			// If we have exceed the imposed time limit, lets pause the iteration here.
			if ( $model->has_exceeded_timelimit() ) {
				break;
			}
		}//end while
	}

	/**
	 * Calculates the checksum of the table.
	 *
	 * @param string $table Table to calculate its checksum.
	 *
	 * @return string $results['Checksum'] Checksum of table.
	 */
	public function get_table_checksum( $table ) {
		global $wpdb;
		//@phpcs:ignore
		$results = $wpdb->get_row( esc_sql( "CHECKSUM TABLE `{$table}`" ), ARRAY_A ); // db call ok; no-cache ok.

		return apply_filters( 'wp_snapshot_table_checksum', $results['Checksum'], $table );
	}

	/**
	 * Emulates the "checksum" of the view based on its structure.
	 *
	 * @param string $view View to calculate its "checksum".
	 * @return string "Checksum" of view.
	 */
	public function get_view_checksum( $view ) {
		global $wpdb;
		//@phpcs:ignore
		$row = $wpdb->get_row( esc_sql( "SHOW CREATE TABLE `{$view}`" ), ARRAY_A ); // db call ok; no-cache ok.

		$result = '';
		if ( $row['Create View'] ) {
			$result = substr( sha1( $row['Create View'] ), 0, 10 );
		}

		return apply_filters( 'wp_snapshot_table_checksum', $result, $view );
	}

	/**
	 * Calculates the size of the table.
	 *
	 * @param string $table Table to calculate its checksum.
	 *
	 * @return string $results['Checksum'] Checksum of table.
	 */
	public function get_table_size( $table ) {
		global $wpdb;

		$db_name = Db::get_db_name();

		//@phpcs:ignore
		$table_size = $wpdb->get_var( $wpdb->prepare( 'SELECT ( DATA_LENGTH + INDEX_LENGTH ) FROM information_schema.tables WHERE table_schema = %s AND table_name LIKE %s', $db_name, $table ) ); // db call ok; no-cache ok.

		return $table_size;
	}

	/**
	 * Get the total number of rows of the table.
	 *
	 * @param string $table Table name.
	 *
	 * @return string|null
	 */
	public function get_table_rows( $table ) {
		global $wpdb;

		$db_name = Db::get_db_name();
		//@phpcs:ignore
		$rows    = $wpdb->get_var( $wpdb->prepare( 'SELECT TABLE_ROWS as total_rows FROM information_schema.tables WHERE table_schema = %s AND table_name = %s', $db_name, $table ) ); // db call ok; no-cache ok.

		return $rows;
	}
}