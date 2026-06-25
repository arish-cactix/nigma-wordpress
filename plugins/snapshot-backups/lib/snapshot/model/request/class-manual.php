<?php // phpcs:ignore
/**
 * Snapshot models: Manual backup model
 *
 * Holds information about the manual backup to be triggered.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model\Log;

/**
 * Manual backup model class
 */
class Manual extends Model\Request {

	const SERVICE_MESSAGE_BACKUP_RUNNING_ALREADY = 'another backup is already running';
	const ERROR_BACKUP_RUNNING_ALREADY           = 'backup_running_already';

	const SERVICE_MESSAGE_BACKUP_SAME_MINUTE = 'backup can\'t run in same minute';
	const ERROR_BACKUP_SAME_MINUTE           = 'backup_same_minute';

	/**
	 * Triggering manual backup request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'schedules';

	/**
	 * Returns action string for logger
	 *
	 * @return string
	 */
	protected function get_action_string() {
		return __( 'create manual backup', 'snapshot' );
	}

	/**
	 * Triggers a manual backup.
	 *
	 * @param string $backup_name The name the user has given for this manual backup.
	 *
	 * @return array|mixed|object
	 */
	public function trigger_manual_backup( $backup_name ) {
		$data   = array();
		$method = 'post';
		$path   = $this->get_api_url();

		$data['bu_frequency']         = 'manual';
		$data['bu_snapshot_name']     = $backup_name;
		$data['bu_files']             = 'all';
		$data['bu_tables']            = 'all';
		$data['bu_time']              = false;
		$data['bu_status']            = false;
		$data['description']          = $this->get( 'description' );
		$data['bu_exclusion_enabled'] = $this->get( 'apply_exclusions' );
		$data['site_name']            = $this->get_this_site();
		$data['plugin_v']             = defined( 'SNAPSHOT_BACKUPS_VERSION' ) ? SNAPSHOT_BACKUPS_VERSION : null;

		$response = $this->request( $path, $data, $method );

		$log = new Log();
		$log->create(
			array(
				'action' => 'manual_backup_started',
			)
		);

		return $response;
	}

	/**
	 * Logs the error message for the latest api request.
	 */
	protected function on_response_error() {
		if ( 400 === $this->response_code ) {
			$response = json_decode( $this->response_body, true );
			$message  = isset( $response['message'] ) ? $response['message'] : null;
			if ( self::SERVICE_MESSAGE_BACKUP_RUNNING_ALREADY === $message ) {
				$this->errors[] = array(
					self::ERROR_BACKUP_RUNNING_ALREADY,
					__( 'The backup failed because there\'s another backup running in parallel.', 'snapshot' ),
				);
				$this->add( 'messages', self::ERROR_BACKUP_RUNNING_ALREADY );
				return;
			} elseif ( self::SERVICE_MESSAGE_BACKUP_SAME_MINUTE === $message ) {
				$this->errors[] = array(
					self::ERROR_BACKUP_SAME_MINUTE,
					__( 'The backup failed because another backup was created in the very same minute.', 'snapshot' ),
				);
				$this->add( 'messages', self::ERROR_BACKUP_SAME_MINUTE );
				return;
			}
		}

		parent::on_response_error();
	}
}