<?php // phpcs:ignore
/**
 * Failed backup email notifications.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Export;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Template;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;

/**
 * Finish backup task class
 */
class Fail extends Task {
	const ERROR_EXPORT_FAILED                    = 'export_failed';
	const ERROR_EXPORT_FAILED_SITE_NOT_RESPONDED = 'export_failed_SiteNotRespondedError';
	const ERROR_EXPORT_FAILED_UNKNOWN            = 'export_failed_UnknownError';

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
				isset( $args['error_message'] ) ? $args['error_message'] : null
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
	 */
	private function send( $email, $name, $service_error, $timestamp, $backup_type, $backup_id, $error_message = null ) {
		$site_host_html = wp_parse_url( get_site_url(), PHP_URL_HOST );

		$dt = new \DateTime();
		$dt->setTimestamp( $timestamp );
		$dt->setTimezone( wp_timezone() );
		$time_human = $dt->format( 'Y-m-d H:i:s' );

		/* translators: %s - website URL */
		$subject  = sprintf( __( 'The backup export for %s failed.', 'snapshot' ), $site_host_html );
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

		$template = new Template();
		ob_start();
		$template->render( 'mail/export-fail', $params );
		$message = ob_get_clean();

		$from = 'noreply@' . $site_host_html;
		/* translators: %s - brand name */
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
		$backups_url    = network_admin_url() . 'admin.php?page=snapshot-backups#backups-' . $backup_id;
		$logs_url       = network_admin_url() . 'admin.php?page=snapshot-backups#logs-' . $backup_id;

		$result = array(
			'p1_html'          => '',
			'p2_html'          => '',
			'button_link'      => $backups_url,
			'button_text'      => __( 'View Backup', 'snapshot' ),
			'bottom_link'      => '',
			'bottom_link_text' => '',
		);

		switch ( $service_error ) {
			case self::ERROR_EXPORT_FAILED:
				/* translators: %1$s - Site URL, %2$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The backup export for <a href="%1$s" target="_blank">%2$s</a> failed. The following error message is listed in the Snapshot log:', 'snapshot' ), $site_url_html, $site_host_html );
				/* translators: %s - support link */
				$result['p2_html'] = sprintf( __( 'Please run another backup, and contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				break;

			case self::ERROR_EXPORT_FAILED_SITE_NOT_RESPONDED:
				/* translators: %1$s - Site URL, %2$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The backup export for <a href="%1$s" target="_blank">%2$s</a> failed. The following error message is listed in the Snapshot log:', 'snapshot' ), $site_url_html, $site_host_html );
				/* translators: %s - support link */
				$result['p2_html'] = sprintf( __( 'Please run another backup, and contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				break;

			case self::ERROR_EXPORT_FAILED_UNKNOWN:
			default:
				/* translators: %1$s - Site URL, %2$s - Site domain */
				$result['p1_html'] = sprintf( __( 'The backup export for <a href="%1$s" target="_blank">%2$s</a> failed. The following error message is listed in the Snapshot log:', 'snapshot' ), $site_url_html, $site_host_html );
				/* translators: %s - support link */
				$result['p2_html'] = sprintf( __( 'Please run another backup, and contact our <a href="%s" target="_blank">support team</a> if the issue persists.', 'snapshot' ), self::URL_CONTACT_SUPPORT );
				break;
		}//end switch

		return $result;
	}
}