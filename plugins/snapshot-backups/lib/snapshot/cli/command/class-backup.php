<?php // phpcs:ignore
/**
 * WP CLI snapshot backup commands.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Cli\Command;

use WPMUDEV\Snapshot4\Cli\Command;
use WP_CLI;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Backup commands
 */
class Backup extends Command {

	/**
	 * List backups
	 *
	 * @usage wp snapshot backup list
	 */
	public function command_list() {
		WP_CLI::line( __( 'Loading backups...', 'snapshot' ) );
		$backups = $this->get_backups();

		if ( count( $backups ) ) {
			WP_CLI\Utils\format_items( 'table', $backups, array( 'id', 'date', 'name', 'size' ) );
		} else {
			WP_CLI::line( __( 'You haven\'t created any backups yet', 'snapshot' ) );
		}
	}

	/**
	 * Returns backup list
	 *
	 * @return array
	 */
	protected function get_backups() {
		$task  = new Task\Request\Listing();
		$model = new Model\Request\Listing();
		$model->set( 'ignore_response_log', true );
		$backups = $task->apply(
			array(
				'request_model' => $model,
				'force_refresh' => true,
			)
		);
		return $backups;
	}

	/**
	 * Returns last backup
	 *
	 * @param string|null $filter_backup_id Backup ID.
	 * @return array | null
	 */
	protected function get_last_backup( $filter_backup_id = null ) {
		$backups = $this->get_backups();
		if ( ! is_null( $filter_backup_id ) ) {
			array_filter(
				$backups,
				function ( $backup ) use ( $filter_backup_id ) {
					return $backup['id'] === $filter_backup_id;
				}
			);
			return null;
		}
		return isset( $backups[0] ) ? $backups[0] : null;
	}

	/**
	 * Returns last backup ID
	 *
	 * @return string | null
	 */
	protected function get_last_backup_id() {
		$backup = $this->get_last_backup();
		return is_array( $backup ) && isset( $backup['id'] ) ? $backup['id'] : null;
	}

	/**
	 * Backup progress
	 * wp snapshot backup status
	 */
	public function command_status() {
		$running_backup = get_site_option( 'snapshot_running_backup' );
		$id             = false;
		if ( isset( $running_backup['id'] ) ) {
			// "manual" or id
			$id = $running_backup['id'];
			if ( 'manual' === $id ) {
				$id = false;
			}
		}

		$running_backup_status = get_site_option( 'snapshot_running_backup_status' );
		if ( $running_backup_status ) {
			$index   = array_search( $running_backup_status, Model\Backup\Progress::STATUSES, true );
			$percent = intval( round( $index / ( count( Model\Backup\Progress::STATUSES ) - 1 ) * 100 ) );
			if ( $id ) {
				/* translators: %s - Backup ID */
				WP_CLI::line( sprintf( __( 'Backup with ID %s is now running', 'snapshot' ), $id ) );
			} else {
				WP_CLI::line( __( 'Backup is now running', 'snapshot' ) );
			}
			/* translators: %s - Backup progress as a percentage */
			WP_CLI::line( sprintf( __( '%s completed', 'snapshot' ), $percent . '%' ) );
		} elseif ( ! $running_backup ) {
			WP_CLI::line( __( 'No backup is running at the moment', 'snapshot' ) );
		}
	}

	/**
	 * Run new backup
	 * wp snapshot backup run
	 * wp snapshot backup run --name="Backup name" --apply-exclusions
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Command options.
	 */
	public function command_run( $args, $assoc_args ) {
		$running_backup = get_site_option( 'snapshot_running_backup' );
		if ( $running_backup ) {
			$this->command_status();
			return;
		}

		$backup_name = '';
		if ( isset( $assoc_args['name'] ) && is_string( $assoc_args['name'] ) ) {
			$backup_name = trim( $assoc_args['name'] );
		}
		$apply_exclusions = false;
		if ( ! empty( $assoc_args['apply-exclusions'] ) ) {
			$apply_exclusions = true;
		}

		$data = array(
			'backup_name'      => $backup_name,
			'description'      => '',
			'apply_exclusions' => $apply_exclusions,
		);

		$task           = new Task\Request\Manual();
		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			WP_CLI::error( __( 'Invalid backup parameters', 'snapshot' ) );
		}

		$model         = new Model\Request\Manual();
		$args          = $validated_data;
		$args['model'] = $model;
		$result        = $task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				/* translators: %s - Error message */
				WP_CLI::error( $error->get_error_message(), false );
			}
			exit( 1 );
		}

		if ( $result ) {
			WP_CLI::success( __( 'New backup initiated', 'snapshot' ) );
			return;
		}
	}

	/**
	 * Export backup
	 * wp snapshot backup download
	 * wp snapshot backup download <backup_id> --send-email --force
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Command options.
	 */
	public function command_download( $args, $assoc_args ) {
		$backup = $this->choose_backup( isset( $args[0] ) ? trim( $args[0] ) : '' );
		if ( ! $backup ) {
			return;
		}
		$backup_id = $backup['id'];

		$send_email = false;
		if ( ! empty( $assoc_args['send_email'] ) ) {
			$send_email = true;
		}

		$force_export = false;
		if ( ! empty( $assoc_args['force'] ) ) {
			$force_export = true;
		}

		$download_link = false;

		if ( ! $send_email && ! $force_export ) {
			// Check existing exports.
			$model    = new Model\Request\Export\Listing();
			$response = $model->get_list( $backup_id );
			$result   = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( is_array( $result ) ) {
				usort(
					$result,
					function ( $item1, $item2 ) {
						return $item2['created_at'] <=> $item1['created_at'];
					}
				);
				foreach ( $result as $item ) {
					if ( isset( $item['export_status'] ) && 'export_completed' === $item['export_status'] && isset( $item['download_link'] ) ) {
						$download_link = $item['download_link'];
						/* translators: %s - Export ID */
						WP_CLI::line( sprintf( __( 'Export ID: %s', 'snapshot' ), $item['export_id'] ) );
						break;
					}
				}
			}
		}//end if

		if ( $download_link ) {
			WP_CLI::success( $download_link );
			return;
		}

		$export_id = false;
		if ( is_array( $result ) ) {
			foreach ( $result as $item ) {
				if ( isset( $item['export_status'] ) && 'queued_for_export' === $item['export_status'] && isset( $item['export_id'] ) ) {
					$export_id = $item['export_id'];
					/* translators: %s - Export ID */
					WP_CLI::line( sprintf( __( 'Export ID: %s', 'snapshot' ), $export_id ) );
					break;
				}
			}
		}

		if ( ! $export_id ) {
			// Export backup if there were no exports before .
			$task           = new Task\Request\Export();
			$data           = array(
				'backup_id'  => $backup_id,
				'send_email' => $send_email,
			);
			$validated_data = $task->validate_request_data( $data );
			if ( is_wp_error( $validated_data ) ) {
				WP_CLI::error();
			}
			$args                  = $validated_data;
			$args['request_model'] = new Model\Request\Export();
			/* translators: %s - Backup ID */
			WP_CLI::line( sprintf( __( 'Requesting the API to export the backup with ID %s', 'snapshot' ), $backup_id ) );
			$result = $task->apply( $args );
			if ( $task->has_errors() ) {
				foreach ( $task->get_errors() as $error ) {
					/* translators: %s - Error message */
					WP_CLI::error( $error->get_error_message(), false );
				}
				exit( 1 );
			}
			$export_id = $result['export_id'];
			/* translators: %s - Export ID */
			WP_CLI::line( sprintf( __( 'Export ID: %s', 'snapshot' ), $export_id ) );
		}//end if

		if ( ! $export_id ) {
			WP_CLI::error();
		}

		WP_CLI::line( __( 'Waiting for export to complete...', 'snapshot' ) );

		while ( true ) {
			$export = $this->get_export( $export_id );
			if ( isset( $export['export_status'] ) && 'export_completed' === $export['export_status'] ) {
				if ( isset( $export['download_link'] ) ) {
					$download_link = $export['download_link'];
				}
				break;
			}
			sleep( 5 );
		}

		if ( $download_link ) {
			WP_CLI::success( $download_link );
			return;
		}
	}

	/**
	 * Return last backup or backup by id
	 *
	 * @param string $backup_id Backup ID.
	 * @return array|null
	 */
	protected function choose_backup( $backup_id = '' ) {
		$backup_id = trim( $backup_id );
		$backup    = null;
		if ( empty( $backup_id ) ) {
			$backup = $this->get_last_backup();
			if ( $backup && $backup['id'] ) {
				$backup_id = $backup['id'];
				/* translators: %s - Backup ID and name */
				WP_CLI::line( sprintf( __( 'Last backup: %s', 'snapshot' ), "$backup[id] - $backup[name]" ) );
			} else {
				WP_CLI::line( __( 'You haven\'t created any backups yet', 'snapshot' ) );
				return null;
			}
		} else {
			$backup = $this->get_last_backup( $backup_id );
		}
		if ( ! $backup ) {
			/* translators: %s - Backup ID */
			WP_CLI::error( sprintf( __( 'Backup with ID %s not found ', 'snapshot' ), $backup_id ) );
		}
		return $backup;
	}

	/**
	 * Returns export
	 *
	 * @param string $export_id Export ID.
	 * @return array
	 */
	protected function get_export( $export_id ) {
		$task   = new Task\Request\Export\Status();
		$model  = new Model\Request\Export\Status();
		$args   = array(
			'export_id'     => $export_id,
			'request_model' => $model,
		);
		$result = $task->apply( $args );
		if ( $task->has_errors() ) {
			WP_CLI::error( $model->get_status_error_string() );
		}
		return $result;
	}

	/**
	 * Show backup log
	 * wp snapshot backup log
	 * wp snapshot backup log <backup_id> --errors --warnings
	 *
	 * @param array $args       Command arguments.
	 * @param array $assoc_args Command options.
	 */
	public function command_log( $args, $assoc_args ) {
		$backup = $this->choose_backup( isset( $args[0] ) ? trim( $args[0] ) : '' );
		if ( ! $backup ) {
			return;
		}
		$backup_id = $backup['id'];

		$filter = null;
		if ( ! empty( $assoc_args['errors'] ) ) {
			$filter = Log::ERROR;
		}
		if ( ! empty( $assoc_args['warnings'] ) ) {
			$filter = Log::WARNING;
		}

		$log       = Log::parse_log_file( $backup_id, 0, false );
		$log_items = array_filter(
			$log['items'],
			function ( $item ) use ( $filter ) {
				if ( $filter ) {
					if ( Log::ERROR === $filter && Log::ERROR !== $item['level'] ) {
						return false;
					} elseif ( Log::WARNING === $filter && Log::ERROR !== $item['level'] && Log::WARNING !== $item['level'] ) {
						return false;
					}
				}
				return true;
			}
		);
		unset( $log['items'] );

		foreach ( $log_items as $item ) {
			switch ( $item['level'] ) {
				case Log::ERROR:
					WP_CLI::line( WP_CLI::colorize( '%R' . $item['message'] . '%n' ) );
					break;
				case Log::WARNING:
					WP_CLI::line( WP_CLI::colorize( '%y' . $item['message'] . '%n' ) );
					break;
				default:
					WP_CLI::line( $item['message'] );
			}
		}
	}
}