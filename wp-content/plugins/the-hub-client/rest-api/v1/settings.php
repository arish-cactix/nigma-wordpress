<?php

class WPMUDEV_HUB_REST_API_V1_Settings extends WPMUDEV_HUB_REST_API_Abstract {
	protected $version = 1;

	protected $rest_base = 'settings';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'doc_summary'         => __( 'Get Whitelabel HUB settings.', 'thc' ),
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'doc_summary'         => __( 'Update Whitelabel HUB settings.', 'thc' ),
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'doc_summary'         => __( 'Reset Whitelabel HUB settings.', 'thc' ),
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	public function item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'wpmudev-hub-whitelabel-settings',
			'type'       => 'object',
			'properties' => array(
				'is_reset_on_uninstall'          => array(
					'description' => __( 'Whether to reset settings on uninstall.', 'thc' ),
					'type'        => 'boolean',
					'example'     => false,
				),
				'is_manage_only_selected_admins' => array(
					'description' => __( 'Whether manage plugin is available only for selected admins.', 'thc' ),
					'type'        => 'boolean',
					'example'     => false,
				),
				'selected_admins'                => array(
					'description' => __( 'Selected admins that allowed to manage plugin.', 'thc' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'           => array(
								'type'     => 'integer',
								'required' => true,
							),
							'name'         => array(
								'type'     => 'string',
								'format'   => 'text',
								'readonly' => true,
							),
							'email'        => array(
								'type'     => 'string',
								'format'   => 'email',
								'readonly' => true,
							),
							'avatar_url'   => array(
								'type'     => 'string',
								'format'   => 'uri',
								'readonly' => true,
							),
							'is_removable' => array(
								'type'     => 'boolean',
								'readonly' => true,
							),
						),
					),
				),
			),
		);
	}

	public function get_item_permissions_check( $request ) {
		return WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user();
	}

	public function prepare_item_for_response( $item, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		if ( rest_is_field_included( 'is_reset_on_uninstall', $fields ) ) {
			$data['is_reset_on_uninstall'] = isset( $item['is_reset_on_uninstall'] ) ? (bool) $item['is_reset_on_uninstall'] : false;
		}
		if ( rest_is_field_included( 'is_manage_only_selected_admins', $fields ) ) {
			$data['is_manage_only_selected_admins'] = WPMUDEV_HUB_Permissions::get_instance()->is_only_selected_admins();
		}
		if ( rest_is_field_included( 'selected_admins', $fields ) ) {
			$data['selected_admins'] = WPMUDEV_HUB_Permissions::get_instance()->get_allowed_admin_users( get_current_user_id() );
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		return rest_ensure_response( $data );
	}

	public function get_item( $request ) {
		return rest_ensure_response( $this->prepare_item_for_response( WPMUDEV_HUB_Plugin::get_customization(), $request ) );
	}

	public function update_item( $request ) {
		$current_user_id = (int) get_current_user_id();

		$is_reset_on_uninstall          = $request->get_param( 'is_reset_on_uninstall' );
		$is_manage_only_selected_admins = $request->get_param( 'is_manage_only_selected_admins' );
		$selected_admins                = $request->get_param( 'selected_admins' );

		$current_is_only_selected_admins = WPMUDEV_HUB_Permissions::get_instance()->is_only_selected_admins();

		if ( ! is_null( $is_manage_only_selected_admins ) || ! is_null( $selected_admins ) ) {
			$is_selected_admins = ! is_null( $is_manage_only_selected_admins ) ? $is_manage_only_selected_admins : $current_is_only_selected_admins;

			if ( $is_selected_admins ) {
				$selected_admins    = is_array( $selected_admins ) ? $selected_admins : array();
				$selected_admin_ids = wp_list_pluck( $selected_admins, 'id' );
				$selected_admin_ids = array_map( 'intval', $selected_admin_ids );

				if ( empty( $selected_admin_ids ) ) {
					return new WP_Error( 'empty_selected_admins', __( 'Please provide selected admins to be allowed to manage.', 'thc' ), array( 'status' => 400 ) );
				}

				if ( ! in_array( $current_user_id, $selected_admin_ids, true ) ) {
					return new WP_Error(
						'self_not_found',
						__( 'To ensure you will still have access to the plugin settings, please add your own user to the selected admins list.', 'thc' ),
						array( 'status' => 400 )
					);
				}

				$updated = WPMUDEV_HUB_Permissions::get_instance()->set_allowed_admin_ids( $selected_admin_ids );
				if ( is_wp_error( $updated ) ) {
					return $updated;
				}
			}

			// only do this once all good
			WPMUDEV_HUB_Permissions::get_instance()->set_only_selected_admins( $is_selected_admins );
		}

		$data = array();

		if ( ! is_null( $is_reset_on_uninstall ) ) {
			$data['is_reset_on_uninstall'] = (bool) $is_reset_on_uninstall;
		}

		WPMUDEV_HUB_Plugin::update_customization( $data );

		$current_customization = WPMUDEV_HUB_Plugin::get_customization( '', false, true );

		return rest_ensure_response( $this->prepare_item_for_response( $current_customization, $request ) );
	}

	public function delete_item( $request ) {
		// dont delete auto created page
		WPMUDEV_HUB_Plugin::reset_data( false );

		return rest_ensure_response( $this->prepare_item_for_response( WPMUDEV_HUB_Plugin::get_customization( '', false, true ), $request ) );
	}
}

new WPMUDEV_HUB_REST_API_V1_Settings();