<?php // phpcs:ignore
/**
 * Snapshot requesting model abstraction class.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper\Zip;

use Pclzip;
use WPMUDEV\Snapshot4\Helper\Lock;

/**
 * Pclzip helper class
 */
class Pcl extends Abstraction {

	/**
	 * Initializes.
	 */
	public function initialize() {
		if ( ! defined( 'PCLZIP_TEMPORARY_DIR' ) ) {
			define( 'PCLZIP_TEMPORARY_DIR', Lock::get_lock_dir() );
		}

		if ( ! class_exists( 'PclZip' ) ) {
			include_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
		}

		$this->_zip = new PclZip( $this->_path );
	}

	/**
	 * Check for zip file
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function has( $path ) {
		$path = $this->_to_root_relative( $path );

		if ( empty( $path ) ) {
			return false;
		}

		$contents = $this->_zip->listContent();

		if ( empty( $contents ) ) {
			return false;
		}

		foreach ( $contents as $entry ) {

			if ( empty( $entry['filename'] ) ) {
				continue;
			}

			if ( $path === $entry['filename'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Extracts from zip file
	 *
	 * @param string $destination Path to extract.
	 *
	 * @return bool
	 */
	public function extract( $destination ) {
		// @todo - remove this function if not needed anymore after extract_in_chunks
		if ( empty( $destination ) ) {
			return false;
		}

		$destination = wp_normalize_path( $destination );

		if ( empty( $destination ) || ! file_exists( $destination ) ) {
			return false;
		}

		$zip_contents = $this->_zip->listContent();

		if ( empty( $zip_contents ) ) {
			return false;
		}

		$extract_files = $this->_zip->extract( PCLZIP_OPT_PATH, $destination );

		return ! empty( $extract_files );
	}

	/**
	 * Extract backup zip in chunks
	 *
	 * @param string $destination Path to extract.
	 * @param string $backup_id ID of backup being restored.
	 * @return bool|string
	 */
	public function extract_in_chunks( $destination, $backup_id ) {
		if ( empty( $destination ) ) {
			return false;
		}

		$destination = wp_normalize_path( $destination );

		if ( empty( $destination ) || ! file_exists( $destination ) ) {
			return false;
		}

		$zip_contents = $this->_zip->listContent();

		if ( empty( $zip_contents ) ) {
			return false;
		}

		$entries        = count( $zip_contents );
		$chunk_count    = apply_filters( 'snapshot_restore_extraction_chunk', 1000 );
		$locked_content = Lock::read( $backup_id );

		$extracted_index_start = empty( $locked_content['extracted_index'] ) ? 0 : $locked_content['extracted_index'];
		$extracted_index_end   = $extracted_index_start + $chunk_count;
		if ( $extracted_index_end > $entries ) {
			$extracted_index_end = $entries;
		}

		$status = true;
		for ( $i = $extracted_index_start; $i < $extracted_index_end; $i++ ) {

			$to_extract = $destination . $zip_contents[ $i ]['filename'];

			if ( file_exists( $to_extract ) ) {
				unlink( $to_extract ); //phpcs:ignore
			}

			if ( ! $this->_zip->extractByIndex( $i, $destination ) ) {
				return false;
			}
		}

		if ( $extracted_index_end + 1 >= $entries ) {
			$locked_content['extracted_index'] = -1;
			$status                            = 'done';
		} else {
			$locked_content['extracted_index'] = $extracted_index_end;
		}

		Lock::write( $locked_content, $backup_id );

		return $status;
	}

	/**
	 * Extracts specific files from zip file
	 *
	 * @param string $destination Path to extract.
	 * @param array  $files       Files to extract.
	 *
	 * @return bool
	 */
	public function extract_specific( $destination, $files ) {
		// @todo - remove this function if not needed anymore after extract_in_chunks
		if ( empty( $destination ) ) {
			return false;
		}

		if ( empty( $files ) ) {
			return false;
		}

		if ( ! is_array( $files ) ) {
			return false;
		}

		$destination = wp_normalize_path( $destination );

		if ( empty( $destination ) || ! file_exists( $destination ) ) {
			return false;
		}

		$zip_contents = $this->_zip->listContent();

		if ( empty( $zip_contents ) ) {
			return false;
		}

		$extract_files = $this->_zip->extract( PCLZIP_OPT_PATH, $destination, PCLZIP_OPT_BY_NAME, $files );

		return ! empty( $extract_files );
	}
}