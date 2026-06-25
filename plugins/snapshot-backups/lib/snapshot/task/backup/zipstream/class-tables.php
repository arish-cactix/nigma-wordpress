<?php // phpcs:ignore
/**
 * Fetching a backup zipstream of requested tables from the plugin to the service.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup\Zipstream;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Db;
use WPMUDEV\Snapshot4\Helper\Pattern;
use WPMUDEV\Snapshot4\Helper\Settings;

/**
 * Backup zipstream task class
 */
class Tables extends Task\Backup\Zipstream {

	const ERR_CLEAN_TEMP_FILE       = 'snapshot_is_done_tables';
	const ERR_STRING_REQUEST_PARAMS = 'Request for table zipstream was not successful';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'db_chunk'     => 'intval',
		'table'        => self::class . '::validate_table',
		'starting_row' => 'intval',
	);

	/**
	 * All db tables.
	 *
	 * @var array
	 */
	private static $all_tables = array();

	/**
	 * Checks table param against the actual db tables.
	 *
	 * @param array $table table to be exported.
	 *
	 * @return array|false
	 */
	public static function validate_table( $table ) {
		if ( empty( self::$all_tables ) ) {
			self::$all_tables = Db::get_all_database_tables();
		}

		if ( ! in_array( $table, array_column( self::$all_tables, 'name' ), true ) ) {
			// We can't go through with the db iteration.
			return false;
		}

		return $table;
	}

	/**
	 * Runs over the requested tables and builds a zipstream out of them.
	 *
	 * @param array $args Info about the current table requesting, like the rows we're gonna split the export into , what we're continuing to export from, etc.
	 */
	public function apply( $args = array() ) {
		require dirname( SNAPSHOT_PLUGIN_FILE ) . '/vendor/autoload.php';

		/**
		 * Tables backup model
		 *
		 * @var Model\Backup\Zipstream\Tables
		 */
		$model = $args['model'];

		// Clear the temporary sql file from previous exports.
		if ( ! $model->clear_temp_sql_file() ) {
			/* translators: %s - Temp sql file name */
			$this->add_error( self::ERR_CLEAN_TEMP_FILE, sprintf( __( 'Snapshot couldn\'t clear out the temporary sql file before exporting the db: %s', 'snapshot' ), $model->get_temp_sql_filename() ) );
			return false;
		}

		// If we've any output, we need to discard them.
		if ( ob_get_level() > 0 ) {
			$content = ob_get_clean();
			unset( $content );
		}

		// Enable output of HTTP headers.
		$zipstream_options = new \ZipStream\Option\Archive();
		$zipstream_options->setSendHttpHeaders( true );
		if ( Settings::get_zipstream_flush_buffer() ) {
			$zipstream_options->setFlushOutput( true );
		}

		// Actually zipstream the rows of the requested table.
		$this->zipstream_table( $model, $zipstream_options );
	}

	/**
	 * Builds a zipstream out of exporting the requested table from the row we left off to the row the chunk arg is allowing us to.
	 *
	 * @param \WPMUDEV\Snapshot4\Model\Backup\Zipstream\Tables $model Instance.
	 * @param \ZipStream\Option\Archive                        $options instance.
	 */
	public function zipstream_table( $model, $options ) {
		/**
		 * WPDB instance
		 *
		 * @var \wpdb $wpdb
		 */
		global $wpdb;

		// Create a new zipstream object.
		$zip   = new \ZipStream\ZipStream( $model->name_zipstream(), $options );
		$table = $model->get( 'requested_table' );

		$table_rows = $model->get_rows_count( $table );
		// @TODO: Improve by calculating the above in the beginning of the backup and make it persistent.

		// Export the table.
		$table_starting_row = $model->get( 'starting_row' );
		$dump_method        = $model->get( 'dump_method' );

		if ( 'mysqldump' === $dump_method ) {
			// Uses MySQL DUMP.
			$dump = $model->dump( $table, $model->get( 'db_chunk' ), $table_rows->total_rows, $table_starting_row );

			/**
				* Since we can't get the exact number of rows dumped through mysqldump
				* we have to rely on the regex pattern where we count the number of
				* "INSERT INTO `{$table}`" statement in the dumped file. We might have
				* a false result if the table data contains "INSERT INTO `{$table}`"
				* but it is pretty rate to find. This might happen on
				* "Tutorials site".
				*/
			$matches = ( new Pattern( $table, $dump['file'] ) )
				->build_insert_into_pattern()
				->match();

			$result = array();

			$result['done']        = true;
			$result['current_row'] = abs( $matches['count'] ) + $table_starting_row;

			if ( $result['current_row'] < $table_rows->total_rows ) {
				$result['done'] = false;
			}
		} else {
			// Default 'php_code' method.
			$result = $model->backup_table( $table, $model->get( 'db_chunk' ), $table_rows->total_rows, $table_starting_row );
		}//end if

		$model->set( 'done', $result['done'] );
		$model->set( 'current_row', $result['current_row'] );

		// And add it to the zipstream.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$zip->addFile( $table . '.sql', file_get_contents( $model->get_temp_sql_filename() ) );

		// But also include the manifest file that contains the info on where we left off.
		$manifest_contents['table']       = $table;
		$manifest_contents['done']        = $result['done'];
		$manifest_contents['current_row'] = $result['current_row'];
		$manifest_contents['db_prefix']   = $wpdb->base_prefix;

		$zip->addFile( 'manifest.txt', wp_json_encode( $manifest_contents, JSON_FORCE_OBJECT ) );

		// finish the zip stream.
		$zip->finish();
	}
}