<?php // phpcs:ignore
/**
 * Sends email with export link retrieved from the service.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Export;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Datetime;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Model\Env;
use WPMUDEV\Snapshot4\Model\Settings;

/**
 * Send export email task class
 */
class Email extends Task {

	const ERR_STRING_REQUEST_PARAMS = 'Request for sending export email was not successful';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'snapshot_id'         => 'sanitize_text_field',
		'export_link'         => self::class . '::validate_export_link',
		'email_account'       => self::class . '::validate_email_account',
		'display_name'        => self::class . '::validate_display_name',
		'snapshot_name'       => self::class . '::validate_extra_detail',
		'snapshot_created_at' => self::class . '::validate_extra_detail',
	);

	/**
	 * Validates export link coming from the service.
	 *
	 * @param string $export_link Link to export backup.
	 *
	 * @return string
	 */
	public static function validate_export_link( $export_link ) {
		return filter_var( $export_link, FILTER_VALIDATE_URL );
	}

	/**
	 * Validates email_account coming from the service.
	 *
	 * @param string $email_account Recipient email account.
	 *
	 * @return string
	 */
	public static function validate_email_account( $email_account ) {
		return filter_var( $email_account, FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Validates display_name coming from the service.
	 *
	 * @param string $display_name Name to be displayed in the email.
	 *
	 * @return string
	 */
	public static function validate_display_name( $display_name ) {
		return empty( $display_name ) ? $display_name : sanitize_text_field( $display_name );
	}

	/**
	 * Validates extra details coming from the service.
	 *
	 * @param string $value Name to be displayed in the email.
	 *
	 * @return string
	 */
	public static function validate_extra_detail( $value ) {
		return empty( $value ) ? $value : sanitize_text_field( $value );
	}

	/**
	 * Runs over the site's files and returns all info to the controller.
	 *
	 * @param array $args Info about what time the file iteration started and its timelimit.
	 */
	public function apply( $args = array() ) {
		$model = $args['model'];

		if ( isset( $model->get( 'export' )['email_account'] ) && ! empty( $model->get( 'export' )['email_account'] ) ) {
			$mail_to = $model->get( 'export' )['email_account'];
		} else {
			$mail_to = Settings::get_auth_user_email();
		}

		if ( empty( $mail_to ) ) {
			Log::error( __( 'Unable to send email because user\'s email is empty', 'snapshot' ) );
			return new \WP_Error( 'empty_wdp_un_auth_user', 'unable to send email because user\'s email is empty' );
		}

		if ( isset( $model->get( 'export' )['display_name'] ) && ! empty( $model->get( 'export' )['display_name'] ) ) {
			$name = $model->get( 'export' )['display_name'];
		} else {
			$wdp_un_profile_data = get_site_option( 'wdp_un_profile_data' );
			$name                = isset( $wdp_un_profile_data['profile']['name'] ) ? $wdp_un_profile_data['profile']['name'] : '';
		}

		$site_url = get_site_url();
		$site     = wp_parse_url( $site_url, PHP_URL_HOST );

		$backup_name = $model->get( 'export' )['snapshot_name'];
		$export_date = $model->get( 'export' )['export_date'];

		if ( ! empty( $export_date ) ) {
			$export_date = strtotime( $export_date );
			$export_date = Datetime::format( $export_date );
		}

		if ( 'null' === $backup_name ) {
			/* translators: $s - Scheduled backup name */
			$backup_name = sprintf( __( 'Scheduled - %s', 'snapshot' ), $export_date );
		}

		/* translators: %s - website URL */
		$subject  = sprintf( __( 'The backup for %s is ready to download!', 'snapshot' ), $site );
		$template = new Helper\Template();
		ob_start();
		$template->render(
			'mail/backup-export-done',
			array(
				'plugin_custom_name'     => Helper\Settings::get_brand_name(),
				'name'                   => $name,
				'site'                   => $site,
				'site_url'               => $site_url,
				'export_link'            => $model->get( 'export' )['export_link'],
				'backup_url'             => network_admin_url() . 'admin.php?page=snapshot-backups#backups-' . $model->get( 'export' )['id'],
				'subject'                => $subject,
				'snapshot_installer_url' => Env::get_wpmu_api_server_url() . 'api/snapshot/v2/download-installer-script',
				'snapshot_name'          => $backup_name,
				'export_date'            => $export_date,
			)
		);
		$message = ob_get_clean();

		$from        = 'noreply@' . $site;
		$from_name   = 'Snapshot';
		$from_header = "From: $from_name <$from>";

		$result = wp_mail( $mail_to, $subject, $message, array( 'Content-Type: text/html', $from_header ) );
		if ( ! $result ) {
			Log::error( __( 'Unable to send email', 'snapshot' ) );
			return new \WP_Error( 'send_mail_error', 'unable to send email' );
		}
	}
}