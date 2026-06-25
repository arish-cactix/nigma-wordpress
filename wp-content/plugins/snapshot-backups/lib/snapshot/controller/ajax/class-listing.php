<?php // phpcs:ignore
/**
 * Snapshot controllers: Backup listing AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Backup listing AJAX controller class
 */
class Listing extends Controller\Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		// Request the service actions regarding backup schedules.
		add_action( 'wp_ajax_snapshot-list_backups', array( $this, 'json_list_backups' ) );
	}

	/**
	 * Handles requesting the service for actions about backup listing.
	 */
	public function json_list_backups() {
		$this->do_request_sanity_check( 'snapshot_list_backups', self::TYPE_POST );

		$task = new Task\Request\Listing();

		$request_model = new Model\Request\Listing();

		$args                  = array();
		$args['request_model'] = $request_model;
		// phpcs:ignore WordPress.Security.NonceVerification
		$args['force_refresh'] = isset( $_POST['force_refresh'] ) ? boolval( $_POST['force_refresh'] ) : false;

		$backups = $task->apply( $args );

		if ( $task->has_errors() ) {
			$errors = array();
			foreach ( $task->get_errors() as $error ) {
				$errors[] = $error;
				Log::error( $error->get_error_message() );
			}

			wp_send_json_error( $errors );
		}

		$backup_running = get_site_option( self::SNAPSHOT_RUNNING_BACKUP );

		$destination_backup_count = array();
		foreach ( $backups as $backup ) {
			foreach ( $backup['done_tpd_ids'] as $tpd_id ) {
				if ( ! isset( $destination_backup_count[ $tpd_id ] ) ) {
					$destination_backup_count[ $tpd_id ] = 0;
				}
				++$destination_backup_count[ $tpd_id ];
			}
		}

		wp_send_json_success(
			array(
				'backups'                  => $backups,
				'backup_running'           => $backup_running,
				'failed_backups'           => $request_model->get( 'failed_backups' ),
				'backup_count'             => count( $backups ) - $request_model->get( 'failed_backups' ),
				'destination_backup_count' => $destination_backup_count,
			)
		);
	}
}