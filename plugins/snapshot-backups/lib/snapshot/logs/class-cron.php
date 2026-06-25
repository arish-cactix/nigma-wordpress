<?php
/**
 * Cron controller
 *
 * @package Snapshot
 */

namespace WPMUDEV\Snapshot4\Logs;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Model\Log;

/**
 * Cron class.
 */
class Cron extends Controller {

	/**
	 * Boots up the controller
	 *
	 * @return void
	 */
	public function boot() {
		add_action( 'wp', array( $this, 'schedule_daily_cron' ) );
		add_action( 'snapshot_delete_old_user_action_logs', array( $this, 'trigger_delete_old_entries' ) );
	}

	/**
	 * Register the WP cron event.
	 *
	 * @return void
	 */
	public function schedule_daily_cron() {
		if ( ! wp_next_scheduled( 'snapshot_delete_old_user_action_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'snapshot_delete_old_user_action_logs' );
		}
	}

	/**
	 * Triggers deletion of older entries.
	 *
	 * @return void
	 */
	public function trigger_delete_old_entries() {
		$date = gmdate( 'Y-m-d H:i:s', strtotime( '-60 days' ) );

		$log = new Log();
		$log->delete_entries( $date );
	}
}