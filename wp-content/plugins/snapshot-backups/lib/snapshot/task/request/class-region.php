<?php // phpcs:ignore
/**
 * Get/Set creds task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request;

use WPMUDEV\Snapshot4\Model\Log;
use WPMUDEV\Snapshot4\Task;

/**
 * Gets/sets creds (region, storage limit) from/to the service task class.
 */
class Region extends Task {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'action' => self::class . '::validate_action',
	);

	/**
	 * Validates creds action.
	 *
	 * @param string $action Action coming from controller.
	 *
	 * @return string
	 */
	public static function validate_action( $action ) {
		return ( 'get' === $action || 'set' === $action || 'set_storage' === $action ) ? $action : null;
	}

	/**
	 * Get/Set creds.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		$request_model = $args['request_model'];
		$action        = $args['action'];
		$region        = isset( $args['region'] ) ? $args['region'] : null;
		$storage_limit = isset( $args['storage_limit'] ) ? $args['storage_limit'] : 30;

		$request_model->set( 'action', $action );

		if ( 'get' === $action ) {
			// site id *** have no region set yet.
			$request_model->set( 'ok_codes', array( 404 ) );

			$result = $request_model->get_credsls();

			if ( $request_model->add_errors( $this ) ) {
				return false;
			}

			return $result;
		}

		if ( 'set' === $action ) {
			$response = $request_model->set_region( $region );

			if ( $request_model->add_errors( $this ) ) {
				return false;
			}

			$result = json_decode( wp_remote_retrieve_body( $response ), true );

			$log = new Log();
			$log->create(
				array(
					'action'  => 'snapshot_region_changed',
					'details' => 'Snapshot region is changed.',
				)
			);
			return isset( $result['bu_region'] ) ? $result['bu_region'] : null;
		}

		if ( 'set_storage' === $action ) {
			$response = $request_model->set_storage( $storage_limit );

			if ( $request_model->add_errors( $this ) ) {
				return false;
			}

			$log = new Log();
			$log->create(
				array(
					'action'  => 'snapshot_storage_limit_changed',
					'details' => 'Snapshot storage limit is changed.',
				)
			);

			$result = json_decode( wp_remote_retrieve_body( $response ), true );
			return isset( $result['rotation_frequency'] ) ? $result['rotation_frequency'] : null;
		}
	}
}