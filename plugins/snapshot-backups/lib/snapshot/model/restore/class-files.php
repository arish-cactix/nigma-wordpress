<?php // phpcs:ignore
/**
 * Snapshot file restore tasks model class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Restore;

use WPMUDEV\Snapshot4\Helper\Lock;
use WPMUDEV\Snapshot4\Helper\Zip;
use WPMUDEV\Snapshot4\Model;

/**
 * File restore tasks model class
 */
class Files extends Model {
	const KEY_PATHS      = 'snapshot4_restore_key_paths';
	const KEY_LAST_PATHS = 'snapshot4_restore_key_last_paths';

	/**
	 * Constructor
	 *
	 * @param string $backup_id Backup id.
	 */
	public function __construct( $backup_id ) {
		$this->set_data(
			array(
				'backup_id' => $backup_id,
			)
		);
	}

	/**
	 * Returns string to be used when we had issues with file restoration.
	 *
	 * @return string
	 */
	public function get_files_error_string() {
		return esc_html__( 'the files were being restored', 'snapshot' );
	}

	/**
	 * Extracts downloaded backup to the temp dir
	 *
	 * @param string $destination The path where the zip will be extracted.
	 * @param string $chunk_enabled Flag to extract in chunks.
	 */
	public function extract_backup( $destination, $chunk_enabled ) {
		wp_mkdir_p( $destination );
		$backup_id = $this->get( 'backup_id' );

		$zip_loc = path_join( Lock::get_lock_dir(), $backup_id . '/' . $backup_id . '.zip' );
		$zip     = Zip::get( $zip_loc );
		if ( $chunk_enabled ) {
			$extract_status = $zip->extract_in_chunks( $destination, $backup_id );
			if ( $extract_status && 'done' !== $extract_status ) {
				$this->set( 'is_done', false );
				return;
			}
		} else {
			// @todo remove simple extraction if not needed further after extraction in chunks.
			$extract_status = $zip->extract( $destination );
		}

		if ( ( $extract_status && ! $chunk_enabled ) || ( $chunk_enabled && 'done' === $extract_status ) ) {
			unlink( $zip_loc ); //phpcs:ignore

			return;
		}

		$this->errors[] = array(
			'initial_extract_error',
			__( 'Couldn\'t extract the downloaded backup zip in order to restore.', 'snapshot' ),
		);
	}

	/**
	 * Gets files gathered this far, or loads the next batch.
	 *
	 * @return array List of files to be restored.
	 */
	public function get_files() {
		if ( $this->get( 'is_done', false ) ) {
			return $this->get( 'files' );
		}

		$last_files_run = $this->get( 'last_files_run' );
		$processed      = 0;
		$limit          = $this->get_paths_limit();
		$limit_files    = $limit * 6;

		$root  = $last_files_run ? $this->get_last_files_root() : $this->get_root();
		$paths = $last_files_run ? get_site_option( self::KEY_LAST_PATHS, array( $root ) ) : get_site_option( self::KEY_PATHS, array( $root ) );
		while ( ! empty( $paths ) ) {
			$path = array_pop( $paths );
			++$processed;

			$contents = defined( 'GLOB_BRACE' )
				? glob( trailingslashit( $path ) . '{,.}[!.,!..]*', GLOB_BRACE )
				: glob( trailingslashit( $path ) . '[!.,!..]*' );

			foreach ( $contents as $item ) {
				if ( false !== strpos( $item, 'plugins/snapshot-backups' ) ) {
					// Skip snapshot plugin because it might break the restoration.
					$this->add( 'skipped_files', $item );
					continue;
				}

				if ( is_file( $item ) && ! is_link( $item ) ) {
					$this->add( 'files', $item );
				} elseif ( is_dir( $item ) && ! is_link( $item ) ) {
					if ( ! in_array( $item, $paths, true ) ) {
						$paths[] = $item;
					}
				}
			}

			$this->set( 'paths_left', $paths );

			if ( count( $this->get( 'files', array() ) ) >= $limit_files ) {
				break;
			}

			if ( $processed >= $limit ) {
				break;
			}
		}//end while

		$paths = $this->get( 'paths_left' );
		if ( empty( $paths ) ) {
			// So we are done. Say so.
			$this->set( 'is_done', true );
		}

		return $this->get( 'files' );
	}

	/**
	 * Gets paths limitation
	 *
	 * @return int
	 */
	public function get_paths_limit() {
		$limit = defined( 'SNAPSHOT4_FILESET_CHUNK_SIZE' ) && is_numeric( SNAPSHOT4_FILESET_CHUNK_SIZE )
			? intval( SNAPSHOT4_FILESET_CHUNK_SIZE )
			: 250;
		return (int) apply_filters( 'snapshot4_model_restore_files_paths_limit', $limit );
	}

	/**
	 * Gets exported files's root
	 *
	 * @return string
	 */
	public function get_root() {
		return path_join( Model\Restore::get_intermediate_destination( $this->get( 'backup_id' ) ), 'www' );
	}

	/**
	 * Gets dir of the folder where we'll be placing the files to be restored at the very end of the file restoration.
	 *
	 * @return string
	 */
	public function get_last_files_root() {
		return path_join( Model\Restore::get_intermediate_destination( $this->get( 'backup_id' ) ), 'last_files' );
	}

	/**
	 * Checks if file is possible to be one of the files W3 Total Cache installs in the wp-content.
	 *
	 * @param string $file_path File path.
	 *
	 * @return bool
	 */
	public function check_if_w3tc_file( $file_path ) {
		$advanced_cache = WP_CONTENT_DIR . '/advanced-cache.php';
		$db             = WP_CONTENT_DIR . '/db.php';
		$object_cache   = WP_CONTENT_DIR . '/object-cache.php';

		if ( 0 === strcmp( $advanced_cache, $file_path ) || 0 === strcmp( $db, $file_path ) || 0 === strcmp( $object_cache, $file_path ) ) {
			return true;
		}

		return false;
	}
}