<?php // phpcs:ignore
/**
 * Snapshot models: Fetching Zipstream of large files model
 *
 * Holds information for fetching the backup zipstream of chuck of file from the plugin to the service.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Backup\Zipstream;

use WPMUDEV\Snapshot4\Model;

/**
 * Fetching Zipstream of files model class
 */
class LargeFiles extends Model {

	/**
	 * Returns a name for the zipstream based on the current time.
	 *
	 * @return string
	 */
	public function name_zipstream() {
		$zipstream_name = date( 'files-YmdGis', time() ) . '.zip'; // phpcs:ignore

		return $zipstream_name;
	}
}