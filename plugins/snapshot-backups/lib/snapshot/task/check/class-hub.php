<?php
/**
 * Hub API readiness check.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Check;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Model\Env;

/**
 * Hub API check class.
 */
class Hub extends Task {

	const ERR_DASH_PRESENT = 'snapshot_dash_present';
	const ERR_DASH_ACTIVE  = 'snapshot_dash_active';
	const ERR_DASH_APIKEY  = 'snapshot_dash_api_key';

	/**
	 * Checks whether we're overall Hub connection-ready.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool|string|\WP_Error True if we are ready, false if one of pre-conditions failed (see errors).
	 */
	public function apply( $args = array() ) {
		if ( ! $this->has_dashboard_present() ) {
			$this->add_error( self::ERR_DASH_PRESENT );
			return false;
		}

		if ( ! $this->is_dashboard_active() ) {
			$this->add_error( self::ERR_DASH_ACTIVE );
			return false;
		}

		if ( ! self::has_api_key() ) {
			$this->add_error( self::ERR_DASH_APIKEY );
			return false;
		}

		if ( empty( $args ) ) {
			return true;
		}

		$dev_site = Env::get_wpmu_api_server_url();

		$host = wp_parse_url( $dev_site, PHP_URL_HOST );
		$url  = $dev_site . 'api/snapshot/v2/site?domain=' . network_site_url();

		$headers = array(
			'Accept'          => '*/*',
			'Accept-Encoding' => 'gzip, deflate',
			'Authorization'   => $args['api_key'],
			'Host'            => $host,
		);

		$arguments = array(
			'headers'   => $headers,
			'sslverify' => false,
			'timeout'   => 15,
		);

		$response = wp_remote_get( $url, $arguments );

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code < 200 || $response_code >= 300 ) {
			return new \WP_Error( 'auth_error', 'We couldn\'t authenticate the request to retrieve info from the Hub.', array( 'status' => 403 ) );
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Checks whether we have WPMU DEV Dashboard plugin available (albeit possibly not installed).
	 *
	 * @return bool
	 */
	public function has_dashboard_present() {
		$present = false;

		// Do the faster check first.
		if ( ! $this->is_dashboard_active() ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugins = get_plugins();
			if ( ! is_array( $plugins ) || empty( $plugins ) ) {
				$present = false;
			} else {
				$present = ! empty( $plugins['wpmudev-updates/update-notifications.php'] );
			}
		} else {
			$present = true;
		}

		/**
		 * Dashboard present check filter.
		 *
		 * @param $present Whether the dash is present.
		 *
		 * @return bool
		 */
		return apply_filters(
			'wp_snapshot_checks_hub_dashboard_present',
			$present
		);
	}

	/**
	 * Checks if we have WPMU DEV Dashboard plugin installed.
	 *
	 * @return bool
	 */
	public function is_dashboard_active() {
		$active = class_exists( 'WPMUDEV_Dashboard' );

		/**
		 * Dashboard active check filter.
		 *
		 * @param $active Whether the dash is active.
		 *
		 * @return bool
		 */
		return apply_filters(
			'wp_snapshot_checks_hub_dashboard_active',
			$active
		);
	}

	/**
	 * Checks whether we have WPMU DEV API key present.
	 *
	 * @return bool
	 */
	public static function has_api_key() {
		$model = new Model\Api();

		/**
		 * Dashboard API key presence check filter.
		 *
		 * @param $result Whether the dash API key is present.
		 *
		 * @return bool
		 */
		return apply_filters(
			'wp_snapshot_checks_hub_dashboard_apikey',
			$model->get( 'api_key' )
		);
	}
}