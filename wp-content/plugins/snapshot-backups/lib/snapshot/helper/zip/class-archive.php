<?php // phpcs:ignore
/**
 * Snapshot requesting model abstraction class.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper\Zip;

use WPMUDEV\Snapshot4\Helper\Lock;

/**
 * Archive helper class
 */
class Archive extends Abstraction {

	/**
	 * Initializes.
	 */
	public function initialize() {
		$this->_zip = new \ZipArchive();
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

		$handle = $this->_zip->open( $this->_path );
		if ( ! $handle ) {
			return false;
		}

		$status = $this->_zip->locateName( $path );
		$this->_zip->close();

		return false === $status ? false : true;
	}

	/**
	 * Extracts from zip file
	 *
	 * @param string $destination Path to extract.
	 *
	 * @return bool
	 */
	public function extract( $destination ) {
		if ( empty( $destination ) ) {
			return false;
		}

		$destination = wp_normalize_path( $destination );
		if ( empty( $destination ) || ! file_exists( $destination ) ) {
			return false;
		}

		$handle = $this->_zip->open( $this->_path );
		if ( ! $handle ) {
			return false;
		}

		$status = $this->_zip->extractTo( $destination );

		$this->_zip->close();

		return $status;
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
		if ( ! file_exists( $destination ) ) {
			return false;
		}

		$handle = $this->_zip->open( $this->_path, \ZipArchive::RDONLY );
		if ( ! $handle ) {
			return false;
		}

		$entries               = $this->_zip->count();
		$chunk_count           = apply_filters( 'snapshot_restore_extraction_chunk', 1000 );
		$locked_content        = Lock::read( $backup_id );
		$extracted_index_start = empty( $locked_content['extracted_index'] ) ? 0 : $locked_content['extracted_index'];
		$extracted_index_end   = $extracted_index_start + $chunk_count;
		if ( $extracted_index_end > $entries ) {
			$extracted_index_end = $entries;
		}

		$extract_part = array();
		for ( $i = $extracted_index_start; $i < $extracted_index_end; $i++ ) {
			$stat = $this->_zip->statIndex( $i );
			array_push( $extract_part, $stat['name'] );
		}
		$status = $this->_zip->extractTo( $destination, $extract_part );

		if ( $extracted_index_end + 1 >= $entries ) {
			$locked_content['extracted_index'] = -1;
			$status                            = 'done';
		} else {
			$locked_content['extracted_index'] = $extracted_index_end;
		}
		Lock::write( $locked_content, $backup_id );

		$this->_zip->close();

		return $status;
	}

	/**
	 * Extracts specific files from zip file
	 *
	 * @param string $destination Path to extract.
	 * @param array  $files Files to extract.
	 *
	 * @return bool
	 */
	public function extract_specific( $destination, $files ) {
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

		$handle = $this->_zip->open( $this->_path );
		if ( ! $handle ) {
			return false;
		}

		$status = $this->_zip->extractTo( $destination, $files );
		$this->_zip->close();

		return $status;
	}
}