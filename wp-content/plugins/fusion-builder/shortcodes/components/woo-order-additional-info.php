<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.10
 */

if ( fusion_is_element_enabled( 'fusion_woo_order_additional_info' ) ) {

	if ( ! class_exists( 'Fusion_Woo_Order_Additional_Info' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.10
		 */
		class Fusion_Woo_Order_Additional_Info extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @since 3.10
			 * @var array
			 */
			protected $defaults;

			/**
			 * The internal container counter.
			 *
			 * @since 3.10
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Current Order object, or false if don't exist.
			 *
			 * @var WC_Order|false
			 */
			private $wc_order = false;

			/**
			 * Constructor.
			 *
			 * @since 3.10
			 */
			public function __construct() {
				parent::__construct( 'fusion_woo_order_additional_info' );
				add_filter( 'fusion_attr_fusion_woo_order_additional_info-shortcode', [ $this, 'attr' ] );
			}

			/**
			 * Check if component should render
			 *
			 * @since 3.10
			 * @return boolean
			 */
			public function should_render() {
				return is_singular();
			}

			/**
			 * Gets the default values.
			 *
			 * @since 3.10
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'class'               => '',
					'id'                  => '',

					'margin_top'          => '',
					'margin_right'        => '',
					'margin_bottom'       => '',
					'margin_left'         => '',

					'animation_direction' => 'left',
					'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'     => '',
					'animation_delay'     => '',
					'animation_type'      => '',
					'animation_color'     => '',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @since 3.10
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $avada_woocommerce;

				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_woo_order_additional_info' );
				$this->wc_order = $this->get_order_object( false ); // Do not return dummy element for LE.

				// Return if order doesn't exist.
				if ( false === $this->wc_order ) {
					return '';
				}

				// Remove the classic Avada order table/details.
				remove_action( 'woocommerce_thankyou', [ $avada_woocommerce, 'view_order' ] );

				$html    = '';
				$content = '';

				ob_start();
				do_action( 'woocommerce_thankyou_' . $this->wc_order->get_payment_method(), $this->wc_order->get_id() );
				do_action( 'woocommerce_thankyou', $this->wc_order->get_id() );
				$content .= ob_get_clean();

				if ( ! empty( $content ) ) {
					$html .= '<section ' . FusionBuilder::attributes( 'fusion_woo_order_additional_info-shortcode' ) . '>';
					$html .= $content;
					$html .= '</section>';
				}

				// Add them back, although maybe not needed.
				add_action( 'woocommerce_thankyou', [ $avada_woocommerce, 'view_order' ] );

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @since 3.10
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'awb-woo-order-additional-info awb-woo-order-additional-info--' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['style'] .= $this->get_style_variables();

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				return $attr;
			}

			/**
			 * Get the style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_style_variables() {
				$css_vars_options = [
					'margin_top'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];
				$custom_vars      = [];

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Load base CSS.
			 *
			 * @since 3.5
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-order-additional-info.min.css' );
			}
		}
	}

	new Fusion_Woo_Order_Additional_Info();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.10
 */
function fusion_component_woo_order_additional_info() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'Fusion_Woo_Order_Additional_Info',
			[
				'name'      => esc_attr__( 'Woo Order Additional Info', 'fusion-builder' ),
				'shortcode' => 'fusion_woo_order_additional_info',
				'icon'      => 'fusiona-woo-order-received-additional-info',
				'component' => true,
				'templates' => [ 'content' ],
				'params'    => [
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],

					'fusion_margin_placeholder'    => [
						'param_name' => 'margin',
						'heading'    => esc_attr__( 'Margin', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],

					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-woo-order-additional-info',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_order_additional_info' );
