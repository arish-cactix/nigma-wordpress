<?php // phpcs:ignore
/**
 * Snapshot controllers: Schedule endpoints for Hub
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller;

use WPMUDEV\Snapshot4\Configs\Config;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Main;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model\Log as ModelLog;

/**
 * Schedule endpoints for Hub handling controller class
 */
class Hub extends Controller\Service {

	/**
	 * Gets the list of known service actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		return array(
			self::HUB_END_START_BACKUP,
			self::HUB_END_DELETE_BACKUPS,
			self::HUB_END_DELETE_CACHE,
			self::HUB_END_DELETE_SETTINGS_CACHE,
			self::HUB_END_IMPORT_CONFIGS,
			self::HUB_END_EXPORT_CONFIGS,
		);
	}

	/**
	 * Triggers a new manual backup.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_hub_end_start_backup( $params, $action, $request = false ) {
		Log::info( __( 'The Hub requested to trigger a new backup.', 'snapshot' ) );
		$data = (array) $params;

		$task = new Task\Request\Manual();

		$validated_params = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_params ) ) {
			return $this->send_response_error( $validated_params, $request );
		}

		$log = new ModelLog();
		$log->create(
			array(
				'action'  => 'hub_manual_backup_triggered',
				'details' => 'The hub requested to trigger a new backup.',
			)
		);

		$model = new Model\Request\Manual();

		$args          = $validated_params;
		$args['model'] = $model;
		$result        = $task->apply( $args );

		if ( $task->has_errors() ) {
			$errors = array();
			foreach ( $task->get_errors() as $error ) {
				$errors[] = $error;
				Log::error( $error->get_error_message() );
				return $this->send_response_error( $error, $request );
			}
		}

		$response = (object) array(
			'backup_running' => $result,
		);

		Log::info( __( 'Communication with the service API, in order to create manual backup, was successful.', 'snapshot' ) );

		return $this->send_response_success( $response, $request );
	}

	/**
	 * Deletes all backups for the site.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_hub_end_delete_backups( $params, $action, $request = false ) {
		$task = new Task\Request\Delete();

		$args                  = array();
		$args['request_model'] = new Model\Request\Delete();
		$task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				return $this->send_response_error( $error, $request );
			}
		}

		$log = new ModelLog();
		$log->create(
			array(
				'action'  => 'hub_deleted_backups',
				'details' => 'The hub requested to delete all the backups.',
			)
		);

		$response = (object) array(
			'backups_deleted' => true,
		);

		return $this->send_response_success( $response, $request );
	}

	/**
	 * Deletes transient of the backup listing.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_hub_end_delete_cache( $params, $action, $request = false ) {
		delete_transient( 'snapshot_listed_backups' );
		delete_transient( 'snapshot_current_stats' );

		$response = (object) array(
			'cache_deleted' => true,
		);

		return $this->send_response_success( $response, $request );
	}

	/**
	 * Deletes transient of the "extra security step" option.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_hub_end_delete_settings_cache( $params, $action, $request = false ) {
		delete_transient( 'snapshot_extra_security_step' );

		$response = (object) array(
			'cache_deleted' => true,
		);

		return $this->send_response_success( $response, $request );
	}

	/**
	 * Import Configs for the HUB
	 *
	 * @param object $params Params.
	 */
	public function json_import_settings( $params ) {
		if ( empty( $params->configs ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing config data', 'snapshot' ),
				)
			);
		}

		$preset = json_decode( wp_json_encode( $params->configs ), true );
		$config = Config::get_instance();

		$log = new ModelLog();
		$log->create(
			array(
				'action'  => 'hub_applied_config',
				'details' => 'The hub requested to apply the configs',
			)
		);

		try {
			$config->apply( $preset, 'hub' );
			Settings::set_started_seen( true );
		} catch ( \Exception $e ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Export of configs for the HUB.
	 *
	 * @return void
	 */
	public function json_export_settings() {
		$config = Config::get_instance();
		$preset = $config->export();

		wp_send_json_success( $preset['config'] );
	}
}