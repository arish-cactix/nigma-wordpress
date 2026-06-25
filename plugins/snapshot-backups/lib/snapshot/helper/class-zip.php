<?php
/**
 * Zip helper class.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Zip helper class
 */
class Zip {

	const TYPE_ARCHIVE = 'archive';
	const TYPE_PCLZIP  = 'pclzip';

	/**
	 * Spawn a ZIP archive object
	 *
	 * If ZipArchive is available and no force_zip defines present, it sets it as the zip engine to use.
	 *
	 * @return object Snapshot_Helper_Zip_Abstract instance
	 */
	public static function get_object() {

		if ( defined( 'SNAPSHOT4_FORCE_ZIP_LIBRARY' ) && 'pclzip' === SNAPSHOT4_FORCE_ZIP_LIBRARY ) {
			return new Zip\Pcl();
		}

		if ( class_exists( 'ZipArchive' ) ) {
			return new Zip\Archive();
		} else {
			return new Zip\Pcl();
		}
	}

	/**
	 * Spawns and prepares a ZIP object instance
	 *
	 * This is how we get a ZIP archive handler ready to use
	 *
	 * @param string $path Full ZIP archive destination path.
	 * @param string $variation Optional ZIP variation to use.
	 *
	 * @return object Prepared ZIP object ready to use
	 */
	public static function get( $path, $variation = false ) {
		$instance = self::get_object( $variation );
		$instance->prepare( $path );
		return $instance;
	}
}