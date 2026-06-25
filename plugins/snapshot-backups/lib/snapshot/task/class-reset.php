<?php // phpcs:ignore
/**
 * Reset settings task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Reset settings task class
 */
class Reset extends Task {

	/**
	 * Does the initial actions needed to trigger a restore.
	 *
	 * @param array $args Restore arguments, like backup_id and rootpath.
	 */
	public function apply( $args = array() ) {
		// Reset the schedule, by making it inactive.
		$schedule_model = new Model\Schedule( array() );
		$request_model  = new Model\Request\Schedule();

		$reset_schedule_args                   = array();
		$reset_schedule_args['request_model']  = $request_model;
		$reset_schedule_args['schedule_model'] = $schedule_model;
		$reset_schedule_args['action']         = 'delete';

		$task = new Task\Request\Schedule();

		$task->apply( $reset_schedule_args );
		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}

			wp_send_json_error();
		}

		// Then, lets try and reset storage limit to default, 30 backups.
		$storage_data           = array();
		$storage_data['action'] = 'set_storage';

		$storage_task             = new Task\Request\Region();
		$storage_validated_params = $storage_task->validate_request_data( $storage_data );

		$storage_args                  = $storage_validated_params;
		$storage_args['request_model'] = new Model\Request\Region();
		$storage_args['storage_limit'] = 30;

		$changed_storage = $storage_task->apply( $storage_args );

		if ( $storage_task->has_errors() ) {
			foreach ( $storage_task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}
			wp_send_json_error();
		}

		// Then, lets try and delete ALL destinations.
		$destination_data = array(
			'tpd_action' => 'delete_all_destinations',
		);

		$destination_task = new Task\Request\Destination( $destination_data['tpd_action'] );
		$validated_data   = $destination_task->validate_request_data( $destination_data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		$args                  = $validated_data;
		$args['request_model'] = new Model\Request\Destination();
		$destination_task->apply( $args );

		if ( $destination_task->has_errors() ) {
			foreach ( $destination_task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}
			wp_send_json_error();
		}

		delete_site_option( 'snapshot_global_exclusions' );
		delete_site_option( 'snapshot_remove_on_uninstall' );
		delete_site_option( 'snapshot_email_settings' );
		delete_site_option( 'snapshot_exclude_large' );
		delete_site_option( 'snapshot_started_seen' );

		global $wpdb;
		update_site_option(
			'snapshot_excluded_tables',
			array(
				$wpdb->prefix . 'defender_lockout_log',
			)
		);
	}
}