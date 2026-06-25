<?php // phpcs:ignore
/**
 * Snapshot models: Export list model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request\Export;

use WPMUDEV\Snapshot4\Model;

/**
 * Export list requests model class
 */
class Listing extends Model\Request {

	/**
	 * Export list request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'exportsls';

	/**
	 * Retrieves backup exports.
	 *
	 * @param string $backup_id Backup ID.
	 *
	 * @return array|mixed|object
	 */
	public function get_list( $backup_id ) {
		$data   = array();
		$method = 'get';
		$path   = trailingslashit( $this->get_api_url() ) . $backup_id;

		$response = $this->request( $path, $data, $method );

		return $response;
	}
}