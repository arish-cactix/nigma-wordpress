<?php

class WPMUDEV_HUB_REST_API_V1_Reseller_Hosting_Settings_Products extends WPMUDEV_HUB_REST_API_V1_Reseller_Hosting_Settings {
	protected $version = 1;

	protected $rest_base = 'reseller/hosting-settings/products';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'doc_summary'         => __( 'Add product to Hosting Reseller settings.', 'thc' ),
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array(
						'product_id'                  => array(
							'description' => __( 'Hub Product ID.', 'thc' ),
							'type'        => 'integer',
							'example'     => 1,
							'required'    => true,
						),
						'plans'                       => array(
							'type'     => 'array',
							'items'    => array(
								'type'       => 'object',
								'properties' => array(
									'id' => array(
										'description' => __( 'Hub Plan ID.', 'thc' ),
										'type'        => array( 'integer' ),
										'required'    => true,
										'example'     => 1,
									),
								),
							),
							'required' => false,
						),
						'sort_order'                  => array(
							'description' => __( 'Reseller Product sort order.', 'thc' ),
							'type'        => 'integer',
							'example'     => 0,
							'required'    => false,
						),
						'is_visible_in_pricing_table' => array(
							'description' => __( 'Whether product will be visible in Pricing Table.', 'thc' ),
							'type'        => 'boolean',
							'required'    => false,
						),
						'is_visible_in_hub_embed'     => array(
							'description' => __( 'Whether product will be visible in Hub Embed.', 'thc' ),
							'type'        => 'boolean',
							'required'    => false,
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/sort-orders',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'doc_summary'         => __( 'Set product sort order to Hosting Reseller settings.', 'thc' ),
					'callback'            => array( $this, 'update_sort_oder_items' ),
					'permission_callback' => array( $this, 'update_sort_oder_items_permissions_check' ),
					'args'                => array(
						'products' => array(
							'type'     => 'array',
							'required' => true,
							'items'    => array(
								'type'       => 'object',
								'properties' => array(
									'id'         => array(
										'description' => __( 'Reseller Product identifier.', 'thc' ),
										'type'        => 'string',
										'example'     => 'product-id',
										'required'    => true,
									),
									'sort_order' => array(
										'description' => __( 'Reseller Product sort order.', 'thc' ),
										'type'        => 'integer',
										'example'     => 0,
										'required'    => true,
									),
								),
							),
						),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\\w-]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Reseller Product identifier.', 'thc' ),
						'type'        => 'string',
						'example'     => 'product-id',
						'required'    => true,
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'doc_summary'         => __( 'Edit existing product on Hosting Reseller settings.', 'thc' ),
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => array(
						'product_id'                  => array(
							'description' => __( 'Hub Product ID.', 'thc' ), // optional on update, it will use existing product when not provided
							'type'        => 'integer',
							'example'     => 1,
							'required'    => false,
						),
						'plans'                       => array(
							'type'     => 'array',
							'items'    => array(
								'type'       => 'object',
								'properties' => array(
									'id' => array(
										'description' => __( 'Hub Plan ID.', 'thc' ),
										'type'        => array( 'integer' ),
										'required'    => true,
										'example'     => 1,
									),
								),
							),
							'required' => false,
						),
						'sort_order'                  => array(
							'description' => __( 'Reseller Product sort order.', 'thc' ),
							'type'        => 'integer',
							'example'     => 0,
							'required'    => false,
						),
						'is_visible_in_pricing_table' => array(
							'description' => __( 'Whether product will be visible in Pricing Table.', 'thc' ),
							'type'        => 'boolean',
							'required'    => false,
						),
						'is_visible_in_hub_embed'     => array(
							'description' => __( 'Whether product will be visible in Hub Embed.', 'thc' ),
							'type'        => 'boolean',
							'required'    => false,
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'doc_summary'         => __( 'Delete existing product on Hosting Reseller settings.', 'thc' ),
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function create_item_permissions_check( $request ) {
		$allowed = WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
		if ( ! $allowed ) {
			return $allowed;
		}

		if ( ! WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active() ) {
			return new WP_Error( 'inactive', __( 'Hosting Reseller is inactive. Please activate it first.', 'thc' ), array( 'status' => 400 ) );
		}

		return $allowed;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return true|WP_Error
	 */
	public function update_sort_oder_items_permissions_check( $request ) {
		$allowed = WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
		if ( ! $allowed ) {
			return $allowed;
		}

		if ( ! WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active() ) {
			return new WP_Error( 'inactive', __( 'Hosting Reseller is inactive. Please activate it first.', 'thc' ), array( 'status' => 400 ) );
		}

		return $allowed;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		$allowed = WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
		if ( ! $allowed ) {
			return $allowed;
		}

		if ( ! WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active() ) {
			return new WP_Error( 'inactive', __( 'Hosting Reseller is inactive. Please activate it first.', 'thc' ), array( 'status' => 400 ) );
		}

		return $allowed;
	}

	public function delete_item_permissions_check( $request ) {
		$allowed = WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
		if ( ! $allowed ) {
			return $allowed;
		}

		if ( ! WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active() ) {
			return new WP_Error( 'inactive', __( 'Hosting Reseller is inactive. Please activate it first.', 'thc' ), array( 'status' => 400 ) );
		}

		return $allowed;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$product_id                  = $request->get_param( 'product_id' );
		$plans                       = $request->get_param( 'plans' );
		$sort_order                  = $request->get_param( 'sort_order' );
		$is_visible_in_pricing_table = $request->get_param( 'is_visible_in_pricing_table' );
		$is_visible_in_hub_embed     = $request->get_param( 'is_visible_in_hub_embed' );

		$plan_ids = null;
		if ( ! is_null( $plans ) ) {
			$plans    = is_array( $plans ) ? $plans : array();
			$plan_ids = wp_list_pluck( $plans, 'id' );
			$plan_ids = array_values( $plan_ids );
			$plan_ids = array_unique( $plan_ids );
		}

		$added = WPMUDEV_HUB_Hosting_Reseller::get_instance()->add_product(
			$product_id,
			$plan_ids,
			array(
				'sort_order'                  => $sort_order,
				'is_visible_in_pricing_table' => $is_visible_in_pricing_table,
				'is_visible_in_hub_embed'     => $is_visible_in_hub_embed,
			)
		);
		if ( is_wp_error( $added ) ) {
			return $added;
		}

		// sync
		WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products( true );

		return $this->prepare_item_for_response( array(), $request );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$id                          = $request->get_param( 'id' );
		$product_id                  = $request->get_param( 'product_id' );
		$plans                       = $request->get_param( 'plans' );
		$sort_order                  = $request->get_param( 'sort_order' );
		$is_visible_in_pricing_table = $request->get_param( 'is_visible_in_pricing_table' );
		$is_visible_in_hub_embed     = $request->get_param( 'is_visible_in_hub_embed' );

		$plan_ids = null;
		if ( ! is_null( $plans ) ) {
			$plans    = is_array( $plans ) ? $plans : array();
			$plan_ids = wp_list_pluck( $plans, 'id' );
			$plan_ids = array_values( $plan_ids );
			$plan_ids = array_unique( $plan_ids );
		}

		$updated = WPMUDEV_HUB_Hosting_Reseller::get_instance()->update_product(
			$id,
			$product_id,
			$plan_ids,
			array(
				'sort_order'                  => $sort_order,
				'is_visible_in_pricing_table' => $is_visible_in_pricing_table,
				'is_visible_in_hub_embed'     => $is_visible_in_hub_embed,
			)
		);
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		// sync
		WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products( true );

		return $this->prepare_item_for_response( array(), $request );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_sort_oder_items( $request ) {
		$products = $request->get_param( 'products' );
		$products = is_array( $products ) ? $products : array();

		$products_data = WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products();
		foreach ( $products as $product ) {
			$id         = isset( $product['id'] ) ? $product['id'] : '';
			$sort_order = (int) ( isset( $product['sort_order'] ) ? $product['sort_order'] : 0 );
			if ( ! $id ) {
				continue;
			}
			if ( ! isset( $products_data[ $id ] ) ) {
				continue;
			}
			WPMUDEV_HUB_Hosting_Reseller::get_instance()->update_product_sort_order( $id, $sort_order );
		}

		return $this->prepare_item_for_response( array(), $request );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$id              = $request->get_param( 'id' );
		$products        = WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products();
		$deleted_product = isset( $products[ $id ] ) ? $products[ $id ] : null;
		if ( $deleted_product ) {
			$deleted_product['product_id'] = $deleted_product['id'];
			$deleted_product['id']         = $id;
		}

		WPMUDEV_HUB_Hosting_Reseller::get_instance()->delete_product( $id );

		// sync
		WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products( true );

		$response = $this->prepare_item_for_response( array(), $request );
		$response->set_data(
			wp_parse_args(
				array(
					'deleted_product' => $deleted_product,
				),
				$response->get_data()
			)
		);

		return $response;
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Hosting_Settings_Products();