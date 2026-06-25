<?php // phpcs:ignore
/**
 * Scanning files exlusions for recommendation.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Check;

use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Fs;

/**
 * Files Preflight Check class.
 */
class Files extends Task {

	/**
	 * List of all large files.
	 *
	 * @var array
	 */
	private $large_files = array();

	/**
	 * Root path of the filesystem.
	 *
	 * @var string
	 */
	private $root_path;

	/**
	 * Preflight model.
	 *
	 * @var Model\Preflight
	 */
	private $model;

	/**
	 * Offset of the root path.
	 *
	 * @var int
	 */
	private $root_path_offset;

	/**
	 * Exclusions model.
	 *
	 * @var Model\Blacklist
	 */
	private $exclusions;

	/**
	 * Required preflight parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'checking_stage' => 'sanitize_text_field',
	);

	/**
	 * Files Preflight check handler.
	 *
	 * @param array $args Preflight arguments.
	 *
	 * @return void
	 */
	public function apply( $args = array() ) {
		$this->model            = $args['model'];
		$this->root_path        = Fs::get_root_path();
		$this->root_path_offset = strlen( $this->root_path ) - 1;
		$paths                  = ( empty( $this->model->get( 'paths_left' ) ) ) ? array( $this->root_path ) : $this->model->get( 'paths_left' );

		$user_exclusions  = get_site_option( 'snapshot_global_exclusions', array() );
		$this->exclusions = new Model\Blacklist( $user_exclusions );

		$this->check_large_files( $paths, 100 * 1024 * 1024 );

		if ( ! empty( $this->large_files ) ) {
			$this->model->set(
				'files_check',
				array_merge( $this->model->get( 'files_check' ), $this->large_files )
			);
		}
	}

	/**
	 * Check for large files.
	 *
	 * @param array $paths          Paths to scan.
	 * @param int   $size_threshold Size threshold for large files.
	 * @return void
	 */
	public function check_large_files( $paths, $size_threshold ) {

		while ( ! empty( $paths ) ) {
			$path  = array_pop( $paths );
			$files = scandir( $path );

			foreach ( $files as $file ) {
				if ( '.' !== $file && '..' !== $file ) {
					$full_path     = $path . $file;
					$reffered_path = substr( $full_path, $this->root_path_offset );
					if ( ! $this->exclusions->is_excluded( $reffered_path ) ) {

						if ( is_dir( $full_path ) ) {
							array_push( $paths, trailingslashit( untrailingslashit( $full_path ) ) );
						} else {
							$file_size = filesize( $full_path );

							if ( $file_size > $size_threshold ) {
								$file_data = array(
									'path' => $reffered_path,
									'size' => round( $file_size / 1024 / 1024 ) . ' MB',
									'name' => $file,
								);
								array_push( $this->large_files, $file_data );
								Log::info( "Large File: $reffered_path ($file_size )\n" );
							}
						}
					}
				}//end if
			}//end foreach

			if ( $this->model->has_exceeded_timelimit() ) {
				$this->model->set( 'paths_left', $paths );
				Log::info( 'Files scanning time out ' . "\n" );

				return;
			}
		}//end while

		$this->model->set( 'paths_left', $paths );
	}
}