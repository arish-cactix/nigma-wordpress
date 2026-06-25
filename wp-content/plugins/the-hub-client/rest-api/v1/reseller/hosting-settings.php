<?php

class WPMUDEV_HUB_REST_API_V1_Reseller_Hosting_Settings extends WPMUDEV_HUB_REST_API_V1_Reseller_Settings {
	protected $version = 1;

	protected $rest_base = 'reseller/hosting-settings';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'doc_summary'         => __( 'Get Hosting Reseller settings.', 'thc' ),
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'doc_summary'         => __( 'Update Hosting Reseller settings.', 'thc' ),
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function item_schema() {
		$product_properties               = $this->get_hosting_product_item_schema_properties();
		$product_properties['product_id'] = $product_properties['id'];
		$product_properties['id']         = array(
			'description' => __( 'Reseller Product identifier.', 'thc' ),
			'type'        => 'string',
			'example'     => 'product-id',
			'readonly'    => true,
			'context'     => array( 'list', 'view', 'edit' ),
		);
		$product_properties['sort_order'] = array(
			'description' => __( 'Reseller Product sort order.', 'thc' ),
			'type'        => 'integer',
			'example'     => 0,
			'context'     => array( 'list', 'view', 'edit' ),
		);

		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'wpmudev-hub-whitelabel-reseller-hosting-settings',
			'type'       => 'object',
			'properties' => array(
				'is_active'                    => array(
					'description' => __( 'Whether hosting reseller active.', 'thc' ),
					'type'        => 'boolean',
				),
				'products'                     => array(
					'description' => __( 'List of available products.', 'thc' ),
					'type'        => 'array',
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => $product_properties,
					),
				),
				'pricing_table_customizations' => array(
					'description' => __( 'Hosting Reseller pricing table customizations.', 'thc' ),
					'type'        => 'object',
					'properties'  => array(
						'product_fields'                => array(
							'description' => __( 'Product fields to be displayed in pricing table.', 'thc' ),
							'type'        => 'array',
							'uniqueItems' => true,
							'items'       => array(
								'type'   => 'string',
								'format' => 'text-field',
								'enum'   => array( 'image', 'name', 'description', 'feature_list' ),
							),
						),
						'layout'                        => array(
							'description' => __( 'Pricing table layout.', 'thc' ),
							'type'        => 'string',
							'format'      => 'text-field',
							'enum'        => array( 'grid', 'stack' ),
						),
						'order_button_label_text'       => array(
							'description' => __( 'Order button label text.', 'thc' ),
							'type'        => 'string',
							'format'      => 'text-field',
						),
						'order_button_label_color'      => array(
							'description' => __( 'Order button label color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
						'order_button_background_color' => array(
							'description' => __( 'Order button background color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
						'container_background_color'    => array(
							'description' => __( 'Container background color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
					),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_pricing_table_customizations' ),
					),
				),
				'is_hub_client_billing_active' => array(
					'description' => __( 'Whether Hub Client Billing active. This will return null when hosting reseller already active.', 'thc' ),
					'type'        => array( 'boolean', 'null' ),
					'readonly'    => true,
				),
			),
		);
	}

	/**
	 * @param mixed           $value
	 * @param WP_REST_Request $request
	 * @param string          $param
	 *
	 * @return void
	 */
	public function sanitize_pricing_table_customizations( $value, $request, $param ) {
		$value = rest_sanitize_request_arg( $value, $request, $param );
		if ( version_compare( WPMUDEV_HUB_Plugin::get_wp_version(), '5.9', 'lt' ) ) {
			$text_fields = array( 'order_button_label_text' ); // product_fields and layout also text-fields, but we dont need to sanitize it, as it has enum set already
			$value       = is_array( $value ) ? $value : array();
			foreach ( $text_fields as $text_field ) {
				if ( isset( $value[ $text_field ] ) ) {
					$value[ $text_field ] = sanitize_text_field( $value[ $text_field ] );
				}
			}
		}

		return $value;
	}

	public function prepare_item_for_response( $item, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		$is_active = WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active();
		if ( rest_is_field_included( 'is_active', $fields ) ) {
			$data['is_active'] = $is_active;
		}

		if ( rest_is_field_included( 'products', $fields ) ) {
			// just to sync
			$products         = WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products_maybe_sync();
			$data['products'] = array();
			if ( $is_active ) {
				foreach ( $products as $product_uid => $product ) {
					$data['products'][] = wp_parse_args(
						array(
							'id'         => $product_uid,
							'product_id' => isset( $product['id'] ) ? $product['id'] : 0,
						),
						$product
					);
				}
			}
		}

		if ( rest_is_field_included( 'pricing_table_customizations', $fields ) ) {
			$pricing_table_customization          = WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_pricing_table_customizations();
			$data['pricing_table_customizations'] = array();
			if ( $is_active ) {
				$data['pricing_table_customizations'] = array(
					'product_fields'                => $pricing_table_customization['product_fields'],
					'layout'                        => $pricing_table_customization['layout'],
					'order_button_label_text'       => $pricing_table_customization['order_button_label_text'],
					'order_button_label_color'      => $pricing_table_customization['order_button_label_color'],
					'order_button_background_color' => $pricing_table_customization['order_button_background_color'],
					'container_background_color'    => $pricing_table_customization['container_background_color'],
				);
			}
		}

		if ( rest_is_field_included( 'is_hub_client_billing_active', $fields ) ) {
			$data['is_hub_client_billing_active'] = null;// default
			// only check when its not active
			if ( ! $is_active ) {
				WPMUDEV_HUB_Hosting_Reseller::get_instance()->clear_reseller_api_transient(); // need real time data to avoid blocking user due to cache
				$data['is_hub_client_billing_active'] = WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_hub_client_billing_active();
			}
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		return rest_ensure_response( $data );
	}

	public function get_item_permissions_check( $request ) {
		return WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	public function update_item_permissions_check( $request ) {
		return WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	public function get_item( $request ) {
		if ( WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active() ) {
			// sync
			WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products( true );
		}

		return $this->prepare_item_for_response( array(), $request );
	}

	public function update_item( $request ) {
		$is_active = $request->get_param( 'is_active' );

		if ( ! is_null( $is_active ) ) {
			WPMUDEV_HUB_Hosting_Reseller::get_instance()->set_active( $is_active );
		}

		if ( WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active() ) {
			// sync
			WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products( true );
		}

		$pricing_table_customizations = $request->get_param( 'pricing_table_customizations' );

		if ( ! is_null( $pricing_table_customizations ) ) {
			if ( ! WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active() ) {
				return new WP_Error( 'inactive', __( 'Hosting Reseller is inactive. Please activate it first.', 'thc' ), array( 'status' => 400 ) );
			}
			$pricing_table_customizations = is_array( $pricing_table_customizations ) ? $pricing_table_customizations : array();
			$args                         = array();
			if ( isset( $pricing_table_customizations['product_fields'] ) ) {
				$args['product_fields'] = $pricing_table_customizations['product_fields'];
			}
			if ( isset( $pricing_table_customizations['layout'] ) ) {
				$args['layout'] = $pricing_table_customizations['layout'];
			}
			if ( isset( $pricing_table_customizations['order_button_label_text'] ) ) {
				$args['order_button_label_text'] = $pricing_table_customizations['order_button_label_text'];
			}
			if ( isset( $pricing_table_customizations['order_button_label_color'] ) ) {
				$args['order_button_label_color'] = $pricing_table_customizations['order_button_label_color'];
			}
			if ( isset( $pricing_table_customizations['order_button_background_color'] ) ) {
				$args['order_button_background_color'] = $pricing_table_customizations['order_button_background_color'];
			}
			if ( isset( $pricing_table_customizations['container_background_color'] ) ) {
				$args['container_background_color'] = $pricing_table_customizations['container_background_color'];
			}
			if ( $args ) {
				WPMUDEV_HUB_Hosting_Reseller::get_instance()->set_pricing_table_customizations( $args );
			}
		}

		return $this->prepare_item_for_response( array(), $request );
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Hosting_Settings();