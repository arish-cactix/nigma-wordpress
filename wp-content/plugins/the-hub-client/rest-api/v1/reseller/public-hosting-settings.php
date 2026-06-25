<?php

class WPMUDEV_HUB_REST_API_V1_Reseller_Public_Hosting_Settings extends WPMUDEV_HUB_REST_API_V1_Reseller_Hosting_Settings {
	protected $version = 1;

	protected $rest_base = 'reseller/public/hosting-settings';

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
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function item_schema() {
		$default = parent::item_schema();
		// not needed in public ( Front End )
		unset( $default['properties']['is_hub_client_billing_active'] );

		return $default;
	}

	public function get_item_permissions_check( $request ) {
		// everyone allowed, even non logged in
		// we use this for front end: Gutenberg, Hub Embed, Shortcode
		return true;
	}

	public function prepare_item_for_response( $item, $request ) {
		$response        = parent::prepare_item_for_response( $item, $request );
		$response_data   = $response->get_data();
		$is_hub_fe_ready = WPMUDEV_HUB_Plugin::is_hub_fe_ready();
		// deactivate if hub FE is not ready ( client won't be able to checkout without it )
		if ( ! $is_hub_fe_ready ) {
			if ( isset( $response_data['is_active'] ) && $response_data['is_active'] ) {
				$response_data['is_active'] = false;
			}
			if ( isset( $response_data['products'] ) ) {
				$response_data['products'] = array();
			}
			$response->set_data( $response_data );

			return $response;
		}

		if ( isset( $response_data['products'] ) ) {
			$products = $response_data['products'];
			// safe-op
			$products = is_array( $products ) ? $products : array();
			foreach ( $products as $key => $product ) {
				// safe-op
				$product = is_array( $product ) ? $product : array();

				unset( $product['stripe_product_id'] );
				unset( $product['wp_user_role'] );
				unset( $product['default_client_role'] );
				unset( $product['client_role'] );
				unset( $product['auto_suspend'] );
				unset( $product['auto_delete'] );

				if ( isset( $product['hosting_plan'] ) ) {
					// safe-op
					$product['hosting_plan'] = is_array( $product['hosting_plan'] ) ? $product['hosting_plan'] : array();

					unset( $product['hosting_plan']['price'] );
					unset( $product['hosting_plan']['price_with_tax'] );
					unset( $product['hosting_plan']['currency'] );
					unset( $product['hosting_plan']['currency_label'] );
				}

				// safe-op
				$plans = isset( $product['plans'] ) ? $product['plans'] : array();
				$plans = is_array( $plans ) ? $plans : array();

				foreach ( $plans as $plan_key => $plan ) {
					// safe-op
					$plan = is_array( $plan ) ? $plan : array();
					unset( $plan['stripe_plan_id'] );
					$plans[ $plan_key ] = $plan;
				}

				$product['plans'] = $plans;
				$products[ $key ] = $product;
			}
			$response_data['products'] = $products;
			$response->set_data( $response_data );
		}

		return $response;
	}

	public function get_item( $request ) {
		if ( WPMUDEV_HUB_Hosting_Reseller::get_instance()->is_active() ) {
			// sync ( maybe )
			WPMUDEV_HUB_Hosting_Reseller::get_instance()->get_products_maybe_sync();
		}

		return $this->prepare_item_for_response( array(), $request );
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Public_Hosting_Settings();