<?php // phpcs:ignore
/**
 * Snapshot models: filesystem path exclusions model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Helper\Fs;
use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Log;

/**
 * Blacklist model class
 */
class Blacklist extends Model {

	const SNAPSHOT_EXCLUDE_LARGE = 'snapshot_exclude_large';

	/**
	 * Holds a list of blacklisted files.
	 *
	 * These have to be a partial match to be omitted.
	 *
	 * @var array
	 */
	private $files = array();

	/**
	 * Holds a list of blacklisted large files.
	 *
	 * @var array
	 */
	private $large_files = array();

	/**
	 * Holds a list of blacklisted directories.
	 *
	 * These need to be a partial match to be omitted.
	 *
	 * @var array
	 */
	private $dirs = array();

	/**
	 * Holds a list of blacklisted large directories.
	 *
	 * @var array
	 */
	private $large_dirs = array();

	/**
	 * Constructor
	 * Also sets up default exclusions list
	 *
	 * @param array $user_exclusions File exclusions.
	 */
	public function __construct( $user_exclusions ) {
		$exclude_large = get_site_option( self::SNAPSHOT_EXCLUDE_LARGE, true );

		$this->large_files = array(
			trailingslashit( ABSPATH ) . 'error_log',
			trailingslashit( WP_CONTENT_DIR ) . 'debug.log',
			trailingslashit( WP_CONTENT_DIR ) . 'uploads/wp-defender/defender.log',
		);
		$this->large_dirs  = array(
			trailingslashit( ABSPATH ) . 'wp-snapshots/',
			trailingslashit( WP_CONTENT_DIR ) . 'backups-dup-lite/',
			trailingslashit( WP_CONTENT_DIR ) . 'cache/',
			trailingslashit( WP_CONTENT_DIR ) . 'et-cache/',
			trailingslashit( WP_CONTENT_DIR ) . 'wphb-cache/',
			trailingslashit( WP_CONTENT_DIR ) . 'wphb-logs/',
			trailingslashit( WP_CONTENT_DIR ) . 'updraft/',
			trailingslashit( WP_CONTENT_DIR ) . 'ai1wm-backups/',
			trailingslashit( WP_CONTENT_DIR ) . 'uploads/shipper/',
			trailingslashit( WP_CONTENT_DIR ) . 'uploads/snapshot/',
			trailingslashit( WP_CONTENT_DIR ) . 'uploads/snapshots/',
		);

		$this->files = $this->get_default_file_exclusions( $exclude_large );
		$this->dirs  = $this->get_default_directory_exclusions( $user_exclusions, $exclude_large );
	}

	/**
	 * Gets a list of directory full paths
	 *
	 * @return array
	 */
	public function get_directories() {
		return (array) $this->dirs;
	}

	/**
	 * Gets a list of file exclusions (full paths)
	 *
	 * @return array
	 */
	public function get_files() {
		return (array) $this->files;
	}

	/**
	 * Gets the file exclusions that should be there always
	 *
	 * @param bool $exclude_large Whether the user has seclected to exclude large folders/files.
	 *
	 * @return array
	 */
	public function get_default_file_exclusions( $exclude_large ) {
		$exclusions = array(

			// Snapshot specific.
			Model\Backup\Zipstream\Tables::get_temp_sql_filename(),

			// WP Engine specific!
			trailingslashit( WP_CONTENT_DIR ) . 'mysql.sql',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/mu-plugin.php',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/slt-force-strong-passwords.php',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/stop-long-comments.php',

			// GoDaddy specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/gd-system-plugin.php',

			// Kinsta specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/kinsta-mu-plugins.php',

			// Flywheel specific.
			trailingslashit( ABSPATH ) . '.fw-config.php',

			// EasyWP specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wp-nc-easywp.php',

			// Bluehost specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/endurance-browser-cache.php',

			// iThemes specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/cs-cache-enabler.php',
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/cs-filters-and-actions.php',

			// WPMU DEV specific.
			trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpmudev-hosting.php',

		);
		if ( Model\Env::is_wpmu_hosting() ) {
			// WPMU DEV hosting object-cache.php because it doesn't do much checks.
			$exclusions[] = trailingslashit( WP_CONTENT_DIR ) . 'object-cache.php';
		}

		if ( ! empty( $exclude_large ) ) {
			$exclusions = array_merge( $exclusions, $this->large_files );
		}

		return $exclusions;
	}

	/**
	 * Gets the directory exclusions that should be there always
	 *
	 * @param array $user_exclusions File exclusions selected by the user.
	 * @param bool  $exclude_large   Whether the user has seclected to exclude large folders/files.
	 *
	 * @return array
	 */
	public function get_default_directory_exclusions( array $user_exclusions, $exclude_large ) {
		$lists = array_merge(
			$user_exclusions,
			array(

				// VCS files.
				'/.git/',
				'/.svn/',

				// Snapshot specific.
				Log::get_log_dir() . '/',

				// Ourselves too.
				trailingslashit( dirname( SNAPSHOT_PLUGIN_FILE ) ),

				// Well-known.
				trailingslashit( ABSPATH ) . '.well-known/',

				// WP Engine specific!
				trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/force-strong-passwords/',
				trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpengine-common/',

				// SiteGround-specific.
				trailingslashit( WP_CONTENT_DIR ) . 'plugins/sg-cachepress/',

				// GoDaddy-specific.
				trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/gd-system-plugin/',

				// Kinsta specific.
				trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/kinsta-mu-plugins/',

				// EasyWP specific.
				trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wp-nc-easywp/',

				// iThemes specific.
				trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/cs-cache-enabler/',

				// WPMU DEV specific.
				trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins/wpmudev-hosting/',
			)
		);
		$lists = array_filter( $lists );

		if ( ! empty( $exclude_large ) ) {
			$lists = array_unique( array_merge( $lists, $this->large_dirs ) );
		}

		return $lists;
	}

	/**
	 * Add a directory to the exclusion list
	 *
	 * @param string $path Full path to the excluded directory.
	 */
	public function add_directory( $path ) {
		$this->dirs[] = wp_normalize_path( $path );
	}

	/**
	 * Add a file to the exclusion list
	 *
	 * @param string $path Full path to the excluded file.
	 */
	public function add_file( $path ) {
		$this->files[] = wp_normalize_path( $path );
	}

	/**
	 * Checks to see whether a path is in an excluded directory
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function is_in_directory( $path ) {
		$path             = trailingslashit( wp_normalize_path( $path ) );
		$root_path        = Fs::get_root_path();
		$root_path_offset = strlen( $root_path ) - 1;

		foreach ( $this->get_directories() as $exclusion ) {
			$exclusion = trailingslashit( wp_normalize_path( ( stripos( $exclusion, $root_path ) !== false ) ? substr( $exclusion, $root_path_offset ) : $exclusion ) );
			if ( $exclusion === $path ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks to see whether a path matches an excluded file.
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function is_excluded_file( $path ) {
		$path             = trailingslashit( wp_normalize_path( $path ) );
		$root_path        = Fs::get_root_path();
		$root_path_offset = strlen( $root_path ) - 1;

		foreach ( $this->get_files() as $file ) {
			$file = trailingslashit( substr( $file, $root_path_offset ) );
			if ( $path === $file ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether a path is excluded.
	 *
	 * This can be either because it's in the excluded directory,
	 * or because it is a directly excluded path.
	 *
	 * @param string $path Path to check.
	 *
	 * @return bool
	 */
	public function is_excluded( $path ) {
		if ( $this->is_in_directory( $path ) ) {
			return true;
		}

		return $this->is_excluded_file( $path );
	}

	/**
	 * Get all the files & folders exclusion
	 *
	 * @return array
	 */
	public function get_all_exclusions() {
		return array_unique( array_merge( $this->large_dirs, $this->dirs, $this->large_files, $this->files ) );
	}
}