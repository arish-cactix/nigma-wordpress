<?php // phpcs:ignore
/**
 * Snapshot helpers: strings replacer
 *
 * Handles low level strings replacement transformations.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper\Replacer;

use WPMUDEV\Snapshot4\Helper\Replacer;

/**
 * String replacer class
 */
class Strings extends Replacer {

	/**
	 * Applies migration transformations to a string
	 *
	 * @param string $source Source string to process.
	 *
	 * @return string
	 */
	public function transform( $source ) {
		if ( ! is_string( $source ) ) {
			// Can't deal with this, pass through.
			return $source;
		}
		$xforms = $this->get_codec_list();

		foreach ( $xforms as $codec ) {
			$source = $codec->transform( $source, $this->get_direction() );
		}

		return $source;
	}
}