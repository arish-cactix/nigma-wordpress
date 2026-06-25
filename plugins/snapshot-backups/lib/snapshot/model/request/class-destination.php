<?php // phpcs:ignore
/**
 * Snapshot models: Destination backup requests model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request;

use WPMUDEV\Snapshot4\Model;

/**
 * Destination requests model class
 */
class Destination extends Model\Request {
	const DEFAULT_ERROR = 'snapshot_destination_service_unreachable';

	/**
	 * Destination request endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'tpd_creds';

	/**
	 * Returns action string for logger
	 *
	 * @return string
	 */
	protected function get_action_string() {
		return __( 'configure destination', 'snapshot' );
	}

	/**
	 * Test connection bucket and potentially store it system-side.
	 *
	 * @param array $data Config data.
	 *
	 * @return array|mixed|object array of sent info if success.
	 */
	public function test_connection_final( $data ) {
		$method = 'post';
		$path   = $this->get_api_url();

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Request list of remote destinations.
	 *
	 * @return array|mixed|object array of sent info if success.
	 */
	public function get_destinations() {
		return $this->request( $this->get_api_url(), array(), 'get' );
	}

	/**
	 * Delete remote destination.
	 *
	 * @param string $tpd_id Destination ID.
	 */
	public function delete_destination( $tpd_id ) {
		$method = 'delete';
		$path   = $this->get_api_url() . '/' . $tpd_id;
		$data   = array();

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Update destination.
	 *
	 * @param string $tpd_id        Destination ID.
	 * @param string $tpd_accesskey Access Key.
	 * @param string $tpd_secretkey Secret Key.
	 * @param string $tpd_region    Region.
	 * @param string $tpd_path      Path, created by the selected bucket plus any additional chosen dir inside the bucket.
	 * @param string $tpd_name      Name to be assigned to the stored destination, chosen by the user.
	 * @param int    $tpd_limit     Number of backups to be kept in the destination before rotating.
	 * @param int    $tpd_type      Type of provider (aws, backblaze, etc).
	 * @param string $tpd_endpoint  Provider's endpoint or more specifically complete URL.
	 * @param array  $meta          Meta for FTP destinations.
	 * @param string $drive_id      OneDrive root Drive.id.
	 * @param string $item_id       OneDrive root DriveItem.id.
	 *
	 * @return mixed array of sent info if success.
	 */
	public function update_destination(
		$tpd_id,
		$tpd_accesskey,
		$tpd_secretkey,
		$tpd_region,
		$tpd_path,
		$tpd_name,
		$tpd_limit,
		$tpd_type,
		$tpd_endpoint = '',
		$meta = array(),
		$drive_id = null,
		$item_id = null
	) {
		$method = 'put';
		$path   = $this->get_api_url() . '/' . $tpd_id;

		$data = array(
			'tpd_accesskey' => $tpd_accesskey,
			'tpd_secretkey' => $tpd_secretkey,
			'tpd_region'    => $tpd_region,
			'tpd_path'      => $tpd_path,
			'tpd_name'      => $tpd_name,
			'tpd_limit'     => $tpd_limit,
			'tpd_type'      => $tpd_type,
			'tpd_save'      => 1,
		);

		if ( ! empty( $tpd_endpoint ) ) {
			$data['tpd_endpoint'] = $tpd_endpoint;
		}

		if ( isset( $meta['ftp_timeout'] ) ) {
			$data['ftp_timeout'] = $meta['ftp_timeout'];
			$data['ftp_mode']    = $meta['ftp_mode'];
		}

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Update destination.
	 *
	 * @param string $tpd_id        Destination ID.
	 * @param int    $aws_storage   Set destination as active if = 1.
	 *
	 * @return array|mixed|object array of sent info if success.
	 */
	public function activate_destination( $tpd_id, $aws_storage ) {
		$method = 'put';
		$path   = $this->get_api_url() . '/' . $tpd_id;

		$data = array(
			'aws_storage' => $aws_storage,
		);

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Delete ALL destinations.
	 *
	 * @return array|mixed|object API response.
	 */
	public function delete_all_destinations() {
		$method         = 'delete';
		$this->endpoint = 'tpd_credsls';
		$path           = $this->get_api_url();

		$data = array();

		$response = $this->request( $path, $data, $method );

		return $response;
	}
}