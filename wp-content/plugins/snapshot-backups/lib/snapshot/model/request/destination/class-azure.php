<?php // phpcs:ignore
/**
 * Snapshot models: Azure destination requests model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request\Destination;

use WPMUDEV\Snapshot4\Model;

/**
 * Destination requests model class
 */
class Azure extends Model\Request\Destination {

	/**
	 * Get containers of Azure destination.
	 *
	 * @param array $data Config data.
	 *
	 * @return array|mixed|object array of containers if creds were correct.
	 */
	public function load_containers( $data ) {
		$method         = 'post';
		$this->endpoint = 'tpd_bucketls';
		$path           = $this->get_api_url();

		$response = $this->request( $path, $data, $method );

		return $response;
	}
}