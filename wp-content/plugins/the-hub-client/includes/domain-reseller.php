<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * @since 2.2.0
 */
class WPMUDEV_HUB_Domain_Reseller extends WPMUDEV_HUB_Reseller {

	/**
	 * @var null|self
	 */
	protected static $instance = null;

	const OPTION_NAME = 'wpmudev_hub_domain_reseller_settings';

	/**
	 * Customizations keys
	 */
	const WIDGET_CUSTOMIZATIONS_KEY_SEARCH_PLACEHOLDER_TEXT   = 'search_placeholder_text';
	const WIDGET_CUSTOMIZATIONS_KEY_SEARCH_PLACEHOLDER_COLOR  = 'search_placeholder_color';
	const WIDGET_CUSTOMIZATIONS_KEY_SEARCH_INPUT_BG_COLOR     = 'search_input_background_color';
	const WIDGET_CUSTOMIZATIONS_KEY_SEARCH_INPUT_COLOR        = 'search_input_color';
	const WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_LABEL_TEXT  = 'search_button_label_text';
	const WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_LABEL_COLOR = 'search_button_label_color';
	const WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_BG_COLOR    = 'search_button_background_color';
	const WIDGET_CUSTOMIZATIONS_KEY_RESULT_OPTIONS            = 'result_options';
	const WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_LABEL_TEXT     = 'buy_button_label_text';
	const WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_LABEL_COLOR    = 'buy_button_label_color';
	const WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_BG_COLOR       = 'buy_button_background_color';

	/**
	 * Result Options values
	 */
	const WIDGET_CUSTOMIZATIONS_RESULT_OPTIONS_MATCHES             = 'matches';
	const WIDGET_CUSTOMIZATIONS_RESULT_OPTIONS_MATCHES_SUGGESTIONS = 'matches_suggestions';

	public static $widget_customizations_keys
		= array(
			self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_PLACEHOLDER_TEXT,
			self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_PLACEHOLDER_COLOR,
			self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_INPUT_BG_COLOR,
			self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_INPUT_COLOR,
			self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_LABEL_TEXT,
			self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_LABEL_COLOR,
			self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_BG_COLOR,
			self::WIDGET_CUSTOMIZATIONS_KEY_RESULT_OPTIONS,
			self::WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_LABEL_TEXT,
			self::WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_LABEL_COLOR,
			self::WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_BG_COLOR,
		);

	/**
	 * @return self
	 *
	 * @since 2.2.0
	 */
	public static function get_instance() {
		/**
		 * Filter Hub Domain Reseller adapter
		 *
		 * @param WPMUDEV_HUB_Domain_Reseller|null $instance
		 *
		 * @since 2.2.0
		 */
		self::$instance = apply_filters( 'wpmudev_hub_domain_reseller_adapter', self::$instance );
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param $customization_key
	 *
	 * @return string
	 *
	 * @since 2.2.0
	 */
	public static function get_default_widget_customization( $customization_key ) {
		switch ( $customization_key ) {
			case self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_PLACEHOLDER_TEXT:
				return __( 'Eg: businessname.com', 'thc' );
			case self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_PLACEHOLDER_COLOR:
				return '#737373';
			case self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_INPUT_COLOR:
				return '#14171c';
			case self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_INPUT_BG_COLOR:
			case self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_LABEL_COLOR:
			case self::WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_BG_COLOR:
				return '#ffffff';
			case self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_LABEL_TEXT:
				return __( 'Search Domain', 'thc' );
			case self::WIDGET_CUSTOMIZATIONS_KEY_SEARCH_BUTTON_BG_COLOR:
				return '#2d47d0';
			case self::WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_LABEL_COLOR:
				return '#1a1a1a';
			case self::WIDGET_CUSTOMIZATIONS_KEY_RESULT_OPTIONS:
				return self::WIDGET_CUSTOMIZATIONS_RESULT_OPTIONS_MATCHES_SUGGESTIONS;
			case self::WIDGET_CUSTOMIZATIONS_KEY_BUY_BUTTON_LABEL_TEXT:
				return __( 'Buy Now', 'thc' );
			default:
				return '';
		}
	}

	/**
	 * @param $key
	 * @param $fallback
	 *
	 * @return mixed depends on the raw data value and fallback
	 *
	 * @since 2.2.0
	 */
	protected function get_raw_data( $key = '', $fallback = false ) {
		$raw_data = get_site_option( self::OPTION_NAME, array() );
		$raw_data = is_array( $raw_data ) ? $raw_data : array();

		if ( $key ) {
			return ( $raw_data[ $key ] ?? $fallback );
		}

		return ! is_null( $raw_data ) ? $raw_data : $fallback;
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 *
	 * @since 2.2.0
	 */
	protected function update_raw_data( $data ) {
		return update_site_option(
			self::OPTION_NAME,
			wp_parse_args( $data, $this->get_raw_data() )
		);
	}

	/**
	 * @return bool
	 *
	 * @since 2.2.0
	 */
	public function is_active() {
		/**
		 * Filter whether domain reseller active
		 *
		 * @param bool $active current active state
		 *
		 * @since 2.2.0
		 */
		$active = apply_filters( 'wpmudev_hub_is_domain_reseller_active', $this->get_raw_data( 'is_active' ) );

		return wp_validate_boolean( $active );
	}

	/**
	 * @param $active
	 *
	 * @return bool
	 *
	 * @since 2.2.0
	 */
	public function set_active( $active = true ) {
		$active = wp_validate_boolean( $active );

		return $this->update_raw_data( array( 'is_active' => $active ) );
	}

	/**
	 * @return array
	 *
	 * @since 2.2.0
	 */
	public function get_default_widget_customizations() {
		$default_customizations = array();
		foreach ( self::$widget_customizations_keys as $widget_customizations_key ) {
			$default_customizations[ $widget_customizations_key ] = self::get_default_widget_customization( $widget_customizations_key );
		}

		return $default_customizations;
	}

	public function get_widget_customizations() {
		$widget_customizations = wp_parse_args(
			$this->get_raw_data( 'widget_customizations', array() ),
			// defaults
			$this->get_default_widget_customizations()
		);

		/**
		 * Filter widget customization for domain reseller
		 *
		 * @param array $widget_customizations current widget customization
		 *
		 * @since 2.2.0
		 */
		$widget_customizations = apply_filters( 'wpmudev_hub_domain_reseller_widget_customizations', $widget_customizations );

		return is_array( $widget_customizations ) ? $widget_customizations : array();
	}

	/**
	 * @param $customizations
	 *
	 * @return bool
	 *
	 * @since 2.2.0
	 */
	public function set_widget_customizations( $customizations ) {
		$customizations = wp_parse_args(
			$customizations,
			$this->get_widget_customizations()
		);

		return $this->update_raw_data( array( 'widget_customizations' => $customizations ) );
	}

	/**
	 * @param $args
	 * @param $response_headers
	 *
	 * @return array|WP_Error
	 *
	 * @since 2.2.0
	 */
	public function get_api_plans( $args, &$response_headers = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'page'     => 1,
				'per_page' => 20,
			)
		);

		// TODO, somehow have a cache / memoizer logic in this
		// its paginated data, so unless being done carefully, it would be premature to optimize it
		// maybe have some metadata API, that comparable to decide the validity of cache
		// one of the classic computer science hard things, am i right ? cache invalidation

		return WPMUDEV_HUB_API_Request::get_instance()->exec(
			array(
				'path'   => '/client-billing/products/reseller/domain/plans',
				'method' => 'GET',
				'data'   => $args,
			),
			$redirected_location,
			$response_headers
		);
	}

	public function get_api_lookup( $args ) {
		$widget_customizations = $this->get_widget_customizations();
		$result_option         = $widget_customizations[ self::WIDGET_CUSTOMIZATIONS_KEY_RESULT_OPTIONS ] ?? self::WIDGET_CUSTOMIZATIONS_RESULT_OPTIONS_MATCHES_SUGGESTIONS;

		$args['with_suggestions'] = self::WIDGET_CUSTOMIZATIONS_RESULT_OPTIONS_MATCHES_SUGGESTIONS === $result_option;

		return WPMUDEV_HUB_API_Request::get_instance()->exec(
			array(
				'path'   => '/client-billing-client/checkout/domain-names/lookup',
				'method' => 'GET',
				'data'   => $args,
			)
		);
	}

	public function reset() {
		delete_site_option( self::OPTION_NAME );
	}
}