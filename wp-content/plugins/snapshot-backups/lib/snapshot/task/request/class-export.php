<?php // phpcs:ignore
/**
 * Export backup task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Request;

use WPMUDEV\Snapshot4\Task;

/**
 * Export backup task class.
 */
class Export extends Task {
	const ERR_SERVICE_UNREACHABLE = 'snapshot_export_backup_service_unreachable';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id'   => 'sanitize_key',
		'export_type' => 'sanitize_text_field',
	);

	/**
	 * Start the backup export process.
	 *
	 * @param array $args Optional. Additional arguments for the request.
	 *
	 * @return mixed
	 */
	public function apply( $args = array() ) {
		$request_model = $args['request_model'];
		$send_email    = ( isset( $args['send_email'] ) && false === $args['send_email'] ) ? false : true;
		$email         = ( isset( $args['email_account'] ) ) ? $args['email_account'] : '';
		$export_type   = ( isset( $args['export_type'] ) ) ? $args['export_type'] : 'full_backup';

		$data = array(
			'backup_id'   => $args['backup_id'],
			'send_email'  => $send_email,
			'email'       => $email,
			'export_type' => $export_type,
		);

		if ( isset( $args['subsite_id'] ) ) {
			$data['subsite_id']   = $args['subsite_id'];
			$data['subsite_name'] = $args['subsite_name'];
		}

		$response = $request_model->export_backup( $data );

		if ( $request_model->add_errors( $this ) ) {
			return false;
		}

		$result = json_decode( wp_remote_retrieve_body( $response ), true );

		return $result;
	}
}