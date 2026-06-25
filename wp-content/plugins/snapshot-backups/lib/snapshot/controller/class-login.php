<?php // phpcs:ignore
/**
 * Snapshot controllers: Login controller class
 *
 * Intercepts the Google Login
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model\Env;

/**
 * Login Controller class
 */
class Login extends Controller {

	/**
	 * Boots up the Login controller
	 *
	 * @return void
	 */
	public function boot() {
		add_action( 'admin_init', array( $this, 'check_google_login' ) );
	}

	/**
	 * Check if we passed the Google Login
	 *
	 * @return void
	 */
	public function check_google_login() {

		if ( isset( $_REQUEST['referer'] ) && 'google_login' === trim( $_REQUEST['referer'] ) ) { //phpcs:ignore

			// Verify nonce.
			if ( ! wp_verify_nonce( trim( $_REQUEST['ref_nonce'] ), 'snapshot-google-login-nonce' ) ) { //phpcs:ignore
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Check if API Key is present in the URL.
			if ( ! isset( $_REQUEST['set_apikey'] ) ) {
				return;
			}

			// Check if returned API Key matches with the stored API Key.
			if ( trim( $_REQUEST['set_apikey'] ) !== Env::get_wpmu_api_key() ) {//phpcs:ignore
				return;
			}

			Settings::allow_delete_backup( true );
		}//end if
	}
}