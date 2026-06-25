<?php // phpcs:ignore
/**
 * Scanning recommended DB tables exlusions.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Check;

use WPMUDEV\Snapshot4\Task;

/**
 * Tables Preflight Check class.
 */
class Tables extends Task {

	/**
	 * Tables Preflight check handler.
	 *
	 * @param array $args Preflight arguments.
	 *
	 * @return void
	 */
	public function apply( $args = array() ) {
		global $wpdb;
		$model = $args['model'];

		$db = esc_sql( $wpdb->dbname );

		$tables = $wpdb->get_results(//phpcs:ignore
			$wpdb->prepare(
				" SELECT table_name AS 'table_name', round(((data_length + index_length) / 1024 / 1024), 2) 'size'
		FROM information_schema.tables WHERE table_schema = %s",
				$db
			),
			ARRAY_A
		);

		$old_exclusions = get_site_option( 'snapshot_excluded_tables', array() );
		$large_tables   = array_filter(
			$tables,
			function ( $table ) use ( $old_exclusions ) {
				return $table['size'] > 50 && ! in_array( $table['table_name'], $old_exclusions, true );
			}
		);

		if ( ! empty( $large_tables ) ) {
			$model->set( 'database_check', $large_tables );
		}
	}
}