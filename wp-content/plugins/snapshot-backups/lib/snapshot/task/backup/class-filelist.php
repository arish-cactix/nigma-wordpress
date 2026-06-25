<?php // phpcs:ignore
/**
 * Filelist exchange between plugin and service
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Fs;
use WPMUDEV\Snapshot4\Helper\Settings;

/**
 * Filelist exchange task class
 */
class Filelist extends Task {

	const NOTICE_UNWRITABLE = 'snapshot_unwritable_file';
	const NOTICE_NOINFO     = 'snapshot_noinfo_file';

	const ERR_STRING_REQUEST_PARAMS = 'Request for file list was not successful';

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'ex_rt'             => 'intval',
		'exclusion_enabled' => 'boolval',
	);

	/**
	 * Runs over the site's files and returns all info to the controller.
	 *
	 * @param array $args Info about what time the file iteration started and its timelimit.
	 */
	public function apply( $args = array() ) {
		$model = $args['model'];

		$model->set( 'is_done', false );
		$this->get_files( $model );

		if ( empty( $model->get( 'paths_left' ) ) ) {
			// So we are done. Say so.
			$model->set( 'is_done', true );
		}
	}

	/**
	 * Runs a breadth-first iteration on all files and gathers the relevant info for each one.
	 *
	 * @param \WPMUDEV\Snapshot4\Model\Backup\Filelist $model Filelist instance.
	 */
	public function get_files( $model ) {
		// Cache settings so we don't have to make repetitive calls to the same function.
		$enable_log_verbose = Settings::get_filelist_log_verbose();

		$enable_log_verbose && Log::info( __( 'The "Filelist" task is started', 'snapshot' ) );

		include_once ABSPATH . 'wp-admin/includes/file.php';
		$root_path = Fs::get_root_path();
		$model->set( 'root_path', $root_path );

		$enable_log_verbose &&
			/* translators: %s - root path for filelist task */
			Log::info( sprintf( __( 'The "Filelist" task - root path: %s', 'snapshot' ), $root_path ) );

		$paths           = ( empty( $model->get( 'paths_left' ) ) ) ? array( $root_path ) : $model->get( 'paths_left' );
		$user_exclusions = ( $model->get( 'exclusion_enabled' ) ) ? get_site_option( 'snapshot_global_exclusions', array() ) : array();

		$model->set( 'excluded_files', $user_exclusions );

		$exclusions = new Model\Blacklist( $user_exclusions );

		while ( ! empty( $paths ) ) {

			$path = array_pop( $paths );

			$enable_log_verbose &&
				/* translators: %s - current path in filelist task */
				Log::info( sprintf( __( 'The "Filelist" task - current path: %s', 'snapshot' ), $path ) );

			// Skip ".." items.
			if ( preg_match( '/\.\.([\/\\\\]|$)/', $path ) ) {
				continue;
			}
			if ( $exclusions->is_excluded( $path ) ) {
				continue;
			}
			if ( 0 !== strpos( $path, $root_path ) ) {
				// Build the absolute path in case it's not the first iteration.
				$path = rtrim( $root_path, '/' ) . $path;
			}

			$contents = defined( 'GLOB_BRACE' )
				? glob( trailingslashit( $path ) . '{,.}[!.,!..]*', GLOB_BRACE )
				: glob( trailingslashit( $path ) . '[!.,!..]*' );

			$enable_log_verbose &&
				/* translators: %d - current number of files in filelist task */
				Log::info( sprintf( __( 'The "Filelist" task - number of files: %d', 'snapshot' ), count( $contents ) ) );

			foreach ( $contents as $item ) {
				$enable_log_verbose &&
					/* translators: %s - current file/dir item in filelist task */
					Log::info( sprintf( __( 'The "Filelist" task - current item: %s', 'snapshot' ), $item ) );

				$file = array();

				$sub_path = substr( $item, strlen( $root_path ) - 1 );
				if ( $exclusions->is_excluded( $sub_path ) ) {
					continue;
				} elseif ( is_file( $item ) ) {
					$file = ( is_readable( $item ) ) ? $this->get_file_info( $item ) : null;

					if ( apply_filters( 'wp_snapshot_unreadable_file', empty( $file ), $item ) ) {
						/* translators: %s - filename */
						Log::warning( sprintf( __( 'The %s file is not readable, so it is not included in the backup.', 'snapshot' ), $item ) );
						continue;
					}

					if ( ! apply_filters( 'wp_snapshot_writable_file', is_writable( $item ), $item ) ) { //phpcs:ignore
						// @TODO: Improve by improving logs about not writable files.
						/* translators: %s - filename */
						Log::warning( sprintf( __( 'The %s file is not writable. It has been included in the backup, but will not be able to be restored from Snapshot.', 'snapshot' ), $item ) );
					}

					$file['name'] = $this->relative_path( $item, $root_path );

					$model->add( 'files', $file );
				} elseif ( is_dir( $item ) ) {
					if ( ! in_array( $item, $paths, true ) ) {
						$paths[] = $this->relative_path( $item, $root_path );
					}
				} elseif ( is_link( $item ) ) {
					if ( ! in_array( $item, $paths, true ) ) {
						$original_path = realpath( readlink( $item ) );

						if ( is_file( $original_path ) ) {
							$file = ( is_readable( $original_path ) ) ? $this->get_file_info( $original_path ) : null;

							if ( apply_filters( 'wp_snapshot_unreadable_file', empty( $file ), $item ) ) {
								/* translators: %s - filename */
								Log::warning( sprintf( __( 'The %s file is not readable, so it is not included in the backup.', 'snapshot' ), $original_path ) );
								continue;
							}

							if ( ! apply_filters( 'wp_snapshot_writable_file', is_writable( $original_path ), $item ) ) { //phpcs:ignore
								// @TODO: Improve by improving logs about not writable files.
								/* translators: %s - filename */
								Log::warning( sprintf( __( 'The %s file is not writable. It has been included in the backup, but will not be able to be restored from Snapshot.', 'snapshot' ), $item ) );
							}

							$file['name'] = $original_path;

							$model->add( 'files', $file );
						} elseif ( is_dir( $original_path ) ) {
							$paths[] = $original_path;
						}//end if
					}//end if

					$contents = Fs::get_link_contents( $item );
				}//end if
			}//end foreach

			// If we have exceed the imposed time limit, lets pause the iteration here.
			if ( $model->has_exceeded_timelimit() ) {
				if ( $enable_log_verbose ) {
					$time_diff_str = number_format( microtime( true ) - $model->get( 'start_time' ), 2, '.', '' );
					/* translators: %s - exceeded timelimit in filelist task */
					Log::info( sprintf( __( 'The "Filelist" task - time limit exceeded: %s', 'snapshot' ), $time_diff_str ) );
				}
				break;
			}
		}//end while
			$model->set( 'paths_left', $paths );
	}

	/**
	 * Returns rel path of file/dir, relative to site root.
	 *
	 * @param string $item File's absolute path.
	 * @param string $root_path Site root.
	 *
	 * @return string
	 */
	public function relative_path( $item, $root_path ) {
		// Retrieve the relative to the site root path of the file.
		$pos = strpos( $item, $root_path );
		if ( 0 === $pos ) {
			return substr_replace( $item, '/', $pos, strlen( $root_path ) );
		}

		return $item;
	}

	/**
	 * Checks file health and returns as many info as it can.
	 *
	 * @param string $item The file to be investigated.
	 *
	 * @return mixed File info or false for failure.
	 */
	public function get_file_info( $item ) {
		$file          = array();
		$file['mtime'] = filemtime( $item );
		$file['size']  = filesize( $item );

		if ( empty( $file['mtime'] ) && empty( $file['size'] ) ) {
			return false;
		}

		return $file;
	}
}