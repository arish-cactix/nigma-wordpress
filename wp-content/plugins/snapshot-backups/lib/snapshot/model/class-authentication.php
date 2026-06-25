<?php
/**
 * Authentication model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Authentication\Credentials;
use WPMUDEV\Snapshot4\Model;
/**
 * Authentication class to make request to the API.
 */
class Authentication extends Model\Request {

	/**
	 * Endpoint for requests.
	 *
	 * @var string
	 */
	protected $endpoint = 'http_creds';

	/**
	 * Stores the credentials.
	 *
	 * @param Credentials $credentials Credentials.
	 *
	 * @return WP_Error | array
	 */
	public function store( Credentials $credentials ) {
		$data = $credentials->mapped();
		$path = $this->get_api_url();

		$response = $this->request( $path, $data );
		return $response;
	}

	/**
	 * Retrieve the credentials stored in API.
	 *
	 * @return WP_Error|array
	 */
	public function retrieve() {
		$path = $this->get_api_url();
		$data = array();

		$response = $this->request( $path, $data, 'get' );
		return $response;
	}

	/**
	 * Update credentials.
	 *
	 * @param Credentials $credentials Credentials.
	 * @return mixed
	 */
	public function update( Credentials $credentials ) {
		$data = $credentials->mapped();
		$path = $this->get_api_url();

		$response = $this->request( $path, $data, 'put' );
		return $response;
	}

	/**
	 * Delete the existing credentials.
	 *
	 * @return mixed
	 */
	public function delete() {
		$path = $this->get_api_url();
		$data = array();

		$response = $this->request( $path, $data, 'delete' );
		return $response;
	}
}