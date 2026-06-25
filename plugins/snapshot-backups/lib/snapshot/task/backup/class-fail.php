<?php // phpcs:ignore
/**
 * Failed backup email notifications.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Template;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;

/**
 * Finish backup task class
 */
class Fail extends Task {

	const ERROR_HUB_INFO_RESPONDED_INVALID_URI      = 'snapshot_failed_HubInfoRespondedInvalidURI';
	const ERROR_HUB_INFO_RESPONDED_ERROR            = 'snapshot_failed_HubInfoRespondedError';
	const ERROR_USER_INFO_NOT_RETURN                = 'snapshot_failed_UserInfoNotReturn';
	const ERROR_STORAGE_LIMIT                       = 'snapshot_failed_storage_limit';
	const ERROR_FETCH_FILESLIST                     = 'snapshot_failed_FetchFileslist';
	const ERROR_SITE_NOT_RESPONDED_ZIPSTREAM_ERROR  = 'snapshot_failed_SiteNotRespondedZipstreamError';
	const ERROR_ZIPSTREAM_FILE_MISSING              = 'snapshot_failed_ZipstreamFileMissing';
	const ERROR_SITE_NOT_RESPONDED_LARGE_FILE_ERROR = 'snapshot_failed_SiteNotRespondedLargeFileError';
	const ERROR_WRAPPER_DBLIST_FETCH                = 'snapshot_failed_Wrapper_DBlistFetch';
	const ERROR_WRAPPER_TABLE_ZIPSTREAM             = 'snapshot_failed_Wrapper_TableZipstream';
	const ERROR_TOO_LARGE_TABLE_HANGED              = 'snapshot_failed_TooLargeTableHanged';
	const ERROR_SITE_NOT_RESPONDED_ERROR            = 'snapshot_failed_SiteNotRespondedError';
	const ERROR_UNKNOWN_ERROR                       = 'snapshot_failed_UnknownError';
	const ERROR_GENERIC_ERROR                       = 'snapshot_failed_genericError';
	const ERROR_NONCE_FAILED                        = 'snapshot_failed_nonce_failed';
	const ERROR_EXPORT_FAILED                       = 'export_failed';

	const URL_CONTACT_SUPPORT     = 'https://wpmudev.com/hub2/support?utm_source=snapshot&utm_medium=email&utm_campaign=snapshot-email-get-support#get-support';
	const URL_ADD_STORAGE_SPACE   = 'https://wpmudev.com/hub/account/?utm_source=snapshot&utm_medium=email&utm_campaign=snapshot-email-add-storage#dash2-modal-add-storage';
	const URL_GUIDE_BACKUP_FAILED = 'https://wpmudev.com/docs/wpmu-dev-plugins/snapshot-4-0/?utm_source=snapshot&utm_medium=email&utm_campaign=snapshot-email-snapshot-failed#backup-failed';

	/**
	 * Send email notifications when a backup fails
	 *
	 * @param array $args Task args.
	 */
	public function apply( $args = array() ) {
		foreach ( $args['recipients'] as $recipient ) {
			$this->send(
				$recipient['email'],
				$recipient['name'],
				$args['service_error'],
				$args['timestamp'],
				$args['backup_type'],
				$args['backup_id'],
				isset( $args['error_message'] ) ? $args['error_message'] : null,
				$args['backup']
			);
		}
	}

	/**
	 * Send email to specified recipient when a backup fails
	 *
	 * @param string $email             Recipient email address.
	 * @param string $name              Recipient first name.
	 * @param string $service_error     Service's backup error message.
	 * @param int    $timestamp         Error time.
	 * @param string $backup_type       Type of backup ("scheduled" or "manual").
	 * @param string $backup_id         Backup ID.
	 * @param string $error_message     Custom error message.
	 * @param array  $backup            Additional backup info.
	 */
	private function send( $email, $name, $service_error, $timestamp, $backup_type, $backup_id, $error_message = null, $backup = array() ) {
		$site_host_html = wp_parse_url( get_site_url(), PHP_URL_HOST );

		$dt = new \DateTime();
		$dt->setTimestamp( $timestamp );
		$dt->setTimezone( wp_timezone() );
		$time_human = $dt->format( 'd-M-Y H:i:s' );

		/* translators: %s - website URL */
		$subject  = sprintf( __( 'The backup for %s failed to create.', 'snapshot' ), $site_host_html );
		$site_url = get_site_url();
		$site     = wp_parse_url( $site_url, PHP_URL_HOST );

		$params = array(
			'plugin_custom_name' => Settings::get_brand_name(),
			'name'               => $name,
			'error1'             => "[$time_human]",
			/* translators: %s - Service's backup error message */
			'error2'             => sprintf( __( 'ERROR: %s', 'snapshot' ), ( $error_message ? $error_message : $service_error ) ),
			'subject'            => $subject,
			'site'               => $site,
			'site_url'           => $site_url,
		);

		$params += self::get_texts( $service_error, $backup_type, $backup_id );

		switch ( $service_error ) {
			case self::ERROR_STORAGE_LIMIT:
				$params['storage_info'] = isset( $backup['storage_info'] ) ? (array) $backup['storage_info'] : array();
				$tpl                    = 'mail/backup-fail/storage-limit';
				break;

			default:
				$tpl = 'mail/backup-fail';
		}

		$template = new Template();
		ob_start();
		$template->render( $tpl, $params );
		$message = ob_get_clean();

		$from = 'noreply@' . $site_host_html;
		/* translators: %s - Brand name */
		$from_name   = sprintf( __( '%s Team', 'snapshot' ), Settings::get_brand_name() );
		$from_header = "From: $from_name <$from>";

		$result = wp_mail( $email, $subject, $message, array( 'Content-Type: text/html', $from_header ) );
		if ( ! $result ) {
			/* translators: %s - "mail to" address */
			Log::error( sprintf( __( 'Unable to send email to %s', 'snapshot' ), $email ), array(), $backup_id );
		}
	}

	/**
	 * Returns texts for email depending on $service_error and $backup_type
	 *
	 * @param string $service_error Service's backup error message.
	 * @param string $backup_type   Type of backup ("scheduled" or "manual").
	 * @param string $backup_id     Backup ID.
	 * @return array
	 */
	private static function get_texts( $service_error, $backup_type, $backup_id ) {
		$site_host_html = esc_attr( wp_parse_url( get_site_url(), PHP_URL_HOST ) );
		$site_url_html  = esc_attr( get_site_url() );
		$backups_url    = network_admin_url() . 'admin.php?page=snapshot-backups';
		$logs_url       = network_admin_url() . 'admin.php?page=snapshot-backups#logs-' . $backup_id;

		$result = array(
			'p1_html'          => '',
			'p2_html'          => '',
			'button_link'      => $backups_url,
			'button_text'      => __( 'View Backup', 'snapshot' ),
			'bottom_link'      => '',
			'bottom_link_text' => '',
		);

		$backup_type_html = esc_html( $backup_type . ' backup' );
		if ( 'scheduled' === $backup_type ) {
			$backup_type_html = esc_html__( 'scheduled backup', 'snapshot' );
		} elseif ( 'manual' === $backup_type ) {
			$backup_type_html = esc_html__( 'manual backup', 'snapshot' );
		} elseif ( empty( $backup_type ) ) {
			$backup_type_html = esc_html__( 'backup', 'snapshot' );
		}

		switch ( $service_error ) {
			case self::ERROR_HUB_INFO_RESPONDED_INVALID_URI:
			case self::ERROR_HUB_INFO_RESPONDED_ERROR:
			case self::ERROR_USER_INFO_NOT_RETURN:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed due to the API connection with The Hub. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please run another backup, and contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please run another backup, and contact our support team if the issue persists.', 'snapshot' );
				}
				break;
			case self::ERROR_STORAGE_LIMIT:
				$result['p1_html'] = sprintf(
					/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
					__( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed due to insufficient storage space. To continue backing up your websites without any issues, free up your storage space, or upgrade your storage plan.', 'snapshot' ),
					$backup_type_html,
					$site_url_html,
					$site_host_html
				);
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please add more storage space and run another backup. If you are unable to add more storage space, contact our <a href="%s" target="_blank">support team</a> for assistance.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please add more storage space and run another backup. If you are unable to add more storage space, contact our support team for assistance.', 'snapshot' );
				}

				$result['button_link'] = self::URL_ADD_STORAGE_SPACE;
				$result['button_text'] = __( 'Add Storage Space', 'snapshot' );

				$result['bottom_link']      = $backups_url;
				$result['bottom_link_text'] = __( 'View Backup', 'snapshot' );
				break;
			case self::ERROR_FETCH_FILESLIST:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed due to a problem iterating over your site\'s files and building the file list. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %1$s - URL to logs, %2$s - support link */
					$result['p2_html'] = sprintf( __( 'Please add <strong>define( \'SNAPSHOT4_FILELIST_LOG_VERBOSE\', true );</strong> to the site\'s wp-config.php file and run another backup. Then check <a href="%1$s" target="_blank">the logs</a> to identify files that cannot be backed up. Contact our <a href="%2$s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), $logs_url, self::URL_CONTACT_SUPPORT );
				} else {
					/* translators: %1$s - URL to logs */
					$result['p2_html'] = sprintf( __( 'Please add <strong>define( \'SNAPSHOT4_FILELIST_LOG_VERBOSE\', true );</strong> to the site\'s wp-config.php file and run another backup. Then check <a href="%1$s" target="_blank">the logs</a> to identify files that cannot be backed up. Contact our support team if the issue persists.', 'snapshot' ), $logs_url );
				}
				break;
			case self::ERROR_SITE_NOT_RESPONDED_ZIPSTREAM_ERROR:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed while attempting to send files to our API. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please increase <strong>max_execution_time</strong> and PHP Memory, and then run another backup. Contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please increase <strong>max_execution_time</strong> and PHP Memory, and then run another backup. Contact our support team if the issue persists.', 'snapshot' );
				}
				break;
			case self::ERROR_ZIPSTREAM_FILE_MISSING:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed while attempting to send files to our API because a file was renamed or deleted during the backup process. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please add <strong>define( \'SNAPSHOT4_FILE_ZIPSTREAM_LOG_VERBOSE\', true );</strong> to the site\'s wp-config.php file and run another backup. Then, check the logs for any folder(s) that contain files that are rapidly changing (most often a cache folder) and exclude the folder(s) from subsequent backups. Contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please add <strong>define( \'SNAPSHOT4_FILE_ZIPSTREAM_LOG_VERBOSE\', true );</strong> to the site\'s wp-config.php file and run another backup. Then, check the logs for any folder(s) that contain files that are rapidly changing (most often a cache folder) and exclude the folder(s) from subsequent backups. Contact our support team if the issue persists.', 'snapshot' );
				}
				break;
			case self::ERROR_SITE_NOT_RESPONDED_LARGE_FILE_ERROR:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed while attempting to back up a large file over 100MB. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %1$s - URL to logs, %2$s - support link */
					$result['p2_html'] = sprintf( __( 'Please add <strong>define( \'SNAPSHOT4_FILELIST_LOG_VERBOSE\', true );</strong> to the site\'s wp-config.php file and run another backup. Then check <a href="%1$s" target="_blank">the logs</a> to identify the file, and exclude it from subsequent backups. Contact our <a href="%2$s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), $logs_url, self::URL_CONTACT_SUPPORT );
				} else {
					/* translators: %1$s - URL to logs */
					$result['p2_html'] = sprintf( __( 'Please add <strong>define( \'SNAPSHOT4_FILELIST_LOG_VERBOSE\', true );</strong> to the site\'s wp-config.php file and run another backup. Then check <a href="%1$s" target="_blank">the logs</a> to identify the file, and exclude it from subsequent backups. Contact our support team if the issue persists.', 'snapshot' ), $logs_url );
				}
				break;
			case self::ERROR_WRAPPER_DBLIST_FETCH:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed due to a problem iterating the database\'s tables and building the table list. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please increase <strong>max_execution_time</strong> and PHP Memory, and then run another backup. Contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please increase <strong>max_execution_time</strong> and PHP Memory, and then run another backup. Contact our support team if the issue persists.', 'snapshot' );
				}
				break;
			case self::ERROR_WRAPPER_TABLE_ZIPSTREAM:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed while attempting to send a database table to our API. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please increase <strong>max_execution_time</strong> and PHP Memory, and then run another backup. Contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please increase <strong>max_execution_time</strong> and PHP Memory, and then run another backup. Contact our support team if the issue persists.', 'snapshot' );
				}
				break;
			case self::ERROR_TOO_LARGE_TABLE_HANGED:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed while backing up a large table. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %1$s - URL to logs, %2$s - support link */
					$result['p2_html'] = sprintf( __( 'Please check <a href="%1$s" target="_blank">the logs</a> to identify the table, and contact our <a href="%2$s" target="_blank">support team</a> to exclude the table from subsequent backups, if necessary.', 'snapshot' ), $logs_url, self::URL_CONTACT_SUPPORT );
				} else {
					/* translators: %1$s - URL to logs */
					$result['p2_html'] = sprintf( __( 'Please check <a href="%1$s" target="_blank">the logs</a> to identify the table, and contact our support team to exclude the table from subsequent backups, if necessary.', 'snapshot' ), $logs_url );
				}
				break;
			case self::ERROR_SITE_NOT_RESPONDED_ERROR:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please increase <strong>max_execution_time</strong> and PHP Memory, and then run another backup. Contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please increase <strong>max_execution_time</strong> and PHP Memory, and then run another backup. Contact our support team if the issue persists.', 'snapshot' );
				}
				break;
			case self::ERROR_EXPORT_FAILED:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( ! Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please run another backup, and contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please run another backup, and contact our support team if the issue persists.', 'snapshot' );
				}
				break;
			case self::ERROR_UNKNOWN_ERROR:
			default:
				/* translators: %1$s - manual/scheduled backup, %2$s - Site URL, %3$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The %1$s for <a href="%2$s" target="_blank">%3$s</a> failed. The following error message is listed in the Snapshot log:', 'snapshot' ), $backup_type_html, $site_url_html, $site_host_html );
				if ( Settings::is_branding_hidden() ) {
					/* translators: %s - support link */
					$result['p2_html'] = sprintf( __( 'Please run another backup, and contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				} else {
					$result['p2_html'] = __( 'Please run another backup, and contact our support team if the issue persists.', 'snapshot' );
				}
				break;
		}//end switch

		return $result;
	}
}