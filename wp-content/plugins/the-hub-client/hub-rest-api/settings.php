<?php

class WPMUDEV_HUB_HUB_REST_API_Settings {
	public function __construct() {
		add_filter( 'wdp_register_hub_action', array( $this, 'register_endpoints' ) );
	}

	public function register_endpoints( $actions ) {
		$actions = is_array( $actions ) ? $actions : array();

		$actions['hub_client_update_settings'] = array( $this, 'process' );

		return $actions;
	}

	// the callback spec requires these unused params
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	/**
	 * @param object                        $params
	 * @param string                        $action
	 * @param bool|WPMUDEV_Dashboard_Remote $remote
	 */
	public function process( $params, $action, $remote = false ) {
		$params  = (array) $params;
		$updated = $this->update( $params );
		if ( is_wp_error( $updated ) ) {
			wp_send_json_error(
				array(
					'code'    => $updated->get_error_code(),
					'message' => $updated->get_error_message(),
					'data'    => $updated->get_error_data(),
				)
			);
		}

		wp_send_json_success( $updated );
	}
	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

	/**
	 * @param array $args
	 *
	 * @return array|WP_Error
	 */
	protected function update( $args ) {
		$prev_customization = WPMUDEV_HUB_Plugin::get_customization();

		$args = wp_parse_args(
			$args,
			array(
				'brand_name' => isset( $prev_customization['app_name'] ) ? (string) $prev_customization['app_name'] : '',
			)
		);

		// normalize to options
		$data = array(
			'app_name' => $args['brand_name'],
		);

		WPMUDEV_HUB_Plugin::update_customization( $data );

		$customization = WPMUDEV_HUB_Plugin::get_customization();

		return array(
			'brand_name' => isset( $customization['app_name'] ) ? (string) $customization['app_name'] : '',
		);
	}
}

new WPMUDEV_HUB_HUB_REST_API_Settings();