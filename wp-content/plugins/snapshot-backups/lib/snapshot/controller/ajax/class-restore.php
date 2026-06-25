<?php // phpcs:ignore
/**
 * Snapshot controllers: Restore AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper\Lock;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Model\Log as ModelLog;
use WPMUDEV\Snapshot4\Model\Restore\Stats;
use WPMUDEV\Snapshot4\Task;

/**
 * Rrestore AJAX controller class
 */
class Restore extends Controller\Ajax {

	const SNAPSHOT_DOWNLOAD_BACKUP_PROGRESS = 'snapshot_download_backup_progress';

	/**
	 * Stats class.
	 *
	 * @var \WPMUDEV\Snapshot4\Model\Restore\Stats
	 */
	protected $stats = null;

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		$this->stats = new Stats();

		add_action( 'wp_ajax_snapshot-process_restore', array( $this, 'json_process_restore' ) );
		add_action( 'wp_ajax_snapshot-cancel_restore', array( $this, 'json_cancel_restore' ) );

		add_action( 'wp_ajax_nopriv_snapshot-check_logged_in', array( $this, 'json_check_logged_in' ) );
		add_action( 'wp_ajax_snapshot-check_logged_in', array( $this, 'json_check_logged_in' ) );
	}

	/**
	 * Responsible to identify whether we got an error during restore because we got logged out or not.
	 */
	public function json_check_logged_in() {
		wp_send_json_success(
			array(
				'logged_in' => is_user_logged_in() ? 'yes' : 'no',
			)
		);
	}

	/**
	 * Cancels the running restore.
	 */
	public function json_cancel_restore() {
		$this->do_request_sanity_check( 'snapshot_cancel_backup_restore', self::TYPE_POST );

		$data              = array();
		$data['backup_id'] = isset( $_POST['backup_id'] ) ? $_POST['backup_id'] : null; // phpcs:ignore

		$task = new Task\Restore();

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		set_site_transient( 'snapshot_cancelled_restore', $data['backup_id'], true, 60 );

		Model\Restore::clean_residuals( false, 'cancel' );

		wp_send_json_success();
	}

	/**
	 * Responsible for calling the appropriate restore action, depending on what stage we're on, according to the locks in snapshot folder.
	 */
	public function json_process_restore() {
		$this->do_request_sanity_check( 'snapshot_trigger_backup_restore', self::TYPE_POST );
		$manual_restore = Settings::get_manual_restore_mode();
		$data           = array();

		$backup_id = isset( $_POST['data']['backup_id'] )? $_POST['data']['backup_id'] : null; // phpcs:ignore
		if ( get_site_transient( 'snapshot_cancelled_restore' ) === $backup_id ) {
			delete_site_transient( 'snapshot_cancelled_restore' );
			Model\Restore::clean_residuals( $manual_restore );
			wp_send_json_success(
				array(
					'cancelled' => true,
				)
			);
		}

		$data['backup_id']     = $backup_id;
		$data['export_id']     = isset( $_POST['data']['export_id'] ) ? $_POST['data']['export_id'] : null; // phpcs:ignore
		$data['restore_id']    = isset( $_POST['data']['restore_id'] ) ? $_POST['data']['restore_id'] : null; // phpcs:ignore
		$data['initial']       = isset( $_POST['data']['initial'] ) ? boolval( $_POST['data']['initial'] ) : null; // phpcs:ignore
		$data['download_link'] = isset( $_POST['data']['download_link'] ) ? $_POST['data']['download_link'] : null; // phpcs:ignore
		$data['export_type']   = isset( $_POST['data']['export_options'] ) ? $_POST['data']['export_options'] : 'full_backup'; // phpcs:ignore
		$expand                = isset( $_POST['expand'] ) ? $_POST['expand'] : null; // phpcs:ignore

		if ( is_multisite() ) {
			$restore_what = isset( $_POST['data']['restore_what'] ) ? $_POST['data']['restore_what'] : 'network'; // phpcs:ignore

			$data['subsite_id']   = isset( $_POST['data']['subsite_id'] ) ? $_POST['data']['subsite_id'] : 0; // phpcs:ignore

			if ( 'subsite' === $restore_what ) {
				$site                 = get_blog_details( array( 'blog_id' => $data['subsite_id'] ) );
				$data['subsite_name'] = untrailingslashit( $site->domain . $site->path );
			}

			if ( 'network' === $restore_what ) {
				$data['subsite_id']   = 'null';
				$data['subsite_name'] = untrailingslashit( get_site_url() );
			}

			$data['export_type'] = 'full_backup';
		}

		$task = new Task\Restore();

		$lock = Lock::read( $data['backup_id'] );

		if ( ! empty( $lock ) ) {
			$data            = array_merge( $data, $lock );
			$data['initial'] = false;
		}

		if ( ! empty( $data['restore_id'] ) && 'export' === $lock['stage'] ) {
			Lock::append( array( 'restore_id' => $data['restore_id'] ), $data['backup_id'] );
		}

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		// Artificially fail the restore.
		$manual_fail = apply_filters(
			'snapshot_manual_fail_restore',
			false
		);

		if ( $data['initial'] ) {
			$log = new ModelLog();
			$log->create(
				array(
					'action'  => 'snapshot_restore_started', /* translators: %s - Total downloaded size */
					'details' => esc_html( sprintf( __( 'Proceeding with the restoration of backup id: %s', 'snapshot' ), $data['backup_id'] ) ),
				)
			);
		}

		if ( $data['initial'] ) {
			// This is a brand new restore, we have to clear out any residuals from older restores.
			Model\Restore::clean_residuals( $manual_restore );

			$lock_data = array(
				'backup_id'    => $data['backup_id'],
				// Appending the restore type for the lock.
				'restore_type' => $data['export_type'],
			);

			if ( isset( $data['subsite_id'] ) ) {
				$lock_data['subsite_id']   = $data['subsite_id'];
				$lock_data['subsite_name'] = $data['subsite_name'];
			}

			Lock::write(
				$lock_data,
				$data['backup_id'],
			);

			$lock = Lock::read( $data['backup_id'] );

			Log::info( __( 'Restore has been initiated', 'snapshot' ), array(), $validated_data['backup_id'] );
		}//end if

		if ( $manual_restore && ! isset( $lock['stage'] ) ) {
			// Let's go straight to the file restoration.
			$result = $this->restore_files( $validated_data['backup_id'], false );
		} else {
			if ( ! isset( $lock['stage'] ) ) {
				$trigger_data = array(
					'backup_id'   => $validated_data['backup_id'],
					'export_type' => $data['export_type'],
				);

				if ( isset( $data ['subsite_id'] ) ) {
					$trigger_data['subsite_id']   = $lock['subsite_id'];
					$trigger_data['subsite_name'] = $lock['subsite_name'];
				}

				$this->trigger_export( $trigger_data );
			}

			$result = array();
			switch ( $lock['stage'] ) {
				case 'export':
				case 'exporting':
					if ( 'export' === $manual_fail || 'exporting' === $manual_fail ) {
						die;
					}

					$result = $this->get_export_status(
						$data['export_id'],
						$validated_data['backup_id'],
						$data['restore_id']
					);
					break;
				case 'download_info':
					if ( 'download_info' === $manual_fail ) {
						die;
					}

					$result = $this->get_downloadable_backup_info(
						$data['download_link'],
						$validated_data['backup_id'],
						$data['restore_id']
					);
					break;
				case 'download':
					if ( 'download' === $manual_fail ) {
						die;
					}

					$result = $this->download_backup(
						$data['download_link'],
						$validated_data['backup_id'],
						$data['restore_id']
					);
					break;
				case 'files':
					if ( 'files' === $manual_fail ) {
						die;
					}

					$result = $this->restore_files(
						$validated_data['backup_id'],
						false,
						$data['restore_id'],
						$lock['restore_type'],
					);
					break;
				case 'last-files':
					if ( 'last-files' === $manual_fail ) {
						die;
					}

					$result = $this->restore_files(
						$validated_data['backup_id'],
						true,
						$data['restore_id'],
						$lock['restore_type'],
					);
					break;
				case 'tables':
					if ( 'tables' === $manual_fail ) {
						die;
					}

					$result = $this->restore_tables(
						$validated_data['backup_id'],
						$data['restore_id'],
						$lock['restore_type'],
					);
					break;
				case 'finalize':
					if ( 'finalize' === $manual_fail ) {
						die;
					}
					$result = $this->finalize_restore(
						$validated_data['backup_id'],
						$data['restore_id'],
						isset( $data['subsite_id'] ) ? $data['subsite_id'] : null
					);
					break;
				default:
					break;
			}//end switch
		}//end if

		if ( 'log' === $expand ) {
			$log_offset    = isset( $_POST['log_offset'] ) ? intval( $_POST['log_offset'] ) : 0; // phpcs:ignore
			$log           = Log::parse_log_file( $validated_data['backup_id'], $log_offset ); // phpcs:ignore
			$result['log'] = $log;
		}

		wp_send_json_success( $result );
	}

	/**
	 * Trigger a backup restore by requesting a backup export.
	 *
	 * @param array $export_data Export data.
	 */
	public function trigger_export( $export_data ) {
		$backup_id         = $export_data['backup_id'];
		$data['backup_id'] = $backup_id;

		// We're dealing with full backup only for now.
		$data['export_type'] = ! empty( $export_data['export_type'] ) ? $export_data['export_type'] : 'full_backup';

		$task  = new Task\Request\Export();
		$model = new Model\Request\Export();

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error(
				array(
					'failed_stage' => $model->get_trigger_error_string(),
					'error'        => $validated_data,
				)
			);
		}

		if ( isset( $export_data['subsite_id'] ) ) {
			$validated_data['subsite_id']   = $export_data['subsite_id'];
			$validated_data['subsite_name'] = $export_data['subsite_name'];
		}

		$args                  = $validated_data;
		$args['request_model'] = $model;
		$args['send_email']    = false;
		$result                = $task->apply( $args );

		if ( $task->has_errors() ) {
			Log::error( __( 'A backup export couldn\'t be triggered.', 'snapshot' ), array(), $backup_id );

			wp_send_json_error(
				array(
					'failed_stage' => $model->get_trigger_error_string(),
				)
			);
		}

		// We just started exporting, please update the lock file.
		$lock_content = array(
			'stage'     => 'export',
			'export_id' => $result['export_id'],
		);

		Lock::append( $lock_content, $backup_id );

		Log::info( __( 'A backup export has been triggered.', 'snapshot' ), array(), $backup_id );

		$data = array(
			'snapshot_id'    => $backup_id,
			'export_id'      => $result['export_id'],
			'restore_status' => 'snapshot4_restore_export_started',
			'processor'      => 'plugin',
		);

		$restores = $this->stats->store( $data );

		$result['restores'] = $restores;

		wp_send_json_success(
			array(
				'task'         => 'export',
				'api_response' => $result,
			)
		);
	}

	/**
	 * Get the status of an export.
	 *
	 * @param string $export_id Export id.
	 * @param string $backup_id Backup id.
	 * @param string $restore_id Restore id.
	 */
	public function get_export_status( $export_id, $backup_id, $restore_id ) {
		// We now quering for export status, please update the lock file.
		$lock_content = array(
			'stage' => 'exporting',
		);
		Lock::append( $lock_content, $backup_id );

		Log::info( __( 'Checking backup export status.', 'snapshot' ), array(), $backup_id );

		$data['export_id'] = $export_id;

		$task  = new Task\Request\Export\Status();
		$model = new Model\Request\Export\Status();

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			$this->stats->update( $restore_id, 'snapshot4_restore_stage_export_failed' );

			wp_send_json_error(
				array(
					'failed_stage' => $model->get_status_error_string(),
					'error'        => $validated_data,
				)
			);
		}

		$args                  = $validated_data;
		$args['request_model'] = $model;
		$result                = $task->apply( $args );

		if ( $task->has_errors() ) {
			Log::error( __( 'The backup export has failed.', 'snapshot' ), array(), $backup_id );

			$this->stats->update( $restore_id, 'snapshot4_restore_stage_export_failed' );
			wp_send_json_error(
				array(
					'failed_stage' => $model->get_status_error_string(),
				)
			);
		}

		if ( isset( $result['export_status'] ) && 'export_completed' === $result['export_status'] ) {
			// We're done exporting, please update the lock file.
			$this->stats->update( $restore_id, 'snapshot4_restore_stage_export_completed' );
			$lock_content = array(
				'stage'         => 'download_info',
				'download_link' => $result['download_link'],
			);
			Lock::append( $lock_content, $backup_id );
			Log::info( __( 'The backup export has been completed.', 'snapshot' ), array(), $backup_id );
		}

		return array(
			'task'         => 'exporting',
			'api_response' => $result,
		);
	}

	/**
	 * Grabs the size related information from the provided download link.
	 *
	 * @param string $download_link Download link.
	 * @param string $backup_id     Backup id.
	 * @param string $restore_id    Restore id.
	 */
	public function get_downloadable_backup_info( $download_link, $backup_id, $restore_id ) {
		$data = array(
			'download_link' => $download_link,
			'backup_id'     => $backup_id,
		);

		$task  = new Task\Download();
		$model = new Model\Download();

		$validated_data = $task->validate_request_data( $data );

		if ( is_wp_error( $validated_data ) ) {
			if ( $restore_id ) {
				$this->stats->update( $restore_id, 'snapshot4_restore_stage_download_info_failed' );
			}
			wp_send_json_error(
				array(
					'failed_stage' => $model->get_downloadable_file_not_found_error_string(),
					'error'        => $validated_data,
				)
			);
		}

		$args          = $validated_data;
		$args['model'] = $model;

		$result = $task->get_export_info( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $err ) {
				Log::error( $err->get_error_message(), array(), $backup_id );
			}

			Log::error( __( 'We couldn\'t get further information from the resource.', 'snapshot' ), array(), $backup_id );
			if ( $restore_id ) {
				$this->stats->update( $restore_id, 'snapshot4_restore_stage_download_invalid' );
			}
			wp_send_json_error(
				array(
					'failed_stage' => $model->get_downloadable_file_not_found_error_string(),
				)
			);
		}

		if ( ! array_key_exists( 'size', $result ) ) {
			$this->stats->update( $restore_id, 'snapshot4_restore_stage_download_unknown_size' );
			wp_send_json_error(
				array(
					'failed_stage' => $model->get_downloadable_file_not_found_error_string(),
				)
			);
		}

		$lock_content = array(
			'stage'         => 'download',
			'download_link' => $download_link,
			'size'          => $result['size'],
		);

		Lock::append( $lock_content, $backup_id );

		if ( $restore_id ) {
			$this->stats->update( $restore_id, 'snapshot4_restore_stage_downloading' );
		}

		Log::info( __( 'Snapshot will now proceed with the downloading.', 'snapshot' ), array(), $backup_id );

		$result['task'] = 'download';

		return $result;
	}

	/**
	 * Downloads a backup from a S3 link.
	 *
	 * @param string $download_link Download link.
	 * @param string $backup_id     Backup id.
	 * @param string $restore_id    For restoration stats.
	 */
	public function download_backup( $download_link, $backup_id, string $restore_id = '' ) {
		$data['download_link'] = $download_link;
		$data['backup_id']     = $backup_id;

		$task  = new Task\Download();
		$model = new Model\Download();

		$validated_data = $task->validate_request_data( $data );

		if ( is_wp_error( $validated_data ) ) {
			Log::info( 'Error on data validation.', array(), $backup_id );
			wp_send_json_error(
				array(
					'failed_stage' => $model->get_download_error_string(),
					'error'        => $validated_data,
				)
			);
		}

		$lock = Lock::read( $backup_id );

		$args          = $validated_data;
		$args['model'] = $model;

		$result = $task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message(), array(), $backup_id );
			}

			Log::error( __( 'We couldn\'t download the exported backup.', 'snapshot' ), array(), $backup_id );

			if ( $restore_id ) {
				$this->stats->update( $restore_id, 'snapshot4_restore_stage_download_failed' );
			}

			wp_send_json_error(
				array(
					'failed_stage' => $model->get_download_error_string(),
				)
			);
		}

		if ( ! $result ) {
			if ( $restore_id ) {
				$this->stats->update( $restore_id, 'snapshot4_restore_stage_download_failed' );
			}

			Log::error( __( 'We couldn\'t download the exported backup.', 'snapshot' ), array(), $backup_id );
			wp_send_json_error(
				array(
					'failed_stage' => $model->get_download_error_string(),
				)
			);
		}

		Log::info( __( 'Downloading the exported backup.', 'snapshot' ), array(), $backup_id );

		$response = array(
			'task' => 'download',
			'done' => $model->get( 'download_completed' ),
		);

		$size            = (int) $lock['size'];
		$downloaded_size = 0;

		if ( $model->has( 'downloaded_till' ) ) {
			$downloaded_size = (int) $model->get( 'downloaded_till' );
		}

		$size_readable = parse_size_readable( $size );
		/* translators: %1$s - Downloaded file size, %2$s - Total download size */
		$response['downloaded'] = sprintf( __( '(Downloading %1$s of %2$s)', 'snapshot' ), parse_size_readable( $downloaded_size ), $size_readable );

		if ( true === $model->get( 'download_completed' ) ) {
			if ( $restore_id ) {
				$this->stats->update( $restore_id, 'snapshot4_restore_stage_download_completed' );
			}

			Log::info( __( 'The exported backup has been downloaded.', 'snapshot' ), array(), $backup_id );
			/* translators: %s - Total downloaded size */
			$response['downloaded'] = sprintf( __( '(Download complete - %s)', 'snapshot' ), $size_readable );
		}

		return $response;
	}

	/**
	 * Deals with restoring the files from the exported backup.
	 *
	 * @param string $backup_id    Backup id.
	 * @param bool   $last_run     Wether we have finished restoring main files and we're now restoring the leftovers.
	 * @param string $restore_id   Restore id.
	 * @param string $restore_type Type of restoration: full_backup | files | database.
	 */
	public function restore_files(
		$backup_id,
		$last_run,
		string $restore_id = '',
		string $restore_type = 'full_backup'
	) {
		$data['backup_id'] = $backup_id;

		if ( get_site_transient( 'snapshot_cancelled_restore' ) === $backup_id ) {
			delete_site_transient( 'snapshot_cancelled_restore' );
			Model\Restore::clean_residuals();
			wp_send_json_success(
				array(
					'cancelled' => true,
				)
			);
		}

		$task  = new Task\Restore\Files();
		$model = new Model\Restore\Files( $data['backup_id'] );

		$model->set( 'skipped_files', array() );
		$model->set( 'last_files_run', $last_run );
		$model->set( 'need_last_run', false );

		$validated_data = $task->validate_request_data( $data );

		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error(
				array(
					'failed_stage' => $model->get_files_error_string(),
					'error'        => $validated_data,
				)
			);
		}

		$args          = $validated_data;
		$args['model'] = $model;

		/**
		 * Short-circuit the restoration process.
		 * We're skipping the files restoration if the restore type is a database.
		 */
		if ( 'database' === $restore_type ) {
			$lock_content = array(
				'stage' => 'tables',
			);

			Lock::append( $lock_content, $backup_id );

			Log::info( __( 'Files restoration is skipped for database only restoration.', 'snapshot' ), array(), $backup_id );

			/**
			 * Since we skipped the files restoration phase and it includes the backup extraction,
			 * We need to handle it before sending a response.
			 */
			$exported_root = Model\Restore::get_intermediate_destination( $args['backup_id'] );
			$model->extract_backup( $exported_root, false );

			return array(
				'task'          => 'files',
				'done'          => true,
				'skipped_files' => array(),
				'restore_type'  => $restore_type,
			);
		}//end if

		if ( $restore_id && ! $last_run ) {
			$this->stats->update( $restore_id, 'snapshot4_restore_stage_files_restoration_started' );
		}

		Log::info( __( 'File restoration is under way.', 'snapshot' ), array(), $backup_id );

		$task->apply( $args );

		if ( $task->has_errors() ) {
			$errors          = $task->get_errors();
			$warning_counter = 0;
			foreach ( $errors as $error ) {
				// Lets see if the error was something that we can recover from (eg. unable to overwrite an unwrittable file).
				if ( false !== strpos( $error->get_error_code(), 'failed_file_move' ) ) {
					// We can recover from that.
					Log::warning( $error->get_error_message(), array(), $backup_id );
					++$warning_counter;

					continue;
				}
				Log::error( $error->get_error_message(), array(), $backup_id );
			}

			if ( count( $errors ) !== $warning_counter ) {
				// This means that aside from recoverable warnings, we faced irrecoverable ones too, lets fail the restore.
				Log::error( __( 'File restoration has failed.', 'snapshot' ), array(), $backup_id );

				if ( $restore_id ) {
					$this->stats->update( $restore_id, 'snapshot4_restore_stage_files_restore_failed' );
				}

				wp_send_json_error(
					array(
						'failed_stage' => $model->get_files_error_string(),
					)
				);
			}
		}//end if

		$last_run = $model->get( 'last_files_run' );

		if ( true === $model->get( 'is_done' ) ) {
			// We're done restoring files, let's see if we have also restored the special files that we saved for last.

			if ( $last_run ) {
				// We are done restoring files, please update the lock file.
				$lock_content = array(
					'stage' => 'tables',
				);

				if ( $restore_id ) {
					$this->stats->update( $restore_id, 'snapshot4_restore_stage_files_restore_completed' );
				}

				Lock::append( $lock_content, $backup_id );
				Log::info( __( 'File restoration has been completed.', 'snapshot' ), array(), $backup_id );

			} else {
				// We are done restoring the main files, please update the lock file, so we can restore the leftover files in the next run.
				$lock_content = array(
					'stage' => 'last-files',
				);

				if ( $restore_id ) {
					$this->stats->update( $restore_id, 'snapshot4_restore_stage_files_restore_last' );
				}

				Lock::append( $lock_content, $backup_id );

				$model->set( 'need_last_run', true );
			}//end if
		} else {
			$lock_content          = Lock::read( $backup_id );
			$lock_content['stage'] = $last_run ? 'last-files' : 'files';
		}//end if

		Lock::append( $lock_content, $backup_id );
		return array(
			'task'          => 'files',
			'done'          => $model->get( 'is_done' ) && ! $model->get( 'need_last_run' ),
			'skipped_files' => $model->get( 'skipped_files', array() ),
		);
	}

	/**
	 * Deals with restoring the tables from the exported backup.
	 *
	 * @param string $backup_id Backup id.
	 * @param string $restore_id Restore id.
	 * @param string $restore_type Type of restoration: full_backup | files | database.
	 */
	public function restore_tables(
		$backup_id,
		string $restore_id = '',
		string $restore_type = 'full_backup'
	) {
		$data['backup_id'] = $backup_id;

		/**
		 * Short-circuit the restoration process.
		 * We're now skipping the database restoration if the restore type is files only.
		 */
		if ( 'files' === $restore_type ) {
			$lock_content = array(
				'stage' => 'finalize',
			);

			Lock::append( $lock_content, $backup_id );

			Log::info( __( 'Database restoration is skipped for files only restoration.', 'snapshot' ), array(), $backup_id );

			return array(
				'task'           => 'tables',
				'done'           => true,
				'skipped_tables' => array(),
				'restore_type'   => $restore_type,
			);
		}

		$task  = new Task\Restore\Tables();
		$model = new Model\Restore\Tables( $data['backup_id'] );

		$model->set( 'skipped_tables', array() );
		$model->set( 'is_done', false );

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			if ( $restore_id ) {
				$this->stats->update( $restore_id, 'snapshot4_restore_stage_db_tables_restoration_failed' );
			}

			wp_send_json_error(
				array(
					'failed_stage' => $model->get_tables_error_string(),
					'error'        => $validated_data,
				)
			);
		}

		if ( $restore_id ) {
			$this->stats->update( $restore_id, 'snapshot4_restore_stage_db_tables_restoration_started' );
		}

		Log::info( __( 'DB restoration is under way.', 'snapshot' ), array(), $backup_id );

		$args          = $validated_data;
		$args['model'] = $model;

		$task->apply( $args );

		if ( $task->has_errors() ) {
			$errors = $task->get_errors();
			foreach ( $errors as $error ) {
				Log::error( $error->get_error_message(), array(), $backup_id );
			}
			Log::error( __( 'DB restoration has failed.', 'snapshot' ), array(), $backup_id );

			if ( $restore_id ) {
				$this->stats->update( $restore_id, 'snapshot4_restore_stage_db_restoration_failed' );
			}

			wp_send_json_error(
				array(
					'failed_stage' => $model->get_tables_error_string(),
				)
			);
		}

		if ( true === $model->get( 'is_done' ) ) {
			if ( $restore_id ) {
				$this->stats->update( $restore_id, 'snapshot4_restore_stage_tables_completed' );
			}

			// We are done restoring files, please update the lock file.
			$lock_content = array(
				'stage' => 'finalize',
			);
			Lock::append( $lock_content, $backup_id );

			$task->cleanup();

			Log::info( __( 'DB restoration has been completed.', 'snapshot' ), array(), $backup_id );
		}

		return array(
			'task'           => 'tables',
			'done'           => $model->get( 'is_done' ),
			'skipped_tables' => $model->get( 'skipped_tables', array() ),
		);
	}

	/**
	 * Cleans up residuals, etc.
	 *
	 * @param string      $backup_id Backup id.
	 * @param string      $restore_id Restore id.
	 * @param string|null $site_id (Optional) Site id.
	 */
	public function finalize_restore( $backup_id, string $restore_id = '', $site_id = null ) {
		Model\Restore::clean_residuals();

		$log = new ModelLog();
		$log->create(
			array(
				'action'  => 'snapshot_restore_completed', /* translators: %s - Backup ID */
				'details' => esc_html( sprintf( __( 'Finishing the restoration of backup id: %s', 'snapshot' ), $backup_id ) ),
			)
		);

		Log::info( __( 'Restore has been completed successfully.', 'snapshot' ), array(), $backup_id );

		if ( $restore_id ) {
			$this->stats->update( $restore_id, 'snapshot4_restore_completed' );
		}

		return array(
			'task' => 'finalize',
			'done' => true,
			'home' => get_home_url( $site_id ),
		);
	}
}