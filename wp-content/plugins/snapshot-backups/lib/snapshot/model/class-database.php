<?php // phpcs:ignore
/**
 * Snapshot models: Database model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Database model class
 */
class Database extends Model {
	const STATEMENT_DELIMITER       = 'end_snapshot_statement';
	const MAX_SQL_TABLE_NAME_LENGTH = 64;

	/**
	 * Gets a statement delimiter string
	 *
	 * This delimiter is later used in import, to break up the
	 * importing work into smaller, more manageable chunks.
	 *
	 * @return string
	 */
	public function get_statement_delimiter() {
		return "\n" .
			'# ' .
			join( '', array_fill( 0, 10, '-' ) ) .
			' ' .
			self::STATEMENT_DELIMITER .
			' ' .
			join( '', array_fill( 0, 10, '-' ) ) .
		"\n";
	}

	/**
	 * Gets a temporary table name, used in renaming
	 *
	 * @param string $src Source table name.
	 *
	 * @return string
	 */
	public static function get_temporary_table_name( $src ) {
		$random_suffix        = '_tmp';
		$renamed_table_length = strlen( $src . $random_suffix );

		if ( $renamed_table_length >= self::MAX_SQL_TABLE_NAME_LENGTH ) {
			// Ensure table name uniqueness for very long tables.
			$src = substr( $src, 0, ( self::MAX_SQL_TABLE_NAME_LENGTH - 9 ) );
		}
		return $src . $random_suffix;
	}
}