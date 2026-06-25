<?php
/**
 * AJAX handler for Configs
 *
 * @since   4.5.0
 * @package Snapshot
 */

namespace WPMUDEV\Snapshot4\Configs;

use Exception;
use WP_Error;
use WPMUDEV\Snapshot4\Exceptions\Dashboard_Api_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Login_Method_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Password_Exception;
use WPMUDEV\Snapshot4\Exceptions\Invalid_Username_Exception;
use WPMUDEV\Snapshot4\Exceptions\WP_Error_Exception;
use WPMUDEV\Snapshot4\Helper\Api;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model\Env;
use WPMUDEV\Snapshot4\Task\Check\Hub;

/**
 * Ajax class
 */
class Ajax {

	/**
	 * Ajax instance
	 *
	 * @var \WPMUDEV\Snapshot4\Configs\Ajax
	 */
	protected static $instance = null;

	/**
	 * Dummy constructor.
	 */
	public function __construct() {}

	/**
	 * Creates the singleton instance of this class.
	 *
	 * @return \WPMUDEV\Snapshot4\Configs\Ajax
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Boots of the Configs AJAX class.
	 *
	 * @return void
	 */
	public function boot() {
		add_action( 'wp_ajax_snapshot_upload_config', array( $this, 'handle_upload' ) );
		add_action( 'wp_ajax_snapshot_create_config', array( $this, 'create_config' ) );
		add_action( 'wp_ajax_snapshot_apply_config', array( $this, 'apply_config' ) );
		add_action( 'wp_ajax_snapshot_sync_configs', array( $this, 'sync_configs' ) );
		add_action( 'wp_ajax_snapshot_get_region', array( $this, 'get_current_region' ) );
		add_action( 'wp_ajax_snapshot_apply_config_confirm_wpmudev_password', array( $this, 'check_wpmudev_password' ) );
		add_action( 'wp_ajax_snapshot_set_started_seen', array( $this, 'set_started_seen' ) );
		add_action( 'wp_ajax_snapshot_check_region', array( $this, 'validate_region' ) );
	}

	/**
	 * AJAX Handler:: Upload the config file
	 *
	 * @return void
	 */
	public function handle_upload() {
		check_ajax_referer( 'snapshot-fetch' );

		$permission = is_multisite() ? 'manage_network' : 'manage_options';

		if ( ! current_user_can( $permission ) ) {
			wp_send_json_error( null, 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$file = isset( $_FILES['file'] ) ? wp_unslash( $_FILES['file'] ) : false;

		if ( ! $file ) {
			wp_send_json_error( null, 400 );
		}

		$config     = Config::get_instance();
		$new_config = $config->upload( $file );

		if ( ! is_wp_error( $new_config ) ) {
			wp_send_json_success( $new_config );
		}

		wp_send_json_error(
			array(
				'error_msg' => $new_config->get_error_message(),
			)
		);
	}

	/**
	 * Creates the configuration file.
	 *
	 * @return void
	 */
	public function create_config() {
		check_ajax_referer( 'snapshot-fetch' );

		$capability = is_multisite() ? 'manage_network' : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error( null, 403 );
		}

		$config = new Config();
		wp_send_json_success( $config->export() );
	}

	/**
	 * AJAX Handler:: Apply the configuration.
	 *
	 * @return void
	 */
	public function apply_config() {
		check_ajax_referer( 'snapshot-fetch' );

		$permission = is_multisite() ? 'manage_network' : 'manage_options';

		if ( ! current_user_can( $permission ) ) {
			wp_send_json_error( null, 403 );
		}

		$config_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		if ( ! $config_id ) {
			wp_send_json_error(
				array(
					'error_msg' => esc_html__( 'Missing config ID', 'snapshot' ),
				)
			);
		}

		$config = Config::get_instance();
		$preset = $config->get( $config_id );

		if ( empty( $preset ) && 1 === $config_id ) {
			$preset = $config->get_default_config();
		}

		$current_user_can = $this->current_user_can_change_region();

		$errors = array(
			'preset'      => $preset,
			'_ajax_nonce' => wp_create_nonce( 'snapshot-fetch' ),
		);

		if ( is_array( $current_user_can ) ) {

			if ( 'success' === $current_user_can['type'] && ! $current_user_can['can_delete_backup'] ) {
				$errors['error'] = 'password_required';
			} elseif ( 'success' === $current_user_can['type'] && $current_user_can['can_delete_backup'] ) { // @phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedElseif
				// User can delete the backup.
			} else {
				wp_send_json_error(
					array(
						'error'   => $current_user_can['status'],
						'message' => $current_user_can['message'],
					)
				);
			}
		}

		if ( isset( $errors['error'] ) && 'password_required' === $errors['error'] ) {
			wp_send_json_error( $errors );
		}

		// We're now safe to proceed. Apply the config.
		$applied = $config->apply( $preset['config'] );

		if ( ! is_wp_error( $applied ) && $applied ) {
			wp_send_json_success( $preset );
		}

		if ( is_a( $applied, WP_Error::class ) ) {
			$errors = array();
			foreach ( $applied->get_error_messages() as $err ) {
				array_push( $errors, $err );
			}
			wp_send_json_error( array( 'errors' => $errors ) );
		}

		if ( ! Api::is_pro() ) {
			wp_send_json_error( array( 'error_msg' => __( 'You are limited to monthly schedule. So we\'ve defaulted it to Monthly.', 'snapshot' ) ) );
		}

		wp_send_json_error( array( 'error_msg' => __( 'Sorry! Something went wrong!', 'snapshot' ) ) );
	}

	/**
	 * AJAX Handler:: Sync the configs received from the hub.
	 *
	 * @return void
	 */
	public function sync_configs() {
		check_ajax_referer( 'snapshot-fetch' );

		$permission = is_multisite() ? 'manage_network' : 'manage_options';

		if ( ! current_user_can( $permission ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You are not authorized to perform this action.', 'snapshot' ),
				),
				404
			);
		}

		$post_configs = ( isset( $_POST['configs'] ) ) ? $_POST['configs'] : ''; // phpcs:ignore
		$post_configs = json_decode( stripslashes( $post_configs ), true );

		$config = Config::get_instance();

		$configs = $config->all();

		$ids = array();
		if ( is_array( $configs ) ) {
			foreach ( $configs as $lc ) {
				$ids[] = absint( $lc['id'] );
			}
		}

		if ( $post_configs && is_array( $post_configs ) ) {
			foreach ( $post_configs as $cfg ) {
				if ( ! in_array( absint( $cfg['id'] ), $ids, true ) ) {
					$config->set( $cfg );
				}
			}
		}

		wp_send_json_success( $config->all() );
	}

	/**
	 * AJAX Handler:: Get current snapshot storage region
	 *
	 * @return void
	 */
	public function get_current_region() {
		check_ajax_referer( 'snapshot-fetch-region' );

		$config      = Config::get_instance();
		$region_info = $config->get_storage_region_info();

		if ( is_wp_error( $region_info ) ) {
			wp_send_json_error();
		}

		wp_send_json_success( array( 'region' => $region_info['bu_region'] ) );
	}

	/**
	 * AJAX Handler: Checks WPMUDEV Password
	 *
	 * @return void
	 */
	public function check_wpmudev_password() {
		check_ajax_referer( 'snapshot-fetch' );

		$permission = is_multisite() ? 'manage_network' : 'manage_options';

		if ( ! current_user_can( $permission ) ) {
			wp_send_json_error( null, 403 );
		}

		$wpmudev_password  = isset( $_POST['password'] ) ? $_POST['password'] : null; // phpcs:ignore

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

		wp_send_json_success(
			array(
				'password_is_valid' => $password_is_valid,
			)
		);
	}

	/**
	 * If logged in user is able to change the region or delete backup.
	 *
	 * @return array
	 */
	public function current_user_can_change_region() {
		$extra_step = get_transient( 'snapshot_extra_security_step' );

		if ( false === $extra_step ) {
			$task = new Hub();

			/**
			 * Current user can change region or not.
			 *
			 * @var boolean|\WP_Error
			 */
			$result = $task->apply( array( 'api_key' => Env::get_wpmu_api_key() ) );

			if ( false === $result ) {
				return array(
					'type'    => 'error',
					'status'  => 'dashboard_error',
					'message' => esc_html__( 'This site does not appear to be registered to this user in the Hub. Please check Hub registration and try again.', 'snapshot' ),
				);
			}

			if ( is_wp_error( $result ) ) {
				return array(
					'type'    => 'error',
					'status'  => $result->get_error_code(),
					'message' => $result->get_error_message(),
				);
			}

			$result = json_decode( $result, true );

			$extra_step = isset( $result['snapshot_extra_security_step'] )
				? boolval( $result['snapshot_extra_security_step'] )
				: true;

			set_transient( 'snapshot_extra_security_step', $extra_step ? 1 : 0, 60 * 60 );
		}//end if

		return array(
			'type'              => 'success',
			'can_delete_backup' => Settings::can_delete_backup(),
		);
	}

	/**
	 * Disable the Welcome modal.
	 *
	 * @return void
	 */
	public function set_started_seen() {
		check_ajax_referer( 'snapshot-fetch' );

		$permission = is_multisite() ? 'manage_network' : 'manage_options';

		if ( ! current_user_can( $permission ) ) {
			wp_send_json_error( null, 403 );
		}

		Settings::set_started_seen( true );
		Settings::set_started_seen_persistent( true );

		wp_send_json_success();
	}

	/**
	 * Validates the current region
	 *
	 * @return void
	 */
	public function validate_region() {
		check_ajax_referer( 'snapshot-fetch' );

		$permission = is_multisite() ? 'manage_network' : 'manage_options';

		if ( ! current_user_can( $permission ) ) {
			wp_send_json_error( null, 403 );
		}

		$config_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		if ( ! $config_id ) {
			wp_send_json_error(
				array(
					'error_msg' => esc_html__( 'Missing config ID', 'snapshot' ),
				)
			);
		}

		$config = Config::get_instance();
		$preset = $config->get( $config_id );

		// Get the current region info.
		$region_info = $config->get_storage_region_info();

		$response = array(
			'nonce' => wp_create_nonce( 'snapshot-fetch' ),
		);

		if ( is_null( $region_info ) ) {
			// Probably a new installation.
			$response['current_region'] = null;
			$response['message']        = 'region_match';
			wp_send_json_success( $response );
		}

		if ( is_array( $region_info ) ) {
			$response['current_region'] = $region_info['bu_region'];

			if ( ! array_key_exists( 'bu_region', $region_info ) ) {
				// Region isn't set previously.
				$response['message'] = 'region_match';
				wp_send_json_success( $response );
			}
		}

		$settings = $preset['config']['configs'];
		$settings = array_key_exists( 'settings', $settings ) ? $settings['settings'] : $settings;

		if ( 0 === strcasecmp( $region_info['bu_region'], $settings['region'] ) ) {
			$response['message'] = 'region_match';
			// Send the json success response.
			wp_send_json_success( $response );
		}

		$response['message']       = 'region_mismatch';
		$response['config_region'] = $settings['region'];
		$response['config_name']   = $preset['name'];

		// Send the error response.
		wp_send_json_error( $response );
	}
}