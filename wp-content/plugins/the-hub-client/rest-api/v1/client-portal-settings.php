<?php

class WPMUDEV_HUB_REST_API_V1_Client_Portal_Settings extends WPMUDEV_HUB_REST_API_Abstract {
	protected $version = 1;

	protected $rest_base = 'client-portal-settings';

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'doc_summary'         => __( 'Get Client Portal settings.', 'thc' ),
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'doc_summary'         => __( 'Update Client Portal settings.', 'thc' ),
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
			'title'      => 'wpmudev-hub-whitelabel-settings',
			'type'       => 'object',
			'properties' => array(
				'site_id'                             => array(
					'description' => __( 'Site ID where the Hub will be located / displayed. Only available for multisite setup.', 'thc' ),
					'type'        => 'integer',
					'example'     => 99,
				),
				'site_title'                          => array(
					'description' => __( 'Site Title where the Hub will be located / displayed. Only available for multisite setup.', 'thc' ),
					'type'        => 'string',
					'example'     => 'Page Title',
				),
				'page_id'                             => array(
					'description' => __( 'Page ID where the Hub will be located / displayed.', 'thc' ),
					'type'        => 'integer',
					'example'     => 99,
				),
				'page_title'                          => array(
					'description' => __( 'Page Title where the Hub will be located / displayed.', 'thc' ),
					'type'        => 'string',
					'example'     => 'Page Title',
				),
				'brand_name'                          => array(
					'description' => __( 'Brand name.', 'thc' ),
					'type'        => 'string',
					'format'      => 'text-field',
					'example'     => '',
					'arg_options' => $this->arg_options_for_text_field(),
				),
				'brand_logo_url'                      => array(
					'description' => __( 'Brand logo URL.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'example'     => 'https://example.org/image.png',
				),
				'brand_logo_urls'                     => array(
					'description' => __( 'Brand logo URLs. In various sizes.', 'thc' ),
					'type'        => 'array',
					'items'       => array(
						'type'    => 'string',
						'format'  => 'uri',
						'example' => 'https://example.org/image.png',
					),
				),
				'navigation_background_color'         => array(
					'description' => __( 'Hex color for navigation background.', 'thc' ),
					'type'        => 'string',
					'format'      => 'hex-color',
					'example'     => '#000000',
				),
				'navigation_text_color'               => array(
					'description' => __( 'Hex color for navigation text.', 'thc' ),
					'type'        => 'string',
					'format'      => 'hex-color',
					'example'     => '#000000',
				),
				'navigation_selected_hover_color'     => array(
					'description' => __( 'Hex color for navigation selected & hover.', 'thc' ),
					'type'        => 'string',
					'format'      => 'hex-color',
					'example'     => '#000000',
				),
				'content_hyperlink_color'             => array(
					'description' => __( 'Hex color for content hyperlink.', 'thc' ),
					'type'        => 'string',
					'format'      => 'hex-color',
					'example'     => '#000000',
				),
				'tos_page_id'                         => array(
					'description' => __( 'Page ID where the Terms of Service page will be located / displayed.', 'thc' ),
					'type'        => 'integer',
					'example'     => 99,
					'minimum'     => 1,
				),
				'tos_page_title'                      => array(
					'description' => __( 'Page Title where the Terms of Service page will be located / displayed.', 'thc' ),
					'type'        => 'string',
					'example'     => 'Page Title',
				),
				'tos_url'                             => array(
					'description' => __( 'Term of Service custom URL.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'example'     => 'https://example.org',
				),
				'privacy_page_id'                     => array(
					'description' => __( 'Page ID where the Privacy Policy page will be located / displayed.', 'thc' ),
					'type'        => 'integer',
					'example'     => 99,
					'minimum'     => 1,
				),
				'privacy_page_title'                  => array(
					'description' => __( 'Page Title where the Privacy Policy page will be located / displayed.', 'thc' ),
					'type'        => 'string',
					'example'     => 'Page Title',
				),
				'privacy_url'                         => array(
					'description' => __( 'Privacy Policy custom URL.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'example'     => 'https://example.org',
				),
				'wp_menus'                            => array(
					'description' => __( 'List of WP menus.', 'thc' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'                => array(
								'description' => __( 'Menu ID.', 'thc' ),
								'type'        => 'integer',
								'example'     => 1,
							),
							'name'              => array(
								'description' => __( 'Menu name.', 'thc' ),
								'type'        => 'string',
								'example'     => 'Menu #1',
							),
							'is_hub_navigation' => array(
								'description' => __( 'Flag whether this menu is Hub navigation.', 'thc' ),
								'type'        => 'boolean',
								'example'     => false,
							),
						),
					),
				),
				'navigation_items'                    => array(
					'description' => __( 'List of extra items to be displayed in navigation.', 'thc' ),
					'type'        => 'array',
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'title' => array(
								'description' => __( 'Menu title.', 'thc' ),
								'type'        => 'string',
								'example'     => 'Menu #1',
							),
							'url'   => array(
								'description' => __( 'Menu url.', 'thc' ),
								'type'        => 'string',
								'format'      => 'uri',
								'example'     => 'Menu #1',
							),
						),
					),
				),
				'admin_menus_url'                     => array(
					'description' => __( 'URL of custom menus screen in WP Admin.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'example'     => 'https://example.org/wp-admin/',
				),
				'admin_settings_url'                  => array(
					'description' => __( 'URL of settings in WP Admin.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'example'     => 'https://example.org/wp-admin/',
				),
				'dash_admin_translation_settings_url' => array(
					'description' => __( 'URL of WPMU DEV Dashboard translations settings in WP Admin.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'example'     => 'https://example.org/wp-admin/',
				),
				'front_page_url'                      => array(
					'description' => __( 'Front Page absolute url, can be empty, if not set it yet.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'example'     => 'https://example.org',
				),
				'manage_users_front_page_url'         => array(
					'description' => __( 'URL to manage users and roles, fallback to WPMUDEV Hub if front page not set.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'readonly'    => true,
					'example'     => 'https://example.org',
				),
				'help_url'                            => array(
					'description' => __( 'Help custom URL.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'example'     => 'https://example.org',
				),
				'custom_home_url'                     => array(
					'description' => __( 'Custom Home URL.', 'thc' ),
					'type'        => 'string',
					'format'      => 'uri',
					'example'     => 'https://example.org',
				),
				'custom_home_title'                   => array(
					'description' => __( 'Custom Home Title.', 'thc' ),
					'type'        => 'string',
					'format'      => 'text-field',
					'arg_options' => $this->arg_options_for_text_field(),
					'example'     => 'Title',
				),
				'custom_home_url_is_new_tab'          => array(
					'description' => __( 'Whether custom Home URL will be opened in new tab.', 'thc' ),
					'type'        => 'boolean',
					'example'     => false,
				),
				'live_chats'                          => array(
					'description' => __( 'Live Chats configuration.', 'thc' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'         => array(
								'type'   => 'string',
								'format' => 'text-field',
							),
							'is_enabled' => array(
								'type' => 'boolean',
							),
							// LC
							'license_id' => array(
								'type'   => 'string',
								'format' => 'text-field',
							),
							// tawk.to
							'chat_link'  => array(
								'type'   => 'string',
								'format' => 'uri',
							),
							// hubspot
							'tracker_id' => array(
								'type'   => 'string',
								'format' => 'text-field',
							),
						),
					),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_live_chats' ),
					),
				),
				'default_language'                    => array(
					'description' => __( 'Default language that will be used for Client Portal.', 'thc' ),
					'type'        => 'object',
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Language ID', 'thc' ),
							'type'        => 'string',
							'format'      => 'text-field',
							'example'     => 'en_US',
						),
						'name' => array(
							'description' => __( 'Language Display Name', 'thc' ),
							'type'        => 'string',
							'example'     => 'English (United States)',
						),
					),
				),
				'available_languages'                 => array(
					'description' => __( 'Available Languages to Select for Client Portal', 'thc' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'   => array(
								'description' => __( 'Language ID', 'thc' ),
								'type'        => 'string',
								'example'     => 'en_US',
							),
							'name' => array(
								'description' => __( 'Language Display Name', 'thc' ),
								'type'        => 'string',
								'example'     => 'English (United States)',
							),
						),
					),
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
	public function sanitize_live_chats( $value, $request, $param ) {
		$value = rest_sanitize_request_arg( $value, $request, $param );
		if ( version_compare( WPMUDEV_HUB_Plugin::get_wp_version(), '5.9', 'lt' ) ) {
			$text_fields = array( 'id', 'license_id', 'tracker_id' );
			$value       = is_array( $value ) ? $value : array();
			foreach ( $value as $key => $item ) {
				foreach ( $text_fields as $text_field ) {
					if ( isset( $item[ $text_field ] ) ) {
						$value[ $key ][ $text_field ] = sanitize_text_field( $item[ $text_field ] );
					}
				}
			}
		}

		return $value;
	}

	public function prepare_item_for_response( $item, $request ) {
		$data   = array();
		$fields = $this->get_fields_for_response( $request );

		$site_id = (int) WPMUDEV_HUB_Plugin::get_front_site_id();
		$page_id = WPMUDEV_HUB_Plugin::get_front_page_id();

		WPMUDEV_HUB_Plugin::maybe_switch_to_front_site();

		if ( rest_is_field_included( 'site_id', $fields ) ) {
			$data['site_id'] = $site_id;
		}

		if ( rest_is_field_included( 'site_title', $fields ) ) {
			$data['site_title'] = get_bloginfo( 'name' );
		}

		if ( rest_is_field_included( 'page_id', $fields ) ) {
			$data['page_id'] = $page_id;
		}

		if ( rest_is_field_included( 'page_title', $fields ) ) {
			$data['page_title'] = __( '--Page not found--', 'thc' );

			if ( $page_id ) {
				$post = get_post( $page_id );
				if ( $post ) {
					$data['page_title'] = $post->post_title;
				}
			} else {
				$data['page_title'] = __( 'Please select a page', 'thc' );
			}
		}

		if ( rest_is_field_included( 'brand_name', $fields ) ) {
			$data['brand_name'] = isset( $item['app_name'] ) ? (string) $item['app_name'] : '';
		}

		if ( rest_is_field_included( 'brand_logo_url', $fields ) ) {
			$data['brand_logo_url'] = WPMUDEV_HUB_Plugin::get_customization_app_logo();
		}

		if ( rest_is_field_included( 'brand_logo_urls', $fields ) ) {
			$data['brand_logo_urls'] = WPMUDEV_HUB_Plugin::get_customization_app_logo( 'all' );
		}

		if ( rest_is_field_included( 'navigation_background_color', $fields ) ) {
			$data['navigation_background_color'] = isset( $item['navigation_background_color'] )
				? (string) $item['navigation_background_color']
				: WPMUDEV_HUB_Plugin::DEFAULT_NAVIGATION_BACKGROUND_COLOR;
		}

		if ( rest_is_field_included( 'navigation_text_color', $fields ) ) {
			$data['navigation_text_color'] = isset( $item['navigation_text_color'] ) ? (string) $item['navigation_text_color'] : WPMUDEV_HUB_Plugin::DEFAULT_NAVIGATION_TEXT_COLOR;
		}

		if ( rest_is_field_included( 'navigation_selected_hover_color', $fields ) ) {
			$data['navigation_selected_hover_color'] = isset( $item['navigation_selected_hover_color'] )
				? (string) $item['navigation_selected_hover_color']
				: WPMUDEV_HUB_Plugin::DEFAULT_SELECTED_HOVER_COLOR;
		}

		if ( rest_is_field_included( 'content_hyperlink_color', $fields ) ) {
			$data['content_hyperlink_color'] = isset( $item['content_hyperlink_color'] ) ? (string) $item['content_hyperlink_color'] : WPMUDEV_HUB_Plugin::DEFAULT_NAVIGATION_TEXT_COLOR;
		}

		$tos_page_id = isset( $item['tos_page_id'] ) ? (int) $item['tos_page_id'] : 0;
		if ( rest_is_field_included( 'tos_page_id', $fields ) ) {
			$data['tos_page_id'] = $tos_page_id;
		}
		if ( rest_is_field_included( 'tos_page_title', $fields ) ) {
			$data['tos_page_title'] = __( '--Page not found--', 'thc' );

			if ( $tos_page_id ) {
				$post = get_post( $tos_page_id );
				if ( $post ) {
					$data['tos_page_title'] = $post->post_title;
				}
			} else {
				$data['tos_page_title'] = __( 'Please select a page', 'thc' );
			}
		}
		if ( rest_is_field_included( 'tos_url', $fields ) ) {
			if ( $tos_page_id ) {
				$data['tos_url'] = (string) get_page_link( $tos_page_id );
			} else {
				$data['tos_url'] = isset( $item['tos_url'] ) ? (string) $item['tos_url'] : '';
			}
		}

		$privacy_page_id = isset( $item['privacy_page_id'] ) ? (int) $item['privacy_page_id'] : 0;
		if ( rest_is_field_included( 'privacy_page_id', $fields ) ) {
			$data['privacy_page_id'] = $privacy_page_id;
		}
		if ( rest_is_field_included( 'privacy_page_title', $fields ) ) {
			$data['privacy_page_title'] = __( '--Page not found--', 'thc' );

			if ( $privacy_page_id ) {
				$post = get_post( $privacy_page_id );
				if ( $post ) {
					$data['privacy_page_title'] = get_the_title( $post );
				}
			} else {
				$data['privacy_page_title'] = __( 'Please select a page', 'thc' );
			}
		}
		if ( rest_is_field_included( 'privacy_url', $fields ) ) {
			if ( $privacy_page_id ) {
				$data['privacy_url'] = (string) get_page_link( $privacy_page_id );
			} else {
				$data['privacy_url'] = isset( $item['privacy_url'] ) ? (string) $item['privacy_url'] : '';
			}
		}

		if ( rest_is_field_included( 'help_url', $fields ) ) {
			$data['help_url'] = isset( $item['help_url'] ) ? (string) $item['help_url'] : '';
		}

		if ( rest_is_field_included( 'custom_home_url', $fields ) ) {
			$data['custom_home_url'] = isset( $item['custom_home_url'] ) ? (string) $item['custom_home_url'] : '';
		}

		if ( rest_is_field_included( 'custom_home_title', $fields ) ) {
			$data['custom_home_title'] = isset( $item['custom_home_title'] ) ? (string) $item['custom_home_title'] : '';
		}

		if ( rest_is_field_included( 'custom_home_url_is_new_tab', $fields ) ) {
			$data['custom_home_url_is_new_tab'] = isset( $item['custom_home_url_is_new_tab'] ) ? (bool) $item['custom_home_url_is_new_tab'] : false;
		}

		if ( rest_is_field_included( 'live_chats', $fields ) ) {
			$data['live_chats'] = isset( $item['live_chats'] ) ? $item['live_chats'] : array();
		}

		if ( rest_is_field_included( 'wp_menus', $fields ) ) {
			$menu_locations = get_nav_menu_locations();
			$hub_menu_id    = isset( $menu_locations[ WPMUDEV_HUB_Plugin::PLUGIN_SLUG ] ) ? $menu_locations[ WPMUDEV_HUB_Plugin::PLUGIN_SLUG ] : 0;

			// wp_menus
			$data['wp_menus'] = array();
			$wp_menus         = wp_get_nav_menus();

			foreach ( $wp_menus as $wp_menu ) {
				$data['wp_menus'][] = array(
					'id'                => $wp_menu->term_id,
					'title'             => $wp_menu->name,
					'is_hub_navigation' => (int) $hub_menu_id === (int) $wp_menu->term_id,
				);
			}
		}

		if ( rest_is_field_included( 'navigation_items', $fields ) ) {
			// menus
			$data['navigation_items'] = WPMUDEV_HUB_Plugin::get_extra_navigation_items();
		}

		if ( rest_is_field_included( 'admin_menus_url', $fields ) ) {
			// menus
			$data['admin_menus_url'] = admin_url( 'nav-menus.php' );
		}

		if ( rest_is_field_included( 'admin_settings_url', $fields ) ) {
			$data['admin_settings_url'] = admin_url( 'options-general.php' );
		}
		if ( rest_is_field_included( 'dash_admin_translation_settings_url', $fields ) ) {
			$data['dash_admin_translation_settings_url'] = network_admin_url( 'admin.php' ) . '?page=wpmudev-settings#translation';
		}

		if ( rest_is_field_included( 'front_page_url', $fields ) ) {
			// front page url
			$data['front_page_url'] = WPMUDEV_HUB_Plugin_Front::get_embed_url();
		}

		if ( rest_is_field_included( 'manage_users_front_page_url', $fields ) ) {
			$data['manage_users_front_page_url'] = '';
			if ( WPMUDEV_HUB_Plugin_Front::get_embed_url() ) {
				$data['manage_users_front_page_url'] = WPMUDEV_HUB_Plugin_Front::get_embed_url() . '#users';
			}
		}

		if ( rest_is_field_included( 'default_language', $fields ) ) {
			$data['default_language'] = $item['default_language'] ?? array(
				'id'   => 'en_US',
				'name' => 'English (US)',
			);
		}
		if ( rest_is_field_included( 'available_languages', $fields ) ) {
			$data['available_languages'] = $this->get_hub_available_languages();
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		WPMUDEV_HUB_Plugin::maybe_restore_current_blog_from_front_site();

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
		return rest_ensure_response( $this->prepare_item_for_response( WPMUDEV_HUB_Plugin::get_customization(), $request ) );
	}

	public function update_item( $request ) {
		$site_id                    = $request->get_param( 'site_id' );
		$page_id                    = $request->get_param( 'page_id' );
		$brand_name                 = $request->get_param( 'brand_name' );
		$nav_background_color       = $request->get_param( 'navigation_background_color' );
		$nav_text_color             = $request->get_param( 'navigation_text_color' );
		$nav_selected_color         = $request->get_param( 'navigation_selected_hover_color' );
		$content_link_color         = $request->get_param( 'content_hyperlink_color' );
		$wp_menus                   = $request->get_param( 'wp_menus' );
		$tos_page_id                = $request->get_param( 'tos_page_id' );
		$tos_url                    = $request->get_param( 'tos_url' );
		$privacy_page_id            = $request->get_param( 'privacy_page_id' );
		$privacy_url                = $request->get_param( 'privacy_url' );
		$brand_logo_file            = $request->get_param( 'brand_logo_file' );
		$help_url                   = $request->get_param( 'help_url' );
		$custom_home_url            = $request->get_param( 'custom_home_url' );
		$custom_home_title          = $request->get_param( 'custom_home_title' );
		$custom_home_url_is_new_tab = $request->get_param( 'custom_home_url_is_new_tab' );
		$live_chats                 = $request->get_param( 'live_chats' );
		$default_language           = $request->get_param( 'default_language' );

		if ( WPMUDEV_HUB_Plugin::is_multisite() && ! is_null( $site_id ) ) {
			$prev_site_id = WPMUDEV_HUB_Plugin::get_front_site_id();
			$site_id      = (int) $site_id;
			$blog         = get_blog_details( $site_id );
			if ( ! $blog ) {
				/* translators: %d: Site ID. */
				return new WP_Error( 'site_not_found', sprintf( __( 'Site with id %d not found.', 'thc' ), $site_id ), array( 'status' => WP_Http::BAD_REQUEST ) );
			}
			WPMUDEV_HUB_Plugin::update_front_site_id( $site_id );

			// reset page_id, menu_id, tos_page_id and privacy_page_id
			if ( (int) $prev_site_id !== $site_id ) {
				WPMUDEV_HUB_Plugin::update_front_page_id( 0 );

				$menu_locations = get_nav_menu_locations();
				unset( $menu_locations[ WPMUDEV_HUB_Plugin::PLUGIN_SLUG ] );
				set_theme_mod( 'nav_menu_locations', $menu_locations );

				WPMUDEV_HUB_Plugin::update_customization(
					array(
						'tos_page_id'     => null,
						'privacy_page_id' => null,
						// but keep the custom urls
					)
				);
			}
		}

		WPMUDEV_HUB_Plugin::maybe_switch_to_front_site();

		if ( ! is_null( $page_id ) ) {
			$page_id = (int) $page_id;
			if ( $page_id ) {
				$post = get_post( $page_id );
				if ( ! $post || 'page' !== $post->post_type ) {
					/* translators: %d: Page ID. */
					return new WP_Error( 'page_not_found', sprintf( __( 'Page with id %d not found.', 'thc' ), $page_id ), array( 'status' => WP_Http::BAD_REQUEST ) );
				}
			}
			WPMUDEV_HUB_Plugin::update_front_page_id( $page_id );
		}

		$data = array();

		if ( ! is_null( $brand_name ) ) {
			$data['app_name'] = $brand_name;
		}
		if ( ! is_null( $nav_background_color ) ) {
			$data['navigation_background_color'] = $nav_background_color;
		}
		if ( ! is_null( $nav_text_color ) ) {
			$data['navigation_text_color'] = $nav_text_color;
		}
		if ( ! is_null( $nav_selected_color ) ) {
			$data['navigation_selected_hover_color'] = $nav_selected_color;
		}
		if ( ! is_null( $content_link_color ) ) {
			$data['content_hyperlink_color'] = $content_link_color;
		}

		if ( ! is_null( $tos_page_id ) ) {
			$page_id = (int) $tos_page_id;
			if ( $page_id ) {
				$post = get_post( $page_id );
				if ( ! $post || 'page' !== $post->post_type ) {
					return new WP_Error(
						'page_not_found',
						/* translators: %d: Page ID. */
						sprintf( __( 'Invalid Term of Services page. Page with id %d not found.', 'thc' ), $page_id ),
						array( 'status' => WP_Http::BAD_REQUEST )
					);
				}
			}

			$data['tos_page_id'] = $page_id;
			$data['tos_url']     = null;
		} elseif ( ! is_null( $tos_url ) ) {
			// only consider tos_url only when tos_page_id is not provided
			$data['tos_url']     = $tos_url;
			$data['tos_page_id'] = null;
		}

		if ( ! is_null( $privacy_page_id ) ) {
			$page_id = (int) $privacy_page_id;
			if ( $page_id ) {
				$post = get_post( $page_id );
				if ( ! $post || 'page' !== $post->post_type ) {
					return new WP_Error(
						'page_not_found',
						/* translators: %d: Page ID. */
						sprintf( __( 'Invalid Privacy Policies page. Page with id %d not found.', 'thc' ), $page_id ),
						array( 'status' => WP_Http::BAD_REQUEST )
					);
				}
			}

			$data['privacy_page_id'] = $page_id;
			$data['privacy_url']     = null;
		} elseif ( ! is_null( $privacy_url ) ) {
			// only consider tos_url only when tos_page_id is not provided
			$data['privacy_url']     = $privacy_url;
			$data['privacy_page_id'] = null;
		}

		if ( ! is_null( $help_url ) ) {
			$data['help_url'] = $help_url;
		}
		if ( ! is_null( $custom_home_url ) ) {
			$data['custom_home_url'] = $custom_home_url;
		}
		if ( ! is_null( $custom_home_title ) ) {
			$data['custom_home_title'] = $custom_home_title;
		}
		if ( ! is_null( $custom_home_url_is_new_tab ) ) {
			$data['custom_home_url_is_new_tab'] = (bool) $custom_home_url_is_new_tab;
		}
		if ( ! is_null( $live_chats ) ) {
			$data['live_chats'] = $live_chats;
		}

		// uploader
		$files = $request->get_file_params();
		if ( isset( $files['brand_logo_file'] ) ) {
			$uploaded = WPMUDEV_HUB_Plugin::upload_app_logo_attach_id( $files['brand_logo_file'] );
			if ( is_wp_error( $uploaded ) ) {
				return $uploaded;
			}
		} elseif ( ! is_null( $brand_logo_file ) && empty( $brand_logo_file ) ) { // check removal, not null but empty
			if ( WPMUDEV_HUB_Plugin::has_custom_app_logo() ) {

				// keep it, in case its used on other pages by the user. They can delete it manually from Media library later
				//$old_attach_id = WPMUDEV_HUB_Plugin::get_customization_app_logo_attach_id();
				//wp_delete_attachment( $old_attach_id, true );
				WPMUDEV_HUB_Plugin::update_customization( array( 'app_logo_attach_id' => '' ) );
			}
		}

		if ( ! is_null( $wp_menus ) ) {
			$active_hub_wp_menu = 0;
			$wp_menu_ids        = array_map( 'intval', wp_list_pluck( wp_get_nav_menus(), 'term_id' ) );
			$wp_menus           = is_array( $wp_menus ) ? $wp_menus : array();
			foreach ( $wp_menus as $key => $wp_menu ) {
				if ( isset( $wp_menu['is_hub_navigation'], $wp_menu['id'] ) ) {
					$is_active = filter_var( $wp_menu['is_hub_navigation'], FILTER_VALIDATE_BOOLEAN );
					if ( $is_active ) {
						// only first
						$active_hub_wp_menu = (int) $wp_menu['id'];
						// check existence
						if ( ! in_array( $active_hub_wp_menu, $wp_menu_ids, true ) ) {
							return new WP_Error(
								'rest_invalid_param',
								/* translators: 1: Parameter name, 2: List of menu IDs */
								sprintf( __( '%1$s is not one of %2$s.', 'thc' ), 'wp_menus[' . $key . '][id]', implode( ', ', $wp_menu_ids ) ),
								array( 'status' => WP_Http::BAD_REQUEST )
							);
						}
						break;
					}
				}
			}

			$menu_locations                                    = array();
			$menu_locations[ WPMUDEV_HUB_Plugin::PLUGIN_SLUG ] = $active_hub_wp_menu;
			$menu_locations                                    = array_merge( get_nav_menu_locations(), $menu_locations );
			// Set menu locations.
			set_theme_mod( 'nav_menu_locations', $menu_locations );
		}

		// language
		if ( ! is_null( $default_language ) ) {
			$default_language_id    = $default_language['id'] ?? 'en_US';
			$available_languages    = $this->get_hub_available_languages();
			$available_language_ids = wp_list_pluck( $available_languages, 'id' );
			if ( ! in_array( $default_language_id, $available_language_ids, true ) ) {
				return new WP_Error(
					'rest_invalid_param',
					/* translators: 1: language id from input, 2: List of available languages */
					sprintf( __( '%1$s is not one of %2$s.', 'thc' ), $default_language_id, implode( ', ', $available_language_ids ) ),
					array( 'status' => WP_Http::BAD_REQUEST )
				);
			}

			// Force normalize lang name
			$default_language_name = $default_language['name'] ?? 'English (US)';
			foreach ( $available_languages as $available_language ) {
				$available_language_id = $available_language['id'] ?? '';
				if ( $default_language_id === $available_language_id ) {
					$default_language_name = $available_language['name'] ?? '';
					break;
				}
			}

			$data['default_language'] = array(
				'id'   => $default_language_id,
				'name' => $default_language_name,
			);
		}

		WPMUDEV_HUB_Plugin::update_customization( $data );

		WPMUDEV_HUB_Plugin::maybe_restore_current_blog_from_front_site();

		$current_customization = WPMUDEV_HUB_Plugin::get_customization( '', false, true );

		return rest_ensure_response( $this->prepare_item_for_response( $current_customization, $request ) );
	}

	private function get_hub_available_languages( $re_fetch = false ) {
		if ( $re_fetch ) {
			delete_site_transient( 'wpmudev_hub_available_languages' );
		}

		$available_languages = get_site_transient( 'wpmudev_hub_available_languages' );
		if ( is_array( $available_languages ) ) {
			return $available_languages;
		}

		$res = WPMUDEV_HUB_API_Request::get_instance()->exec(
			array(
				'path'   => '/app-settings',
				'method' => 'GET',
				'data'   => array(
					'_fields' => 'available_languages',
				),
			)
		);

		if ( is_wp_error( $res ) ) {
			// return empty, not cached
			return array();
		}

		$available_languages = $res['available_languages'] ?? array();
		// cache 1 day
		set_site_transient( 'wpmudev_hub_available_languages', $available_languages, DAY_IN_SECONDS );

		return $available_languages;
	}
}

new WPMUDEV_HUB_REST_API_V1_Client_Portal_Settings();