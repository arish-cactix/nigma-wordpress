<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooCartTotals' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_WooCartTotals extends Fusion_Element {

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
				parent::__construct();
				add_filter( 'fusion_attr_woo-cart-totals-shortcode-wrapper', [ $this, 'wrapper_attr' ] );
				add_shortcode( 'fusion_woo_cart_totals', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_fusion_get_woo_cart_totals', [ $this, 'ajax_query' ] );
			}


			/**
			 * Gets the query data.
			 *
			 * @access public
			 * @since 3.3
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
				$this->args = $_POST['model']['params']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

				$html = $this->generate_element_content();

				echo wp_json_encode( $html );
				wp_die();
			}


			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					// Element margin.
					'margin_top'                       => '',
					'margin_right'                     => '',
					'margin_bottom'                    => '',
					'margin_left'                      => '',

					// Element margin.
					'button_margin_top'                => '',
					'button_margin_right'              => '',
					'button_margin_bottom'             => '',
					'button_margin_left'               => '',

					// Cell padding.
					'cell_padding_top'                 => '',
					'cell_padding_right'               => '',
					'cell_padding_bottom'              => '',
					'cell_padding_left'                => '',

					'table_cell_backgroundcolor'       => '',
					'heading_cell_backgroundcolor'     => '',

					// Heading styles.
					'heading_color'                    => '',
					'fusion_font_family_heading_font'  => '',
					'fusion_font_variant_heading_font' => '',
					'heading_font_size'                => '',
					'heading_text_transform'           => '',
					'heading_line_height'              => '',
					'heading_letter_spacing'           => '',

					// Text styles.
					'text_color'                       => '',
					'fusion_font_family_text_font'     => '',
					'fusion_font_variant_text_font'    => '',
					'text_font_size'                   => '',
					'text_text_transform'              => '',
					'text_line_height'                 => '',
					'text_letter_spacing'              => '',

					'border_color'                     => '',

					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'class'                            => '',
					'id'                               => '',
					'animation_type'                   => '',
					'animation_direction'              => 'down',
					'animation_speed'                  => '0.1',
					'animation_delay'                  => '',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),
					'animation_color'                  => '',

					'buttons_visibility'               => '',
					'buttons_layout'                   => '',
					'floated_buttons_alignment'        => '',
					'stacked_buttons_alignment'        => '',
					'button_span'                      => '',
				];
			}


			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output
			 */
			public function render( $args, $content = '' ) {
				if ( ! is_object( WC()->cart ) || ( WC()->cart->is_empty() && ! fusion_is_preview_frame() ) ) {
					return;
				}
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_woo_cart_totals' );

				$html = '<div ' . FusionBuilder::attributes( 'woo-cart-totals-shortcode-wrapper' ) . '>' . $this->generate_element_content() . '</div>';

				$this->on_render();
				$this->counter++;
				return apply_filters( 'fusion_element_cart_totals_content', $html, $args );
			}

			/**
			 * Generates element content
			 *
			 * @return string
			 */
			public function generate_element_content() {

				if ( ! is_object( WC()->cart ) || WC()->cart->is_empty() ) {
					return '';
				}

				// Check cart items are valid.
				do_action( 'woocommerce_check_cart_items' );

				// Calc totals.
				WC()->cart->calculate_totals();

				ob_start();
				woocommerce_cart_totals();
				return ob_get_clean();
			}

			/**
			 * Get the style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			public function get_style_variables() {
				$custom_vars      = [];
				$css_vars_options = [
					'heading_cell_backgroundcolor' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'heading_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'table_cell_backgroundcolor'   => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'border_color'                 => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'margin_top'                   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'cell_padding_top'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'cell_padding_bottom'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'cell_padding_left'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'cell_padding_right'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'heading_font_size'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'heading_letter_spacing'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'text_font_size'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'text_letter_spacing'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_margin_top'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_margin_bottom'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_margin_left'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_margin_right'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'fusion_font_family_heading_font',
					'fusion_font_variant_heading_font',
					'heading_line_height',
					'heading_text_transform',
					'fusion_font_family_text_font',
					'fusion_font_variant_text_font',
					'text_line_height',
					'text_text_transform',
					'floated_buttons_alignment',
					'stacked_buttons_alignment',
				];

				if ( ! $this->is_default( 'text_color' ) ) {
					$custom_vars['text_color']   = $this->args['text_color'];
					$custom_vars['button_color'] = $this->args['text_color'];
					$custom_vars['amount_color'] = $this->args['text_color'];
				}

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-woo-cart-totals-wrapper fusion-woo-cart-totals-wrapper-' . $this->counter,
						'style' => '',
					]
				);

				if ( WC()->customer->has_calculated_shipping() ) {
					$attr['class'] .= ' calculated_shipping';
				}

				if ( 'show' === $this->args['buttons_visibility'] ) {
					$attr['class'] .= ' show-buttons';
					$attr['class'] .= ' buttons-' . $this->args['buttons_layout'];

					if ( 'yes' === $this->args['button_span'] ) {
						$attr['class'] .= ' buttons-span-yes';
					}
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
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
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.9
			 * @return void
			 */
			public function on_first_render() {
				// Skip if empty.
				if ( null === $this->args || empty( $this->args ) ) {
					return;
				}

				Fusion_Dynamic_JS::enqueue_script(
					'awb-cart-totals',
					FusionBuilder::$js_folder_url . '/general/awb-cart-totals.js',
					FusionBuilder::$js_folder_path . '/general/awb-cart-totals.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-cart-totals.min.css' );
			}
		}
	}

	new FusionSC_WooCartTotals();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_cart_totals() {
	if ( class_exists( 'WooCommerce' ) ) {
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooCartTotals',
				[
					'name'          => esc_attr__( 'Woo Cart Totals', 'fusion-builder' ),
					'shortcode'     => 'fusion_woo_cart_totals',
					'icon'          => 'fusiona-cart-totals',
					'help_url'      => '',
					'inline_editor' => true,
					'subparam_map'  => [
						'fusion_font_family_heading_font'  => 'heading_fonts',
						'fusion_font_variant_heading_font' => 'heading_fonts',
						'heading_font_size'                => 'heading_fonts',
						'heading_text_transform'           => 'heading_fonts',
						'heading_line_height'              => 'heading_fonts',
						'heading_letter_spacing'           => 'heading_fonts',
						'heading_color'                    => 'heading_fonts',
						'fusion_font_variant_text_font'    => 'text_fonts',
						'fusion_font_family_text_font'     => 'text_fonts',
						'text_font_size'                   => 'text_fonts',
						'text_text_transform'              => 'text_fonts',
						'text_line_height'                 => 'text_fonts',
						'text_letter_spacing'              => 'text_fonts',
						'text_color'                       => 'text_fonts',
					],
					'params'        => [
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Buttons', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide buttons.', 'fusion-builder' ),
							'param_name'  => 'buttons_visibility',
							'default'     => 'show',
							'value'       => [
								'show' => esc_html__( 'Show', 'fusion-builder' ),
								'hide' => esc_html__( 'Hide', 'fusion-builder' ),
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Buttons Layout', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the layout of buttons.', 'fusion-builder' ),
							'param_name'  => 'buttons_layout',
							'value'       => [
								'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
								'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
							],
							'default'     => 'floated',
							'dependency'  => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_html__( 'Buttons Horizontal Align', 'fusion-builder' ),
							'description' => esc_html__( 'Change the horizontal alignment of buttons within its container column.', 'fusion-builder' ),
							'param_name'  => 'floated_buttons_alignment',
							'default'     => 'flex-start',
							'grid_layout' => true,
							'back_icons'  => true,
							'icons'       => [
								'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
								'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
								'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
								'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
								'space-around'  => '<span class="fusiona-horizontal-space-around"></span>',
								'space-evenly'  => '<span class="fusiona-horizontal-space-evenly"></span>',
							],
							'value'       => [
								// We use "start/end" terminology because flex direction changes depending on RTL/LTR.
								'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
								'center'        => esc_html__( 'Center', 'fusion-builder' ),
								'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
								'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
								'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
								'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
							],
							'dependency'  => [
								[
									'element'  => 'buttons_layout',
									'value'    => 'floated',
									'operator' => '==',
								],
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_html__( 'Buttons Horizontal Align', 'fusion-builder' ),
							'description' => esc_html__( 'Change the horizontal alignment of buttons within its container column.', 'fusion-builder' ),
							'param_name'  => 'stacked_buttons_alignment',
							'grid_layout' => true,
							'back_icons'  => true,
							'icons'       => [
								'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
								'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
								'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
							],
							'value'       => [
								'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
								'center'     => esc_html__( 'Center', 'fusion-builder' ),
								'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
							],
							'default'     => 'flex-start',
							'dependency'  => [
								[
									'element'  => 'buttons_layout',
									'value'    => 'stacked',
									'operator' => '==',
								],
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],

						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to have the button span the full width.', 'fusion-builder' ),
							'param_name'  => 'button_span',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
							'dependency'  => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Buttons Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
							'param_name'       => 'buttonsmargin',
							'value'            => [
								'button_margin_top'    => '',
								'button_margin_right'  => '',
								'button_margin_bottom' => '',
								'button_margin_left'   => '',
							],
							'dependency'       => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
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
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						],
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-woo-cart-totals-wrapper',
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Table Cell Padding', 'fusion-builder' ),
							'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 5px 0 5px 0 value.', 'fusion-builder' ),
							'param_name'       => 'cell_padding',
							'value'            => [
								'cell_padding_top'    => '',
								'cell_padding_right'  => '',
								'cell_padding_bottom' => '',
								'cell_padding_left'   => '',
							],
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'         => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Heading Cell Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the heading cell background color. ', 'fusion-builder' ),
							'param_name'  => 'heading_cell_backgroundcolor',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Table Cell Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the table cell background color. ', 'fusion-builder' ),
							'param_name'  => 'table_cell_backgroundcolor',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Table Border Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the color of the table border, ex: #000.' ),
							'param_name'  => 'border_color',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'             => 'typography',
							'heading'          => esc_attr__( 'Heading Cell Typography', 'fusion-builder' ),
							'description'      => esc_html__( 'Controls the typography of the heading. Leave empty for the global font family.', 'fusion-builder' ),
							'param_name'       => 'heading_fonts',
							'choices'          => [
								'font-family'    => 'heading_font',
								'font-size'      => 'heading_font_size',
								'text-transform' => 'heading_text_transform',
								'line-height'    => 'heading_line_height',
								'letter-spacing' => 'heading_letter_spacing',
								'color'          => 'heading_color',
							],
							'default'          => [
								'font-family'    => '',
								'variant'        => '400',
								'font-size'      => '',
								'text-transform' => '',
								'line-height'    => '',
								'letter-spacing' => '',
								'color'          => '',
							],
							'remove_from_atts' => true,
							'global'           => true,
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'         => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'             => 'typography',
							'heading'          => esc_attr__( 'Content Typography', 'fusion-builder' ),
							'description'      => esc_html__( 'Controls the typography of the content text. Leave empty for the global font family.', 'fusion-builder' ),
							'param_name'       => 'text_fonts',
							'choices'          => [
								'font-family'    => 'text_font',
								'font-size'      => 'text_font_size',
								'text-transform' => 'text_text_transform',
								'line-height'    => 'text_line_height',
								'letter-spacing' => 'text_letter_spacing',
								'color'          => 'text_color',
							],
							'default'          => [
								'font-family'    => '',
								'variant'        => '400',
								'font-size'      => '',
								'text-transform' => '',
								'line-height'    => '',
								'letter-spacing' => '',
								'color'          => '',
							],
							'remove_from_atts' => true,
							'global'           => true,
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'         => [
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

					],
					'callback'      => [
						'function' => 'fusion_ajax',
						'action'   => 'fusion_get_woo_cart_totals',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'fusion_builder_wp_loaded', 'fusion_element_woo_cart_totals' );
