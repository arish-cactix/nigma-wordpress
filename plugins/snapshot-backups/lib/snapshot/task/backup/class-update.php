<?php // phpcs:ignore
/**
 * Update the running backup.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;

/**
 * Update the running backup.
 */
class Update extends Task {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id'   => 'sanitize_key',
		'description' => 'sanitize_textarea_field',
	);

	/**
	 * Updates the backup description.
	 *
	 * @param array $args Arguments to use in the task.
	 *
	 * @return mixed
	 */
	public function apply( $args = array() ) {
		$backup_id = $args['backup_id'];
		$model     = $args['model'];

		$model->set( 'description', $args['description'] );
		$response = $model->update_backup( $backup_id );

		if ( $model->add_errors( $this ) ) {
			return false;
		}

		return $response;
	}
}