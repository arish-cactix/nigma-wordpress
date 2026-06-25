<?php // phpcs:ignore
/**
 * Snapshot helpers: lock helper class
 *
 * Does locking-related work - writing to locks, reading from locks, etc. Used in backup restores.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

use WPMUDEV\Snapshot4\Helper\Fs;
/**
 * Lock helper class
 */
class Lock {
	const UPLOADS_SUBDIR = 'snapshot-backups';

	/**
	 * Writes to backup-specific lock.
	 *
	 * @param array  $content The content to be written, in data pairs.
	 * @param string $backup_id The backup_id that is being restored.
	 * @param string $lockname The name of the lock.
	 */
	public static function write( $content, $backup_id, $lockname = '' ) {
		$search_by = empty( $lockname ) ? $backup_id : $backup_id . '-' . $lockname;
		$filename  = self::get_lock_filename( $search_by );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		file_put_contents( $filename, wp_json_encode( $content ) ); //phpcs:ignore
	}

	/**
	 * Returns lock contents
	 *
	 * @param string $backup_id Backup id.
	 * @param string $lockname The name of the lock.
	 *
	 * @return mixed|bool
	 */
	public static function read( $backup_id, $lockname = '' ) {
		$search_by = empty( $lockname ) ? $backup_id : $backup_id . '-' . $lockname;
		$filename  = self::get_lock_filename( $search_by );

		if ( file_exists( $filename ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			return json_decode( file_get_contents( $filename ), true );
		}

		return false;
	}

	/**
	 * Appends to backup-specific lock.
	 *
	 * @param array  $content The content to be appended.
	 * @param string $backup_id The backup_id that is being restored.
	 * @param string $lockname The name of the lock.
	 */
	public static function append( $content, $backup_id, $lockname = '' ) {
		$search_by = empty( $lockname ) ? $backup_id : $backup_id . '-' . $lockname;
		$filename  = self::get_lock_filename( $search_by );

		if ( ! empty( $content ) && file_exists( $filename ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$lock_content = json_decode( file_get_contents( $filename ), true );
			$lock_content = empty( $lock_content ) ? array() : $lock_content;
			$lock_content = array_merge( $lock_content, $content );
			file_put_contents( $filename, wp_json_encode( $lock_content ) ); // phpcs:ignore
		}
	}

	/**
	 * Returns lock dir
	 *
	 * @return string
	 */
	public static function get_lock_dir() {
		return path_join( wp_upload_dir()['basedir'], self::UPLOADS_SUBDIR . '/locks' );
	}

	/**
	 * Creates lock dir if it doesn't exist
	 *
	 * @return string
	 */
	public static function check_dir() {
		$dir = self::get_lock_dir();
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		// Add empty index file for security.
		$index_file = trailingslashit( $dir ) . 'index.php';
		Fs::add_index_file( $index_file );

		return $dir;
	}

	/**
	 * Returns lock filename
	 *
	 * @param string $backup_id Backup id.
	 *
	 * @return string Full path to lock file
	 */
	public static function get_lock_filename( $backup_id ) {
		$dir = self::check_dir();

		$filename = path_join( $dir, sanitize_file_name( $backup_id . '.json' ) );

		return $filename;
	}

	/**
	 * Clears lock file
	 *
	 * @param string $backup_id Backup id.
	 */
	public static function clear( $backup_id = null ) {
		$filename = self::get_lock_filename( $backup_id );
		if ( file_exists( $filename ) ) {
			unlink( $filename ); //phpcs:ignore
		}
	}

	/**
	 * Remove lock dir
	 */
	public static function remove_lock_dir() {
		$dir = self::get_lock_dir();

		if ( ! file_exists( $dir ) ) {
			return;
		}

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $fileinfo ) {
			if ( $fileinfo->isDir() ) {
				rmdir( $fileinfo->getRealPath() ); //phpcs:ignore
			} else {
				unlink( $fileinfo->getRealPath() ); //phpcs:ignore
			}
		}

		rmdir( $dir ); //phpcs:ignore
	}
}