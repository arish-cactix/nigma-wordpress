<?php // phpcs:ignore
/**
 * Singleton abstraction class.
 *
 * Used with objects that need to be instantiated only once, such as the main class.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Singleton abstraction class
 */
abstract class Singleton {

	/**
	 * Instance unique identifier.
	 * Used for tests.
	 *
	 * @var int
	 */
	private $id = false;

	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->id = uniqid();
	}

	/**
	 * Instance obtaining method
	 *
	 * @return object Singleton instance
	 */
	public static function get() {
		static $instances  = array();
		$called_class_name = get_called_class();
		if ( ! isset( $instances[ $called_class_name ] ) ) {
			$instances[ $called_class_name ] = new $called_class_name();
		}
		return $instances[ $called_class_name ];
	}

	/**
	 * Gets instance unique identifier
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}
}