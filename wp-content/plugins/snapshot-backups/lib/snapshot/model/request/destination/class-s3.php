<?php // phpcs:ignore
/**
 * Snapshot models: S3 destination requests model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request\Destination;

use WPMUDEV\Snapshot4\Model;

/**
 * Destination requests model class
 */
class S3 extends Model\Request\Destination {

	/**
	 * Get buckets of S3 destination.
	 *
	 * @param array $data Config data.
	 *
	 * @return array|mixed|object array of buckets for that region if creds were correct.
	 */
	public function load_buckets( $data ) {
		$method         = 'post';
		$this->endpoint = 'tpd_bucketls';
		$path           = $this->get_api_url();

		$response = $this->request( $path, $data, $method );

		return $response;
	}
}