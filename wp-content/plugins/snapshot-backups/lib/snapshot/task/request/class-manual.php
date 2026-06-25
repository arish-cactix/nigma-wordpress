<?php // phpcs:ignore
/**
 * Manual backup trigger task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper;

/**
 * Manual backup trigger task class.
 */
class Manual extends Task {
	const ERR_SERVICE_UNREACHABLE = 'snapshot_manual_backup_service_unreachable';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_name'      => 'sanitize_text_field',
		'description'      => 'sanitize_textarea_field',
		'apply_exclusions' => 'boolval',
	);

	/**
	 * Triggers a manual backup.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		$backup['name'] = $args['backup_name'];
		$backup['id']   = 'manual';
		if ( empty( $backup['name'] ) ) {
			$backup['name'] = __( 'Snapshot', 'snapshot' ) . ' - ' . Helper\Datetime::format( time() );
		}
		$model = $args['model'];

		$model->set( 'description', $args['description'] );
		$model->set( 'apply_exclusions', $args['apply_exclusions'] );

		// This is where we are going to handle the API request to trigger the manual backup.
		$model->trigger_manual_backup( $backup['name'] );

		if ( $model->add_errors( $this ) ) {
			return false;
		}

		update_site_option( Controller::SNAPSHOT_RUNNING_BACKUP, $backup );
		update_site_option( Controller::SNAPSHOT_RUNNING_BACKUP_STATUS, 'just_triggered' );
		update_site_option( Controller::SNAPSHOT_MANUAL_BACKUP_TRIGGER_TIME, time() );
		// This, so we can manually fail the running manual backup in case it hasnt gotten a snapshot_id assigned from Task\Start after 30mins.

		// And this is where we are going to handle the display of the newly triggered backup.
		$task  = new Task\Backup\Progress();
		$model = new Model\Backup\Progress( false );

		$model->set( 'backup_running', $backup );
		$model->set( 'backup_running_status', 'just_triggered' );

		$args['model'] = $model;
		$result        = $task->apply( $args );

		return $result;
	}
}