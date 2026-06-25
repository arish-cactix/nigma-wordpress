<?php
/**
 * Connection Helper class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Connection class
 */
class Connection {
	/**
	 * Stores the instance of this class.
	 *
	 * @var Connection
	 */
	protected static $instance = null;

	/**
	 * Stores the connection details.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Connection class constructor.
	 */
	private function __construct() {
		$dbuser     = defined( 'DB_USER' ) ? DB_USER : '';
		$dbpassword = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '';
		$dbname     = defined( 'DB_NAME' ) ? DB_NAME : '';
		$dbhost     = defined( 'DB_HOST' ) ? DB_HOST : '';

		$this->data = compact( 'dbuser', 'dbpassword', 'dbname', 'dbhost' );
	}

	/**
	 * Gets us the single instance of this class.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the connection info.
	 *
	 * @return array
	 */
	public function get_info(): array {
		return $this->data;
	}

	/**
	 * Get the connection string for the data.
	 *
	 * @return string
	 */
	public function to_string(): string {
		$info = $this->data;

		$connection = '';

		if ( isset( $info['dbhost'] ) ) {
			$connection .= sprintf( '-h %s', $info['dbhost'] );
		}

		if ( isset( $info['dbuser'] ) ) {
			$connection .= sprintf( ' -u %s', $info['dbuser'] );
		}

		if ( isset( $info['dbpassword'] ) ) {
			$connection .= sprintf( " -p'%s'", $info['dbpassword'] );
		}

		if ( isset( $info['dbname'] ) ) {
			$connection .= sprintf( ' %s', $info['dbname'] );
		}

		return $connection;
	}
}