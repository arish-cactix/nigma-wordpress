<?php // phpcs:ignore
/**
 * Cancel running backup.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Controller;

/**
 * Cancel running backup task class
 */
class Cancel extends Task {

	const ERR_STRING_REQUEST_PARAMS = 'Cancelling backup was not successful';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id' => 'sanitize_key',
	);

	/**
	 * Stores the backup as cancelled.
	 *
	 * @param array $args Arguments to use in the task.
	 */
	public function apply( $args = array() ) {
		$backup_id = $args['backup_id'];

		// Add a temporary entry, for the backup processes to check and not go through with their tasks (while we wait for the service to actually cancel the running backup).
		update_site_option( Controller\Ajax\Backup::SNAPSHOT_CANCELLED_BACKUP, $backup_id );
	}
}