<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.10
 */

if ( fusion_is_element_enabled( 'fusion_woo_order_customer_details' ) ) {

	if ( ! class_exists( 'Fusion_Woo_Order_Customer_Details' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.10
		 */
		class Fusion_Woo_Order_Customer_Details extends Fusion_Woo_Component {

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
				parent::__construct( 'fusion_woo_order_customer_details' );

				add_filter( 'fusion_attr_fusion_woo_order_customer_details-heading-shortcode', [ $this, 'heading_attr' ] );
				add_filter( 'fusion_attr_fusion_woo_order_customer_details-shortcode', [ $this, 'attr' ] );
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
					'hide_on_mobile'                    => fusion_builder_default_visibility( 'string' ),
					'class'                             => '',
					'id'                                => '',

					'title_size'                        => '2',

					'fusion_font_family_headings_typo'  => '',
					'fusion_font_variant_headings_typo' => '',
					'headings_typo_font_size'           => '',
					'headings_typo_line_height'         => '',
					'headings_typo_letter_spacing'      => '',
					'headings_typo_text_transform'      => '',
					'headings_typo_color'               => '',

					'separator_style'                   => '',
					'separator_color'                   => '',
					'separator_width'                   => '70',
					'separator_height'                  => '',

					'headings_margin_top'               => '',
					'headings_margin_right'             => '',
					'headings_margin_bottom'            => '',
					'headings_margin_left'              => '',

					'fusion_font_family_address_typo'   => '',
					'fusion_font_variant_address_typo'  => '',
					'address_typo_font_size'            => '',
					'address_typo_line_height'          => '',
					'address_typo_letter_spacing'       => '',
					'address_typo_text_transform'       => '',
					'address_typo_color'                => '',

					'address_margin_top'                => '',
					'address_margin_right'              => '',
					'address_margin_bottom'             => '',
					'address_margin_left'               => '',

					'margin_top'                        => '',
					'margin_right'                      => '',
					'margin_bottom'                     => '',
					'margin_left'                       => '',

					'animation_direction'               => 'left',
					'animation_offset'                  => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                   => '',
					'animation_delay'                   => '',
					'animation_type'                    => '',
					'animation_color'                   => '',
				];
			}

			/**
			 * Render the shortcode.
			 *
			 * @since 3.10
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_woo_order_customer_details' );
				$this->wc_order = $this->get_order_object( false );

				// Return if order doesn't exist.
				if ( false === $this->wc_order ) {
					return '';
				}

				$show_customer_details = is_user_logged_in() && $this->wc_order->get_user_id() === get_current_user_id();
				$show_customer_details = apply_filters( 'awb_woo_order_customer_show_details', $show_customer_details );
				$show_shipping         = ! wc_ship_to_billing_address_only() && $this->wc_order->needs_shipping_address();
				$show_shipping         = apply_filters( 'awb_woo_order_customer_show_shipping', $show_shipping );

				if ( ! $show_customer_details ) {
					return '';
				}

				$html = ' <section ' . FusionBuilder::attributes( 'fusion_woo_order_customer_details-shortcode' ) . '> ';

				if ( $show_shipping ) {
					$html .= ' <section class="awb-woo-order-customer-details__cols">';
					$html .= ' <div class="awb-woo-order-customer-details__col awb-woo-order-customer-details__col--1"> ';
				}

				$html .= $this->get_billing_inner_column();

				if ( $show_shipping ) {
					$html .= ' </div> ';
					$html .= ' <div class="awb-woo-order-customer-details__col awb-woo-order-customer-details__col--2"> ';
					$html .= $this->get_shipping_inner_column();
					$html .= ' </div> ';
					$html .= ' </section> ';
				}

				ob_start();
				do_action( 'woocommerce_order_details_after_customer_details', $this->wc_order );
				$html .= ob_get_clean();

				$html .= ' </section> ';

				$this->counter++;
				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Get the billing address column.
			 *
			 * @return string
			 */
			private function get_billing_inner_column() {
				$title_tag = $this->get_title_tag();
				$html      = ' <' . $title_tag . ' ' . FusionBuilder::attributes( 'fusion_woo_order_customer_details-heading-shortcode' ) . '>' . esc_html__( 'Billing address', 'woocommerce' ) . '</' . $title_tag . '> ';

				if ( 'none' !== $this->args['separator_style'] ) {
					$html .= '<hr class="awb-woo-order-customer-details__sep" />';
				}

				$html .= ' <address class="awb-woo-order-customer-details__address"> ';
				$html .= wp_kses_post( $this->wc_order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ) );

				if ( $this->wc_order->get_billing_phone() ) {
					$html .= ' <p class="awb-woo-order-customer-details--phone">' . esc_html( $this->wc_order->get_billing_phone() ) . '</p> ';
				}

				if ( $this->wc_order->get_billing_email() ) {
					$html .= ' <p class="awb-woo-order-customer-details--email">' . esc_html( $this->wc_order->get_billing_email() ) . '</p> ';
				}

				$html .= ' </address> ';

				return $html;
			}

			/**
			 * Get the billing address column.
			 *
			 * @return string
			 */
			private function get_shipping_inner_column() {
				$title_tag = $this->get_title_tag();
				$html      = ' <' . $title_tag . ' ' . FusionBuilder::attributes( 'fusion_woo_order_customer_details-heading-shortcode' ) . '>' . esc_html__( 'Shipping address', 'woocommerce' ) . '</' . $title_tag . '> ';

				if ( 'none' !== $this->args['separator_style'] ) {
					$html .= '<hr class="awb-woo-order-customer-details__sep" />';
				}

				$html .= ' <address class="awb-woo-order-customer-details__address"> ';
				$html .= wp_kses_post( $this->wc_order->get_formatted_shipping_address( esc_html__( 'N/A', 'woocommerce' ) ) );

				if ( $this->wc_order->get_shipping_phone() ) {
					$html .= ' <p class="awb-woo-order-customer-details__phone">' . esc_html( $this->wc_order->get_shipping_phone() ) . '</p> ';
				}

				$html .= ' </address> ';

				return $html;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @since 3.10
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'awb-woo-order-customer-details awb-woo-order-customer-details--' . $this->counter,
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
			 * Get the tag of the title.
			 *
			 * @return string
			 */
			public function get_title_tag() {
				$tag_option = $this->args['title_size'];
				if ( is_numeric( $tag_option ) ) {
					return 'h' . $tag_option;
				}

				if ( ! $tag_option ) {
					return 'h2';
				}

				return $tag_option;
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
					'address_typo_font_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'address_typo_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'address_typo_line_height',
					'address_typo_text_transform',
					'address_typo_color'          => [ 'callback' => 'Fusion_Sanitize::color' ],

					'separator_style',
					'separator_color'             => [ 'callback' => 'Fusion_Sanitize::color' ],
					'separator_height'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'headings_margin_top'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'headings_margin_right'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'headings_margin_bottom'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'headings_margin_left'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'address_margin_top'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'address_margin_right'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'address_margin_bottom'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'address_margin_left'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'margin_top'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];
				$custom_vars = [];

				if ( ! $this->is_default( 'separator_width' ) ) {
					$custom_vars['separator_width'] = $this->args['separator_width'] . '%';
				}

				$font_family_vars = $this->get_font_styling_vars( 'address_typo' );

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars ) . $font_family_vars;

				return $styles;
			}

			/**
			 * Builds the headings attributes array.
			 *
			 * @since 3.10
			 * @return array
			 */
			public function heading_attr() {
				$attr = [
					'class' => 'awb-woo-order-customer-details__title',
					'style' => '',
				];

				$title_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'headings_typo', 'array' );
				$font_var_args    = [
					'font-family'    => ( isset( $title_typography['font-family'] ) && $title_typography['font-family'] ? $title_typography['font-family'] : '' ),
					'font-weight'    => ( isset( $title_typography['font-weight'] ) && $title_typography['font-weight'] ? $title_typography['font-weight'] : '' ),
					'font-style'     => ( isset( $title_typography['font-style'] ) && $title_typography['font-style'] ? $title_typography['font-style'] : '' ),
					'font-size'      => $this->args['headings_typo_font_size'],
					'letter-spacing' => $this->args['headings_typo_letter_spacing'],
					'line-height'    => $this->args['headings_typo_line_height'],
					'text-transform' => $this->args['headings_typo_text_transform'],
					'color'          => $this->args['headings_typo_color'],
				];

				$attr['style'] .= $this->get_heading_font_vars( $this->get_title_tag(), $font_var_args );

				return $attr;
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * Should run inside render() function.
			 *
			 * @since 3.10
			 * @return array
			 */
			public static function get_element_extras() {
				return self::get_order_extras();
			}

			/**
			 * Load base CSS.
			 *
			 * @since 3.5
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-order-customer-details.min.css' );
			}
		}
	}

	new Fusion_Woo_Order_Customer_Details();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.10
 */
function fusion_component_woo_order_customer_details() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'Fusion_Woo_Order_Customer_Details',
			[
				'name'      => esc_attr__( 'Woo Order Customer Details', 'fusion-builder' ),
				'shortcode' => 'fusion_woo_order_customer_details',
				'icon'      => 'fusiona-woo-order-received-customer-details',
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

					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'HTML Heading Tag', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of both headings, either div, p or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'title_size',
						'value'       => [
							'1'   => 'H1',
							'2'   => 'H2',
							'3'   => 'H3',
							'4'   => 'H4',
							'5'   => 'H5',
							'6'   => 'H6',
							'div' => 'DIV',
							'p'   => 'P',
						],
						'default'     => '2',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],

					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Headings Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the headings typography.', 'fusion-builder' ) ),
						'param_name'       => 'headings_typo',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'headings_typo',
							'font-size'      => 'headings_typo_font_size',
							'line-height'    => 'headings_typo_line_height',
							'letter-spacing' => 'headings_typo_letter_spacing',
							'text-transform' => 'headings_typo_text_transform',
							'color'          => 'headings_typo_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
					],

					[
						'type'             => 'dimension',
						'param_name'       => 'headings_margin',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Headings Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 4%. Let empty for default H2 title margin.', 'fusion-builder' ),
						'value'            => [
							'headings_margin_top'    => '',
							'headings_margin_right'  => '',
							'headings_margin_bottom' => '',
							'headings_margin_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],

					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Separator Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border style of the heading.', 'fusion-builder' ),
						'param_name'  => 'separator_style',
						'value'       => [
							'none'   => esc_attr__( 'None', 'fusion-builder' ),
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
							'double' => esc_attr__( 'Double', 'fusion-builder' ),
						],
						'default'     => 'solid',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],

					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Separator Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the width of the separator.', 'fusion-builder' ),
						'param_name'  => 'separator_width',
						'value'       => '70',
						'min'         => '0',
						'max'         => '100',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'separator_style',
								'value'    => 'none',
								'operator' => '!=',
							],
						],
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Separator Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the separator color.', 'fusion-builder' ),
						'param_name'  => 'separator_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'separator_style',
								'value'    => 'none',
								'operator' => '!=',
							],
						],
					],

					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Separator Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the separator width.', 'fusion-builder' ),
						'param_name'  => 'separator_height',
						'value'       => '1',
						'min'         => '1',
						'max'         => '20',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'separator_style',
								'value'    => 'none',
								'operator' => '!=',
							],
						],
					],

					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Address Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the headings typography.', 'fusion-builder' ) ),
						'param_name'       => 'address_typo',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'address_typo',
							'font-size'      => 'address_typo_font_size',
							'line-height'    => 'address_typo_line_height',
							'letter-spacing' => 'address_typo_letter_spacing',
							'text-transform' => 'address_typo_text_transform',
							'color'          => 'address_typo_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
					],

					[
						'type'             => 'dimension',
						'param_name'       => 'address_margin',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Address Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
						'value'            => [
							'address_margin_top'    => '',
							'address_margin_right'  => '',
							'address_margin_bottom' => '',
							'address_margin_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],

					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-woo-order-customer-details',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_order_customer_details' );
