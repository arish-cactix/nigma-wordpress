<?php // phpcs:ignore
/**
 * Hosting backup API requests.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Hosting;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model\Env;

/**
 * Hosting backups requesting abstract class
 */
abstract class Common extends Task {

	/**
	 * Requesting hosting backups API
	 *
	 * @param string $method HTTP method.
	 * @param string $path request path.
	 * @param array  $params HTTP params.
	 * @return array|\WP_Error The response or WP_Error on failure.
	 */
	protected function request( $method, $path, array $params = array() ) {
		$method   = strtolower( $method );
		$response = new \WP_Error();

		if ( Env::is_wpmu_hosting() && Env::get_wpmu_hosting_site_id() ) {
			$api_key = Env::get_wpmu_api_key();
			$site_id = Env::get_wpmu_hosting_site_id();
			$url     = sprintf(
				Env::get_wpmu_api_server_url() . 'api/hosting/v1/%s/' . $path,
				rawurlencode( $site_id )
			);

			$args = array(
				'timeout' => 60,
				'headers' => array(
					'Authorization' => $api_key,
				),
			);

			if ( 'get' === $method ) {
				if ( count( $params ) ) {
					$url .= '?' . http_build_query( $params );
				}
				$response = wp_remote_get( $url, $args );
			} elseif ( 'post' === $method ) {
				$args['body'] = $params;
				$response     = wp_remote_post( $url, $args );
			}
		}//end if

		return $response;
	}
}