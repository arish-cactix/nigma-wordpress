<?php // phpcs:ignore
/**
 * Snapshot models: API model
 *
 * Holds information for communication with Snapshot Hub API.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Model;

use WPMUDEV\Snapshot4\Model;
use WPMUDEV\Snapshot4\Model\Env;

/**
 * API model class
 */
class Api extends Model {

	/**
	 * Constructor
	 *
	 * Sets up data.
	 */
	public function __construct() {
		$this->populate();
	}

	/**
	 * Initializes the data
	 */
	public function populate() {
		$api_key = Env::get_wpmu_api_key();

		$this->set_data(
			array(
				'api_key' => $api_key,
			)
		);
	}
}