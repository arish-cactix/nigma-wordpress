<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class WPMUDEV_HUB_Plugin_Admin_Module_Abstract {

	protected $menu_slug;
	protected $menu_title;
	protected $page_title;
	protected $page_container = 'hub-whitelabel-admin';

	protected $sui_classes  = 'sui-wrap sui-theme--light';
	protected $is_main_menu = false;

	protected $requests = array();

	/**
	 * @var WP_Error[]
	 */
	protected $errors = array();

	/**
	 * @var string[]
	 */
	protected $successes = array();

	protected $dash_status
		= array(
			'is_installed'       => false,
			'is_active'          => false,
			'notice_title'       => '',
			'notice_message'     => '',
			'notice_action_text' => '',
			'notice_action_url'  => '',
		);

	protected $has_missing_requirement = true;

	const HOOK_GLOBAL_PLUGIN_ACTION_LINKS = 'plugin_action_links';
	const HOOK_GLOBAL_ADMIN_PRINT_STYLES  = 'admin_print_styles';

	private static $attached_global_hooks = array();

	protected $messages_kses
		= array(
			'a'      => array(
				'href'   => true,
				'rel'    => true,
				'rev'    => true,
				'name'   => true,
				'target' => true,
			),
			'strong' => array(),
			'li'     => array(
				'align' => true,
				'value' => true,
			),
			'ul'     => array(
				'type' => true,
			),
			'ol'     => array(
				'start'    => true,
				'type'     => true,
				'reversed' => true,
			),
		);

	protected $page_hook_suffix = false;

	public function __construct() {
		add_action( 'init', array( $this, 'setup' ) );
	}


	public function setup() {
		if ( ! is_admin() ) {
			return;
		}

		$this->page_title = static::get_page_title();
		$this->menu_title = static::get_menu_title();

		$this->maybe_attach_global_hooks();

		if ( ! WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user() ) {
			return;
		}
		add_action( WPMUDEV_HUB_Plugin_Admin_Module_Admin::get_admin_menu_hook(), array( $this, 'on_admin_menu' ) );
	}

	public function setup_page_hooks() {
		add_action( 'admin_head-' . $this->page_hook_suffix, array( $this, 'on_head' ) );
		add_action( 'load-' . $this->page_hook_suffix, array( $this, 'on_load' ) );
		add_action( 'load-' . $this->page_hook_suffix, array( $this, 'register_scripts' ) );
		add_action( 'load-' . $this->page_hook_suffix, array( $this, 'load_scripts_locale' ) );
		add_action( 'admin_print_styles-' . $this->page_hook_suffix, array( $this, 'add_styles' ) );
		add_action( 'admin_print_footer_scripts-' . $this->page_hook_suffix, array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Attach global hooks
	 * this is hooks that only happen in wp_admin and attached only once
	 * this class is extended + instantiated multiple times
	 * so we need to track that global hooks not getting repeated
	 *
	 * @return void
	 */
	public function maybe_attach_global_hooks() {
		if ( ! self::is_global_hook_added( self::HOOK_GLOBAL_PLUGIN_ACTION_LINKS ) ) {
			add_filter( 'plugin_action_links_' . WPMUDEV_HUB_Plugin::get_plugin_basename(), array( $this, 'plugin_action_links' ) );
			self::mark_global_filter_attached( self::HOOK_GLOBAL_PLUGIN_ACTION_LINKS );
		}
		if ( ! self::is_global_hook_added( self::HOOK_GLOBAL_ADMIN_PRINT_STYLES ) ) {
			add_filter( 'admin_print_styles', array( $this, 'print_styles' ) );
			self::mark_global_filter_attached( self::HOOK_GLOBAL_ADMIN_PRINT_STYLES );
		}
	}

	private static function is_global_hook_added( $filter ) {
		return in_array( $filter, self::$attached_global_hooks, true );
	}

	private static function mark_global_filter_attached( $filter ) {
		self::$attached_global_hooks[] = $filter;

		return self::$attached_global_hooks;
	}

	public function plugin_action_links( $links ) {
		if ( ! is_array( $links ) ) {
			return $links;
		}

		array_unshift(
			$links,
			sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url(
					trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() )
					. 'docs/wpmu-dev-plugins/the-hub-client-wpmu-dev/?utm_source=the-hub-client&utm_medium=plugin&utm_campaign=the-hub-client_pluginlist_docs'
				),
				esc_html( __( 'Docs', 'thc' ) )
			)
		);

		// show settings only if allowed
		if ( WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user() ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( add_query_arg( array( 'page' => WPMUDEV_HUB_Plugin::PLUGIN_SLUG ), WPMUDEV_HUB_Plugin::get_admin_url( 'admin.php' ) ) ),
					esc_html( __( 'Settings', 'thc' ) )
				)
			);
		}

		return $links;
	}

	public function print_styles() {
		// only when user actually has access
		if ( ! WPMUDEV_HUB_Permissions::get_instance()->is_allowed_user() ) {
			return;
		}
		// @see: https://incsub.atlassian.net/browse/THC-231
		// reseller always 3th child -- hopefully
		// TODO: improve style selector and syntax
		?>
		<?php // phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed ?>
		<style>
            #adminmenu #toplevel_page_wpmudev-hub li span.thc-menu-tag {
                background-color: #a1a1a1;
                border-radius: 12px;
                color: #ffffff;
                font-size: 10px;
                font-style: normal;
                font-weight: 700;
                height: 16px;
                letter-spacing: -0.1px;
                line-height: 12px;
                padding: 2px 8px;
                text-align: center;
                width: 38px;
            }

            #adminmenu #toplevel_page_wpmudev-hub li span.thc-menu-tag.thc-menu-tag--green {
                background-color: #138639;
            }

			#adminmenu #toplevel_page_wpmudev-hub li span.thc-menu-tag.thc-menu-tag--blue {
				background-color: #0059ff;
			}

			#adminmenu #toplevel_page_wpmudev-hub li span.thc-menu-tag.thc-menu-tag--navy {
				background-color: #0045c4;
			}

			#adminmenu #toplevel_page_wpmudev-hub li span.thc-menu-tag.thc-menu-tag--yellow {
				background-color: #ffcc20;
				color: #1a1a1a;
			}

			#adminmenu #toplevel_page_wpmudev-hub li span.thc-menu-tag.thc-menu-tag--red {
				background-color: #f45c59;
			}

			#adminmenu #toplevel_page_wpmudev-hub li span.thc-menu-tag.thc-menu-tag--black {
				background-color: #1a1a1a;
			}

			#adminmenu #toplevel_page_wpmudev-hub li span.thc-menu-tag.thc-menu-tag--white {
				background-color: #ffffff;
				color: #1a1a1a;
			}
		</style>
		<?php // phpcs:enable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed ?>
		<?php
	}

	abstract protected function menu_cap();

	public function on_head() {
		add_filter( 'admin_body_class', array( $this, 'body_classes' ) );
	}

	public function body_classes( $classes ) {
		$classes .= ' dwl-ui ';

		return $classes;
	}

	/**
	 * Menu Title
	 * Meant to be overridden in subclass
	 *
	 * @return string
	 */
	public static function get_menu_title() {
		return '';
	}

	/**
	 * Page title
	 * By default same as menu title
	 *
	 * @return string
	 */
	public static function get_page_title() {
		return self::get_menu_title();
	}

	public function on_admin_menu() {
		// hub client icon svg
		$menu_icon
			= 'PHN2ZyB3aWR0aD0nMTgnIGhlaWdodD0nMjAnIHZpZXdCb3g9JzAgMCAxOCAyMCcgZmlsbD0nbm9uZScgeG1sbnM9J2h0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnJz48cGF0aCBkPSdNMCAzLjg4ODg5QzAgMS43NDExMSAxLjY1OTE4IDAgMy43MDU4OCAwQzUuNzUyNTggMCA3LjQxMTc2IDEuNzQxMTEgNy40MTE3NiAzLjg4ODg5VjYuMTExMTFDNy40MTE3NiA2LjcyNDc2IDYuOTM3NzEgNy4yMjIyMiA2LjM1Mjk0IDcuMjIyMjJDNS43NjgxNyA3LjIyMjIyIDUuMjk0MTIgNi43MjQ3NiA1LjI5NDEyIDYuMTExMTFWMy44ODg4OUM1LjI5NDEyIDIuOTY4NDEgNC41ODMwNCAyLjIyMjIyIDMuNzA1ODggMi4yMjIyMkMyLjgyODcyIDIuMjIyMjIgMi4xMTc2NSAyLjk2ODQxIDIuMTE3NjUgMy44ODg4OVYxNi4xMTExQzIuMTE3NjUgMTcuMDMxNiAyLjgyODcyIDE3Ljc3NzggMy43MDU4OCAxNy43Nzc4QzQuNTgzMDQgMTcuNzc3OCA1LjI5NDEyIDE3LjAzMTYgNS4yOTQxMiAxNi4xMTExVjE0LjQ0NDRDNS4yOTQxMiAxMy44MzA4IDUuNzY4MTcgMTMuMzMzMyA2LjM1Mjk0IDEzLjMzMzNDNi45Mzc3MSAxMy4zMzMzIDcuNDExNzYgMTMuODMwOCA3LjQxMTc2IDE0LjQ0NDRWMTYuMTExMUM3LjQxMTc2IDE4LjI1ODkgNS43NTI1OCAyMCAzLjcwNTg4IDIwQzEuNjU5MTggMjAgMCAxOC4yNTg5IDAgMTYuMTExMVYzLjg4ODg5WicgZmlsbD0nd2hpdGUnLz48cGF0aCBkPSdNMTggMy44ODg4OUMxOCAxLjc0MTExIDE2LjM0MDggMCAxNC4yOTQxIDBDMTIuMjQ3NCAwIDEwLjU4ODIgMS43NDExMSAxMC41ODgyIDMuODg4ODlWNi4xMTExMUMxMC41ODgyIDYuNzI0NzYgMTEuMDYyMyA3LjIyMjIyIDExLjY0NzEgNy4yMjIyMkMxMi4yMzE4IDcuMjIyMjIgMTIuNzA1OSA2LjcyNDc2IDEyLjcwNTkgNi4xMTExMVYzLjg4ODg5QzEyLjcwNTkgMi45Njg0MSAxMy40MTcgMi4yMjIyMiAxNC4yOTQxIDIuMjIyMjJDMTUuMTcxMyAyLjIyMjIyIDE1Ljg4MjQgMi45Njg0MSAxNS44ODI0IDMuODg4ODlWMTYuMTExMUMxNS44ODI0IDE3LjAzMTYgMTUuMTcxMyAxNy43Nzc4IDE0LjI5NDEgMTcuNzc3OEMxMy40MTcgMTcuNzc3OCAxMi43MDU5IDE3LjAzMTYgMTIuNzA1OSAxNi4xMTExVjE0LjQ0NDRDMTIuNzA1OSAxMS4zNzYyIDEwLjMzNTYgOC44ODg4OSA3LjQxMTc2IDguODg4ODlINS4yOTQxMkM0LjcwOTM1IDguODg4ODkgNC4yMzUyOSA5LjM4NjM1IDQuMjM1MjkgMTBDNC4yMzUyOSAxMC42MTM3IDQuNzA5MzUgMTEuMTExMSA1LjI5NDEyIDExLjExMTFINy40MTE3NkM5LjE2NjA4IDExLjExMTEgMTAuNTg4MiAxMi42MDM1IDEwLjU4ODIgMTQuNDQ0NFYxNi4xMTExQzEwLjU4ODIgMTguMjU4OSAxMi4yNDc0IDIwIDE0LjI5NDEgMjBDMTYuMzQwOCAyMCAxOCAxOC4yNTg5IDE4IDE2LjExMTFWMy44ODg4OVonIGZpbGw9J3doaXRlJy8+PC9zdmc+Cg==';

		if ( $this->is_main_menu ) {
			$this->page_hook_suffix = add_menu_page(
				$this->page_title,
				$this->menu_title,
				$this->menu_cap(),
				$this->menu_slug,
				array( $this, 'page' ),
				'data:image/svg+xml;base64,' . $menu_icon
			);

			// replace main menu title with "Client Portal"
			$this->page_hook_suffix = add_submenu_page(
				$this->menu_slug,
				esc_html__( 'Client Portal', 'thc' ),
				esc_html__( 'Client Portal', 'thc' ),
				WPMUDEV_HUB_Plugin::get_manage_plugin_cap(),
				$this->menu_slug
			);
		} else {
			$this->page_hook_suffix = add_submenu_page(
				'wpmudev-hub',
				$this->page_title,
				$this->menu_title,
				$this->menu_cap(),
				$this->menu_slug,
				array( $this, 'page' )
			);
		}

		if ( $this->page_hook_suffix ) {
			$this->setup_page_hooks();
		}
	}

	public function on_load() {
		// remove footer texts
		add_filter( 'admin_footer_text', '__return_empty_string', 11 );
		add_filter( 'update_footer', '__return_empty_string', 11 );

		$activate_url = wp_nonce_url(
			WPMUDEV_HUB_Plugin::get_admin_url( 'plugins.php?action=activate&plugin=wpmudev-updates%2Fupdate-notifications.php' ),
			'activate-plugin_wpmudev-updates/update-notifications.php'
		);
		$install_url  = wp_nonce_url(
			WPMUDEV_HUB_Plugin::get_admin_url( 'update.php?action=install-plugin&plugin=install_wpmudev_dash' ),
			'install-plugin_install_wpmudev_dash'
		);

		$activate_url = wp_specialchars_decode( $activate_url );
		$install_url  = wp_specialchars_decode( $install_url );

		if ( ! WPMUDEV_HUB_Plugin::is_dash_plugin_installed() ) {
			$this->dash_status = array(
				'is_installed'       => false,
				'is_active'          => false,
				'notice_title'       => esc_html__(
					'Configure WPMU DEV Dash Plugin',
					'thc'
				),
				'notice_message'     => esc_html__(
					'The Hub Client requires WPMU DEV Dashboard plugin to be working. Please make sure you have installed, activated and logged into the Dashboard.',
					'thc'
				),
				'notice_action_text' => esc_html__( 'Install Plugin', 'thc' ),
				'notice_action_url'  => $install_url,
			);
		} elseif ( ! WPMUDEV_HUB_Plugin::is_dash_plugin_active() ) {
			$this->dash_status = array(
				'is_installed'       => true,
				'is_active'          => false,
				'notice_title'       => esc_html__(
					'Configure WPMU DEV Dash Plugin',
					'thc'
				),
				'notice_message'     => esc_html__(
					'Just one more step to enable updates and support for The Hub Client!.',
					'thc'
				),
				'notice_action_text' => __( 'Activate WPMU DEV Dashboard', 'thc' ),
				'notice_action_url'  => $activate_url,
			);
		} elseif ( ! WPMUDEV_HUB_Plugin::get_account_data() || ! WPMUDEV_HUB_Plugin::get_hub_site_id() ) {
			$this->has_missing_requirement = false;
			$this->dash_status             = array(
				'is_installed'       => true,
				'is_active'          => true,
				'notice_title'       => esc_html__(
					'Configure WPMU DEV Dash Plugin',
					'thc'
				),
				'notice_message'     => esc_html__(
					'The Hub Client requires WPMU DEV Dashboard plugin to be working. Please make sure you have installed, activated and logged into the Dashboard.',
					'thc'
				),
				'notice_action_text' => esc_html__( 'Log In', 'thc' ),
				'notice_action_url'  => add_query_arg( array( 'page' => 'wpmudev' ), WPMUDEV_HUB_Plugin::get_admin_url( 'admin.php' ) ),
			);
		} else {
			// all is well
			$this->dash_status['is_installed'] = true;
			$this->dash_status['is_active']    = true;
			$this->has_missing_requirement     = false;
		}

		if ( $this->has_missing_requirement ) {
			// dont process request
			return;
		}

		// do first sync if required
		$hosting_reseller_first_sync = get_site_option( 'wpmudev_hub_reseller_first_sync', 0 );
		$domain_reseller_first_sync  = get_site_option( 'wpmudev_hub_domain_reseller_first_sync', 0 );
		if ( ! $hosting_reseller_first_sync || ! $domain_reseller_first_sync ) {
			if ( class_exists( 'WPMUDEV_Dashboard' ) && method_exists( 'WPMUDEV_Dashboard_Api', 'hub_sync' ) ) {
				WPMUDEV_Dashboard::$api->hub_sync( false, true );
			}    // whatever happen, mark as done
			if ( ! $hosting_reseller_first_sync ) {
				update_site_option( 'wpmudev_hub_reseller_first_sync', time() );
			}
			if ( ! $domain_reseller_first_sync ) {
				update_site_option( 'wpmudev_hub_domain_reseller_first_sync', time() );
			}
		}

		// no missing requirement
		// default on load
		$this->process_request();
	}

	public function add_styles() {
		// default styles wp_enqueue_style
		wp_enqueue_style(
			'wpmudev-hub2-settings',
			plugins_url( '/build/admin/index.css', WPMUDEV_HUB_PLUGIN_FILE ),
			array( 'thc-shared-style' ), // already registered in WPMUDEV_HUB_Plugin::register_shared_assets
			WPMUDEV_HUB_Plugin::VERSION
		);

		// include pricing block style in plugin admin pages
		wp_enqueue_style( 'thc-hosting-pricing-style' ); // already registered in WPMUDEV_HUB_Plugin::register_hosting_pricing_block_assets
	}

	public function get_main_script_key() {
		return WPMUDEV_HUB_Plugin::PLUGIN_SLUG . '-admin-settings';
	}

	public function register_scripts() {
		wp_register_script(
			$this->get_main_script_key(),
			plugins_url( '/build/admin/index.js', WPMUDEV_HUB_PLUGIN_FILE ),
			array( 'wp-i18n' ),
			WPMUDEV_HUB_Plugin::VERSION,
			true
		);
	}

	public function load_scripts_locale() {
		$hub_account = WPMUDEV_HUB_Plugin::get_account_data();

		$has_reseller_hosting_access = false;
		if ( class_exists( 'WPMUDEV_Dashboard' ) && method_exists( 'WPMUDEV_Dashboard_Api', 'has_access' ) ) {
			$has_reseller_hosting_access = WPMUDEV_Dashboard::$api->has_access( 'reseller-hosting' );
		}
		$has_reseller_domain_access = false;
		if ( class_exists( 'WPMUDEV_Dashboard' ) && method_exists( 'WPMUDEV_Dashboard_Api', 'has_access' ) ) {
			$has_reseller_domain_access = WPMUDEV_Dashboard::$api->has_access( 'reseller-domain' );
		}

		$current_user = get_userdata( get_current_user_id() );

		// global vars to pass on react app
		wp_localize_script(
			$this->get_main_script_key(),
			'hub_whitelabel_settings',
			array(
				'api_nonce'                   => wp_create_nonce( 'wp_rest' ),
				'api_url'                     => WPMUDEV_HUB_Plugin::get_rest_url( WPMUDEV_HUB_Plugin::REST_API_SLUG_BASE ),
				'api_urls'                    => WPMUDEV_HUB_Plugin::get_rest_url(),
				'admin_menus_url'             => get_admin_url( WPMUDEV_HUB_Plugin::get_front_site_id(), 'nav-menus.php' ),
				'is_multisite'                => WPMUDEV_HUB_Plugin::is_multisite(),
				'build_path'                  => esc_url( plugins_url( 'assets/build/admin/', WPMUDEV_HUB_PLUGIN_FILE ) ),
				'hub_api_url'                 => WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server(),
				'hub_team_id'                 => WPMUDEV_HUB_Plugin::get_team_id(),
				'has_reseller_hosting_access' => $has_reseller_hosting_access,
				'has_reseller_domain_access'  => $has_reseller_domain_access,
				'app_container'               => $this->page_container,
				'dash_status'                 => $this->dash_status,
				'hub_embed_site_id'           => WPMUDEV_HUB_Plugin::get_hub_site_id(),
				'hub_embed_url'               => WPMUDEV_HUB_Plugin_Front::get_embed_url( true ),
				'hub_account'                 => array(
					'name'       => esc_html( isset( $hub_account['name'] ) ? $hub_account['name'] : '' ),
					'email'      => esc_html( isset( $hub_account['email'] ) ? $hub_account['email'] : '' ),
					'avatar_url' => esc_url( isset( $hub_account['avatar_url'] ) ? $hub_account['avatar_url'] : '' ),
				),
				'current_user'                => array(
					'id'         => $current_user ? ( isset( $current_user->ID ) ? $current_user->ID : 0 ) : 0,
					'name'       => $current_user ? ( isset( $current_user->display_name ) ? $current_user->display_name : '' ) : '',
					'email'      => $current_user ? ( isset( $current_user->user_email ) ? $current_user->user_email : '' ) : '',
					'avatar_url' => $current_user ? ( get_avatar_url( ( isset( $current_user->ID ) ? $current_user->ID : 0 ), array( 'size' => 48 ) ) ) : '',
				),
				'wpmudev_urls'                => array(
					'site'           => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) ),
					'hub'            => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'hub2/' ),
					'support'        => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'get-support/' ),
					'documentations' => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'docs/' ),
					'documentation'  => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'docs/wpmu-dev-plugins/the-hub-client-wpmu-dev/' ),
					'plugins'        => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'projects/category/plugins/' ),
					'roadmaps'       => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'roadmap/' ),
					'roadmap'        => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'roadmap/#hub-client' ),
					'community'      => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'hub2/community/' ),
					'tos'            => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'terms-of-service/' ),
					'privacy_policy' => esc_url( trailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) . 'privacy-policy/' ),
				),
				'wpmudev_branding'            => apply_filters(
					'wpmudev_branding',
					array(
						'footer_text' => '__THC_DEFAULT__',
					)
				),
				'version'                     => WPMUDEV_HUB_Plugin::VERSION,
			)
		);

		/**
		 * @see WP_Scripts::print_translations()
		 */
		$json_translations         = wp_json_encode( WPMUDEV_HUB_Plugin::get_locale_data( 'thc' ) );
		$translation_inline_script = <<<JS
			( function( domain, translations ) {
				var localeData = translations.locale_data[ domain ] || translations.locale_data.messages
				localeData[""].domain = domain
				wp.i18n.setLocaleData( localeData, domain )
			} )( 'thc', {$json_translations} )
		JS;
		wp_add_inline_script( $this->get_main_script_key(), $translation_inline_script, 'before' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->get_main_script_key() );
	}

	protected function process_request() {
	}

	protected function get_base_url() {
		return add_query_arg(
			array(
				'page' => $this->menu_slug,
			),
			WPMUDEV_HUB_Plugin::get_admin_url( 'admin.php' )
		);
	}

	protected function get_request( $key ) {
		return isset( $this->requests[ $key ] ) ? $this->requests[ $key ] : '';
	}

	protected function unset_requests( $keys = array() ) {
		foreach ( $keys as $key ) {
			unset( $this->requests[ $key ] );
		}
	}

	public function maybe_display_errors() {
		if ( empty( $this->errors ) ) {
			return;
		}
		?>

		<div class="notice notice-error inline">
			<?php foreach ( $this->errors as $error ) : ?>
				<p><?php echo wp_kses( $error->get_error_message(), $this->messages_kses ); ?></p>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function maybe_display_successes() {
		if ( empty( $this->successes ) ) {
			return;
		}
		?>
		<div class="notice notice-success inline">
			<?php foreach ( $this->successes as $success ) : ?>
				<p><?php echo wp_kses( $success, $this->messages_kses ); ?></p>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function page() {
		?>
		<?php
		$this->maybe_display_errors();
		$this->maybe_display_successes();
		$this->page_content();
		?>
		<?php
	}

	public function page_content() {
		?>
		<div class="<?php echo esc_attr( $this->page_container ) . ' ' . esc_attr( $this->sui_classes ); ?>" id="<?php echo esc_attr( $this->page_container ); ?>"></div>
		<?php
	}
}