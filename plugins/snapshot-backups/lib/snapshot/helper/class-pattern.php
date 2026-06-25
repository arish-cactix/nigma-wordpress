<?php
/**
 * This class is responsible for handling of all the Directory explorer related feature.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Pattern class
 */
class Pattern {
	/**
	 * Stores the table.
	 *
	 * @var string
	 */
	public $table = '';

	/**
	 * Stores the complete file path.
	 *
	 * @var string
	 */
	public $file = '';

	/**
	 * Stores the pattern.
	 *
	 * @var string
	 */
	protected $pattern = '';

	/**
	 * Pattern constructor
	 *
	 * @param string $table Table file name.
	 * @param string $file File name.
	 */
	public function __construct( $table, $file ) {
		$this->table = $table;
		$this->file  = $file;
	}

	/**
	 * Builds the "INSERT INTO {$table}" pattern
	 *
	 * @return \WPMUDEV\Snapshot4\Helper\Pattern
	 */
	public function build_insert_into_pattern() {
		$table         = $this->table;
		$this->pattern = "/INSERT\sINTO\s`{$table}`/";

		return $this;
	}

	/**
	 * Find all the "INSERT INTO" statements from the file.
	 *
	 * @return array
	 */
	public function match(): array {
		$contents = Fs::get_file_contents( $this->file );
		$matches  = array();
		$count    = preg_match_all( $this->pattern, $contents, $matches );
		$contents = null;

		return compact( 'count', 'matches' );
	}
}