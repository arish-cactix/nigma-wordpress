<?php
/**
 * Directory iterator class.
 *
 * @package snapshot
 * @since 4.13.0
 */

namespace WPMUDEV\Snapshot4\Helper;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Iterables class
 */
class Iterables {
	/**
	 * Path to iterate through.
	 *
	 * @var string
	 */
	protected $path = '';

	/**
	 * Depth.
	 *
	 * @var integer
	 */
	protected $depth = 0;

	/**
	 * Dir_Iterables constructor.
	 *
	 * @param string $path Path for iteration.
	 */
	public function __construct( $path ) {
		$this->path = $path;
	}

	/**
	 * Iterate through the provided path to get files and directory.
	 *
	 * @return RecursiveIteratorIterator
	 */
	public function get_iterables() {
		$dir_iterator = new RecursiveDirectoryIterator( $this->path, FilesystemIterator::SKIP_DOTS );
		$iterables    = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );

		$iterables->setMaxDepth( $this->depth );

		return $iterables;
	}

	/**
	 * Checks if the directory is browsable.
	 *
	 * @return boolean
	 */
	public function browsable() {
		foreach ( $this->get_iterables() as $it ) {
			return $it->isFile() || $it->isDir();
		}
	}
}