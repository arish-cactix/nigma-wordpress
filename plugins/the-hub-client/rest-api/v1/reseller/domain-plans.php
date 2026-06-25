<?php

class WPMUDEV_HUB_REST_API_V1_Reseller_Domain_Plans extends WPMUDEV_HUB_REST_API_V1_Reseller_Settings {
	protected $version = 1;

	protected $rest_base = 'reseller/domain-plans';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'doc_summary'         => __( 'Get available Domain Reseller plans from Hub.', 'thc' ),
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function get_collection_params() {
		// mimic Hub
		$params = parent::get_collection_params();

		$params['per_page']['default'] = 20;

		return $params;
	}

	public function get_items_permissions_check( $request ) {
		return WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	public function item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'wpmudev-hub-whitelabel-reseller-domain-plans',
			'type'       => 'object',
			'properties' => $this->get_domain_plan_item_schema_properties(),
		);
	}

	public function prepare_item_for_response( $item, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		if ( rest_is_field_included( 'id', $fields ) ) {
			$data['id'] = $item['id'] ?? 0;
		}
		if ( rest_is_field_included( 'tld_id', $fields ) ) {
			$data['tld_id'] = $item['tld_id'] ?? 0;
		}
		if ( rest_is_field_included( 'tld_name', $fields ) ) {
			$data['tld_name'] = $item['tld_name'] ?? '';
		}
		if ( rest_is_field_included( 'registration_price', $fields ) ) {
			$data['registration_price'] = $item['registration_price'] ?? 0;
		}
		if ( rest_is_field_included( 'renewal_price', $fields ) ) {
			$data['renewal_price'] = $item['renewal_price'] ?? 0;
		}
		if ( rest_is_field_included( 'formatted_registration_price', $fields ) ) {
			$data['formatted_registration_price'] = $item['formatted_registration_price'] ?? '';
		}
		if ( rest_is_field_included( 'formatted_renewal_price', $fields ) ) {
			$data['formatted_renewal_price'] = $item['formatted_renewal_price'] ?? '';
		}
		if ( rest_is_field_included( 'currency', $fields ) ) {
			$data['currency'] = $item['currency'] ?? '';
		}
		if ( rest_is_field_included( 'currency_symbol', $fields ) ) {
			$data['currency_symbol'] = $item['currency_symbol'] ?? '';
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		return rest_ensure_response( $data );
	}

	public function get_items( $request ) {
		$args = array();
		foreach ( array( 'page', 'per_page', 'search' ) as $key ) {
			$param = $request->get_param( $key );
			if ( ! is_null( $param ) ) {
				$args[ $key ] = $param;
			}
		}

		$plans = WPMUDEV_HUB_Domain_Reseller::get_instance()->get_api_plans( $args, $headers );

		if ( is_wp_error( $plans ) ) {
			return $plans;
		}

		$total_items = (int) ( $headers['X-WP-Total'] ?? 0 );
		$total_pages = (int) ( $headers['X-WP-TotalPages'] ?? 0 );

		return $this->prepare_items_for_response( $plans, $request, $total_items, $total_pages );
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Domain_Plans();