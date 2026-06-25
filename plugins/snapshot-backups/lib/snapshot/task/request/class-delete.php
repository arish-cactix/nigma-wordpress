<?php // phpcs:ignore
/**
 * Delete all backups task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request;

use WPMUDEV\Snapshot4\Model\Log;
use WPMUDEV\Snapshot4\Task;

/**
 * Delete all backups task class.
 */
class Delete extends Task {
	const ERR_SERVICE_UNREACHABLE = 'snapshot_delete_all_backups_service_unreachable';

	/**
	 * Delete ALL backups.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		$request_model = $args['request_model'];

		// site id *** have no snapshot.
		$request_model->set( 'ok_codes', array( 404 ) );

		$response = $request_model->delete_all_backups();
		if ( $request_model->add_errors( $this ) ) {
			return false;
		}

		delete_transient( 'snapshot_listed_backups' );
		delete_transient( 'snapshot_current_stats' );

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		return $result;
	}
}