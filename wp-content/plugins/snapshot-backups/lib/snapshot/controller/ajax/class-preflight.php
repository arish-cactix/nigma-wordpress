<?php // phpcs:ignore
/**
 * Snapshot controllers: Preflight AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Preflight AJAX controller class
 */
class Preflight extends Controller\Ajax {

	/**
	 * Boots the controller and sets up preflight ajaxs.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		add_action( 'wp_ajax_snapshot-preflight_check', array( $this, 'json_preflight_check' ) );
		add_action( 'wp_ajax_snapshot-preflight_save_exclusions', array( $this, 'json_preflight_save_exclusions' ) );
	}

	/**
	 * Handles preflight process.
	 */
	public function json_preflight_check() {
		$this->do_request_sanity_check( 'snapshot_preflight_check' );

		$data                   = array();
		$data['checking_stage'] = isset( $_POST['checking_stage'] ) ? sanitize_text_field( wp_unslash( $_POST['checking_stage'] ) ) : '';
		$scanned_data = ( isset( $_POST['scanned_data'] ) && 'null' !== $_POST['scanned_data'] ) ? json_decode( stripslashes( $_POST['scanned_data'] ), true ) : array(); //phpcs:ignore
		$paths_left   = ( isset( $_POST['paths_left'] ) && 'null' !== $_POST['paths_left'] ) ? json_decode( stripslashes( $_POST['paths_left'] ), true ) : array(); //phpcs:ignore

		$preflight_model = new Model\Preflight( 5, microtime( true ) );

		$preflight_model->set( 'paths_left', $paths_left );
		$preflight_model->set( $data['checking_stage'] . '_check', $scanned_data );

		$args          = array();
		$args['model'] = $preflight_model;

		switch ( $data['checking_stage'] ) {
			case 'system':
				$task = new Task\Check\System();
				$task->apply( $args );
				Log::info( __( 'System check applied.', 'snapshot' ) );
				break;
			case 'files':
				$task = new Task\Check\Files();
				$task->apply( $args );
				Log::info( __( 'Files check applied.', 'snapshot' ) );
				break;
			case 'database':
				$task = new Task\Check\Tables();
				$task->apply( $args );
				Log::info( __( 'Database check applied.', 'snapshot' ) );
				break;

			default:
				wp_send_json_error(
					array(
						'message' => __( 'Invalid checking stage.', 'snapshot' ),
					)
				);
		}//end switch

		if ( empty( $preflight_model->get( 'paths_left' ) ) ) {
			$template = new Helper\Template();

			ob_start();
			$result_data = $preflight_model->get( $data['checking_stage'] . '_check' );
			$template->render( 'modals/check/modal_parts/' . $data['checking_stage'] . '-result', $result_data );
			$html = ob_get_clean();

			$response = array(
				'html' => $html,
				'done' => true,
			);

		} else {
			$response = array(
				'paths_left'   => $preflight_model->get( 'paths_left' ),
				'done'         => false,
				'scanned_data' => $preflight_model->get( $data['checking_stage'] . '_check' ),
			);
		}
		$response['checking_stage'] = $data['checking_stage'];

		wp_send_json_success(
			$response
		);
	}

	/**
	 * Save Preflight exclusions.
	 */
	public function json_preflight_save_exclusions() {
		$this->do_request_sanity_check( 'snapshot_preflight_check' );

		$data                   = array();
		$data['checking_stage'] = isset( $_POST['check'] ) ? sanitize_text_field( wp_unslash( $_POST['check'] ) ) : '';
		$data['action_type']    = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';
		$data['items'] = json_decode( stripslashes( $_POST['items'] ) ); //phpcs:ignore
		$data['items'] = is_array( $data['items'] ) ? array_map( 'sanitize_text_field', $data['items'] ) : array(); //phpcs:ignore

		if ( ! empty( $data['items'] ) ) {
			$option_name = ( 'files' === $data['checking_stage'] ) ? 'snapshot_global_exclusions' : 'snapshot_excluded_tables';
			if ( 'exclude' === $data['action_type'] ) {
					$old_exclusions = get_site_option( $option_name, array() );
					$new_exclusions = array_unique( array_merge( $old_exclusions, $data['items'] ) );
					update_site_option( $option_name, $new_exclusions );
			} else {
					$old_exclusions = get_site_option( $option_name, array() );
				foreach ( $data['items'] as $key => $item ) {
					$index = array_search( $item, $old_exclusions, true );
					if ( false !== $index ) {
						unset( $old_exclusions[ $index ] );
					}
				}
					update_site_option( $option_name, $old_exclusions );
			}
		}

		$response['checking_stage'] = $data['checking_stage'];

		wp_send_json_success(
			$response
		);
	}
}