<?php
/**
 * User action logs model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

/**
 * This class is responsible for storing the user action logs
 */
class Log {
	/**
	 * WPDB instance
	 *
	 * @var \wpdb
	 */
	protected $db = null;

	/**
	 * Log constructor.
	 */
	public function __construct() {
		/**
		 * WPDB instance
		 *
		 * @var \wpdb
		 */
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Get all the entries
	 *
	 * @param array|null $args Args.
	 *
	 * @return array|object|null
	 */
	public function all( ?array $args = null ) {
		$defaults = array(
			'number'                 => 100,
			// Retrieve all logs.
							'offset' => 0,
			// Offset for pagination.
							'action' => null,
			// Filter by action.
							'order'  => 'DESC',
			'orderby'                => 'id',
		);

		$args = wp_parse_args( $args, $defaults );

		$orderby = 'date' === $args['orderby'] ? 'performed_at' : $args['orderby'];
		$number  = absint( $args['number'] );
		$offset  = absint( $args['offset'] );
		$order   = strtoupper( $args['order'] );

		$query = 'SELECT * FROM ' . $this->get_table();

		if ( ! is_null( $args['action'] ) ) {
			$query .= $this->db->prepare( ' WHERE action = %s', $args['action'] );
		}

		$query .= $this->db->prepare( " ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", $number, $offset );

		$results = $this->db->get_results( $query );

		return $results;
	}

	/**
	 * Create the log.
	 *
	 * @param array $data Data.
	 *
	 * @return void
	 */
	public function create( array $data ): void {
		$insert = array();

		$insert['user_id']      = $data['user_id'] ?? get_current_user_id();
		$insert['action']       = $data['action'];
		$insert['details']      = $data['details'] ?? '';
		$insert['performed_at'] = current_time( 'mysql' );

		$this->db->insert(
			$this->get_table(),
			$insert,
			array( '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Deletes entries
	 *
	 * @param string $date Date.
	 * @return void
	 */
	public function delete_entries( string $date = null ): void {
		$query = 'DELETE FROM ' . $this->get_table();

		if ( ! is_null( $date ) ) {
			$query .= $this->db->prepare( ' WHERE performed_at < %s', $date );
		}

		$this->db->query( $query );
	}

	/**
	 * Get the table name
	 *
	 * @return string
	 */
	private function get_table() {
		return $this->db->prefix . 'snapshot_action_logs';
	}
}