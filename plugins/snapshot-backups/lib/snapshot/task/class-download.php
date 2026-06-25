<?php // phpcs:ignore
/**
 * Download from S3 task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task;

use WPMUDEV\Snapshot4\Helper\Lock;
use WPMUDEV\Snapshot4\Task;

/**
 * Download task class
 */
class Download extends Task {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id'     => 'sanitize_key',
		'download_link' => self::class . '::validate_url',
	);

	/**
	 * Validates download links.
	 *
	 * @param string $download_link Download link for exported backup.
	 *
	 * @return string
	 */
	public static function validate_url( $download_link ) {
		return wp_strip_all_tags(
			stripslashes(
				filter_var( $download_link, FILTER_VALIDATE_URL )
			)
		);
	}

	/**
	 * Get the export details.
	 *
	 * @param array $args Arguements.
	 * @return array
	 */
	public function get_export_info( $args = array() ): array {
		$download_link = $args['download_link'];
		$backup_id     = $args['backup_id'];

		/**
		 * Download model
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Download
		 */
		$model = $args['model'];

		$model->set( 'backup_id', $backup_id );

		$result = $model->get_downloadable_backup_info( $download_link );

		if ( $model->add_errors( $this ) ) {
			return array();
		}

		return $result;
	}

	/**
	 * Does the initial actions needed to trigger a restore.
	 *
	 * @param array $args Restore arguments, like backup_id and rootpath.
	 */
	public function apply( $args = array() ) {
		$download_link = $args['download_link'];
		$backup_id     = $args['backup_id'];

		/**
		 * Download model
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Download
		 */
		$model = $args['model'];

		$model->set( 'backup_id', $backup_id );
		$model->set( 'download_completed', false );

		$result = $model->handle_download( $download_link );

		$content = array(
			'backup_id' => $backup_id,
		);

		switch ( $result['status'] ) {
			case 'download_complete':
				$content['stage'] = 'files';
				$model->set( 'download_completed', true );
				break;

			case 'invalid_zip':
				$content['stage'] = 'invalid';
				break;

			case 'part_downloaded':
			default:
				$content['stage']      = 'download';
				$content['size']       = $result['size'];
				$content['index']      = $result['index'];
				$content['link']       = $result['link'];
				$content['iterations'] = $result['iterations'];

				$model->set( 'downloaded_till', $result['downloaded'] );
				break;
		}//end switch
		Lock::append( $content, $backup_id );

		if ( 'invalid_zip' === $result['status'] ) {
			if ( $model->add_errors( $this ) ) {
				return false;
			}
		}

		return true;
	}
}