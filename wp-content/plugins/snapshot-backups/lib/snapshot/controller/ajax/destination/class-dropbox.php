<?php // phpcs:ignore
/**
 * Snapshot controllers: Dropbox Destination AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax\Destination;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Google Drive Destination AJAX controller class
 */
class Dropbox extends Controller\Ajax\Destination {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		// Request the service actions regarding Google Drive destinations.
		add_action( 'wp_ajax_snapshot-dropbox_connection', array( $this, 'json_dropbox_connection' ) );
	}

	/**
	 * Handles requesting the service for testing a destination's config.
	 */
	public function json_dropbox_connection() {
		$this->do_request_sanity_check( 'snapshot_dropbox_connection', self::TYPE_POST );

		$data = array(
			'tpd_acctoken_gdrive' => isset( $_POST['tpd_acctoken_dropbox'] ) ? $_POST['tpd_acctoken_dropbox'] : null, // phpcs:ignore
			'tpd_retoken_gdrive'  => isset( $_POST['tpd_retoken_dropbox'] ) ? $_POST['tpd_retoken_dropbox'] : null, // phpcs:ignore
			'tpd_email_gdrive'    => isset( $_POST['tpd_email_dropbox'] ) ? $_POST['tpd_email_dropbox'] : null, // phpcs:ignore
			'tpd_auth_code'       => isset( $_POST['tpd_auth_code'] ) ? $_POST['tpd_auth_code'] : null, // phpcs:ignore
			'tpd_action'          => isset( $_POST['tpd_action'] ) ? $_POST['tpd_action'] : null, // phpcs:ignore
			'tpd_path'            => isset( $_POST['tpd_path'] ) ? $_POST['tpd_path'] : null, // phpcs:ignore
			'tpd_name'            => isset( $_POST['tpd_name'] ) ? $_POST['tpd_name'] : null, // phpcs:ignore
			'tpd_type'            => isset( $_POST['tpd_type'] ) ? $_POST['tpd_type'] : null, // phpcs:ignore
			'tpd_limit'           => isset( $_POST['tpd_limit'] ) ? intval( $_POST['tpd_limit'] ) : null, // phpcs:ignore
			'tpd_save'            => isset( $_POST['tpd_save'] ) ? intval( $_POST['tpd_save'] ) : null, // phpcs:ignore
		);

		$task = new Task\Request\Destination\Dropbox( $data['tpd_action'] );

		$validated_data = $task->validate_request_data( $data );

		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		$args                  = $validated_data;
		$args['request_model'] = new Model\Request\Destination\Dropbox();
		$result                = $task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}
			wp_send_json_error(
				array(
					'api_response' => $result,
				)
			);
		}

		wp_send_json_success(
			array(
				'api_response' => $result,
			)
		);
	}
}