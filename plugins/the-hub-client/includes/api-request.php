<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WPMUDEV_HUB_API_Request {

	/**
	 * @var null|self
	 */
	protected static $instance = null;

	/**
	 * @return self
	 */
	public static function get_instance() {
		/**
		 * Filter Hub API Request adapter
		 *
		 * @param WPMUDEV_HUB_API_Request|null $instance
		 *
		 * @since 2.0.0
		 */
		self::$instance = apply_filters( 'wpmudev_hub_api_request_adapter', self::$instance );
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
	}

	public function get_base_api_server() {
		$server = 'https://wpmudev.com';
		if ( defined( 'WPMUDEV_CUSTOM_API_SERVER' ) && WPMUDEV_CUSTOM_API_SERVER ) {
			$server = WPMUDEV_CUSTOM_API_SERVER;
		}

		return trailingslashit( $server );
	}

	public function get_dashboard_api_key() {
		return defined( 'WPMUDEV_APIKEY' ) && WPMUDEV_APIKEY
			? WPMUDEV_APIKEY
			: get_site_option( 'wpmudev_apikey', false );
	}

	/**
	 * Execute HTTP Requests to WPMU DEV APIs
	 *
	 * @param $request_args
	 * @param $redirected_location
	 * @param $response_headers
	 *
	 * @return array|WP_Error
	 *
	 * @since 2.0.0
	 */
	public function exec( $request_args, &$redirected_location = null, &$response_headers = array() ) {
		$request_args = wp_parse_args(
			$request_args,
			array(
				'path'      => '/',
				'method'    => 'GET',
				'data'      => array(),
				'options'   => array(),
				'base_path' => 'api/hub/v1/',
			)
		);

		/**
		 * Filters Hub API Request args
		 *
		 * @param array $request_args
		 *
		 * @since 2.0.0
		 */
		$request_args = apply_filters( 'wpmudev_hub_api_request_args', $request_args );

		$path        = $request_args['path'];
		$method      = $request_args['method'];
		$data        = $request_args['data'];
		$options     = $request_args['options'];
		$base_path   = $request_args['base_path'];
		$hub_site_id = WPMUDEV_HUB_Plugin::get_hub_site_id();

		$base_url = $this->get_base_api_server();

		$path = ltrim( $path, '/' );

		$options = wp_parse_args(
			$options,
			array(
				'timeout'    => 30,
				'method'     => $method,
				'user-agent' => 'WPMUDEV HUB Client/' . WPMUDEV_HUB_Plugin::VERSION . ' (+' . network_site_url() . ')',
			)
		);

		if ( defined( 'WPMUDEV_API_SSLVERIFY' ) ) {
			$options['sslverify'] = WPMUDEV_API_SSLVERIFY;
		}

		if ( ! isset( $options['headers'] ) ) {
			$options['headers'] = array();
		}

		$options['headers']['Authorization'] = $this->get_dashboard_api_key();

		$embed_url = WPMUDEV_HUB_Plugin_Front::get_embed_url( true );

		if ( $embed_url ) {
			$options['headers']['X-HUB-EMBED-URL'] = $embed_url;
		}

		if ( $hub_site_id ) {
			$options['headers']['X-HUB-EMBED-SITE-ID'] = $hub_site_id;
		}

		// upload
		if ( isset( $options['hub-file-upload'] ) && $options['hub-file-upload'] ) {
			$boundary                           = wp_generate_uuid4();
			$options['headers']['content-type'] = sprintf( 'multipart/form-data; boundary=%s', $boundary );
			$body                               = '';

			// consist of two: `data` and `files
			if ( isset( $data['data'] ) && $data['data'] ) {
				foreach ( $data['data'] as $param => $value ) {
					$body .= '--' . $boundary;
					$body .= "\r\n";
					$body .= 'Content-Disposition: form-data; name="' . $param . '"' . "\r\n\r\n";
					$body .= $value;
					$body .= "\r\n";
				}
			}
			if ( isset( $data['files'] ) && $data['files'] ) {
				foreach ( $data['files'] as $file_id => $file ) {
					$file_name    = isset( $file['name'] ) ? $file['name'] : 'Hub File';
					$file_content = isset( $file['content'] ) ? $file['content'] : '';
					$file_type    = isset( $file['type'] ) ? $file['type'] : '';

					$body .= '--' . $boundary;
					$body .= "\r\n";
					$body .= 'Content-Disposition: form-data; name="' . $file_id . '"; filename="' . basename( $file_name ) . '"' . "\r\n";
					$body .= 'Content-Type: ' . $file_type . "\r\n";
					$body .= "\r\n";
					$body .= $file_content;
					$body .= "\r\n";
				}
			}
			$body .= '--' . $boundary . '--';

			$options['body'] = $body;

			unset( $options['hub-file-upload'] );
		} elseif ( $data ) {
			$options['body'] = $data;
		}

		if ( defined( 'WPMUDEV_API_DEBUG' ) && WPMUDEV_API_DEBUG ) {
			// only in debug mode
			error_log( 'Request: ' . $base_url . $base_path . $path );// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			if ( defined( 'WPMUDEV_API_DEBUG_ALL' ) && WPMUDEV_API_DEBUG_ALL ) {
				error_log( 'Request Options: ' . wp_json_encode( $options ) );// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		/**
		 * Filters Hub API Request Response
		 *
		 * @param WP_Error|array $response
		 * @param string         $base_url
		 * @param string         $base_path
		 * @param string         $path
		 * @param array          $options
		 *
		 * @since 2.0.0
		 */
		$response = apply_filters( 'wpmudev_hub_api_request_response', null, $base_url, $base_path, $path, $options );

		// TODO: Implement backoff

		if ( is_null( $response ) ) {
			$response = wp_remote_request(
				$base_url . $base_path . $path,
				$options
			);
		}

		$code             = (int) wp_remote_retrieve_response_code( $response );
		$body             = wp_remote_retrieve_body( $response );
		$response_headers = wp_remote_retrieve_headers( $response );

		if ( defined( 'WPMUDEV_API_DEBUG' ) && WPMUDEV_API_DEBUG ) {
			// only in debug mode
			error_log( 'Response Code: ' . $code );// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			if ( defined( 'WPMUDEV_API_DEBUG_ALL' ) && WPMUDEV_API_DEBUG_ALL ) {
				error_log( 'Response Headers: ' . wp_json_encode( $response_headers ) );// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Response Body: ' . $body );// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		// catch 302, 301 response code for redirect_location
		if ( in_array( (int) $code, array( 302, 301 ), true ) ) {
			$redirected_location = wp_remote_retrieve_header( $response, 'location' );
			if ( ! $redirected_location ) {
				$redirected_location = null;
			}
		}

		if ( is_wp_error( $response ) || $code > 299 ) {
			$error_message = $body;
			$error_code    = 'api_error';
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				$error_code    = $response->get_error_code();
			}

			// Parse message from API
			$decoded_body = json_decode( $body, true );
			if ( $decoded_body ) {
				if ( isset( $decoded_body['code'] ) ) {
					$error_code = is_scalar( $decoded_body['code'] ) ? $decoded_body['code'] : wp_json_encode( $decoded_body['code'] );
				}
				if ( isset( $decoded_body['message'] ) ) {
					$error_message = is_scalar( $decoded_body['message'] ) ? $decoded_body['message'] : wp_json_encode( $decoded_body['message'] );
				}
			}

			$error_data = array();
			if ( $code ) {
				$error_data['status'] = $code;
			}

			return new WP_Error( $error_code, 'Hub API Error : ' . $error_message, $error_data );
		}

		// success but decode failure
		if ( ! is_wp_error( $response ) ) {
			$response = json_decode( $body, true );
			// failed to decode response body
			if ( is_null( $response ) && json_last_error() !== JSON_ERROR_NONE ) {
				$response = new WP_Error( json_last_error(), json_last_error_msg() );
			}
		}

		return $response;
	}
}