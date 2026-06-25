<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WPMUDEV_HUB_Permissions {
	/**
	 * @var null|self
	 */
	protected static $instance = null;

	/**
	 * @return self
	 */
	public static function get_instance() {
		/**
		 * Filter Hub Hosting Reseller adapter
		 *
		 * @param WPMUDEV_HUB_Permissions|null $instance
		 *
		 * @since 2.0.0
		 */
		self::$instance = apply_filters( 'wpmudev_hub_permissions_adapter', self::$instance );
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
	}

	public function is_only_selected_admins() {
		/**
		 * Filters whether is only selected admins allowed to manage
		 *
		 * @param bool $is_only_selected_admin current state whether is only selected admins allowed
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'wpmudev_hub_manage_manage_is_only_selected_admins', filter_var( get_site_option( 'wpmudev_hub_manage_is_only_selected_admins' ), FILTER_VALIDATE_BOOLEAN ) );
	}

	public function set_only_selected_admins( $only_selected = true ) {
		return update_site_option( 'wpmudev_hub_manage_is_only_selected_admins', $only_selected );
	}

	public function is_allowed_user( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		}

		if ( ! $user_id ) {
			return false;
		}

		$user_id = (int) $user_id;

		// if only selected admins mode
		if ( $this->is_only_selected_admins() ) {
			// make sure it exists
			$allowed_admin_ids = $this->get_allowed_admin_ids();
			if ( ! in_array( $user_id, $allowed_admin_ids, true ) ) {
				return false;
			}
		}

		$user_data = function_exists( 'get_userdata' ) ? get_userdata( $user_id ) : false;
		if ( ! $user_data ) {
			return false;
		}

		// cap check for all
		if ( ! user_can( $user_id, WPMUDEV_HUB_Plugin::get_manage_plugin_cap() ) ) {
			return false;
		}

		// multisite, the user has to be super-admin
		if ( WPMUDEV_HUB_Plugin::is_multisite() ) {
			if ( ! is_super_admin( $user_id ) ) {
				return false;
			}
		} else {
			// single site, user role has to be administrator
			$roles = $user_data->roles;
			$roles = is_array( $roles ) ? $roles : array();
			if ( ! in_array( 'administrator', $roles, true ) ) {
				return false;
			}
		}

		return true;
	}

	public function get_allowed_admin_ids() {
		$data = get_site_option( 'wpmudev_hub_manage_selected_admin_ids', array() );
		$data = is_array( $data ) ? $data : array();

		/**
		 * Filters selected / allowed
		 *
		 * @param array $admin_ids current allowed admin ids
		 *
		 * @since 2.0.0
		 */
		return apply_filters( 'wpmudev_hub_manage_manage_selected_admin_ids', array_map( 'intval', $data ) );
	}

	public function get_allowed_admin_users( $current_user_id = null ) {
		if ( ! $current_user_id ) {
			$current_user_id = get_current_user_id();
		}
		$current_user_id = (int) $current_user_id;

		if ( ! $this->is_only_selected_admins() ) {
			return array();
		}

		$allowed_admin_users = array();
		$allowed_admin_ids   = $this->get_allowed_admin_ids();
		foreach ( $allowed_admin_ids as $key => $allowed_admin_id ) {
			// no longer allowed
			if ( ! $this->is_allowed_user( $allowed_admin_id ) ) {
				unset( $allowed_admin_ids[ $key ] );
			}
		}

		$allowed_admin_ids = array_values( $allowed_admin_ids );
		foreach ( $allowed_admin_ids as $allowed_admin_id ) {
			$user_data             = get_userdata( $allowed_admin_id );
			$user_data_id          = (int) ( isset( $user_data->ID ) ? $user_data->ID : 0 );
			$allowed_admin_users[] = array(
				'id'           => $user_data_id,
				'name'         => $user_data ? ( isset( $user_data->display_name ) ? $user_data->display_name : '' ) : '',
				'email'        => $user_data ? ( isset( $user_data->user_email ) ? $user_data->user_email : '' ) : '',
				'avatar_url'   => $user_data ? ( get_avatar_url( $user_data_id, array( 'size' => 32 ) ) ) : '',
				'is_removable' => $user_data_id !== $current_user_id,
			);
		}

		return $allowed_admin_users;
	}

	public function set_allowed_admin_ids( $admin_ids ) {
		$admin_ids = array_map( 'intval', $admin_ids );
		$admin_ids = array_unique( $admin_ids );
		// validate
		foreach ( $admin_ids as $user_id ) {
			$user_data = get_userdata( $user_id );
			if ( ! $user_data ) {
				/* translators: %d: User ID. */
				return new WP_Error( 'user_id_not_found', sprintf( __( 'User with id %d not found.', 'thc' ), $user_id ), array( 'status' => 400 ) );
			}

			// role has to be administrator
			$roles = $user_data->roles;
			$roles = is_array( $roles ) ? $roles : array();

			// single site, role has to be administrator
			if ( ! WPMUDEV_HUB_Plugin::is_multisite() ) {
				if ( ! in_array( 'administrator', $roles, true ) ) {
					return new WP_Error(
						'invalid_user_role',
						/* translators: 1: User ID, 2: Role id/name. */
						sprintf( __( 'User with id %1$d does not have %2$s role.', 'thc' ), $user_id, 'administrator' ),
						array( 'status' => 400 )
					);
				}
			}

			if ( ! user_can( $user_data->ID, WPMUDEV_HUB_Plugin::get_manage_plugin_cap() ) ) {
				return new WP_Error(
					'invalid_user_cap',
					/* translators: 1: User ID, 2: Capability id/name. */
					sprintf( __( 'User with id %1$d does not have %2$s capability.', 'thc' ), $user_id, WPMUDEV_HUB_Plugin::get_manage_plugin_cap() ),
					array( 'status' => 400 )
				);
			}

			// super admin
			if ( WPMUDEV_HUB_Plugin::is_multisite() ) {
				if ( ! is_super_admin( $user_id ) ) {
					return new WP_Error(
						'not_super_admin',
						/* translators: 1: User ID, 2: Capability id/name. */
						sprintf( __( 'User with id %1$d is not Super Admin. Multisite only allow Super Admin to be added.', 'thc' ), $user_id, WPMUDEV_HUB_Plugin::get_manage_plugin_cap() ),
						array( 'status' => 400 )
					);
				}
			}
		}

		return update_site_option( 'wpmudev_hub_manage_selected_admin_ids', $admin_ids );
	}

	public function reset_allowed_admin_ids() {
		update_site_option( 'wpmudev_hub_manage_selected_admin_ids', array() );
	}
}