<?php // phpcs:ignore
/**
 * Snapshot controllers: Backup AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Ajax;

use Exception;
use stdClass;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Exceptions\Dashboard_Api_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Login_Method_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Password_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Username_Exception;
use WPMUDEV\Snapshot4\Exceptions\WP_Error_Exception;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Helper\Api;
use WPMUDEV\Snapshot4\Model\Env;
use WPMUDEV\Snapshot4\Model\Log as ModelLog;

/**
 * Backup AJAX controller class
 */
class Backup extends Controller\Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		add_action( 'wp_ajax_snapshot-check_ips', array( $this, 'json_check_ips' ) );
		add_action( 'wp_ajax_snapshot-trigger_backup', array( $this, 'json_trigger_backup' ) );
		add_action( 'wp_ajax_snapshot-update_backup', array( $this, 'json_update_backup' ) );
		add_action( 'wp_ajax_snapshot-cancel_backup', array( $this, 'json_cancel_backup' ) );
		add_action( 'wp_ajax_snapshot-update_backup_progress', array( $this, 'json_update_backup_progress' ) );
		add_action( 'wp_ajax_snapshot-delete_backup', array( $this, 'json_delete_backup' ) );
		add_action( 'wp_ajax_snapshot-get_backup_log', array( $this, 'json_paginate_log' ) );
		add_action( 'wp_ajax_snapshot-paginate_log', array( $this, 'json_paginate_log' ) );
		add_action( 'wp_ajax_snapshot-get_log_list', array( $this, 'json_get_log_list' ) );
		add_action( 'wp_ajax_snapshot-download_log', array( $this, 'json_download_log' ) );
		add_action( 'wp_ajax_snapshot-check_wpmudev_password', array( $this, 'json_check_wpmudev_password' ) );
		add_action( 'wp_ajax_snapshot-check_can_delete_backup', array( $this, 'json_check_can_delete_backup' ) );
	}

	/**
	 * Checks Snapshot Server IP
	 *
	 * @return void
	 */
	public function json_check_ips() {
		$this->do_request_sanity_check( 'snapshot_backup_create_manual', self::TYPE_GET );

		if ( defined( 'SNAPSHOT_BYPASS_IP_ALLOWLIST_CHECK' ) && SNAPSHOT_BYPASS_IP_ALLOWLIST_CHECK ) {
			wp_send_json_success( array( 'status' => 'allowlisted' ) );
		}

		$task = new Task\Request\Check();

		$args          = array();
		$args['model'] = new Model\Request\Check();

		/**
		 * Apply the task.
		 */
		$result = $task->apply( $args );

		if ( $task->has_errors() ) {
			$errors = array();

			foreach ( $task->get_errors() as $err ) {
				$errors[] = $err;
				Log::error( $err->get_error_message() );
			}

			wp_send_json_error( array( 'errors' => $errors ) );
		}

		if ( ! is_wp_error( $result ) ) {
			$response_code = wp_remote_retrieve_response_code( $result );
			$response_json = wp_remote_retrieve_body( $result );
			$response      = json_decode( $response_json, true );

			if ( 200 === $response_code ) {
				Log::info( __( 'Snapshot IPs are allowlisted. Ready to kick off the backup process!', 'snapshot' ) );
				wp_send_json_success( array( 'response' => $response ) );
			}

			wp_send_json_error( array( 'response' => $response ), 400 );
		}
	}

	/**
	 * Trigger a manual backup.
	 */
	public function json_trigger_backup() {
		$this->do_request_sanity_check( 'snapshot_backup_create_manual', self::TYPE_POST );

		$is_http_auth_required = get_site_transient( 'snapshot_http_authentication_required' );

		if ( $is_http_auth_required && 'yes' === $is_http_auth_required && ! get_site_option( 'snapshot_http_authentication_added' ) ) {
			wp_send_json_error( array( 'status' => 'http_auth_required' ) );
		}

		$data = array();

		$data['backup_name']      = isset( $_POST['data']['backup_name'] ) ? trim( $_POST['data']['backup_name'] ) : null; // phpcs:ignore
		$data['apply_exclusions'] = isset( $_POST['data']['apply_exclusions'] ) ? ( 'true' === $_POST['data']['apply_exclusions'] ) : null; // phpcs:ignore
		$data['description']      = isset ( $_POST['data']['description'] ) ? trim( $_POST['data']['description'] ) : null;	// phpcs:ignore

		$task = new Task\Request\Manual();

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		$model = new Model\Request\Manual();

		$validated_data['backup_name'] = stripslashes( $validated_data['backup_name'] );
		$args                          = $validated_data;
		$args['model']                 = $model;
		$result                        = $task->apply( $args );

		if ( $task->has_errors() ) {
			$errors = array();
			foreach ( $task->get_errors() as $error ) {
				$errors[] = $error;
				Log::error( $error->get_error_message() );
			}

			wp_send_json_error(
				array(
					'errors'   => $errors,
					'messages' => $model->get( 'messages' ),
				)
			);
		}

		Log::info( __( 'Communication with the service API, in order to create manual backup, was successful.', 'snapshot' ) );

		wp_send_json_success(
			array(
				'backup_running' => $result,
			)
		);
	}

	/**
	 * Updates the backup.
	 *
	 * Updates the backup description/comment. And returns wp_send_json_success upon success
	 * and wp_send_json_error upon failure to update.
	 *
	 * @since 4.3.5
	 */
	public function json_update_backup() {
		$this->do_request_sanity_check( 'snapshot_update_backup_comment', self::TYPE_POST );

		// Check for backup ID.
		if ( ! isset( $_POST['backup_id'] ) || empty( $_POST['backup_id'] ) ) { //phpcs:ignore
			wp_send_json_error( array( 'status' => 'no_backup_id' ) );
		}

		$data = array();

		$data['description'] = sanitize_textarea_field( $_POST['description'] );//phpcs:ignore
		$data['backup_id']   = sanitize_text_field( $_POST['backup_id'] );//phpcs:ignore

		$task = new Task\Backup\Update();

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		$args          = $validated_data;
		$args['model'] = new Model\Backup\Update();

		$result = $task->apply( $args );

		if ( $task->has_errors() ) {
			$errors = array();

			foreach ( $task->get_errors() as $error ) {
				$errors[] = $error;
				Log::error( $error->get_error_message() );
			}

			wp_send_json_error(
				array(
					'errors' => $errors,
				)
			);
		}

		Log::info( __( 'Communication with the service API, in order to edit backup comment, was successful.', 'snapshot' ) );

		// @todo Append table row html along with the updated content.
		$nonce = wp_create_nonce( 'snapshot_list_backups' );
		wp_send_json_success(
			array(
				'backup_id' => $data['backup_id'],
				'nonce'     => $nonce,
				'result'    => $result,
			)
		);
	}

	/**
	 * Cancel a running backup.
	 */
	public function json_cancel_backup() {
		$this->do_request_sanity_check( 'snapshot_cancel_backup', self::TYPE_POST );

		$data = array();

		$data['backup_id'] = isset( $_POST['backup_id'] ) ? $_POST['backup_id'] : null; // phpcs:ignore

		$task = new Task\Backup\Cancel();

		$validated_data = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_data ) ) {
			wp_send_json_error( $validated_data );
		}

		$args = $validated_data;
		$task->apply( $args );

		Log::info( __( 'There has been a request to cancel the running backup.', 'snapshot' ) );

		wp_send_json_success();
	}

	/**
	 * Updates the running backup progress continuously.
	 */
	public function json_update_backup_progress() {
		$this->do_request_sanity_check( 'snapshot_backup_progress', self::TYPE_GET );

		// Use the DOM's running backup info if it exists, or the Db's info if it doesn't.
		$backup_running        = ( ! isset( $_GET['already_running_backup'] ) || ( ! is_array( $_GET['already_running_backup'] ) && ! boolval( $_GET['already_running_backup'] ) ) ) ? get_site_option( self::SNAPSHOT_RUNNING_BACKUP ) : array_filter( $_GET['already_running_backup'], self::class . '::sanitize_running_backup' ); // phpcs:ignore
		$backup_running_status = ( ! isset( $_GET['already_running_backup_status'] ) || ! boolval( $_GET['already_running_backup_status'] ) ) ? get_site_option( self::SNAPSHOT_RUNNING_BACKUP_STATUS ) : sanitize_text_field( wp_unslash( $_GET['already_running_backup_status'] ) ); // phpcs:ignore
		$needs_api_call        = isset( $_GET['do_api_call'] ) ? boolval( $_GET['do_api_call'] ) : false;  // phpcs:ignore
		$expand                = isset( $_GET['expand'] ) ? $_GET['expand'] : null;  // phpcs:ignore

		$cancelled = isset( $backup_running['id'] ) ? get_site_option( self::SNAPSHOT_CANCELLED_BACKUP_PERSISTENT . $backup_running['id'] ) : false;

		if ( ! empty( $cancelled ) ) {
			$result = array(
				'backup_cancelled' => boolval( $cancelled ),
			);

			wp_send_json_success( $result );
		}

		$task  = new Task\Backup\Progress();
		$model = new Model\Backup\Progress( $needs_api_call );

		$model->set( 'ignore_response_log', true );
		$model->set( 'backup_running', $backup_running );
		$model->set( 'backup_running_status', $backup_running_status );
		$model->set( 'backup_failed', false );

		$args['model'] = $model;
		$result        = $task->apply( $args );

		if ( $task->has_errors() ) {
			$errors = array();
			foreach ( $task->get_errors() as $error ) {
				$errors[] = $error;
				Log::error( $error->get_error_message() );
			}

			wp_send_json_error( $errors );
		}

		// Check again for cancelled, to avoid race conditions.
		$cancelled = isset( $backup_running['id'] ) ? get_site_option( self::SNAPSHOT_CANCELLED_BACKUP_PERSISTENT . $backup_running['id'] ) : false;

		if ( ! empty( $cancelled ) ) {
			$result = array(
				'backup_cancelled' => boolval( $cancelled ),
			);

			wp_send_json_success( $result );
		}

		$result = array(
			'backup_cancelled'      => $cancelled,
			'backup_running_row'    => $result,
			'backup_running'        => $model->get( 'backup_running' ),
			'backup_running_status' => $model->get( 'backup_running_status' ),
			'backup_failed'         => $model->get( 'backup_failed' ),
			'export_text'           => $model->get( 'export_text' ),
			'error_message_html'    => $model->get( 'error_message_html' ),
		);

		if ( is_array( $result['backup_running'] ) &&
			array_key_exists( 'name', $result['backup_running'] ) ) {
			$result['backup_running']['name'] = stripslashes( $result['backup_running']['name'] );
		}

		if ( 'log' === $expand ) {
			$log_offset    = isset( $_GET['log_offset'] ) ? intval( $_GET['log_offset'] ) : 0;  // phpcs:ignore
			$log           = Log::parse_log_file( $backup_running['id'], $log_offset );
			$result['log'] = $log;
		}

		wp_send_json_success( $result );
	}

	/**
	 * Deletes remote backups.
	 */
	public function json_delete_backup() {
		$this->do_request_sanity_check( 'snapshot_delete_backup', self::TYPE_POST );

		if ( ! Settings::can_delete_backup() ) {
			wp_send_json_error();
		}

		$task = new Task\Backup\Delete();
		$data = isset( $_POST['data'] ) ? $_POST['data'] : null; // phpcs:ignore

		$validated_params = $task->validate_request_data( $data );
		if ( is_wp_error( $validated_params ) ) {
			wp_send_json_error( $validated_params );
		}

		$model = new Model\Backup\Delete( $data['backup_id'] );

		$args          = array();
		$args['model'] = $model;

		$result = $task->apply( $args );

		if ( ! $result ) {
			wp_send_json_error();
		}

		$log = new ModelLog();
		$log->create( array( 'action' => 'snapshot_backup_deleted' ) );

		wp_send_json_success();
	}

	/**
	 * Get backup log
	 */
	public function json_get_backup_log() {
		$this->do_request_sanity_check( 'snapshot_get_backup_log', self::TYPE_GET );

		$backup_id  = isset( $_GET['backup_id'] ) ? sanitize_key( $_GET['backup_id'] ) : null; // phpcs:ignore

		$log = Log::parse_log_file_enhanced( $backup_id, 0, 1 );

		wp_send_json_success(
			array(
				'log' => $log,
			)
		);
	}

	/**
	 * AJAX Handler:: Paginate the log file.
	 *
	 * @since 4.4.0
	 */
	public function json_paginate_log() {
		$this->do_request_sanity_check( 'snapshot_get_backup_log', self::TYPE_GET );

		$backup_id = isset( $_REQUEST['backup_id'] ) ? sanitize_key( $_REQUEST['backup_id'] ) : null; // phpcs:ignore
		$page      = filter_input( INPUT_GET, 'offset', FILTER_VALIDATE_INT );

		if ( ! $page ) {
			$page = 1;
		}

		if ( null === $backup_id || '' === $backup_id ) {
			wp_send_json_error(
				array(
					'status'  => 'no_backup_id',
					'message' => esc_html__( 'Couldn\'t load the backup file.', 'snapshot' ),
				)
			);
		}

		$log = Log::parse_log_file_enhanced( $backup_id, 0, $page );
		if ( empty( $log ) ) {
			wp_send_json_error(
				array(
					'status'  => 'no_content',
					'message' => esc_html__( 'Cannot read the file.', 'snapshot' ),
				)
			);
		}

		wp_send_json_success( array( 'log' => $log ) );
	}

	/**
	 * Get log tab content
	 */
	public function json_get_log_list() {
		$this->do_request_sanity_check( 'snapshot_get_backup_log', self::TYPE_GET );

		$task          = new Task\Request\Listing();
		$request_model = new Model\Request\Listing();
		$args          = array(
			'request_model' => $request_model,
			'return_ids'    => true,
		);
		$logs          = $task->apply( $args );
		$exist_logs    = Log::get_backup_ids();
		$logs          = array_values(
			array_filter(
				$logs,
				function ( $item ) use ( $exist_logs ) {
					return in_array( $item['backup_id'], $exist_logs, true );
				}
			)
		);

		$template = new Helper\Template();
		ob_start();
		$append_log = true;
		foreach ( $logs as $log ) {
			$template->render(
				'pages/backups/log-row',
				array(
					'name'                     => Helper\Datetime::format( $log['created_at'] ),
					'log'                      => array(),
					'log_url'                  => Log::get_log_url( $log['backup_id'] ),
					'backup_id'                => $log['backup_id'],
					'append_log'               => intval( $append_log ),
					'backup_type'              => $log['type'],
					'tpd_exp_status'           => $log['tpd_exp_status'],
					'destination_icon_details' => Settings::get_icon_details(),
				)
			);
			$append_log = false;
		}
		$content = ob_get_clean();

		wp_send_json_success(
			array(
				'content'  => $content,
				'show_log' => boolval( count( $logs ) ),
			)
		);
	}

	/**
	 * Outputs log file
	 */
	public function json_download_log() {
		$this->do_request_sanity_check( Log::NONCE, self::TYPE_GET );

		$backup_id = isset( $_GET['backup_id'] ) ? sanitize_key( $_GET['backup_id'] ) : null; // phpcs:ignore
		Log::output_log( $backup_id );
	}

	/**
	 * Sanitizes the running backup info
	 *
	 * @param string $input Element to be sanitized.
	 */
	public static function sanitize_running_backup( $input ) {
		return sanitize_text_field( wp_unslash( $input ) );
	}

	/**
	 * Check user's WPMU DEV account password
	 */
	public function json_check_wpmudev_password() {
		$this->do_request_sanity_check( 'snapshot_check_wpmudev_password', self::TYPE_POST );

		$wpmudev_password  = isset( $_POST['wpmudev_password'] ) ? $_POST['wpmudev_password'] : null; // phpcs:ignore

		$password_is_valid = false;
		try {
			$password_is_valid = Api::check_password( wp_unslash( $wpmudev_password ) );
		} catch ( Invalid_Username_Exception $e ) {
			/**
				* It will be easier to understand the point of failure in the future if anything changes in the API.
				* So, all the exceptions are handled separately.
				*/
			wp_send_json_error(
				array(
					'code'              => 'invalid_username',
					'message'           => $e->getMessage(),
					'password_is_valid' => false,
				)
			);
		} catch ( Dashboard_Api_Exception $e ) {
			wp_send_json_error(
				array(
					'code'              => 'inaccessible_dashboard_api',
					'message'           => $e->getMessage(),
					'password_is_valid' => false,
				)
			);
		} catch ( WP_Error_Exception $e ) {
			wp_send_json_error(
				array(
					'code'              => 'wp_error',
					'message'           => $e->getMessage(),
					'password_is_valid' => false,
				)
			);
		} catch ( Invalid_Login_Method_Exception $e ) {
			wp_send_json_error(
				array(
					'code'              => 'invalid_login_method',
					'message'           => $e->getMessage(),
					'password_is_valid' => false,
				)
			);
		} catch ( Invalid_Password_Exception $e ) {
			wp_send_json_error(
				array(
					'code'              => 'invalid_password',
					'message'           => $e->getMessage(),
					'password_is_valid' => false,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'code'              => $e->getCode(),
					'message'           => $e->getMessage(),
					'password_is_valid' => false,
				)
			);
		}//end try

		if ( $password_is_valid ) {
			Settings::allow_delete_backup();
		}

		wp_send_json_success( array( 'password_is_valid' => $password_is_valid ) );
	}

	/**
	 * Check user's ability to delete backup
	 */
	public function json_check_can_delete_backup() {
		$this->do_request_sanity_check( 'snapshot_check_can_delete_backup', self::TYPE_POST );

		$extra_step = get_transient( 'snapshot_extra_security_step' );

		if ( false === $extra_step ) {
			$task   = new Task\Check\Hub();
			$result = $task->apply( array( 'api_key' => Env::get_wpmu_api_key() ) );

			if ( false === $result ) {
				$result = new \WP_Error( 'dashboard_error', 'This site does not appear to be registered to this user in the hub. Please check hub registration and try again.', array( 'status' => 403 ) );
				wp_send_json_error( $result );
			}

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( $result );
			}

			$result = json_decode( $result, true );

			$extra_step = isset( $result['snapshot_extra_security_step'] )
				? boolval( $result['snapshot_extra_security_step'] )
				: true;

			set_transient( 'snapshot_extra_security_step', $extra_step ? 1 : 0, 60 * 60 );
		}//end if

		wp_send_json_success(
			array(
				'can_delete_backup' => Settings::can_delete_backup(),
			)
		);
	}
}