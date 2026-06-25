<?php

class WPMUDEV_HUB_Plugin_Front {
	private static $instance;

	const SHORTCODE_ATT_MODULE_HOSTING_RESELLER_PRICING_TABLE = 'hosting-reseller-pricing-table';
	const SHORTCODE_ATT_MODULE_DOMAIN_RESELLER_WIDGET         = 'domain-reseller-widget';

	const SHORTCODE_ATT_DEFAULT_MODULE = self::SHORTCODE_ATT_MODULE_HOSTING_RESELLER_PRICING_TABLE;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_filter( 'page_template', array( $this, 'hub_page_template' ) );
		add_filter( 'template_redirect', array( $this, 'client_page_dynamic_loader' ) );

		add_action( 'init', array( $this, 'register_shared_assets' ) );
		add_action( 'init', array( $this, 'register_blocks_assets' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );
		add_action( 'init', array( $this, 'register_hosting_pricing_block' ) );
		add_action( 'init', array( $this, 'register_domain_widget_block' ) );
		add_filter( 'should_load_separate_core_block_assets', array( $this, 'set_core_block_assets_load_behavior' ) );

		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	public function hub_page_template( $page_template ) {
		global $post;

		// multisite
		if ( WPMUDEV_HUB_Plugin::is_multisite() ) {
			if ( (int) WPMUDEV_HUB_Plugin::get_front_site_id() !== (int) get_current_blog_id() ) {
				return $page_template;
			}
		}
		if ( ! WPMUDEV_HUB_Plugin::get_front_page_id() ) {
			return $page_template;
		}

		// HUB2 start here
		if ( (int) WPMUDEV_HUB_Plugin::get_front_page_id() === (int) $post->ID ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				// not our global, but compatibility global. w3-total-cache known to use this. https://wordpress.org/support/topic/disable-caching-for-a-specific-page/
				define( 'DONOTCACHEPAGE', 1 );// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
			}

			/**
			 * Oxygen compat, and other plugins that use similar method
			 *
			 * @since 1.0.4
			 */
			remove_all_filters( 'template_include' );

			return plugin_dir_path( __FILE__ ) . 'template.php';
		}

		return $page_template;
	}

	public static function get_assets() {
		// check availability
		if ( ! WPMUDEV_HUB_Plugin::is_hub_fe_ready() ) {
			if ( WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user() ) {
				wp_die(
					sprintf(
					/* translators: %s: Settings URL in wp-admin. */
						esc_html__( 'Something went wrong on Whitelabel Hub configuration, please review %1$shere%2$s.', 'thc' ),
						'<a href="' . esc_url( add_query_arg( array( 'page' => WPMUDEV_HUB_Plugin::PLUGIN_SLUG ), WPMUDEV_HUB_Plugin::get_admin_url( 'admin.php' ) ) ) . '">',
						'<a>'
					)
				);
			}
			wp_die( esc_html__( 'Hub not available at the moment please comeback later.', 'thc' ) );
		}
		$manifest = WPMUDEV_HUB_Plugin::get_assets_manifest();
		if ( is_wp_error( $manifest ) ) {
			if ( WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user() ) {
				wp_die( esc_html( $manifest->get_error_message() ), esc_html( $manifest->get_error_code() ) );
			}
			wp_die( esc_html__( 'Hub manifest not available at the moment. Please come back later.', 'thc' ) );
		}

		$assets = array(
			'css' => array(),
			'js'  => array(),
		);

		// Main assets
		if ( isset( $manifest['main.css'] ) ) {
			$assets['css']['wpmudev-hub-main'] = untrailingslashit( WPMUDEV_HUB_Plugin::get_base_static_server() ) . $manifest['main.css'];
		}

		// Order matters
		if ( isset( $manifest['main.js'] ) ) {
			$assets['js']['wpmudev-hub-main'] = untrailingslashit( WPMUDEV_HUB_Plugin::get_base_static_server() ) . $manifest['main.js'];
		}
		if ( isset( $manifest['runtime~main.js'] ) ) {
			$assets['js']['wpmudev-hub-runtime'] = untrailingslashit( WPMUDEV_HUB_Plugin::get_base_static_server() ) . $manifest['runtime~main.js'];
		}
		if ( isset( $manifest['vendors~main.js'] ) ) {
			$assets['js']['vendors~main.js'] = WPMUDEV_HUB_Plugin::get_base_static_server() . $manifest['vendors~main.js'];
		}

		return $assets;
	}

	public static function get_embed_url( $switch_blog = false ) {
		if ( ! WPMUDEV_HUB_Plugin::get_front_page_id() ) {
			return '';
		}

		if ( is_multisite() && $switch_blog ) {
			WPMUDEV_HUB_Plugin::maybe_switch_to_front_site();
		}

		if ( ! get_post( WPMUDEV_HUB_Plugin::get_front_page_id() ) ) {
			return '';
		}

		// this function always assume page exists, check get_posts first
		$link = get_page_link( WPMUDEV_HUB_Plugin::get_front_page_id() );

		if ( is_multisite() && $switch_blog ) {
			WPMUDEV_HUB_Plugin::maybe_restore_current_blog_from_front_site();
		}

		return $link;
	}

	public static function get_site_name() {
		if ( is_multisite() ) {
			// show blog name for selected front site id
			// https://incsub.atlassian.net/browse/HUB-221
			$site_name = wp_specialchars_decode( get_blog_option( WPMUDEV_HUB_Plugin::get_front_site_id(), 'blogname' ), ENT_QUOTES );
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		return $site_name;
	}

	public function client_page_dynamic_loader() {
		// no-nonce for this. we process url param ( comes from emails / hub )
		// url/form data is not stored nor displayed to end user
		// it's used to build the redirect ( using wp_safe_redirect )
		wp_verify_nonce( null );
		$is_hub_client_page = isset( $_REQUEST['_hub_client_page'] ) ? filter_var( $_REQUEST['_hub_client_page'], FILTER_VALIDATE_BOOLEAN ) : false;
		// not client page, do nothing
		if ( ! $is_hub_client_page ) {
			return false;
		}

		// check if client page setup
		if ( ! WPMUDEV_HUB_Plugin::get_front_page_id() ) {
			return false;
		}

		// generate the url
		$base_url = self::get_embed_url( true );
		if ( ! $base_url ) {
			WPMUDEV_HUB_Plugin::maybe_switch_to_front_site();

			return false;
		}

		$path = isset( $_REQUEST['_path'] ) ? (string) $_REQUEST['_path'] : '';

		$query_args = $_REQUEST;
		unset( $query_args['_hub_client_page'] );
		unset( $query_args['_path'] );

		$redirect = add_query_arg( $query_args, $base_url );

		// has custom path
		if ( ! empty( $path ) ) {
			// sanitize path
			$path = ltrim( $path, '/' );

			if ( ! empty( $path ) ) {
				// add hash for FE
				$redirect = trailingslashit( $redirect . '#' ) . $path;
			}
		}

		WPMUDEV_HUB_Plugin::maybe_switch_to_front_site();
		wp_safe_redirect( $redirect );
		exit();
	}

	public function set_core_block_assets_load_behavior( $default_load ) {
		/**
		 * By default for non-wp-block theme, `wp_should_load_separate_core_block_assets` would be false.
		 * False: means block styles and scripts will be loaded on every front end pages, regardless of whether the block is rendered in a page
		 * True: means loads block assets only when they are rendered
		 *
		 * @see https://core.trac.wordpress.org/ticket/55485
		 * @see wp_should_load_separate_core_block_assets()
		 *
		 * For optimization sake, we override the value here to be `true`, that way block assets will only render when they are rendered
		 * If any issue encountered due to this, override it using `wpmudev_hub_should_load_separate_core_block_assets`
		 * Or reuse `should_load_separate_core_block_assets` with higher priority/order
		 */

		// default THC behavior
		$load_separate_core_block_assets = true;

		/**
		 * Filter block assets load behavior
		 *
		 * @param bool $load_separate_core_block_assets Whether to load block assets on-render only
		 * @param bool $default_load                    Default value from wp_should_load_separate_core_block_assets
		 *
		 * @since 2.1.0
		 */
		return apply_filters( 'wpmudev_hub_should_load_separate_core_block_assets', $load_separate_core_block_assets, $default_load );
	}

	public function register_shared_assets() {
		// Register shared styles ( used in front-embed and admin )
		wp_register_style(
			'thc-shared-style',
			plugins_url( '/build/shared/index.css', WPMUDEV_HUB_PLUGIN_FILE ),
			array(),
			WPMUDEV_HUB_Plugin::VERSION
		);
	}

	public function register_blocks_assets() {
		// only registers here, not enqueue it, we will enqueue later only when needed
		wp_register_script(
			'thc-blocks-script',
			plugins_url( '/build/blocks/index.js', WPMUDEV_HUB_PLUGIN_FILE ),
			array( 'wp-blocks', 'wp-i18n' ),
			WPMUDEV_HUB_Plugin::VERSION,
			true
		);

		// Register editor style src/editor.css
		wp_register_style(
			'thc-blocks-style',
			plugins_url( '/build/blocks/index.css', WPMUDEV_HUB_PLUGIN_FILE ),
			array( 'thc-shared-style' ),
			WPMUDEV_HUB_Plugin::VERSION
		);

		$this->register_block_asset_data( 'thc-blocks-script' );

		/**
		 * @see WP_Scripts::print_translations()
		 */
		$json_translations         = wp_json_encode( WPMUDEV_HUB_Plugin::get_locale_data( 'thc' ) );
		$translation_inline_script = <<<JS
			( function( domain, translations ) {
				var localeData = translations.locale_data[ domain ] || translations.locale_data.messages
				localeData[""].domain = domain
				wp.i18n.setLocaleData( localeData, domain )
			} )( "thc", {$json_translations} )
		JS;
		wp_add_inline_script( 'thc-blocks-script', $translation_inline_script, 'before' );

		/**
		 * Fires after hosting pricing block assets registered
		 *
		 * @since      2.0.0
		 * @deprecated 2.2.2 Use the {@see 'wpmudev_hub_after_register_blocks_assets'} action instead.
		 */
		do_action_deprecated( 'wpmudev_hub_after_register_hosting_pricing_block_assets', array(), '2.2.2', 'wpmudev_hub_after_register_blocks_assets' );

		/**
		 * Fires after domain widget block assets registered
		 *
		 * @since      2.0.0
		 * @deprecated 2.2.2 Use the {@see 'wpmudev_hub_after_register_blocks_assets'} action instead.
		 */
		do_action_deprecated( 'wpmudev_hub_after_register_domain_widget_block_assets', array(), '2.2.2', 'wpmudev_hub_after_register_blocks_assets' );

		/**
		 * Fires after blocks assets registered
		 *
		 * @since 2.2.0
		 */
		do_action( 'wpmudev_hub_after_register_blocks_assets' );
	}

	protected function register_block_asset_data( $handle ) {
		wp_localize_script(
			$handle,
			'hub_whitelabel_settings',
			array(
				'wpmudev_hub_api_server'   => untrailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ),
				'wpmudev_hub_site_id'      => WPMUDEV_HUB_Plugin::get_hub_site_id(),
				'hub_client_page_base_url' => self::get_client_page_base_url(),
				'api_url'                  => WPMUDEV_HUB_Plugin::get_rest_url( WPMUDEV_HUB_Plugin::REST_API_SLUG_BASE ), // base
				'api_urls'                 => WPMUDEV_HUB_Plugin::get_rest_url(
					array(
						WPMUDEV_HUB_Plugin::REST_API_SLUG_BASE,
						WPMUDEV_HUB_Plugin::REST_API_SLUG_PUBLIC_RESELLER_HOSTING_SETTINGS,
						WPMUDEV_HUB_Plugin::REST_API_SLUG_PUBLIC_RESELLER_DOMAIN_SETTINGS,
						WPMUDEV_HUB_Plugin::REST_API_SLUG_PUBLIC_RESELLER_DOMAIN_LOOKUP,
					)
				),
			)
		);
	}

	/**
	 * @param $block_categories
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public function register_block_category( $block_categories ) {
		// something is wrong
		if ( ! $block_categories || ! is_array( $block_categories ) ) {
			return $block_categories;
		}

		/**
		 * Filters block category detail for THC
		 *
		 * @param array $block_category Block category
		 *
		 * @since 2.0.0
		 */
		$wpmudev_hub_block_category = apply_filters(
			'wpmudev_hub_block_category',
			array(
				'slug'  => 'wpmudev-hub',
				'title' => esc_html__( 'The Hub Client', 'thc' ),
				'icon'  => null,
			)
		);

		/**
		 * Filters block category for THC whether as first or not
		 *
		 * @param bool $is_first
		 *
		 * @since 2.0.0
		 */
		$is_first = apply_filters( 'wpmudev_hub_block_category_is_first', true );
		if ( $is_first ) {
			array_unshift( $block_categories, $wpmudev_hub_block_category );
		} else {
			$block_categories[] = $wpmudev_hub_block_category;
		}

		/**
		 * Filters block categories after THC modification
		 *
		 * @param array $block_categories Block categories
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'wpmudev_hub_block_categories', $block_categories );
	}

	public function register_hosting_pricing_block() {
		/**
		 * Filters Category for hosting pricing block
		 *
		 * @param string $category_id the category id
		 *
		 * @since 2.0.0
		 */
		$block_category = apply_filters( 'wpmudev_hub_hosting_pricing_block_category', 'wpmudev-hub' );

		$args = array(
			'title'           => __( 'Hosting Reseller', 'thc' ),
			'description'     => __( 'Resell WPMU DEV hosting to your clients using this Hosting Reseller pricing table block.', 'thc' ),
			'icon'            => 'slides',
			'category'        => $block_category,
			'render_callback' => function ( $attributes ) {
				return '<div id="thc-reseller-hosting-block" class="thc-reseller-hosting-block align' . esc_attr( $attributes['align'] ) . '"></div>';
			},
			'supports'        => array(
				'align' => array( 'wide', 'full' ),
			),
			'attributes'      => array(
				'preview' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'align'   => array(
					'type'    => 'string',
					'default' => 'wide',
				),
			),
			'example'         => array(
				'attributes' => array(
					'preview' => true,
				),
			),
		);

		/**
		 * Forward Compatibility
		 * script and style is deprecated since 6.1
		 *
		 * @see https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/class-wp-block-type.php#L245
		 */
		if ( version_compare( WPMUDEV_HUB_Plugin::get_wp_version(), '6.1', 'ge' ) ) {
			// both editor and viewer
			$args['script_handles'] = array( 'thc-blocks-script' );
			$args['style_handles']  = array( 'thc-blocks-style' );
		} else {
			// both editor and viewer
			$args['script'] = 'thc-blocks-script';
			$args['style']  = 'thc-blocks-style';
		}

		/**
		 * Filter args for register hosting pricing block
		 *
		 * @param array $args current args
		 *
		 * @since 2.0.0
		 */
		$args = apply_filters( 'wpmudev_hub_hosting_pricing_block_args', $args );

		register_block_type( 'hub-white-label/hosting-pricing', $args );
	}


	/**
	 * Register domain reseller block
	 *
	 * @since 2.2.0
	 */
	public function register_domain_widget_block() {
		/**
		 * Filters Category for domain widget
		 *
		 * @param string $category_id the category id
		 *
		 * @since 2.2.0
		 */
		$block_category = apply_filters( 'wpmudev_hub_domain_widget_block_category', 'wpmudev-hub' );

		$args = array(
			'title'           => __( 'Domains Reseller', 'thc' ),
			'description'     => __( 'Resell WPMU DEV domains to your clients using this Domain Reseller block.', 'thc' ),
			'icon'            => 'slides',
			'category'        => $block_category,
			'render_callback' => function ( $attributes ) {
				return '<div id="thc-reseller-domain-block" class="thc-reseller-domain-block align' . esc_attr( $attributes['align'] ) . '"></div>';
			},
			'supports'        => array(
				'align' => array( 'wide', 'full' ),
			),
			'attributes'      => array(
				'preview' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'align'   => array(
					'type'    => 'string',
					'default' => 'wide',
				),
			),
			'example'         => array(
				'attributes' => array(
					'preview' => true,
				),
			),
		);

		/**
		 * Forward Compatibility
		 * script and style is deprecated since 6.1
		 *
		 * @see https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/class-wp-block-type.php#L245
		 */
		if ( version_compare( WPMUDEV_HUB_Plugin::get_wp_version(), '6.1', 'ge' ) ) {
			// both editor and viewer
			$args['script_handles'] = array( 'thc-blocks-script' );
			$args['style_handles']  = array( 'thc-blocks-style' );
		} else {
			// both editor and viewer
			$args['script'] = 'thc-blocks-script';
			$args['style']  = 'thc-blocks-style';
		}

		/**
		 * Filter args for register domain widget
		 *
		 * @param array $args current args
		 *
		 * @since 2.2.0
		 */
		$args = apply_filters( 'wpmudev_hub_domain_widget_block_args', $args );

		register_block_type( 'hub-white-label/domain-widget', $args );
	}

	public function register_shortcodes() {
		add_shortcode( 'thc', array( $this, 'render_shortcode' ) );
	}

	public function render_shortcode( $atts, $content, $shortcode_tag ) {
		$atts = shortcode_atts(
			array(
				'module' => self::SHORTCODE_ATT_DEFAULT_MODULE,
			),
			$atts,
			$shortcode_tag
		);

		if ( self::SHORTCODE_ATT_MODULE_HOSTING_RESELLER_PRICING_TABLE === $atts['module'] ) {
			// inactive check, we can do here and return $content right-away, but it will have page-caching problem
			// instead since we are using render_block, which will use REST API later, we can avoid page-caching problem. Though, loader will be required
			if ( ! function_exists( 'render_block' ) ) {
				return $content;
			}

			// use render_block
			return render_block( array( 'blockName' => 'hub-white-label/hosting-pricing' ) );
		} elseif ( self::SHORTCODE_ATT_MODULE_DOMAIN_RESELLER_WIDGET === $atts['module'] ) {
			if ( ! function_exists( 'render_block' ) ) {
				return $content;
			}

			// use render_block
			return render_block( array( 'blockName' => 'hub-white-label/domain-widget' ) );
		}

		// other than that, do nothing
		return $content;
	}

	/**
	 * Get client page base url
	 * Return empty string if front page not set up
	 *
	 * @return string
	 * @since 2.2.0
	 */
	public static function get_client_page_base_url() {
		$base_url = '';
		if ( WPMUDEV_HUB_Plugin::get_front_page_id() ) {
			WPMUDEV_HUB_Plugin::maybe_switch_to_front_site();
			$base_url = site_url();
			$base_url = add_query_arg(
				array(
					'_hub_client_page' => 1,
				),
				$base_url
			);
			WPMUDEV_HUB_Plugin::maybe_restore_current_blog_from_front_site();
		}

		return $base_url;
	}
}

WPMUDEV_HUB_Plugin_Front::get_instance();