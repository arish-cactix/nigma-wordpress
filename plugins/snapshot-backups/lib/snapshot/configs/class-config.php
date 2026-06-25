<?php // phpcs:ignore
/**
 * Handles the request to list, store & apply configs.
 *
 * @since   4.5.0
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Configs;

use Exception;
use WP_Error;
use WPMUDEV\Snapshot4\Configs\Traits\Helper;
use WPMUDEV\Snapshot4\Model\Log;

/**
 * Config class.
 */
class Config {

	use Helper;

	/**
	 * Config instance
	 *
	 * @var \WPMUDEV\Snapshot4\Configs\Config
	 */
	protected static $instance = null;

	/**
	 * Database key.
	 */
	const KEY = 'snapshot-preset_configs';

	/**
	 * Configuration ID.
	 *
	 * @var mixed
	 */
	protected $config_id = null;

	/** Dummy Constructor */
	public function __construct() { }

	/**
	 * Creates the singleton instance of this class.
	 *
	 * @return \WPMUDEV\Snapshot4\Configs\Config
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the default configurations.
	 *
	 * @return array
	 */
	public function get_default_config() {
		$settings = array(
			'schedule'        => array(
				'frequency'          => 'weekly',
				'frequency_weekday'  => 6,
				'frequency_monthday' => null,
				'files'              => 'all',
				'tables'             => 'all',
				'time'               => '00:00',
				'human_readable'     => 'Weekly @ 12:00 am on Friday',
			),
			'region'          => 'US',
			'limit'           => 5,
			'notifications'   => array(
				'on_fail_send'       => true,
				'notify_on_fail'     => true,
				'notify_on_complete' => false,
			),
			'exclusions'      => 1,
			'exclusions_list' => array(),
			'options'         => array(
				'remove_data' => 0,
			),
		);

		return array(
			'id'          => 1,
			'name'        => __( 'Default Config', 'snapshot' ),
			'description' => __( 'Recommended backup config for all sites.', 'snapshot' ),
			'default'     => 1,
			'config'      => array(
				'configs' => array(
					'settings' => $settings,
				),
				'strings' => $this->get_config_strings( $settings ),
			),
		);
	}

	/**
	 * Get the stored configs.
	 *
	 * @param int $id Config ID.
	 *
	 * @return mixed
	 */
	public function get( $id ) {
		$configs = $this->all();

		if ( ! $configs ) {
			return null;
		}

		if ( null !== $id && is_int( $id ) && $id > 0 ) {
			$config = array();

			foreach ( $configs as $cfg ) {
				if ( $id === $cfg['id'] ) {
					$config = $cfg;
					break;
				}
			}

			if ( is_array( $config ) && null !== $config['id'] ) {
				return $config;
			}
		}

		return null;
	}

	/**
	 * Get all the configs
	 *
	 * @return mixed
	 */
	public function all() {
		return get_site_option( self::KEY );
	}

	/**
	 * Set the configs.
	 *
	 * @param array $config Individual config.
	 *
	 * @return void
	 */
	public function set( $config ) {
		$configs = $this->all();

		if ( false === $configs ) {
			$data = $config;
		} else {
			if ( is_array( $config ) ) {
				$settings = $config['config'];

				if ( is_string( $settings ) ) {
					$settings         = json_decode( $settings, true );
					$config['config'] = $settings;
				}
			}
			$data = array_merge( $configs, array( $config ) );
		}

		update_site_option( self::KEY, $data );
	}

	/**
	 * Uploads the Snapshot Configuration file.
	 *
	 * @param array $file Uploaded file.
	 *
	 * @return WP_Error|array
	 */
	public function upload( $file ) {
		try {
			$config = $this->decode_and_validate_config_file( $file );
		} catch ( Exception $e ) {
			return new WP_Error( 'error_saving', $e->getMessage() );
		}

		return $config;
	}

	/**
	 * Apply the config.
	 *
	 * @param array  $config  Config to be applied.
	 * @param string $referer internal|hub.
	 *
	 * @return bool|WP_Error
	 */
	public function apply( $config, $referer = 'internal' ) {
		$errors  = new WP_Error();
		$log     = new Log();
		$applied = array();

		$current = $this->get_storage_region_info();

		$settings = 'hub' === $referer ? $config['settings'] : $config['configs'];

		// Backwards compatibility.
		$settings = isset( $settings['settings'] ) && array_key_exists( 'settings', $settings ) ? $settings['settings'] : $settings;

		$region = $this->set_region( $settings['region'] );
		if ( is_wp_error( $region ) ) {
			$errors->add( $region->get_error_code(), $region->get_error_message() );
		} else {
			$applied['region'] = $region;
		}

		$limit = $this->set_limit( $settings['limit'] );
		if ( is_wp_error( $limit ) ) {
			$errors->add( $limit->get_error_code(), $limit->get_error_message() );
		} else {
			$applied['limit'] = $limit;
		}

		if ( is_array( $settings['schedule'] ) ) {
			$schedule = $this->set_schedule( $settings['schedule'] );
			if ( is_wp_error( $schedule ) ) {
				$errors->add( $schedule->get_error_code(), $schedule->get_error_message() );
			} else {
				$log->create(
					array(
						'action' => 'schedule_is_changed_after_configs_application',
					)
				);
				$applied['schedule'] = $schedule;
			}
		}

		if ( $errors->has_errors() ) {
			// When we have errors, return immediately.
			return $errors;
		}

		if ( $current && isset( $applied['region'] ) && 0 !== strcasecmp( $current['bu_region'], $settings['region'] ) ) {
			$log->create(
				array(
					'action' => 'backups_deleted_after_applying_configs',
				)
			);
			// Remove older backups after we're sure that we've changed the region.
			$this->delete_all_backups();
		}

		if ( isset( $settings['exclusions'] ) ) {
			update_site_option( 'snapshot_exclude_large', $settings['exclusions'] );
		}

		if ( ! empty( $settings['exclusions_list'] ) ) {
			update_site_option( 'snapshot_global_exclusions', $settings['exclusions_list'] );
		}

		if ( isset( $settings['notifications'] ) && is_array( $settings['notifications'] ) ) {
			update_site_option( 'snapshot_email_settings', $settings['notifications'] );
		}

		if ( is_array( $settings['options'] ) ) {
			$options = $settings['options'];
			update_site_option( 'snapshot_remove_on_uninstall', $options['remove_data'] );
		}

		if ( 'internal' === $referer ) {
			$log->create(
				array(
					'action'  => 'snapshot_configs_applied',
					'details' => 'User applied the configs.',
				)
			);
		}

		return true;
	}

	/**
	 * Export the current settings as Config.
	 *
	 * @return array
	 */
	public function export() {
		$region_info = $this->get_storage_region_info();
		$schedule    = $this->get_formatted_schedule();

		$region        = isset( $region_info['bu_region'] ) ? strtoupper( $region_info['bu_region'] ) : 'US';
		$limit         = ( isset( $region_info['rotation_frequency'] ) ) ? $region_info['rotation_frequency'] : 5;
		$schedule      = $schedule;
		$large_files   = get_site_option( 'snapshot_exclude_large' );
		$exclusions    = get_site_option( 'snapshot_global_exclusions', array() );
		$notifications = get_site_option( 'snapshot_email_settings' );
		$on_uninstall  = get_site_option( 'snapshot_remove_on_uninstall', 0 );

		if ( isset( $notifications['on_fail_recipients'] )
			&& is_array( $notifications['on_fail_recipients'] )
			&& count( $notifications['on_fail_recipients'] ) > 0 ) {
			unset( $notifications['on_fail_recipients'] );
		}

		$configs = array(
			'region'        => $region,
			'limit'         => $limit,
			'schedule'      => $schedule,
			'notifications' => $notifications,
			'options'       => array(
				'remove_data' => boolval( $on_uninstall ),
			),
		);

		$configs['exclusions']      = boolval( $large_files );
		$configs['exclusions_list'] = $exclusions;

		return array(
			'config' => array(
				'configs' => array(
					'settings' => $configs,
				),
				'strings' => $this->get_config_strings( $configs ),
			),
		);
	}
}