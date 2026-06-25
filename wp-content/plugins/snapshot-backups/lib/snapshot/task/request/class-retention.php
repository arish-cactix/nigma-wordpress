<?php // phpcs:ignore
/**
 * Runs retention task from the plugin.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request;

use WPMUDEV\Snapshot4\Task;

/**
 * Runs force retention task from the plugin once every 24 hours.
 */
class Retention extends Task {

	const ERR_SERVICE_UNREACHABLE = 'snapshot_run_force_retention_service_unreachable';

	/**
	 * Runs retention task.
	 *
	 * @param array $args Args fro retention.
	 * @return boolean
	 */
	public function apply( $args = array() ) {
		/**
		 * Retention request model
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Request\Retention
		 */
		$request_model = $args['request_model'];

		$request_model->set( 'ok_codes', array() );

		// We don't care about the response.
		$request_model->ping(
			array(
				'timeout' => 5,
			)
		);

		set_site_transient( 'snapshot_retention_job', true, DAY_IN_SECONDS );
		return true;
	}
}