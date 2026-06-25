<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_checkout_shipping' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Checkout_Shipping' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionTB_Woo_Checkout_Shipping extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $defaults;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.3
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.3
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_woo_checkout_shipping' );
				add_filter( 'fusion_attr_fusion_tb_woo_checkout_shipping-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_checkout_shipping', [ $this, 'ajax_render' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 3.3
			 * @return boolean
			 */
			public function should_render() {
				return is_singular();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'margin_bottom'            => '',
					'margin_left'              => '',
					'margin_right'             => '',
					'margin_top'               => '',
					'field_bg_color'           => $fusion_settings->get( 'form_bg_color' ),
					'field_text_color'         => $fusion_settings->get( 'form_text_color' ),
					'field_border_color'       => $fusion_settings->get( 'form_border_color' ),
					'field_border_focus_color' => $fusion_settings->get( 'form_focus_border_color' ),
					'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
					'class'                    => '',
					'id'                       => '',
					'animation_type'           => '',
					'animation_direction'      => 'down',
					'animation_speed'          => '0.1',
					'animation_delay'          => '',
					'animation_offset'         => $fusion_settings->get( 'animation_offset' ),
					'animation_color'          => '',
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$return_data = [];
				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$args           = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$post_id        = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->defaults = self::get_element_defaults();
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_shipping' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$return_data['woo_checkout_shipping'] = $this->get_woo_checkout_shipping_content();
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_shipping' );

				$html = '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_checkout_shipping-shortcode' ) . '>' . $this->get_woo_checkout_shipping_content() . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo Checkout Shipping element.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function get_woo_checkout_shipping_content() {
				$content  = '';
				$checkout = WC()->checkout();

				if ( ! is_object( WC()->cart ) || 0 === WC()->cart->get_cart_contents_count() ) {
					return $content;
				}

				ob_start();
				wc_get_template( 'checkout/form-shipping.php', [ 'checkout' => $checkout ] );
				if ( true === WC()->cart->needs_shipping_address() ) {
					$content = preg_replace( '/<h3 id=(.+?)>(.+?)<\/h3>/is', '<div id=$1>$2</div>', ob_get_clean(), 1 );
				} else {
					$content = preg_replace( '#<h3>(.*?)</h3>#', '', ob_get_clean(), 1 );
				}

				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr() {
				$attr       = [
					'class' => 'fusion-woo-checkout-shipping-tb fusion-woo-checkout-shipping-tb-' . $this->counter,
					'style' => '',
				];
				$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $is_builder ) {
					$attr['class'] .= ' awb-live';
				}

				$attr['style'] .= $this->get_style_variables();

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
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
				$custom_vars = [];

				if ( ! $this->is_default( 'field_text_color' ) ) {
					$custom_vars['placeholder_color'] = Fusion_Color::new_color( $this->args['field_text_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();
				}

				if ( ! $this->is_default( 'field_border_focus_color' ) ) {
					$custom_vars['hover_color'] = Fusion_Color::new_color( $this->args['field_border_focus_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();
				}

				$css_vars_options = [
					'field_bg_color'           => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'field_text_color'         => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'field_border_color'       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'field_border_focus_color' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'margin_top'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.9
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-checkout-shipping.min.css' );
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function on_first_render() {
				wp_enqueue_script( 'wc-checkout' );
			}
		}
	}

	new FusionTB_Woo_Checkout_Shipping();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.3
 */
function fusion_component_woo_checkout_shipping() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Checkout_Shipping',
			[
				'name'      => esc_attr__( 'Woo Checkout Shipping', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_woo_checkout_shipping',
				'icon'      => 'fusiona-checkout-shipping',
				'params'    => [
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Form Field Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the form input fields.', 'fusion-builder' ),
						'param_name'  => 'field_bg_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Form Field Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the form input fields.', 'fusion-builder' ),
						'param_name'  => 'field_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Field Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the form input fields.', 'fusion-builder' ),
						'param_name'  => 'field_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Field Border Color On Focus', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the form input fields on focus.', 'fusion-builder' ),
						'param_name'  => 'field_border_focus_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_focus_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
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
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-woo-checkout-shipping-tb',
					],
				],
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_checkout_shipping',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_checkout_shipping' );
