<?php // phpcs:ignore
/**
 * Snapshot models: Download model
 *
 * Holds information for downloading the exported backup.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Controller\Ajax\Restore;
use WPMUDEV\Snapshot4\Helper\Fs;
use WPMUDEV\Snapshot4\Helper\Lock;
use WPMUDEV\Snapshot4\Helper\Log;
use ZipArchive;

/**
 * Export email model class
 */
class Download extends Model {

	/**
	 * Download chunk
	 *
	 * @var int
	 */
	private $chunk = 0;

	/**
	 * Reading step
	 *
	 * @var int
	 */
	private $step = 0;

	/**
	 * The basepath where the file will be downloaded at.
	 *
	 * @var string
	 */
	private $local_base = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->chunk      = 50 * 1024 * 1024;
		$this->step       = 5 * 1024 * 1024;
		$this->local_base = Lock::get_lock_dir();
	}

	/**
	 * Returns string to be used when an export has failed to be downloaded on restore.
	 *
	 * @return string
	 */
	public function get_download_error_string() {
		return esc_html__( 'the exported backup was being downloaded', 'snapshot' );
	}

	/**
	 * Invalid download link message.
	 *
	 * @return string
	 */
	public function get_downloadable_file_not_found_error_string() {
		return esc_html__( 'The provided download link is invalid or is expired.', 'snapshot' );
	}

	/**
	 * Get the size of the export file from the link.
	 *
	 * @param string $link Backup download link.
	 *
	 * @return false|array
	 */
	public function get_downloadable_backup_info( $link ) {
		$headers = get_headers( $link, true );

		$status = $headers[0];

		if ( 'HTTP/1.1 200 OK' !== $status ) {
			$this->errors[] = array(
				'invalid_link',
				$this->get_downloadable_file_not_found_error_string(),
			);

			return false;
		}

		$response = array(
			'download_link' => rawurlencode( $link ),
			'backup_id'     => $this->get( 'backup_id' ),
			'size'          => $headers['Content-Length'],
			'readable'      => Fs::format_size( $headers['Content-Length'] ),
		);

		return $response;
	}

	/**
	 * Downloads from S3 url, using chunks.
	 *
	 * @param string $download_link Exported file download link.
	 */
	public function download_backup_chunk( $download_link ) {
		$pointer = intval( get_site_option( Restore::SNAPSHOT_DOWNLOAD_BACKUP_PROGRESS, '0' ) );
		$start   = $pointer;

		$local_dirpath = path_join( $this->local_base, $this->get( 'backup_id' ) );

		if ( ! file_exists( $local_dirpath ) ) {
			mkdir( $local_dirpath, 0755 ); //phpcs:ignore
		}
		$local_filepath = path_join( $local_dirpath, $this->get( 'backup_id' ) . '.zip' );

		$download_done = false;

		// phpcs:ignore
		$localfile_handle = fopen( $local_filepath, 'ab' );

		if ( false === $localfile_handle ) {
			$this->errors[] = array(
				'failed_fopen',
				/* translators: %s - Local File */
				sprintf( __( 'We couldn\'t download the remote backup zip in %s to restore.', 'snapshot' ), $local_filepath ),
			);
			return 'failed_open';
		}

		$last_pass = false;
		while ( $pointer < $start + $this->chunk ) {
			$step_end = $pointer + $this->step;

			$headers = array(
				'Range' => 'bytes=' . $pointer . '-' . $step_end,
			);

			$args = array(
				'timeout'   => (int) apply_filters( 'snapshot4_restore_download_timeout', 20 ),
				'sslverify' => false,
				'headers'   => $headers,
			);

			$response      = wp_remote_get( $download_link, $args );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( $response_code < 200 || $response_code >= 300 ) {
				$this->errors[] = array(
					'failed_download_link',
					__( 'We couldn\'t download the remote backup zip from the given download link.', 'snapshot' ),
				);
				return;
			}

			$contents = wp_remote_retrieve_body( $response );

			if ( strlen( $contents ) < $this->step ) {
				$last_pass = true;
			}

			// phpcs:ignore
			fwrite( $localfile_handle, $contents );

			if ( $last_pass ) {
				$this->set( 'download_completed', true );

				$download_done = true;

				delete_site_option( Restore::SNAPSHOT_DOWNLOAD_BACKUP_PROGRESS );

				$lock_content = array(
					'stage' => 'files',
				);
				Lock::write( $lock_content, $this->get( 'backup_id' ) );
				break;
			}

			$pointer += ( $this->step + 1 );
		}//end while

		// phpcs:ignore
		fclose( $localfile_handle );

		if ( ! $download_done ) {
			$lock_content = array(
				'stage' => 'download',
			);

			Lock::write( $lock_content, $this->get( 'backup_id' ) );
			update_site_option( Restore::SNAPSHOT_DOWNLOAD_BACKUP_PROGRESS, $pointer );
		}

		return true;
	}

	/**
	 * Handles the download
	 *
	 * @param string $download_link Backup download link.
	 *
	 * @return array
	 */
	public function handle_download( $download_link ) {
		// phpcs:ignore Universal.Operators.DisallowShortTernary.Found
		$data = Lock::read( $this->get( 'backup_id' ) ) ?: array();
		$size = (int) $data['size'];
		$this->set( 'dowload_size', $size );

		$index = 0;
		if ( array_key_exists( 'index', $data ) ) {
			$index = (int) $data['index'];
		}

		$multiplier = 5;
		if ( $size > ( 100 * 1024 * 1024 ) ) {
			// If the downloadable size is greater than 100MB, double the size of multiplier.
			$multiplier = 10;
		}

		$chunk_size = $multiplier * 1024 * 1024;

		if ( ! array_key_exists( 'iterations', $data ) ) {
			$iterations = (int) ceil( $size / $chunk_size );
		} else {
			$iterations = (int) $data['iterations'];
		}

		$start = $index * $chunk_size;
		$end   = ( $start + $chunk_size ) - 1;

		$is_last_part = ( ( $end + $chunk_size ) > $size ) && ( $end < $size );

		$local_dirpath = path_join( $this->local_base, $this->get( 'backup_id' ) );

		if ( ! file_exists( $local_dirpath ) ) {
			mkdir( $local_dirpath, 0755 ); //phpcs:ignore
		}

		$local_file = path_join( $local_dirpath, $this->get( 'backup_id' ) . '.zip' );

		// initialize curl with the URL and range.
		$ch = curl_init( $download_link ); //phpcs:ignore
		curl_setopt( $ch, CURLOPT_RANGE, $start . '-' . $end );//phpcs:ignore

		// open the local file for appending.
		$handle = fopen( $local_file, 'ab' ); //phpcs:ignore
		if ( $handle ) {
			// set the CURLOPT_FILE option to write the data to the local file.
			curl_setopt( $ch, CURLOPT_FILE, $handle ); //phpcs:ignore
			// set the CURLOPT_BUFFERSIZE option to the chunk size.
			curl_setopt( $ch, CURLOPT_BUFFERSIZE, $chunk_size ); //phpcs:ignore
			// execute the curl request and download the data.
			curl_exec( $ch ); //phpcs:ignore
			// close the curl handle and the local file handle.
			curl_close( $ch ); //phpcs:ignore
			fclose( $handle ); //phpcs:ignore
		}

		if ( $end >= $size ) {

			// Signal the end of backup and check the final zip archive.
			if ( class_exists( 'ZipArchive' ) ) {
				$zip_archive = new \ZipArchive();

				if ( false !== $zip_archive->open( $local_file ) ) {
					$zip_archive->close();

					return array(
						'status' => 'download_complete',
					);

				} else {
					$this->errors[] = array(
						'invalid_zip',
						esc_html__( 'The downloaded zip file was invalid and cannot be opened. Please try restoration from the beginning.', 'snapshot' ),
					);

					return array(
						'status'     => 'invalid_zip',
						'local_file' => $local_file,
						'link'       => rawurlencode( $download_link ),
					);
				}
			}//end if

			// Since we couldn't check the downloaded file, let's assume it is working.
			return array(
				'status' => 'download_complete',
			);
		}//end if

		if ( $end < $size ) {
			Log::info(
				sprintf(
					/* translators: %s - Downloaded file size. */
					__( 'Downloaded %s bytes of data.', 'snapshot' ),
					$end
				)
			);

			$response = array(
				'status'     => 'part_downloaded',
				'link'       => rawurlencode( $download_link ),
				'index'      => $index + 1,
				'size'       => $size,
				'stage'      => 'download',
				'iterations' => $iterations,
				'last_part'  => $is_last_part,
				'downloaded' => $end,
			);

			return $response;
		}//end if
	}
}