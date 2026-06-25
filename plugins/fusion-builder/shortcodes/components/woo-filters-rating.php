<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.8
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_filters_rating' ) ) {

	if ( ! class_exists( 'FusionTB_WooFiltersRating' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.8
		 */
		class FusionTB_WooFiltersRating extends AWB_Woo_Filters {

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.8
			 */
			public function __construct() {
				$this->shortcode_handle = 'fusion_tb_woo_filters_rating';
				parent::__construct();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public static function get_element_defaults() {
				$defaults        = parent::get_element_defaults();
				$fusion_settings = awb_get_fusion_settings();

				$args = wp_parse_args(
					[
						'text_color'       => $fusion_settings->get( 'link_color' ),
						'text_hover_color' => $fusion_settings->get( 'link_hover_color' ),
						'star_color'       => $fusion_settings->get( 'primary_color' ),
						'star_hover_color' => $fusion_settings->get( 'link_hover_color' ),
					],
					$defaults
				);

				return $args;
			}

			/**
			 * Fetch general options.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function fetch_general_options() {
				$options = parent::fetch_general_options();
				$params  = [];

				foreach ( $options as $opt ) {
					if ( 'title' === $opt['param_name'] ) {
						$opt['value'] = esc_html__( 'Average rating', 'fusion-builder' );
					}
					if ( in_array( $opt['param_name'], [ 'title', 'title_size' ], true ) ) {
						$opt['callback']['action'] = "get_{$this->shortcode_handle}";
					}

					$params[] = $opt;
				}

				return $params;
			}

			/**
			 * Fetch design options.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function fetch_design_options() {
				$options         = parent::fetch_design_options();
				$fusion_settings = awb_get_fusion_settings();

				$params = [
					[
						'type'          => 'colorpickeralpha',
						'heading'       => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Controls the text color of rating filter.', 'fusion-builder' ),
						'param_name'    => 'text_color',
						'value'         => '',
						'default'       => $fusion_settings->get( 'link_color' ),
						'group'         => esc_html__( 'Design', 'fusion-builder' ),
						'states'        => [
							'hover' => [
								'label'      => __( 'Hover / Active', 'fusion-builder' ),
								'param_name' => 'text_hover_color',
								'default'    => $fusion_settings->get( 'link_hover_color' ),
								'preview'    => [
									'selector' => '.wc-layered-nav-rating a',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'star_color' ],
					],
					[
						'type'          => 'colorpickeralpha',
						'heading'       => esc_attr__( 'Star Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Controls the star color of rating filter.', 'fusion-builder' ),
						'param_name'    => 'star_color',
						'value'         => '',
						'default'       => $fusion_settings->get( 'primary_color' ),
						'group'         => esc_html__( 'Design', 'fusion-builder' ),
						'states'        => [
							'hover' => [
								'label'      => __( 'Hover / Active', 'fusion-builder' ),
								'param_name' => 'star_hover_color',
								'default'    => $fusion_settings->get( 'link_hover_color' ),
								'preview'    => [
									'selector' => '.wc-layered-nav-rating a',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'text_color' ],
					],
				];

				foreach ( $params as $param ) {
					$options[] = $param;
				}

				return $options;
			}

			/**
			 * Get the style variables.
			 *
			 * @access protected
			 * @since 3.8
			 * @param array $custom_vars The custom CSS vars array.
			 * @return string
			 */
			protected function get_style_variables( $custom_vars = [] ) {
				$custom_vars = [];
				$custom_vars = parent::get_style_variables( $custom_vars );

				$css_vars_options = [
					'text_color'       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'text_hover_color' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'star_color'       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'star_hover_color' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
				];

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Emulate filter element for LE.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function emulate_filter_element() {
				if ( is_null( WC()->query->get_main_query() ) ) {
					WC()->query->product_query( $GLOBALS['wp_query'] );
				}
			}

			/**
			 * Restore filter element for LE.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function restore_filter_element() {
				if ( WC()->query->get_main_query() ) {
					WC()->query->product_query( new WP_Query() );
				}
			}
		}
	}

	/**
	 * Instantiates the class.
	 *
	 * @return object
	 */
	function awb_woo_filter_rating() { // phpcs:ignore WordPress.NamingConventions
		return FusionTB_WooFiltersRating::get_instance();
	}

	// Instantiate.
	awb_woo_filter_rating();
}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_filters_rating() {
	if ( class_exists( 'WooCommerce' ) ) {
		$params    = [];
		$subparams = [];

		// We only need options if element is active.
		if ( function_exists( 'awb_woo_filter_rating' ) ) {
			$params    = awb_woo_filter_rating()->get_element_params();
			$subparams = awb_woo_filter_rating()->get_element_subparams();
		}

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionTB_WooFiltersRating',
				[
					'name'         => esc_attr__( 'Woo Filter By Rating', 'fusion-builder' ),
					'shortcode'    => 'fusion_tb_woo_filters_rating',
					'icon'         => 'fusiona-filter-by-rating',
					'component'    => true,
					'templates'    => [ 'content' ],
					'subparam_map' => $subparams,
					'params'       => $params,
					'callback'     => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_tb_woo_filters_rating',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'fusion_builder_before_init', 'fusion_element_woo_filters_rating' );
