<?php // phpcs:ignore
/**
 * Snapshot controllers: Backup fetching service actions
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Service\Backup;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Backup fetching service actions handling controller class
 */
class Fetching extends Controller\Service {

	/**
	 * Gets the list of known service actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		$known = array(
			self::FETCH_FILELIST,
			self::FETCH_DBLIST,
		);
		return $known;
	}

	/**
	 * Handles fetching back the filelist that contains all relevant info for all files.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_fetch_filelist( $params, $action, $request = false ) {
		$cancelled = $this->check_cancelled_backup();

		if ( false !== $cancelled ) {
			return $this->send_response_success( $cancelled, $request );
		}

		Log::info( __( 'The API has requested the filelist for the currently running backup.', 'snapshot' ) );

		$task = new Task\Backup\Filelist();

		$data             = (array) $params;
		$validated_params = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_params ) ) {
			return $this->send_response_error( $validated_params, $request );
		}

		$model = new Model\Backup\Filelist( $data['ex_rt'], microtime( true ) );
		$model->set( 'paths_left', $data['paths_left'] );
		$model->set( 'exclusion_enabled', $data['exclusion_enabled'] );
		$model->set( 'files', array_reverse( $model->get( 'files', array() ) ) );

		$args          = array();
		$args['model'] = $model;

		$task->apply( $args );

		if ( $model->get( 'is_done' ) ) {
			Log::info( __( 'Snapshot has completed sending back the filelist to the API, so we\'re ready to begin the actual backup of the files.', 'snapshot' ) );
		} else {
			/* translators: %s - number of files */
			Log::info( sprintf( __( 'Snapshot has sent back the filelist consisting of %s files but the file iteration hasn\'t been completed yet. Awaiting for the next filelist request from the API.', 'snapshot' ), count( $model->get( 'files', array() ) ) ) );
		}

		// Response to "service".
		$response = (object) array(
			'done'           => $model->get( 'is_done' ),
			'site_root'      => $model->get( 'root_path' ),
			'paths_left'     => $model->get( 'paths_left' ),
			'files'          => $model->get( 'files', array() ),
			'excluded_files' => $model->get( 'excluded_files' ),
		);
		return $this->send_response_success( $response, $request );
	}

	/**
	 * Handles fetching back the db tables along with checksums to check which ones we should include in the next backup.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_fetch_dblist( $params, $action, $request = false ) {
		$cancelled = $this->check_cancelled_backup();

		if ( false !== $cancelled ) {
			return $this->send_response_success( $cancelled, $request );
		}

		Log::info( __( 'The API has requested the DB tablelist for the currently running backup.', 'snapshot' ) );

		$task = new Task\Backup\Dblist();

		$data = (array) $params;

		$model            = new Model\Backup\Dblist( $data['ex_rt'], microtime( true ) );
		$remaining_tables = isset( $data['tables_left'] ) ? $data['tables_left'] : array();

		$model->set( 'tables_left', $remaining_tables );

		$validated_params = $task->validate_request_data( $data );

		if ( is_wp_error( $validated_params ) ) {
			return $this->send_response_error( $validated_params, $request );
		}

		$args          = array();
		$args['model'] = $model;

		$task->apply( $args );

		if ( $task->has_errors() ) {
			$errors = $task->get_errors();
			Log::error( $errors[0]->get_error_message() );
			$response = $this->send_response_error( $errors[0], $request );
			return $response;
		}

		if ( $model->get( 'is_done' ) ) {
			Log::info( __( 'Snapshot has completed sending back the DB tablelist to the API, so we\'re ready to begin the actual backup of the tables.', 'snapshot' ) );
		} else {
			/* translators: %s - number of tables */
			Log::info( sprintf( __( 'Snapshot has sent back the DB tablelist consisting of %s tables but the table iteration hasn\'t been completed yet. Awaiting for the next DB tablelist request from the API.', 'snapshot' ), count( $model->get( 'tables' ) ) ) );
		}

		// Response to "service".
		$response = (object) array(
			'done'            => $model->get( 'is_done' ),
			'db_name'         => $model->get( 'db_name' ),
			'tables_left'     => $model->get( 'tables_left' ),
			'tables'          => $model->get( 'tables', array() ),
			'exclusions'      => $model->get( 'tables_excluded', false ),
			'excluded_tables' => $model->get( 'excluded_tables', array() ),
		);

		return $this->send_response_success( $response, $request );
	}
}