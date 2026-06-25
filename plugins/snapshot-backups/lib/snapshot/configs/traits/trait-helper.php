<?php // phpcs:ignore
/**
 * Config Helper.
 *
 * @since   4.5.0
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Configs\Traits;

use Exception;
use WPMUDEV\Snapshot4\Helper\Api;
use WPMUDEV\Snapshot4\Model\Log;
use WPMUDEV\Snapshot4\Model\Request\Region as RequestRegion;
use WPMUDEV\Snapshot4\Model\Request\Schedule;
use WPMUDEV\Snapshot4\Model\Schedule as ModelSchedule;
use WPMUDEV\Snapshot4\Model\Request\Delete as ModelDelete;
use WPMUDEV\Snapshot4\Task\Request\Region;
use WPMUDEV\Snapshot4\Task\Request\Delete;
use WPMUDEV\Snapshot4\Task\Request\Schedule as RequestSchedule;

trait Helper {

	/**
	 * Region
	 *
	 * @var array
	 */
	protected $regions = array(
		'US',
		'EU',
	);

	/**
	 * Config values for display.
	 *
	 * @param array $settings Snapshot settings.
	 * @return array
	 */
	public function get_config_strings( $settings ) {
		$strings  = array();
		$enabled  = esc_html__( 'Active', 'snapshot' );
		$disabled = esc_html__( 'Inactive', 'snapshot' );

		if ( isset( $settings['settings'] ) && is_array( $settings['settings'] ) ) {
			$settings = $settings['settings'];
		}

		if ( ! is_array( $settings ) || empty( $settings ) ) {
			return $strings;
		}

		$strings['notifications'] = $disabled;
		$strings['exclusions']    = $disabled;

		if ( isset( $settings['schedule'] ) ) {
			$strings['schedule'] = ( null === $settings['schedule']['frequency'] ) ? $disabled : $settings['schedule']['human_readable'];
		}

		if ( isset( $settings['region'] ) ) {
			$strings['region'] = strtoupper( $settings['region'] );
		}

		if ( isset( $settings['limit'] ) ) {
			$strings['limit'] = absint( $settings['limit'] );
		}

		if ( isset( $settings['notifications'] ) && ! empty( $settings['notifications'] ) ) {
			$notifications            = $settings['notifications'];
			$strings['notifications'] = boolval( $notifications['on_fail_send'] ) ? $enabled : $disabled;
		}

		if ( isset( $settings['exclusions'] ) ) {
			$strings['exclusions'] = boolval( $settings['exclusions'] ) ? $enabled : $disabled;
		}

		if ( isset( $settings['options'] ) ) {
			$options            = $settings['options'];
			$strings['options'] = ( 0 === intval( $options['remove_data'] ) ) ? __( 'Keep', 'snapshot' ) : __( 'Remove', 'snapshot' );
		}

		return $strings;
	}

	/**
	 * Validate and Sanitize the uploaded configs file.
	 *
	 * @param array $configs Config list.
	 *
	 * @return array
	 */
	public function validate_configs_data( $configs ) {

		$validated = array();
		$settings  = array();

		if ( isset( $configs['settings'] ) && is_array( $configs['settings'] ) ) {
			$settings = $configs['settings'];
		}

		if ( ! is_array( $settings ) || empty( $settings ) ) {
			return $validated;
		}

		if ( isset( $settings['schedule'] ) ) {
			$validated['schedule'] = array_map( 'wp_strip_all_tags', $settings['schedule'] );
		}

		if ( isset( $settings['region'] ) && in_array( strtoupper( $settings['region'] ), $this->regions, true ) ) {
			$validated['region'] = strtoupper( $settings['region'] );
		}

		if ( isset( $settings['limit'] ) ) {
			$validated['limit'] = absint( $settings['limit'] );
		}

		if ( isset( $settings['notifications'] ) && ! empty( $settings['notifications'] ) ) {
			$validated['notifications'] = array_map( 'boolval', $settings['notifications'] );
		} else {
			$validated['notifications'] = false;
		}

		if ( isset( $settings['options'] ) ) {
			$validated['options'] = array_map( 'boolval', $settings['options'] );
		}

		if ( isset( $settings['exclusions'] ) ) {
			$validated['exclusions'] = boolval( $settings['exclusions'] );
		}

		if ( isset( $settings['exclusions_list'] ) ) {
			$validated['exclusions_list'] = array_map( 'wp_normalize_path', $settings['exclusions_list'] );
		}

		return array(
			'settings' => $validated,
		);
	}

	/**
	 * Get the default storage region.
	 *
	 * @return mixed
	 */
	public function get_storage_region_info() {
		$data           = array();
		$data['action'] = 'get';

		// Task.
		$region           = new Region();
		$validated_params = $region->validate_request_data( $data );

		$args                  = $validated_params;
		$args['request_model'] = new RequestRegion();
		$result                = $region->apply( $args );

		return $result;
	}

	/**
	 * Decode and validate the config file.
	 *
	 * @param array $file Uploaded file.
	 *
	 * @return array
	 * @throws Exception When there is an error with uploaded file.
	 */
	public function decode_and_validate_config_file( $file ) {
		if ( ! $file ) {
			throw new Exception( esc_html__( 'The configs file is required', 'snapshot' ) );
		} elseif ( ! empty( $file['error'] ) ) {
			/* translators: error message */
			throw new Exception( esc_html( sprintf( __( 'Error: %s.', 'snapshot' ), $file['error'] ) ) );
		} elseif ( 'application/json' !== $file['type'] ) {
			throw new Exception( esc_html( __( 'The file must be a JSON.', 'snapshot' ) ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$json_file = file_get_contents( $file['tmp_name'] );

		if ( ! $json_file ) {
			throw new Exception( esc_html( __( 'There was an error getting the contents of the file.', 'snapshot' ) ) );
		}

		$configs = json_decode( $json_file, true );
		if ( empty( $configs ) || ! is_array( $configs ) ) {
			throw new Exception( esc_html( __( 'There was an error decoding the file.', 'snapshot' ) ) );
		}

		// Make sure the config has a name and configs.
		if ( empty( $configs['name'] ) || empty( $configs['config'] ) ) {
			throw new Exception( esc_html( __( 'The uploaded config must have a name and a set of settings. Please make sure the uploaded file is the correct one.', 'snapshot' ) ) );
		}

		// Sanitize.
		$configs['config'] = array(
			'configs' => $this->validate_configs_data( $configs['config']['configs'] ),
			// Let's re-create this to avoid differences between imported settings coming from other versions.
			'strings' => $this->get_config_strings( $configs['config']['configs'] ),
		);

		if ( empty( $configs['config']['configs'] ) ) {
			throw new Exception( esc_html( __( 'The provided configs list isn’t correct. Please make sure the uploaded file is the correct one.', 'snapshot' ) ) );
		}

		// Don't keep these if they exist.
		unset( $configs['hub_id'] );
		unset( $configs['default'] );

		return $configs;
	}

	/**
	 * Save uploaded config.
	 *
	 * @since 4.5.0
	 *
	 * @param array $file  Uploaded file.
	 */
	public function save_uploaded_config( $file ) {
		try {
			$config = $this->decode_and_validate_config_file( $file );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'error_saving', $e->getMessage() );
		}

		return $config;
	}

	/**
	 * Set the region.
	 *
	 * @param string $region EU|US.
	 * @return mixed
	 */
	public function set_region( $region ) {
		$task = new Region();

		$validated = $task->validate_request_data( array( 'action' => 'set' ) );

		$args                  = $validated;
		$args['request_model'] = new RequestRegion();
		$args['region']        = strtoupper( $region );

		$response = $task->apply( $args );

		if ( ! $task->has_errors() ) {
			return $response;
		}

		return $task->get_errors()[0];
	}

	/**
	 * Set storage limit.
	 *
	 * @param int $limit Backup limit.
	 * @return mixed
	 */
	public function set_limit( $limit ) {
		$task      = new Region();
		$validated = $task->validate_request_data( array( 'action' => 'set_storage' ) );

		$args                  = $validated;
		$args['request_model'] = new RequestRegion();
		$args['storage_limit'] = $limit;

		$response = $task->apply( $args );

		if ( ! $task->has_errors() ) {
			return $response;
		}

		return $task->get_errors()[0];
	}

	/**
	 * Set snapshot schedule.
	 *
	 * @param string $schedule Snapshot scheduled backup schedule.
	 * @return mixed
	 */
	public function set_schedule( $schedule ) {

		$membership_type = Api::get_dashboard_membership_type();

		if ( 'free' === $membership_type || 'free_hub' === $membership_type ) {
			$schedule = array(
				'frequency'          => 'monthly',
				'frequency_weekday'  => null,
				'frequency_monthday' => 2,
				'time'               => '00:00',
				'files'              => 'all',
				'tables'             => 'all',
				'status'             => 'active',
			);
		}

		if ( isset( $schedule['human_readable'] ) ) {
			unset( $schedule['human_readable'] );
		}

		$formatted = array();
		$converted = ModelSchedule::convert_timezone(
			$schedule['frequency'],
			wp_timezone(),
			new \DateTimeZone( 'UTC' ),
			$schedule['time'],
			isset( $schedule['frequency_weekday'] ) ? $schedule['frequency_weekday'] : null,
			isset( $schedule['frequency_monthday'] ) ? $schedule['frequency_monthday'] : null
		);

		if ( isset( $schedule['frequency'] ) && in_array( strtolower( $schedule['frequency'] ), array( 'daily', 'weekly', 'monthly' ), true ) ) {
			$formatted['schedule_action'] = 'update';
		} else {
			$formatted['schedule_action'] = 'delete';
		}

		$formatted['data'] = array(
			'frequency'          => $schedule['frequency'],
			'files'              => $schedule['files'],
			'tables'             => $schedule['tables'],
			'time'               => $converted['time'],
			'frequency_weekday'  => $converted['weekday'],
			'frequency_monthday' => $converted['monthday'],
			'status'             => 'active',
		);

		$request_model  = new Schedule();
		$schedule_model = new ModelSchedule( $formatted['data'] );

		$args                   = array();
		$args['request_model']  = $request_model;
		$args['schedule_model'] = $schedule_model;
		$args['action']         = $formatted['schedule_action'];

		$task     = new RequestSchedule();
		$response = $task->apply( $args );

		if ( ! $task->has_errors() ) {
			$log = new Log();
			$log->create( array( 'action' => 'schedule_is_changed_after_configs_application' ) );

			return $response;
		}

		return $task->get_errors()[0];
	}

	/**
	 * Get the schedule info.
	 *
	 * @return mixed
	 */
	public function get_formatted_schedule() {
		$schedule = ModelSchedule::get_schedule_info();

		return array(
			'frequency'          => $schedule['values']['frequency'],
			'frequency_weekday'  => $schedule['values']['frequency_weekday'],
			'frequency_monthday' => $schedule['values']['frequency_monthday'],
			'time'               => $schedule['values']['time'],
			'human_readable'     => $schedule['text'],
			'files'              => 'all',
			'tables'             => 'all',
			'status'             => 'active',
		);
	}

	/**
	 * Deletes all the backups once the region is changed.
	 *
	 * @return boolean
	 */
	public function delete_all_backups() {
		$task = new Delete();

		$args                  = array();
		$args['request_model'] = new ModelDelete();

		$task->apply( $args );

		if ( ! $task->has_errors() ) {
			return true;
		}

		return false;
	}
}