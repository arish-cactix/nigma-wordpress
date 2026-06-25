<?php // phpcs:ignore
/**
 * Zipstream reusable tasks.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Task\Backup;

use WPMUDEV\Snapshot4\Task;

/**
 * Zipstream task abstraction class
 */
abstract class Zipstream extends Task\Backup {

	/**
	 * Decode the base64 encoded string in url safe way.
	 *
	 * @param string $file Base64 encoded string.
	 *
	 * @return string
	 */
	public function url_safe_base64_decode( $file ) {
		$remainder = strlen( $file ) % 4;

		if ( $remainder ) {
			$padlen = 4 - $remainder;
			$file  .= str_repeat( '=', $padlen );
		}

		return base64_decode( strtr( $file, '-_,', '+/=' ) ); //@phpcs:ignore
	}
}