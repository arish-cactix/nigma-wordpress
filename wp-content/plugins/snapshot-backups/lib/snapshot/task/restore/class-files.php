<?php // phpcs:ignore
/**
 * Restore files task.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Restore;

use WPMUDEV\Snapshot4\Helper\Fs;
use WPMUDEV\Snapshot4\Helper\Lock;
use WPMUDEV\Snapshot4\Model\Env;
use WPMUDEV\Snapshot4\Model\Restore;
use WPMUDEV\Snapshot4\Task;

/**
 * Restore files task class
 */
class Files extends Task {

	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'backup_id' => null,
	// backup_id has already been sanitised in json_process_restore().
	);

	/**
	 * Restores files.
	 *
	 * @param array $args Restore arguments, like backup_id and rootpath.
	 */
	public function apply( $args = array() ) {
		/**
		 * Files model
		 *
		 * @var \WPMUDEV\Snapshot4\Model\Restore\Files
		 */
		$model = $args['model'];

		$last_files_run = $model->get( 'last_files_run' );
		$exported_root  = Restore::get_intermediate_destination( $args['backup_id'] );
		$source         = $last_files_run ? $model->get_last_files_root() : $model->get_root();
		$destination    = Fs::get_root_path();
		$last_files     = array();
		$lock_content   = Lock::read( $model->get( 'backup_id' ) );

		if ( ! $last_files_run && ( ! isset( $lock_content['extracted_index'] ) || $lock_content['extracted_index'] >= 0 ) ) {
			$model->extract_backup( $exported_root, true );
			if ( $model->add_errors( $this ) ) {
				return;
			}

			return;

		}

		$file_items = $model->get_files();
		if ( ! is_array( $file_items ) ) {
			$file_items = array();
		}

		// Store where we left off, for the next file iteration.
		$key_paths = $last_files_run ? $model::KEY_LAST_PATHS : $model::KEY_PATHS;
		update_site_option( $key_paths, $model->get( 'paths_left' ) );

		$skip_wp_config = apply_filters( 'snapshot4_restoration_skip_config_file', Env::is_wpmu_staging() );
		$skipped_lists  = apply_filters( 'snapshot4_restoration_skipped_file_lists', array() );

		foreach ( $file_items as $item ) {
			$filepath = preg_replace( '/^' . preg_quote( $source, '/' ) . '/i', '', $item );
			if ( $skip_wp_config && '/wp-config.php' === $filepath ) {
				$model->add( 'skipped_files', $filepath );
				continue;
			}

			if ( ! empty( $skipped_lists ) && in_array( $filepath, $skipped_lists, true ) ) {
				$model->add( 'skipped_files', $filepath );
				continue;
			}

			$path     = trim( wp_normalize_path( dirname( $filepath ) ), '/' );
			$fullpath = trailingslashit( wp_normalize_path( "{$destination}{$path}" ) );

			if ( ! is_dir( $fullpath ) ) {
				wp_mkdir_p( $fullpath );
			}

			$dest_file = $fullpath . basename( $item );

			// If file is a W3 Total Cache one (non-plugin file, to be created at wp-content), move it at the end of the restoration (essentially make sure the actual plugin is already restored).
			if ( ! $last_files_run && $model->check_if_w3tc_file( $dest_file ) ) {
				$last_files[] = $item;

				continue;
			}

			if ( ! rename( $item, $dest_file ) ) { //phpcs:ignore
				$error_code = 'failed_file_move';
				/* translators: %1s - temp file path, %2s - restored file path */
				$error_message = sprintf( __( 'Couldn\'t move the temp %1$1s file to its restored path: %2$2s.', 'snapshot' ), $item, $dest_file );
				$this->add_error( $error_code, $error_message );

				$model->add( 'skipped_files', $dest_file );
			}
		}//end foreach

		if ( ! $last_files_run ) {
			// Now deal with the files that need to be restored last.
			$last_files_destination = $model->get_last_files_root();
			if ( ! empty( $last_files ) ) {
				$model->set( 'need_last_run', true );
				// Place those files in the appropriate folder, so that we can restore them right after we finish with aaaall the other files.
				if ( ! is_dir( $last_files_destination ) ) {
					wp_mkdir_p( $last_files_destination );
				}
				$last_files_destination = trailingslashit( $last_files_destination );

				foreach ( $last_files as $last_file ) {
					$last_filepath = preg_replace( '/^' . preg_quote( $source, '/' ) . '/i', '', $last_file );
					$last_path     = trim( wp_normalize_path( dirname( $last_filepath ) ), '/' );
					$last_fullpath = trailingslashit( wp_normalize_path( "{$last_files_destination}{$last_path}" ) );

					if ( ! is_dir( $last_fullpath ) ) {
						wp_mkdir_p( $last_fullpath );
					}
					$dest_lastfile = $last_fullpath . basename( $last_file );

					if ( ! rename( $last_file, $dest_lastfile ) ) { //phpcs:ignore
						$error_code = 'failed_file_move';
						/* translators: %1s - temp file path, %2s - restored file path */
						$error_message = sprintf( __( 'Couldn\'t move the temp %1$1s file to its restored path: %2$2s.', 'snapshot' ), $last_file, $dest_lastfile );
						$this->add_error( $error_code, $error_message );

						$model->add( 'skipped_files', $dest_lastfile );
					}
				}
			}//end if
		} elseif ( ! $model->get( 'is_done' ) ) {
			// If we are on the _last files_ run *and* have restored all residuals, then we dont have to do another run.
				$model->set( 'need_last_run', true );
		}//end if
	}
}