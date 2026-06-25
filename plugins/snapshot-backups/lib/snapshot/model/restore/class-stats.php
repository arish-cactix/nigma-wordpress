<?php
/**
 * Stats request model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Restore;

use WPMUDEV\Snapshot4\Model\Request;
/**
 * Stats Request Class
 */
class Stats extends Request {

	/**
	 * Stats constructor
	 */
	public function __construct() {
		// Initialize the endpoint.
		$this->endpoint = 'restores';
	}

	/**
	 * POST request: Stores the restore stats
	 *
	 * @param array $data Data for restore stats.
	 *
	 * @return array
	 */
	public function store( array $data ): array {
		$api_url = $this->get_api_url();

		$result = $this->request( $api_url, $data );

		return $this->get_response( $result );
	}

	/**
	 * PUT request: Updates the restore status for the provided
	 *
	 * @param string $restore_id Restore id.
	 * @param string $status    Restore Status.
	 * @return array
	 */
	public function update( string $restore_id, string $status ): array {
		$api_url = sprintf( '%s/%s', $this->get_api_url(), $restore_id );

		$data = array(
			'restore_status' => $status,
		);

		$result = $this->request( $api_url, $data, 'put' );

		return $this->get_response( $result );
	}

	/**
	 * Undocumented function
	 *
	 * @param array|mixed|object $request Remote request.
	 * @return array
	 */
	private function get_response( $request ): array {
		if ( is_wp_error( $request ) ) {
			return array();
		}

		$code = wp_remote_retrieve_response_code( $request );
		if ( $code < 200 && $code >= 300 ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $request );
		return json_decode( $body, true );
	}
}