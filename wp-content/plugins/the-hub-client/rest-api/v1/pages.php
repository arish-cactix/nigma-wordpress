<?php

class WPMUDEV_HUB_REST_API_V1_Pages extends WP_REST_Posts_Controller {
	protected $version = 1;

	public static function init_rest() {
		new self();
	}

	public function __construct( $post_type = 'page' ) {
		$this->post_type = $post_type;

		if ( $this->version ) {
			$this->namespace = trailingslashit( 'wpmudev-hub' ) . 'v' . $this->version;
		}

		$obj             = get_post_type_object( $post_type );
		$this->rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;

		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
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

	public function get_items_permissions_check( $request ) {
		return WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	public function get_items( $request ) {
		WPMUDEV_HUB_Plugin::maybe_switch_to_front_site();
		$items = parent::get_items( $request );
		WPMUDEV_HUB_Plugin::maybe_restore_current_blog_from_front_site();

		return $items;
	}
}

add_action( 'rest_api_init', array( 'WPMUDEV_HUB_REST_API_V1_Pages', 'init_rest' ) );