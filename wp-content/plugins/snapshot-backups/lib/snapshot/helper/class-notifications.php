<?php // phpcs:ignore

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Notifications class
 */
class Notifications {

	/**
	 * Stores the database key.
	 *
	 * @var string
	 */
	protected $key = 'snapshot_failed_backups_notification';

	/**
	 * Get all the elements in the stack.
	 *
	 * @return array
	 */
	public function all() {
		return get_site_option( $this->key, array() );
	}

	/**
	 * Total number of elements in the stack.
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->all() );
	}

	/**
	 * Checks if there is at least one element in the stack.
	 *
	 * @return boolean
	 */
	public function has_one() {
		return $this->count() === 1;
	}

	/**
	 * Returns the last data.
	 *
	 * @return array
	 */
	public function last() {
		$all = $this->all();
		if ( $this->count() > 1 ) {
			$last = end( $all );
			reset( $all );
		} else {
			$last = $all[0];
		}

		return $last;
	}

	/**
	 * Appends the last item to the stack.
	 *
	 * @param array $data Item to append.
	 * @return int Index of the appended data.
	 */
	public function push( array $data ) {
		$all   = $this->all();
		$all[] = $data;

		$count = count( $all );
		$this->update( $all );

		return $count - 1;
	}

	/**
	 * Clears the failed backups stack.
	 *
	 * @return bool
	 */
	public function clear() {
		return $this->update( array() );
	}

	/**
	 * Updates the failed backup stack.
	 *
	 * @param array $data Data to be updated.
	 * @return bool
	 */
	private function update( array $data ) {
		return update_site_option( $this->key, $data );
	}
}