<?php // phpcs:ignore
/**
 * Snapshot models: Schedule requests model
 *
 * Holds information for communication with the service about processing backup schedules.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Backup schedule requests model class
 */
class Schedule extends Model\Request {
	const DEFAULT_ERROR = 'snapshot_schedule_service_unreachable';

	/**
	 * Schedule request endpoint
	 *
	 * @var string
	 */

	protected $endpoint = 'schedules';

	/**
	 * Current request action
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Returns action string for logger
	 *
	 * @return string
	 */
	protected function get_action_string() {
		$action_string = parent::get_action_string();

		switch ( strtolower( $this->action ) ) {
			case 'create':
				$action_string = __( 'create a backup schedule', 'snapshot' );
				break;
			case 'get_status':
				$action_string = __( 'get info about a backup schedule', 'snapshot' );
				break;
			case 'update':
				$action_string = __( 'update a backup schedule', 'snapshot' );
				break;
			case 'delete':
				$action_string = __( 'delete a backup schedule', 'snapshot' );
				break;
		}

		return $action_string;
	}

	/**
	 * Maps action params to endpoint paths and methods.
	 *
	 * @param string         $action The action coming from the ajax endpoint.
	 * @param Model\Schedule $schedule Model\Schedule instance.
	 *
	 * @return array|mixed|object
	 */
	public function schedule_request( $action, Model\Schedule $schedule ) {
		$this->action = $action;

		$data = array();

		$data['bu_frequency'] = $schedule->get( 'frequency' );
		$data['bu_status']    = $schedule->get( 'status' );
		$data['bu_files']     = $schedule->get( 'files' );
		$data['bu_tables']    = $schedule->get( 'tables' );

		$data['site_name'] = $this->get_this_site();

		$data['bu_time']               = $schedule->get( 'time' );
		$data['bu_frequency_weekday']  = $schedule->get( 'frequency_weekday', null );
		$data['bu_frequency_monthday'] = $schedule->get( 'frequency_monthday', null );

		$data['bu_exclusion_enabled'] = true;
		// Exclusions always enabled for scheduled backups.

		$data['plugin_v'] = defined( 'SNAPSHOT_BACKUPS_VERSION' ) ? SNAPSHOT_BACKUPS_VERSION : null;

		$path = $this->get_api_url();

		switch ( strtolower( $action ) ) {
			case 'create':
				$data['site_id'] = Helper\Api::get_site_id();
				$method          = 'post';
				// request: /site_id/schedules - POST.

				break;
			case 'get_status':
				$method = 'get';
				$data   = array();

				$path .= '/' . $schedule->get( 'schedule_id' );
				// request: /site_id/schedules/schedule_id - GET.

				break;

			case 'get_status_all':
				$method = 'get';
				$data   = array();
				// request: /site_id/schedules - GET.

				break;
			case 'update':
				$method = 'put';

				$path .= '/' . $schedule->get( 'schedule_id' );
				break;
			case 'delete':
				$method            = 'put';
				$data['bu_status'] = 'inactive';

				if ( 'null' === $data['bu_frequency_weekday'] ) {
					$data['bu_frequency_weekday'] = null;
				}
				if ( 'null' === $data['bu_frequency_monthday'] ) {
					$data['bu_frequency_monthday'] = null;
				}

				$path .= '/' . $schedule->get( 'schedule_id' );
				break;
			case 'hard_delete':
				$method = 'DELETE';
				$data   = array();

				$path .= '/' . $schedule->get( 'schedule_id' );
				break;
			default:
				break;
		}//end switch
		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Validates data before schedule requests.
	 *
	 * @param array $hub_args Array with Hub request's args or empty array if AJAX call.
	 *
	 * @return array|object
	 */
	public function validate_schedule_data( $hub_args = array() ) {
		if ( isset( $hub_args['schedule_action'] ) ) {
			$schedule_action = $hub_args['schedule_action'];
		} else {
			// phpcs:ignore
			$schedule_action = sanitize_key( isset( $_POST['schedule_action'] ) ? wp_unslash( $_POST['schedule_action'] ) : '' );
		}

		if ( ! $schedule_action ) {
			$error = new \WP_Error( 'no_schedule_action', 'schedule_action is required and was not included in the request.' );
			Log::error( __( 'Request for a backup schedule was not successful - A schedule_action parameter must be provided', 'snapshot' ) );
			return $error;
		}

		$allowed_actions = array( 'create', 'get_status', 'update', 'delete' );
		if ( ! in_array( $schedule_action, $allowed_actions, true ) ) {
			$allowed_actions = implode( ', ', $allowed_actions );
			$error           = new \WP_Error( 'invalid_schedule_action', "schedule_action must be one of: $allowed_actions." );
			/* translators: %s - allowed actions */
			Log::error( sprintf( __( 'Request for a backup schedule was not successful - A schedule_action must be one of: %s.', 'snapshot' ), $allowed_actions ) );
			return $error;
		}

		$data = array();

		if ( 'create' === $schedule_action || 'update' === $schedule_action ) {
			if ( isset( $hub_args['new_schedule'] ) ) {
				$data = $hub_args['new_schedule'];
			} else {
				// phpcs:ignore
				$data = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
			}
			if ( ! $data ) {
				$error = new \WP_Error( 'no_data', 'data is required and was not included in the request.' );
				/* translators: %s - scheduled action */
				Log::error( sprintf( __( 'Request for a backup schedule (%s) was not successful - A data parameter must be provided', 'snapshot' ), $schedule_action ) );
				return $error;
			}

			$data = json_decode( $data, true );
			if ( ! $data ) {
				$error = new \WP_Error( 'invalid_data', 'data parameter is invalid.' );
				/* translators: %s - scheduled action */
				Log::error( sprintf( __( 'Request for a backup schedule (%s) was not successful - A data parameter is invalid', 'snapshot' ), $schedule_action ) );
				return $error;
			}

			$required_fields = array( 'frequency', 'status', 'time', 'files', 'tables' );
			$empty_fields    = array();
			foreach ( $required_fields as $required_field ) {
				if ( ! array_key_exists( $required_field, $data ) ) {
					$empty_fields[] = $required_field;
				} else {
					$data[ $required_field ] = sanitize_text_field( $data[ $required_field ] );
				}
			}
			if ( isset( $data['frequency'] ) ) {
				if ( 'weekly' === $data['frequency'] && empty( $data['frequency_weekday'] ) ) {
					$empty_fields[] = 'frequency_weekday';
				} elseif ( 'monthly' === $data['frequency'] && empty( $data['frequency_monthday'] ) ) {
					$empty_fields[] = 'frequency_monthday';
				}
			}
			if ( count( $empty_fields ) ) {
				$empty_fields = implode( ', ', $empty_fields );
				$error        = new \WP_Error( 'invalid_data_fields', "data parameter is invalid, empty fields: $empty_fields" );
				/* translators: %s - scheduled action */
				Log::error( sprintf( __( 'Request for a backup schedule (%1$s) was not successful - A data parameter is invalid, empty fields: %2$s', 'snapshot' ), $schedule_action, $empty_fields ) );
				return $error;
			}

			if ( ! preg_match( '/^(([01]\d)|(2[0-3])):[0-5]\d$/', $data['time'] ) ) {
				$error = new \WP_Error( 'invalid_data_time', 'time in data parameter is invalid.' );
				/* translators: %s - scheduled action */
				Log::error( sprintf( __( 'Request for a backup schedule (%s) was not successful - Time in data parameter is invalid', 'snapshot' ), $schedule_action ) );
				return $error;
			}

			if ( isset( $data['frequency_weekday'] ) ) {
				$data['frequency_weekday'] = intval( $data['frequency_weekday'] );
				if ( ! in_array( $data['frequency_weekday'], range( 1, 7 ), true ) ) {
					$error = new \WP_Error( 'invalid_data_frequency_weekday', 'frequency_weekday in data parameter is invalid.' );
					/* translators: %s - scheduled action */
					Log::error( sprintf( __( 'Request for a backup schedule (%s) was not successful - frequency_weekday in data parameter is invalid', 'snapshot' ), $schedule_action ) );
					return $error;
				}
			}

			if ( isset( $data['frequency_monthday'] ) ) {
				$data['frequency_monthday'] = intval( $data['frequency_monthday'] );
				if ( $data['frequency_monthday'] < 1 || $data['frequency_monthday'] > 28 ) {
					$error = new \WP_Error( 'invalid_data_frequency_monthday', 'frequency_monthday in data parameter is invalid.' );
					/* translators: %s - scheduled action */
					Log::error( sprintf( __( 'Request for a backup schedule (%s) was not successful - frequency_monthday in data parameter is invalid', 'snapshot' ), $schedule_action ) );
					return $error;
				}
			}
		}//end if

		$request_data = array(
			'schedule_action' => $schedule_action,
			'data'            => $data,
		);
		return $request_data;
	}
}