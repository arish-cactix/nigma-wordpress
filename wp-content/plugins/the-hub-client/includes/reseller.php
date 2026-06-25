<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WPMUDEV_HUB_Reseller {

	const RESELLER_API_TRANSIENT_NAME = 'wpmudev_hub_reseller_api_data';

	protected static $api_reseller_response = null;

	/**
	 * @return bool
	 */
	public function is_hub_client_billing_active() {
		// fallback / default is true, so its not blocking the flow, let them setup, any future error will be thrown naturally
		return $this->get_reseller_api_data( 'is_stripe_connected', true ) && $this->get_reseller_api_data( 'is_currency_locked', true );
	}

	public function clear_reseller_api_transient() {
		delete_site_transient( self::RESELLER_API_TRANSIENT_NAME );
		self::$api_reseller_response = null;
	}

	/**
	 * @return mixed
	 */
	public function get_reseller_api_data( $key = null, $default_data = null ) {
		$api_reseller = $this->get_api_reseller();

		if ( is_wp_error( $api_reseller ) ) {
			return $default_data;
		}

		// no specific key
		if ( is_null( $key ) ) {
			return $api_reseller;
		}

		return $api_reseller[ $key ] ?? $default_data;
	}

	protected function get_api_reseller() {
		// memoize
		if ( ! is_null( self::$api_reseller_response ) ) {
			return self::$api_reseller_response;
		}

		// transient
		$transient = get_site_transient( self::RESELLER_API_TRANSIENT_NAME );
		if ( false !== $transient ) {
			self::$api_reseller_response = $transient;

			return self::$api_reseller_response;
		}

		$res = WPMUDEV_HUB_API_Request::get_instance()->exec(
			array(
				'path'   => '/client-billing/reseller',
				'method' => 'GET',
			)
		);

		if ( is_wp_error( $res ) ) {
			return $res;
		}

		// cache this for 1 day
		set_site_transient( self::RESELLER_API_TRANSIENT_NAME, $res, DAY_IN_SECONDS );
		self::$api_reseller_response = $res;

		return self::$api_reseller_response;
	}
}