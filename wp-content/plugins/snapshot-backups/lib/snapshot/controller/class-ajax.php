<?php // phpcs:ignore
/**
 * Snapshot controllers: AJAX controller class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Main;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Helper\Api;
use WPMUDEV\Snapshot4\Helper\Explorer;
use WPMUDEV\Snapshot4\Model\Env;
use WPMUDEV\Snapshot4\Model\Log as ModelLog;

/**
 * AJAX controller class
 */
class Ajax extends Controller {

	const TYPE_GET  = 'GET';
	const TYPE_POST = 'POST';

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		add_action( 'wp_ajax_save_snapshot_settings', array( $this, 'save_snapshot_settings' ) );
		add_action( 'wp_ajax_reset_snapshot_settings', array( $this, 'reset_snapshot_settings' ) );
		add_action( 'wp_ajax_delete_snapshot_logs', array( $this, 'delete_all_logs' ) );
		add_action( 'wp_ajax_reactivate_snapshot_schedule', array( $this, 'reactivate_snapshot_schedule' ) );
		add_action( 'wp_ajax_save_snapshot_region', array( $this, 'save_snapshot_region' ) );
		add_action( 'wp_ajax_snapshot_delete_all_backups', array( $this, 'delete_all_backups' ) );
		add_action( 'wp_ajax_snapshot-handle_dashboard', array( $this, 'json_handle_dashboard' ) );
		add_action( 'wp_ajax_snapshot-uninstall_snapshot_v3', array( $this, 'json_uninstall_snapshot_v3' ) );
		add_action( 'wp_ajax_snapshot-uninstall_v3_notice_dismiss', array( $this, 'json_uninstall_v3_notice_dismiss' ) );
		add_action( 'wp_ajax_snapshot-get_storage', array( $this, 'json_get_storage' ) );
		add_action( 'wp_ajax_snapshot_change_region', array( $this, 'snapshot_change_region' ) );
		add_action( 'wp_ajax_snapshot_change_storage_limit', array( $this, 'snapshot_change_storage_limit' ) );
		add_action( 'wp_ajax_snapshot-check_creds', array( $this, 'check_creds' ) );
		add_action( 'wp_ajax_snapshot-migrate_region', array( $this, 'migrate_region' ) );
		add_action( 'wp_ajax_snapshot-recheck_requirements', array( $this, 'json_recheck_requirements' ) );
		add_action( 'wp_ajax_snapshot-json_validate_email', array( $this, 'json_validate_email' ) );
		add_action( 'wp_ajax_snapshot-whats_new_seen', array( $this, 'json_whats_new_seen' ) );
		add_action( 'wp_ajax_snapshot-tutorials_slider_seen', array( $this, 'json_tutorials_slider_seen' ) );
		add_action( 'wp_ajax_snapshot-dismiss_export_notice', array( $this, 'dismiss_export_notice' ) );

		/**
		 * Instantiate the explorer class, that is going to handle the AJAX request.
		 */
		$explorer = Explorer::instance();
		add_action( 'wp_ajax_snapshot-file_explorer', array( $explorer, 'json_file_explorer' ) );
	}

	/**
	 * Checks whether the current user can perform any of the AJAX actions
	 *
	 * Dies with JSON error if they can't as a side-effect.
	 *
	 * @param string $action Optional nonce action to check.
	 * @param string $type Optional request type (used with action param).
	 *
	 * @return bool
	 */
	public function do_request_sanity_check( $action = '', $type = false ) {
		if ( ! empty( $action ) ) {
			$type = ! empty( $type ) ? $type : self::TYPE_POST;
			// @codingStandardsIgnoreLine This is where we actually process the nonce.
			$request = self::TYPE_POST === $type ? $_POST : $_GET;

			if (
				! isset( $request['_wpnonce'] ) ||
				! wp_verify_nonce( $request['_wpnonce'], $action ) ||
				! snapshot_user_can_snapshot()
			) {
				return wp_send_json_error(
					__( 'You are not authorized to perform this action.', 'snapshot' )
				);
			}
		}

		if ( snapshot_user_can_snapshot() ) {
			// All good.
			return true;
		}

		return wp_send_json_error(
			__( 'You are not authorized to perform this action.', 'snapshot' )
		);
	}

	/**
	 * Requests current storage limits and used space for the active API key.
	 */
	public function json_get_storage() {
		$this->do_request_sanity_check( 'snapshot_get_storage', self::TYPE_POST );

		$stats = get_transient( 'snapshot_current_stats' );

		if ( false !== $stats ) {
			wp_send_json_success( $stats );
		}

		$args = array();

		$args['api_key'] = Env::get_wpmu_api_key();

		$task   = new Task\Check\Hub();
		$result = $task->apply( $args );

		if ( false === $result ) {
			$result = new \WP_Error( 'dashboard_error', 'This site does not appear to be registered to this user in the hub. Please check hub registration and try again.', array( 'status' => 403 ) );
			wp_send_json_error( $result );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result );
		}

		$result = json_decode( $result, true );

		$response                  = array();
		$response['width_float']   = round( $result['current_bytes'] / $result['user_limit'], 6 );
		$response['width']         = round( 100 * $response['width_float'], 4 ) . '%';
		$response['current_stats'] = esc_html( round( $result['current_bytes'] / ( 1024 * 1024 * 1024 ), 1 ) . ' GB/' . round( $result['user_limit'] / ( 1024 * 1024 * 1024 ), 1 ) . ' GB' );

		$response['snapshot_extra_security_step'] = isset( $result['snapshot_extra_security_step'] )
			? $result['snapshot_extra_security_step']
			: true;

		set_transient( 'snapshot_current_stats', $response, 60 * 60 );

		wp_send_json_success( $response );
	}

	/**
	 * Ajax for installing/activating WPMUDEV Dashboard.
	 */
	public function json_handle_dashboard() {
		$this->do_request_sanity_check( 'snapshot_install_dashboard', self::TYPE_POST );

		$installed = isset( $_POST['installed'] ) ? boolval( $_POST['installed'] ) : null; // phpcs:ignore

		if ( $installed ) {
			// Activate and check if we're logged in.
			$result = activate_plugin( '/wpmudev-updates/update-notifications.php' );

			if ( ! is_wp_error( $result ) ) {
				update_site_option( 'wdp_un_autoactivated', 1 );

				$logged_in = Task\Check\Hub::has_api_key();

				$welcome_modal = ! Settings::get_started_seen();

				$redirect_to = $logged_in ? false : network_admin_url( 'admin.php?page=wpmudev' );

				wp_send_json_success(
					array(
						'action'                => 'activation',
						'result'                => $result,
						'redirect_to'           => $redirect_to,
						'welcome_modal'         => $welcome_modal,
						'reactivate_membership' => Api::need_reactivate_membership(),
					)
				);
			}

			wp_send_json_error( $result );
		}//end if

		// Dashboard is not installed, lets install.
		wp_send_json_success(
			array(
				'action' => 'installation',
				'nonce'  => wp_create_nonce( 'install-plugin_install_wpmudev_dash' ),
			)
		);
	}

	/**
	 * Ajax for uninstalling Snapshot v3.
	 */
	public function json_uninstall_snapshot_v3() {
		$this->do_request_sanity_check( 'snapshot_uninstall_snapshot_v3', self::TYPE_POST );

		deactivate_plugins( '/snapshot/snapshot.php' );

		if ( ! class_exists( '\WPMUDEV_Dashboard' ) ) {
			wp_send_json_error();
		}

		$upgrader       = \WPMUDEV_Dashboard::$upgrader;
		$v3_uninstalled = $upgrader->delete_plugin( 257 );

		if ( empty( $v3_uninstalled ) ) {
			wp_send_json_error();
		}

		// V3 is uninstalled.
		wp_send_json_success();
	}

	/**
	 * Persistently dismiss the uninstall v3 notice.
	 */
	public function json_uninstall_v3_notice_dismiss() {
		$this->do_request_sanity_check( 'snapshot_dismiss_uninstall_notice', self::TYPE_POST );
		update_site_option( Main::SNAPSHOT4_V3_ADMIN_NOTICE_DISMISSED, true );
		wp_send_json_success();
	}

	/**
	 * Stores the submitted region in the system's DB.
	 */
	public function save_snapshot_region() {
		$this->do_request_sanity_check( 'save_snapshot_region', self::TYPE_POST );

		// phpcs:ignore WordPress.Security.NonceVerification
		$region = isset( $_POST['region'] ) ? sanitize_key( $_POST['region'] ) : null;

		// Store the region system-side.
		$data           = array();
		$data['action'] = 'set';

		$task             = new Task\Request\Region();
		$validated_params = $task->validate_request_data( $data );

		$args                  = $validated_params;
		$args['request_model'] = new Model\Request\Region();
		$args['region']        = strtoupper( $region );

		$result = $task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}
			wp_send_json_error();
		}

		$regions = array(
			'US',
			'EU',
		);

		if ( ! in_array( $result, $regions, true ) ) {
			Log::error( __( 'The backup region had a different value than expected and it wasn\'t stored properly system-side.', 'snapshot' ) );
			wp_send_json_error();
		}

		// Let's update the flag so that we dont keep displaying the Get Started modal.
		Settings::set_started_seen( true );

		// And let's update the flag so that we know we have gone through all that if we unistall/reinstall.
		Settings::set_started_seen_persistent( true );

		// Let's delete the local db entry of region (if there is one).
		delete_site_option( 'snapshot_backup_region' );

		Log::info( __( 'The backup region has been set system-side.', 'snapshot' ) );

		wp_send_json_success(
			array(
				'selected_region' => $args['region'],
			)
		);
	}

	/**
	 * Ajax for saving settings
	 */
	public function save_snapshot_settings() {
		$this->do_request_sanity_check( 'save_snapshot_settings', self::TYPE_POST );
		$result = array();
		$log    = new ModelLog();
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['global_exclusions'] ) ) {
			$global_exclusions = array_map( 'sanitize_text_field', json_decode( wp_unslash( $_POST['global_exclusions'] ), true ) );  // phpcs:ignore
			$old_exclusions    = get_site_option( 'snapshot_global_exclusions', array() );

			if ( ( count( $global_exclusions ) !== count( $old_exclusions ) ) || ! empty( array_diff( $global_exclusions, $old_exclusions ) ) ) {
				$log->create( array( 'action' => 'snapshot_global_exclusions_updated' ) );
				update_site_option( 'snapshot_global_exclusions', $global_exclusions );
			}
			$result['global_exclusions'] = $global_exclusions;
		}

		// @phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['snapshot-excluded-tables'] ) ) {
			$db_exclusions = "[]" === $_POST['snapshot-excluded-tables'] ? array() : (array) $_POST['snapshot-excluded-tables']; // phpcs:ignore
			$db_exclusions  = array_map( 'sanitize_text_field', $db_exclusions );
			$old_exclusions = get_site_option( 'snapshot_excluded_tables', array() );
			if ( ( count( $db_exclusions ) !== count( $old_exclusions ) ) || ! empty( array_diff( $db_exclusions, $old_exclusions ) ) ) {
				update_site_option( 'snapshot_excluded_tables', $db_exclusions );
				$log->create( array( 'action' => 'snapshot_tables_exclusion_updated' ) );
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['exclusions_settings'] ) ) {
			add_site_option( 'snapshot_exclude_large', true );
			// phpcs:ignore WordPress.Security.NonceVerification
			$default_exclusions = isset( $_POST['snapshot-default-exclusions'] ) ? true : false;
			$old_exclusions     = (bool) get_site_option( 'snapshot_exclude_large', $default_exclusions );

			if ( $default_exclusions !== $old_exclusions ) {
				if ( $default_exclusions ) {
					$log->create( array( 'action' => 'snapshot_large_exclusions_enabled' ) );
				} else {
					$log->create( array( 'action' => 'snapshot_large_exclusions_disabled' ) );
				}
			}
			update_site_option( 'snapshot_exclude_large', $default_exclusions );
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['build_type'] ) && in_array( $_POST['build_type'], array( 'php_code', 'mysqldump' ), true ) ) {
			$old_method = get_site_option( 'snapshot_database_build_type' );
			if ( $_POST['build_type'] !== $old_method ) { // phpcs:ignore
				$log->create(
					array(
						'action'  => 'snapshot_db_build_type_modified',
						'details' => 'Build type is set to: ' . sanitize_text_field( wp_unslash( $_POST['build_type'] ) ), //phpcs:ignore
					)
				);
			}
			Settings::set_db_build_method( sanitize_text_field( wp_unslash( $_POST['build_type'] ) ) ); //phpcs:ignore
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['remove_on_uninstall'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$remove_on_uninstall = intval( boolval( wp_unslash( $_POST['remove_on_uninstall'] ) ) );
			update_site_option( 'snapshot_remove_on_uninstall', $remove_on_uninstall );
			$result['remove_on_uninstall'] = $remove_on_uninstall;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['email_settings'] ) ) {
		// phpcs:ignore
			$email_settings = json_decode( wp_unslash( $_POST['email_settings'] ), true );

			$sanitized_settings                       = array();
			$sanitized_settings['on_fail_send']       = false;
			$sanitized_settings['on_fail_recipients'] = array();

			if ( isset( $email_settings['on_fail_send'] ) ) {
				$sanitized_settings['on_fail_send'] = boolval( $email_settings['on_fail_send'] );
			}
			if ( isset( $email_settings['notify_on_fail'] ) ) {
				$sanitized_settings['notify_on_fail'] = boolval( $email_settings['notify_on_fail'] );
			}
			if ( isset( $email_settings['notify_on_complete'] ) ) {
				$sanitized_settings['notify_on_complete'] = boolval( $email_settings['notify_on_complete'] );
			}
			if ( isset( $email_settings['on_fail_recipients'] ) && is_array( $email_settings['on_fail_recipients'] ) ) {
				foreach ( $email_settings['on_fail_recipients'] as $recipient ) {
					$sanitized_settings['on_fail_recipients'][] = array_map( 'sanitize_text_field', $recipient );
				}
			}

			$log->create(
				array(
					'action' => 'snapshot_email_settings_updated',
				)
			);

			Settings::update_email_settings( $sanitized_settings );
			$result                    = Settings::get_email_settings();
			$result['top_notice_text'] = __( 'Your settings have been updated successfully.', 'snapshot' );
		}//end if

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['snapshot-backup-limit'] ) ) {
			$limit = filter_input( INPUT_POST, 'snapshot-backup-limit', FILTER_SANITIZE_NUMBER_INT );

			if ( isset( $_POST['existing_backup_limit'] ) && $limit !== $_POST['existing_backup_limit'] ) { //phpcs:ignore
				$result['continue_ajax'] = true;
				$result['next_action']   = 'change_storage_limit';
				$result['next_args']     = array(
					'action'        => 'snapshot_change_storage_limit',
					'_wpnonce'      => wp_create_nonce( 'snapshot_change_storage_limit' ),
					'storage_limit' => $limit,
				);
			}
		}

		if ( count( $result ) ) {
			wp_send_json_success( $result );
		}

		wp_send_json_error();
	}

	/**
	 * Ajax for resetting settings
	 */
	public function reset_snapshot_settings() {
		$this->do_request_sanity_check( 'reset_snapshot_settings', self::TYPE_POST );

		$task = new Task\Reset();
		$task->apply();

		$log = new ModelLog();
		$log->create(
			array(
				'action' => 'snapshot_settings_has_been_reset',
			)
		);

		Log::info( __( 'Snapshot settings have been reset.', 'snapshot' ) );

		wp_send_json_success();
	}

	/**
	 * Ajax for deleting all logs
	 */
	public function delete_all_logs() {
		$this->do_request_sanity_check( 'delete_snapshot_logs', self::TYPE_POST );
		Log::remove_log_dir( true );

		$log = new ModelLog();
		$log->create(
			array(
				'action' => 'snapshot_logs_deleted',
			)
		);
		update_option( 'snapshot_skip_logs_before', time() );
		wp_send_json_success();
	}

	/**
	 * Ajax for deleting all backups
	 */
	public function delete_all_backups() {
		$this->do_request_sanity_check( 'snapshot_delete_all_backups', self::TYPE_POST );

		if ( ! Settings::can_delete_backup() ) {
			wp_send_json_error();
		}

		$task = new Task\Request\Delete();

		$args                  = array();
		$args['request_model'] = new Model\Request\Delete();
		$result                = $task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}
			wp_send_json_error();
		}

		$log = new ModelLog();
		$log->create(
			array(
				'action' => 'snapshot_backups_deleted',
			)
		);

		wp_send_json_success();
	}

	/**
	 * Ajax for reactivating schedule after re-install.
	 */
	public function reactivate_snapshot_schedule() {
		$this->do_request_sanity_check( 'reactivate_snapshot_schedule', self::TYPE_POST );

		$activate_schedule = get_site_option( 'snapshot_activate_schedule', true );
		$stored_schedule   = Task\Request\Schedule::get_current_schedule();

		// Let's update the flag so that we dont keep displaying the Get Started modal.
		Settings::set_started_seen( true );

		// And let's update the flag so that we know we have gone through all that if we unistall/reinstall.
		Settings::set_started_seen_persistent( true );

		if ( empty( $stored_schedule ) ) {
			// We never had any schedules before, so lets go on with our lives.
			wp_send_json_success(
				array(
					'show_schedule_modal' => true,
				)
			);
		}

		if ( isset( $stored_schedule['bu_status'], $stored_schedule['bu_frequency'] ) && 'inactive' === $stored_schedule['bu_status'] && 'None' === $stored_schedule['bu_frequency'] ) {
			// We have already set the schedule to None on the Hub.
			wp_send_json_success(
				array(
					'show_schedule_modal' => false,
				)
			);
		}

		if ( ! empty( $activate_schedule ) && ( isset( $stored_schedule['bu_status'] ) && 'active' === $stored_schedule['bu_status'] ) ) {
			// This means, we have uninstalled Snapshot before and selected to keep options in the event of re-install.
			$request_data['status'] = 'active';
			$show_schedule_modal    = false;
		} else {
			// This means, we have uninstalled Snapshot before and selected to remove options in the event of re-install, or we had no active schedule before uninstalling.
			$request_data['status'] = 'inactive';
			$show_schedule_modal    = true;
		}

		// Delete the entry telling us whether we should activate the schedule or not.
		delete_site_option( 'snapshot_activate_schedule' );

		// Lets see if we can reset the schedule, by making (or keeping it) it inactive.
		if ( isset( $stored_schedule['bu_status'] ) && 'active' === $stored_schedule['bu_status'] ) {
			// @TODO: Refactor this *please*.
			$request_data['schedule_id']        = isset( $stored_schedule['schedule_id'] ) ? $stored_schedule['schedule_id'] : null;
			$request_data['site_id']            = isset( $stored_schedule['site_id'] ) ? $stored_schedule['site_id'] : null;
			$request_data['frequency']          = isset( $stored_schedule['bu_frequency'] ) ? $stored_schedule['bu_frequency'] : null;
			$request_data['files']              = isset( $stored_schedule['bu_files'] ) ? $stored_schedule['bu_files'] : null;
			$request_data['tables']             = isset( $stored_schedule['bu_tables'] ) ? $stored_schedule['bu_tables'] : null;
			$request_data['time']               = isset( $stored_schedule['bu_time'] ) ? $stored_schedule['bu_time'] : null;
			$request_data['frequency_weekday']  = isset( $stored_schedule['bu_frequency_weekday'] ) ? $stored_schedule['bu_frequency_weekday'] : null;
			$request_data['frequency_monthday'] = isset( $stored_schedule['bu_frequency_monthday'] ) ? $stored_schedule['bu_frequency_monthday'] : null;

			$request_data['frequency_weekday']  = 'null' !== $request_data['frequency_weekday'] ? $request_data['frequency_weekday'] : null;
			$request_data['frequency_monthday'] = 'null' !== $request_data['frequency_monthday'] ? $request_data['frequency_monthday'] : null;

			$schedule_model = new Model\Schedule( $request_data );
			$request_model  = new Model\Request\Schedule();

			$reset_schedule_args                   = array();
			$reset_schedule_args['request_model']  = $request_model;
			$reset_schedule_args['schedule_model'] = $schedule_model;
			$reset_schedule_args['action']         = 'update';

			$task = new Task\Request\Schedule();

			$task->apply( $reset_schedule_args );
			if ( $task->has_errors() ) {
				foreach ( $task->get_errors() as $error ) {
					Log::error( $error->get_error_message() );
				}

				wp_send_json_error(
					array(
						'show_schedule_modal' => $show_schedule_modal,
					)
				);
			}
		}//end if

		// Response to "service".
		wp_send_json_success(
			array(
				'show_schedule_modal' => $show_schedule_modal,
			)
		);
	}

	/**
	 * Ajax for changing region.
	 */
	public function snapshot_change_region() {
		$this->do_request_sanity_check( 'snapshot_change_region', self::TYPE_POST );

		if ( ! Settings::can_delete_backup() ) {
			wp_send_json_error();
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		$no_backups = ( isset( $_POST['no_backups'] ) && false === boolval( $_POST['no_backups'] ) ) ? false : true;
		// phpcs:ignore WordPress.Security.NonceVerification
		$new_region = ( isset( $_POST['new_region'] ) && 'EU' === $_POST['new_region'] ) ? 'EU' : 'US';

		$changed_schedule = false;

		// First we delete all existing backups, if we got any.
		if ( true !== $no_backups ) {
			$task = new Task\Request\Delete();

			$args                  = array();
			$args['request_model'] = new Model\Request\Delete();

			$task->apply( $args );

			if ( $task->has_errors() ) {
				foreach ( $task->get_errors() as $error ) {
					Log::error( $error->get_error_message() );
				}
				wp_send_json_error();
			}
		}

		$backup_running = get_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP );
		if ( isset( $backup_running['id'] ) ) {
			update_site_option( Controller\Ajax\Backup::SNAPSHOT_CANCELLED_BACKUP, $backup_running['id'] );
			delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP );
			delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP_STATUS );
		}

		// Then we see if there's an active schedule, to decide what notice we're gonna show on success.
		$stored_schedule = Task\Request\Schedule::get_current_schedule( true );
		if ( ! is_wp_error( $stored_schedule ) ) {
			if ( isset( $stored_schedule['bu_status'] ) && 'active' === $stored_schedule['bu_status'] ) {
				$changed_schedule = true;
			} else {
				$changed_schedule = false;
			}
		}

		// And now we can actually store the region system-side.
		$data           = array();
		$data['action'] = 'set';

		$task             = new Task\Request\Region();
		$validated_params = $task->validate_request_data( $data );

		$args                  = $validated_params;
		$args['request_model'] = new Model\Request\Region();
		$args['region']        = $new_region;

		$task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}
			wp_send_json_error();
		}

		$log = new ModelLog();
		$log->create(
			array(
				'action' => 'snapshot_region_change',
			)
		);

		wp_send_json_success(
			array(
				'changed_schedule' => $changed_schedule,
			)
		);
	}

	/**
	 * Ajax for changing storage limit.
	 */
	public function snapshot_change_storage_limit() {
		$this->do_request_sanity_check( 'snapshot_change_storage_limit', self::TYPE_POST );

		// phpcs:ignore WordPress.Security.NonceVerification
		$storage_limit = ( isset( $_POST['storage_limit'] ) ) ? intval( $_POST['storage_limit'] ) : 30;

		$data           = array();
		$data['action'] = 'set_storage';

		$task             = new Task\Request\Region();
		$validated_params = $task->validate_request_data( $data );

		$args                  = $validated_params;
		$args['request_model'] = new Model\Request\Region();
		$args['storage_limit'] = $storage_limit;

		$changed_storage = $task->apply( $args );

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}
			wp_send_json_error();
		}

		wp_send_json_success(
			array(
				'changed_storage' => $changed_storage,
			)
		);
	}

	/**
	 * Ajax for checking creds (region, storage limit) service-side.
	 */
	public function check_creds() {
		$this->do_request_sanity_check( 'snapshot_check_if_region', self::TYPE_POST );

		$data           = array();
		$data['action'] = 'get';

		$task             = new Task\Request\Region();
		$validated_params = $task->validate_request_data( $data );

		$args                  = $validated_params;
		$args['request_model'] = new Model\Request\Region();
		$result                = $task->apply( $args );

		if ( $task->has_errors() ) {
			$messages = array();

			foreach ( $task->get_errors() as $error ) {
				array_push( $messages, $error->get_error_message() );
				Log::error( $error->get_error_message() );
			}
			wp_send_json_error( array( 'errors' => $messages ) );
		}

		wp_send_json_success(
			array(
				'region'             => isset( $result['bu_region'] ) ? $result['bu_region'] : null,
				'rotation_frequency' => isset( $result['rotation_frequency'] ) ? $result['rotation_frequency'] : 30,
			)
		);
	}

	/**
	 * Ajax for migrating region from local db(v.4.0.0) to system db(v.4.0.1+).
	 */
	public function migrate_region() {
		$this->do_request_sanity_check( 'snapshot_migrate_region', self::TYPE_POST );

		// Let's add a flag so that we don't attempt that API call more than once on page load.
		add_site_option( 'snapshot4_region_migration_attempt', true );
		// Autoloaded for single site.

		$local_region = get_site_option( 'snapshot_backup_region', false );

		if ( empty( $local_region ) ) {
			// We're good, no local region here.
			wp_send_json_success();
		}

		// Store the region system-side.
		$data           = array();
		$data['action'] = 'get';

		$get_task         = new Task\Request\Region();
		$validated_params = $get_task->validate_request_data( $data );

		$args                  = $validated_params;
		$args['request_model'] = new Model\Request\Region();

		$result = $get_task->apply( $args );

		if ( $get_task->has_errors() ) {
			// The request failed, lets repeat in an hour.
			wp_schedule_single_event( time() + 3600, 'snapshot4_retry_region_migration' );
			wp_send_json_error();
		}

		$region = isset( $result['bu_region'] ) ? $result['bu_region'] : null;
		if ( ! empty( $region ) ) {
			// Region is set system-side, no need to update it.
			wp_send_json_success();
		}

		$data['action'] = 'set';

		$set_task         = new Task\Request\Region();
		$validated_params = $set_task->validate_request_data( $data );

		$args                  = $validated_params;
		$args['request_model'] = new Model\Request\Region();
		$args['region']        = strtoupper( $local_region );

		$result = $set_task->apply( $args );

		if ( $set_task->has_errors() ) {
			foreach ( $set_task->get_errors() as $error ) {
				Log::error( $error->get_error_message() );
			}

			wp_schedule_single_event( time() + 3600, 'snapshot4_retry_region_migration' );

			wp_send_json_error();
		}

		$regions = array(
			'US',
			'EU',
		);

		if ( ! in_array( $result, $regions, true ) ) {
			Log::error( __( 'The backup region had a different value than expected and it wasn\'t stored properly system-side.', 'snapshot' ) );
			wp_schedule_single_event( time() + 3600, 'snapshot4_retry_region_migration' );
			wp_send_json_error();
		}

		// Let's delete the local db entry of region.
		delete_site_option( 'snapshot_backup_region' );

		Log::info( __( 'The backup region has been set system-side.', 'snapshot' ) );

		wp_send_json_success();
	}

	/**
	 * Ajax for rechecking Snapshot requirements before triggering a backup.
	 */
	public function json_recheck_requirements() {
		$this->do_request_sanity_check( 'snapshot_recheck_requirements', self::TYPE_POST );

		wp_send_json_success(
			array(
				'compat_php_version' => version_compare( phpversion(), '7.4.0' ),
			)
		);
	}

	/**
	 * Validate email (for failed backup notifications).
	 */
	public function json_validate_email() {
		$this->do_request_sanity_check( 'snapshot_validate_email', self::TYPE_POST );
		$is_valid = false;
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['email'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification
			$is_valid = is_email( wp_unslash( $_POST['email'] ) );
		}
		wp_send_json_success( array( 'is_valid' => $is_valid ) );
	}

	/**
	 * Set "What's new" modal as viewed
	 */
	public function json_whats_new_seen() {
		$this->do_request_sanity_check( 'snapshot_whats_new_seen', self::TYPE_POST );
		Settings::set_whats_new_seen();
		wp_send_json_success();
	}

	/**
	 * Set the Tuturials Slider as viewed
	 */
	public function json_tutorials_slider_seen() {
		$this->do_request_sanity_check( 'snapshot_tutorials_slider_seen', self::TYPE_POST );
		Settings::set_snapshot_tutorials_seen();
		wp_send_json_success();
	}

	/**
	 * Dismiss the export notice.
	 */
	public function dismiss_export_notice() {
		$this->do_request_sanity_check( 'snapshot-dismiss-exported-backup-notification', self::TYPE_GET );
		delete_site_transient( 'snapshot_download_link_notification' );
		wp_send_json_success();
	}
}