<?php // phpcs:ignore
/**
 * Snapshot models: Export status requests model
 *
 * Holds information for communication with the service about retrieving the status of an export.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request\Export;

use WPMUDEV\Snapshot4\Model;

/**
 * Export status requests model class
 */
class Status extends Model\Request {

	/**
	 * Export status backups request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'exports';

	/**
	 * Retrieves the status of the export.
	 *
	 * @param string $export_id Export ID.
	 *
	 * @return array|mixed|object
	 */
	public function get_status( $export_id ) {
		$data   = array();
		$method = 'get';
		$path   = trailingslashit( $this->get_api_url() ) . $export_id;

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Returns string to be used when an export has failed on restoring.
	 *
	 * @return string
	 */
	public function get_status_error_string() {
		return esc_html__( 'a backup was being exported', 'snapshot' );
	}
}