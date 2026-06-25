<?php // phpcs:ignore
/**
 * Delete backup task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Delete backup task class.
 */
class Delete extends Task {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id' => 'sanitize_text_field',
	);

	/**
	 * Takes the info about the running backup from the db and displays the appropriate row.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		$model = $args['model'];

		$model->delete_backup( $model->get( 'backup_id' ) );

		if ( $model->add_errors( $this ) ) {
			return false;
		}

		delete_transient( 'snapshot_listed_backups' );
		delete_transient( 'snapshot_current_stats' );

		Log::clear( $model->get( 'backup_id' ) );

		return true;
	}
}