<?php // phpcs:ignore
/**
 * Snapshot controllers: Export backup service actions
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Service\Export;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;

/**
 * Export backup service actions handling controller class
 */
class Email extends Controller\Service {

	/**
	 * Gets the list of known service actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		$known = array(
			self::EXPORT_BACKUP_EMAIL,
			self::EXPORT_LOGGING,
		);
		return $known;
	}

	/**
	 * Retrieves the export link from the service and sends an email including that link.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_export_backup_email( $params, $action, $request = false ) {
		Log::info( __( 'The API has completed a backup export.', 'snapshot' ) );

		$task = new Task\Export\Email();

		$data = (array) $params;

		$validated_params = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_params ) ) {
			return $this->send_response_error( $validated_params, $request );
		}

		set_site_transient(
			'snapshot_export_' . $validated_params['snapshot_id'],
			$validated_params['export_link'],
			7 * DAY_IN_SECONDS
		);

		$option_name = 'snapshot_direct_download_export_' . $validated_params['snapshot_id'];

		if ( get_site_transient( $option_name ) ) {
			// Set another transient for notification.
			// Since the download link expires after 7 days, it only makes sense to show the notification once within 7 days.
			// Once the notification is shown, we will delete the transients immediately.
			set_site_transient(
				'snapshot_download_link_notification',
				$validated_params['snapshot_id'],
				7 * DAY_IN_SECONDS
			);
			set_site_transient(
				'snapshot_download_link_immediate_notification',
				$validated_params['snapshot_id'],
				7 * DAY_IN_SECONDS
			);

			// Some cleanup.
			delete_site_transient( $option_name );
			// Exiting early and sending a successful response to the API.
			return $this->send_response_success( true, $request );
		}

		if ( array_key_exists( 'send_email', $data ) && '0' === $data['send_email'] ) {
			Log::info( __( 'Export link will not be sent via email.', 'snapshot' ) );
			return $this->send_response_success( true, $request );
		}

		$model  = new Model\Export\Email();
		$export = array();

		$export['id']            = $validated_params['snapshot_id'];
		$export['export_link']   = $validated_params['export_link'];
		$export['email_account'] = $validated_params['email_account'];
		$export['display_name']  = $validated_params['display_name'];
		$export['snapshot_name'] = $validated_params['snapshot_name'];
		$export['export_date']   = $validated_params['snapshot_created_at'];

		$model->set( 'export', $export );
		$args          = array();
		$args['model'] = $model;

		$result = $task->apply( $args );

		if ( is_wp_error( $result ) ) {
			return $this->send_response_error( $result, $request );
		}

		Log::info( __( 'An email with the export link has been sent.', 'snapshot' ) );

		return $this->send_response_success( true, $request );
	}

	/**
	 * Receives the failure log in case the export failed.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_export_logging( $params, $action, $request = false ) {
		$data = (array) $params;

		$backup_id    = isset( $data['snapshot_id'] ) ? sanitize_key( $data['snapshot_id'] ) : null;
		$failure_code = isset( $data['export_status'] ) ? sanitize_text_field( $data['export_status'] ) : null;

		/* translators: %s - export failure code */
		Log::error( sprintf( __( 'The backup export has failed: %s.', 'snapshot' ), $failure_code ), array(), $backup_id );

		$this->send_email_notifications( Task\Export\Fail::ERROR_EXPORT_FAILED, time(), null, $backup_id, $failure_code );

		return $this->send_response_success( true, $request );
	}

	/**
	 * Send email when a backup export fails
	 *
	 * @param string $service_error     Service's backup error message.
	 * @param int    $timestamp         Error time.
	 * @param string $backup_type       Type of backup ("scheduled" or "manual").
	 * @param string $backup_id         Backup ID.
	 * @param string $error_message     Error message (export_status).
	 */
	protected function send_email_notifications( $service_error, $timestamp, $backup_type, $backup_id, $error_message ) {
		$service_error = apply_filters( 'snapshot_custom_service_error', $service_error );

		$email_settings = Settings::get_email_settings()['email_settings'];
		if ( ! $email_settings['on_fail_send'] || ! $email_settings['notify_on_fail'] ) {
			return;
		}
		$recipients = $email_settings['on_fail_recipients'];

		$task = new Task\Export\Fail();
		$task->apply(
			array(
				'recipients'    => $recipients,
				'service_error' => $service_error,
				'timestamp'     => $timestamp,
				'backup_type'   => $backup_type,
				'backup_id'     => $backup_id,
				'error_message' => $error_message,
			)
		);
	}
}