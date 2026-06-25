<?php

class WPMUDEV_HUB_REST_API_V1_Reseller_Public_Domain_Settings extends WPMUDEV_HUB_REST_API_V1_Reseller_Domain_Settings {
	protected $version = 1;

	protected $rest_base = 'reseller/public/domain-settings';

	/**
	 * Whether to clear the reseller settings API transient
	 * this API will be accessed by public ( gutenberg block and Front End ), we dont want real time data there to avoid performance issue
	 *
	 * @var bool
	 */
	protected $clear_reseller_api_transient = false;

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
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function item_schema() {
		$default = parent::item_schema();
		// not needed in public ( Front End ),
		// the main reason its in separated API route. This also will gives future flexibility if needed
		unset( $default['properties']['is_hub_client_billing_active'] ); // perf

		return $default;
	}

	public function get_item_permissions_check( $request ) {
		// everyone allowed, even non logged in
		// we use this for front end: Gutenberg, Hub Embed, Shortcode
		return true;
	}

	public function get_item( $request ) {
		return $this->prepare_item_for_response( array(), $request );
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Public_Domain_Settings();