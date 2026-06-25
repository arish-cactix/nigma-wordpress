<?php // phpcs:ignore
/**
 * Update backup progress task in the backups page.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task\Backup\Fail;

/**
 * Update backup progress task class.
 */
class Progress extends Task {

	/**
	 * Takes the info about the running backup from the db and displays the appropriate row.
	 *
	 * @param array $args Arguments coming from the ajax call.
	 */
	public function apply( $args = array() ) {
		/**
		 * Snapshot backup progress model.
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Backup\Progress
		 */
		$model          = $args['model'];
		$backup_running = $model->get( 'backup_running' );

		$backup_running_info = $model->get_running_backup_info( $backup_running );

		$this->add_error_message_html( $model );

		if ( $model->add_errors( $this ) ) {
			delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP );
			delete_site_option( Controller\Ajax\Backup::SNAPSHOT_RUNNING_BACKUP_STATUS );

			return false;
		}

		return $backup_running_info;
	}

	/**
	 * Add error message for notice.
	 *
	 * @param \WPMUDEV\Snapshot4\Model\Backup\Progress $model Backup progress model.
	 */
	private function add_error_message_html( $model ) {
		if ( ! $model->get( 'backup_failed' ) ) {
			return;
		}
		$backup_running = $model->get( 'backup_running' );
		if ( ! isset( $backup_running['id'] ) ) {
			return;
		}
		$backup_id = $backup_running['id'];
		$data      = get_transient( 'snapshot_backup_error' );
		delete_transient( 'snapshot_backup_error' );
		if ( ! isset( $data['timestamp'] ) || ! isset( $data['backup_id'] ) || $data['backup_id'] !== $backup_id ) {
			return;
		}
		$timestamp     = $data['timestamp'];
		$service_error = $data['backup_status'];

		$dt = new \DateTime();
		$dt->setTimestamp( $timestamp );
		$dt->setTimezone( wp_timezone() );
		$time_human = $dt->format( 'd-M-Y H:i:s' );

		$p = array();
		switch ( $service_error ) {
			case Fail::ERROR_HUB_INFO_RESPONDED_INVALID_URI:
			case Fail::ERROR_HUB_INFO_RESPONDED_ERROR:
			case Fail::ERROR_USER_INFO_NOT_RETURN:
				$p[] = wp_kses_post( __( 'The backup failed due to the API connection with The Hub.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please run another backup, and <a href="%s" target="_blank">contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_STORAGE_LIMIT:
				$p[] = wp_kses_post( __( 'The backup failed due to insufficient storage space.', 'snapshot' ) );
				/* translators: %1$s - add storage link, %2$s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please <a href="%1$s" target="_blank">add more storage space</a> and run another backup. If you are unable to add more storage space, <a href="%2$s" target="_blank">contact our support team</a> for assistance.', 'snapshot' ), Fail::URL_ADD_STORAGE_SPACE, Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_FETCH_FILESLIST:
				$p[] = wp_kses_post( __( 'The backup failed due to a problem iterating over your site\'s files and building the file list.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please add <strong>define(\'SNAPSHOT4_FILELIST_LOG_VERBOSE\', true);</strong> to your site\'s wp-config.php file and run another backup. Then, <a href="#" class="snapshot-log-link">check the logs</a> to identify files that cannot be backed up. <a href="%s" target="_blank">Contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_SITE_NOT_RESPONDED_ZIPSTREAM_ERROR:
				$p[] = wp_kses_post( __( 'The backup failed while attempting to send files to our API.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please increase <strong>max_execution_time</strong> and <strong>PHP Memory</strong>, and then run another backup. <a href="%s" target="_blank">Contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_ZIPSTREAM_FILE_MISSING:
				$p[] = wp_kses_post( __( 'The backup failed while attempting to send files to our API because a file was renamed or deleted during the backup process.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please add <strong>define(\'SNAPSHOT4_FILE_ZIPSTREAM_LOG_VERBOSE\', true);</strong> to your site\'s wp-config.php file and run another backup. Then, <a href="#" class="snapshot-log-link">check the logs</a> for any folder(s) that contain files that are rapidly changing (most often a cache folder) and exclude the folder(s) from subsequent backups. <a href="%s" target="_blank">Contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_SITE_NOT_RESPONDED_LARGE_FILE_ERROR:
				$p[] = wp_kses_post( __( 'The backup failed while attempting to backup a large file over 100MB.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please add <strong>define(\'SNAPSHOT4_FILELIST_LOG_VERBOSE\', true);</strong> to your site\'s wp-config.php file and run another backup. Then, <a href="#" class="snapshot-log-link">check the logs</a> to identify the file, and exclude it from subsequent backups. <a href="%s" target="_blank">Contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_WRAPPER_DBLIST_FETCH:
				$p[] = wp_kses_post( __( 'The backup failed due to a problem iterating the database\'s tables and building the table list.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please increase <strong>max_execution_time</strong> and <strong>PHP Memory</strong>, and then run another backup. <a href="%s" target="_blank">Contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_WRAPPER_TABLE_ZIPSTREAM:
				$p[] = wp_kses_post( __( 'The backup failed while attempting to send a database table to our API.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please increase <strong>max_execution_time</strong> and <strong>PHP Memory</strong>, and then run another backup. <a href="%s" target="_blank">Contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_TOO_LARGE_TABLE_HANGED:
				$p[] = wp_kses_post( __( 'The backup failed while backing up a large table.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please <a href="#" class="snapshot-log-link">check the logs</a> to identify the table, and <a href="%s" target="_blank">contact our support team</a> to exclude the table from subsequent backups, if necessary.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_SITE_NOT_RESPONDED_ERROR:
				$p[] = wp_kses_post( __( 'The backup failed.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please increase <strong>max_execution_time</strong> and <strong>PHP Memory</strong>, and then run another backup. <a href="%s" target="_blank">Contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_GENERIC_ERROR:
				$p[] = wp_kses_post( __( 'The backup failed due to limited server resources.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please run another backup, and <a href="%s" target="_blank">contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_NONCE_FAILED:
				$p[] = wp_kses_post( __( 'The backup failed due to desynchronization with the timestamp in the nonce saved in the database.', 'snapshot' ) );
				/* translators: %1$s - guide link, %2$s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please manually delete the nonce with the help of <a href="%1$s" target="_blank">our guide</a>, and then run another backup. <a href="%2$s" target="_blank">Contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_GUIDE_BACKUP_FAILED, Fail::URL_CONTACT_SUPPORT ) );
				break;
			case Fail::ERROR_UNKNOWN_ERROR:
			default:
				$p[] = wp_kses_post( __( 'The backup failed.', 'snapshot' ) );
				/* translators: %s - support link */
				$p[] = wp_kses_post( sprintf( __( 'Please run another backup, and <a href="%s" target="_blank">contact our support team</a> if the issue persists.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) );
				break;
		}//end switch

		$html = implode(
			"\n",
			array_map(
				function ( $str ) {
					return '<p>' . $str . '</p>';
				},
				$p
			)
		);

		$html .= '<div class="notice-log-row">' .
			/* translators: %1$s - Date and time, %2$s - Backup error status */
			wp_kses_post( sprintf( __( 'Log: [%1$s] - <span class="backup-status">ERROR: %2$s</span>', 'snapshot' ), $time_human, $service_error ) ) .
			'</div>';

		$model->set( 'error_message_html', $html );
	}
}