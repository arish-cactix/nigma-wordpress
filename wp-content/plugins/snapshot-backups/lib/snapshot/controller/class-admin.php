<?php // phpcs:ignore
/**
 * Snapshot controllers: admin controller class
 *
 * Sets up and works with front-facing requests on admin pages.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller;

use WPMUDEV\Snapshot4\Authentication\Auth;
use WPMUDEV\Snapshot4\Configs\Rest;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Assets;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Helper\Fs;
use WPMUDEV\Snapshot4\Helper\Notifications;
use WPMUDEV\Snapshot4\Model\Env;
use WPMUDEV\Snapshot4\Helper\Db;

/**
 * Admin controller class
 */
class Admin extends Controller {

	/**
	 * Localized messages for JS.
	 *
	 * @var array
	 */
	private $localized_messages = array();

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		add_action(
			( is_multisite() ? 'network_admin_menu' : 'admin_menu' ),
			array( $this, 'add_menu' )
		);

		add_filter( 'plugin_action_links_' . SNAPSHOT_BASE_NAME, array( $this, 'plugin_links' ) );
		add_filter( 'network_admin_plugin_action_links_' . SNAPSHOT_BASE_NAME, array( $this, 'plugin_links' ) );

		if ( get_site_transient( 'snapshot_download_link_notification' ) ) {
			if ( is_network_admin() ) {
				add_action( 'network_admin_notices', array( $this, 'snapshot_backup_exported_notification' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'snapshot_backup_exported_notification' ) );
			}
			add_action( 'admin_print_footer_scripts', array( $this, 'snapshot_print_admin_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'snapshot_print_admin_styles' ) );
		}
	}

	/**
	 * Returns user snapshot capability.
	 *
	 * @return string
	 */
	public function get_capability() {
		return is_multisite()
			? 'manage_network_options'
			: 'manage_options';
	}

	/**
	 * Sets up menu items.
	 *
	 * Also sets up front-end dependencies loading on page load.
	 */
	public function add_menu() {
		$capability = $this->get_capability();
		if ( ! current_user_can( $capability ) ) {
			return false;
		}

		add_menu_page(
			_x( 'Snapshot', 'page label', 'snapshot' ),
			_x( 'Snapshot Pro', 'menu label', 'snapshot' ),
			$capability,
			'snapshot',
			array( $this, 'page_dashboard' ),
			$this->get_menu_icon()
		);

		$dashboard = add_submenu_page(
			'snapshot',
			_x( 'Dashboard', 'page label', 'snapshot' ),
			_x( 'Dashboard', 'menu label', 'snapshot' ),
			$capability,
			'snapshot',
			array( $this, 'page_dashboard' )
		);
		$backups   = add_submenu_page(
			'snapshot',
			/* translators: %s - plugin name */
			Settings::get_brand_name() === 'WPMU DEV' ? sprintf( _x( '%s Backups', 'page label', 'snapshot' ), 'Snapshot' ) : _x( 'Backups', 'page label', 'snapshot' ),
			/* translators: %s - plugin name */
			Settings::get_brand_name() === 'WPMU DEV' ? sprintf( _x( '%s Backups', 'page label', 'snapshot' ), 'Snapshot' ) : _x( 'Backups', 'page label', 'snapshot' ),
			$capability,
			'snapshot-backups',
			array( $this, 'page_backups' )
		);
		if ( Env::is_wpmu_hosting() ) {
			$hosting_backups = add_submenu_page(
				'snapshot',
				_x( 'Hosting Backups', 'page label', 'snapshot' ),
				_x( 'Hosting Backups', 'menu label', 'snapshot' ),
				$capability,
				'snapshot-hosting-backups',
				array( $this, 'page_hosting_backups' )
			);
		}
		$destinations = add_submenu_page(
			'snapshot',
			_x( 'Destinations', 'page label', 'snapshot' ),
			_x( 'Destinations', 'menu label', 'snapshot' ),
			$capability,
			'snapshot-destinations',
			array( $this, 'page_destinations' )
		);
		$settings     = add_submenu_page(
			'snapshot',
			_x( 'Settings', 'page label', 'snapshot' ),
			_x( 'Settings', 'menu label', 'snapshot' ),
			$capability,
			'snapshot-settings',
			array( $this, 'page_settings' )
		);
		if ( ! Settings::get_branding_hide_doc_link() ) {
			$tutorials = add_submenu_page(
				'snapshot',
				_x( 'Tutorials', 'page label', 'snapshot' ),
				_x( 'Tutorials', 'menu label', 'snapshot' ),
				$capability,
				'snapshot-tutorials',
				array( $this, 'page_tutorials' )
			);
		}

		$this->localized_messages = array();

		add_action( "load-{$dashboard}", array( $this, 'add_dashboard_dependencies' ) );
		add_action( "load-{$backups}", array( $this, 'add_backups_dependencies' ) );
		if ( Env::is_wpmu_hosting() ) {
			add_action( "load-{$hosting_backups}", array( $this, 'add_hosting_backups_dependencies' ) );
		}
		add_action( "load-{$destinations}", array( $this, 'add_destinations_dependencies' ) );
		add_action( "load-{$settings}", array( $this, 'add_settings_dependencies' ) );
		if ( ! Settings::get_branding_hide_doc_link() ) {
			add_action( "load-{$tutorials}", array( $this, 'add_tutorials_dependencies' ) );
		}

		do_action( 'snapshot_after_admin_menu_rendered' );
	}

	/**
	 * Add the plugin action links to Snapshot.
	 *
	 * @since 4.8.0
	 *
	 * @param  array $links Action links.
	 *
	 * @return array
	 */
	public function plugin_links( $links ) {

		$settings_link = is_multisite() ? network_admin_url( 'admin.php?page=snapshot-backups#settings' ) : admin_url( 'admin.php?page=snapshot-backups#settings' );

		$links['snapshot_docs']     = '<a href="' . SNAPSHOT_WPMUDEV_DOCS . '?utm_source=snapshot&utm_medium=plugin&utm_campaign=snapshot_backups_docs" target="_blank" aria-label="' . esc_attr__( 'View Snapshot Documentation', 'snapshot' ) . '"> ' . esc_html__( 'Docs', 'snapshot' ) . ' </a>';
		$links['snapshot_settings'] = '<a href="' . esc_url( $settings_link ) . '" aria-label="' . esc_attr__( 'Go to Snapshot Settings', 'snapshot' ) . '">' . esc_html__( 'Settings', 'snapshot' ) . '</a>';

		return array_reverse( $links );
	}

	/**
	 * Display the backup exported notification notice.
	 *
	 * @return void
	 */
	public function snapshot_backup_exported_notification() {
		$template = new Helper\Template();
		$template->render(
			'notices/exported-backup-notification',
			array(
				'backups_page_url' => network_admin_url( 'admin.php?page=snapshot-backups' ),
			)
		);
	}

	/**
	 * Adds some style to the backup exported notification.
	 *
	 * @return void
	 */
	public function snapshot_print_admin_styles() {
		?>
	<style id="snapshot-admin-styles">
		.notice.snapshot-global-notice {
			opacity: 0;
			height: auto;
			border-left-color: rgba(26, 188, 156, 1);
			border-radius: 4px;
			border-left-width: 3px;
		}

		.notice.snapshot-global-notice p a{
			text-decoration: none;
			color: rgba( 23, 168, 227, 1 );
			font-weight: 600;
		}

		.snapshot-global-notice .dashicons-yes-alt {
			color: rgba(26, 188, 156, 1);
			width: 18px;
			height: 18px;
			display: inline-block;
			font-size: 18px;
			margin-top: 1px;
			margin-right: 5px;
		}

		.sui-wrap .snapshot-global-notice {
			margin: 0 0 30px 0;
		}

		.sui-wrap .snapshot-global-notice p {
			margin-bottom: 0 !important;
			font-size: 13px !important;
			line-height: 2.5 !important;
		}

		.sui-wrap .snapshot-global-notice .dashicons-yes-alt {
			line-height: 1.5;
		}
	</style>
		<?php
	}

	/**
	 * Adds some script to the backup exported notification.
	 *
	 * @return void
	 */
	public function snapshot_print_admin_scripts() {
		?>
		<script id="snapshot-admin-scripts">
			const nonce = '<?php echo esc_js( wp_create_nonce( 'snapshot-dismiss-exported-backup-notification' ) ); ?>';

			document.addEventListener( 'DOMContentLoaded', ( event ) => {
				const suiHeader = document.querySelector( '.sui-header' );
				const notice = document.querySelector( '#snapshot-exported-backup-notification' );
				var box = document.querySelector( '.sui-box' );

				if ( box && notice ) {
					// Insert notice after the title on Snapshot and WPMUDEV pages.
					box.parentNode.insertBefore( notice, box.previousSibling);
				}

				if ( suiHeader && notice ) {
					// Since bottom offset is quite large, we need to control the margin.
					suiHeader.style.marginBottom = '10px';
				}

				// Delay the display of notice to prevent the layout shift.
				setTimeout(	() => {
					if ( notice ) {
						notice.style.opacity = 1;
					}
				}, 50 );

				notice.addEventListener( 'click', ( event ) => {
					const el = event.target;
					if ( el.classList.contains('snapshot-notice__dismiss' ) || el.classList.contains( 'notice-dismiss' ) ) {
						event.preventDefault();

						// Send a request to actually delete the transient so that the notice doesn't re-appear.
						fetch(`${ajaxurl}?action=snapshot-dismiss_export_notice&_wpnonce=${nonce}`, {
							method: 'GET',
							credentials: 'same-origin'
						} )
						.then( ( response ) => response.json() )
						.then( ( result ) => {
							if ( result.success ) {
								el.closest( '.snapshot-global-notice' ).remove();
								if ( 'a' === el.tagName.toLowerCase() && window.location.href !== el.href ) {
									// Since we prevented the default behavior of a tag, now we need to redirect the user.
									window.location.href = el.href;
								}
							}
						} );
					}
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Renders the Dasbhoard page.
	 */
	public function page_dashboard() {
		$check = new Task\Check\Hub();
		$out   = new Helper\Template();

		$welcome_modal     = ! Settings::get_started_seen();
		$welcome_modal_alt = Settings::get_started_seen_persistent() && ! Settings::get_remove_settings();

		$disable_backup_button = get_site_option( self::SNAPSHOT_RUNNING_BACKUP );

		$active_v3 = is_plugin_active( 'snapshot/snapshot.php' );

		$custom_hero_image  = Assets::get_custom_hero_image();
		$plugin_custom_name = Settings::get_brand_name();
		$sui_branding_class = Assets::get_sui_branding_class();
		$is_branding_hidden = Settings::is_branding_hidden();

		// Check if there are local (v3) snapshots around.
		$v3_local    = false;
		$v3_settings = get_option( 'wpmudev_snapshot' );

		if ( isset( $v3_settings['items'] ) && is_array( $v3_settings['items'] ) && ! empty( $v3_settings['items'] ) ) {
			$v3_local = true;
		}

		$check->apply();
		$out->render(
			'pages/dashboard',
			array(
				'errors'                => $check->get_errors(),
				'welcome_modal'         => $welcome_modal,
				'welcome_modal_alt'     => $welcome_modal_alt,
				'disable_backup_button' => $disable_backup_button,
				'active_v3'             => $active_v3,
				'v3_local'              => $v3_local,
				'custom_hero_image'     => $custom_hero_image,
				'plugin_custom_name'    => $plugin_custom_name,
				'sui_branding_class'    => $sui_branding_class,
				'is_branding_hidden'    => $is_branding_hidden,
				'plugin_icon_details'   => Settings::get_icon_details(),
			)
		);
	}

	/**
	 * Renders the backups page.
	 */
	public function page_backups() {
		$check = new Task\Check\Hub();
		$out   = new Helper\Template();

		$global_exclusions  = get_site_option( 'snapshot_global_exclusions', array() );
		$default_exclusions = get_site_option( 'snapshot_exclude_large', true );

		$welcome_modal     = ! Settings::get_started_seen();
		$welcome_modal_alt = Settings::get_started_seen_persistent() && ! Settings::get_remove_settings();

		$disable_backup_button = get_site_option( self::SNAPSHOT_RUNNING_BACKUP );

		$active_v3 = is_plugin_active( 'snapshot/snapshot.php' );

		$custom_hero_image  = Assets::get_custom_hero_image();
		$plugin_custom_name = Settings::get_brand_name();
		$sui_branding_class = Assets::get_sui_branding_class();
		$is_branding_hidden = Settings::is_branding_hidden();

		$all_db_tables        = Db::get_all_database_tables( false );
		$all_db_tables        = Db::bulk_selection_classes( array_column( $all_db_tables, 'name' ) );
		$db_exclusions        = Db::get_tables_exclusions();
		$db_exclusion_default = (
			get_site_option(
				'snapshot_excluded_tables',
				false
			) === false
		) ? true : false;

		// Check if there are local (v3) snapshots around.
		$v3_local    = false;
		$v3_settings = get_option( 'wpmudev_snapshot' );

		if ( isset( $v3_settings['items'] ) && is_array( $v3_settings['items'] ) && ! empty( $v3_settings['items'] ) ) {
			$v3_local = true;
		}

		$compat_php_version = version_compare( phpversion(), '7.0.0' );

		$check->apply();

		$out->render(
			'pages/backups',
			array(
				'errors'                => $check->get_errors(),
				'welcome_modal'         => $welcome_modal,
				'welcome_modal_alt'     => $welcome_modal_alt,
				'global_exclusions'     => $global_exclusions,
				'default_exclusions'    => $default_exclusions,
				'all_db_tables'         => $all_db_tables,
				'db_exclusions'         => $db_exclusions,
				'db_exclusions_default' => $db_exclusion_default,
				'disable_backup_button' => $disable_backup_button,
				'logs'                  => array(),
				'loading_logs'          => true,
				'compat_php_version'    => $compat_php_version,
				'active_v3'             => $active_v3,
				'v3_local'              => $v3_local,
				'email_settings'        => Settings::get_email_settings(),
				'custom_hero_image'     => $custom_hero_image,
				'plugin_custom_name'    => $plugin_custom_name,
				'sui_branding_class'    => $sui_branding_class,
				'is_branding_hidden'    => $is_branding_hidden,
			)
		);
	}

	/**
	 * Renders the hosting backups page.
	 */
	public function page_hosting_backups() {
		$check = new Task\Check\Hub();
		$out   = new Helper\Template();

		$welcome_modal     = ! Settings::get_started_seen();
		$welcome_modal_alt = Settings::get_started_seen_persistent() && ! Settings::get_remove_settings();

		$active_v3 = is_plugin_active( 'snapshot/snapshot.php' );

		$custom_hero_image  = Assets::get_custom_hero_image();
		$sui_branding_class = Assets::get_sui_branding_class();
		$is_branding_hidden = Settings::is_branding_hidden();

		// Check if there are local (v3) snapshots around.
		$v3_local    = false;
		$v3_settings = get_option( 'wpmudev_snapshot' );

		if ( isset( $v3_settings['items'] ) && is_array( $v3_settings['items'] ) && ! empty( $v3_settings['items'] ) ) {
			$v3_local = true;
		}

		$args = array(
			'per_page' => 1,
			'page'     => 1,
		);

		$task     = new Task\Hosting\Listing();
		$response = $task->apply( $args );

		if ( is_array( $response ) ) {
			$has_hosting_backups = (bool) $response['total_backups'];
			$schedule_type       = $response['schedule'];
		}
		$user_profile = Helper\Api::get_dashboard_profile();

		$out->render(
			'pages/hosting_backups',
			array(
				'errors'              => $check->get_errors(),
				'welcome_modal'       => $welcome_modal,
				'welcome_modal_alt'   => $welcome_modal_alt,
				'active_v3'           => $active_v3,
				'v3_local'            => $v3_local,
				'custom_hero_image'   => $custom_hero_image,
				'sui_branding_class'  => $sui_branding_class,
				'is_branding_hidden'  => $is_branding_hidden,
				'has_hosting_backups' => $has_hosting_backups ?? false,
				'user_name'           => $user_profile['name'] ?? '',
				'schedule_type'       => $schedule_type ?? false,
			)
		);
	}

	/**
	 * Renders the destinations page.
	 */
	public function page_destinations() {
		$check = new Task\Check\Hub();
		$out   = new Helper\Template();

		$welcome_modal     = ! Settings::get_started_seen();
		$welcome_modal_alt = Settings::get_started_seen_persistent() && ! Settings::get_remove_settings();

		$active_v3 = is_plugin_active( 'snapshot/snapshot.php' );

		$custom_hero_image  = Assets::get_custom_hero_image();
		$plugin_custom_name = Settings::get_brand_name();
		$sui_branding_class = Assets::get_sui_branding_class();
		$is_branding_hidden = Settings::is_branding_hidden();

		// Check if there are local (v3) snapshots around.
		$v3_local    = false;
		$v3_settings = get_option( 'wpmudev_snapshot' );

		if ( isset( $v3_settings['items'] ) && is_array( $v3_settings['items'] ) && ! empty( $v3_settings['items'] ) ) {
			$v3_local = true;
		}

		// Produce the Google Oauth link to be used for setting up destinations.
		$auth_url = Model\Request\Destination\Googledrive::create_oauth_link();

		// Dropbox OAuth authorization link.
		$dropbox_auth_url = Model\Request\Destination\Dropbox::create_oauth_link();

		// OneDrive OAuth authorization link.
		$onedrive_auth_url = Model\Request\Destination\Onedrive::create_oauth_link();

		$check->apply();
		$out->render(
			'pages/destinations',
			array(
				'errors'              => $check->get_errors(),
				'welcome_modal'       => $welcome_modal,
				'welcome_modal_alt'   => $welcome_modal_alt,
				'active_v3'           => $active_v3,
				'v3_local'            => $v3_local,
				'custom_hero_image'   => $custom_hero_image,
				'plugin_custom_name'  => $plugin_custom_name,
				'sui_branding_class'  => $sui_branding_class,
				'is_branding_hidden'  => $is_branding_hidden,
				'plugin_icon_details' => Settings::get_icon_details(),
				'auth_url'            => $auth_url,
				'dropbox_auth_url'    => $dropbox_auth_url,
				'onedrive_auth_url'   => $onedrive_auth_url,
			)
		);
	}

	/**
	 * Renders the settings page.
	 */
	public function page_settings() {
		$check = new Task\Check\Hub();
		$out   = new Helper\Template();

		$global_exclusions   = get_site_option( 'snapshot_global_exclusions' );
		$remove_on_uninstall = get_site_option( 'snapshot_remove_on_uninstall', 0 );

		$welcome_modal     = ! Settings::get_started_seen();
		$welcome_modal_alt = Settings::get_started_seen_persistent() && ! Settings::get_remove_settings();

		$active_v3 = is_plugin_active( 'snapshot/snapshot.php' );

		$is_branding_hidden = Settings::is_branding_hidden();

		// Check if there are local (v3) snapshots around.
		$v3_local    = false;
		$v3_settings = get_option( 'wpmudev_snapshot' );

		if ( isset( $v3_settings['items'] ) && is_array( $v3_settings['items'] ) && ! empty( $v3_settings['items'] ) ) {
			$v3_local = true;
		}

		$check->apply();
		$out->render(
			'pages/settings',
			array(
				'errors'              => $check->get_errors(),
				'welcome_modal'       => $welcome_modal,
				'welcome_modal_alt'   => $welcome_modal_alt,
				'global_exclusions'   => ! empty( $global_exclusions ) ? $global_exclusions : array(),
				'remove_on_uninstall' => $remove_on_uninstall,
				'active_v3'           => $active_v3,
				'v3_local'            => $v3_local,
				'is_branding_hidden'  => $is_branding_hidden,
			)
		);
	}

	/**
	 * Renders the tutorials page.
	 */
	public function page_tutorials() {
		$check = new Task\Check\Hub();
		$out   = new Helper\Template();

		$welcome_modal     = ! Settings::get_started_seen();
		$welcome_modal_alt = Settings::get_started_seen_persistent() && ! Settings::get_remove_settings();

		$active_v3 = is_plugin_active( 'snapshot/snapshot.php' );

		$is_branding_hidden = Settings::is_branding_hidden();

		// Check if there are local (v3) snapshots around.
		$v3_local    = false;
		$v3_settings = get_option( 'wpmudev_snapshot' );

		if ( isset( $v3_settings['items'] ) && is_array( $v3_settings['items'] ) && ! empty( $v3_settings['items'] ) ) {
			$v3_local = true;
		}

		$check->apply();
		$out->render(
			'pages/tutorials',
			array(
				'errors'             => $check->get_errors(),
				'welcome_modal'      => $welcome_modal,
				'welcome_modal_alt'  => $welcome_modal_alt,
				'active_v3'          => $active_v3,
				'v3_local'           => $v3_local,
				'is_branding_hidden' => $is_branding_hidden,
			)
		);
	}

	/**
	 * Adds shared UI body class.
	 *
	 * @see https://wpmudev.github.io/shared-ui/
	 *
	 * @param string $classes Admin page body classes this far.
	 *
	 * @return string
	 */
	public function add_admin_body_class( $classes ) {
		if ( apply_filters( 'wpmudev_branding_hide_branding', false ) ) {
			$classes .= ' wpmudev-hide-branding';
		}

		$classes .= ' sui-' . SNAPSHOT_SUI;

		return $classes;
	}

	/**
	 * Adds front-end dependencies that are shared between Snapshot admin pages.
	 */
	public function add_shared_dependencies() {
		$screen = get_current_screen();

		add_filter( 'admin_body_class', array( $this, 'add_admin_body_class' ) );

		$assets = new Helper\Assets();

		$hide_doc_link = Settings::get_branding_hide_doc_link();

		wp_enqueue_style( 'snapshot', $assets->get_asset( 'css/snapshot.css' ), array(), SNAPSHOT_BACKUPS_VERSION );

		$deps = array( 'clipboard' );

		if ( isset( $screen->id ) && ( false !== strpos( $screen->id, 'snapshot' ) ) ) {
			$rest = Rest::get_instance();
			wp_enqueue_script( 'snapshot-configs', $assets->get_asset( 'js/snapshot_configs.js' ), array( 'wp-i18n', 'lodash' ), SNAPSHOT_BACKUPS_VERSION, true );
			$react_data = array(
				'links'                => array(
					'accordionImg' => $assets->get_asset( 'img/configs-icon.svg' ),
					'hubConfigs'   => \snapshot_get_external_links( 'configs' ),
					'hubWelcome'   => \snapshot_get_external_links( 'hub-welcome' ),
					'configsPage'  => network_admin_url() . 'admin.php?page=snapshot-settings#apply-configs',
				),
				'module'               => array(
					'isMember'       => true,
					'isWhiteLabeled' => apply_filters( 'wpmudev_branding_hide_branding', false ),
				),
				'requestsData'         => array(
					'root'           => esc_url_raw( rest_url( $rest->get_namespace() . '/preset_configs' ) ),
					'nonce'          => wp_create_nonce( 'wp_rest' ),
					'apiKey'         => ENV::get_wpmu_api_key(),
					'hubBaseURL'     => Env::get_wpmu_api_server_url() . '/api/hub/v1/package-configs',
					'pluginData'     => get_file_data(
						SNAPSHOT_DIR_PATH . basename( SNAPSHOT_BASE_NAME ),
						array(
							'name' => 'Plugin Name',
							'id'   => 'WDP ID',
						)
					),
					'pluginRequests' => array(
						'nonce'         => wp_create_nonce( 'snapshot-fetch' ),
						'uploadAction'  => 'snapshot_upload_config',
						'createAction'  => 'snapshot_create_config',
						'applyAction'   => 'snapshot_apply_config',
						'callback'      => 'snapshot_get_region_description',
						'ajax_callback' => 'snapshot_display_password_modal',
					),
				),
				'fetch_region_nonce'   => wp_create_nonce( 'snapshot-fetch-region' ),
				'region_mismatch_desc' => esc_html__( 'Are you sure you want to apply the {configName} config to this site?', 'snapshot' ),
				'config_applied'       => esc_html__( '{configName} has been applied successfully.', 'snapshot' ),
			);
			wp_localize_script( 'snapshot-configs', 'snapshotReact', $react_data );

			$deps[] = 'snapshot-configs';
		}//end if

		wp_enqueue_script( 'snapshot', $assets->get_asset( 'js/snapshot.js' ), $deps, SNAPSHOT_BACKUPS_VERSION, true );

		wp_localize_script(
			'snapshot',
			'SnapshotAjax',
			array(
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'auth'     => array(
					'required' => ( new Auth() )->is_enabled(),
					'checked'  => get_site_transient( 'snapshot_http_authentication_checked' ) ? true : false,
					'nonce'    => wp_create_nonce( 'snapshot-http-auth' ),
					/* translators: %s - admin url*/
					'notice'   => sprintf( __( 'Your site has Password Protection enabled. Please navigate to the <a href="%s">Settings > Password Protection</a> to add your site\'s credentials.', 'snapshot' ), esc_url( network_admin_url( 'admin.php?page=snapshot-settings#password-protection' ) ) ),
					'exists'   => false,
					'succeed'  => __( 'Connection successful', 'snapshot' ),
					'failed'   => __( 'Failed to connect. Incorrect HTTP/HTTPS authentication username or password. Please try again.', 'snapshot' ),
					'stored'   => __( 'Authentication credentials stored successfully!', 'snapshot' ),
					'updated'  => __( 'Authentication credentials updated successfully!', 'snapshot' ),
					'deleted'  => __( 'Authentication credentials deleted successfully!', 'snapshot' ),
					'notified' => get_site_transient( 'snapshot_http_auth_enabled_notified' ) ? true : false,
				),
				'explorer' => array(
					'nonce' => wp_create_nonce( 'snapshot-file-explorer' ),
				),
				'exported' => get_site_transient( 'snapshot_download_link_immediate_notification' ) ? 'true' : 'false',
			)
		);

		/**
		 * Clear exclusions
		 */
		$this->localized_messages['cleared_exclusion_success'] = wp_kses_post(
			/* translators: %1$s - Cleared message, %2$s - Undo text */
			sprintf(
				'%1$s <a href="#" class="snapshot-undo--exclusions__list">%2$s</a>',
				__( 'Cleared global file exclusions successfully.', 'snapshot' ),
				__( 'Undo', 'snapshot' )
			)
		);

		$this->localized_messages['settings_save_success']   = __( 'Your settings have been updated successfully.', 'snapshot' );
		$this->localized_messages['settings_delete_success'] = __( 'You deleted 1 backup.', 'snapshot' );
		$this->localized_messages['reset_settings_success']  = __( 'Your settings have been reset.', 'snapshot' );
		$this->localized_messages['reset_settings_error']    = __( 'Your settings couldn\'t be reset.', 'snapshot' );
		$this->localized_messages['generic_error']           = __( 'Sorry we are unable to process this request at the moment. Please try again later.', 'snapshot' );
		$this->localized_messages['comment_added']           = __( 'Your comment has been successfully added.', 'snapshot' );
		$this->localized_messages['comment_updated']         = __( 'Your comment has been successfully updated.', 'snapshot' );
		/* translators: %s - number of backups */
		$this->localized_messages['settings_save_error'] = __( 'Request to save settings was not successful.', 'snapshot' );
		$this->localized_messages['schedule_save_error'] = __( 'Request for a backup schedule was not successful.', 'snapshot' );
		$this->localized_messages['get_schedule_error']  = __( 'Request for a backup schedule was not successful.', 'snapshot' );
		$this->localized_messages['api_error']           = __( 'We couldn\'t connect to the API.', 'snapshot' );

		/* translators: %s - date of scheduled backups */
		$this->localized_messages['schedule_backup_time'] = __( 'You set your backup schedule to %s.', 'snapshot' );
		/* translators: %s - date of scheduled backups */
		$this->localized_messages['schedule_update_time'] = __( 'Backup schedule has been changed to %s.', 'snapshot' );
		$this->localized_messages['schedule_delete']      = __( 'You have turned off the backup schedule', 'snapshot' );
		/* translators: %s - date of next scheduled backup */
		$this->localized_messages['schedule_next_backup_time_note'] = __( 'Your next backup is scheduled to run on %s. Note: the first backup may take some time to complete, subsequent backups will be much faster.', 'snapshot' );
		/* translators: %s - date of next scheduled backup */
		$this->localized_messages['schedule_next_backup_time'] = __( 'The next scheduled backup will be on %s.', 'snapshot' );
		$this->localized_messages['schedule_run_backup_text']  = __( 'You can also <a href="#">run on-demand manual backups</a>.', 'snapshot' );
		$this->localized_messages['onboarding_schedule_close'] = __( 'You set up your account successfully. You are now ready to <a href="#">create your first backup</a> or you can <a href="#">set a schedule</a> to create backups automatically.', 'snapshot' );
		/* translators: %s - website name */
		$this->localized_messages['backup_export_success'] = __( 'We are preparing your backup export for <strong>%s</strong>, it will be sent to your email when it is ready.', 'snapshot' );
		/* translators: %s - website name */
		$this->localized_messages['backup_export_success_download'] = __( 'We are preparing your backup export for <strong>%s</strong>, You will be notified once the download is ready.', 'snapshot' );
		$this->localized_messages['backup_export_download_ready']   = __( 'Your backup has been successfully exported and is available for download. Please download your backup within the next 7 days before the link expires.', 'snapshot' );

		/* translators: %s - backup name */
		$this->localized_messages['backup_export_already_requested'] = __( 'You’ve already requested a download link for the backup <strong>%s</strong>. The backup export is in progress, and a download link will be sent to your email when complete. Please wait an hour before submitting another request.', 'snapshot' );

		/* Backup export steps */
		$this->localized_messages['export_step_1'] = __( 'Step 1 of 2', 'snapshot' );
		$this->localized_messages['export_step_2'] = __( 'Step 2 of 2', 'snapshot' );

		$this->localized_messages['backup_export_error'] = $hide_doc_link
			? __( 'We couldn\'t send the backup export to your email due to a connection problem. Please try downloading the backup again, or contact support if the issue persists.', 'snapshot' )
			/* translators: %s - HUB link */
			: sprintf( __( 'We couldn\'t send the backup export to your email due to a connection problem. Please try downloading the backup again, or <a href="%s" target="_blank">contact our support team</a> if the issue persists.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' );

		$this->localized_messages['manual_backup_success'] = __( 'Your backup is in progress. First time backups can take some time to complete, though subsequent backups will be much faster.', 'snapshot' );
		$this->localized_messages['backup_is_in_progress'] = __( 'Your backup is in progress. The duration of the backup depends on your website size. Small sites won\'t take longer than a few minutes, but larger sites can take a couple of hours.', 'snapshot' );
		$this->localized_messages['manual_backup_error']   = __( 'Request to create manual backup was not successful.', 'snapshot' );
		$this->localized_messages['failed_listing_logs']   = __( 'The logs couldn\'t be loaded at the moment. Please try again later.', 'snapshot' );
		$this->localized_messages['log_backup_not_found']  = __( 'This backup doesn\'t exist', 'snapshot' );
		$this->localized_messages['no_logs_found']         = __( 'No logs found.', 'snapshot' );
		$this->localized_messages['backup_log_not_found']  = __( 'Log for this backup doesn\'t exist', 'snapshot' );
		/* translators: %s - brand name */
		$this->localized_messages['api_key_copied']       = sprintf( __( 'The %s API Key is copied successfully.', 'snapshot' ), Settings::get_brand_name() );
		$this->localized_messages['site_id_copied']       = __( 'The Site ID is copied successfully.', 'snapshot' );
		$this->localized_messages['update_progress_fail'] = __( 'Couldn\'t return info for the running backup.', 'snapshot' );
		$this->localized_messages['running_backup_fail']  = sprintf(
			/* translators: %s - IPs list */
			__( 'The backup failed. Please <strong>add the following IPs to the allowlist</strong> and then run another backup.<pre class="sui-code-snippet snapshot-ips-snippet">%s</pre>', 'snapshot' ),
			implode( "\r\n", array( '35.157.144.199', '18.204.159.253', '34.196.51.17', '54.227.51.40', '3.93.131.0' ) )
		);
		$this->localized_messages['manual_backup_running_already'] = __( 'The backup failed because another backup is already running. Please <a href="#">check the logs</a> for more information, and then <a href="#">run another backup</a>.', 'snapshot' );
		$this->localized_messages['manual_backup_same_minute']     = __( 'The backup failed because another backup started running at the same time. Please <a href="#">check the logs</a> for more information, and then <a href="#">run another backup</a>.', 'snapshot' );

		/* translators: %s - Website link */
		$this->localized_messages['trigger_restore_success'] = __( 'Your website has been restored successfully. <a href="%s" target="_blank">View website</a>', 'snapshot' );
		/* translators: %s - Skipped file path */
		$this->localized_messages['trigger_restore_success_one_skipped_file'] = __( 'Your website has been restored successfully. We found 1 unwritable file <strong>%s</strong> which we were unable to restore due to its file permissions. We recommend <a href="#" class="snapshot-view-log">checking the restoration logs</a> for more information.', 'snapshot' );
		/* translators: %s - Number of skipped files */
		$this->localized_messages['trigger_restore_success_few_skipped_files'] = __( 'Your website has been restored successfully. We found %s unwritable files which we were unable to restore due to their file permissions. We recommend <a href="#" class="snapshot-view-log">checking the restoration logs</a> for more information.', 'snapshot' );
		/* translators: %s - Skipped table name */
		$this->localized_messages['trigger_restore_success_one_skipped_table'] = __( 'Your website restored successfully. *Note: During restoration we found a db table <strong>%s</strong> with the wrong database prefix. If needed you can export the backup and manually add it to the database. Refer to <a href="#" class="snapshot-view-log">restoration logs</a> for more information.', 'snapshot' );
		/* translators: %s - Number of skipped tables */
		$this->localized_messages['trigger_restore_success_few_skipped_tables'] = __( 'Your website restored successfully. *Note: During restoration we found %s db tables with the wrong database prefix. If needed you can export the backup and manually add the tables to the database. Refer to <a href="#" class="snapshot-view-log">restoration logs</a> for more information.', 'snapshot' );
		$this->localized_messages['trigger_restore_success_wp_config_skipped']  = __( 'We excluded <strong>wp-config.php</strong> from being restored, so the backup restore process will finish without fail. Note, the wp-config.php file is available in the backup, just isn\'t restored.', 'snapshot' );

		$this->localized_messages['trigger_restore_error'] = $hide_doc_link
			/* translators: %s - Stage of the restore */
			? __( 'The backup failed to restore while %s. <a href="#" class="snapshot-view-log">Check the logs</a> for more information and then try restoring the backup again. Contact support if the issue persists.', 'snapshot' )
			/* translators: %s - Stage of the restore */
			: __( 'The backup failed to restore while %s. <a href="#" class="snapshot-view-log">Check the logs</a> for more information and then try restoring the backup again. Alternatively, you can try <a href="#" class="snapshot-ftp-restoration-hub">FTP restoration</a> via The Hub. <a href="https://wpmudev.com/hub2/support#get-support" target="_blank">Contact our support</a> team if the issue persists.', 'snapshot' );

		$this->localized_messages['trigger_restore_generic_error'] = $hide_doc_link
			? __( 'The backup failed to restore. <a href="#" class="snapshot-view-log">Check the logs</a> for more information and then try restoring the backup again. Contact support if the issue persists.', 'snapshot' )
			: __( 'The backup failed to restore. <a href="#" class="snapshot-view-log">Check the logs</a> for more information and then try restoring the backup again. Alternatively, you can try <a href="#" class="snapshot-ftp-restoration-hub">FTP restoration</a> via The Hub. <a href="https://wpmudev.com/hub2/support#get-support" target="_blank">Contact our support</a> team if the issue persists.', 'snapshot' );
		$this->localized_messages['trigger_restore_info']          = __( 'Your site is currently being restored from a backup. Please keep this page open until the process has finished - this could take a few minutes for small sites to a few hours for larger sites.', 'snapshot' );
		$this->localized_messages['restore_cancel_success']        = __( 'The running restore is cancelled.', 'snapshot' );
		$this->localized_messages['restore_cancel_error']          = __( 'The running restore couldn\'t be cancelled.', 'snapshot' );
		$this->localized_messages['delete_all_backups_success']    = __( 'You have deleted all backups.', 'snapshot' );
		$this->localized_messages['delete_all_backups_error']      = __( 'We weren\'t able to delete your backups.', 'snapshot' );
		$this->localized_messages['delete_all_logs_success']       = __( 'All your logs have been deleted successfully.', 'snapshot' );
		$this->localized_messages['delete_all_logs_error']         = __( 'We weren\'t able to delete your logs.', 'snapshot' );

		$this->localized_messages['cancel_backup_error']   = __( 'The running backup couldn\'t be cancelled.', 'snapshot' );
		$this->localized_messages['cancel_backup_success'] = __( 'Backup aborted. Please run the backup again.', 'snapshot' );

		$this->localized_messages['change_region_no_schedule'] = __( 'The backup region was changed successfully. Because all the existing backups have been removed, we recommend you <a href="#">create a backup now</a> or <a href="#">set a schedule</a> to run backups automatically.', 'snapshot' );
		/* translators: %s - Schedule frequency */
		$this->localized_messages['change_region_with_schedule'] = __( 'The backup region was changed successfully, and all the previous backups have been removed. %s scheduled backups will continue in the new region.', 'snapshot' );

		$this->localized_messages['change_region_failure'] = $hide_doc_link
			? __( 'We were unable to change the backup storage region. Please try again or contact support if the problem persists.', 'snapshot' )
			/* translators: %s - HUB link */
			: sprintf( __( 'We were unable to change the backup storage region. Please try again or <a href="%s" target="_blank">contact our support team</a> if the problem persists.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' );

		$this->localized_messages['snapshot_v3_uninstall_success'] = __( 'You uninstalled the old version of Snapshot successfully.', 'snapshot' );

		/* translators: %s - Email recipient name */
		$this->localized_messages['notifications_user_added'] = __( '%s been added as a recipient. Make sure to save your changes below to set this live.', 'snapshot' );

		$this->localized_messages['last_backup_unknown_date'] = __( 'Never', 'snapshot' );

		/* translators: %s - Name of the missing cred */
		$this->localized_messages['required_s3_cred']        = __( '%s is required.', 'snapshot' );
		$this->localized_messages['required_provider']       = __( 'Choose Provider to proceed.', 'snapshot' );
		$this->localized_messages['choose_region']           = __( 'Choose Region', 'snapshot' );
		$this->localized_messages['choose_provider']         = __( 'Choose Non AWS Storage Provider', 'snapshot' );
		$this->localized_messages['require_region']          = __( 'AWS Region is required.', 'snapshot' );
		$this->localized_messages['require_bucket_name']     = __( 'AWS bucket name is required.', 'snapshot' );
		$this->localized_messages['choose_bucket']           = __( 'Choose Bucket', 'snapshot' );
		$this->localized_messages['require_limit']           = __( 'A valid storage limit is required.', 'snapshot' );
		$this->localized_messages['require_valid_container'] = __( 'Provided name is not valid.', 'snapshot' );
		$this->localized_messages['require_name']            = __( 'Destination name is required.', 'snapshot' );
		$this->localized_messages['require_directory_id']    = __( 'A Directory ID is required.', 'snapshot' );
		$this->localized_messages['require_valid_path']      = __( 'Use "/" before the folder and between the folder and subfolders.', 'snapshot' );
		/* translators: %1$s - Chosen name of the destination, %2$s - Active current schedule, %3$s - Link to set the schedule */
		$this->localized_messages['destination_saved_schedule'] = __( '%1$s has been added as a destination. The backups will be running %2$s, according to the schedule set <a href="%3$s">here</a>.', 'snapshot' );
		/* translators: %1$s - Chosen name of the destination, %2$s - Link to set the schedule, %3$s - Link to run a backup */
		$this->localized_messages['destination_saved_no_schedule'] = __( '%1$s has been added as a destination. <a href="%2$s">Set a schedule</a> to create backups automatically or <a href="%3$s">run a manual backup</a> now.', 'snapshot' );
		/* translators: %s - Name of the destination */
		$this->localized_messages['destination_delete_successful'] = __( 'You have successfully deleted <strong>%s</strong> destination.', 'snapshot' );
		/* translators: %s - Name of the destination */
		$this->localized_messages['destination_notice_activated'] = __( 'You have successfully activated <strong>%s</strong> destination.', 'snapshot' );
		/* translators: %s - Name of the destination */
		$this->localized_messages['destination_notice_deactivated'] = __( 'You have successfully deactivated <strong>%s</strong> destination.', 'snapshot' );
		$this->localized_messages['destination_tooltip_deactivate'] = __( 'Deactivate destination', 'snapshot' );
		$this->localized_messages['destination_tooltip_activate']   = __( 'Activate destination', 'snapshot' );

		$this->localized_messages['loading_destinations'] = __( 'Loading...', 'snapshot' );
		$this->localized_messages['no_destinations']      = __( 'None', 'snapshot' );
		/* translators: %d - Number of configured 3rd party destinations */
		$this->localized_messages['more_destinations'] = __( ' + %d more', 'snapshot' );
		/* translators: %s - Field to be completed */
		$this->localized_messages['provider_placeholder'] = __( 'Place %s here', 'snapshot' );
		/* translators: %s - Storage provider to be configured */
		$this->localized_messages['configure_provider'] = __( 'Configure %s', 'snapshot' );

		$this->localized_messages['tutorials']          = __( 'Tutorials', 'snapshot' );
		$this->localized_messages['snapshot_tutorials'] = __( 'Snapshot Tutorials', 'snapshot' );

		$this->localized_messages['add_comment_text']  = __( 'Add comment', 'snapshot' );
		$this->localized_messages['edit_comment_text'] = __( 'Save edits', 'snapshot' );

		$this->localized_messages['empty_host']   = __( 'Host is required', 'snapshot' );
		$this->localized_messages['invalid_port'] = __( 'Port number is required', 'snapshot' );
		$this->localized_messages['invalid_user'] = __( 'Username is required', 'snapshot' );
		$this->localized_messages['invalid_pass'] = __( 'Password is required', 'snapshot' );
		$this->localized_messages['invalid_path'] = __( 'Path is required', 'snapshot' );

		/**
		 * Error messages for OneDrive destination
		 */
		$this->localized_messages['invalid_drive_id'] = __( 'Directory ID is empty.', 'snapshot' );
		$this->localized_messages['invalid_item_id']  = __( 'Item ID is empty.', 'snapshot' );

		$this->localized_messages['restoration_steps_partial'] = array(
			esc_html__( 'Step 0/4', 'snapshot' ),
			esc_html__( 'Step 1/4', 'snapshot' ),
			esc_html__( 'Step 2/4', 'snapshot' ),
			esc_html__( 'Step 3/4', 'snapshot' ),
			esc_html__( 'Step 4/4', 'snapshot' ),
		);

		$this->localized_messages['restoration_steps_full'] = array(
			esc_html__( 'Step 0/5', 'snapshot' ),
			esc_html__( 'Step 1/5', 'snapshot' ),
			esc_html__( 'Step 2/5', 'snapshot' ),
			esc_html__( 'Step 3/5', 'snapshot' ),
			esc_html__( 'Step 4/5', 'snapshot' ),
			esc_html__( 'Step 5/5', 'snapshot' ),
		);

		/**
		 * Configs
		 */
		/* translators: %s - brand name */
		$this->localized_messages['config_confirm_text'] = __( 'Are you sure you want to apply <strong>%s</strong> to this site?', 'snapshot' );
		// Apply Config.
		$this->localized_messages['applying_config']    = __( 'Applying Config...', 'snapshot' );
		$this->localized_messages['google_auth_failed'] = $hide_doc_link
			? __( 'Your account authentication failed. Please try again or contact our support team for help.', 'snapshot' )
			: __( 'Your account authentication failed. Please try again or contact our <a href="https://wpmudev.com/hub2/support#get-support" target="_blank">support team</a> for help.', 'snapshot' );

		// Authentication method.
		$account_link = 'https://wpmudev.com/hub2/account/details';

		$this->localized_messages['google_auth_method_invalid'] = $hide_doc_link
		? __( 'You are currently using Email & Password as your preferred login method. Please try to re-authenticate using your email and password.', 'snapshot' )
		/* translators: %s - wpmudev account link */
		: sprintf( __( 'You are currently using Email & Password as your preferred login method. Please try to re-authenticate using your email and password. Or, if you wish to change the login method, you can change it from your <a href="%s" target="_blank">Account page</a>.', 'snapshot' ), esc_url( $account_link ) );

		$this->localized_messages['auth_method_invalid'] = $hide_doc_link
		? __( 'You are currently using Google Account as your preferred login method. Please try to re-authenticate using your Google Account.', 'snapshot' )
		/* translators: %s - Create Backup link */
		: sprintf( __( 'You are currently using Google Account as your preferred login method. Please try to re-authenticate using your Google Account. Or, if you wish to change the login method, you can change it from your <a href="%s" target="_blank">Account page</a>.', 'snapshot' ), esc_url( $account_link ) );

		// HTTP Authentication.
		$this->localized_messages['general_error'] = __( 'Sorry, something went wrong!', 'snapshot' );

		/**
		 * Scheduled backups failed notification.
		 */
		$notifications = new Notifications();
		if ( $notifications->count() > 0 ) {
			$this->localized_messages['notify']            = true;
			$this->localized_messages['notification_text'] = $hide_doc_link
				? sprintf(
					/* translators: %s - Create Backup link */
					__( 'Last scheduled backup failed. We recommend creating a <a href="%s" class="blue-link">manual backup</a>. If the issue persists, please get in touch with our support team.', 'snapshot' ),
					network_admin_url( 'admin.php?page=snapshot-backups#create-backup' )
				)
				: sprintf(
					/* translators: %1$s - Create Bakup link, %2$s - Hub link */
					__( 'Last scheduled backup failed. We recommend creating a <a href="%1$s" class="blue-link">manual backup</a>. If the issue persists, please get in touch with our <a href="%2$s" target="_blank">support team</a>.', 'snapshot' ),
					network_admin_url( 'admin.php?page=snapshot-backups#create-backup' ),
					'https://wpmudev.com/hub2/support#get-support'
				);

			// Clear notifications.
			$notifications->clear();
		}

		wp_localize_script( 'snapshot', 'snapshot_messages', $this->localized_messages );

		wp_localize_script(
			'snapshot',
			'snapshot_urls',
			array(
				'dashboard'         => network_admin_url() . 'admin.php?page=snapshot',
				'backups'           => network_admin_url() . 'admin.php?page=snapshot-backups',
				'destinations'      => network_admin_url() . 'admin.php?page=snapshot-destinations',
				'settings'          => network_admin_url() . 'admin.php?page=snapshot-settings',
				'install_dashboard' => network_admin_url() . 'update.php?action=install-plugin',
				'hub_backup_tab'    => Env::get_wpmu_api_server_url() . 'hub2/site/' . Helper\Api::get_site_id() . '/backups',
			)
		);

		wp_localize_script(
			'snapshot',
			'snapshot_default_restore_path',
			array(
				'path'        => Fs::get_root_path(),
				'uploads_dir' => Fs::get_uploads_dir(),
			)
		);

		wp_localize_script(
			'snapshot',
			'snapshot_env',
			array(
				'values' => array(
					'has_hosting_backups' => Env::is_wpmu_hosting(),
					'is_pro'              => Helper\Api::is_pro(),
				),
			)
		);

		// We might need to pass the exclusion lists to the JavaScript.
		wp_localize_script( 'snapshot', 'exclusionsList', array( 'exclusions' => get_site_option( 'snapshot_global_exclusions', array() ) ) );
	}

	/**
	 * Adds front-end dependencies specific for the dashboard page.
	 */
	public function add_dashboard_dependencies() {
		$this->add_shared_dependencies();
	}

	/**
	 * Adds front-end dependencies specific for the backups page.
	 */
	public function add_backups_dependencies() {
		$this->localized_messages['create_backup_success'] = __( 'Backup created and stored successfully.', 'snapshot' );
		$this->localized_messages['export_backup_success'] = __( 'Backup created and exported successfully.', 'snapshot' );
		/* translators: %s - brand name */
		$this->localized_messages['export_backup_failure'] = sprintf( __( 'The backup is stored on %s storage, but has failed to export to the connected destination(s). Make sure you have the destination set up correctly and try to run the backup again.', 'snapshot' ), Settings::get_brand_name() );
		$this->localized_messages['storage_limit_success'] = __( 'The storage limit has been saved successfully.', 'snapshot' );
		$this->localized_messages['storage_limit_failure'] = __( 'An error occurred while saving your storage limit. Please try it again.', 'snapshot' );
		$this->localized_messages['storage_limit_invalid'] = __( 'Please add a number between 1 and 30.', 'snapshot' );

		if ( Settings::get_branding_hide_doc_link() ) {
			$this->localized_messages['insufficient_storage_space_notice'] =
				__( 'There is insufficient space to upload backups. Please contact your administrator to upgrade your storage space. Once upgraded, return here and set your schedule or run a manual backup.', 'snapshot' );
		} else {
			$this->localized_messages['insufficient_storage_space_notice'] =
				/* translators: %1$s - Add storage space link, %2$s - Hub account link */
				sprintf( __( 'There is insufficient space to upload backups. <a href="%1$s" target="_blank">Add storage space</a> to continue backing up your site. You can upgrade your storage plan from your <a href="%2$s" target="_blank">Hub / Account page</a>. Once upgraded, return here and set your schedule or run a manual backup.', 'snapshot' ), 'https://wpmudev.com/hub/account/#dash2-modal-add-storage', 'https://wpmudev.com/hub/account/' );
		}

		$this->add_shared_dependencies();
	}

	/**
	 * Adds front-end dependencies specific for the hosting backups page.
	 */
	public function add_hosting_backups_dependencies() {
		$this->add_shared_dependencies();
	}

	/**
	 * Adds front-end dependencies specific for the destinations page.
	 */
	public function add_destinations_dependencies() {
		$this->add_shared_dependencies();

		// Map of S3 compatible providers and their approrpiate info.
		$snapshot_s3_providers = array(
			'aws'          => array(
				'providerName' => esc_html__( 'Amazon S3', 'snapshot' ),
				'link'         => 'https://console.aws.amazon.com/s3',
				'fields'       => array(
					'access-key-id'     => esc_html__( 'AWS Access Key ID', 'snapshot' ),
					'secret-access-key' => esc_html__( 'AWS Secret Access Key', 'snapshot' ),
					'region'            => esc_html__( 'Region', 'snapshot' ),
				),
			),
			'backblaze'    => array(
				'providerName' => esc_html__( 'Backblaze', 'snapshot' ),
				'link'         => 'https://secure.backblaze.com/user_signin.htm',
				'fields'       => array(
					'bucket-name'       => esc_html__( 'Bucket Name', 'snapshot' ),
					'access-key-id'     => esc_html__( 'Key ID', 'snapshot' ),
					'secret-access-key' => esc_html__( 'Application Key', 'snapshot' ),
					'region'            => esc_html__( 'Region', 'snapshot' ),
				),
			),
			'googlecloud'  => array(
				'providerName' => esc_html__( 'Google Cloud', 'snapshot' ),
				'link'         => 'https://cloud.google.com/',
				'fields'       => array(
					'access-key-id'     => esc_html__( 'Access Key', 'snapshot' ),
					'secret-access-key' => esc_html__( 'Secret', 'snapshot' ),
					'region'            => esc_html__( 'Region', 'snapshot' ),
				),
			),
			'digitalocean' => array(
				'providerName' => esc_html__( 'DigitalOcean Spaces', 'snapshot' ),
				'link'         => 'https://cloud.digitalocean.com/login',
				'fields'       => array(
					'access-key-id'     => esc_html__( 'Access Key ID', 'snapshot' ),
					'secret-access-key' => esc_html__( 'Secret Access Key', 'snapshot' ),
					'region'            => esc_html__( 'Region', 'snapshot' ),
				),
			),
			'wasabi'       => array(
				'providerName' => esc_html__( 'Wasabi', 'snapshot' ),
				'link'         => 'https://console.wasabisys.com',
				'fields'       => array(
					'access-key-id'     => esc_html__( 'Access Key ID', 'snapshot' ),
					'secret-access-key' => esc_html__( 'Secret Access Key', 'snapshot' ),
					'region'            => esc_html__( 'Region', 'snapshot' ),
				),
			),
			's3_other'     => array(
				'providerName' => esc_html__( 'Other', 'snapshot' ),
				'fields'       => array(
					'access-key-id'     => esc_html__( 'Access Key', 'snapshot' ),
					'secret-access-key' => esc_html__( 'Secret Key', 'snapshot' ),
					'region'            => esc_html__( 'Region', 'snapshot' ),
					'endpoint'          => esc_html__( 'Endpoint', 'snapshot' ),
				),
			),
		);
		wp_localize_script(
			'snapshot',
			'snapshot_s3_providers',
			$snapshot_s3_providers
		);
	}

	/**
	 * Adds front-end dependencies specific for the settings page.
	 */
	public function add_settings_dependencies() {
		$this->add_shared_dependencies();
	}

	/**
	 * Adds front-end dependencies specific for the tutorials page.
	 */
	public function add_tutorials_dependencies() {
		$this->add_shared_dependencies();
	}

	/**
	 * Snapshot icon svg image.
	 *
	 * @return string
	 */
	private function get_menu_icon() {
		ob_start();
		?>
<svg width="16px" height="18px" viewBox="0 0 16 18" version="1.1" xmlns="http://www.w3.org/2000/svg"
	xmlns:xlink="http://www.w3.org/1999/xlink">
	<g id="Symbols" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
		<g id="Wp-Menu" transform="translate(-11.000000, -397.000000)" fill="#FFFFFF">
			<path
				d="M11.8958333,400.71599 L17.2291667,397.536993 L13.6666667,403.873508 L11.8958333,400.71599 Z M16.9166667,402.305489 L21.0833333,402.305489 L23.2083333,406.085919 L21.125,409.694511 L16.9166667,409.694511 L16.9166667,409.673031 L14.8541667,406 L16.9166667,402.305489 Z M25.2291667,400.178998 L19.8958333,397 L18.1041667,400.178998 L25.2291667,400.178998 Z M11,403.357995 L14.5625,409.694511 L11,409.694511 L11,403.357995 Z M23.4375,402.305489 L27,402.305489 L27,408.642005 L23.4375,402.305489 Z M26.1041667,411.28401 L20.8125,414.441527 L24.375,408.190931 L26.1041667,411.28401 Z M18.125,414.97852 L19.9166667,411.821002 L12.7708333,411.821002 L18.1041667,415 L18.125,414.97852 Z"
				id="snapshot-icon"></path>
		</g>
	</g>
</svg>
		<?php
		$svg = ob_get_clean();

		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore
	}
}