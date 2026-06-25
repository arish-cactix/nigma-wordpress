<?php // phpcs:ignore

/**
 * Snapshot Controllers: FTP Destination AJAX controller class.
 *
 * @package Snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax\Destination;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Model;

/**
 * Class: FTP destination
 */
class Ftp extends Controller\Ajax\Destination {

	/**
	 * Boots the controller and sets up the event listeners.
	 *
	 * @return void|false
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		add_action( 'wp_ajax_snapshot-ftp_connection', array( $this, 'json_ftp_connection' ) );
	}

	/**
	 * AJAX Handler: "Test Connection" for ftp/sftp destination
	 *
	 * @return void
	 */
	public function json_ftp_connection() {
		$this->do_request_sanity_check( 'snapshot_ftp_connection', self::TYPE_POST );

		$data = array(
			'tpd_type'            => isset( $_POST['tpd_type'] ) ? $_POST['tpd_type'] : null, // phpcs:ignore
			'tpd_accesskey'       => isset( $_POST['tpd_accesskey'] ) ? $_POST['tpd_accesskey'] : null,	//phpcs:ignore
			'tpd_secretkey'       => isset( $_POST['tpd_secretkey'] ) ? $_POST['tpd_secretkey'] : null, // phpcs:ignore
			'tpd_action'          => isset( $_POST['tpd_action'] ) ? $_POST['tpd_action'] : null, // phpcs:ignore
			'tpd_path'            => isset( $_POST['tpd_path'] ) ? $_POST['tpd_path'] : null, // phpcs:ignore
			'tpd_name'            => isset( $_POST['tpd_name'] ) ? $_POST['tpd_name'] : null, // phpcs:ignore
			'tpd_limit'           => isset( $_POST['tpd_limit'] ) ? intval( $_POST['tpd_limit'] ) : 5, // phpcs:ignore
			'tpd_save'            => isset( $_POST['tpd_save'] ) ? intval( $_POST['tpd_save'] ) : null, // phpcs:ignore
			'ftp_host'            => isset( $_POST['ftp-host'] ) ? $_POST['ftp-host'] : null, // phpcs:ignore
			'ftp_port'            => isset( $_POST['ftp-port'] ) ? intval( $_POST['ftp-port'] ) : null, // phpcs:ignore
			'ftp_mode'            => isset( $_POST['ftp-passive-mode'] ) ? boolval( $_POST['ftp-passive-mode'] ) : false, // phpcs:ignore
			'ftp_timeout'         => isset( $_POST['ftp-timeout'] ) ? intval( $_POST['ftp-timeout'] ) : null, // phpcs:ignore
		);

		$task = new Task\Request\Destination\Ftp( $data['tpd_action'] );

		$validated_data = $task->validate_request_data( $data );

		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		$args                  = $validated_data;
		$args['request_model'] = new Model\Request\Destination\Ftp();

		$result = $task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}

			$error_text = 'Invalid directory name' === $result['Error'] ? __( 'directory path', 'snapshot' ) : __( 'username and password', 'snapshot' );
			wp_send_json_error(
				array(
					'api_response'                 => $result,
					'connection_failed_group_text' => $error_text,
				),
				422
			);
		}

		wp_send_json_success(
			array(
				'api_response' => $result,
			)
		);
	}
}