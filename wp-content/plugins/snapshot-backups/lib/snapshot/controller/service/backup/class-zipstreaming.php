<?php // phpcs:ignore
/**
 * Snapshot controllers: Backup zipstreaming service actions
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Service\Backup;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Helper\System;

/**
 * Backup zipstreaming service actions handling controller class
 */
class Zipstreaming extends Controller\Service {

	/**
	 * Gets the list of known service actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		$known = array(
			self::FILES_ZIPSTREAM,
			self::LARGE_FILES_ZIPSTREAM,
			self::TABLES_ZIPSTREAM,
		);
		return $known;
	}

	/**
	 * Returns a zipstream of the requested files.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_files_zipstream( $params, $action, $request = false ) {
		$cancelled = $this->check_cancelled_backup();

		if ( false !== $cancelled ) {
			return $this->send_response_success( $cancelled, $request );
		}

		// Disable SG Optimizer's HTML minification for Snapshot service requests.
		add_filter( 'sgo_html_minify_exclude_params', array( $this, 'sgo_html_minify_exclude_params' ) );

		Log::info( __( 'The API has requested the backing up of files.', 'snapshot' ) );
		$task = new Task\Backup\Zipstream\Files();

		$data             = (array) $params;
		$validated_params = $task->validate_request_data( $data );

		if ( is_wp_error( $validated_params ) ) {
			return $this->send_response_error( $validated_params, $request );
		}

		$model = new Model\Backup\Zipstream\Files( $validated_params['ex_rt'], microtime( true ) );
		$model->set( 'requested_files', $validated_params['files'] );
		$model->set( 'is_encoded', wp_validate_boolean( $data['encoded'] ) );

		$args          = array();
		$args['model'] = $model;

		$task->apply( $args );

		$files_zipstreamed = $model->get( 'files_added', array() );

		if ( count( $files_zipstreamed ) === count( $validated_params['files'] ) ) {
			Log::info( __( 'The plugin has completed the backup of all requested files.', 'snapshot' ) );
		} else {
			/* translators: %1s - number of files completed, %2s - number of files requested */
			Log::info( sprintf( __( 'The plugin has completed the backup of %1$s out of the %2$s requested files.', 'snapshot' ), count( $files_zipstreamed ), count( $validated_params['files'] ) ) );
		}

		// Free up the memory.
		$model->unset( 'files_added' );

		// We exit instead of wp_die(), to avoid data at the end of the zip file.
		exit;
	}

	/**
	 * Returns a zipstream of a chunck of a large file. Files over a limit(defined system-side) will be separately zipstreamed with this method.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_large_files_zipstream( $params, $action, $request = false ) {
		$cancelled = $this->check_cancelled_backup();

		if ( false !== $cancelled ) {
			return $this->send_response_success( $cancelled, $request );
		}

		// Disable SG Optimizer's HTML minification for Snapshot service requests.
		add_filter( 'sgo_html_minify_exclude_params', array( $this, 'sgo_html_minify_exclude_params' ) );

		$task = new Task\Backup\Zipstream\LargeFiles();

		$data = (array) $params;

		$validated_params = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_params ) ) {
			return $this->send_response_error( $validated_params, $request );
		}

		$model      = new Model\Backup\Zipstream\LargeFiles();
		$is_encoded = wp_validate_boolean( $data['encoded'] );

		$file = $validated_params['file'];
		if ( $is_encoded ) {
			$file = $task->url_safe_base64_decode( $validated_params['file'] );
		}

		/* translators: %s - large file relative path */
		Log::info( sprintf( __( 'The API has requested the separate backing up of a large file: %1$s [%2$s-%3$s] ', 'snapshot' ), $file, $validated_params['offset'], $validated_params['offset'] + $validated_params['length'] ) );

		$model->set( 'is_encoded', $is_encoded );
		$model->set( 'file', $validated_params['file'] );
		$model->set( 'offset', $validated_params['offset'] );
		$model->set( 'length', $validated_params['length'] );

		$args          = array();
		$args['model'] = $model;

		$task->apply( $args );

		// We exit instead of wp_die(), to avoid data at the end of the zip file.
		exit;
	}

	/**
	 * Returns a zipstream of the requested tables.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_tables_zipstream( $params, $action, $request = false ) {
		$cancelled = $this->check_cancelled_backup();

		if ( false !== $cancelled ) {
			return $this->send_response_success( $cancelled, $request );
		}

		// Disable SG Optimizer's HTML minification for Snapshot service requests.
		add_filter( 'sgo_html_minify_exclude_params', array( $this, 'sgo_html_minify_exclude_params' ) );

		Log::info( __( 'The API has requested the backing up of a DB table.', 'snapshot' ) );

		$task = new Task\Backup\Zipstream\Tables();

		$data             = (array) $params;
		$validated_params = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_params ) ) {
			return $this->send_response_error( $validated_params, $request );
		}

		/* translators: %1s - name of db table, %2s - index of starting row */
		Log::info( sprintf( __( 'The table to be backed up: %1$s, starting from row: %2$s.', 'snapshot' ), $validated_params['table'], $validated_params['starting_row'] ) );

		$model = new Model\Backup\Zipstream\Tables( $validated_params['db_chunk'] );
		$model->set( 'starting_row', $validated_params['starting_row'] );
		$model->set( 'requested_table', $validated_params['table'] );

		$dump_method   = Settings::get_db_build_method();
		$is_accessible = System::can_call_system();

		if ( 'mysqldump' === $dump_method && $is_accessible ) {
			$model->set( 'dump_method', 'mysqldump' );
		} else {
			$model->set( 'dump_method', 'php_code' );
		}

		$args          = array();
		$args['model'] = $model;

		$task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
				break;
			}
			$this->send_response_error( $error, $request );
		}
		if ( $model->get( 'done' ) ) {
			/* translators: %s - name of db table */
			Log::info( sprintf( __( 'The plugin has completed the backup of the %s table.', 'snapshot' ), $validated_params['table'] ) );
		} else {
			/* translators: %1s - name of db table, %2s - index of current row */
			Log::info( sprintf( __( 'The plugin has backed up the %1$s table until row #%2$s .', 'snapshot' ), $validated_params['table'], $model->get( 'current_row' ) ) );
		}

		// We exit instead of wp_die(), to avoid data at the end of the zip file.
		exit;
	}

	/**
	 * Disable SG Optimizer's HTML minification for Snapshot service requests.
	 *
	 * @param array $exclude_params Exclude requests with wpmudev-hub param.
	 * @return array
	 */
	public function sgo_html_minify_exclude_params( $exclude_params ) {
		$exclude_params[] = 'wpmudev-hub';
		return $exclude_params;
	}
}