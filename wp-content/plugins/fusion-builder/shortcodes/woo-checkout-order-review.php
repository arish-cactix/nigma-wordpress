<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_checkout_order_review' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Checkout_Order_Review' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionTB_Woo_Checkout_Order_Review extends Fusion_Woo_Component {

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
				parent::__construct( 'fusion_tb_woo_checkout_order_review' );
				add_filter( 'fusion_attr_fusion_tb_woo_checkout_order_review-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_checkout_order_review', [ $this, 'ajax_render' ] );
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
					// General.
					'margin_bottom'                   => '',
					'margin_left'                     => '',
					'margin_right'                    => '',
					'margin_top'                      => '',
					'border_color'                    => '',
					'cell_padding_top'                => '',
					'cell_padding_right'              => '',
					'cell_padding_bottom'             => '',
					'cell_padding_left'               => '',

					// Header.
					'table_header'                    => 'show',
					'header_cell_backgroundcolor'     => '',
					'header_color'                    => '',
					'fusion_font_family_header_font'  => '',
					'fusion_font_variant_header_font' => '',
					'header_font_size'                => '',
					'header_text_transform'           => '',
					'header_line_height'              => '',
					'header_letter_spacing'           => '',

					// Body.
					'display_product_images'          => 'show',
					'table_cell_backgroundcolor'      => '',
					'text_color'                      => '',
					'fusion_font_family_text_font'    => '',
					'fusion_font_variant_text_font'   => '',
					'text_font_size'                  => '',
					'text_text_transform'             => '',
					'text_line_height'                => '',
					'text_letter_spacing'             => '',

					// Footer.
					'footer_cell_backgroundcolor'     => '',
					'footer_color'                    => '',
					'fusion_font_family_footer_font'  => '',
					'fusion_font_variant_footer_font' => '',
					'footer_font_size'                => '',
					'footer_text_transform'           => '',
					'footer_line_height'              => '',
					'footer_letter_spacing'           => '',

					// General.
					'hide_on_mobile'                  => fusion_builder_default_visibility( 'string' ),
					'class'                           => '',
					'id'                              => '',
					'animation_type'                  => '',
					'animation_direction'             => 'down',
					'animation_speed'                 => '0.1',
					'animation_delay'                 => '',
					'animation_offset'                => $fusion_settings->get( 'animation_offset' ),
					'animation_color'                 => '',
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
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_order_review' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$return_data['woo_checkout_order_review'] = $this->get_woo_checkout_order_review_content();
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
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_order_review' );

				$html = '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_checkout_order_review-shortcode' ) . '>' . $this->get_woo_checkout_order_review_content() . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo Checkout Order Review element.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function get_woo_checkout_order_review_content() {
				$content = '';

				if ( ! is_object( WC()->cart ) || 0 === WC()->cart->get_cart_contents_count() ) {
					return $content;
				}

				// Check cart items are valid.
				do_action( 'woocommerce_check_cart_items' );

				if ( function_exists( 'woocommerce_order_review' ) ) {
					if ( class_exists( 'WooCommerce_Germanized' ) ) {
						remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', wc_gzd_get_hook_priority( 'checkout_order_review' ) );
						remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', wc_gzd_get_hook_priority( 'checkout_payment' ) );
					} else {
						remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
						remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
					}

					ob_start();
					woocommerce_order_review();
					do_action( 'woocommerce_checkout_order_review' );
					$content .= ob_get_clean();
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
				$attr = [
					'class' => 'fusion-woo-checkout-order-review-tb fusion-woo-checkout-order-review-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( 'show' !== $this->args['table_header'] ) {
					$attr['class'] .= ' hide-header';
				}

				if ( 'show' !== $this->args['display_product_images'] ) {
					$attr['class'] .= ' hide-product-images';
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

				if ( ! $this->is_default( 'footer_color' ) ) {
					$custom_vars['amount_color'] = Fusion_Sanitize::color( $this->args['footer_color'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_footer_font' ) ) {
					$custom_vars['amount_font_weight'] = $this->args['fusion_font_variant_footer_font'];
				}

				if ( ! $this->is_default( 'cell_padding_top' ) ) {
					$custom_vars['header_padding_top'] = Fusion_Sanitize::get_value_with_unit( $this->args['cell_padding_top'] );
					$custom_vars['footer_padding_top'] = Fusion_Sanitize::get_value_with_unit( $this->args['cell_padding_top'] );
				}

				if ( ! $this->is_default( 'cell_padding_bottom' ) ) {
					$custom_vars['header_padding_bottom'] = Fusion_Sanitize::get_value_with_unit( $this->args['cell_padding_bottom'] );
					$custom_vars['footer_padding_bottom'] = Fusion_Sanitize::get_value_with_unit( $this->args['cell_padding_bottom'] );
				}

				if ( ! $this->is_default( 'cell_padding_left' ) ) {
					$custom_vars['header_padding_left'] = Fusion_Sanitize::get_value_with_unit( $this->args['cell_padding_left'] );
					$custom_vars['footer_padding_left'] = Fusion_Sanitize::get_value_with_unit( $this->args['cell_padding_left'] );
				}

				if ( ! $this->is_default( 'cell_padding_right' ) ) {
					$custom_vars['header_padding_right'] = Fusion_Sanitize::get_value_with_unit( $this->args['cell_padding_right'] );
					$custom_vars['footer_padding_right'] = Fusion_Sanitize::get_value_with_unit( $this->args['cell_padding_right'] );
				}

				if ( ! $this->is_default( 'fusion_font_family_footer_font' ) ) {
					$custom_vars['fusion_font_family_footer_td_font'] = $this->args['fusion_font_family_footer_font'];
				}

				if ( ! $this->is_default( 'fusion_font_variant_footer_font' ) ) {
					$custom_vars['fusion_font_variant_footer_td_font'] = $this->args['fusion_font_variant_footer_font'];
				}

				if ( ! $this->is_default( 'footer_font_size' ) ) {
					$custom_vars['footer_td_font_size'] = Fusion_Sanitize::get_value_with_unit( $this->args['footer_font_size'] );
				}

				if ( ! $this->is_default( 'footer_line_height' ) ) {
					$custom_vars['footer_td_line_height'] = $this->args['footer_line_height'];
				}

				if ( ! $this->is_default( 'footer_letter_spacing' ) ) {
					$custom_vars['footer_td_letter_spacing'] = Fusion_Sanitize::get_value_with_unit( $this->args['footer_letter_spacing'] );
				}

				if ( ! $this->is_default( 'footer_text_transform' ) ) {
					$custom_vars['footer_td_text_transform'] = $this->args['footer_text_transform'];
				}

				$css_vars_options = [
					'header_cell_backgroundcolor' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'header_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'table_cell_backgroundcolor'  => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'text_color'                  => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'border_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'footer_cell_backgroundcolor' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'footer_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'margin_top'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'cell_padding_top'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'cell_padding_bottom'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'cell_padding_left'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'cell_padding_right'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'header_font_size'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'header_letter_spacing'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'text_font_size'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'text_letter_spacing'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'footer_font_size'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'footer_letter_spacing'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'fusion_font_family_header_font',
					'fusion_font_variant_header_font',
					'header_line_height',
					'header_text_transform',
					'fusion_font_family_text_font',
					'fusion_font_variant_text_font',
					'text_line_height',
					'text_text_transform',
					'fusion_font_family_footer_font',
					'fusion_font_variant_footer_font',
					'footer_line_height',
					'footer_text_transform',
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-checkout-order-review.min.css' );
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

	new FusionTB_Woo_Checkout_Order_Review();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.3
 */
function fusion_component_woo_checkout_order_review() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Checkout_Order_Review',
			[
				'name'         => esc_attr__( 'Woo Checkout Order Review', 'fusion-builder' ),
				'shortcode'    => 'fusion_tb_woo_checkout_order_review',
				'icon'         => 'fusiona-checkout-order-review',
				'subparam_map' => [
					'fusion_font_family_header_font'  => 'header_fonts',
					'fusion_font_variant_header_font' => 'header_fonts',
					'header_font_size'                => 'header_fonts',
					'header_text_transform'           => 'header_fonts',
					'header_line_height'              => 'header_fonts',
					'header_letter_spacing'           => 'header_fonts',
					'header_color'                    => 'header_fonts',
					'fusion_font_family_text_font'    => 'text_fonts',
					'fusion_font_variant_text_font'   => 'text_fonts',
					'text_font_size'                  => 'text_fonts',
					'text_text_transform'             => 'text_fonts',
					'text_line_height'                => 'text_fonts',
					'text_letter_spacing'             => 'text_fonts',
					'text_color'                      => 'text_fonts',
					'fusion_font_family_footer_font'  => 'footer_fonts',
					'fusion_font_variant_footer_font' => 'footer_fonts',
					'footer_font_size'                => 'footer_fonts',
					'footer_text_transform'           => 'footer_fonts',
					'footer_line_height'              => 'footer_fonts',
					'footer_letter_spacing'           => 'footer_fonts',
					'footer_color'                    => 'footer_fonts',
				],
				'params'       => [
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
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Table Headers', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have table headers displayed.', 'fusion-builder' ),
						'param_name'  => 'table_header',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
						'default'     => 'show',
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Product Images', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have the product images displayed.', 'fusion-builder' ),
						'param_name'  => 'display_product_images',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
						'default'     => 'show',
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
						'heading'     => esc_attr__( 'Header Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the header cell background color. ', 'fusion-builder' ),
						'param_name'  => 'header_cell_backgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'table_header',
								'value'    => 'show',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Header Cell Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the header text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'header_fonts',
						'choices'          => [
							'font-family'    => 'header_font',
							'font-size'      => 'header_font_size',
							'text-transform' => 'header_text_transform',
							'line-height'    => 'header_line_height',
							'letter-spacing' => 'header_letter_spacing',
							'color'          => 'header_color',
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
						'dependency'       => [
							[
								'element'  => 'table_header',
								'value'    => 'show',
								'operator' => '==',
							],
						],
						'callback'         => [
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
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Table Cell Text Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the text. Leave empty for the global font family.', 'fusion-builder' ),
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
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Footer Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the footer cell background color. ', 'fusion-builder' ),
						'param_name'  => 'footer_cell_backgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Footer Cell Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the footer text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'footer_fonts',
						'choices'          => [
							'font-family'    => 'footer_font',
							'font-size'      => 'footer_font_size',
							'text-transform' => 'footer_text_transform',
							'line-height'    => 'footer_line_height',
							'letter-spacing' => 'footer_letter_spacing',
							'color'          => 'footer_color',
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
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-woo-checkout-order-review-tb',
					],
				],
				'callback'     => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_checkout_order_review',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_checkout_order_review' );
