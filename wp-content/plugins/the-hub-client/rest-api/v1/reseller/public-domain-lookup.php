<?php

class WPMUDEV_HUB_REST_API_V1_Reseller_Public_Domain_Lookup extends WPMUDEV_HUB_REST_API_V1_Reseller_Domain_Settings {
	protected $version = 1;

	protected $rest_base = 'reseller/public/domain-lookup';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'doc_summary'         => __( 'Lookup domain.', 'thc' ),
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'search'     => array(
							'type'      => 'string',
							'required'  => true,
							'minLength' => 1,
						),
						'search_key' => array(
							'type' => 'string',
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'wpmudev-hub-whitelabel-public-reseller-domain-lookup',
			'type'       => 'object',
			'properties' => $this->get_domain_lookup_item_schema_properties(),
		);
	}

	public function get_item_permissions_check( $request ) {
		// everyone allowed, even non logged in
		// we use this for front end: the lookup
		return true;
	}

	public function prepare_item_for_response( $item, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		if ( rest_is_field_included( 'is_tld_offered', $fields ) ) {
			$data['is_tld_offered'] = $item['is_tld_offered'] ?? false;
		}
		if ( rest_is_field_included( 'search_tld', $fields ) ) {
			$data['search_tld'] = $item['search_tld'] ?? '';
		}
		if ( rest_is_field_included( 'is_idn', $fields ) ) {
			$data['is_idn'] = $item['is_idn'] ?? false;
		}
		if ( rest_is_field_included( 'is_invalid_search_tld', $fields ) ) {
			$data['is_invalid_search_tld'] = $item['is_invalid_search_tld'] ?? false;
		}
		if ( rest_is_field_included( 'has_exact_match', $fields ) ) {
			$data['has_exact_match'] = $item['has_exact_match'] ?? false;
		}
		if ( rest_is_field_included( 'exact_match', $fields ) ) {
			$data['exact_match'] = $item['exact_match'] ?? null;
		}
		if ( rest_is_field_included( 'lookups', $fields ) ) {
			$data['lookups'] = $item['lookups'] ?? array();
		}
		if ( rest_is_field_included( 'suggestions', $fields ) ) {
			$data['suggestions'] = $item['suggestions'] ?? array();
		}
		if ( rest_is_field_included( 'is_search_completed', $fields ) ) {
			$data['is_search_completed'] = $item['is_search_completed'] ?? true;
		}
		if ( rest_is_field_included( 'search_key', $fields ) ) {
			$data['search_key'] = $item['search_key'] ?? '';
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		return rest_ensure_response( $data );
	}

	public function get_item( $request ) {
		if ( ! WPMUDEV_HUB_Domain_Reseller::get_instance()->is_active() ) {
			return new WP_Error( 'inactive', __( 'Domain Reseller is inactive.', 'thc' ), array( 'status' => 400 ) );
		}
		$args = array(
			'search'     => $request->get_param( 'search' ),
			'search_key' => $request->get_param( 'search_key' ),
		);

		$lookup_result = WPMUDEV_HUB_Domain_Reseller::get_instance()->get_api_lookup( $args );
		if ( is_wp_error( $lookup_result ) ) {
			return $lookup_result;
		}

		return $this->prepare_item_for_response( $lookup_result, $request );
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Public_Domain_Lookup();