<?php // phpcs:ignore
/**
 * Snapshot models: Export backup requests model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Model;

/**
 * Export backup requests model class
 */
class Export extends Model\Request {
	const DEFAULT_ERROR = 'snapshot_export_backup_service_unreachable';

	/**
	 * Export backup request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'exports';

	/**
	 * Returns action string for logger
	 *
	 * @return string
	 */
	protected function get_action_string() {
		return __( 'export backup', 'snapshot' );
	}

	/**
	 * Returns string to be used on errors during restore.
	 *
	 * @return string
	 */
	public function get_trigger_error_string() {
		return esc_html__( 'requesting for a backup export', 'snapshot' );
	}

	/**
	 * Start backup export process.
	 *
	 * @param array $export_args Arguments for the export.
	 *
	 * @return mixed
	 */
	public function export_backup( array $export_args ) {
		$method = 'post';
		$path   = $this->get_api_url();

		$data = array(
			'snapshot_id' => $export_args['backup_id'],
			'send_email'  => $export_args['send_email'],
			'export_type' => $export_args['export_type'],
		);

		if ( isset( $export_args['subsite_id'] ) ) {
			$data['subsite_id']   = $export_args['subsite_id'];
			$data['subsite_name'] = $export_args['subsite_name'];
		}

		if ( isset( $export_args['email'] ) && ! empty( $export_args['email'] ) ) {
			$data['email_account'] = $export_args['email'];
		}

		$response = $this->request( $path, $data, $method );

		return $response;
	}
}