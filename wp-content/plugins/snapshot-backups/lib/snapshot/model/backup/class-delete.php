<?php
/**
 * Snapshot models: Delete backup model
 *
 * Deletes the given backup from the remote storage.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Backup;

use WPMUDEV\Snapshot4\Model;

/**
 * Backup progress model class.
 */
class Delete extends Model\Request {

	/**
	 * Getting info of specific backup request endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = 'snapshots';

	/**
	 * Delete constructor.
	 *
	 * @param string $backup The backup_id we're going to delete.
	 */
	public function __construct( $backup ) {
		$this->set( 'backup_id', $backup );
	}

	/**
	 * Build running backup info for displaying.
	 *
	 * @param string $backup_id The backup_id we're going to delete.
	 *
	 * @return bool|void
	 */
	public function delete_backup( $backup_id ) {
		if ( empty( $backup_id ) ) {
			return false;
		}

		$data   = array();
		$method = 'delete';
		$path   = trailingslashit( $this->get_api_url() ) . $backup_id;

		$this->request( $path, $data, $method );
	}

	/**
	 * Returns action string for logger.
	 *
	 * @return string
	 */
	protected function get_action_string() {
		return __( 'delete a snapshot', 'snapshot' );
	}
}