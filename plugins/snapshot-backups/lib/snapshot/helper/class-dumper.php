<?php
/**
 * Class to help us with MySQL Dump
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Dumper class
 */
class Dumper {

	/**
	 * Connection variable
	 *
	 * @var object
	 */
	protected $connection = null;

	/**
	 * Dumper constructor.
	 *
	 * @param Connection $connection Connection instance.
	 */
	public function __construct( Connection $connection ) {
		$this->connection = $connection;
	}

	/**
	 * Appends the value to the connection string.
	 *
	 * @param string $con_string Connection string.
	 * @param string $value String to be concatenated.
	 * @return void
	 */
	public function append( &$con_string, $value ): void {
		if ( ! empty( $value ) ) {
			$con_string .= sprintf( ' %s', $value );
		}
	}

	/**
	 * Set the limit and offset for mysqldump
	 *
	 * @param string  $base_query Passed as reference.
	 * @param integer $offset Query offset.
	 * @param integer $limit  Limit the number of results.
	 * @return void
	 */
	public function set_limit( &$base_query, $offset, $limit = 1000 ): void {
		$base_query .= sprintf( ' --where="1 limit %d, %d"', $limit, $offset );
	}

	/**
	 * Writes the queries into the following file.
	 *
	 * @param string $string_to_write String to write.
	 * @param string $value File name.
	 * @return void
	 */
	public function write_to( &$string_to_write, $value ): void {
		$string_to_write .= sprintf( ' > %s', sanitize_text_field( $value ) );
	}

	/**
	 * Dumps the queries into the file.
	 *
	 * @param string $file Full path including the file for the queries to be dumped.
	 * @param array  $options Additional options.
	 *
	 * @return array
	 */
	public function dump( $file, $options = array() ): array {
		// Find the mysqldump command location and prepare it.
		$command = System::get_command( 'mysqldump' );

		// Make sure we're able to make the system call.
		if ( ! System::can_call_system() ) {
			return array();
		}

		// We're appending the database related credentials.
		$this->append( $command, $this->connection->to_string() );

		// We're disabling extended insert to make it compatible with PHP Script method.
		$this->append( $command, '--extended-insert=FALSE' );

		// Skip comments.
		$this->append( $command, '--skip-comments' );

		// Skip add locks.
		$this->append( $command, '--skip-add-locks' );

		// Skip charsets.
		$this->append( $command, '--skip-set-charset' );

		// Skip timezones.
		$this->append( $command, '--skip-tz-utc' );

		// Skip keys.
		$this->append( $command, '--skip-disable-keys' );

		if ( isset( $options['offset'] ) && abs( $options['offset'] ) > 0 ) {
			// We don't want to prepend the DROP & CREATE TABLE query.
			$this->append( $command, '--no-create-info' );
		}

		// We want to import the specific table.
		$this->append( $command, sanitize_text_field( $options['table'] ) );

		// Set the query offset and limit.
		$this->set_limit( $command, abs( $options['limit'] ), abs( $options['offset'] ) );

		// Use mysqldump to write the queries to a specific file.
		$this->write_to( $command, $file );

		// Make the system call.
		$result = System::call( $command );

		return compact( 'file', 'result' );
	}
}