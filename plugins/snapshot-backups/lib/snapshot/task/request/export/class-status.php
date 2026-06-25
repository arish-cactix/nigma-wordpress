<?php // phpcs:ignore
/**
 * Status of export.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request\Export;

use WPMUDEV\Snapshot4\Task;

/**
 * Export status requesting class
 */
class Status extends Task {

	const ERROR_EXPORT_FAIL = 'snapshot_export_failed';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'export_id' => 'sanitize_text_field',
	);

	/**
	 * Places the request calls to the service for retrieving the export status.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		$request_model = $args['request_model'];

		$response = $request_model->get_status( $args['export_id'] );
		if ( $request_model->add_errors( $this ) ) {
			return false;
		}

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $result['export_status'] ) && 0 === strpos( $result['export_status'], 'export_failed_' ) ) {
			$this->add_error(
				self::ERROR_EXPORT_FAIL,
				__( 'The backup export failed to complete.', 'snapshot' )
			);
			return false;
		}

		return $result;
	}
}