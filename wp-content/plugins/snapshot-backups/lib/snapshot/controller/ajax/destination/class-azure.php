<?php // phpcs:ignore
/**
 * Snapshot controllers: Azure Destination AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax\Destination;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Azure Destination AJAX controller class
 */
class Azure extends Controller\Ajax\Destination {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		// Request the service actions regarding azure destinations.
		add_action( 'wp_ajax_snapshot-azure_connection', array( $this, 'json_azure_connection' ) );
	}

	/**
	 * Handles requesting the service for testing a destination's config.
	 */
	public function json_azure_connection() {

		$this->do_request_sanity_check( 'snapshot_azure_connection', self::TYPE_POST );

		$data             = array(
			'tpd_accesskey'  => isset( $_POST['tpd_accesskey'] ) ? $_POST['tpd_accesskey'] : null, // phpcs:ignore
			'tpd_secretkey'  => isset( $_POST['tpd_secretkey'] ) ? $_POST['tpd_secretkey'] : null, // phpcs:ignore
			'tpd_bucketname' => isset( $_POST['tpd_container'] ) ? $_POST['tpd_container'] : ( isset( $_POST['tpd_new_container'] ) ? $_POST['tpd_new_container'] : null ), // phpcs:ignore
			'tpd_action'     => isset( $_POST['tpd_action'] ) ? $_POST['tpd_action'] : null, // phpcs:ignore
			'tpd_path'       => isset( $_POST['tpd_path'] ) ? $_POST['tpd_path'] : (isset( $_POST['tpd_new_path'] ) ? $_POST['tpd_new_path'] : null), // phpcs:ignore
			'tpd_limit'      => isset( $_POST['tpd_limit'] ) ? intval( $_POST['tpd_limit'] ) : (isset( $_POST['tpd_new_limit'] ) ? intval( $_POST['tpd_new_limit'] ) : null), // phpcs:ignore
			'tpd_name'       => isset( $_POST['tpd_name'] ) ? $_POST['tpd_name'] : null, // phpcs:ignore
			'tpd_save'       => isset( $_POST['tpd_save'] ) ? intval( $_POST['tpd_save'] ) : null, // phpcs:ignore
		);
		$data['tpd_type'] = 'azure';
		$data['tpd_path'] = empty( $data['tpd_path'] ) ? $data['tpd_bucketname'] : $data['tpd_bucketname'] . '/' . trim( $data['tpd_path'], '/\\' );

		$task = new Task\Request\Destination\Azure( $data['tpd_action'] );

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		$args                  = $validated_data;
		$args['request_model'] = new Model\Request\Destination\Azure();
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