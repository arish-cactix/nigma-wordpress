<?php // phpcs:ignore
/**
 * System readiness check.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Check;

use WPMUDEV\Snapshot4\Task;

/**
 * System Preflight Check class.
 */
class System extends Task {

	/**
	 * List of all system checks.
	 *
	 * @var array
	 */
	protected $cheks = array(
		'memory_limit',
		'max_execution_time',
	);

	/**
	 * System Preflight check handler.
	 *
	 * @param array $args Preflight arguments.
	 *
	 * @return void
	 */
	public function apply( $args = array() ) {
		$model = $args['model'];

		foreach ( $this->cheks as $system_check ) {
			call_user_func( array( $this, 'check_' . $system_check ), $model );
		}
	}

	/**
	 * Check server memory.
	 *
	 * @param WPMUDEV\Snapshot4\Model\Preflight $model Preflight model.
	 * @return void
	 */
	public function check_memory_limit( $model ) {
		$mem_limit = intval( ini_get( 'memory_limit' ) );

		if ( 0 !== $mem_limit && $mem_limit < 256 ) {
			$model->add( 'system_check', 'memory_limit' );
		}
	}

	/**
	 * Check max execution time.
	 *
	 * @param WPMUDEV\Snapshot4\Model\Preflight $model Preflight model.
	 * @return void
	 */
	public function check_max_execution_time( $model ) {
		$max_execution_time = intval( ini_get( 'max_execution_time' ) );

		if ( 0 !== $max_execution_time && $max_execution_time < 60 ) {
			$model->add( 'system_check', 'max_execution_time' );
		}
	}
}