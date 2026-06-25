<?php

class WPMUDEV_HUB_REST_API_V1_Reseller_Domain_Settings extends WPMUDEV_HUB_REST_API_V1_Reseller_Settings {
	protected $version = 1;

	protected $rest_base = 'reseller/domain-settings';

	/**
	 * Whether to clear the reseller settings API transient
	 * this API will be accessed by admin ( in THC settings ), we want real time data there
	 *
	 * @var bool
	 */
	protected $clear_reseller_api_transient = true;

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'doc_summary'         => __( 'Get Domain Reseller settings.', 'thc' ),
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'doc_summary'         => __( 'Update Domain Reseller settings.', 'thc' ),
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'wpmudev-hub-whitelabel-reseller-domain-settings',
			'type'       => 'object',
			'properties' => array(
				'is_active'                                    => array(
					'description' => __( 'Whether domain reseller active.', 'thc' ),
					'type'        => 'boolean',
				),
				'widget_customizations'                        => array(
					'description' => __( 'Domain Reseller widget customizations.', 'thc' ),
					'type'        => 'object',
					'properties'  => array(
						'search_placeholder_text'        => array(
							'description' => __( 'Placeholder text in the search field.', 'thc' ),
							'type'        => 'string',
							'format'      => 'text-field',
						),
						'search_placeholder_color'       => array(
							'description' => __( 'Placeholder color in the search field.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
						'search_input_background_color'  => array(
							'description' => __( 'Search field input background color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
						'search_input_color'             => array(
							'description' => __( 'Search field input color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
						'search_button_label_text'       => array(
							'description' => __( 'Search button label\'s text.', 'thc' ),
							'type'        => 'string',
							'format'      => 'text-field',
						),
						'search_button_label_color'      => array(
							'description' => __( 'Search button label\'s color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
						'search_button_background_color' => array(
							'description' => __( 'Search button background color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
						'result_options'                 => array(
							'description' => __( 'Search result options.', 'thc' ),
							'type'        => 'string',
							'format'      => 'text-field',
							'enum'        => array(
								WPMUDEV_HUB_Domain_Reseller::WIDGET_CUSTOMIZATIONS_RESULT_OPTIONS_MATCHES_SUGGESTIONS,
								WPMUDEV_HUB_Domain_Reseller::WIDGET_CUSTOMIZATIONS_RESULT_OPTIONS_MATCHES,
							),
						),
						'buy_button_label_text'          => array(
							'description' => __( 'Buy button label\'s text.', 'thc' ),
							'type'        => 'string',
							'format'      => 'text-field',
						),
						'buy_button_label_color'         => array(
							'description' => __( 'Buy button label\'s color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
						'buy_button_background_color'    => array(
							'description' => __( 'Buy button background color.', 'thc' ),
							'type'        => 'string',
							'format'      => 'hex-color',
						),
					),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_widget_customizations' ),
					),
				),
				'is_hub_client_billing_active'                 => array(
					'description' => __( 'Whether Hub Client Billing active. This will return null when domain reseller already active.', 'thc' ),
					'type'        => array( 'boolean', 'null' ),
					'readonly'    => true,
				),
				'hub_client_billing_payment_currency'          => array(
					'description' => __( 'Payment currency in the Hub Client Billing settings.', 'thc' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'hub_client_billing_currency_symbol'           => array(
					'description' => __( 'Payment currency symbol in the Hub Client Billing settings.', 'thc' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'hub_reseller_domain_round_off_decimal_places' => array(
					'description' => __( 'Round off decimal places in the Hub Domain Reseller settings.', 'thc' ),
					'type'        => 'integer',
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
	public function sanitize_widget_customizations( $value, $request, $param ) {
		$value = rest_sanitize_request_arg( $value, $request, $param );
		if ( version_compare( WPMUDEV_HUB_Plugin::get_wp_version(), '5.9', 'lt' ) ) {
			$text_fields = array(
				'search_placeholder_text',
				'search_button_label_text',
				'buy_button_label_text',
			); // result_options also text-fields, but we dont need to sanitize it, as it has enum set already
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

		$model = WPMUDEV_HUB_Domain_Reseller::get_instance();

		$is_active = $model->is_active();
		if ( rest_is_field_included( 'is_active', $fields ) ) {
			$data['is_active'] = $is_active;
		}

		if ( rest_is_field_included( 'widget_customizations', $fields ) ) {
			$widget_customizations         = $model->get_widget_customizations();
			$data['widget_customizations'] = array();
			if ( $is_active ) {
				foreach ( WPMUDEV_HUB_Domain_Reseller::$widget_customizations_keys as $customization_key ) {
					$data['widget_customizations'][ $customization_key ] = $widget_customizations[ $customization_key ] ?? null;
				}
			}
		}

		// if is_hub_client_billing_active field required, and the domain reseller not active, clear reseller api transient, to get latest data
		if ( rest_is_field_included( 'is_hub_client_billing_active', $fields ) && ! $is_active ) {
			$this->clear_reseller_api_transient = true;
		}

		if ( $this->clear_reseller_api_transient ) {
			$model->clear_reseller_api_transient();
		}

		if ( rest_is_field_included( 'is_hub_client_billing_active', $fields ) ) {
			$data['is_hub_client_billing_active'] = null;// default
			if ( ! $is_active ) {
				$data['is_hub_client_billing_active'] = $model->is_hub_client_billing_active();
			}
		}

		if ( rest_is_field_included( 'hub_client_billing_payment_currency', $fields ) ) {
			$data['hub_client_billing_payment_currency'] = $model->get_reseller_api_data( 'payment_currency', '' );
		}
		if ( rest_is_field_included( 'hub_client_billing_currency_symbol', $fields ) ) {
			$data['hub_client_billing_currency_symbol'] = $model->get_reseller_api_data( 'currency_symbol', '' );
		}
		if ( rest_is_field_included( 'hub_reseller_domain_round_off_decimal_places', $fields ) ) {
			$data['hub_reseller_domain_round_off_decimal_places'] = $model->get_reseller_api_data( 'domain_round_off_decimal_places', 2 );
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
		return $this->prepare_item_for_response( array(), $request );
	}

	public function update_item( $request ) {
		$is_active = $request->get_param( 'is_active' );

		if ( ! is_null( $is_active ) ) {
			WPMUDEV_HUB_Domain_Reseller::get_instance()->set_active( $is_active );
		}

		$widget_customizations = $request->get_param( 'widget_customizations' );

		if ( ! is_null( $widget_customizations ) ) {
			if ( ! WPMUDEV_HUB_Domain_Reseller::get_instance()->is_active() ) {
				return new WP_Error( 'inactive', __( 'Domain Reseller is inactive. Please activate it first.', 'thc' ), array( 'status' => 400 ) );
			}
			$widget_customizations = is_array( $widget_customizations ) ? $widget_customizations : array();
			$args                  = array();
			foreach ( WPMUDEV_HUB_Domain_Reseller::$widget_customizations_keys as $customization_key ) {
				if ( isset( $widget_customizations[ $customization_key ] ) ) {
					$args[ $customization_key ] = $widget_customizations[ $customization_key ];
				}
			}
			if ( $args ) {
				WPMUDEV_HUB_Domain_Reseller::get_instance()->set_widget_customizations( $args );
			}
		}

		return $this->prepare_item_for_response( array(), $request );
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Domain_Settings();