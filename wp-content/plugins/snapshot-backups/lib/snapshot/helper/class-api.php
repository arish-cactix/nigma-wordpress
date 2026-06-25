<?php // phpcs:ignore
/**
 * API helper class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

use WPMUDEV\Snapshot4\Exceptions\Dashboard_Api_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Login_Method_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Password_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Username_Exception;
use WPMUDEV\Snapshot4\Exceptions\WP_Error_Exception;
use WPMUDEV_Dashboard;

/**
 * Helper class
 */
class Api {

	const SNAPSHOT_PROJECT_ID = 3760011;

	/**
	 * Gets the API key using Dashboard's method.
	 *
	 * @return string
	 */
	public static function get_api_key() {
		global $wpmudev_un;

		if ( ! is_object( $wpmudev_un ) && class_exists( 'WPMUDEV_Dashboard' ) && method_exists( 'WPMUDEV_Dashboard', 'instance' ) ) {
			$wpmudev_un = \WPMUDEV_Dashboard::instance();
		}

		if ( is_object( $wpmudev_un ) && method_exists( $wpmudev_un, 'get_apikey' ) ) {
			$api_key = $wpmudev_un->get_apikey();
		} elseif ( class_exists( 'WPMUDEV_Dashboard' ) && is_object( \WPMUDEV_Dashboard::$api ) && method_exists( \WPMUDEV_Dashboard::$api, 'get_key' ) ) {
			$api_key = \WPMUDEV_Dashboard::$api->get_key();
		} else {
			$api_key = '';
		}

		return $api_key;
	}

	/**
	 * Get the site's url
	 *
	 * @return string
	 */
	public static function get_site_url() {
		global $wpmudev_un;

		if ( ! is_object( $wpmudev_un ) && class_exists( 'WPMUDEV_Dashboard' ) && method_exists( 'WPMUDEV_Dashboard', 'instance' ) ) {
			$wpmudev_un = \WPMUDEV_Dashboard::instance();
		}

		if ( is_object( $wpmudev_un ) && method_exists( $wpmudev_un, 'network_site_url' ) ) {
			$site_url = $wpmudev_un->network_site_url();
		} elseif ( class_exists( 'WPMUDEV_Dashboard' ) && is_object( \WPMUDEV_Dashboard::$api ) && method_exists( \WPMUDEV_Dashboard::$api, 'get_key' ) ) {
			$site_url = \WPMUDEV_Dashboard::$api->network_site_url();
		} else {
			$site_url = ( is_multisite() ) ? network_site_url() : site_url();
		}

		return $site_url;
	}

	/**
	 * Gets site's id.
	 *
	 * @return int
	 */
	public static function get_site_id() {
		$site_id = get_site_option( 'wpmudev_site_id' );

		if ( empty( $site_id ) ) {
			global $wpmudev_un;

			if ( ! is_object( $wpmudev_un ) && class_exists( 'WPMUDEV_Dashboard' ) && method_exists( 'WPMUDEV_Dashboard', 'instance' ) ) {
				$wpmudev_un = \WPMUDEV_Dashboard::instance();
			}

			if ( is_object( $wpmudev_un ) && method_exists( $wpmudev_un, 'get_site_id' ) ) {
				$site_id = $wpmudev_un->get_site_id();
			} elseif ( class_exists( 'WPMUDEV_Dashboard' ) && is_object( \WPMUDEV_Dashboard::$api ) && method_exists( \WPMUDEV_Dashboard::$api, 'get_site_id' ) ) {
				$site_id = \WPMUDEV_Dashboard::$api->get_site_id();
			} else {
				$site_id = '';
			}
		}

		if ( empty( $site_id ) ) {
			Log::error( __( 'The site doesn\'t seem to have an ID assigned. Try login into the WPMU DEV Dashboard again.', 'snapshot' ) );
		}

		return apply_filters( 'wp_snapshot_site_id', $site_id );
	}

	/**
	 * Returns WPMUDEV Dashboard API object
	 *
	 * @return \WPMUDEV_Dashboard_Api|null
	 */
	public static function get_dashboard_api() {
		if ( class_exists( 'WPMUDEV_Dashboard' ) && ! empty( \WPMUDEV_Dashboard::$api ) ) {
			return \WPMUDEV_Dashboard::$api;
		}
		return null;
	}

	/**
	 * Returns user profile
	 *
	 * @return array|null
	 */
	public static function get_dashboard_profile() {
		$api = self::get_dashboard_api();
		if ( $api && is_callable( array( $api, 'get_profile' ) ) ) {
			$result = $api->get_profile();
			return isset( $result['profile'] ) ? $result['profile'] : null;
		}
		return null;
	}

	/**
	 * Returns user profile username (email)
	 *
	 * @return string
	 */
	public static function get_dashboard_profile_username() {
		$profile = self::get_dashboard_profile();
		return isset( $profile['user_name'] ) ? $profile['user_name'] : '';
	}

	/**
	 * Checks password.
	 *
	 * @throws Invalid_Username_Exception Invalid User name.
	 * @throws Dashboard_Api_Exception Dashboard API is not accessible.
	 * @throws WP_Error_Exception WP_Error_Exception exception.
	 * @throws Invalid_Login_Method_Exception Invalid login method.
	 * @throws Invalid_Password_Exception Invalid password.
	 *
	 * @param string $password Password to check.
	 * @return boolean
	 */
	public static function check_password( $password ) {

		$username = self::get_dashboard_profile_username();

		if ( ! $username ) {
			throw new Invalid_Username_Exception( esc_html__( 'Invalid Username', 'snapshot' ) );
		}

		$api = self::get_dashboard_api();
		if ( ! $api || null === $api ) {
			throw new Dashboard_Api_Exception( esc_html__( 'Dashboard API is not accessible.', 'snapshot' ) );
		}

		$data     = array(
			'username'     => $username,
			'password'     => $password,
			'domain'       => esc_url( get_site_url() ),
			'redirect_url' => network_admin_url(),
		);
		$response = $api->call( 'site-authenticate', $data, 'POST', array( 'redirection' => 0 ) );
		if ( is_wp_error( $response ) ) {
			throw new WP_Error_Exception( esc_html( $response->get_error_message() ) );
		}

		$location = wp_remote_retrieve_header( $response, 'Location' );

		$params = array();
		parse_str( wp_parse_url( $location, PHP_URL_QUERY ), $params );
		if ( ! isset( $params['api_error'] ) && ( ! empty( $params['set_apikey'] ) || ! empty( $params['user_apikey'] ) ) ) {
			return true;
		}

		if ( isset( $params['auth_error'] ) && 'google_linked' === $params['auth_error'] ) {
			throw new Invalid_Login_Method_Exception( esc_html__( 'Invalid login method', 'snapshot' ) );
		}

		if ( isset( $params['api_error'] ) && 'auth' === $params['api_error'] ) {
			throw new Invalid_Password_Exception( esc_html__( 'Invalid password', 'snapshot' ) );
		}

		// Tried handling all the cases above. Anyway fail at this point by returning false.
		return false;
	}

	/**
	 * Verifies WPMU DEV password
	 *
	 * @param string $password User's password.
	 * @return boolean
	 */
	public static function verify_password( $password ) {
		$username = self::get_dashboard_profile_username();
		if ( ! $username ) {
			return false;
		}

		$api = self::get_dashboard_api();
		if ( ! $api ) {
			return false;
		}

		$data = array(
			'username'     => $username,
			'password'     => $password,
			'redirect_url' => network_admin_url(),
			'domain'       => esc_url( get_site_url() ),
		);

		$response = $api->call( 'site-authenticate', $data, 'POST', array( 'redirection' => 0 ) );

		$location = wp_remote_retrieve_header( $response, 'Location' );
		$params   = array();

		parse_str( wp_parse_url( $location, PHP_URL_QUERY ), $params );

		if ( ! empty( $params['set_apikey'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns WPMU DEV membership data
	 *
	 * @return array|null
	 */
	public static function get_dashboard_membership_data() {
		$api = self::get_dashboard_api();
		if ( $api && is_callable( array( $api, 'get_membership_data' ) ) ) {
			$result = $api->get_membership_data();
			return $result;
		}
		return null;
	}

	/**
	 * Returns WPMU DEV membership type
	 *
	 * @return string
	 */
	public static function get_dashboard_membership_type() {
		$api = self::get_dashboard_api();
		if ( $api && is_callable( array( $api, 'get_membership_type' ) ) ) {
			$result = $api->get_membership_type();
			$result = strval( apply_filters( 'snapshot_custom_membership_type', $result ) );
			return $result;
		}
		return null;
	}

	/**
	 * Returns array or numeric ids of projects avaiable on plan
	 *
	 * @return array
	 */
	public static function get_dashboard_membership_project_ids() {
		$api = self::get_dashboard_api();
		if ( $api && is_callable( array( $api, 'get_membership_projects' ) ) ) {
			$result = $api->get_membership_projects();
			if ( is_numeric( $result ) ) {
				// "single" plan.
				return array( $result );
			} elseif ( is_array( $result ) ) {
				// "unit" plan.
				return $result;
			}
		}
		return array();
	}

	/**
	 * Checks if member is on WPMUDEV standalone hosting plan
	 *
	 * @return boolean
	 */
	public static function is_standalone_hosting() {
		$api = self::get_dashboard_api();

		if ( $api && is_callable( array( $api, 'is_standalone_hosting_plan' ) ) ) {
			if ( $api->is_standalone_hosting_plan() ) {
				return true;
			}

			return false;
		}

		return false;
	}

	/**
	 * Check if user can access Snapshot.
	 *
	 * @return boolean
	 */
	public static function user_can_access() {
		if ( class_exists( 'WPMUDEV_Dashboard' ) && method_exists( WPMUDEV_Dashboard::$upgrader, 'user_can_install' ) ) {
			return WPMUDEV_Dashboard::$upgrader->user_can_install( self::SNAPSHOT_PROJECT_ID, true );
		}

		return false;
	}

	/**
	 * Returns true if the user needs to activate the membership
	 *
	 * @return boolean
	 */
	public static function need_reactivate_membership() {
		$membership_type = self::get_dashboard_membership_type();
		// Expired membership.
		return 'free' === $membership_type;
	}

	/**
	 * Returns if current member is allowed to set the daily & weekly backup schedules.
	 *
	 * @return boolean
	 */
	public static function has_daily_backup_schedules_access() {
		$api = self::get_dashboard_api();
		if ( $api && is_callable( array( $api, 'has_access' ) ) ) {
			return $api->has_access( 'security-backups-schedule' );
		}
		return false;
	}

	/**
	 * Checks if user can change schedules
	 *
	 * @return boolean
	 */
	public static function can_change_schedules() {
		return self::has_daily_backup_schedules_access();
	}

	/**
	 * Returns tru if the user is allowed to use Snapshot Pro features.
	 *
	 * @deprecated 4.11.2
	 *
	 * @return boolean
	 */
	public static function is_pro_account() {
		$membership_type = self::get_dashboard_membership_type();

		if ( 'free_hub' === $membership_type ) {
			return false;
		} elseif ( 'full' === $membership_type ) {
			return true;
		} elseif ( 'unit' === $membership_type ) {
			return self::has_daily_backup_schedules_access();
		} elseif ( 'single' === $membership_type ) {
			return in_array( self::SNAPSHOT_PROJECT_ID, self::get_dashboard_membership_project_ids(), true );
		}

		return false;
	}

	/**
	 * Checks the user access.
	 *
	 * @return boolean
	 */
	public static function is_pro() {
		return self::has_daily_backup_schedules_access();
	}

	/**
	 * Get all the authorized users allowed to access dashboard plugin.
	 *
	 * @return array
	 */
	public static function get_dashboard_authorized_users() {
		$user_ids = array();
		if ( class_exists( 'WPMUDEV_Dashboard' ) && method_exists( WPMUDEV_Dashboard::$site, 'user_can_install' ) ) {
			$user_ids =  WPMUDEV_Dashboard::$site->get_allowed_users( true );
		}

		return $user_ids;
	}
}