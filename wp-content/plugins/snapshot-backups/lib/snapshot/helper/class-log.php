<?php // phpcs:ignore
/**
 * Snapshot helpers: log helper class
 *
 * Does logging-related work - writing to log, reading from log, etc.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

use WPMUDEV\Snapshot4\Controller;

/**
 * Log helper class
 */
class Log {

	const ERROR   = 'error';
	const WARNING = 'warning';
	const NOTICE  = 'notice';
	const DEBUG   = 'debug';
	const INFO    = 'info';

	const DEFAULT_TIMEZONE = 'UTC';

	const DATETIME_FORMAT = 'Y-m-d H:i:s P';

	const UPLOADS_SUBDIR = 'snapshot-backups';

	const NONCE = 'snapshot_download_log';

	/**
	 * Current backup or last id
	 *
	 * @var string
	 */
	private static $backup_id;

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param string $level Log level Example: error | warning | notice | debug | info.
	 * @param string $message Message.
	 * @param array  $context Context or any extra params.
	 * @param string $backup_id Backup id.
	 */
	private static function log( $level, $message, array $context = array(), $backup_id = null ) {
		$datetime = new \DateTime( 'now', wp_timezone() );

		if ( $message instanceof \Exception ) {
			$exception = $message;
			$message   = 'Exception: ' . $exception->getMessage();

			$context['exception_class'] = get_class( $exception );

			$context['file'] = $exception->getFile();
			$context['line'] = $exception->getLine();
			$context['code'] = $exception->getCode();
		}

		$lines   = array();
		$lines[] = $datetime->format( self::DATETIME_FORMAT ) . " [$level] $message";
		if ( count( $context ) ) {
			$lines[] = wp_json_encode( $context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		}

		$filename = self::get_log_filename( $backup_id );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		file_put_contents( $filename, implode( "\n", $lines ) . "\n\n", FILE_APPEND ); //phpcs:ignore

		if ( defined( 'WP_CLI' ) && WP_CLI && self::INFO !== $level ) {
			\WP_CLI::log( implode( "\n", $lines ) );
		}
	}

	/**
	 * Set backup id
	 *
	 * @param string $backup_id Current backup id.
	 */
	public static function set_backup_id( $backup_id ) {
		$is_empty = empty( self::$backup_id );

		if ( $is_empty ) {
			$contents = self::get_contents( '' );
			if ( '' !== $contents ) {
				self::clear( '' );
			} else {
				$is_empty = false;
			}
		}

		self::$backup_id = $backup_id;

		$filename = self::get_log_filename();
		if ( $is_empty && ! file_exists( $filename ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			file_put_contents( $filename, $contents ); //phpcs:ignore
		}
	}

	/**
	 * Returns current backup id
	 *
	 * @return string|null
	 */
	public static function get_backup_id() {
		self::check_backup_id();
		return self::$backup_id;
	}

	/**
	 * Check and current backup id
	 */
	private static function check_backup_id() {
		if ( empty( self::$backup_id ) ) {
			$backup_running = get_site_option( 'snapshot_running_backup' );
			$backup_id      = ( isset( $backup_running['id'] ) && 'manual' !== $backup_running['id'] )
				? $backup_running['id'] : null;
			if ( ! is_null( $backup_id ) ) {
				self::set_backup_id( $backup_id );
			}
		}
	}

	/**
	 * Returns log dir
	 *
	 * @return string
	 */
	public static function get_log_dir() {
		return path_join( wp_upload_dir()['basedir'], self::UPLOADS_SUBDIR );
	}

	/**
	 * Creates log dir if it doesn't exist
	 *
	 * @param bool $check_index Check and create index.php.
	 *
	 * @return string
	 */
	public static function check_dir( $check_index = false ) {
		$dir = self::get_log_dir();
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		if ( $check_index ) {
			// Add empty index file for security.
			$index_file = trailingslashit( $dir ) . 'index.php';
			Fs::add_index_file( $index_file );
		}

		return $dir;
	}

	/**
	 * Checks if the log directory exists.
	 *
	 * @return bool True if the log directory exists, false otherwise.
	 */
	public static function has_log_dir(): bool {
		return Fs::dir_exists( self::get_log_dir() );
	}

	/**
	 * Returns log filename
	 *
	 * @param string $backup_id Backup id.
	 * @param bool   $return_url Return URL instead of file path.
	 *
	 * @return string Full path to log file
	 */
	public static function get_log_filename( $backup_id = null, $return_url = false ) {
		$name = 'snapshot';
		// Conceal filename.
		$hash  = hash_hmac( 'sha1', $name, sha1( DB_PASSWORD ) );
		$name .= '-' . $hash;

		if ( is_null( $backup_id ) ) {
			self::check_backup_id();
			$backup_id = self::$backup_id;

			if ( ! $backup_id ) {
				// If no running backup, lets take the id of the latest one.
				$backup_id = get_site_option( Controller\Ajax\Backup::SNAPSHOT_LATEST_BACKUP );
			}
		}
		if ( $backup_id ) {
			$name .= '-' . $backup_id;
		}

		$dir = self::check_dir();

		$filename = path_join( $dir, sanitize_file_name( $name . '.log' ) );

		if ( $return_url ) {
			return admin_url( 'admin-ajax.php' ) . '?' . http_build_query(
				array(
					'action'    => 'snapshot-download_log',
					'backup_id' => $backup_id,
					'_wpnonce'  => wp_create_nonce( self::NONCE ),
				)
			);
		} else {
			return $filename;
		}
	}

	/**
	 * Returns log URL
	 *
	 * @param string $backup_id Backup id.
	 */
	public static function get_log_url( $backup_id = null ) {
		return self::get_log_filename( $backup_id, true );
	}

	/**
	 * Returns backup ids for available logs
	 *
	 * @return array
	 */
	public static function get_backup_ids() {
		$log_filename = self::get_log_filename( '' );
		$result       = array();

		$pattern = preg_replace( '/\.log$/u', '-*.log', $log_filename );
		$files   = glob( $pattern );
		foreach ( $files as $file ) {
			$matches = array();
			if ( preg_match( '/\-([0-9a-f]{12})\.log$/ui', $file, $matches ) ) {
				$result[] = $matches[1];
			}
		}

		return $result;
	}

	/**
	 * Enhanced parse log file
	 *
	 * It helps to parse log file along with load-more pagination for larger logs.
	 *
	 * @since 4.3.5
	 *
	 * @param string  $backup_id Backup id.
	 * @param integer $position  Offset where the reading starts on log file.
	 * @param integer $page      Page number for the log to load.
	 *
	 * @return array
	 */
	public static function parse_log_file_enhanced( $backup_id, $position = 0, $page = 1 ) {
		$contents = self::get_contents( $backup_id, $position );
		$result   = array();

		if ( empty( trim( $contents ) ) ) {
			return $result;
		}

		$size              = $position + strlen( $contents );
		$exploded_contents = array_reverse( explode( "\n\n", $contents ) );
		$total_lines       = count( $exploded_contents );
		$lines_to_load     = ( $total_lines <= 100 ) ? $total_lines : 100;

		// Prepares the pagination variables depending on the number of log entries.
		if ( $total_lines > 100 ) {
			$pages = ceil( $total_lines / $lines_to_load );
			if ( $page <= 0 || $page > $pages ) {
				return $result;
			}

			$offset      = ( 1 === $page ? $page : $page - 1 ) * $lines_to_load;
			$start       = 1 === $page ? $page : $offset + 1;
			$stop_line   = 1 === $page ? $lines_to_load : ( ( $offset + $lines_to_load ) < $total_lines ? $offset + $lines_to_load : $total_lines );
			$upper_bound = $total_lines > $stop_line ? $stop_line : $total_lines;
		} else {
			$start       = 1;
			$upper_bound = $total_lines;
		}

		$result['size'] = $size;

		$items = array();
		for ( $i = $start - 1; $i < $upper_bound; $i++ ) {
			$lines = trim( $exploded_contents[ $i ] );

			if ( '' === $lines ) {
				--$total_lines;
				continue;
			}

			// If we exceed the upper bound in the log file, break.
			if ( $total_lines > 100 && $i > $upper_bound ) {
				break;
			}

			$lines     = explode( "\n", $lines );
			$last_line = end( $lines );
			$context   = json_decode( $last_line, true );

			if ( $context ) {
				array_pop( $lines );
			}

			$level     = null;
			$timestamp = 0;

			$matches = array();
			if ( preg_match( '/^([0-9 \-:\+]+) \[(.+)\] (.+)/u', $lines[0], $matches ) ) {
				$level     = $matches[2];
				$dt        = \DateTime::createFromFormat( 'Y-m-d H:i:s P', $matches[1] );
				$timestamp = $dt->getTimestamp();
				$lines[0]  = "[$level]" . ' ' . $dt->format( 'Y-m-d H:i:s' ) . ' ' . $matches[3];
			}

			if ( ! in_array( $level, array( 'error', 'info', 'warning' ), true ) ) {
				$level = 'default';
			}

			if ( $timestamp ) {
				$items[] = array(
					'message'   => implode( "\n", $lines ),
					'level'     => $level,
					'timestamp' => $timestamp,
				);
			}
		}//end for

		if ( $total_lines > 100 && $page !== $pages ) {
			// Include pagination options to display the "Load More" button.
			$result['pages']        = $pages;
			$result['current_page'] = $page;
			$result['next_page']    = ( $page + 1 > $pages ) ? $pages : $page + 1;
		} else {
			$result['pages'] = false;
		}

		if ( ! empty( $items ) ) {
			$result['items'] = $items;
		}

		return $result;
	}

	/**
	 * Parse log file
	 *
	 * @param string $backup_id Backup id.
	 * @param int    $offset    Offset where the reading starts on log file.
	 * @param bool   $reverse   Reverse order.
	 * @return array
	 */
	public static function parse_log_file( $backup_id, $offset = 0, $reverse = true ) {
		$contents = self::get_contents( $backup_id, $offset );
		$size     = $offset + strlen( $contents );

		$result = array(
			'size' => $size,
		);

		$items = array();
		foreach ( explode( "\n\n", $contents ) as $lines ) {
			$lines = trim( $lines );
			if ( '' === $lines ) {
				continue;
			}
			$lines     = explode( "\n", $lines );
			$last_line = end( $lines );
			$context   = json_decode( $last_line, true );
			if ( $context ) {
				array_pop( $lines );
			}

			$level     = null;
			$timestamp = 0;

			$matches = array();
			if ( preg_match( '/^([0-9 \-:\+]+) \[(.+)\] (.+)/u', $lines[0], $matches ) ) {
				$level     = $matches[2];
				$dt        = \DateTime::createFromFormat( self::DATETIME_FORMAT, $matches[1] );
				$timestamp = $dt->getTimestamp();
				$lines[0]  = "[$level]" . ' ' . Datetime::format( $timestamp, 'Y-m-d H:i:s' ) . ' ' . $matches[3];
			}

			if ( ! in_array( $level, array( 'error', 'warning', 'info' ), true ) ) {
				$level = 'default';
			}

			if ( $timestamp ) {
				$items[] = array(
					'message'   => implode( "\n", $lines ),
					'level'     => $level,
					'timestamp' => $timestamp,
				);
			}
		}//end foreach

		$result['items'] = $reverse ? array_reverse( $items ) : $items;

		return $result;
	}

	/**
	 * Returns log contents
	 *
	 * @param string $backup_id Backup id.
	 * @param int    $offset    Offset where the reading starts on log file.
	 *
	 * @return string
	 */
	public static function get_contents( $backup_id = null, $offset = 0 ) {
		$filename = self::get_log_filename( $backup_id );
		if ( file_exists( $filename ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			return file_get_contents( $filename, false, null, $offset );
		}

		return '';
	}

	/**
	 * Clears log file
	 *
	 * @param string $backup_id Backup id.
	 */
	public static function clear( $backup_id = null ) {
		$filename = self::get_log_filename( $backup_id );
		if ( file_exists( $filename ) ) {
			unlink( $filename ); //phpcs:ignore
		}
	}

	/**
	 * Remove log dir
	 *
	 * @param bool $logs_only Removes logs only flag.
	 */
	public static function remove_log_dir( $logs_only = false ) {
		$dir = self::get_log_dir();

		if ( ! file_exists( $dir ) ) {
			return;
		}

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $fileinfo ) {
			if ( $fileinfo->isDir() ) {
				if ( ! $logs_only ) {
					rmdir( $fileinfo->getRealPath() ); //phpcs:ignore
				}
			} elseif ( ! $logs_only || ( $logs_only && 'log' === $fileinfo->getExtension() ) ) {
					unlink( $fileinfo->getRealPath() ); //phpcs:ignore
			}
		}

		if ( ! $logs_only ) {
			rmdir( $dir ); //phpcs:ignore
		}
	}

	/**
	 * Outputs log file
	 *
	 * @param string $backup_id Backup id.
	 */
	public static function output_log( $backup_id = null ) {
		$filename = self::get_log_filename( $backup_id );
		if ( file_exists( $filename ) ) {
			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename=' . basename( $filename ) );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Pragma: public' );
			header( 'Content-Length: ' . filesize( $filename ) );

			readfile( $filename ); // phpcs:ignore
		} else {
			http_response_code( 404 );
		}
		flush();
		exit;
	}

	/**
	 * Log message with "info" level
	 *
	 * @param string $message Message.
	 * @param array  $context Context or any extra params.
	 * @param string $backup_id Backup id.
	 */
	public static function info( $message, array $context = array(), $backup_id = null ) {
		self::log( self::INFO, $message, $context, $backup_id );
	}

	/**
	 * Log message with "debug" level
	 *
	 * @param string $message Message.
	 * @param array  $context Context or any extra params.
	 * @param string $backup_id Backup id.
	 */
	public static function debug( $message, array $context = array(), $backup_id = null ) {
		self::log( self::DEBUG, $message, $context, $backup_id );
	}

	/**
	 * Log message with "notice" level
	 *
	 * @param string $message Message.
	 * @param array  $context Context or any extra params.
	 * @param string $backup_id Backup id.
	 */
	public static function notice( $message, array $context = array(), $backup_id = null ) {
		self::log( self::NOTICE, $message, $context, $backup_id );
	}

	/**
	 * Log message with "warning" level
	 *
	 * @param string $message Message.
	 * @param array  $context Context or any extra params.
	 * @param string $backup_id Backup id.
	 */
	public static function warning( $message, array $context = array(), $backup_id = null ) {
		self::log( self::WARNING, $message, $context, $backup_id );
	}

	/**
	 * Log message with "error" level
	 *
	 * @param string $message Message.
	 * @param array  $context Context or any extra params.
	 * @param string $backup_id Backup id.
	 */
	public static function error( $message, array $context = array(), $backup_id = null ) {
		self::log( self::ERROR, $message, $context, $backup_id );
	}
}