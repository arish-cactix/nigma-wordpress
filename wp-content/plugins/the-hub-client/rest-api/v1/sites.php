<?php

class WPMUDEV_HUB_REST_API_V1_Sites extends WPMUDEV_HUB_REST_API_Abstract {
	protected $version = 1;

	protected $rest_base = 'sites';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'doc_summary'         => __( 'Get Sites / Blogs list.', 'thc' ),
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'wpmudev-hub-whitelabel-sites',
			'type'       => 'object',
			'properties' => array(
				'id'    => array(
					'description' => __( 'Site ID.', 'thc' ),
					'type'        => 'integer',
					'example'     => 'id',
				),
				'title' => array(
					'description' => __( 'Site title.', 'thc' ),
					'type'        => 'string',
					'example'     => 'Site Title',
				),
			),
		);
	}

	public function get_items_permissions_check( $request ) {
		return WPMUDEV_HUB_Plugin::is_multisite() && WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	/**
	 * @param WP_Site         $site
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $site, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		if ( rest_is_field_included( 'id', $fields ) ) {
			$data['id'] = (int) $site->blog_id;
		}

		if ( rest_is_field_included( 'title', $fields ) ) {
			$data['title'] = $site->blogname;
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		return rest_ensure_response( $data );
	}

	public function get_items( $request ) {
		$search   = $request->get_param( 'search' );
		$page     = $request->get_param( 'page' );
		$per_page = $request->get_param( 'per_page' );

		$offset = $per_page * ( $page - 1 );

		/**
		 * @see get_sites()
		 */
		$query        = new WP_Site_Query();
		$site_results = $query->query(
			array(
				'number'         => $per_page,
				'search'         => $search,
				'no_found_rows'  => false,
				'search_columns' => array( 'domain', 'path' ),
				'offset'         => $offset,
			)
		);

		$sites = array();

		foreach ( $site_results as $site ) {
			$data    = $this->prepare_item_for_response( $site, $request );
			$sites[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $sites );
		$response->header( 'X-WP-Total', (int) $query->found_sites );
		$response->header( 'X-WP-TotalPages', (int) $query->max_num_pages );

		return $response;
	}
}

new WPMUDEV_HUB_REST_API_V1_Sites();