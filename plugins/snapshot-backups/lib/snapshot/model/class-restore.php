<?php // phpcs:ignore
/**
 * Snapshot restore model class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper\Lock;

/**
 * Restore model class
 */
class Restore extends Model {

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Gets the intermediate dir where the backup zip is gonna extract to.
	 *
	 * @param string $backup_id Id of the backup to be restored.
	 */
	public static function get_intermediate_destination( $backup_id ) {
		$destination = path_join( Lock::get_lock_dir(), $backup_id . '/imports' );

		return $destination;
	}

	/**
	 * Retrieves every sql file exported and ready to be restored, in the intermediate dir.
	 *
	 * @param array $backup_id Id of backup to be restored.
	 */
	public static function get_db_tables( $backup_id ) {
		$tables = array();
		$dir    = path_join( self::get_intermediate_destination( $backup_id ), 'sql' );

		if ( ! file_exists( $dir ) ) {
			return false;
		}

		$table_files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $table_files as $table_file ) {
			$file_info = pathinfo( $table_file );

			if ( 'sql' === $file_info['extension'] ) {
				$is_usermeta = strpos( $file_info['basename'], '_usermeta' );

				if ( false === $is_usermeta ) {
					$tables[] = $file_info;
				} else {
					array_unshift( $tables, $file_info );
				}
			}
		}

		return $tables;
	}

	/**
	 * Cleans any remaining stuff from the fs and the db.
	 *
	 * This happens both at the start of a new restore and at the end of it.
	 *
	 * @param bool   $manual_restore Whether manual mode is on.
	 * @param string $initiator Who triggered the cleanup.
	 */
	public static function clean_residuals( $manual_restore = false, $initiator = 'process' ) {
		if ( ! $manual_restore ) {
			Lock::remove_lock_dir();
		}

		// We have to remove backup download and file iteration markers.
		delete_site_option( Controller\Ajax\Restore::SNAPSHOT_DOWNLOAD_BACKUP_PROGRESS );
		delete_site_option( Model\Restore\Files::KEY_PATHS );
		delete_site_option( Model\Restore\Files::KEY_LAST_PATHS );

		if ( ! $manual_restore ) {
			// We have to unify the plugin and service schedules again, in case we have a mismatch now that we restored the local db.
			Snapshot4\Main::handle_schedules();
		}
	}
}