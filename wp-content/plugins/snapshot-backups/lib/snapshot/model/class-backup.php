<?php // phpcs:ignore
/**
 * Snapshot backup tasks model abstraction class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Model;

/**
 * Backup tasks model abstraction class
 */
abstract class Backup extends Model {

	/**
	 * Constructor
	 *
	 * @param float $time_limit Time limit for the iteration.
	 * @param float $start_time Time the iteration started.
	 */
	public function __construct( $time_limit, $start_time ) {
		$this->populate( $time_limit, $start_time );
	}

	/**
	 * Initializes the data
	 *
	 * @param float $time_limit Time limit for the iteration.
	 * @param float $start_time Time the iteration started.
	 */
	public function populate( $time_limit, $start_time ) {
		$this->set_data(
			array(
				'time_limit' => $time_limit,
				'start_time' => $start_time,
			)
		);
	}

	/**
	 * Checks if current iteration has exceeded the given time limit.
	 *
	 * @return bool True if we have exceeded the time limit, false if we haven't.
	 */
	public function has_exceeded_timelimit() {
		$current_time = microtime( true );
		$time_diff    = number_format( $current_time - $this->get( 'start_time' ), 2 );

		return ! empty( $this->get( 'time_limit' ) ) && ( $time_diff > $this->get( 'time_limit' ) );
	}
}