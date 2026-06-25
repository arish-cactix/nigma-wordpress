<?php // phpcs:ignore
/**
 * Snapshot models: WPMU Settings Model
 *
 * @package snanpshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV_Dashboard;
/**
 * Settings model class.
 */
class Settings extends Model {

	/**
	 * Get WPMU Dev authenticated user email.
	 *
	 * @return string
	 */
	public static function get_auth_user_email() {
		global $wpmudev_un;

		if ( ! is_object( $wpmudev_un ) && class_exists( 'WPMUDEV_Dashboard' ) && method_exists( 'WPMUDEV_Dashboard', 'instance' ) ) {
			$wpmudev_un = WPMUDEV_Dashboard::instance();
		}

		$email = $wpmudev_un::$settings->get( 'auth_user', 'general' );

		if ( ! $email ) {
			$email = get_site_option( 'wdp_un_auth_user' );
		}

		return $email;
	}
}