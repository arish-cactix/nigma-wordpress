<?php // phpcs:ignore
/**
 * Controllers are responsible for mapping requests and events to appropriate
 * actions to be taken.
 *
 * The actions are handled by atomic tasks, boostrapped by controllers.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4;

use WPMUDEV\Snapshot4\Helper\Singleton;

/**
 * Controller abstraction class
 */
abstract class Controller extends Singleton {

	const SNAPSHOT_RUNNING_BACKUP              = 'snapshot_running_backup';
	const SNAPSHOT_RUNNING_BACKUP_STATUS       = 'snapshot_running_backup_status';
	const SNAPSHOT_MANUAL_BACKUP_TRIGGER_TIME  = 'snapshot_manual_backup_trigger_time';
	const SNAPSHOT_LATEST_BACKUP               = 'snapshot_latest_backup';
	const SNAPSHOT_CANCELLED_BACKUP            = 'snapshot_cancelled_backup';
	const SNAPSHOT_CANCELLED_BACKUP_PERSISTENT = 'snapshot_cancelled_backup_persistent';

	/**
	 * Boots the controller and sets up event listeners.
	 */
	abstract public function boot();
}