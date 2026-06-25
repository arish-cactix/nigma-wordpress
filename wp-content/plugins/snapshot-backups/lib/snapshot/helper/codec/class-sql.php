<?php // phpcs:ignore
/**
 * Snapshot codec: replace stuff in SQL exports
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper\Codec;

use WPMUDEV\Snapshot4\Helper\Codec;

/**
 * SQL replacer class
 */
class Sql extends Codec {

	/**
	 * Optional intermediate prefix
	 *
	 * @var string
	 */
	private $intermediate_prefix = '';

	/**
	 * Gets intermediate codec expansion
	 *
	 * Used in imports
	 *
	 * @param string $prefix Intermediate prefix to use in expansion.
	 *
	 * @return object WPMUDEV\Snapshot4\Helper\Codec\Sql instance.
	 */
	public static function get_intermediate( $prefix = '' ) {
		$me = new self();

		$me->intermediate_prefix = $prefix;
		return $me;
	}

	/**
	 * Gets a list of replacement pairs
	 *
	 * A replacement pair is represented like so:
	 * Context-dependent table name as a key, macro-prefixed table name as value.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		if ( ! empty( $this->replacements_list ) ) {
			return $this->replacements_list;
		}

		global $wpdb;
		$like   = $wpdb->esc_like( $wpdb->base_prefix ) . '%';
		$tables = $wpdb->get_col( //phpcs:ignore
			$wpdb->prepare(
			// @codingStandardsIgnoreLine Not preparing DB name
			'SHOW TABLES FROM `' . DB_NAME . '` LIKE %s', //@todo use %i supported from wp6.2
				$like
			)
		);

		// Also add whatever are the WP defaults.
		foreach ( $wpdb->tables as $rtbl ) {
			$tables[] = "{$wpdb->base_prefix}{$rtbl}";
		}
		foreach ( $wpdb->old_tables as $rtbl ) {
			$tables[] = "{$wpdb->base_prefix}{$rtbl}";
		}
		foreach ( $wpdb->global_tables as $rtbl ) {
			$tables[] = "{$wpdb->base_prefix}{$rtbl}";
		}
		foreach ( $wpdb->ms_global_tables as $rtbl ) {
			$tables[] = "{$wpdb->base_prefix}{$rtbl}";
		}
		$tables = array_values( array_unique( $tables ) );
		// End defaults stuffing.
		$result = array();

		foreach ( $tables as $table ) {
			$key = $table;
			if ( ! empty( $this->intermediate_prefix ) ) {
				// Yank out the prefixless table name.
				if ( substr( $table, 0, strlen( $wpdb->base_prefix ) ) === $wpdb->base_prefix ) {
					$prefixless = substr( $table, strlen( $wpdb->base_prefix ) );
				}
				$key = "{$this->intermediate_prefix}{$prefixless}";
			}
			$result[ $key ] = $table;
		}

		// Catch-all clause.
		if ( ! empty( $this->intermediate_prefix ) ) {
			$result[ "{$this->intermediate_prefix}" ] = $wpdb->base_prefix;
		}

		$this->replacements_list = $result;

		return $result;
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Purposefully single-task oriented - just process the subset of SQL
	 * statements actually used by the export process (drop|create|insert).
	 *
	 * Will match an entire line (one line per statement).
	 *
	 * @param string $original_string Original table name.
	 * @param string $value Optional table name with prefix replaced with a macro.
	 *
	 * @return string
	 */
	public function get_matcher( $original_string, $value = '' ) {
		$value = ! empty( $value )
			? preg_quote( $value, '/' )
			: preg_quote( $string, '/' );
		// @codingStandardsIgnoreStart
		return '^' .
			'(' .
				'DROP TABLE IF EXISTS' .
				'|' .
				'CREATE TABLE IF NOT EXISTS' .
				'|' .
				'INSERT INTO' .
				'|' .
				'CREATE OR REPLACE .*?VIEW' .
			')' .
			'\s*' .
			'(' .
				'`?' . $value .
			')' .
			'(.*)' .
		'$';
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Gets expansion replacement string
	 *
	 * @param string $name Original table name.
	 * @param string $value Process-dependent table name representation
	 *                      (macro-prefixed on export, original on import).
	 *
	 * @return string
	 */
	public function get_replacement( $name, $value ) {
		return '\1 `' . $value . '\3';
	}
}