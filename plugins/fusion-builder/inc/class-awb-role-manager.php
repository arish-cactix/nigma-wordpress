<?php
/**
 * The AWB_Role_Manager class.
 *
 * @package fusion-builder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The AWB_Role_Manager class.
 *
 * @since 3.9
 */
class AWB_Role_Manager {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.9
	 * @var object
	 */
	private static $instance;

	/**
	 * Avada post types.
	 *
	 * @access private
	 * @since 3.11.7
	 * @var array
	 */
	private $avada_post_types = [
		'awb_global_options',
		'awb_prebuilts',
		'awb_studio',
		'fusion_tb_layout',
		'fusion_tb_section',
		'awb_off_canvas',
		'fusion_icons',
		'fusion_form',
		'slide',
		'fusion_template',
		'fusion_element',
		'avada_library',
		'avada_portfolio',
		'avada_faq',
	];

	/**
	 * Non-Avada post types.
	 *
	 * @access private
	 * @since 3.11.7
	 * @var array
	 */
	private $non_avada_post_types = [
		'post',
		'page',
		'product',
	];

	/**
	 * Role manager default role capabilities data.
	 *
	 * @static
	 * @access private
	 * @since 3.11.10
	 * @var array
	 */
	private static $default_role_capabilities;

	/**
	 * Role manager default capabilities data.
	 *
	 * @static
	 * @access private
	 * @since 3.11.10
	 * @var array
	 */
	private static $default_role_manager_capabilities;

	/**
	 * Role manager capabilities data.
	 *
	 * @static
	 * @access private
	 * @since 3.9
	 * @var array
	 */
	private static $role_manager_capabilities;

	/**
	 * Role of the current user.
	 *
	 * @static
	 * @access private
	 * @since 3.11.7
	 * @var string
	 */
	private static $current_user_role;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->set_default_role_capabilities();
		$this->set_default_role_manager_capabilities();
		$this->set_role_manager_capabilities();

		add_action( 'awb_add_builder_options_section', [ $this, 'add_role_manager_options_to_builder_options' ] );
		add_filter( 'awb_role_manager_access_capability', [ $this, 'set_access_capability_based_on_role_manager' ], PHP_INT_MAX, 3 );

		add_action( 'load-post.php', [ $this, 'check_global_element_and_backend_builder_access' ] );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.9
	 * @return object
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Role_Manager();
		}
		return self::$instance;
	}

	/**
	 * Gets all affected post types.
	 *
	 * @since 3.11.7
	 * @access public
	 * @return array
	 */
	public function get_all_post_types() {
		return apply_filters( 'awb_role_manager_post_types', array_merge( $this->avada_post_types, $this->non_avada_post_types ) );
	}

	/**
	 * Gets non-Avada post types.
	 *
	 * @since 3.11.7
	 * @access public
	 * @return array
	 */
	public function get_non_avada_post_types() {
		return apply_filters( 'awb_role_manager_non_avada_post_types', $this->non_avada_post_types );
	}

	/**
	 * Set capabilities for a default role.
	 *
	 * @since 3.11.10
	 * @access private
	 * @return array
	 */
	private function set_default_role_capabilities() {
		if ( null === self::$default_role_capabilities ) {
			self::$default_role_capabilities = [
				'awb_global_options' => $this->get_capability_choices( [ 'off' ] ),
				'awb_prebuilts'      => $this->get_capability_choices( [ 'off' ] ),
				'awb_studio'         => $this->get_capability_choices( [ 'off' ] ),
				'fusion_tb_layout'   => $this->get_capability_choices( [ 'off' ] ),
				'fusion_tb_section'  => $this->get_capability_choices( [ 'off', 'off', 'off', 'off' ] ),
				'awb_off_canvas'     => $this->get_capability_choices( [ 'on', 'on', 'on', 'on' ] ),
				'fusion_icons'       => $this->get_capability_choices( [ 'on' ] ),
				'fusion_form'        => $this->get_capability_choices( [ 'on', 'on', 'on', 'on', 'off' ] ),
				'slide'              => $this->get_capability_choices( [ 'on', '', '', 'on' ] ),
				'avada_library'      => $this->get_capability_choices( [ 'on', 'on', 'on', 'on', '', 'off' ] ),
				'avada_portfolio'    => $this->get_capability_choices( [ 'on', 'on', 'on', 'on' ] ),
				'avada_faq'          => $this->get_capability_choices( [ 'on', 'on', 'on', 'on' ] ),
				'post'               => $this->get_capability_choices( [ '', 'on', 'on', 'on' ] ),
				'page'               => $this->get_capability_choices( [ '', 'on', 'on', 'on' ] ),
				'product'            => $this->get_capability_choices( [ '', 'on', 'on', 'on' ] ),
			];
		}
	}

	/**
	 * Get capability choices.
	 *
	 * @since 3.11.7
	 * @access private
	 * @param array $selection The selection "mask" of choices.
	 * @return array
	 */
	private function get_capability_choices( $selection ) {
		$available_choices = [ 'dashboard_access', 'backed_builder_edit', 'live_builder_edit', 'page_options', 'submissions_access', 'global_elements' ];
		$selected_choices  = [];

		foreach ( $selection as $index => $value ) {
			if ( '' !== $value ) {
				$selected_choices[ $available_choices[ $index ] ] = $value;
			}
		}

		return $selected_choices;
	}

	/**
	 * Get capabilities for a default role.
	 *
	 * @since 3.11.10
	 * @access public
	 * @return array
	 */
	public function get_default_role_capabilities() {
		return self::$default_role_capabilities;
	}

	/**
	 * Get default role manager capabilities.
	 *
	 * @since 3.11.7
	 * @access private
	 * @return array
	 */
	public function set_default_role_manager_capabilities() {
		if ( null === self::$default_role_manager_capabilities ) {
			self::$default_role_manager_capabilities = [
				'editor'      => $this->get_editor_default_role_capabilities(),
				'author'      => $this->get_default_role_capabilities(),
				'contributor' => $this->get_default_role_capabilities(),
				'subscriber'  => $this->get_subscriber_default_role_capabilities(),
				'default'     => $this->get_default_role_capabilities(),
			];
		}
	}

	/**
	 * Get default capabilities for the Editor role.
	 *
	 * @since 3.11.10
	 * @access private
	 * @return array
	 */
	private function get_editor_default_role_capabilities() {
		$default_role_capabilities = $this->get_default_role_capabilities();

		$editor                      = $default_role_capabilities;
		$editor['fusion_tb_layout']  = $this->get_capability_choices( [ 'on' ] );
		$editor['fusion_tb_section'] = $this->get_capability_choices( [ 'on', 'on', 'on', 'on' ] );
		$editor['fusion_form']       = $this->get_capability_choices( [ 'on', 'on', 'on', 'on', 'on' ] );
		$editor['avada_library']     = $this->get_capability_choices( [ 'on', 'on', 'on', 'on', '', 'on' ] );

		return $editor;
	}

	/**
	 * Get default capabilities for the Subscriber role.
	 *
	 * @since 3.11.10
	 * @access private
	 * @return array
	 */
	private function get_subscriber_default_role_capabilities() {
		$subscriber = $this->get_default_role_capabilities();

		foreach ( $subscriber as $post_type => $caps ) {
			$subscriber[ $post_type ] = array_fill_keys( array_keys( $caps ), 'off' );
		}

		return $subscriber;
	}

	public function get_default_role_manager_capabilities() {
		return self::$default_role_manager_capabilities;
	}

	/**
	 * Sets the role manager capabilities from fusion settings.
	 *
	 * @since 3.9
	 * @access private
	 * @return void
	 */
	private function set_role_manager_capabilities() {
		if ( null === self::$role_manager_capabilities ) {
			$fusion_builder_settings = get_option( 'fusion_builder_settings', [] );
			$default_capabilities    = $this->get_default_role_manager_capabilities();

			if ( isset( $fusion_builder_settings['role_manager_caps'] ) ) {
				self::$role_manager_capabilities            = $fusion_builder_settings['role_manager_caps'];
				self::$role_manager_capabilities['default'] = $default_capabilities['default'];
			} else {
				self::$role_manager_capabilities = $default_capabilities;
			}
		}
	}

	/**
	 * Get role manager capabilities.
	 *
	 * @since 3.11.7
	 * @access public
	 * @return array The capabilities.
	 */
	public function get_role_manager_capabilities() {
		return self::$role_manager_capabilities;
	}

	/**
	 * Get role of current user.
	 *
	 * @since 3.11.7
	 * @access public
	 * @return array The capabilities.
	 */
	public function get_current_user_role() {
		if ( ! self::$current_user_role ) {

			if ( current_user_can( 'administrator' ) ) {
				self::$current_user_role = 'administrator';
			} elseif ( is_user_logged_in() ) {
				self::$current_user_role = 'subscriber';
				$capabilities_count      = 0;
				$user                    = wp_get_current_user();
				$user_roles              = (array) $user->roles;

				foreach ( $user_roles as $user_role ) {
					$role_data = get_role( $user_role );

					if ( isset( $role_data->capabilities ) && $capabilities_count < count( $role_data->capabilities ) ) {
						$capabilities_count      = count( $role_data->capabilities );
						self::$current_user_role = $user_role;
					}
				}
			} else {
				self::$current_user_role = 'default';
			}
		}

		return self::$current_user_role;
	}

	/**
	 * Set the needed capability based on what is saved in the role manager.
	 *
	 * @since 3.11.7
	 * @access public
	 * @param string|bool $minimum_capability The minimum capability to access. Bool if for post type registration and a few other areas.
	 * @param string      $context The context for the access.
	 * @param string      $sub_context The sub-context for the access.
	 * @return string The adjusted minimum capability.
	 */
	public function set_access_capability_based_on_role_manager( $minimum_capability, $context, $sub_context = 'dashboard_access' ) {
		$user_role = $this->get_current_user_role();
		$context   = is_null( $context ) ? $this->get_post_type() : $context;

		// Set the capability based on post types, if it is relevant for posts and pages in the source.
		if ( 'edit_' === $minimum_capability || 'publish_' === $minimum_capability ) {
			$post_type_object    = get_post_type_object( $context );
			$minimum_capability .= 'posts';

			if ( isset( $post_type_object->cap->$minimum_capability ) ) {
				$minimum_capability = $post_type_object->cap->$minimum_capability;
			}
		}

		if ( 'administrator' === $user_role ) {
			return $minimum_capability;
		}

		if ( isset( self::$role_manager_capabilities[ $user_role ][ $context ][ $sub_context ] ) ) {
			if ( 'on' === self::$role_manager_capabilities[ $user_role ][ $context ][ $sub_context ] ) {
				$minimum_capability = is_bool( $minimum_capability ) ? true : 'exist';
			} else {
				$minimum_capability = is_bool( $minimum_capability ) ? false : 'administrator';
			}
		}

		return $minimum_capability;
	}

	/**
	 * Prepares and gets the post types for the options page.
	 *
	 * @since 3.7.11
	 * @access public
	 * @return array
	 */
	public function get_post_types_for_options_page() {
		$post_type_names = $this->get_all_post_types();

		// Remove templates and elements. So that library option can be used for both.
		$post_type_names = array_diff( $post_type_names, [ 'fusion_template', 'fusion_element' ] );
		$post_types      = [];

		foreach ( $post_type_names as $post_type_name ) {

			$special_post_types = [
				'awb_global_options' => __( 'Avada Global Options', 'fusion-builder' ),
				'awb_prebuilts'      => __( 'Avada Prebuilts', 'fusion-builder' ),
				'awb_studio'         => __( 'Avada Studio', 'fusion-builder' ),
				'avada_library'      => __( 'Avada Library', 'fusion-builder' ),
			];

			if ( isset( $special_post_types[ $post_type_name ] ) ) {
				$post_type        = new stdClass();
				$post_type->label = $special_post_types[ $post_type_name ];
				$post_type->name  = $post_type_name;
			} else {
				$post_type = get_post_type_object( $post_type_name );
			}

			if ( ! is_object( $post_type ) || ! isset( $post_type->label ) || ! isset( $post_type->name ) ) {
				continue;
			}

			$post_types[ $post_type_name ] = $post_type;
		}

		return $post_types;
	}

	/**
	 * Gets the post type label including any needed prefixes.
	 *
	 * @since 3.7.11
	 * @param array $post_type The post type object.
	 * @access public
	 * @return string
	 */
	public function get_post_type_label( $post_type ) {
		$label = isset( $post_type->label ) ? $post_type->label : '';

		if ( in_array( $post_type->name, $this->get_non_avada_post_types(), true ) ) {
			$label = $post_type->label;
		} elseif ( false === strpos( $post_type->label, 'Avada' ) ) {
			$label = __( 'Avada', 'fusion-builder' ) . ' ' . str_replace( 'Custom ', '', $post_type->label );
		}

		return apply_filters( 'awb_role_manager_post_type_label', $label );
	}

	/**
	 * Gets the capability value for the options page.
	 *
	 * @since 3.11.7
	 * @access public
	 * @param string $role The user role.
	 * @param string $context The context for the access.
	 * @param string $sub_context The sub-context for the access.
	 * @return string The capability or empty string if not set.
	 */
	public function get_option_page_value( $role, $context, $sub_context = 'dashboard_access' ) {
		if ( isset( self::$role_manager_capabilities[ $role ][ $context ][ $sub_context ] ) ) {
			return self::$role_manager_capabilities[ $role ][ $context ][ $sub_context ];
		}

		return '';
	}

	/**
	 * Adds the role manager options to the builder options page
	 *
	 * @since 3.11.7
	 * @access public
	 * @return void
	 */
	public function add_role_manager_options_to_builder_options() {
		$post_types = $this->get_post_types_for_options_page();
		$roles      = get_editable_roles();

		// Remove admin role.
		unset( $roles['administrator'] );

		include FUSION_BUILDER_PLUGIN_DIR . 'inc/admin-screens/role-manager-options.php';
	}

	/**
	 * Gets current post type.
	 *
	 * @since 3.11.7
	 * @access private
	 * @return string
	 */
	private function get_post_type() {
		if ( is_admin() ) {
			$post_type = $this->get_post_type_admin();
		} elseif ( isset( $_SERVER['REQUEST_URI'] ) && ( false !== strpos( $_SERVER['REQUEST_URI'], 'fb-edit' ) || false !== strpos( $_SERVER['REQUEST_URI'], 'builder=true' ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$post_type = $this->get_post_type_live_editor();
		} elseif ( isset( $_SERVER['REQUEST_URI'] ) && false === strpos( $_SERVER['REQUEST_URI'], 'fb-edit' ) && false === strpos( $_SERVER['REQUEST_URI'], 'builder=true' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$post_type = get_post_type();
		}

		$post_type = in_array( $post_type, [ 'fusion_template', 'fusion_element' ], true ) ? 'avada_library' : $post_type;

		return apply_filters( 'awb_role_manager_current_screen_post_type', $post_type );
	}

	/**
	 * Gets post type of WP admin screens.
	 *
	 * @since 3.11.7
	 * @access private
	 * @return string
	 */
	private function get_post_type_admin() {
		global $post, $pagenow, $typenow, $current_screen;

		$post_type = '';

		if ( isset( $post->post_type ) ) {
			$post_type = $post->post_type;
		} elseif ( $typenow ) {
			$post_type = $typenow;
		} elseif ( isset( $current_screen->post_type ) ) {
			$post_type = $current_screen->post_type;
		} elseif ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) );
		} elseif ( 'edit.php' === $pagenow && '' === $typenow ) {
			$post_type = 'post';
		} else {
			$post_type = $this->get_post_type_avada_admin_screen();
		}

		return $post_type;
	}

	/**
	 * Gets post type of Avada admin screens.
	 *
	 * @since 3.11.7
	 * @access private
	 * @return string
	 */
	private function get_post_type_avada_admin_screen() {
		$post_type = '';

		$page    = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$screens = [
			'avada-layouts'         => 'fusion_tb_layout',
			'avada-layout-sections' => 'fusion_tb_section',
			'avada-off-canvas'      => 'awb_off_canvas',
			'avada-icons'           => 'fusion_icons',
			'avada-forms'           => 'fusion_form',
			'avada-form-entries'    => 'fusion_form',
			'avada-library'         => 'avada_library',
			'avada-studio'          => 'awb_studio',
		];

		$post_type = isset( $screens[ $page ] ) ? $screens[ $page ] : $post_type;

		return $post_type;
	}

	/**
	 * Gets current page post type in live editor.
	 *
	 * @since 3.9
	 * @access private
	 * @return string
	 */
	private function get_post_type_live_editor() {
		global $wp_rewrite;

		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;
		$url             = $this->get_current_url();
		$post_type       = '';
		$portfolio_slug  = $fusion_settings && '' !== $fusion_settings->get( 'portfolio_slug' ) ? $fusion_settings->get( 'portfolio_slug' ) : 'portfolio-items';
		$faqs_slug       = $fusion_settings && '' !== $fusion_settings->get( 'faq_slug' ) ? $fusion_settings->get( 'faq_slug' ) : 'faq-items';
		$post_types      = [
			'awb_off_canvas'    => 'awb_off_canvas',
			'fusion_tb_section' => 'fusion_tb_section',
			'fusion_form'       => 'fusion_form',
			'fusion_element'    => 'fusion_element',
			'fusion_template'   => 'fusion_template',
			'avada_portfolio'   => $portfolio_slug,
			'avada_faq'         => $faqs_slug,
		];

		foreach ( $post_types as $item ) {
			if ( false !== strpos( $url, $item ) ) {
				$post_type = $item;
				break;
			}
		}

		if ( empty( $post_type ) && ! is_null( $wp_rewrite ) ) {
			$post_type = get_post_type( url_to_postid( $url ) );
		}

		if ( false === $post_type && get_home_url() === $url && 'page' === get_option( 'show_on_front' ) ) {
			$post_type = 'page';
		}

		return $post_type;
	}

	/**
	 * Gets current URL.
	 *
	 * @since 3.11.7
	 * @access private
	 * @return string
	 */
	private function get_current_url() {
		$url_parts = parse_url( home_url() );
		$full_url  = $url_parts['scheme'] . '://' . $url_parts['host'] . add_query_arg( null, null );

		return $full_url;
	}

	/**
	 * Checks if user should have access to global elements and the backend builder.
	 *
	 * @since 3.11.7
	 * @access public
	 * @return void
	 */
	public function check_global_element_and_backend_builder_access() {
		$post_id   = isset( $_GET['post'] ) ? sanitize_text_field( wp_unslash( $_GET['post'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
		$post_type = $this->get_post_type();

		// Global elements access.
		if ( isset( $post_id ) && 'yes' === get_post_meta( $post_id, '_fusion_is_global', true ) && ! current_user_can( apply_filters( 'awb_role_manager_access_capability', 'edit_private_posts', 'avada_library', 'global_elements' ) ) ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 ); // phpcs:ignore WordPress.Security.EscapeOutput
		}

		// Backend builder access.
		if ( ! current_user_can( apply_filters( 'awb_role_manager_access_capability', 'edit_', $post_type, 'backed_builder_edit' ) ) ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
}

/**
 * Instantiates the AWB_Role_Manager class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.9
 * @return object AWB_Role_Manager
 */
function AWB_Role_Manager() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Role_Manager::get_instance();
}
AWB_Role_Manager();
