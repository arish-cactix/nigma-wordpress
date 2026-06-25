<?php
/**
 * Rest API for Logs.
 *
 * @package Snapshot
 * @since 4.19
 */

namespace WPMUDEV\Snapshot4\Logs;

use WP_REST_Request;
use WP_REST_Server;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper\Api;
use WPMUDEV\Snapshot4\Model\Log;

/**
 * Rest class.
 */
class Rest extends Controller {

	/**
	 * Namespace for the Logs.
	 *
	 * @var string
	 */
	private $namespace = 'snapshot/v2';

	/**
	 * Boots up the controller.
	 *
	 * @return void
	 */
	public function boot() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register rest routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/action-logs',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'lists' ),
					'permission_callback' => array( $this, 'is_allowed' ),
					'args'                => array(
						'limit'   => array(
							'description' => esc_html__( 'Limit the number of items to retrieve.', 'snapshot' ),
							'type'        => 'integer',
						),
						'offset'  => array(
							'description' => esc_html__( 'Offset the result set by a specific number of items.', 'snapshot' ),
							'type'        => 'integer',
						),
						'orderby' => array(
							'description'       => esc_html__( 'Orders the query by: id | action | date', 'snapshot' ),
							'default'           => 'id',
							'validate_callback' => function ( $param, $request, $key ) {
								return in_array( $param, array( 'id', 'action', 'date' ), true );
							},
						),
						'order'   => array(
							'description'       => esc_html__( 'Set the order', 'snapshot' ),
							'default'           => 'desc',
							'validate_callback' => function ( $param, $request, $key ) {
								return in_array( $param, array( 'desc', 'asc' ), true );
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Check for the Auth header
	 *
	 * @param WP_REST_Request $request Instance of WP_REST_Request.
	 *
	 * @return boolean
	 */
	public function is_allowed( WP_REST_Request $request ): bool {
		$bearer_token = $request->get_header( 'authorization' );

		if ( is_null( $bearer_token ) ) {
			// Check if X-Authorization is present instead.
			$bearer_token = $request->get_header( 'x_authorization' );

			if ( is_null( $bearer_token ) ) {
				return false;
			}
		}

		$token = str_replace( 'Bearer ', '', $bearer_token );
		return Api::get_api_key() === $token;
	}

	/**
	 * List all the logs
	 *
	 * @param WP_REST_Request $request WP_REST_Request instance.
	 *
	 * @return WP_REST_Response $response
	 */
	public function lists( WP_REST_Request $request ) {
		$args = $this->get_request_params( $request );

		$logs = ( new Log() )->all( $args );

		$response = rest_ensure_response( $logs );

		// Add no-cache headers.
		$response->header( 'Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0' );
		$response->header( 'Pragma', 'no-cache' );
		$response->header( 'Expires', 'Wed, 11 Jan 1984 05:00:00 GMT' );

		return $response;
	}

	/**
	 * Prepares the params from the request.
	 *
	 * @param WP_REST_Request $request Instance.
	 *
	 * @return array
	 */
	private function get_request_params( WP_REST_Request $request ) {
		$limit    = $request->has_param( 'limit' ) ? $request->get_param( 'limit' ) : 100;
		$offset   = $request->has_param( 'offset' ) ? $request->get_param( 'offset' ) : 0;
		$order    = $request->has_param( 'order' ) ? $request->get_param( 'order' ) : 'DESC';
		$order_by = $request->has_param( 'orderby' ) ? $request->get_param( 'orderby' ) : 'id';

		return array(
			'number'  => $limit,
			'offset'  => $offset,
			'order'   => strtoupper( $order ),
			'orderby' => $order_by,
		);
	}
}