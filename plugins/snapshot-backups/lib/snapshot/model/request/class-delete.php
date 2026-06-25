<?php // phpcs:ignore
/**
 * Snapshot models: Delete all backups request model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Model;

/**
 * Delete all backups request model class
 */
class Delete extends Model\Request {
	const DEFAULT_ERROR = 'snapshot_delete_all_backups_service_unreachable';

	/**
	 * Delete all backups request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'snapshotsls';

	/**
	 * Returns action string for logger
	 *
	 * @return string
	 */
	protected function get_action_string() {
		return __( 'delete all backups', 'snapshot' );
	}

	/**
	 * Make request to delete all backups
	 *
	 * @return array|mixed|object
	 */
	public function delete_all_backups() {
		$method = 'delete';
		$path   = $this->get_api_url();

		$data = array();

		$response = $this->request( $path, $data, $method );

		return $response;
	}
}