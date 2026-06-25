<?php // phpcs:ignore
/**
 * Snapshot models: Update backup model
 *
 * Updates the given backup from the remote storage.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Backup;

use WPMUDEV\Snapshot4\Model;

/**
 * Backup progress model class
 */
class Update extends Model\Request {

	/**
	 * Getting info of specific backup request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'snapshots';

	/**
	 * Update constructor.
	 */
	public function __construct() {}

	/**
	 * Sends the API request to the endpoint.
	 *
	 * @param string $backup_id The backup_id we're going to update.
	 *
	 * @return string The HTML for the backup row.
	 */
	public function update_backup( $backup_id ) {
		if ( empty( $backup_id ) ) {
			return false;
		}

		$data = array(
			'description' => $this->get( 'description' ),
		);

		$method   = 'put';
		$path     = trailingslashit( $this->get_api_url() ) . $backup_id;
		$response = $this->request( $path, $data, $method );

		return $response;
	}
}