<?php // phpcs:ignore
/**
 * Snapshot models: OneDrive destination requests model
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model\Request\Destination;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Helper\Api;

/**
 * OneDrive destination requests model class
 */
class Onedrive extends Model\Request\Destination {

	/**
	 * Sends auth code to retrieve access tokens for Dropbox destination.
	 *
	 * @param array $data Config data.
	 *
	 * @return array|mixed|object tokens for that dropbox account if auth flow was successful.
	 */
	public function generate_tokens( $data ) {
		$method         = 'post';
		$this->endpoint = 'generatetoken';
		$path           = $this->get_api_url();

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Get drives, sharepoint libraries and shared folders of OneDrive destination.
	 *
	 * @param array $data Request data.
	 *
	 * @return array|mixed|object array of available root Drives or DriveItems.
	 */
	public function get_roots( $data ) {
		// @TODO: Snapshot API
		$method         = 'post';
		$this->endpoint = 'tpd_drivels';
		$path           = $this->get_api_url();

		$response = $this->request( $path, $data, $method );

		return $response;
	}

	/**
	 * Creates Oauth link.
	 *
	 * @return string OneDrive oauth link.
	 */
	public static function create_oauth_link() {
		$site_hash = hash_hmac( 'sha256', untrailingslashit( network_site_url() ), Api::get_api_key() );

		$redirect_after_login = add_query_arg(
			array(
				'page'                    => 'snapshot-destinations',
				'snapshot_action'         => 'onedrive-auth',
				'snapshot_site_id'        => Api::get_site_id(),
				'snapshot_site_hash'      => $site_hash,
				'snapshot_onedrive_nonce' => wp_create_nonce( 'snapshot_onedrive_connection' ),
			),
			network_admin_url( 'admin.php' )
		);

		$params = array(
			'client_id'     => SNAPSHOT_ONEDRIVE_APP_ID,
			'scope'         => rawurlencode( SNAPSHOT_ONEDRIVE_APP_SCOPE ),
			'redirect_uri'  => SNAPSHOT_ONEDRIVE_REDIRECT_URI,
			'response_type' => 'code',
			'state'         => rawurlencode( $redirect_after_login ),
		);

		if ( defined( 'SNAPSHOT_ONEDRIVE_PROMPT' ) && SNAPSHOT_ONEDRIVE_PROMPT ) {
			$params['prompt'] = SNAPSHOT_ONEDRIVE_PROMPT;
		}

		$auth_url = add_query_arg( $params, SNAPSHOT_ONEDRIVE_AUTHORIZE_URL );

		return filter_var( $auth_url, FILTER_SANITIZE_URL );
	}
}