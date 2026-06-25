<?php // phpcs:ignore
/**
 * Snapshot controllers: Hub service actions
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Controller\Service;

use WPMUDEV\Snapshot4\Controller;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Model\Env;

/**
 * Hub service actions handling controller class
 */
class Hub extends Controller\Service {

	/**
	 * Gets the list of known service actions
	 *
	 * @return array Known actions
	 */
	public function get_known_actions() {
		$known = array(
			self::HUB_INFO,
		);
		return $known;
	}

	/**
	 * Triggered when the service wants to get info from the Hub.
	 *
	 * @param object $params Parameters of the current request.
	 * @param string $action Current action.
	 * @param object $request Current request.
	 */
	public function json_hub_info( $params, $action, $request = false ) {
		Log::info( __( 'The API has requested info from the Hub.', 'snapshot' ) );

		$args = array();

		$args['api_key'] = Env::get_wpmu_api_key();

		$task   = new Task\Check\Hub();
		$result = $task->apply( $args );

		if ( false === $result ) {
			$result = new \WP_Error( 'dashboard_error', 'This site does not appear to be registered to this user in the hub. Please check hub registration and try again.', array( 'status' => 403 ) );
		}

		if ( is_wp_error( $result ) ) {
			Log::error( __( 'The request to retrieve info from the Hub failed.', 'snapshot' ) );
			return $this->send_response_error( $result, $request );
		}

		Log::info( __( 'Passing the retrieved info from the Hub to the API.', 'snapshot' ) );

		// Response to "service".
		$result = (object) $result;
		return $this->send_response_success( $result, $request );
	}
}