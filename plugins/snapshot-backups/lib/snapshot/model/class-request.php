<?php // phpcs:ignore
/**
 * Snapshot requesting model abstraction class.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;

/**
 * Requesting model abstraction class
 */
abstract class Request extends Model {
	const DEFAULT_ERROR = 'service_unreachable';

	/**
	 * Request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = '';


	/**
	 * Request max timeout
	 *
	 * @var int
	 */
	private $timeout = 25;

	/**
	 * Header arguments
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * POST arguments
	 *
	 * @var array
	 */
	private $post_args = array();

	/**
	 * GET arguments
	 *
	 * @var array
	 */
	private $get_args = array();

	/**
	 * Last response code
	 *
	 * @var int|string
	 */
	protected $response_code = '';

	/**
	 * Last response body
	 *
	 * @var string
	 */
	protected $response_body = '';

	/**
	 * Signs the request adding an auth header
	 */
	protected function sign_request() {
		$api_key = Helper\Api::get_api_key();
		if ( $api_key ) {
			$this->add_header_argument( 'Snapshot-APIKey', $api_key );
		}
	}

	/**
	 * Add a new request argument for requests
	 *
	 * @param string $name   Argument name.
	 * @param string $value  Argument value.
	 */
	public function add_header_argument( $name, $value ) {
		$this->headers[ $name ] = $value;
	}

	/**
	 * Get the Request URL
	 *
	 * @return string
	 */
	public function get_api_url() {
		$url     = Settings::get_service_api_url();
		$site_id = Helper\Api::get_site_id();

		$service_url = $url . $site_id . '/' . $this->endpoint;

		/**
		 * DEV API service URL
		 *
		 * @param string $service_url Snapshot API schedule service URL.
		 *
		 * @return string
		 */
		return apply_filters(
			'snapshot_api_schedule_service_url',
			$service_url
		);
	}

	/**
	 * Make an API Request.
	 *
	 * @param string $path   Path.
	 * @param array  $data   Arguments array.
	 * @param string $method Method.
	 *
	 * @return array|mixed|object
	 */
	public function request( $path, $data = array(), $method = 'post' ) {
		$this->response_code = '';
		$this->errors        = array();

		if ( Model\Env::is_phpunit_test() && ! has_filter( 'pre_http_request' ) ) {
			// We are in test env and we're _not_ mocking request.
			// This'll fail anyway, so may as well save some time.
			return array(); //phpcs:ignore
		}

		$this->sign_request();

		$path = add_query_arg( $this->get_args, $path );
		if ( 'post' !== $method && 'put' !== $method && 'DELETE' !== $method ) {
			$path = add_query_arg( $data, $path );
		}

		$args = array(
			'headers' => $this->headers,
			'method'  => strtoupper( $method ),
			'timeout' => $this->timeout,
		);

		if ( ! $args['timeout'] ) {
			$args['blocking'] = false;
		}

		$request = array(
			'url'    => $path,
			'method' => $method,
			'body'   => is_array( $data ) ? array_merge( $data, $this->post_args ) : $data,
			'args'   => $args,
		);

		$response = apply_filters( 'snapshot_api_before_request', $request );

		if ( isset( $response['response'] ) ) {
			$this->process_response( $response );
			return $response; //phpcs:ignore
		}

		switch ( strtolower( $method ) ) {
			case 'post':
				if ( is_array( $data ) ) {
					$args['body'] = array_merge( $data, $this->post_args );
				} else {
					$args['body'] = $data;
				}

				$response = wp_remote_post( $path, $args );
				break;
			case 'put':
				$args['body'] = $data;

				$response = wp_remote_request( $path, $args );
				break;
			case 'get':
				$response = wp_remote_get( $path, $args );
				break;
			default:
				$response = wp_remote_request( $path, $args );
				break;
		}//end switch

		$response = apply_filters( 'snapshot_api_after_request', $response, $request );

		$this->process_response( $response );

		return $response; //phpcs:ignore
	}

	/**
	 * Get the current Site URL
	 *
	 * @return string
	 */
	public function get_this_site() {
		$site_url_parse = wp_parse_url( get_site_url() );
		if ( isset( $site_url_parse['path'] ) ) {
			return apply_filters( 'wp_snapshot_site_name', $site_url_parse['host'] . $site_url_parse['path'] );
		}

		return apply_filters( 'wp_snapshot_site_name', $site_url_parse['host'] );
	}

	/**
	 * Returns last response code
	 *
	 * @return int|string
	 */
	public function get_response_code() {
		return $this->response_code;
	}

	/**
	 * Check response status and call on_response_error() or on_response_success()
	 *
	 * @param \WP_Error|array $response Response from request() method.
	 */
	private function process_response( $response ) {
		if ( $response instanceof \WP_Error ) {
			$this->on_response_error();
			return;
		}

		$this->response_code = wp_remote_retrieve_response_code( $response );
		$this->response_body = wp_remote_retrieve_body( $response );

		$ok_codes = $this->get( 'ok_codes', array() );

		if ( ( $this->response_code >= 200 && $this->response_code < 300 ) || in_array( $this->response_code, $ok_codes, true ) ) {
			$this->on_response_success();
		} else {
			$this->on_response_error();
		}
	}

	/**
	 * Logs the success message for the latest api request.
	 */
	protected function on_response_success() {
		if ( ! $this->get( 'ignore_response_log' ) ) {
			$action_string = $this->get_action_string();
			/* translators: %s - Request action */
			Log::info( sprintf( __( 'Communication with the service API, in order to %s, was successful.', 'snapshot' ), $action_string ) );
		}
	}

	/**
	 * Logs the error message for the latest api request.
	 */
	protected function on_response_error() {
		$action_string  = $this->get_action_string();
		$this->errors[] = array(
			static::DEFAULT_ERROR,
			/* translators: %s - Request action */
			sprintf( __( 'Communication with the service API, in order to %s, has failed.', 'snapshot' ), $action_string ),
		);
	}

	/**
	 * Returns action string for logger
	 *
	 * @return string
	 */
	protected function get_action_string() {
		return 'retrieve ' . $this->endpoint;
	}
}