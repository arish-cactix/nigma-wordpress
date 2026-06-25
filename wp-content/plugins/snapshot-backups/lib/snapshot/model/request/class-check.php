<?php
/**
 * IP Check Model.
 *
 * @package Snapshot
 * @since   4.4.0
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Model\Request;

/**
 * Prerequisites checking class
 */
class Check extends Request {

	/**
	 * Allowlisted IPs endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = 'check_whitelisted_ip';

	/**
	 * Sends the request to check the IPs.
	 *
	 * @return WP_Error|Array
	 */
	public function check_ips() {
		$data = array();

		$data['site_name'] = $this->get( 'site_name' );
		$path              = $this->get_api_url();

		return $this->request( $path, $data );
	}
}