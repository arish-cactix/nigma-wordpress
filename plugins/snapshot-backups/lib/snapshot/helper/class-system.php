<?php
/**
 * System helper class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Class:: System
 */
class System {

	/**
	 * Checks if a PHP function call is available.
	 *
	 * @param string $func Name of the function.
	 *
	 * @return bool
	 */
	public static function is_available( $func ): bool {
		static $available = array();

		if ( isset( $available[ $func ] ) ) {
			return (bool) $available[ $func ];
		}

		$status = false;

		if ( function_exists( $func ) ) {
			$disabled = sprintf(
				'%s,%s',
				ini_get( 'disable_functions' ),
				ini_get( 'suhosin.executor.func.blacklist' )
			);

			$status = ! in_array(
				$func,
				preg_split( '/,\s*/', $disabled ),
				true
			);
		}

		$available[ $func ] = $status;

		return (bool) $available[ $func ];
	}

	/**
	 * Checks if we can call the system binaries
	 *
	 * @return bool
	 */
	public static function can_call_system(): bool {
		return self::is_available( 'escapeshellcmd' ) && self::is_available( 'exec' );
	}

	/**
	 * Gets system command path.
	 *
	 * @param string $cmd Name of the command.
	 *
	 * @return string Empty on failure, path on success
	 */
	public static function get_command( $cmd ): string {
		if ( ! self::can_call_system() ) {
			return '';
		}

		$cmd = escapeshellcmd( $cmd );

		// We have already checked if system commands are available.
		$output = null;
		$retval = null;

		return exec( "command -v {$cmd}", $output, $retval );// @phpcs:ignore
	}

	/**
	 * Checks for the availability of the command.
	 *
	 * @param string $cmd Name of the command.
	 *
	 * @return bool
	 */
	public static function has_command( $cmd ): bool {
		$result = self::get_command( $cmd );

		return ! empty( $result );
	}

	/**
	 * Checks if we have access
	 *
	 * @return bool
	 */
	public static function has_access(): bool {
		return ( self::can_call_system() && self::has_command( 'mysqldump' ) );
	}

	/**
	 * Makes the call to the system.
	 *
	 * @param string $command Command to run.
	 * @return array
	 */
	public static function call( $command ): array {
		$status = null;
		$output = null;

		// Make the call to the system.
		exec( $command, $output, $status );// @phpcs:ignore
		if ( 2 === $status ) {
			$command = preg_replace( '/:[0-9]+ -u /', ' -u ', $command, 1 );
			exec( $command, $output, $status );// @phpcs:ignore
		}

		return compact( 'status', 'output' );
	}
}