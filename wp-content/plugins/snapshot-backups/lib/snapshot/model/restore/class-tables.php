<?php // phpcs:ignore
/**
 * Snapshot table restore tasks model class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Restore;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Codec;
use WPMUDEV\Snapshot4\Helper\Lock;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Helper\Replacer;
use WPMUDEV\Snapshot4\Helper\Db;

/**
 * Table restore tasks model class
 */
class Tables extends Model {

	/**
	 * DB query error
	 *
	 * @var string
	 */
	private $error = '';

	/**
	 * Constructor
	 *
	 * @param string $backup_id Backup id.
	 */
	public function __construct( $backup_id ) {
		$this->set_data(
			array(
				'backup_id' => $backup_id,
			)
		);
	}

	/**
	 * Clears the last error
	 */
	public function clear_error() {
		global $wpdb;
		$wpdb->last_error = '';
	}

	/**
	 * Checks if we have a DB error
	 *
	 * @return bool
	 */
	public function has_error() {
		return ! empty(
			$this->get_error()
		);
	}

	/**
	 * Gets current DB error
	 *
	 * @return string
	 */
	public function get_error() {
		global $wpdb;
		return (string) $wpdb->last_error;
	}

	/**
	 * Returns string to be used when we had issues with db restoration.
	 *
	 * @return string
	 */
	public function get_tables_error_string() {
		return esc_html__( 'the db tables were being restored', 'snapshot' );
	}

	/**
	 * Figures out local table name for a given source table name
	 *
	 * @param string $prefix Optional prefix to use.
	 *
	 * @return string Local table name
	 */
	public function get_destination_table( $prefix = '' ) {
		global $wpdb;

		$source_table = $this->get( 'db_table' );

		$source_prefix = $wpdb->base_prefix;
		if ( empty( $source_prefix ) ) {
			return $source_table;
		}

		$destination_prefix = ! empty( $prefix ) ? $prefix : $wpdb->base_prefix;

		return preg_replace(
			'/^' . preg_quote( $source_prefix, '/' ) . '/',
			$destination_prefix,
			$source_table
		);
	}

	/**
	 * Actually imports a single statement.
	 *
	 * @param string $statement SQL string to import.
	 *
	 * @return bool|WP_Error true on success, WP_Error with what went wrong otherwise.
	 */
	public function import_statement( $statement ) {
		// Pre-processing is causing way more troubles than it actually should.
		// $statement = $this->preprocess_import_statement( $statement ); //Recheck in future.

		$statement = trim( $statement );

		// Maybe we need to rename the table if exists.
		$this->maybe_rename_table( $statement );

		// Import the statement.
		$row_id = $this->import_statement_insert( $statement );

		if ( is_wp_error( $row_id ) ) {
			return $row_id;
		}

		return true;
	}

	/**
	 * Renames the table if same name already exists in the database.
	 *
	 * @param string $statement SQL Statement.
	 *
	 * @return void
	 */
	public function maybe_rename_table( $statement ) {
		global $wpdb;

		$statement = trim( $statement );
		if ( ! (bool) preg_match( '/^(create table|create or replace)/i', $statement ) ) {
			return;
		}

		// Current table being processed.
		$current_table = $this->get( 'db_table' );

		if ( ! $current_table ) {
			// Extra layer of security.
			return;
		}

		$tables = $wpdb->get_col( //phpcs:ignore
			$wpdb->prepare(
				// @codingStandardsIgnoreLine Not preparing DB name
				'SHOW TABLES FROM `' . DB_NAME . '` LIKE %s', //@todo use %i supported from wp6.2
				$current_table
			)
		);

		if ( empty( $tables ) || ! in_array( $current_table, $tables, true ) ) {
			// No duplicate tables found in the database.
			return;
		}

		// Suffixed with "_old" for the table.
		$new_table = "old_{$current_table}";

		Log::info(
			sprintf(
				/* translators: %1$s - current table name, %2$s - new table name */
				__( 'Table %1$s already exists. Renaming it to %2$s', 'snapshot' ),
				$current_table,
				$new_table
			),
			array(),
			$this->get( 'backup_id' )
		);

		// Finally proceed with the renaming.
		$wpdb->query( //phpcs:ignore
			$wpdb->prepare(
				'RENAME TABLE %s TO %s',
				$current_table,
				$new_table
			)
		);
	}

	/**
	 * Preprocesses the import statement
	 *
	 * @param string $statement Statement to preprocess.
	 *
	 * @return string
	 */
	public function preprocess_import_statement( $statement ) {
		// First up, let's pre-process the file.
		$encoder = new Replacer\Strings( Codec::ENCODE );

		// We'll only be using the SQL query codec.
		$encoder->set_codec_list(
			array(
				Codec\Sql::get_intermediate( Task\Restore\Tables::PREFIX ),
			)
		);

		return $encoder->transform( $statement );
	}

	/**
	 * Inserts an import statement
	 *
	 * @param string $statement Statement to import.
	 *
	 * @return bool|WP_Error
	 */
	public function import_statement_insert( $statement ) {
		global $wpdb;
		// Force the insert ID to empty value.
		$wpdb->insert_id = false;

		if ( (bool) preg_match( '/^(create table|create or replace)/i', $statement ) ) {
			$this->import_statement_create( $statement );

			$this->add( 'create_statement', true );

			// No row ID on table creation.
			return false;
		}

		// 1) Do SQL query
		$result = $this->query_ignore( $statement );

		if ( false === $result ) {
			$this->add( 'db_errors', $this->get_error() );
			Log::info(
				sprintf( /* translators: %1$s - name of sql statement, %2$s - db error */
					__( 'Error in statement %1$s: The database said: %2$s', 'snapshot' ),
					$statement,
					$this->get_error()
				),
				array(),
				$this->get( 'backup_id' )
			);

		}

		// 2) Obtain insert ID, if any.
		$row_id = $wpdb->insert_id;

		return $row_id;
	}

	/**
	 * Runs query with ignored foreign key constraints
	 *
	 * @param string $statement Prepared SQL statement to run.
	 *
	 * @return mixed
	 */
	public function query_ignore( $statement ) {
		global $wpdb;

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' ); //phpcs:ignore
		$result = $wpdb->query( $statement ); //phpcs:ignore
		$error  = $this->get_error();
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );//phpcs:ignore

		$wpdb->last_error = $error;

		return $result;
	}

	/**
	 * Special-case handling for CREATE statements
	 *
	 * Tables can have dependencies as foreign keys, which is why
	 * we're processing them as special case.
	 *
	 * @param string $statement SQL CREATE statement.
	 *
	 * @return bool|WP_Error
	 */
	public function import_statement_create( $statement ) {
		/**
		 * WPDB instance.
		 *
		 * @var \wpdb
		 */
		global $wpdb;

		$dest_table     = $this->get_destination_table();
		$dest_tmp_table = $this->get_destination_table( Task\Restore\Tables::PREFIX );

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' ); //phpcs:ignore

		$wpdb->hide_errors();
		$result = $wpdb->query( $statement ); //phpcs:ignore
		$wpdb->show_errors();

		if ( false === $result ) {
			// CREATE statement failed, lets see if it's due to duplicate FK checks, because we can handle those cases.
			$last_error = $this->get_error();
			if ( false !== strpos( $last_error, 'errno: 121' ) || false !== stripos( $last_error, 'duplicate key' ) ) {
				// Failed on create statement because of duplicate FK checks. Lets drop the original table before trying to recreate it.
				$has_source = $wpdb->get_var( //phpcs:ignore
					$wpdb->prepare( 'SHOW TABLES LIKE %s', $dest_table )
				);
				if ( ! empty( $has_source ) ) {
					Log::warning(
						sprintf(
							/* translators: %1s - sql create statement, %2s - original db table */
							__( 'Table creation issue with %1$s - attempting to drop the original %2$s first', 'snapshot' ),
							$statement,
							$dest_table
						),
						array(),
						$this->get( 'backup_id' )
					);
					$wpdb->query( //phpcs:ignore
						'DROP TABLE ' . $dest_table //phpcs:ignore
					);
					Log::info(
						sprintf(
							/* translators: %1s - original table name, %2s - temporary table name  */
							__( 'Deleted the original %1$s table, let\'s retry to create the temp %2$s table.', 'snapshot' ),
							$dest_table,
							$dest_tmp_table
						),
						array(),
						$this->get( 'backup_id' )
					);
					// Re-try this.
					$result = $wpdb->query( $statement ); //phpcs:ignore
				}//end if
			}//end if
		}//end if

		if ( false === $result ) {
			Log::error(
				sprintf(
					/* translators: %1s - errored statement, %2s - db error  */
					__( 'Error in statement %1$s: The database said: %2$s', 'snapshot' ),
					$statement,
					$this->get_error()
				),
				array(),
				$this->get( 'backup_id' )
			);

			$this->errors[] = array(
				'create_table_error',
				/* translators: %s - Local File */
				sprintf( __( 'Error in statement %1$s: The database said: %2$s.', 'snapshot' ), $statement, $this->get_error() ),
			);
		} else {
			Log::info(
				sprintf(
					/* translators: %s - Temporary db */
					__( 'Created the temp %s table, so now we can import the actual data there.', 'snapshot' ),
					$dest_tmp_table
				),
				array(),
				$this->get( 'backup_id' )
			);
		}//end if

		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' ); //phpcs:ignore

		return $result;
	}

	/**
	 * Reads exported sql files to prepare restore of a table.
	 *
	 * @param array $db_table The table to be restored.
	 */
	public function read_db_file_chunked( $db_table ) {
		$table_file = $this->get_sql_file( $db_table );
		$fh         = fopen( $table_file, 'rb' ); // phpcs:ignore

		if ( false === $fh ) {
			$this->errors[] = array(
				'db_table_read',
				/* translators: %s - db table name */
				sprintf( __( 'Couldn\'t read the exported %s table in order to restore it.', 'snapshot' ), $db_table ),
			);
			return false;
		}

		$lock_contents = Lock::read( $this->get( 'backup_id' ) );
		$pointer       = isset( $lock_contents[ $db_table ] ) ? $lock_contents[ $db_table ]['pointer'] : 0;
		fseek( $fh, $pointer );

		$statements = $this->get_statements( $db_table, $pointer, self::get_lines_limit(), $fh );

		$table_status = array(
			'done'    => feof( $fh ),
			'pointer' => ftell( $fh ),
		);

		fclose( $fh ); //phpcs:ignore
		Lock::append( array( $db_table => $table_status ), $this->get( 'backup_id' ) );

		return $statements;
	}

	/**
	 * Gets lines limitation
	 *
	 * @return int
	 */
	public function get_lines_limit() {
		$limit = defined( 'SNAPSHOT4_DBSET_CHUNK_SIZE' ) && is_numeric( SNAPSHOT4_DBSET_CHUNK_SIZE )
			? intval( SNAPSHOT4_DBSET_CHUNK_SIZE )
			: 250;
		return (int) apply_filters( 'snapshot4_model_restore_db_lines_limit', $limit );
	}

	/**
	 * Gets exported tables's root
	 *
	 * @return string
	 */
	public function get_root() {
		return path_join( Model\Restore::get_intermediate_destination( $this->get( 'backup_id' ) ), 'sql' );
	}

	/**
	 * Gets exported tables's root
	 *
	 * @param string $table DB table.
	 *
	 * @return string
	 */
	public function get_sql_file( $table ) {
		return path_join( $this->get_root(), $table . '.sql' );
	}

	/**
	 * Gets a list of table names from SQL files list
	 *
	 * @return array
	 */
	public function get_table_names_from_files() {
		$tables = array();

		if ( empty( $tables ) ) {
			$tables = array();
			$source = trailingslashit( $this->get_root() );
			$raw    = glob( "{$source}*.sql" );
			foreach ( $raw as $file ) {
				$tables[] = pathinfo( $file, PATHINFO_FILENAME );
			}

			// Resolve table dependencies and options tables.
			$dependencies = array();
			$options      = array();
			$views        = array();
			$matcher      = '(' . join( '|', $tables ) . ')';
			foreach ( $tables as $tidx => $table ) {

				// Check if we're dealing with an options-like table first.
				if ( preg_match( '/(options|sitemeta)$/', $table ) ) {
					// We do.
					$options[] = $table;
					unset( $tables[ $tidx ] );
					// We'll be pushing that last anyway, so carry on.
					continue;
				}

				// For tables: statements = [DROP, CREATE].
				// For views:  statements = [CREATE OR REPLACE].
				$statements = $this->get_statements( $table, 0, 4 );
				if ( preg_match( '/create or replace/i', $statements[0] ) ) {
					$views[] = $table;
					unset( $tables[ $tidx ] );
					continue;
				}
				array_shift( $statements );

				if ( empty( $statements ) ) {
					continue;
				}

				if ( ! preg_match( '/create table/i', $statements[0] ) ) {
					continue;
				}

				if ( preg_match( "/(?!{$table}){$matcher}/", $statements[0] ) ) {
					$dependencies[] = $table;
					unset( $tables[ $tidx ] );
				}
			}//end foreach

			foreach ( $dependencies as $dep ) {
				$tables[] = $dep;
			}

			if ( ! empty( $options ) ) {
				foreach ( $options as $options_like_table ) {
					$tables[] = $options_like_table;
				}
			}

			foreach ( $views as $view ) {
				$tables[] = $view;
			}

			return $tables;
		}//end if
	}

	/**
	 * Extracts a number of statements to import from source
	 *
	 * @param resource $table The table to get statements from.
	 * @param int      $position Offset to start from.
	 * @param int      $limit Import at most this many statements.
	 * @param string   $fp_table File pointer of the table to get statements from.
	 *
	 * @return array Statements to import, as SQL strings
	 */
	public function get_statements( $table, $position, $limit, $fp_table = null ) {
		$close_when_done = false;
		$statements      = array();

		if ( empty( $fp_table ) ) {
			$close_when_done = true;
			$source          = path_join( $this->get_root(), $table . '.sql' );

			if ( ! file_exists( $source ) || ! is_readable( $source ) ) {
				$this->errors[] = array(
					'failed_get_statements',
					/* translators: %s - db table name */
					sprintf( __( 'Unable to read table %1$s data from %2$s', 'snapshot' ), $table, $source ),
				);
				return $statements;
			}

			$fp_table = fopen( $source, 'r' ); //phpcs:ignore
			if ( false === $fp_table ) {
				return false;
			}

			fseek( $fp_table, 0 );
		}

		$start = ftell( $fp_table );

		$delimiter_rx     = preg_quote( Model\Database::STATEMENT_DELIMITER, '/' );
		$count            = 0;
		$create_statement = false;
		while ( ( $line = fgets( $fp_table ) ) !== false ) { //phpcs:ignore
			$line = trim( $line );
			if ( empty( $line ) ) {
				// Nothing to do, empty line.
				continue;
			}

			if ( preg_match( "/{$delimiter_rx}/", $line ) ) {
				// We have the delimiter - increase the count, but don't include.
				// Had enough? We're done for now.
				if ( count( $statements ) >= $limit ) {
					$current_pos = ftell( $fp_table );
					$next_line   = trim( fgets( $fp_table ) );
					if ( ! empty( $next_line ) ) {
						fseek( $fp_table, $current_pos );
					}
					break;
				}
				continue;
			}

			if ( preg_match( '/^#/', $line ) ) {
				// Comment line - don't include.
				continue;
			}

			if ( preg_match( '/^\/\*\!/', $line ) ) {
				// SQL meta information skip those.
				continue;
			}

			// The meat part - add statement to the queue.
			if ( $count + $start >= $position ) {
				if ( false !== strpos( $line, 'DROP TABLE IF EXISTS', 0 ) ) {
					++$count;
					if ( ! isset( $statements[ $count ] ) ) {
						$statements[ $count ] = '';
					}
					$statements[ $count ] = $line;
				}

				if ( false !== strpos( $line, 'CREATE TABLE', 0 ) ) {
					++$count;
					$create_statement = true;
				}

				if ( $create_statement ) {
					if ( ! isset( $statements[ $count ] ) ) {
						$statements[ $count ] = '';
					}
					$statements[ $count ] .= $line;
					if ( ';' === mb_substr( $line, -1 ) ) {
						$create_statement = false;
					}
					continue;
				}

				if ( false !== strpos( $line, 'INSERT INTO', 0 ) ) {
					++$count;
					if ( ! isset( $statements[ $count ] ) ) {
						$statements[ $count ] = '';
					}
					// Transforms "INSERT INTO" to "INSERT IGNORE INTO" to avoid other issues.
					$line                 = str_replace( 'INSERT INTO', 'INSERT IGNORE INTO', $line );
					$statements[ $count ] = $line;
				}
			}//end if
		}//end while

		// At this point, we have somewhat randomly indexed statements.
		$statements = array_values( $statements );

		if ( true === $close_when_done ) {
			// Clean up after ourselves.
			fclose( $fp_table ); //phpcs:ignore
		}

		return $statements;
	}

	/**
	 * Performs the actions after we're done importing all data in a table.
	 *
	 * @param string $table The db table we're about to finalize importing for.
	 *
	 * @return array Statements to import, as SQL strings
	 */
	public function finalize_table_import( $table ) {
		// Since we're pre-processing all the table rename actions.
		// We might not need to post-process the stuffs beside unlinking the table sql file.
		unlink( $this->get_sql_file( $table ) ); //phpcs:ignore
		return;

		//phpcs:disable
		$src_table  = $this->get_destination_table( Task\Restore\Tables::PREFIX );
		$dest_table = $this->get_destination_table();

		$status = $this->rename_table( $src_table, $dest_table );
		if ( empty( $status ) ) {
			$this->errors[] = array(
				'failed_finalizing',
				/* translators: %s - db table name */
				sprintf( __( 'Error finalizing %1$s ( %2$s )', 'snapshot' ), $src_table, $dest_table ),
			);

			return;
		}

		Log::info(
			sprintf(
				/* translators: %s - table name */
				__( 'Table %s is restored.', 'snapshot' ),
				$dest_table
			),
			array(),
			$this->get( 'backup_id' )
		);
		//phpcs:enable
	}

	/**
	 * Renames a table
	 *
	 * @param string $src_table Source table name.
	 * @param string $dest_table New table name.
	 *
	 * @return bool
	 */
	public function rename_table( $src_table, $dest_table ) {
		global $wpdb;

		$have_dest = $wpdb->get_var( //phpcs:ignore
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $dest_table )
		);

		$dest_tmp_table = false;
		if ( ! empty( $have_dest ) ) {
			$dest_tmp_table = Model\Database::get_temporary_table_name( $dest_table );

			Log::info(
				sprintf(
					/* translators: %1s - destination table, %2s - temporary destination table  */
					__( 'Destination table %1$s exists, renaming it to %2$s first.', 'snapshot' ),
					$dest_table,
					$dest_tmp_table
				),
				array(),
				$this->get( 'backup_id' )
			);

			$status = $wpdb->query( //phpcs:ignore
				$wpdb->prepare(
					'RENAME TABLE %s TO %s',
					$dest_table,
					$dest_tmp_table
				)
			);

			if ( false === $status ) {
				$this->errors[] = array(
					'failed_finalizing',
					/* translators: %s - db table name */
					sprintf( __( 'Error moving destination table to temporary destination: %s', 'snapshot' ), $this->get_error() ),
				);

				return false;
			}
		}//end if

		Log::info(
			sprintf(
				/* translators: %1s - source table, %2s - destination table  */
				__( 'Move source table %1$s to destination: %2$s', 'snapshot' ),
				$src_table,
				$dest_table
			),
			array(),
			$this->get( 'backup_id' )
		);

		$status = true;
		if ( ! $dest_tmp_table ) {
			$status = $wpdb->query( //phpcs:ignore
				$wpdb->prepare(
					'RENAME TABLE %s TO %s',
					$src_table,
					$dest_table
				)
			);
		}

		if ( false === $status ) {
			$this->errors[] = array(
				'failed_finalizing',
				/* translators: %s - db table name */
				sprintf( __( 'Error renaming table %1$s to %2$s. The DB said: %3$s', 'snapshot' ), $src_table, $dest_table, $this->get_error() ),
			);

			return false;
		}

		if ( ! empty( $dest_tmp_table ) ) {
			// We still need to clean up!
			Log::info(
				sprintf(
					/* translators: %s - temporary destination table  */
					__( 'Clean up destination table: %s', 'snapshot' ),
					$dest_tmp_table
				),
				array(),
				$this->get( 'backup_id' )
			);

			$db_name    = Db::get_db_name();
			$table_type = $wpdb->get_var( $wpdb->prepare( 'SELECT table_type FROM information_schema.tables WHERE table_schema = %s AND table_name = %s', $db_name, $dest_tmp_table ) ); //phpcs:ignore

			if ( 'VIEW' === $table_type ) {
				$status = $this->query_ignore( "DROP VIEW {$dest_tmp_table}" );
			} else {
				$status = $this->query_ignore( "DROP TABLE {$dest_tmp_table}" );
			}
			if ( false === $status ) {
				Log::warning(
					sprintf(
						/* translators: %1s - temporary destination table, %2s - db error */
						__( 'Error removing intermediate backup table %1$s: %2$s', 'snapshot' ),
						$dest_tmp_table,
						$this->get_error()
					),
					array(),
					$this->get( 'backup_id' )
				);
			}
		}//end if

		return true;
	}

	/**
	 * Checks locks, for whether the given table is already imported.
	 *
	 * @param string $table The db table we're about to check.
	 *
	 * @return bool Whether still need to import the table or not.
	 */
	public function get_table_done( $table ) {
		$lock_contents = Lock::read( $this->get( 'backup_id' ) );
		if ( isset( $lock_contents[ $table ] ) ) {
			if ( true === $lock_contents[ $table ]['done'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether we should skip the table from import (eg. due to wrong prefix).
	 *
	 * @param string $table The db table we're about to check.
	 *
	 * @return bool Whether we're skipping this table or not.
	 */
	public function skip_table( $table ) {
		global $wpdb;

		$active_prefix = $wpdb->base_prefix;

		if ( 0 === strpos( $table, $active_prefix ) ) {
			return false;
		}

		$table_status = array(
			'done' => true,
		);
		Lock::append( array( $table => $table_status ), $this->get( 'backup_id' ) );

		$this->add( 'skipped_tables', $table );

		Log::warning(
			sprintf(
				/* translators: %s - db table name - db error */
				__( 'A db table was found with the wrong db prefix and wasn\'t imported: %s ', 'snapshot' ),
				$table
			),
			array(),
			$this->get( 'backup_id' )
		);

		unlink( $this->get_sql_file( $table ) ); //phpcs:ignore

		return true;
	}
}