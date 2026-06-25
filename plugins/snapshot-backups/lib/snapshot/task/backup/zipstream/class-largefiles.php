<?php // phpcs:ignore
/**
 * Fetching a backup zipstream of chuck of requested file from the plugin to the service.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup\Zipstream;

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Fs;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Settings;
use Wpmudev\ZipstreamDeflateChunk\ZipstreamChunk;

/**
 * Backup zipstream files task class
 */
class LargeFiles extends Task\Backup\Zipstream {
	/**
	 * Required request parameters, with their sanitization method
	 *
	 * @var array
	 */
	protected $required_params = array(
		'file'   => null,
		'offset' => 'intval',
		'length' => 'intval',
	);

	/**
	 * Runs over the requested large file and builds a zipstream out of it.
	 *
	 * @param array $args Task arguments.
	 */
	public function apply( $args = array() ) {
		require dirname( SNAPSHOT_PLUGIN_FILE ) . '/vendor/autoload.php';

		if ( ob_get_level() > 0 ) {
			$content = ob_get_clean();
			unset( $content );
		}

		$model = $args['model'];

		// Enable output of HTTP headers.
		$zipstream_options = new \ZipStream\Option\Archive();
		$zipstream_options->setSendHttpHeaders( true );
		if ( Settings::get_zipstream_flush_buffer() ) {
			$zipstream_options->setFlushOutput( true );
		}

		$this->zipstream_file_chunk( $model, $zipstream_options );
	}

	/**
	 * Runs over the requested large file and builds a zipstream out of it.
	 *
	 * @param \WPMUDEV\Snapshot4\Model\Backup\Zipstream\LargeFiles $model Largefiles model.
	 *
	 * @param \ZipStream\Option\Archive                            $options instance.
	 */
	public function zipstream_file_chunk( $model, $options ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';

		$zip     = new ZipstreamChunk( $model->name_zipstream(), $options );
		$file    = $model->get( 'file' );
		$offset  = $model->get( 'offset' );
		$length  = $model->get( 'length' );
		$encoded = $model->get( 'is_encoded' );
		$skip    = false;
		$done    = false;

		// If the request is encoded, decode it early.
		if ( (bool) $encoded ) {
			$file = $this->url_safe_base64_decode( $file );
		}

		$file_path = trailingslashit( Fs::get_root_path() ) . $file;

		if ( ! file_exists( $file_path ) || false !== strpos( $file, '..' ) ) {
			/* translators: %s - filename */
			Log::warning( sprintf( __( 'The requested %s file does not exist and is not included in the backup.', 'snapshot' ), $file ) );
			$skip = true;
		}

		if ( ! apply_filters( 'wp_snapshot_writable_file_to_zipstream', is_readable( $file_path ), $file_path ) ) {
			/* translators: %s - filename */
			Log::warning( sprintf( __( 'The requested %s file is not readable and can not be included in the backup.', 'snapshot' ), $file ) );
			$skip = true;
		}

		if ( $skip ) {
			$zip->addFile( 'manifest.txt', wp_json_encode( array( 'skip' => true ) ) );
			$zip->finish();
			return;
		}

		if ( defined( 'SNAPSHOT4_CHUNKED_ZIPSTREAMING_LARGE' ) && SNAPSHOT4_CHUNKED_ZIPSTREAMING_LARGE ) {
			$stream = fopen( $file_path, 'rb' ); // phpcs:ignore
			$zip->addFileFromStreamChunk( $file, $stream, null, $offset, $length );
			$done = $offset + $length >= filesize( $file_path );
			if ( is_resource( $stream ) ) {
				fclose( $stream );	// phpcs:ignore
			}
		} else {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$contents = file_get_contents( $file_path, false, null, $offset, $length ); // phpcs:ignore
			$done     = $offset + $length >= filesize( $file_path );
			$zip->addFile( $file, $contents );
		}

		$zip->addFile( 'manifest.txt', wp_json_encode( array( 'done' => $done ) ) );
		$zip->finish();
	}
}