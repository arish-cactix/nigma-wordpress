<?php // phpcs:ignore
/**
 * Restore task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task;

use WPMUDEV\Snapshot4\Task;

/**
 * Restore task class
 */
class Restore extends Task {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id' => 'sanitize_key',
	);

	/**
	 * Does the initial actions needed to trigger a restore.
	 *
	 * @param array $args Restore arguments, like backup_id and rootpath.
	 */
	public function apply( $args = array() ) {
	}
}