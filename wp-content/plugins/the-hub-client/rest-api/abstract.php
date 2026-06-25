<?php

abstract class WPMUDEV_HUB_REST_API_Abstract extends WP_REST_Controller {

	/**
	 * REST API Version
	 *
	 * @var string|int
	 */
	protected $version;

	protected $namespace = 'wpmudev-hub';

	public function __construct() {
		if ( $this->version ) {
			$this->namespace = trailingslashit( $this->namespace ) . 'v' . $this->version;
		}
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Enforcing Item Schema
	 *
	 * @return array
	 */
	abstract public function item_schema();

	/**
	 * Finalized item schema
	 *
	 * @return array
	 */
	public function get_item_schema() {
		return $this->add_additional_fields_schema( $this->item_schema() );
	}

	/**
	 * Sanitize text-field in REST (PRE 5.9)
	 *
	 * @param mixed           $value
	 * @param WP_REST_Request $request
	 * @param string          $param
	 *
	 * @return string
	 * @see rest_sanitize_value_from_schema()
	 * @see rest_sanitize_request_arg()
	 */
	public function sanitize_value_text_field( $value, $request, $param ) {
		// default
		$value = rest_sanitize_request_arg( $value, $request, $param );

		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize text-area-field in REST (PRE 5.9)
	 *
	 * @param mixed           $value
	 * @param WP_REST_Request $request
	 * @param string          $param
	 *
	 * @return string
	 * @see rest_sanitize_value_from_schema()
	 * @see rest_sanitize_request_arg()
	 */
	public function sanitize_value_textarea_field( $value, $request, $param ) {
		// default
		$value = rest_sanitize_request_arg( $value, $request, $param );

		return sanitize_textarea_field( $value );
	}

	/**
	 * Sanitize text fields arg options in REST (PRE 5.9)
	 *
	 * @param string $type
	 *
	 * @return callable[]
	 */
	public function arg_options_for_text_field( $type = 'text' ) {
		$sanitize_callback = 'rest_sanitize_request_arg';
		if ( version_compare( WPMUDEV_HUB_Plugin::get_wp_version(), '5.9', 'lt' ) ) {
			$sanitize_callback = 'text' === $type ? array( $this, 'sanitize_value_text_field' ) : array( $this, 'sanitize_value_textarea_field' );
		}

		return array(
			'sanitize_callback' => $sanitize_callback,
		);
	}

	/**
	 * Prepare items for response ( collection context )
	 *
	 * @param iterable        $items
	 * @param WP_REST_Request $request
	 * @param int             $total_items
	 * @param int             $total_pages
	 *
	 * @return WP_REST_Response
	 *
	 * @since 2.2.0
	 */
	protected function prepare_items_for_response( iterable $items, WP_REST_Request $request, int $total_items, int $total_pages ) {
		$response_items = array();
		foreach ( $items as $item ) {
			$response_items[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $item, $request )
			);
		}

		$response = rest_ensure_response( $response_items );

		$response->header( 'X-WP-Total', $total_items );
		$response->header( 'X-WP-TotalPages', $total_pages );

		$base_path = sprintf( '%1$s/%2$s', $this->namespace, $this->rest_base );
		$base      = add_query_arg( $request->get_query_params(), rest_url( $base_path ) );
		$page      = $request->get_param( 'page' );
		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $total_pages ) {
				$prev_page = $total_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $total_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}
}