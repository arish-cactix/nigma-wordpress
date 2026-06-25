<?php // phpcs:ignore
/**
 * Setting, updating, deleting etc. of backup schedules between plugin and service.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;

/**
 * Schedule requesting class
 */
class Schedule extends Task {

	const ERR_STRING_REQUEST_PARAMS = 'Request for a backup schedule was not successful';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'frequency' => 'sanitize_text_field',
		'status'    => 'sanitize_text_field',
		'time'      => self::class . '::validate_time',
		'files'     => 'sanitize_text_field',
		'tables'    => 'sanitize_text_field',
	);

	/**
	 * Checks time format (H:i)
	 *
	 * @param string $time Time string.
	 *
	 * @return string|false
	 */
	public static function validate_time( $time ) {
		return preg_match( '/^(([01]\d)|(2[0-3])):[0-5]\d$/', $time ) ? $time : false;
	}

	/**
	 * Requests current schedule
	 *
	 * @return false|null|array
	 */
	private function fetch_current_schedule() {
		$request_model = new Model\Request\Schedule();
		$request_model->set( 'ok_codes', array( 404 ) );
		$response = $request_model->schedule_request( 'get_status_all', new Model\Schedule( array() ) );
		if ( $request_model->add_errors( $this ) ) {
			return false;
		}
		$response_code     = wp_remote_retrieve_response_code( $response );
		$current_schedules = json_decode( wp_remote_retrieve_body( $response ), true );
		$current_schedule  = null;
		if ( isset( $current_schedules[0] ) && 200 === $response_code ) {
			$current_schedule = $current_schedules[0];
		}

		return $current_schedule;
	}

	/**
	 * Returns current schedule
	 *
	 * @param bool $no_cache true if don't need to cache in static var.
	 * @return \WP_Error|null|array
	 */
	public static function get_current_schedule( $no_cache = false ) {
		static $schedule = null;

		if ( ! is_null( $schedule ) && ! $no_cache ) {
			return $schedule;
		}

		$task     = new self();
		$response = $task->fetch_current_schedule();
		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				return $error;
			}
		}
		$schedule = $response;
		return $schedule;
	}

	/**
	 * Places the request calls to the service for processing the backup schedule.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		$request_model  = $args['request_model'];
		$schedule_model = $args['schedule_model'];
		$action         = $args['action'];

		$current_schedule = $this->fetch_current_schedule();
		if ( $this->has_errors() ) {
			return false;
		}
		if ( $current_schedule ) {
			$schedule_model->set( 'schedule_id', $current_schedule['schedule_id'] );
		}

		if ( 'delete' === $action ) {
			if ( ! $current_schedule || 'inactive' === $current_schedule['bu_status'] ) {
				return;
			}
			$schedule_model->set( 'frequency', $current_schedule['bu_frequency'] );
			$schedule_model->set( 'files', $current_schedule['bu_files'] );
			$schedule_model->set( 'tables', $current_schedule['bu_tables'] );
			$schedule_model->set( 'time', $current_schedule['bu_time'] );
			$schedule_model->set( 'frequency_weekday', $current_schedule['bu_frequency_weekday'] );
			$schedule_model->set( 'frequency_monthday', $current_schedule['bu_frequency_monthday'] );

		} elseif ( 'create' === $action && $current_schedule ) {
			$action = 'update';
		} elseif ( 'update' === $action && ! $current_schedule ) {
			$action = 'create';
		}

		$request_model->set( 'ok_codes', array() );

		$response = $request_model->schedule_request( $action, $schedule_model );
		if ( $request_model->add_errors( $this ) ) {
			return false;
		}

		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
		$schedule_model->set_data( $response_data );

		return $response_data;
	}
}