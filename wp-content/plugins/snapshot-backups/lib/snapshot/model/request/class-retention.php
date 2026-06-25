<?php // phpcs:ignore
/**
 * Snapshot models: Retention
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Model;

/**
 * Force retention request model class
 */
class Retention extends Model\Request {

	const DEFAULT_ERROR = 'snapshot_retention_service_unreachable';

	/**
	 * Set creds request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'force_retention';

	/**
	 * Make request to apply the force retention.
	 *
	 * It just pings and doesn't wait for the response.
	 *
	 * @param  array $args Args for ping.
	 *
	 * @return mixed
	 */
	public function ping( $args ) {
		return $this->request( $this->get_api_url(), $args, 'get' );
	}
}