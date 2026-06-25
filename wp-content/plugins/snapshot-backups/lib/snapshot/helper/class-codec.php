<?php // phpcs:ignore
/**
 * Snapshot replacers: codec parent abstraction
 *
 * All codec instances will inherit from this
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Codec parent class
 */
abstract class Codec {

	/**
	 * Optional intermediate prefix
	 *
	 * @var string
	 */
	protected $replacements_list = array();

	const ENCODE = 'encode';
	const DECODE = 'decode';

	/**
	 * Gets a list of replacement pairs
	 *
	 * A replacement pair is represented like so:
	 * Context name as a key, generalized representation as value.
	 *
	 * @return array
	 */
	abstract public function get_replacements_list();

	/**
	 * Gets a regex expression matcher string
	 *
	 * Will be used with the `m` modifier, in /-delimited regex.
	 *
	 * @param string $original_string Replacement context value as string.
	 * @param string $value Optional replacement original value string.
	 *
	 * @return string
	 */
	abstract public function get_matcher( $original_string, $value = '' );

	/**
	 * Gets expansion replacement string
	 *
	 * @param string $name Context value.
	 * @param string $value Replacement value.
	 *
	 * @return string
	 */
	abstract public function get_replacement( $name, $value );

	/**
	 * Checks whether we have a snapshot macro present in a string
	 *
	 * @param string $str String to check for macro presence.
	 *
	 * @return bool
	 */
	public static function has_snapshot_macro( $str ) {
		$macro_rx = '/' .
			preg_quote( '{{SNAPSHOT_', '/' ) .
			'\w+' .
			preg_quote( '}}', '/' ) .
		'/';
		return apply_filters(
			'snapshot_has_macro',
			(bool) preg_match( $macro_rx, $str ),
			$str
		);
	}

	/**
	 * Checks if the original value is present
	 *
	 * Codec implementation will not substitute with a value (and will remove
	 * the entire matcher from result) if the original is not present.
	 *
	 * Needs to be overridden for more complex codec situations, where the
	 * context is not a concrete value, but rather a context pointer (name),
	 * such as with defines, variables and such.
	 *
	 * @param string $original Original value context.
	 *
	 * @return bool
	 */
	public function is_original_present( $original ) {//@phpcs:ignore
		return true;
	}

	/**
	 * Gets the original value
	 *
	 * Original value is the macro replacement value in the current site context.
	 *
	 * Needs to be overridden for more complex codec situations, where the
	 * context is not a concrete value, but rather a context pointer (name),
	 * such as with defines, variables and such.
	 *
	 * @param string $original Original value context.
	 *
	 * @return mixed
	 */
	public function get_original_value( $original ) {
		return $original;
	}

	/**
	 * String transformation dispatcher method
	 *
	 * @param string $source String to transform.
	 * @param string $direction Whether to encode or decode (see class constants).
	 *
	 * @return string
	 */
	public function transform( $source = '', $direction = false ) {
		$direction = ! empty( $direction ) && in_array( $direction, array( self::ENCODE, self::DECODE ), true )
			? $direction
			: self::ENCODE;

		return self::ENCODE === $direction
			? $this->encode( $source )
			: $this->decode( $source );
	}

	/**
	 * Encodes the values
	 *
	 * Used in the export process.
	 * Converts all found context values into their generalized replacements.
	 *
	 * @param string $source Source string to process.
	 *
	 * @return string
	 */
	public function encode( $source = '' ) {
		// @TODO: Check if this is adding _too much_ of a strain in the process. Compare with previous test runs.
		$definitions = (array) apply_filters(
			// phpcs:ignore
			sprintf( 'snapshot_codec_%s_replacements_encode', $this->get_codec_type() ),
			$this->get_replacements_list()
		);
		foreach ( $definitions as $temp => $original ) {
			$rx    = $this->get_matcher( $original );
			$value = $this->get_replacement( $original, $temp );

			$source = preg_replace( "/{$rx}/m", $value, $source );
		}

		return $source;
	}

	/**
	 * Decodes the values
	 *
	 * Used in the import process.
	 * Converts all discovered generalized replacements into their concrete context values.
	 *
	 * @param string $source Source string to process.
	 *
	 * @return string
	 */
	public function decode( $source = '' ) {
		$definitions = (array) apply_filters(
			// phpcs:ignore
			sprintf( 'snapshot_codec_%s_replacements_decode', $this->get_codec_type() ),
			$this->get_replacements_list()
		);
		foreach ( $definitions as $original => $temp ) {
			$rx    = $this->get_matcher( $original, $temp );
			$value = '';
			if ( $this->is_original_present( $original ) ) {
				$current_value = apply_filters(
					// phpcs:ignore
					sprintf(
						'snapshot_codec_%s_%s_decode',
						$this->get_codec_type(),
						$this->get_safe_macro_name( $temp )
					),
					$this->get_original_value( $original )
				);

				$value = $this->get_replacement( $original, $current_value );
			}

			$source = preg_replace( "/{$rx}/m", $value, $source );
		}

		return $source;
	}

	/**
	 * Gets codec type
	 *
	 * Basically, strips off parent class (this class) from beginning of the
	 * codec class name.
	 *
	 * @return string
	 */
	public function get_codec_type() {
		$parent  = strtolower( get_class() );
		$current = strtolower( get_called_class() );
		return preg_replace( '/^' . preg_quote( $parent ) . '_?/', '', $current ); //phpcs:ignore
	}

	/**
	 * Gets safe macro name
	 *
	 * Lowercased macro representation, with alnum + (lo)dash.
	 * Also replaces initial snapshot with macro.
	 *
	 * @param string $macro Macro name.
	 *
	 * @return string
	 */
	public function get_safe_macro_name( $macro ) {
		$safe = trim( $macro, '{}' );
		$safe = preg_replace( '/^SNAPSHOT_/', 'macro_', $safe );
		$safe = preg_replace( '/[^-_a-z0-9]/i', '', $safe );

		return strtolower( $safe );
	}
}