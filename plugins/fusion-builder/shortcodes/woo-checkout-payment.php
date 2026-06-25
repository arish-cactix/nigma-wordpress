<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_checkout_payment' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Checkout_Payment' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionTB_Woo_Checkout_Payment extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $defaults;

			/**
			 * An array of the unmerged shortcode arguments.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $params;

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
				parent::__construct( 'fusion_tb_woo_checkout_payment' );
				add_filter( 'fusion_attr_fusion_tb_woo_checkout_payment-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_checkout_payment', [ $this, 'ajax_render' ] );

				if ( class_exists( 'WC_Stripe' ) ) {
					$stripe_payment_class = WC_Stripe::get_instance()->payment_request_configuration;
					add_action( 'woocommerce_review_order_before_submit', [ $stripe_payment_class, 'display_payment_request_button_html' ], 1 );
					add_action( 'woocommerce_review_order_before_submit', [ $stripe_payment_class, 'display_payment_request_button_separator_html' ], 2 );
				}

				if ( class_exists( 'WC_Payments' ) && ! is_admin() && ! fusion_doing_ajax() ) {
					add_action( 'wp', [ $this, 'catch_display_express_checkout_buttons' ] );
				}
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
					// Margin.
					'margin_bottom'                       => '',
					'margin_left'                         => '',
					'margin_right'                        => '',
					'margin_top'                          => '',

					// Label.
					'label_padding_top'                   => '',
					'label_padding_right'                 => '',
					'label_padding_bottom'                => '',
					'label_padding_left'                  => '',
					'label_bg_color'                      => $fusion_settings->get( 'testimonial_bg_color' ),
					'label_bg_hover_color'                => '#f0f0f0',
					'label_color'                         => $fusion_settings->get( 'body_typography', 'color' ),
					'label_hover_color'                   => '',

					// Payment description.
					'payment_padding_top'                 => '',
					'payment_padding_right'               => '',
					'payment_padding_bottom'              => '',
					'payment_padding_left'                => '',
					'payment_box_bg'                      => $fusion_settings->get( 'testimonial_bg_color' ),
					'payment_color'                       => $fusion_settings->get( 'body_typography', 'color' ),

					// Misc.
					'text_font_size'                      => $fusion_settings->get( 'body_typography', 'font-size' ),
					'text_text_transform'                 => '',
					'text_line_height'                    => '',
					'text_letter_spacing'                 => '',
					'fusion_font_family_text_typography'  => 'inherit',
					'fusion_font_variant_text_typography' => '400',
					'link_color'                          => $fusion_settings->get( 'link_color' ),
					'link_hover_color'                    => $fusion_settings->get( 'link_hover_color' ),
					'button_style'                        => '',
					'button_size'                         => '',
					'button_stretch'                      => 'no',
					'button_alignment'                    => '',
					'button_border_top'                   => '',
					'button_border_right'                 => '',
					'button_border_bottom'                => '',
					'button_border_left'                  => '',
					'button_color'                        => '',
					'button_gradient_top'                 => $fusion_settings->get( 'button_gradient_top_color' ),
					'button_gradient_bottom'              => $fusion_settings->get( 'button_gradient_bottom_color' ),
					'button_border_color'                 => $fusion_settings->get( 'button_gradient_top_color_hover' ),
					'button_color_hover'                  => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
					'button_gradient_top_hover'           => '',
					'button_gradient_bottom_hover'        => '',
					'button_border_color_hover'           => '',
					'hide_on_mobile'                      => fusion_builder_default_visibility( 'string' ),
					'class'                               => '',
					'id'                                  => '',
					'animation_type'                      => '',
					'animation_direction'                 => 'down',
					'animation_speed'                     => '0.1',
					'animation_delay'                     => '',
					'animation_offset'                    => $fusion_settings->get( 'animation_offset' ),
					'animation_color'                     => '',
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
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_payment' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$return_data['woo_checkout_payment'] = $this->get_woo_checkout_payment_content();
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
				$this->params   = $args;
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_payment' );

				// Legacy single border width.
				if ( isset( $args['button_border_width'] ) && ! isset( $args['button_border_top'] ) ) {
					$this->args['button_border_top']    = $args['button_border_width'];
					$this->args['button_border_right']  = $this->args['button_border_top'];
					$this->args['button_border_bottom'] = $this->args['button_border_top'];
					$this->args['button_border_left']   = $this->args['button_border_top'];
				}

				$html = '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_checkout_payment-shortcode' ) . '>' . $this->get_woo_checkout_payment_content() . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo Checkout Payment element.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function get_woo_checkout_payment_content() {
				$content = '';

				if ( ! is_object( WC()->cart ) || 0 === WC()->cart->get_cart_contents_count() ) {
					return $content;
				}

				if ( function_exists( 'woocommerce_checkout_payment' ) ) {
					ob_start();
					do_action( 'awb_before_woocommerce_checkout_payment' );
					woocommerce_checkout_payment();
					do_action( 'awb_after_woocommerce_checkout_payment' );
					$content .= ob_get_clean();
				}
				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
			}

			/**
			 * Add Woocommerce Payment ApplePay and GPay Buttons.
			 *
			 * @access public
			 * @since 3.11.3
			 * @return void
			 */
			public function catch_display_express_checkout_buttons() {
				// phpcs:disable WordPress.NamingConventions
				global $wp_filter;

				if ( isset( $wp_filter['woocommerce_checkout_before_customer_details'] ) ) {
					foreach ( $wp_filter['woocommerce_checkout_before_customer_details'] as $index => $actions ) {
						foreach ( $actions as $name => $action ) {
							if ( false !== strpos( $name, 'display_express_checkout_buttons' ) ) {
								add_action( 'awb_before_woocommerce_checkout_payment', $action['function'] );
							}
						}
					}
				}

				// phpcs:enable WordPress.NamingConventions
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-woo-checkout-payment-tb fusion-woo-checkout-payment-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= $this->get_style_variables();

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( ! $this->is_default( 'button_style' ) ) {

					$attr['class'] .= ' button-custom';

					if ( ! $this->is_default( 'button_size' ) ) {
						$attr['class'] .= ' button-' . $this->args['button_size'];
					}

					if ( ! $this->is_default( 'button_stretch' ) ) {
						$attr['class'] .= ' button-stretch';
					}
				}

				// Button alignment.
				if ( ! $this->is_default( 'button_alignment' ) ) {
					$attr['class'] .= ' button-align-' . $this->args['button_alignment'];
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

				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'text_typography', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$custom_vars[ 'text_' . $rule ] = $value;
				}

				if ( ! $this->is_default( 'label_padding_left' ) ) {
					$custom_vars['label_padding_left'] = 'max(55px,' . $this->args['label_padding_left'] . ')';
				}

				if ( ( isset( $this->params['button_gradient_top'] ) && '' !== $this->params['button_gradient_top'] ) || ( isset( $this->params['button_gradient_bottom'] ) && '' !== $this->params['button_gradient_bottom'] ) ) {
					$custom_vars['button_gradient_top']     = $this->args['button_gradient_top'];
					$custom_vars['button_background_image'] = 'linear-gradient( to top, ' . $this->args['button_gradient_bottom'] . ', ' . $this->args['button_gradient_top'] . ' )';
				}

				if ( ( isset( $this->params['button_gradient_top_hover'] ) && '' !== $this->params['button_gradient_top_hover'] ) || ( isset( $this->params['button_gradient_bottom_hover'] ) && '' !== $this->params['button_gradient_bottom_hover'] ) ) {
					$custom_vars['button_gradient_top_hover']     = $this->args['button_gradient_top_hover'];
					$custom_vars['button_background_image_hover'] = 'linear-gradient( to top, ' . $this->args['button_gradient_bottom_hover'] . ', ' . $this->args['button_gradient_top_hover'] . ' )';
				}

				$css_vars_options = [
					'link_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'link_hover_color'          => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'label_bg_color'            => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'label_color'               => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'label_hover_color'         => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'button_color'              => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'button_border_color'       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'label_bg_hover_color'      => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'payment_box_bg'            => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'payment_color'             => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'button_color_hover'        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'button_border_color_hover' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'margin_top'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'text_font_size'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'text_letter_spacing'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'label_padding_top'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'label_padding_bottom'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'label_padding_right'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'payment_padding_top'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'payment_padding_bottom'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'payment_padding_left'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'payment_padding_right'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_border_top'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_border_right'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_border_bottom'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_border_left'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'text_line_height',
					'text_text_transform',
				];

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
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

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-checkout-payment.min.css' );
			}
		}
	}

	new FusionTB_Woo_Checkout_Payment();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.3
 */
function fusion_component_woo_checkout_payment() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Checkout_Payment',
			[
				'name'         => esc_attr__( 'Woo Checkout Payment', 'fusion-builder' ),
				'shortcode'    => 'fusion_tb_woo_checkout_payment',
				'icon'         => 'fusiona-checkout-payment',
				'subparam_map' => [
					'fusion_font_family_text_typography'  => 'main_typography',
					'fusion_font_variant_text_typography' => 'main_typography',
					'text_font_size'                      => 'main_typography',
					'text_text_transform'                 => 'main_typography',
					'text_line_height'                    => 'main_typography',
					'text_letter_spacing'                 => 'main_typography',
				],
				'params'       => [
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the payments text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'main_typography',
						'choices'          => [
							'font-family'    => 'text_typography',
							'font-size'      => 'text_font_size',
							'text-transform' => 'text_text_transform',
							'line-height'    => 'text_line_height',
							'letter-spacing' => 'text_letter_spacing',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
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
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Label Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default value.', 'fusion-builder' ),
						'param_name'       => 'label_padding',
						'value'            => [
							'label_padding_top'    => '',
							'label_padding_right'  => '',
							'label_padding_bottom' => '',
							'label_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'          => 'colorpickeralpha',
						'heading'       => esc_attr__( 'Label Background Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Controls the label background color of the payments.', 'fusion-builder' ),
						'param_name'    => 'label_bg_color',
						'value'         => '',
						'default'       => $fusion_settings->get( 'testimonial_bg_color' ),
						'group'         => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'      => [
							'function' => 'fusion_style_block',
						],
						'states'        => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'default'    => '#f0f0f0',
								'param_name' => 'label_bg_hover_color',
								'preview'    => [
									'selector' => 'ul.wc_payment_methods li',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'label_color' ],
					],
					[
						'type'          => 'colorpickeralpha',
						'heading'       => esc_attr__( 'Label Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Controls the label color of the payments.', 'fusion-builder' ),
						'param_name'    => 'label_color',
						'value'         => '',
						'default'       => $fusion_settings->get( 'body_typography', 'color' ),
						'group'         => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'      => [
							'function' => 'fusion_style_block',
						],
						'states'        => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'param_name' => 'label_hover_color',
								'preview'    => [
									'selector' => 'ul.wc_payment_methods li > label',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'label_bg_color' ],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Payment Description Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default value.', 'fusion-builder' ),
						'param_name'       => 'payment_padding',
						'value'            => [
							'payment_padding_top'    => '',
							'payment_padding_right'  => '',
							'payment_padding_bottom' => '',
							'payment_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Payment Description Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the label background color of the payments description.', 'fusion-builder' ),
						'param_name'  => 'payment_box_bg',
						'value'       => '',
						'default'     => $fusion_settings->get( 'testimonial_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Payment Description Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the description color of the payments.', 'fusion-builder' ),
						'param_name'  => 'payment_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link color of the payments text.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'states'      => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'default'    => $fusion_settings->get( 'link_hover_color' ),
								'param_name' => 'link_hover_color',
								'preview'    => [
									'selector' => 'a',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Place Order Button Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the place order button.', 'fusion-builder' ),
						'param_name'  => 'button_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button size.', 'fusion-builder' ),
						'param_name'  => 'button_size',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the button spans the full width/remaining width of row.', 'fusion-builder' ),
						'param_name'  => 'button_stretch',
						'default'     => 'no',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's alignment.", 'fusion-builder' ),
						'param_name'  => 'button_alignment',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Button Border Size', 'fusion-builder' ),
						'param_name'       => 'button_border_width',
						'description'      => esc_attr__( 'Controls the border size. In pixels.', 'fusion-builder' ),
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'value'            => [
							'button_border_top'    => '',
							'button_border_right'  => '',
							'button_border_bottom' => '',
							'button_border_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'button_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'dependency'       => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_border_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
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
						'preview_selector' => '.fusion-woo-checkout-payment-tb',
					],
				],
				'callback'     => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_checkout_payment',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_checkout_payment' );
