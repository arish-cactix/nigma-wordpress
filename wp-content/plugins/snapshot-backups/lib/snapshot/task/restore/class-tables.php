<?php // phpcs:ignore
/**
 * Restore tables task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Restore;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Lock;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Restore tables task class
 */
class Tables extends Task {

	const PREFIX = 'sb_tmp_';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id' => null,
	// backup_id has already been sanitized in json_process_restore().
	);

	/**
	 * Restores tables.
	 *
	 * @param array $args Restore tables arguments.
	 */
	public function apply( $args = array() ) {
		global $wpdb;

		/**
		 * Tables model
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Restore\Tables
		 */
		$model = $args['model'];

		$backup_id = $model->get( 'backup_id' );

		// Lets get the tables to be restored, in the right order (considering, options-like tables and dependencies).
		$db_tables = $this->get_tables_list( $model );

		if ( empty( $db_tables ) ) {
			// We don't have database tables because tables can be excluded in the backup.
			$model->set( 'is_done', true );
			return;

			/**
				* $error_code    = 'extracted_tables_dir';
				* $error_message = __( 'Couldn\'t find the extracted tables in order to start importing them.', 'snaphsot' );
				* $this->add_error( $error_code, $error_message );
				* return;
				*/
		}

		$model->set( 'db_tables', $db_tables );

		foreach ( $db_tables as $db_table ) {
			if ( $model->get_table_done( $db_table ) ) {
				continue;
			}

			if ( $model->skip_table( $db_table ) ) {
				continue;
			}

			$model->set( 'db_table', $db_table );
			$statements = $model->read_db_file_chunked( $db_table );

			if ( empty( $statements ) ) {
				$model->add_errors( $this );
				return;
			}

			Log::info(
				sprintf(
					/* translators: %1s - number of statements, %2s - db table  */
					__( 'We are about to perform %1$s SQL statements for the %2$s table.', 'snapshot' ),
					count( $statements ),
					$db_table
				),
				array(),
				$model->get( 'backup_id' )
			);

			foreach ( $statements as $statement ) {
				$model->import_statement( $statement );

				if ( $model->add_errors( $this ) ) {
					return;
				}
			}

			Log::info(
				sprintf(
					/* translators: %1s - number of statements, %2s - db table  */
					__( '%1$s SQL statements performed seamlessly for the %2$s table.', 'snapshot' ),
					count( $statements ) - count( $model->get( 'db_errors', array() ) ),
					$db_table
				),
				array(),
				$model->get( 'backup_id' )
			);

			break;
			// One table at a time.
		}//end foreach

		if ( $model->get_table_done( $db_table ) ) {
			$model->finalize_table_import( $db_table );

			$table_name = preg_replace( '/^' . preg_quote( $wpdb->prefix, '/' ) . '/', '', $db_table );
			if ( 'usermeta' === $table_name ) {
				$this->flush_object_cache();
			}

			if ( $model->add_errors( $this ) ) {
				return;
			}
		}

		// Check if there are remaining tables to be restored. If not, we're done here.
		$remaining_tables = Model\Restore::get_db_tables( $backup_id );
		if ( empty( $remaining_tables ) && false !== $remaining_tables ) {
			$model->set( 'is_done', true );
		}
	}

	/**
	 * Gets a cached list of tables.
	 *
	 * @param Model\Restore\Tables $model Tables model.
	 *
	 * Updates cache if needed as a side-effect.
	 *
	 * @return array
	 */
	public function get_tables_list( $model ) {
		$tables = Lock::read( $model->get( 'backup_id' ), 'tablelist' );

		if ( empty( $tables ) ) {
			$tables = $model->get_table_names_from_files();

			if ( ! empty( $tables ) ) {
				// Update cached list for future reference.
				Lock::write( $tables, $model->get( 'backup_id' ), 'tablelist' );
			}
		}

		return $tables;
	}

	/**
	 * Restores tables.
	 *
	 * @param string $backup_id Backup ID.
	 *
	 * @return string|bool.
	 */
	public function get_old_db_prefix( $backup_id ) {
		$manifest = path_join( Model\Restore::get_intermediate_destination( $backup_id ), 'sql/manifest.txt' );
		if ( ! file_exists( $manifest ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$manifest_contents = json_decode( file_get_contents( $manifest ), true );

		return $manifest_contents['db_prefix'];
	}

	/**
	 * Cleans up after db restoration is done.
	 */
	public function cleanup() {
		// Delete db entries that were added while backing up. They're useless now.
		delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP );
		delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP_STATUS );
		delete_transient( 'snapshot_listed_backups' );
		delete_transient( 'snapshot_current_stats' );

		$this->flush_object_cache();
	}

	/**
	 * Flush object cache.
	 */
	private function flush_object_cache() {
		global $wp_object_cache;

		if ( is_object( $wp_object_cache ) && is_callable( array( $wp_object_cache, 'flush' ) ) ) {
			$wp_object_cache->flush( 0 );
		} elseif ( is_callable( 'wp_cache_flush' ) ) {
				wp_cache_flush();
		}
	}
}