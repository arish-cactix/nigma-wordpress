<?php

/**
 * Reserve: General Settings for Reseller
 */
class WPMUDEV_HUB_REST_API_V1_Reseller_Settings extends WPMUDEV_HUB_REST_API_Abstract {
	protected $version = 1;

	protected $rest_base = 'reseller-settings';

	public function register_routes() {
	}

	public function item_schema() {
		return array();
	}

	protected function get_hosting_product_item_schema_properties() {
		return array(
			'id'            => array(
				'description' => __( 'Hub Product ID.', 'thc' ),
				'type'        => 'integer',
				'example'     => 1,
				'context'     => array( 'list', 'view', 'edit' ),
			),
			'is_archived'   => array(
				'description' => __( 'Whether product is archived.', 'thc' ),
				'type'        => 'boolean',
				'example'     => false,
				'context'     => array( 'list', 'view', 'edit' ),
			),
			'name'          => array(
				'description' => __( 'Product Name.', 'thc' ),
				'type'        => 'string',
				'example'     => 'Product Name',
				'required'    => true,
				'context'     => array( 'list', 'view', 'edit' ),
			),
			'image'         => array(
				'description' => __( 'Product Image. Original size', 'thc' ),
				'type'        => 'string',
				'format'      => 'uri',
				'example'     => 'https://example.org/image.png',
				'context'     => array( 'list', 'view', 'edit' ),
			),
			'image_180x180' => array(
				'description' => __( 'Product Image. 180px size', 'thc' ),
				'type'        => 'string',
				'format'      => 'uri',
				'example'     => 'https://example.org/image.png',
				'context'     => array( 'list', 'view', 'edit' ),
			),
			'image_50x50'   => array(
				'description' => __( 'Product Image. 50px size', 'thc' ),
				'type'        => 'string',
				'format'      => 'uri',
				'example'     => 'https://example.org/image.png',
				'context'     => array( 'list', 'view', 'edit' ),
			),
			'plans'         => array(
				'description' => __( 'List of plans.', 'thc' ),
				'type'        => 'array',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'              => array(
							'description' => __( 'Hub Plan ID.', 'thc' ),
							'type'        => array( 'integer', 'null' ),
							'required'    => true,
							'example'     => 1,
						),
						'is_archived'     => array(
							'description' => __( 'Whether plan is archived.', 'thc' ),
							'type'        => 'boolean',
							'example'     => false,
						),
						'name'            => array(
							'description' => __( 'Plan Name.', 'thc' ),
							'type'        => 'string',
							'example'     => 'Plan Name',
						),
						'price'           => array(
							'description' => __( 'Plan Price.', 'thc' ),
							'type'        => 'number',
							'example'     => 0,
						),
						'currency'        => array(
							'description' => __( 'Plan Price Currency.', 'thc' ),
							'type'        => 'string',
							'example'     => 'usd',
						),
						'currency_symbol' => array(
							'description' => __( 'Plan Price Currency symbol.', 'thc' ),
							'type'        => 'string',
							'example'     => '$',
						),
						'is_recurring'    => array(
							'description' => __( 'Whether plan is recurring type.', 'thc' ),
							'type'        => 'boolean',
							'example'     => false,
						),
						'interval'        => array(
							'description' => __( 'Plan billing interval when its recurring.', 'thc' ),
							'type'        => 'string',
							'enum'        => array( '', 'day', 'week', 'month', 'year' ),
							'example'     => '',
						),
						'interval_count'  => array(
							'description' => __( 'Plan billing interval count when its recurring.', 'thc' ),
							'type'        => 'integer',
							'example'     => 0,
						),
						'billing_cycle'   => array(
							'description' => __( 'Plan billing cycle.', 'thc' ),
							'type'        => 'integer',
							'example'     => 0,
						),
					),
				),
				'context'     => array( 'list', 'view', 'edit' ),
			),
		);
	}

	protected function get_domain_plan_item_schema_properties() {
		return array(
			'id'                           => array(
				'description' => __( 'Plan ID.', 'thc' ),
				'type'        => 'integer',
				'readonly'    => true,
			),
			'tld_id'                       => array(
				'description' => __( 'TLD ID.', 'thc' ),
				'type'        => 'integer',
				'readonly'    => true,
			),
			'tld_name'                     => array(
				'description' => __( 'TLD name.', 'thc' ),
				'type'        => 'string',
				'example'     => 'com',
				'readonly'    => true,
			),
			'registration_price'           => array(
				'description' => __( 'Registration Price value for client checkout. Based of markup, currency rate and decimal point round.', 'thc' ),
				'type'        => 'number',
				'example'     => 10.5,
				'readonly'    => true,
			),
			'renewal_price'                => array(
				'description' => __( 'Renewal Price value for client checkout. Based of markup, currency rate and decimal point round.', 'thc' ),
				'type'        => 'number',
				'example'     => 10.5,
				'readonly'    => true,
			),
			'formatted_registration_price' => array(
				'description' => __( 'Registration Price value for client checkout with currency symbol. Based of markup, currency rate and decimal point round.', 'thc' ),
				'type'        => 'number',
				'example'     => 10.5,
				'readonly'    => true,
			),
			'formatted_renewal_price'      => array(
				'description' => __( 'Renewal Price value for client checkout with currency symbol. Based of markup, currency rate and decimal point round.', 'thc' ),
				'type'        => 'number',
				'example'     => 10.5,
				'readonly'    => true,
			),
			'currency'                     => array(
				'description' => __( 'Price currency for client checkout.', 'thc' ),
				'type'        => 'string',
				'readonly'    => true,
				'example'     => 'usd',
			),
			'currency_symbol'              => array(
				'description' => __( 'Price currency symbol for client checkout.', 'thc' ),
				'type'        => 'string',
				'readonly'    => true,
				'example'     => '$',
			),
		);
	}

	protected function get_domain_lookup_item_schema_properties() {
		return array(
			'is_tld_offered'        => array(
				'description' => __( 'Whether the TLD is offered or not.', 'thc' ),
				'type'        => 'boolean',
			),
			'search_tld'            => array(
				'description' => __( 'The TLD being searched.', 'thc' ),
				'type'        => 'string',
			),
			'is_idn'                => array(
				'description' => __( 'Whether the domain is an Internationalized Domain Name.', 'thc' ),
				'type'        => 'boolean',
			),
			'is_invalid_search_tld' => array(
				'description' => __( 'Whether the searched TLD is invalid or not.', 'thc' ),
				'type'        => 'boolean',
			),
			'has_exact_match'       => array(
				'description' => __( 'Whether there is an exact match for the search.', 'thc' ),
				'type'        => 'boolean',
			),
			'exact_match'           => array(
				'description' => __( 'The exact match for the search.', 'thc' ),
				'type'        => 'object',
				'properties'  => array(),
			),
			'lookups'               => array(
				'description' => __( 'The lookup results.', 'thc' ),
				'type'        => 'array',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(),
				),
			),
			'suggestions'           => array(
				'description' => __( 'The domain name suggestions.', 'thc' ),
				'type'        => 'array',
				'items'       => array(
					'type'       => 'object',
					'properties' => array(),
				),
			),
			'is_search_completed'   => array(
				'description' => __( 'Whether the search has been completed.', 'thc' ),
				'type'        => 'boolean',
			),
			'search_key'            => array(
				'description' => __( 'The key used for the search.', 'thc' ),
				'type'        => 'string',
			),
		);
	}
}

new WPMUDEV_HUB_REST_API_V1_Reseller_Settings();