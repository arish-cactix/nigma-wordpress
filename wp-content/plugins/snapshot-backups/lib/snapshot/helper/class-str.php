<?php // phpcs:ignore
/**
 * String helper class.
 *
 * We can make use of this class to perform different string related manipulations.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * String helper class
 */
class Str {

	/**
	 * Mask the email address.
	 *
	 * @param string $email Email address to mask.
	 *
	 * @return string
	 */
	public static function mask_email( string $email ): string {
		$email_parts    = explode( '@', $email );
		$email_parts[0] = substr( $email_parts[0], 0, 2 ) . str_repeat( '*', max( 2, strlen( $email_parts[0] ) - 2 ) );
		return implode( '@', $email_parts );
	}
}