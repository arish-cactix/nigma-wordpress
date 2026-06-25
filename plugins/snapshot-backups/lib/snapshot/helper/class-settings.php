<?php // phpcs:ignore
/**
 * Snapshot helpers: settings helper class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

use WPMUDEV\Snapshot4\Model\Env;

/**
 * Settings helper class
 */
class Settings {

	/**
	 * Returns true if user has selected to keep their settings upon uninstall.
	 *
	 * @return bool
	 */
	public static function get_remove_settings() {
		return boolval( get_site_option( 'snapshot_remove_on_uninstall' ) );
	}

	/**
	 * Returns true if the "Welcome modal" was viewed
	 *
	 * @return bool
	 */
	public static function get_started_seen() {
		return boolval( get_site_option( 'snapshot_started_seen' ) );
	}

	/**
	 * Mark "Welcome modal" as viewed
	 *
	 * @param bool $value Set True to mark as viewed.
	 */
	public static function set_started_seen( $value ) {
		update_site_option( 'snapshot_started_seen', intval( boolval( $value ) ) );
	}

	/**
	 * Returns true if the "Welcome modal" was viewed (even in a previous install)
	 *
	 * @return bool
	 */
	public static function get_started_seen_persistent() {
		return boolval( get_site_option( 'snapshot_started_seen_persistent' ) );
	}

	/**
	 * Mark "Welcome modal" as viewed (even for later installs)
	 *
	 * @param bool $value Set True to mark as viewed.
	 */
	public static function set_started_seen_persistent( $value ) {
		update_site_option( 'snapshot_started_seen_persistent', intval( boolval( $value ) ) );
	}

	/**
	 * Returns true if filelist log must be more detailed.
	 *
	 * @return bool
	 */
	public static function get_filelist_log_verbose() {
		return boolval( defined( 'SNAPSHOT4_FILELIST_LOG_VERBOSE' ) && SNAPSHOT4_FILELIST_LOG_VERBOSE );
	}

	/**
	 * Returns true if zipstream log must be more detailed.
	 *
	 * @return bool
	 */
	public static function get_zipstream_log_verbose() {
		return boolval( defined( 'SNAPSHOT4_FILE_ZIPSTREAM_LOG_VERBOSE' ) && SNAPSHOT4_FILE_ZIPSTREAM_LOG_VERBOSE );
	}

	/**
	 * Returns true if output buffer must be flashed after every zipstream write.
	 *
	 * @return bool
	 */
	public static function get_zipstream_flush_buffer() {
		return boolval( defined( 'SNAPSHOT4_ZIPSTREAM_FLUSH_BUFFER' ) && SNAPSHOT4_ZIPSTREAM_FLUSH_BUFFER );
	}

	/**
	 * Returns true if "manual" restore mode is enabled.
	 *
	 * @return bool
	 */
	public static function get_manual_restore_mode() {
		return boolval( defined( 'SNAPSHOT4_MANUAL_RESTORE_MODE' ) && SNAPSHOT4_MANUAL_RESTORE_MODE );
	}

	/**
	 * Returns API URL of the Service. By default it's a "prod" environment URL.
	 *
	 * @return string
	 */
	public static function get_service_api_url() {
		return SNAPSHOT4_SERVICE_API_URL;
	}

	/**
	 * Returns email settings.
	 *
	 * @return array
	 */
	public static function get_email_settings() {
		$default = array(
			'on_fail_send'       => false,
			'on_fail_recipients' => array(),
		);

		$email_settings = get_site_option( 'snapshot_email_settings', $default );

		if ( ! isset( $email_settings['notify_on_fail'] ) ) {
			$email_settings['notify_on_fail'] = true;
		}
		if ( ! isset( $email_settings['notify_on_complete'] ) ) {
			$email_settings['notify_on_complete'] = false;
		}

		if ( empty( $email_settings['on_fail_recipients'] ) ) {
			$email_settings['on_fail_recipients'] = array(
				array(
					'name'  => wp_get_current_user()->display_name,
					'email' => get_site_option( 'admin_email' ),
				),
			);
		}

		$on_fail_recipients_count = count( $email_settings['on_fail_recipients'] );

		$result['notice_type'] = $email_settings['on_fail_send'] && $on_fail_recipients_count > 0 ? 'success' : null;
		if ( 'success' !== $result['notice_type'] ) {
			$result['notice_text'] = __( 'Email notifications are currently disabled.', 'snapshot' );
		} elseif ( 1 === $on_fail_recipients_count ) {
			$result['notice_text'] = __( 'Email notifications are enabled for 1 recipient.', 'snapshot' );
		} else {
			/* translators: %d - Number of email recipients */
			$result['notice_text'] = sprintf( __( 'Email notifications are enabled for %d recipients.', 'snapshot' ), $on_fail_recipients_count );
		}

		$result['email_settings'] = $email_settings;

		return $result;
	}

	/**
	 * Update email settings.
	 *
	 * @param array $fields Update this params, e.g. on_fail_send, on_fail_recipients.
	 */
	public static function update_email_settings( array $fields ) {
		$settings       = self::get_email_settings();
		$email_settings = $settings['email_settings'];
		foreach ( $fields as $field => $value ) {
			$email_settings[ $field ] = $value;
		}
		if ( isset( $email_settings['on_fail_recipients'] ) && ! count( $email_settings['on_fail_recipients'] ) ) {
			$email_settings['on_fail_send'] = false;
		}
		if ( isset( $email_settings['notify_on_fail'] ) && ! $email_settings['notify_on_fail'] &&
			isset( $email_settings['notify_on_complete'] ) && ! $email_settings['notify_on_complete'] ) {
			$email_settings['notify_on_fail'] = true;
			$email_settings['on_fail_send']   = false;
		}
		update_site_option( 'snapshot_email_settings', $email_settings );
	}

	/**
	 * Returns x.y.z plugin's version
	 *
	 * @return string
	 */
	public static function get_plugin_patch_version() {
		return explode( '-', SNAPSHOT_BACKUPS_VERSION )[0];
	}

	/**
	 * Returns true if the "What's new" modal was viewed
	 *
	 * @return bool
	 */
	public static function get_whats_new_seen() {
		$seen_version  = get_site_option( 'snapshot_whats_new_seen' );
		$patch_version = self::get_plugin_patch_version();
		if ( ! $seen_version ) {
			// Fresh install.
			return false;
		}
		return version_compare( $patch_version, $seen_version ) <= 0;
	}

	/**
	 * Mark "What's new" modal as viewed
	 *
	 * @param string $value Set seen version, null = current version.
	 */
	public static function set_whats_new_seen( $value = null ) {
		if ( is_null( $value ) ) {
			$value = self::get_plugin_patch_version();
		}
		update_site_option( 'snapshot_whats_new_seen', $value );
	}

	/**
	 * Checks whether the WPMU DEV branding is hidden
	 *
	 * @return bool
	 */
	public static function is_branding_hidden() {
		return (bool) apply_filters(
			'wpmudev_branding_hide_branding',
			false
		);
	}

	/**
	 * Checks whether the WPMU DEV branding docs is hidden
	 *
	 * @return bool
	 */
	public static function is_branding_docs_hidden() {
		$dashboard = class_exists( '\WPMUDEV_Dashboard' ) ? \WPMUDEV_Dashboard::instance() : null;
		return is_null( $dashboard ) ? false : (bool) $dashboard::$settings->get( 'doc_links_enabled', 'whitelabel' );
	}

	/**
	 * Gets plugin custom name if applicable.
	 *
	 * @return string
	 */
	public static function get_brand_name() {
		static $value = null;
		if ( is_null( $value ) ) {
			$default   = __( 'WPMU DEV', 'snapshot' );
			$dashboard = class_exists( '\WPMUDEV_Dashboard' ) ? \WPMUDEV_Dashboard::instance() : null;
			if ( ! self::is_branding_hidden() || is_null( $dashboard ) || ! (bool) $dashboard::$settings->get( 'labels_enabled', 'whitelabel' ) ) {
				$value = $default;
			} else {
				$labels = $dashboard::$settings->get( 'labels_config', 'whitelabel' );
				if ( isset( $labels['3760011'] ) && isset( $labels['3760011']['name'] ) ) {
					$value = empty( $labels['3760011']['name'] ) ? $default : $labels['3760011']['name'];
				} else {
					$value = $default;
				}
			}
			self::get_icon_details();
		}

		return $value;
	}

	/**
	 * Gets plugin custom icon details if applicable.
	 * returns array comprising icon_class or icon_url.
	 *
	 * @return array
	 */
	public static function get_icon_details() {
		static $icon_details = null;
		if ( is_null( $icon_details ) ) {
			$dashboard = class_exists( '\WPMUDEV_Dashboard' ) ? \WPMUDEV_Dashboard::instance() : null;
			if ( ! self::is_branding_hidden() || is_null( $dashboard ) || ! (bool) $dashboard::$settings->get( 'labels_enabled', 'whitelabel' ) ) {
				$icon_details = array(
					'icon_class' => 'sui-icon-wpmudev-logo',
				);
			} else {
				$labels = $dashboard::$settings->get( 'labels_config', 'whitelabel' );
				if ( isset( $labels['3760011'] ) && isset( $labels['3760011']['icon_type'] ) ) {
					switch ( $labels['3760011']['icon_type'] ) {
						case 'dashicon':
							$icon_details = array(
								'icon_class' => 'dashicons-before dashicons-' . $labels['3760011']['icon_class'],
							);
							break;
						case 'none':
							$icon_details = array(
								'icon_class' => 'sui-no-icon',
							);
							break;
						case 'link':
							$icon_details = array(
								'icon_url' => $labels['3760011']['icon_url'],
							);
							break;
						case 'upload':
							$upload_url   = wp_get_attachment_thumb_url( $labels['3760011']['thumb_id'] );
							$icon_details = array(
								'icon_url' => $upload_url,
							);
							break;
						default:
							$icon_details = array(
								'icon_class' => 'sui-icon-wpmudev-logo',
							);
							break;
					}//end switch
				} else {
					$icon_details = array(
						'icon_class' => 'sui-icon-wpmudev-logo',
					);
				}//end if
			}//end if
		}//end if
		return $icon_details;
	}

	/**
	 * Returns true if "Docs, Tutorials & Products" is "Hide"
	 *
	 * @return bool
	 */
	public static function get_branding_hide_doc_link() {
		static $value = null;

		if ( is_null( $value ) ) {
			$value = boolval( apply_filters( 'wpmudev_branding_hide_doc_link', false ) );
		}

		return $value;
	}

	/**
	 * Allows the user to delete a backup for the current session
	 *
	 * @param bool $value true - allow, false - deny.
	 */
	public static function allow_delete_backup( $value = true ) {
		$token = sha1( wp_get_session_token() );
		set_transient( "snapshot_allow_delete_backup_$token", intval( boolval( $value ) ), 86400 );
	}

	/**
	 * Returns true if the user in the current session is allowed to delete a backup
	 *
	 * @return bool
	 */
	public static function can_delete_backup() {
		if ( Env::is_phpunit_test() ) {
			// Skip checking during unit test.
			return true;
		}

		$extra_step = get_transient( 'snapshot_extra_security_step' );
		if ( false !== $extra_step && 0 === intval( $extra_step ) ) {
			return true;
		}

		$token = sha1( wp_get_session_token() );
		$value = get_transient( "snapshot_allow_delete_backup_$token" );
		return boolval( $value );
	}

	/**
	 * Returns true if the Tutorials Slider was viewed
	 *
	 * @return bool
	 */
	public static function get_snapshot_tutorials_seen() {
		$value = get_site_option( 'snapshot_tutorials_slider_seen' );
		return boolval( $value );
	}

	/**
	 * Mark the Tutorials Slider as viewed
	 *
	 * @param bool $value true - set as viewed.
	 */
	public static function set_snapshot_tutorials_seen( $value = true ) {
		if ( $value ) {
			update_site_option( 'snapshot_tutorials_slider_seen', 1 );
		} else {
			delete_site_option( 'snapshot_tutorials_slider_seen' );
		}
	}

	/**
	 * Updates the database build method
	 *
	 * @param string $type        Database build method. Must be mysqldump|php_code.
	 */
	public static function set_db_build_method( $type = 'php_code' ) {
		$build_methods = array( 'php_code', 'mysqldump' );
		if ( ! in_array( $type, $build_methods, true ) ) {
			return false;
		}
		update_site_option( 'snapshot_database_build_type', $type );
	}

	/**
	 * Get the database build method.
	 *
	 * @return string
	 */
	public static function get_db_build_method() {
		return get_site_option( 'snapshot_database_build_type' );
	}
}