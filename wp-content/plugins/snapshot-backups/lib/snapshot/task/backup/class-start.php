<?php // phpcs:ignore
/**
 * Start backup from service.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Controller;

/**
 * Start backup task class
 */
class Start extends Task {

	const ERR_STRING_REQUEST_PARAMS = 'Request for starting backup was not successful';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'snapshot_id' => 'sanitize_text_field',
		'created_at'  => 'sanitize_text_field',
	);

	/**
	 * Does the required actions for when a backup is actually started service-side.
	 *
	 * @param array $args Model.
	 */
	public function apply( $args = array() ) {
		$model  = $args['model'];
		$backup = $model->get( 'backup' );

		update_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP, $backup );
		update_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP_STATUS, 'snapshot_initiated' );
		delete_site_option( Controller\Ajax\Backup::SNAPSHOT_MANUAL_BACKUP_TRIGGER_TIME );

		// Thats for logging to check which is the latest backup, in order to put logs irrelevant to backups at the latest backup's logs.
		update_site_option( Controller\Ajax\Backup::SNAPSHOT_LATEST_BACKUP, $backup['id'] );
	}
}