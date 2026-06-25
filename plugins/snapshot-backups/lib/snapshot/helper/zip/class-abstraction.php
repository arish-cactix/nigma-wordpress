<?php // phpcs:ignore
/**
 * Snapshot zip helper abstraction class.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper\Zip;

use WPMUDEV\Snapshot4\Helper\Fs;

/**
 * Zip abstract class
 */
abstract class Abstraction {

	/**
	 * Zip class
	 *
	 * @var string
	 */
	protected $_zip; // phpcs:ignore

	/**
	 * Zip path
	 *
	 * @var string
	 */
	protected $_path; // phpcs:ignore

	/**
	 * Zip root path
	 *
	 * @var string
	 */
	private $_root_path; // phpcs:ignore

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->_root_path = Fs::get_root_path();
	}

	/**
	 * Sets root
	 *
	 * @param string $root Root path.
	 */
	public function set_root( $root ) {
		if ( empty( $root ) ) {
			return false;
		}
		$this->_root_path = trailingslashit( wp_normalize_path( $root ) );
	}

	/**
	 * Initializes.
	 */
	abstract public function initialize();

	/**
	 * Extract files from prepared archive
	 *
	 * @param string $destination Destination path to extract to.
	 *
	 * @return bool
	 */
	abstract public function extract( $destination );

	/**
	 * Extract specific files from prepared archive
	 *
	 * @param string $destination Destination path to extract to.
	 * @param array  $files Specific list of files.
	 *
	 * @return bool
	 */
	abstract public function extract_specific( $destination, $files );

	/**
	 * Extract backup zip in chunks
	 *
	 * @param string $destination Path to extract.
	 * @param string $backup_id ID of backup being restored.
	 * @return bool|string
	 */
	abstract public function extract_in_chunks( $destination, $backup_id );

	/**
	 * Whether or not a file is in the archive
	 *
	 * @param string $file File path, full or relative (will be converted).
	 *
	 * @return bool
	 */
	abstract public function has( $file );

	/**
	 * Prepares
	 *
	 * @param string $path File path.
	 */
	public function prepare( $path ) {
		$this->_path = $path;
		$this->initialize();
	}

	/**
	 * Prepares
	 *
	 * @param string $file File path.
	 * @param string $relative_path Relative file path.
	 */
	protected function _to_root_relative( $file, $relative_path = false ) { // phpcs:ignore
		$file = wp_normalize_path( $file );
		$root = $this->_get_root_path();

		$rel = ! empty( $relative_path )
			? trailingslashit( wp_normalize_path( $relative_path ) )
			: '';

		return preg_replace( '/^' . preg_quote( $root, '/' ) . '/i', $rel, $file );
	}

	protected function _get_root_path () { // phpcs:ignore
		return $this->_root_path;
	}
}