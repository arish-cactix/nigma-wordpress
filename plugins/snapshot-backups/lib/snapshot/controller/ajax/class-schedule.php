<?php // phpcs:ignore
/**
 * Snapshot controllers: Schedule AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Model\Log as ModelLog;

/**
 * Schedule AJAX controller class
 */
class Schedule extends Controller\Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		// Request the service actions regarding backup schedules.
		add_action( 'wp_ajax_snapshot-backup_schedule', array( $this, 'json_backup_schedule' ) );
		add_action( 'wp_ajax_snapshot-get_schedule', array( $this, 'json_get_schedule' ) );
	}

	/**
	 * Handles requesting the service for actions about backup schedules and stores schedules locally.
	 */
	public function json_backup_schedule() {
		$this->do_request_sanity_check( 'snapshot_backup_schedule', self::TYPE_POST );

		$request_model = new Model\Request\Schedule();
		$request_data  = $request_model->validate_schedule_data();

		if ( is_wp_error( $request_data ) ) {
			wp_send_json_error( $request_data );
		}

		if ( ! empty( $request_data['data']['frequency'] ) ) {
			$converted = Model\Schedule::convert_timezone(
				$request_data['data']['frequency'],
				wp_timezone(),
				new \DateTimeZone( 'UTC' ),
				$request_data['data']['time'],
				isset( $request_data['data']['frequency_weekday'] ) ? $request_data['data']['frequency_weekday'] : null,
				isset( $request_data['data']['frequency_monthday'] ) ? $request_data['data']['frequency_monthday'] : null
			);

			$request_data['data']['time']               = $converted['time'];
			$request_data['data']['frequency_weekday']  = $converted['weekday'];
			$request_data['data']['frequency_monthday'] = $converted['monthday'];
		}

		$schedule_model = new Model\Schedule( $request_data['data'] );

		$args                   = array();
		$args['request_model']  = $request_model;
		$args['schedule_model'] = $schedule_model;
		$args['action']         = $request_data['schedule_action'];

		$task = new Task\Request\Schedule();

		$response = $task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}

			wp_send_json_error();
		}

		$log = new ModelLog();
		$log->create( array( 'action' => 'backup_schedule_modified' ) );

		// Response to "service".
		wp_send_json_success(
			array(
				'api_response' => $response,
				'schedule'     => Model\Schedule::get_schedule_info( true ),
			)
		);
	}

	/**
	 * Returns current schedule
	 */
	public function json_get_schedule() {
		$this->do_request_sanity_check( 'snapshot_get_schedule', self::TYPE_GET );

		$response = Model\Schedule::get_schedule_info();

		wp_send_json_success( $response );
	}
}