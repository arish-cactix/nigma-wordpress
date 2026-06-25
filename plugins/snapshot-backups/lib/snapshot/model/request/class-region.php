<?php // phpcs:ignore
/**
 * Snapshot models: Get/set creds request model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Model;

/**
 * Gets/sets creds (region, storage limit) request model class
 */
class Region extends Model\Request {
	const DEFAULT_ERROR = 'snapshot_region_service_unreachable';

	/**
	 * Set creds request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'creds';

	/**
	 * Returns action string for logger
	 *
	 * @return string
	 */
	protected function get_action_string() {
		if ( 'set' === $this->get( 'action' ) ) {
			return __( 'set region', 'snapshot' );
		}

		return __( 'get region', 'snapshot' );
	}

	/**
	 * Make request to get the stored creds from system-side.
	 *
	 * @return array|mixed|object
	 */
	public function get_credsls() {
		$method         = 'get';
		$this->endpoint = 'credsls';
		$path           = $this->get_api_url();

		$data = array();

		$this->request( $path, $data, $method );

		if ( 404 === $this->get_response_code() ) {
			// No region set yet.
			return null;
		}

		$response = json_decode( $this->response_body, true );

		return $response;
	}

	/**
	 * Make request to set the stored region system-side.
	 *
	 * @param string $region Region to be stored.
	 *
	 * @return array|mixed|object
	 */
	public function set_region( $region ) {
		$method = 'post';
		$path   = $this->get_api_url();

		$data              = array();
		$data['bu_region'] = $region;

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Make request to set the stored storage limit system-side.
	 *
	 * @param int $storage_limit The new storage limit based on which backups will be rotated.
	 *
	 * @return array|mixed|object
	 */
	public function set_storage( $storage_limit ) {
		$method = 'post';
		$path   = $this->get_api_url();
		$data   = array();

		$data['rotation_frequency'] = $storage_limit;

		$response = $this->request( $path, $data, $method );

		return $response;
	}
}