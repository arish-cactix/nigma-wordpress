<?php // phpcs:ignore
/**
 * Snapshot controllers: Backup service actions
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Service;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Notifications;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model\Log as ModelLog;

/**
 * Backup service actions handling controller class
 */
class Backup extends Controller\Service {

	/**
	 * Gets the list of known service actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		$known = array(
			self::START_BACKUP,
			self::FINISH_BACKUP,
			self::CANCELLED_BACKUP,
		);
		return $known;
	}

	/**
	 * Signals the start of the backup.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_start_backup( $params, $action, $request = false ) {
		$cancelled = $this->check_cancelled_backup();
		if ( false !== $cancelled ) {
			return $this->send_response_success( $cancelled, $request );
		}

		Log::info( __( 'The API has requested backup initiation.', 'snapshot' ) );

		$task = new Task\Backup\Start();

		$data             = json_decode( wp_json_encode( $params ), true );
		$validated_params = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_params ) ) {
			return $this->send_response_error( $validated_params, $request );
		}

		$model  = new Model\Backup\Start();
		$backup = array();

		$backup['id']   = $validated_params['snapshot_id'];
		$backup['name'] = Helper\Datetime::format( strtotime( $validated_params['created_at'] ) );

		if ( isset( $validated_params['bu_snapshot_name'] ) && 'null' !== $validated_params['bu_snapshot_name'] ) {
			$backup['name'] = \sanitize_text_field( $validated_params['bu_snapshot_name'] );
		}

		if ( isset( $backup['schedule_id'] ) && 'manual' !== $backup['schedule_id'] ) {
			$log = new ModelLog();
			$log->create( array( 'action' => 'scheduled_backup_started' ) );
		}

		$model->set( 'backup', $backup );
		$args          = array();
		$args['model'] = $model;

		$task->apply( $args );

		Log::clear();
		Log::info( __( 'A backup has been initiated.', 'snapshot' ) );

		$response = (object) array(
			'plugin_v'     => defined( 'SNAPSHOT_BACKUPS_VERSION' ) ? SNAPSHOT_BACKUPS_VERSION : null,
			'batch_cutoff' => defined( 'SNAPSHOT_FILES_ZIPSTREAM_BATCH_CUTOFF' ) ? absint( SNAPSHOT_FILES_ZIPSTREAM_BATCH_CUTOFF ) : null,
			'large_cutoff' => defined( 'SNAPSHOT_LARGE_FILE_ZIPSTREAM_CUTOFF' ) ? absint( SNAPSHOT_LARGE_FILE_ZIPSTREAM_CUTOFF ) : null,
			'db_chunk'     => defined( 'SNAPSHOT_DB_CHUNK_SIZE' ) ? absint( SNAPSHOT_DB_CHUNK_SIZE ) : null,
		);

		return $this->send_response_success( $response, $request );
	}

	/**
	 * Signals the end of the backup.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_finish_backup( $params, $action, $request = false ) {
		$data            = ( ! is_object( $params ) ) ? json_decode( $params, true ) : (array) $params;
		$snapshot_status = isset( $data['snapshot_status'] ) ? $data['snapshot_status'] : '';
		$service_error   = apply_filters( 'snapshot_custom_service_error', $snapshot_status );

		if ( $service_error && $service_error !== $snapshot_status ) {
			// > frontend refresh interval to have time to backup id.
			sleep( 6 );
			$data['success']         = false;
			$data['snapshot_status'] = $service_error;
		}

		$is_successful_backup = isset( $data['success'] ) ? boolval( $data['success'] ) : false;

		$task = new Task\Backup\Finish();

		$task->apply();

		if ( true === $is_successful_backup ) {
			delete_transient( 'snapshot_current_stats' );
			Log::info( __( 'The backup has been completed.', 'snapshot' ) );
			$this->maybe_find_any_failed_exports( $data );
			$this->send_email_success_notifications( $data['bu_frequency'], $data['snapshot_id'] );
		} else {
			$time = time();
			/* translators: %s - Backups status from the API */
			Log::error( sprintf( __( 'The backup has failed to complete. The API responded with: %s', 'snapshot' ), $snapshot_status ) );

			$snapshot_status = isset( $data['snapshot_status'] ) ? sanitize_text_field( $data['snapshot_status'] ) : '';

			self::save_backup_error( $data['snapshot_id'], $snapshot_status, $time );

			Task\Request\Listing::add_backup_type( $data );
			$this->notify(
				$snapshot_status,
				$time,
				$data
			);
		}//end if

		$this->send_response_success( true, $request );
	}

	/**
	 * The backup has been cancelled service-side and the API responded here with the snapshot_id.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_cancelled_backup( $params, $action, $request = false ) {
		$data = (array) $params;

		$cancelled_backup_id = isset( $data['snapshot_id'] ) ? sanitize_key( $data['snapshot_id'] ) : null;

		if ( ! empty( $cancelled_backup_id ) ) {
			// Add a persistent entry, so as to not show this backup as running ever again.
			update_site_option( Controller\Ajax\Backup::SNAPSHOT_CANCELLED_BACKUP_PERSISTENT . $cancelled_backup_id, true );
		}

		// Now, lets clean up like we do when a backup is finished.
		$task = new Task\Backup\Finish();

		$task->apply();

		Log::info( __( 'The backup has been cancelled.', 'snapshot' ) );

		$this->send_response_success( true, $request );
	}

	/**
	 * Send email when a backup fails
	 *
	 * @param string $service_error     Service's backup error message.
	 * @param int    $timestamp         Error time.
	 * @param array  $backup       Backup data.
	 */
	protected function notify( $service_error, $timestamp, $backup ) {
		$backup_type   = $backup['type'];
		$backup_id     = $backup['snapshot_id'];
		$service_error = apply_filters( 'snapshot_custom_service_error', $service_error );

		// Get the email settings.
		$email_settings = Settings::get_email_settings()['email_settings'];

		if ( ! $email_settings['on_fail_send'] || ! $email_settings['notify_on_fail'] ) {
			if ( 'scheduled' === $backup_type ) {
				$notifications = new Notifications();
				if ( $notifications->count() > 0 ) {
					/**
						* There is nothing to do for multiple failed notifications. Just clear for now.
						*/
					$notifications->clear();
				}
				// Push notifications to WP Admin.
				$notifications->push( compact( 'backup_id', 'service_error' ) );
			}
			return;
		}

		$recipients = $email_settings['on_fail_recipients'];
		$task       = new Task\Backup\Fail();
		$task->apply(
			array(
				'recipients'    => $recipients,
				'service_error' => $service_error,
				'timestamp'     => $timestamp,
				'backup_type'   => $backup_type,
				'backup_id'     => $backup_id,
				'backup'        => $backup,
			)
		);
	}

	/**
	 * Checks for any failed third party destination exports in the provided data.
	 * Updates the snapshot_failed_third_party_destination_exports option with any failed exports.
	 *
	 * @param array $data The backup data to check for failed exports.
	 */
	protected function maybe_find_any_failed_exports( $data ) {
		$exports_list = isset( $data['tpd_exp_done'] ) && is_string( $data['tpd_exp_done'] ) && ! empty( $data['tpd_exp_done'] )
			? str_replace( "'", '"', $data['tpd_exp_done'] )
			: $data['tpd_exp_done'] ?? '';

		if ( is_null( $exports_list ) ) {
			return;
		}

		if ( is_string( $exports_list ) ) {
			$exports_list = ( '' !== $exports_list ) ? json_decode( $exports_list, true ) : array();
		} elseif ( is_object( $exports_list ) ) {
			$exports_list = (array) $exports_list;
		}

		$failed_tpds  = get_site_option( 'snapshot_failed_third_party_destination_exports', array() );
		$google_drive = isset( $failed_tpds['google_drive'] ) ? $failed_tpds['google_drive'] : array();

		if ( isset( $exports_list['tpd_gdrive'] ) ) {
			foreach ( $exports_list['tpd_gdrive'] as $tpd_value => $export_status ) {
				if ( 'export_failed' === $export_status || 'export_failed_due_to_missing_folder' === $export_status ) {
					// Currently handling export failed scenario for Google Drive only.
					if ( ! in_array( $tpd_value, $google_drive, true ) ) {
						$google_drive[] = $tpd_value;
					}
				}

				if ( 'export_success' === $export_status && in_array( $tpd_value, $google_drive, true ) ) {
					// Edge case but if the previous export failed and then succeeded on subsequent backups,
					// we should remove it from the failed list.
					$google_drive = array_diff( $google_drive, array( $tpd_value ) );
				}

				$failed_tpds['google_drive'] = $google_drive;
			}
		}

		if ( ! empty( $failed_tpds ) ) {
			// Only update if there are any failed exports to the connected third party destinations.
			update_site_option( 'snapshot_failed_third_party_destination_exports', $failed_tpds );
		}
	}

	/**
	 * Send email when a backup completes
	 *
	 * @param string $frequency Backup frequency (scheduled or manual).
	 * @param string $backup_id Backup ID.
	 */
	protected function send_email_success_notifications( $frequency, $backup_id ) {
		$email_settings = Settings::get_email_settings()['email_settings'];
		if ( ! $email_settings['on_fail_send'] || ! $email_settings['notify_on_complete'] ) {
			return;
		}
		$recipients = $email_settings['on_fail_recipients'];

		$task = new Task\Backup\Complete();
		$task->apply(
			array(
				'recipients' => $recipients,
				'frequency'  => $frequency,
				'backup_id'  => $backup_id,
			)
		);
	}

	/**
	 * Save backup error status.
	 *
	 * @param string $backup_id Backup ID.
	 * @param string $backup_status Service error.
	 * @param int    $timestamp Timestamp.
	 */
	public static function save_backup_error( $backup_id, $backup_status, $timestamp ) {
		$data = array(
			'backup_id'     => $backup_id,
			'backup_status' => $backup_status,
			'timestamp'     => $timestamp,
		);
		set_transient( 'snapshot_backup_error', $data, 30 * 60 );
	}
}