<?php // phpcs:ignore
/**
 * Snapshot model abstraction class
 *
 * Snapshot models are units of data, with corresponding manipulation methods.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4;

/**
 * Model abstraction class
 */
abstract class Model {

	const SCOPE_DELIMITER = '::';

	/**
	 * Internal data storage reference
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Model errors
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Gets value from internal storage
	 *
	 * @param string $what Value key.
	 * @param mixed  $fallback Optional fallback.
	 *
	 * @return mixed Corresponding value, or fallback
	 */
	public function get( $what, $fallback = false ) {
		return isset( $this->data[ $what ] )
			? $this->data[ $what ]
			: $fallback;
	}

	/**
	 * Gets the whole internal data store
	 *
	 * @return array
	 */
	public function get_data() {
		return (array) $this->data;
	}

	/**
	 * Sets value to an internal storage key
	 *
	 * @param string $what Value key.
	 * @param mixed  $value Value to set.
	 *
	 * @return object Model instance
	 */
	public function set( $what, $value ) {
		$this->data[ $what ] = $value;
		return $this;
	}

	/**
	 * Checks if the value is set in the internal storage.
	 *
	 * @param string $what Key.
	 *
	 * @return boolean
	 */
	public function has( $what ) {
		return isset( $this->data[ $what ] );
	}

	/**
	 * Unsets the value from the internal storage.
	 *
	 * @param string $what key.
	 *
	 * @return boolen
	 */
	public function unset( $what ) {
		if ( $this->has( $what ) ) {
			unset( $this->data[ $what ] );
			return true;
		}
		return false;
	}

	/**
	 * Sets all of the internal storage in one go
	 *
	 * @param array $values Values to replace storage with.
	 *
	 * @return object Model instance
	 */
	public function set_data( $values ) {
		if ( is_array( $values ) ) {
			$this->data = $values;
		}
		return $this;
	}

	/**
	 * Adds element to data array
	 *
	 * @param string $what Value key.
	 * @param string $value Value to add.
	 *
	 * @return object Model instance
	 */
	public function add( $what, $value ) {
		$this->data[ $what ][] = $value;
		return $this;
	}

	/**
	 * Adds errors to task
	 *
	 * @param Task $task Task instance.
	 * @return bool
	 */
	public function add_errors( Task $task ) {
		if ( count( $this->errors ) ) {
			foreach ( $this->errors as $error ) {
				call_user_func_array( array( $task, 'add_error' ), $error );
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if task as one or more errors
	 *
	 * @return boolean
	 */
	public function has_errors() {
		return count( $this->errors ) > 0;
	}
}