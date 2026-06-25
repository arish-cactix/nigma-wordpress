<?php // phpcs:ignore
/**
 * Finish backup from service.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Finish backup task class
 */
class Finish extends Task {

	/**
	 * Does the required actions for when a backup is finished service-side
	 *
	 * @param array $args Task args.
	 */
	public function apply( $args = array() ) {
		$backup_id = Log::get_backup_id();

		delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP );
		delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP_STATUS );
		delete_site_option( Controller\Ajax\Backup::SNAPSHOT_MANUAL_BACKUP_TRIGGER_TIME );

		delete_transient( 'snapshot_listed_backups' );
		delete_transient( 'snapshot_current_stats' );

		Log::set_backup_id( $backup_id );

		$temp_sql_file = Model\Backup\Zipstream\Tables::get_temp_sql_filename();
		if ( file_exists( $temp_sql_file ) ) {
			unlink( $temp_sql_file ); //phpcs:ignore
		}
	}
}