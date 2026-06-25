<?php

class WPMUDEV_HUB_REST_API_V1_Admins extends WP_REST_Users_Controller {
	protected $version = 1;

	public static function init_rest() {
		new self();
	}

	public function __construct() {
		parent::__construct();
		if ( $this->version ) {
			$this->namespace = trailingslashit( 'wpmudev-hub' ) . 'v' . $this->version;
		}
		$this->rest_base = 'admins';
		$this->register_routes();
	}

	public function register_routes() {
		// only enable get_items
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function get_item_schema() {
		$schema = parent::get_item_schema();
		// something horribly wrong happened
		if ( ! isset( $schema['properties'] ) || ! is_array( $schema['properties'] ) ) {
			return $schema;
		}

		if ( ! isset( $schema['properties']['email'] ) ) {
			return $schema;
		}

		// allow email in list
		$schema['properties']['email'] = array( 'context', 'embed', 'view', 'edit' );

		// introduce avatar_url
		$schema['properties']['avatar_url'] = array(
			'description' => __( 'Avatar URL with image size of 48 pixels.', 'thc' ),
			'type'        => 'string',
			'format'      => 'uri',
			'context'     => array( 'embed', 'view', 'edit' ),
		);

		return $schema;
	}

	protected function add_additional_fields_to_object( $prepared, $request ) {
		$requested_fields = $this->get_fields_for_response( $request );
		$prepared         = parent::add_additional_fields_to_object( $prepared, $request );

		if ( rest_is_field_included( 'avatar_url', $requested_fields ) ) {
			$prepared['avatar_url'] = get_avatar_url( ( isset( $prepared['id'] ) ? $prepared['id'] : 0 ), array( 'size' => 48 ) );
		}

		return $prepared;
	}

	public function get_items_permissions_check( $request ) {
		return WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	public function filter_rest_user_query( $args ) {
		if ( WPMUDEV_HUB_Plugin::is_multisite() ) {
			// set blog_id = 0, to get all users
			$args['blog_id'] = 0;
			/**
			 * list contains login name
			 *
			 * @see grant_super_admin
			 */
			$args['login__in'] = get_super_admins();
		}

		return $args;
	}

	public function get_items( $request ) {
		// force only this on single site
		if ( ! WPMUDEV_HUB_Plugin::is_multisite() ) {
			$request->set_param( 'roles', array( 'administrator' ) );
		}

		add_filter( 'rest_user_query', array( $this, 'filter_rest_user_query' ) );

		return parent::get_items( $request );
	}
}

add_action( 'rest_api_init', array( 'WPMUDEV_HUB_REST_API_V1_Admins', 'init_rest' ) );