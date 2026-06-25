<?php

class WPMUDEV_HUB_REST_API_V1_Reseller_Hosting_Products extends WPMUDEV_HUB_REST_API_V1_Reseller_Settings {
	protected $version = 1;

	protected $rest_base = 'reseller/hosting-products';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'doc_summary'         => __( 'Get available Hosting Reseller products from Hub.', 'thc' ),
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

		$params['order'] = array(
			'description' => __( 'Order sort attribute ascending or descending.', 'thc' ),
			'type'        => 'string',
			'default'     => 'desc',
			'enum'        => array(
				'asc',
				'desc',
			),
		);

		$params['orderby'] = array(
			'description' => __( 'Sort products by object attribute.', 'thc' ),
			'type'        => 'string',
			'default'     => 'product_id',
			'enum'        => array(
				'product_id',
				'name',
				'created_on',
			),
		);

		$params['ids'] = array(
			'description' => __( 'Filter by IDs.', 'thc' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'integer',
			),
		);

		$params['is_archived_products'] = array(
			'description' => __( 'Filter the results based on product is_archived state.', 'thc' ),
			'type'        => array( 'boolean', 'null' ),
			'default'     => false,
		);
		$params['is_archived_plans']    = array(
			'description' => __( 'Filter the plan results based on product is_archived state.', 'thc' ),
			'type'        => array( 'boolean', 'null' ),
			'default'     => false,
		);

		return $params;
	}

	public function get_items_permissions_check( $request ) {
		return WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	public function item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'wpmudev-hub-whitelabel-reseller-hosting-products',
			'type'       => 'object',
			'properties' => $this->get_hosting_product_item_schema_properties(),
		);
	}

	public function get_item_permissions_check( $request ) {
		return WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	public function prepare_item_for_response( $item, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		foreach ( $fields as $field ) {
			if ( 'plans' === $field ) {
				continue;
			}
			if ( rest_is_field_included( $field, $fields ) ) {
				$data[ $field ] = isset( $item[ $field ] ) ? $item[ $field ] : '';
			}
		}

		if ( rest_is_field_included( 'plans', $fields ) ) {
			$plans = isset( $item['plans'] ) ? $item['plans'] : array();
			$plans = is_array( $plans ) ? $plans : array();

			$data['plans'] = array();
			foreach ( $plans as $plan ) {
				$data_plan = array();
				if ( rest_is_field_included( 'plans.id', $fields ) ) {
					$data_plan['id'] = isset( $plan['id'] ) ? $plan['id'] : 0;
				}
				if ( rest_is_field_included( 'plans.is_archived', $fields ) ) {
					$data_plan['is_archived'] = isset( $plan['is_archived'] ) ? $plan['is_archived'] : false;
				}
				if ( rest_is_field_included( 'plans.name', $fields ) ) {
					$data_plan['name'] = esc_html( isset( $plan['name'] ) ? $plan['name'] : '' );
				}
				if ( rest_is_field_included( 'plans.price', $fields ) ) {
					$data_plan['price'] = (float) ( isset( $plan['price'] ) ? $plan['price'] : 0 );
				}
				if ( rest_is_field_included( 'plans.currency', $fields ) ) {
					$data_plan['currency'] = esc_html( isset( $plan['currency'] ) ? $plan['currency'] : '' );
				}
				if ( rest_is_field_included( 'plans.currency_symbol', $fields ) ) {
					$data_plan['currency_symbol'] = esc_html( isset( $plan['currency_symbol'] ) ? $plan['currency_symbol'] : '' );
				}
				if ( rest_is_field_included( 'plans.is_recurring', $fields ) ) {
					$data_plan['is_recurring'] = isset( $plan['is_recurring'] ) ? $plan['is_recurring'] : false;
				}
				if ( rest_is_field_included( 'plans.interval', $fields ) ) {
					$data_plan['interval'] = esc_html( isset( $plan['interval'] ) ? $plan['interval'] : '' );
				}
				if ( rest_is_field_included( 'plans.interval_count', $fields ) ) {
					$data_plan['interval_count'] = (int) ( isset( $plan['interval_count'] ) ? $plan['interval_count'] : 0 );
				}
				if ( rest_is_field_included( 'plans.billing_cycle', $fields ) ) {
					$data_plan['billing_cycle'] = (int) ( isset( $plan['billing_cycle'] ) ? $plan['billing_cycle'] : 0 );
				}

				$data['plans'][] = $data_plan;
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		return rest_ensure_response( $data );
	}

	public function get_items( $request ) {
		$args = array();
		foreach ( array( 'page', 'per_page', 'search', 'order', 'orderby', 'ids', 'is_archived_products', 'is_archived_plans' ) as $key ) {
			$param = $request->get_param( $key );
			if ( ! is_null( $param ) ) {
				$args[ $key ] = $param;
			}
		}

		$products = WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_api_products( $args, true, $products_headers );

		if ( is_wp_error( $products ) ) {
			return $products;
		}

		$total_items = (int) ( $products_headers['X-WP-Total'] ?? 0 );
		$total_pages = (int) ( $products_headers['X-WP-TotalPages'] ?? 0 );

		return $this->prepare_items_for_response( $products, $request, $total_items, $total_pages );
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Hosting_Products();