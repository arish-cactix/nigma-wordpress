<?php

/**
 * Init/root class
 */
class WPMUDEV_HUB_Plugin {

	private static $instance = null;

	const VERSION     = WPMUDEV_HUB_VERSION;
	const PLUGIN_SLUG = 'wpmudev-hub';

	const DEFAULT_NAVIGATION_BACKGROUND_COLOR = '#56146b';
	const DEFAULT_NAVIGATION_TEXT_COLOR       = '#C09FDA';
	const DEFAULT_SELECTED_HOVER_COLOR        = '#FFFFFF';

	private static $base_static_server = null;

	private static $logo_sizes
		= array(
			'180'   => array(
				'width'  => 180,
				'height' => 180,
				'crop'   => false, // when its false it will be scaled, it won't be always have exact width X height
			),
			'360'   => array(
				'width'  => 360,
				'height' => 360,
				'crop'   => false, // when its false it will be scaled, it won't be always have exact width X height
			),
			'96'    => array(
				'width'  => 96,
				'height' => 96,
				'crop'   => false, // when its false it will be scaled, it won't be always have exact width X height
			),
			'30'    => array(
				'width'  => 30,
				'height' => 30,
				'crop'   => false, // when its false it will be scaled, it won't be always have exact width X height
			),
			'h_180' => array(
				'width'  => 0,
				'height' => 180,
				'crop'   => false, // when its false it will be scaled, it won't be always have exact width X height
			),
			'h_96'  => array(
				'width'  => 0,
				'height' => 96,
				'crop'   => false, // when its false it will be scaled, it won't be always have exact width X height
			),
			'h_30'  => array(
				'width'  => 0,
				'height' => 30,
				'crop'   => false, // when its false it will be scaled, it won't be always have exact width X height
			),
		);

	const LOGO_SIZE_PREFIX = 'wpmudev-hub-logo-';

	const REST_API_SLUG_BASE                               = 'base';
	const REST_API_SLUG_SETTINGS                           = 'settings';
	const REST_API_SLUG_CLIENT_PORTAL_SETTINGS             = 'client_portal_settings';
	const REST_API_SLUG_RESELLER_HOSTING_SETTINGS          = 'reseller_hosting_settings';
	const REST_API_SLUG_PUBLIC_RESELLER_HOSTING_SETTINGS   = 'public_reseller_hosting_settings';
	const REST_API_SLUG_RESELLER_HOSTING_SETTINGS_PRODUCTS = 'reseller_hosting_settings_products';
	const REST_API_SLUG_ADMINS                             = 'admins';
	const REST_API_SLUG_PAGES                              = 'pages';
	const REST_API_SLUG_SITES                              = 'sites';
	const REST_API_SLUG_RESELLER_HOSTING_PRODUCTS          = 'reseller_hosting_products';
	const REST_API_SLUG_RESELLER_DOMAINS_PLANS             = 'reseller_domains_plans';
	const REST_API_SLUG_RESELLER_DOMAINS_SETTINGS          = 'reseller_domains_settings';
	const REST_API_SLUG_PUBLIC_RESELLER_DOMAIN_SETTINGS    = 'public_reseller_domain_settings';
	const REST_API_SLUG_PUBLIC_RESELLER_DOMAIN_LOOKUP      = 'public_reseller_domain_lookup';

	// memoize
	private static $hub_branding = array();

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		register_activation_hook( WPMUDEV_HUB_PLUGIN_FILE, array( 'WPMUDEV_HUB_Plugin', 'maybe_auto_create_front_page' ) );
		register_deactivation_hook( WPMUDEV_HUB_PLUGIN_FILE, array( 'WPMUDEV_HUB_Plugin', 'maybe_remove_auto_created_front_page' ) );
		register_uninstall_hook( WPMUDEV_HUB_PLUGIN_FILE, array( 'WPMUDEV_HUB_Plugin', 'uninstall' ) );
		add_action( 'init', array( $this, 'register_nav_menus' ) );
		add_action( 'init', array( $this, 'load_locale' ) );

		require_once plugin_dir_path( __FILE__ ) . 'includes/api-request.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/permissions.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/reseller.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/hosting-reseller.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/domain-reseller.php';
		require_once plugin_dir_path( __FILE__ ) . 'admin/admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'admin/reseller.php';
		require_once plugin_dir_path( __FILE__ ) . 'admin/settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'front/front.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/abstract.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/client-portal-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller/hosting-products.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller/hosting-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller/hosting-settings/products.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller/public-hosting-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller/domain-plans.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller/domain-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller/public-domain-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/reseller/public-domain-lookup.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/pages.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/sites.php';
		require_once plugin_dir_path( __FILE__ ) . 'rest-api/v1/admins.php';
		require_once plugin_dir_path( __FILE__ ) . 'hub-rest-api/settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'hub-rest-api/email.php';

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'admin/dash-notice/wpmudev-dash-notification.php' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'admin/dash-notice/wpmudev-dash-notification.php';
		}
	}

	public static function get_manage_plugin_cap() {
		/**
		 * Filters WP capability to manage Hub Client settings
		 *
		 * @param string $cap_name the cap name that will be checked to allow Hub client settings
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'wpmudev_hub_manage_plugin_cap', self::is_multisite() ? 'manage_network_options' : 'manage_options' );
	}

	public function load_locale() {
		load_plugin_textdomain( 'thc', false, dirname( plugin_basename( WPMUDEV_HUB_PLUGIN_FILE ) ) . '/i18n/languages/' );
	}

	public static function maybe_auto_create_front_page() {
		require_once ABSPATH . 'wp-admin/includes/post.php';
		$front_page_id = self::get_front_page_id();

		// sanity old data
		if ( $front_page_id ) {
			if ( self::is_multisite() ) {
				self::maybe_switch_to_front_site();
			}

			$old_post = get_post( $front_page_id );

			if ( self::is_multisite() ) {
				self::maybe_switch_to_main_site();
			}

			if ( ! $old_post || ( $old_post instanceof WP_Post && ( 'page' !== $old_post->post_type || 'publish' !== $old_post->post_status ) ) ) {
				self::update_front_page_id( 0 );
			}

			// reload
			$front_page_id = self::get_front_page_id();
		}

		// check old front_site id
		$front_site_id = self::get_front_site_id();
		if ( $front_site_id ) {
			// no longer multisite
			if ( ! self::is_multisite() ) {
				self::update_front_site_id( 0 );
			} elseif ( ! get_blog_details( $front_site_id ) ) {// site no longer exists
				self::update_front_site_id( 0 );
			}
		}

		// only if not set
		if ( ! $front_page_id ) {
			if ( self::is_multisite() ) {
				self::maybe_switch_to_front_site();
			}

			// post exists prev
			if ( post_exists( 'hub', '', '', 'page' ) ) {
				// retries
				$max_retries = 10;
				for ( $suffix = 1; $suffix <= $max_retries; $suffix++ ) {
					if ( ! post_exists( sprintf( 'hub%d', $suffix ), '', '', 'page' ) ) {
						// create the page
						$front_page_id = wp_insert_post(
							array(
								'post_status' => 'publish',
								'post_type'   => 'page',
								'post_title'  => sprintf( 'hub%d', $suffix ),
							)
						);
						break;
					}
				}
			} else {
				// create the page
				$front_page_id = wp_insert_post(
					array(
						'post_status' => 'publish',
						'post_type'   => 'page',
						'post_title'  => 'hub',
					)
				);
			}

			if ( self::is_multisite() ) {
				self::maybe_switch_to_main_site();
			}

			if ( $front_page_id ) {
				// so we can clean it up later
				update_site_option(
					'wpmudev_hub_auto_front_page',
					array(
						'site_id' => self::get_front_site_id(),
						'page_id' => $front_page_id,
					)
				);

				self::update_front_page_id( $front_page_id );
			}
		}
	}

	public static function maybe_remove_auto_created_front_page() {
		// old data
		$auto_front_page = get_site_option( 'wpmudev_hub_auto_front_page', array() );
		$auto_front_page = is_array( $auto_front_page ) ? $auto_front_page : array();

		if ( isset( $auto_front_page['page_id'] ) && $auto_front_page['page_id'] ) {
			if ( self::is_multisite() ) {
				// multisite
				// only if site_id set
				if ( isset( $auto_front_page['site_id'] ) && $auto_front_page['site_id'] ) {
					$auto_front_site = get_blog_details( $auto_front_page['site_id'] );
					if ( $auto_front_site ) {
						self::switch_to_site( $auto_front_page['site_id'] );

						wp_delete_post( $auto_front_page['page_id'], true ); // bypass trash
					}
				}
			} else {
				// single site
				wp_delete_post( $auto_front_page['page_id'], true ); // bypass trash
			}

			if ( self::is_multisite() ) {
				self::maybe_switch_to_main_site();
			}
		}

		// whatever happens delete the option
		delete_site_option( 'wpmudev_hub_auto_front_page' );
	}

	public function register_nav_menus() {
		if ( self::is_multisite() ) {
			if ( (int) self::get_front_site_id() !== get_current_blog_id() ) {
				return;
			}
		}

		register_nav_menus(
			array(
				self::PLUGIN_SLUG => sanitize_text_field( __( 'The Hub Client Navigation', 'thc' ) ),
			)
		);
	}

	public static function is_dash_plugin_installed() {
		/**
		 * Filters whether Dashboard Plugin installed
		 *
		 * @param bool $installed
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'wpmudev_hub_is_dash_plugin_installed', file_exists( WP_PLUGIN_DIR . '/wpmudev-updates/update-notifications.php' ) );
	}

	public static function is_dash_plugin_active() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$active = false;
		if ( is_multisite() ) {
			if ( is_plugin_active_for_network( 'wpmudev-updates/update-notifications.php' ) ) {
				$active = true;
			}
		} elseif ( is_plugin_active( 'wpmudev-updates/update-notifications.php' ) ) {
			$active = true;
		}

		/**
		 * Filters whether Dashboard Plugin activated
		 *
		 * @param bool $active
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'wpmudev_hub_is_dash_plugin_active', $active );
	}

	public static function can_connect_api() {
		/**
		 * Filters whether can connect to API
		 *
		 * @param bool $can_connect
		 *
		 * @since 2.0.0
		 */
		return apply_filters(
			'wpmudev_hub_can_connect_api',
			( self::is_dash_plugin_installed() && self::is_dash_plugin_active() && WPMUDEV_HUB_API_Request::get_instance()->get_dashboard_api_key() )
		);
	}

	public static function get_account_data( $re_fetch = false ) {
		self::maybe_sync_account_data( $re_fetch );

		$data = get_site_transient( 'wpmudev_hub_account_data' );
		$data = is_array( $data ) ? $data : array();

		unset( $data['used_api_key'] );
		unset( $data['used_api_server'] );

		return $data;
	}

	public static function update_account_data( $data ) {
		// transient
		// append api_key
		$data['used_api_key']    = WPMUDEV_HUB_API_Request::get_instance()->get_dashboard_api_key();
		$data['used_api_server'] = WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server();

		set_site_transient( 'wpmudev_hub_account_data', $data, DAY_IN_SECONDS );
	}

	public static function maybe_sync_account_data( $re_fetch = false ) {
		if ( $re_fetch ) {
			// delete transient
			self::update_account_data( array() );
		}

		if ( ! self::can_connect_api() ) {
			self::update_account_data( array() );
		} else {
			// transient expired
			$invalid_data = ! get_site_transient( 'wpmudev_hub_account_data' );
			if ( ! $invalid_data ) {
				// check api_key / api_server changes
				$account_data = get_site_transient( 'wpmudev_hub_account_data' );
				if ( ! isset( $account_data['used_api_key'], $account_data['used_api_server'] ) ) {
					$invalid_data = true;
				} elseif (
					WPMUDEV_HUB_API_Request::get_instance()->get_dashboard_api_key() !== $account_data['used_api_key']
					|| WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() !== $account_data['used_api_server']
				) {
					$invalid_data = true;
				} elseif ( isset( $account_data['used_api_key'], $account_data['used_api_server'] ) ) {
					unset( $account_data['used_api_key'] );
					unset( $account_data['used_api_server'] );
					if ( ! $account_data ) {
						$invalid_data = true;
					}
				}
			}

			if ( $invalid_data ) {
				$response = self::hub_api_request(
					array(
						'path'   => 'account',
						'method' => 'GET',
					)
				);
				if ( ! is_wp_error( $response ) ) {
					self::update_account_data( $response );
				} else {
					self::update_account_data( array() );
				}
			}
		}
	}

	public static function get_base_static_server() {
		if ( ! is_null( self::$base_static_server ) ) {
			return self::$base_static_server;
		}

		self::$base_static_server = WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server();

		return self::$base_static_server;
	}

	public static function hub_api_request( $request_args, &$redirect_location = null ) {
		return WPMUDEV_HUB_API_Request::get_instance()->exec( $request_args, $redirect_location );
	}

	public static function is_hub_fe_ready() {
		self::maybe_sync_account_data();

		return self::can_connect_api() && self::get_account_data() && self::get_hub_site_id();
	}

	public static function get_team_id() {
		if ( ! self::is_hub_fe_ready() ) {
			return 0;
		}

		$account_data = self::get_account_data();

		return (int) $account_data['id'];
	}

	public static function get_assets_manifest() {
		$manifest = self::hub_api_request(
			array(
				'path'      => '/manifest.json',
				'base_path' => 'hub2/build/',
			)
		);
		if ( is_wp_error( $manifest ) ) {
			/* translators: %s: Error message from API. */
			$message = sprintf( __( 'Unable to get Hub Manifest: %s', 'thc' ), $manifest->get_error_message() );

			return new WP_Error( $manifest->get_error_code(), $message );
		}

		return $manifest;
	}

	public static function get_hub_branding( $re_fetch = false ) {
		if ( $re_fetch ) {
			self::$hub_branding = array();
		}
		if ( self::$hub_branding ) {
			return self::$hub_branding;
		}

		$branding = array(
			'app_name' => '',
			'app_logo' => '',
		);

		$account_data = self::get_account_data( $re_fetch );

		if ( $account_data ) {
			$teams = $account_data['teams'] ?? array();
			$teams = is_array( $teams ) ? $teams : array();

			// find the owner
			foreach ( $teams as $team ) {
				if ( isset( $team['is_owner'] ) && $team['is_owner'] ) {
					$branding['app_name'] = $team['name'] ?? '';
					$branding['app_logo'] = $team['avatar_url'] ?? '';

					// default gravatar, assume empty
					if ( stripos( $branding['app_logo'], 'https://secure.gravatar.com' ) === 0 ) {
						$branding['app_logo'] = '';
					}
					break;
				}
			}
		}

		self::$hub_branding = $branding;

		return self::$hub_branding;
	}

	public static function get_customization_data( $item = '', $fallback = false, $re_fetch = false ) {
		$local_option = get_site_option( 'wpmudev_hub_customization', array() );

		// Override Name and Logo from Hub data if empty
		if ( ! self::has_custom_app_name() || ! self::has_custom_app_logo() ) {
			$branding = self::get_hub_branding( $re_fetch );
			if ( ! isset( $local_option['app_name'] ) || ! $local_option['app_name'] ) {
				$local_option['app_name'] = $branding['app_name'];
			}
			if ( ! isset( $local_option['app_logo'] ) || ! $local_option['app_logo'] ) {
				$local_option['app_logo'] = $branding['app_logo'];
			}
		}

		if ( ! $item ) {
			return $local_option;
		}

		$option = $local_option;
		if ( ! isset( $option[ $item ] ) ) {
			return $fallback;
		}

		return $option[ $item ];
	}

	public static function get_customization( $item = '', $fallback = false, $re_fetch = false ) {
		$data = self::get_customization_data( $item, $fallback, $re_fetch );

		/**
		 * Filters Hub Client Customization value
		 *
		 * @param mixed  $data     the customization value
		 * @param string $item     the customization item / key
		 * @param mixed  $fallback the default value to be returned when item not exists
		 * @param bool   $re_fetch whether fresh data requested
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'wpmudev_hub_get_customization', $data, $item, $fallback, $re_fetch );
	}

	public static function get_tos_url() {
		$tos_page_id = self::get_customization( 'tos_page_id', '' );
		if ( $tos_page_id ) {
			return apply_filters( 'wpmudev_hub_tos_url', get_permalink( $tos_page_id ) );
		}

		return self::get_customization( 'tos_url', '' );
	}

	public static function get_privacy_url() {
		$privacy_page_id = self::get_customization( 'privacy_page_id', '' );
		if ( $privacy_page_id ) {
			return apply_filters( 'wpmudev_hub_privacy_url', get_permalink( $privacy_page_id ) );
		}

		return self::get_customization( 'privacy_url', '' );
	}

	public static function has_custom_app_name() {
		$local_option = get_site_option( 'wpmudev_hub_customization', array() );

		return ! empty( $local_option['app_name'] );
	}

	public static function has_custom_app_logo() {
		$local_option       = get_site_option( 'wpmudev_hub_customization', array() );
		$app_logo_attach_id = ( isset( $local_option['app_logo_attach_id'] ) && $local_option['app_logo_attach_id'] ) ? $local_option['app_logo_attach_id'] : false;
		if ( ! $app_logo_attach_id || ! is_numeric( $app_logo_attach_id ) ) {
			return false;
		}

		// check attachment existence
		return wp_attachment_is_image( $app_logo_attach_id );
	}

	public static function get_customization_app_name( $fallback = '' ) {
		return self::get_customization( 'app_name', $fallback );
	}

	public static function get_customization_app_logo( $size = 'wpmudev-hub-logo-96' ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$attach_id = self::get_customization_app_logo_attach_id();
		if ( ! $attach_id ) {
			return self::get_customization( 'app_logo', '' );
		}

		if ( ! is_numeric( $attach_id ) ) {
			// lets delete this invalid reference
			self::update_customization( array( 'app_logo_attach_id', '' ) );

			return self::get_customization( 'app_logo', '' );
		}

		// check attachment existence
		$exist = wp_attachment_is_image( $attach_id );
		if ( ! $exist ) {
			// lets delete this invalid reference
			self::update_customization( array( 'app_logo_attach_id', '' ) );

			return self::get_customization( 'app_logo', '' );
		}

		self::add_custom_logo_sizes();

		// maybe generate missing customs sizes
		wp_update_image_subsizes( $attach_id );

		$full_image  = '';
		$sized_image = '';
		$image_src   = wp_get_attachment_image_src( $attach_id, 'full' );
		if ( ! empty( $image_src[0] ) ) {
			$full_image = $image_src[0];
		}
		if ( 'full' === $size ) {
			self::remove_custom_logo_sizes();

			/**
			 * Filters app logo url
			 *
			 * @param string|array $image_url
			 * @param string       $size      the size id
			 * @param int          $attach_id Attachment id of uploaded logo
			 *
			 * @since 2.0.0
			 */
			return apply_filters( 'wpmudev_hub_app_logo_url', $full_image, $size, $attach_id );
		}

		if ( 'all' === $size ) {
			$images = array( 'full' => $full_image );

			foreach ( array_keys( self::get_logo_sizes_config() ) as $size_id ) {
				$size_logo_id = 'wpmudev-hub-logo-' . $size_id;
				$image_src    = wp_get_attachment_image_src( $attach_id, $size_logo_id );
				if ( ! empty( $image_src[0] ) ) {
					$images[ $size_id ] = $image_src[0];
				} else {
					$images[ $size_id ] = $full_image;
				}
			}

			self::remove_custom_logo_sizes();

			/**
			 * Filters app logo url
			 *
			 * @param string|array $image_url
			 * @param string       $size      the size id
			 * @param int          $attach_id Attachment id of uploaded logo
			 *
			 * @since 2.0.0
			 */
			return apply_filters( 'wpmudev_hub_app_logo_url', $images, $size, $attach_id );
		}

		$image_src = wp_get_attachment_image_src( $attach_id, $size );
		if ( ! empty( $image_src[0] ) ) {
			$sized_image = $image_src[0];
		}

		self::remove_custom_logo_sizes();

		/**
		 * Filters app logo url
		 *
		 * @param string|array $image_url
		 * @param string       $size      the size id
		 * @param int          $attach_id Attachment id of uploaded logo
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'wpmudev_hub_app_logo_url', $sized_image ? $sized_image : $full_image, $size, $attach_id );
	}

	public static function upload_app_logo_attach_id( $file ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		if ( ! $file ) {
			return new WP_Error( 'invalid_image_file', __( 'Invalid image file.', 'thc' ) );
		}

		$type = $file['type'] ?? '';
		/**
		 * @see media_handle_upload()
		 */
		if ( 0 !== strpos( $type, 'image/' ) ) {
			/* translators: %s: Detected File Type. */
			return new WP_Error( 'invalid_image_type', sprintf( __( 'Invalid image type : %s', 'thc' ), $type ) );
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : '';
		if ( $size && $size > ( 1 * MB_IN_BYTES ) ) { // 1mb limit
			/* translators: %s: Detected File Size. */
			return new WP_Error( 'invalid_image_size', sprintf( __( 'Invalid file size : %s MB', 'thc' ), number_format_i18n( $size / MB_IN_BYTES ) ) );
		}

		try {
			self::add_custom_logo_sizes();
			$attach_id = media_handle_sideload( $file );
			if ( is_wp_error( $attach_id ) ) {
				self::remove_custom_logo_sizes();

				return $attach_id;
			}

			// keep it, in case its used on other pages by the user. They can delete it manually from Media library later
			//          if ( self::has_custom_app_logo() ) {
			//              $old_attach_id = self::get_customization_app_logo_attach_id();
			//              wp_delete_attachment( $old_attach_id, true );
			//          }

			self::update_customization( array( 'app_logo_attach_id' => $attach_id ) );

			return true;
		} catch ( Exception $e ) {
			self::remove_custom_logo_sizes();

			// attempt to catch exception

			return new WP_Error(
				'upload_image_failed',
				/* translators: %s: Error message from upload failure. */
				sprintf( __( 'Failed to upload image : %s', 'thc' ), $e->getMessage() )
			);
		}
	}

	public static function get_customization_app_logo_attach_id() {
		return self::get_customization( 'app_logo_attach_id', '' );
	}

	public static function get_default_language() {
		$default_language = self::get_customization(
			'default_language',
			array(
				'id'   => 'en_US',
				'name' => 'English (US)',
			)
		);

		/**
		 * Filters Client Portal default language ID
		 *
		 * @param array $default_language Default Language in array format [id=>'',name=>'']
		 *
		 * @since 2.1.0
		 */
		return apply_filters( 'wpmudev_hub_default_language', $default_language );
	}

	public static function update_customization( $data ) {
		$data = wp_parse_args( $data, self::get_customization() );

		/**
		 * Fires before Hub Client customization update
		 *
		 * @param array $data customization data in request
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpmudev_hub_before_update_customization', $data );

		$updated = update_site_option( 'wpmudev_hub_customization', $data );

		/**
		 * Fires after Hub Client customization update
		 *
		 * @param array $data    customization data in request
		 * @param bool  $updated whether customization data updated
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpmudev_hub_after_update_customization', $data, $updated );

		return $updated;
	}

	public static function get_extra_navigation_items() {
		$items          = array();
		$menu_locations = get_nav_menu_locations();
		$hub_menu_id    = isset( $menu_locations[ self::PLUGIN_SLUG ] ) ? $menu_locations[ self::PLUGIN_SLUG ] : 0;
		if ( $hub_menu_id ) {
			/** @var WP_Post[] $menus */
			$menus = wp_get_nav_menu_items( $hub_menu_id );
			// classes in array
			_wp_menu_item_classes_by_context( $menus );

			// cleaning up properties
			foreach ( $menus as $menu ) {
				/** @var WP_Post $item */
				$item                        = new stdClass();
				$item->ID                    = isset( $menu->ID ) ? (int) $menu->ID : 0;
				$item->title                 = isset( $menu->title ) ? (string) $menu->title : '';
				$item->url                   = isset( $menu->url ) ? (string) $menu->url : '#';
				$item->target                = isset( $menu->target ) ? (string) $menu->target : '';
				$item->attr_title            = isset( $menu->attr_title ) ? (string) $menu->attr_title : '';
				$item->description           = isset( $menu->description ) ? (string) $menu->description : '';
				$item->classes               = isset( $menu->classes ) ? ( is_array( $menu->classes ) ? $menu->classes : array() ) : array();
				$item->xfn                   = isset( $menu->xfn ) ? (string) $menu->xfn : '';
				$item->menu_item_parent      = isset( $menu->menu_item_parent ) ? (int) $menu->menu_item_parent : 0;
				$item->current               = isset( $menu->current ) ? (bool) $menu->current : false;
				$item->current_item_ancestor = isset( $menu->current_item_ancestor ) ? (bool) $menu->current_item_ancestor : false;
				$item->current_item_parent   = isset( $menu->current_item_parent ) ? (bool) $menu->current_item_parent : false;
				$item->menu_order            = isset( $menu->menu_order ) ? (int) $menu->menu_order : 0;
				$item->has_children          = false;
				$item->children              = isset( $menu->children ) ? ( is_array( $menu->children ) ? $menu->children : array() ) : array();

				$items[] = $item;
			}

			unset( $menus );//perf

			$depth = self::get_extra_navigation_items_depth();

			/**
			 * Filters Extra navigation items, the menu items here still in flat mode, before converted to tree structure
			 * Use it if you want to quickly remove item or modify item, the tree structure will auto adjust later
			 *
			 * @param array $items the menu items
			 *
			 * @since 1.0.8
			 */
			$items = apply_filters( 'wpmudev_hub_extra_navigation_items', $items );

			$tree = self::get_extra_navigation_items_tree( $items, $depth );

			/**
			 * Filters Extra navigation items tree, this one when the items in flat mode, before converted to tree mode
			 *
			 * @param array $tree  the menu in tree structure
			 * @param array $items the menu in flat structure
			 * @param int   $depth depth of the expected tree
			 *
			 * @since 1.0.8
			 */
			$items = apply_filters( 'wpmudev_hub_get_extra_navigation_items_tree', $tree, $items, $depth );

			unset( $tree ); // perf

		}

		return $items;
	}

	public static function get_extra_navigation_items_depth() {
		/**
		 * Filters Extra navigation items depth, this one when the items in flat mode, before converted to tree mode
		 *
		 * @param array $depth the depth, 0 = unlimited
		 *
		 * @since 1.0.8
		 */
		return apply_filters( 'wpmudev_hub_extra_navigation_items_depth', 0 );
	}

	/**
	 * Build the tree menu structure from flat structure
	 *
	 * @param array $items
	 * @param int   $max_depth max depth of the expected tree, 0 = unlimited
	 *
	 * @return array
	 */
	public static function get_extra_navigation_items_tree( $items, $max_depth = 0 ) {
		// first thing first lets sort
		$sorted_items = array();
		foreach ( $items as $item ) {
			$sorted_items[ $item->menu_order ] = $item;
		}

		$items = $sorted_items;

		unset( $sorted_items ); // perf

		// building root and non root list
		$non_root_items = array();
		foreach ( $items as $key => $item ) {
			if ( $item->menu_item_parent ) {
				if ( ! isset( $non_root_items[ $item->menu_item_parent ] ) ) {
					$non_root_items[ $item->menu_item_parent ] = array();
				}
				$non_root_items[ $item->menu_item_parent ][ $item->menu_order ] = $item;
				unset( $items[ $key ] );
			}
		}

		// no non root items, doesn't need to traverse
		if ( ! $non_root_items ) {
			return array_values( $items );
		}

		// menu items is only root now
		foreach ( $items as &$item ) {
			$item = self::extra_navigation_item_walker( $item, $non_root_items, $max_depth );
		}

		// ensure array
		return array_values( $items );
	}

	/**
	 * Walk the menu item, to build the item's children
	 *
	 * @param object   $item           menu item
	 * @param object[] $non_root_items menu items that are non root
	 * @param int      $max_depth
	 * @param int      $depth
	 *
	 * @return object
	 */
	public static function extra_navigation_item_walker( $item, $non_root_items, $max_depth = 0, $depth = 1 ) {
		if ( ! isset( $non_root_items[ $item->ID ] ) ) {
			return $item;
		}

		// 0 = unlimited depth
		if ( 0 !== $max_depth ) {
			// has to be limited
			if ( $depth >= $max_depth ) {
				return $item;
			}
		}

		$non_root_items_list = $non_root_items[ $item->ID ];
		unset( $non_root_items[ $item->ID ] );

		$item->has_children = true;
		$item->classes[]    = 'menu-item-has-children';

		foreach ( $non_root_items_list as $order => $non_root_item ) {
			$children_item            = self::extra_navigation_item_walker( $non_root_item, $non_root_items, $max_depth, $depth + 1 );
			$item->children[ $order ] = $children_item;
		}

		$item->children = array_values( $item->children );

		return $item;
	}

	public static function is_multisite() {
		return is_multisite();
	}

	public static function get_admin_url( $path = '', $scheme = 'admin' ) {
		if ( self::is_multisite() ) {
			return network_admin_url( $path, $scheme );
		}

		return admin_url( $path, $scheme );
	}

	public static function get_network_main_site_id() {
		if ( ! self::is_multisite() ) {
			return false;
		}

		$network = get_network();
		if ( ! $network ) {
			return false;
		}

		return $network->site_id;
	}

	public static function maybe_switch_to_main_site() {
		$main_site_id = self::get_network_main_site_id();
		if ( ! $main_site_id ) {
			return false;
		}

		return switch_to_blog( $main_site_id );
	}

	public static function maybe_restore_current_blog_from_main_site() {
		if ( ! self::get_network_main_site_id() ) {
			return false;
		}

		return restore_current_blog();
	}

	public static function get_front_page_id() {
		return (int) get_site_option( 'wpmudev_hub_front_page_id', defined( 'WPMUDEV_HUB_PAGE_ID' ) ? WPMUDEV_HUB_PAGE_ID : '0' );
	}

	public static function update_front_page_id( $page_id ) {
		return update_site_option( 'wpmudev_hub_front_page_id', (int) $page_id );
	}

	public static function get_front_site_id() {
		if ( ! self::is_multisite() ) {
			return null;
		}

		$network_main_site_id = self::get_network_main_site_id();
		$site_id              = get_site_option( 'wpmudev_hub_front_site_id', $network_main_site_id );

		if ( $network_main_site_id !== $site_id ) {
			// check site existence
			$site = get_site( $site_id );
			if ( ! $site ) {
				delete_site_option( 'wpmudev_hub_front_site_id' );

				// also delete front page setting, as it could be different
				delete_site_option( 'wpmudev_hub_front_page_id' );
				// also delete front logo id, as it could be different
				self::update_customization( array( 'app_logo_attach_id', '' ) );

				/**
				 * Fires when selected subsite not found ( e.g. deleted )
				 * Only fired once, after this routine executed the selected subsite will revert to main network site id
				 *
				 * @param int $site_id              Site ID that being not found
				 * @param int $network_main_site_id Network main site id
				 *
				 * @since 2.1.0
				 */
				do_action( 'wpmudev_hub_front_site_id_not_found', $site_id, $network_main_site_id );

				return $network_main_site_id;
			}
		}

		return $site_id;
	}

	public static function update_front_site_id( $site_id ) {
		return update_site_option( 'wpmudev_hub_front_site_id', (int) $site_id );
	}

	public static function maybe_switch_to_front_site() {
		$front_site_id = self::get_front_site_id();
		if ( ! self::get_front_site_id() ) {
			return false;
		}

		return self::switch_to_site( $front_site_id );
	}

	public static function switch_to_site( $site_id ) {
		return switch_to_blog( $site_id );
	}

	public static function maybe_restore_current_blog_from_front_site() {
		if ( ! self::get_front_site_id() ) {
			return false;
		}

		return restore_current_blog();
	}

	public static function reset_data( $remove_auto_created_page = true ) {
		global $wpdb;

		// delete options
		delete_site_option( 'wpmudev_hub_front_site_id' );
		delete_site_option( 'wpmudev_hub_front_page_id' );
		delete_site_option( 'wpmudev_hub_manage_is_only_selected_admins' );
		delete_site_option( 'wpmudev_hub_manage_selected_admin_ids' );

		delete_site_option( 'wpmudev_hub_customization' );
		delete_site_transient( 'wpmudev_hub_account_data' );
		delete_site_transient( 'wpmudev_hub_available_languages' );
		WPMUDEV_HUB_Hosting_Reseller::get_instance()->reset();
		WPMUDEV_HUB_Domain_Reseller::get_instance()->reset();

		if ( ! $remove_auto_created_page ) {
			// use old auto_created_front_page if still exists, restore attempt
			$auto_front_page = get_site_option( 'wpmudev_hub_auto_front_page', array() );
			$auto_front_page = is_array( $auto_front_page ) ? $auto_front_page : array();

			if ( isset( $auto_front_page['page_id'] ) && $auto_front_page['page_id'] ) {
				$restore_page = true;
				if ( self::is_multisite() && isset( $auto_front_page['site_id'] ) && $auto_front_page['site_id'] ) {
					// multisite, lets validate and switch
					$auto_front_site = get_blog_details( $auto_front_page['site_id'] );
					if ( $auto_front_site ) {
						self::switch_to_site( $auto_front_page['site_id'] );
					} else {
						// sub-site removed, dont restore
						$restore_page = false;
					}
				}

				if ( $restore_page ) {
					// but page already gone
					if ( ! get_post( $auto_front_page['page_id'] ) ) {
						$restore_page = false;
					}
				}

				// lets remove and re-create
				if ( ! $restore_page ) {
					if ( self::is_multisite() ) {
						self::maybe_switch_to_main_site();
					}
					// delete auto created page
					self::maybe_remove_auto_created_front_page();
					self::maybe_auto_create_front_page();
				}

				if ( $restore_page ) {
					self::update_front_page_id( $auto_front_page['page_id'] );
					if ( self::is_multisite() ) {
						self::update_front_site_id( $auto_front_page['site_id'] );
					}
				}

				// ensure we revert site
				if ( self::is_multisite() ) {
					self::maybe_switch_to_main_site();
				}
			} else {
				// auto front page not exists, lets auto re-create
				self::maybe_auto_create_front_page();
			}
		} else {
			// delete auto created page
			self::maybe_remove_auto_created_front_page();
		}

		// reset navigation item
		$menu_locations = get_nav_menu_locations();
		unset( $menu_locations[ self::PLUGIN_SLUG ] );
		set_theme_mod( 'nav_menu_locations', $menu_locations );
	}

	public static function uninstall() {
		if ( (bool) self::get_customization( 'is_reset_on_uninstall' ) ) {
			self::reset_data();
		}
	}

	public static function get_hub_site_id() {
		$hub_site_id = 0;
		if ( class_exists( 'WPMUDEV_Dashboard' ) && is_object( WPMUDEV_Dashboard::$api ) && method_exists( WPMUDEV_Dashboard::$api, 'get_site_id' ) ) {
			$hub_site_id = WPMUDEV_Dashboard::$api->get_site_id();
		}

		/**
		 * Filters Hub Site ID
		 *
		 * @param string $hub_site_id Hub site id that connected through Dashboard Plugin
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'wpmudev_hub_site_id', $hub_site_id );
	}

	public static function get_logo_sizes_config() {
		/**
		 * Filters Logo sizes config
		 *
		 * @param array $logo_sizes Logo sizes config ( refer to add_image_size )
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'wpmudev_hub_app_logo_sizes_config', self::$logo_sizes );
	}

	public static function add_custom_logo_sizes() {
		foreach ( self::get_logo_sizes_config() as $key => $config ) {
			add_image_size( self::LOGO_SIZE_PREFIX . $key, $config['width'], $config['height'], $config['crop'] );
		}
	}

	public static function remove_custom_logo_sizes() {
		foreach ( self::get_logo_sizes_config() as $key => $config ) {
			remove_image_size( self::LOGO_SIZE_PREFIX . $key );
		}
	}

	public static function get_wp_version() {
		// Include an unmodified $wp_version.
		require ABSPATH . WPINC . '/version.php';

		return isset( $wp_version ) ? $wp_version : '0.0.0';
	}

	/**
	 * @param string|array $slug
	 *
	 * @return array|string
	 */
	public static function get_rest_url( $slug = null ) {
		$rest_api_urls = array(
			self::REST_API_SLUG_BASE                               => rest_url( self::PLUGIN_SLUG . '/v1/' ),
			self::REST_API_SLUG_SETTINGS                           => rest_url( self::PLUGIN_SLUG . '/v1/settings' ),
			self::REST_API_SLUG_CLIENT_PORTAL_SETTINGS             => rest_url( self::PLUGIN_SLUG . '/v1/client-portal-settings' ),
			self::REST_API_SLUG_RESELLER_HOSTING_SETTINGS          => rest_url( self::PLUGIN_SLUG . '/v1/reseller/hosting-settings' ),
			self::REST_API_SLUG_PUBLIC_RESELLER_HOSTING_SETTINGS   => rest_url( self::PLUGIN_SLUG . '/v1/reseller/public/hosting-settings' ),
			self::REST_API_SLUG_RESELLER_HOSTING_SETTINGS_PRODUCTS => rest_url( self::PLUGIN_SLUG . '/v1/reseller/hosting-settings/products' ),
			self::REST_API_SLUG_ADMINS                             => rest_url( self::PLUGIN_SLUG . '/v1/admins' ),
			self::REST_API_SLUG_PAGES                              => rest_url( self::PLUGIN_SLUG . '/v1/pages' ),
			self::REST_API_SLUG_SITES                              => rest_url( self::PLUGIN_SLUG . '/v1/sites' ),
			self::REST_API_SLUG_RESELLER_HOSTING_PRODUCTS          => rest_url( self::PLUGIN_SLUG . '/v1/reseller/hosting-products' ),
			self::REST_API_SLUG_RESELLER_DOMAINS_PLANS             => rest_url( self::PLUGIN_SLUG . '/v1/reseller/domain-plans' ),
			self::REST_API_SLUG_RESELLER_DOMAINS_SETTINGS          => rest_url( self::PLUGIN_SLUG . '/v1/reseller/domain-settings' ),
			self::REST_API_SLUG_PUBLIC_RESELLER_DOMAIN_SETTINGS    => rest_url( self::PLUGIN_SLUG . '/v1/reseller/public/domain-settings' ),
			self::REST_API_SLUG_PUBLIC_RESELLER_DOMAIN_LOOKUP      => rest_url( self::PLUGIN_SLUG . '/v1/reseller/public/domain-lookup' ),
		);

		if ( empty( $slug ) ) {
			return $rest_api_urls;
		}

		if ( is_array( $slug ) ) {
			$rest_api_urls = array();
			foreach ( $slug as $item ) {
				if ( empty( $item ) ) {
					continue;
				}
				$rest_api_urls[ $item ] = self::get_rest_url( $item );
			}

			return $rest_api_urls;
		}

		return isset( $rest_api_urls[ $slug ] ) ? $rest_api_urls[ $slug ] : $rest_api_urls[ self::REST_API_SLUG_BASE ];
	}

	/**
	 * Returns Jed-formatted localization data.
	 * Based upon Gutenberg's implementation
	 *
	 * @see https://github.com/WordPress/gutenberg/blob/955f7b7e8682de100719c6affcfd304dd7cf23ac/lib/i18n.php#L21
	 *
	 *
	 * @param $domain
	 *
	 * @return array|array[]
	 */
	public static function get_locale_data( $domain ) {
		$translations = get_translations_for_domain( $domain );

		$locale = array(
			'translation-revision-date' => $translations->headers['PO-Revision-Date'] ?? '',
			'domain'                    => 'messages',
			'generator'                 => $translations->headers['X-Generator'] ?? '',
			'locale_data'               => array(
				'messages' => array(
					'' => array(
						'domain'       => 'messages',
						'plural-forms' => $translations->headers['Plural-Forms'] ?? 'nplurals=2; plural=n > 1;',
					),
				),

			),
		);

		if ( isset( $translations->headers['Language'] ) && $translations->headers['Language'] ) {
			$locale['locale_data']['messages']['']['lang'] = $translations->headers['Language'];
		}

		foreach ( $translations->entries as $msgid => $entry ) {
			$locale['locale_data']['messages'][ $msgid ] = $entry->translations;
		}

		return $locale;
	}

	public static function get_plugin_basename() {
		return plugin_basename( WPMUDEV_HUB_PLUGIN_FILE );
	}
}

WPMUDEV_HUB_Plugin::get_instance();