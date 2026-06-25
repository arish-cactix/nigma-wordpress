<?php // phpcs:ignore
/**
 * Snapshot controllers: Service action receiver abstraction
 *
 * Handles actions received remotely, from the Service.
 * All Service controllers extend from this.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Service actions controller class
 */
abstract class Service extends Controller {

	const START_BACKUP                  = 'start_backup';
	const FINISH_BACKUP                 = 'finish_backup';
	const CANCELLED_BACKUP              = 'cancelled_backup';
	const FETCH_FILELIST                = 'fetch_filelist';
	const FETCH_DBLIST                  = 'fetch_dblist';
	const FILES_ZIPSTREAM               = 'files_zipstream';
	const LARGE_FILES_ZIPSTREAM         = 'large_files_zipstream';
	const TABLES_ZIPSTREAM              = 'tables_zipstream';
	const EXPORT_BACKUP_EMAIL           = 'export_backup_email';
	const EXPORT_LOGGING                = 'export_logging';
	const HUB_INFO                      = 'hub_info';
	const HUB_END_START_BACKUP          = 'hub_end_start_backup';
	const HUB_END_DELETE_BACKUPS        = 'hub_end_delete_backups';
	const HUB_END_DELETE_CACHE          = 'hub_end_delete_cache';
	const HUB_END_DELETE_SETTINGS_CACHE = 'hub_end_delete_settings_cache';
	const HUB_END_IMPORT_CONFIGS        = 'import_settings';
	const HUB_END_EXPORT_CONFIGS        = 'export_settings';

	/**
	 * Gets the list of known Service actions
	 *
	 * @return array Known actions
	 */
	abstract public function get_known_actions();

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		add_filter( 'wdp_register_hub_action', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Registers handlers for actions pushed from the Service
	 *
	 * @param array $actions Known actions.
	 *
	 * @return array Augmented actions
	 */
	public function register_endpoints( $actions ) {
		if ( ! is_array( $actions ) ) {
			return $actions;
		}

		$known = $this->get_known_actions();
		if ( ! is_array( $known ) ) {
			return $actions;
		}

		foreach ( $known as $action_raw_name ) {
			$method = "json_{$action_raw_name}";
			if ( ! is_callable( array( $this, $method ) ) ) {
				continue;
				// We don't know how to handle this action.
			}

			$action_name             = "snapshot4_{$action_raw_name}";
			$actions[ $action_name ] = array( $this, $method );
		}

		return $actions;
	}

	/**
	 * Wraps error sending response
	 *
	 * If we have enough info to build appropriate response
	 * and we have access to request object, use that to send back more
	 * meaningful, verbose response.
	 * Otherwise default to WP JSON response handling.
	 *
	 * @param \WP_Error|mixed $info Info on what went wrong.
	 * @param object          $request Optional \WPMUDEV_Dashboard_Remote object.
	 *
	 * @return bool
	 */
	public function send_response_error( $info, $request = false ) {
		$status = $info;
		if ( is_wp_error( $info ) ) {
			$code   = $info->get_error_code();
			$status = array(
				'code'    => $code,
				'message' => $info->get_error_message( $code ),
				'data'    => $info->get_error_data( $code ),
			);
		}
		if (
			! empty( $status ) &&
			is_object( $request ) &&
			is_callable( array( $request, 'send_json_error' ) )
		) {
			return $request->send_json_error( $status );
		}
		return wp_send_json_error( $status );
	}

	/**
	 * Wraps success sending response
	 *
	 * If we have enough info to build appropriate response
	 * and we have access to request object, use that to send back more
	 * meaningful, verbose response.
	 * Otherwise default to WP JSON response handling.
	 *
	 * @param mixed  $info Info status.
	 * @param object $request Optional \WPMUDEV_Dashboard_Remote object.
	 *
	 * @return bool
	 */
	public function send_response_success( $info, $request = false ) {
		$status = $info;
		if (
			! empty( $status ) &&
			is_object( $request ) &&
			is_callable( array( $request, 'send_json_success' ) )
		) {
			return $request->send_json_success( $status );
		}
		return wp_send_json_success( $status );
	}

	/**
	 * Checks if there has been a request to cancel the running backup. If so, it will return a flag to the service.
	 */
	public function check_cancelled_backup() {
		$cancelled_backup = get_site_option( Controller\Ajax\Backup::SNAPSHOT_CANCELLED_BACKUP );

		if ( ! empty( $cancelled_backup ) ) {
			Log::clear( $cancelled_backup );

			delete_site_option( Controller\Ajax\Backup::SNAPSHOT_CANCELLED_BACKUP );
			$response = (object) array(
				'cancel_running_snap' => true,
			);
			return $response;
		}

		return false;
	}
}