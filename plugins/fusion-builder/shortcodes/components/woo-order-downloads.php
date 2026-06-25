<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.10
 */

if ( fusion_is_element_enabled( 'fusion_woo_order_downloads' ) ) {

	if ( ! class_exists( 'Fusion_Woo_Order_Downloads' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.10
		 */
		class Fusion_Woo_Order_Downloads extends Fusion_Woo_Component {

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
				parent::__construct( 'fusion_woo_order_downloads' );
				add_filter( 'fusion_attr_fusion_woo_order_downloads-shortcode', [ $this, 'attr' ] );
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
					'hide_on_mobile'                      => fusion_builder_default_visibility( 'string' ),
					'class'                               => '',
					'id'                                  => '',

					'margin_top'                          => '',
					'margin_right'                        => '',
					'margin_bottom'                       => '',
					'margin_left'                         => '',

					'h_cell_pad_top'                      => '',
					'h_cell_pad_right'                    => '',
					'h_cell_pad_bottom'                   => '',
					'h_cell_pad_left'                     => '',

					'b_cell_pad_top'                      => '',
					'b_cell_pad_right'                    => '',
					'b_cell_pad_bottom'                   => '',
					'b_cell_pad_left'                     => '',

					'h_cell_bg'                           => '',
					'b_cell_bg'                           => '',

					'border_s'                            => '',
					'border_w'                            => '',
					'border_c'                            => '',

					'fusion_font_family_table_h_typo'     => '',
					'fusion_font_variant_table_h_typo'    => '',
					'table_h_typo_font_size'              => '',
					'table_h_typo_line_height'            => '',
					'table_h_typo_letter_spacing'         => '',
					'table_h_typo_text_transform'         => '',
					'table_h_typo_color'                  => '',

					'fusion_font_family_table_item_typo'  => '',
					'fusion_font_variant_table_item_typo' => '',
					'table_item_typo_font_size'           => '',
					'table_item_typo_line_height'         => '',
					'table_item_typo_letter_spacing'      => '',
					'table_item_typo_text_transform'      => '',
					'table_item_typo_color'               => '',
					'item_link_color_hover'               => '',

					'animation_direction'                 => 'left',
					'animation_offset'                    => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                     => '',
					'animation_delay'                     => '',
					'animation_type'                      => '',
					'animation_color'                     => '',
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
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_woo_order_downloads' );
				$this->wc_order = $this->get_order_object( false );

				// Return if order doesn't exist.
				if ( false === $this->wc_order ) {
					return '';
				}

				$show_downloads = $this->wc_order->has_downloadable_item() && $this->wc_order->is_download_permitted();
				if ( ! $show_downloads ) {
					return '';
				}

				$downloads = $this->wc_order->get_downloadable_items();

				$html = '<section ' . FusionBuilder::attributes( 'fusion_woo_order_downloads-shortcode' ) . '>';

				$html .= '<table class="awb-woo-order-downloads__table shop_table shop_table_responsive order_details">';

				$html .= '<thead>';
				$html .= '<tr>';
				foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) {
					$html .= '<th class="' . esc_attr( $column_id ) . '"><span class="nobr">' . esc_html( $column_name ) . '</span></th>';
				}
				$html .= '</tr>';
				$html .= '</thead>';

				foreach ( $downloads as $download ) {
					$html .= '<tr class="awb-woo-order-downloads__item">';
					foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) {
						$html .= $this->get_column_value( $column_id, $column_name, $download );
					}
					$html .= '</tr>';
				}

				$html .= '</table>';

				$html .= '</section>';

				$this->counter++;
				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Get the column value depending on the context.
			 *
			 * @param string $column_id The column Id.
			 * @param string $column_name The column name.
			 * @param array  $download  The download info.
			 * @return string
			 */
			public function get_column_value( $column_id, $column_name, $download ) {
				$html = '';

				$html .= '<td class="' . esc_attr( $column_id ) . '" data-title="' . esc_attr( $column_name ) . '">';
				if ( has_action( 'woocommerce_account_downloads_column_' . $column_id ) ) {
					ob_start();
					do_action( 'woocommerce_account_downloads_column_' . $column_id, $download );
					$html .= ob_get_clean();
				} else {
					switch ( $column_id ) {
						case 'download-product':
							if ( $download['product_url'] ) {
								$html .= '<a href="' . esc_url( $download['product_url'] ) . '">' . esc_html( $download['product_name'] ) . '</a>';
							} else {
								$html .= esc_html( $download['product_name'] );
							}
							break;
						case 'download-file':
							$html .= '<a href="' . esc_url( $download['download_url'] ) . '" class="woocommerce-MyAccount-downloads-file button alt">' . esc_html( $download['download_name'] ) . '</a>';
							break;
						case 'download-remaining':
							$html .= is_numeric( $download['downloads_remaining'] ) ? esc_html( $download['downloads_remaining'] ) : esc_html__( '&infin;', 'woocommerce' );
							break;
						case 'download-expires':
							if ( ! empty( $download['access_expires'] ) ) {
								$html .= '<time datetime="' . esc_attr( date( 'Y-m-d', strtotime( $download['access_expires'] ) ) ) . '" title="' . esc_attr( strtotime( $download['access_expires'] ) ) . '">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) ) . '</time>';
							} else {
								$html .= esc_html__( 'Never', 'woocommerce' );
							}
							break;
					}
				}
				$html .= '</td>';

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
					'class' => 'awb-woo-order-downloads awb-woo-order-downloads--' . $this->counter,
					'style' => $this->get_style_variables(),
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

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
					'margin_top'                     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'                   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'h_cell_pad_top'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'h_cell_pad_right'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'h_cell_pad_bottom'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'h_cell_pad_left'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'b_cell_pad_top'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'b_cell_pad_right'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'b_cell_pad_bottom'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'b_cell_pad_left'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'h_cell_bg'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'b_cell_bg'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'border_s',
					'border_w'                       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_c'                       => [ 'callback' => 'Fusion_Sanitize::color' ],

					'table_h_typo_font_size'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_h_typo_letter_spacing'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_h_typo_line_height',
					'table_h_typo_text_transform',
					'table_h_typo_color'             => [ 'callback' => 'Fusion_Sanitize::color' ],

					'table_item_typo_font_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_item_typo_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_item_typo_line_height',
					'table_item_typo_text_transform',
					'table_item_typo_color'          => [ 'callback' => 'Fusion_Sanitize::color' ],
					'item_link_color_hover'          => [ 'callback' => 'Fusion_Sanitize::color' ],
				];

				$font_family_vars = $this->get_font_styling_vars( 'table_h_typo' ) . $this->get_font_styling_vars( 'table_item_typo' );

				return $this->get_css_vars_for_options( $css_vars_options ) . $font_family_vars;
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-order-downloads.min.css' );
			}
		}
	}

	new Fusion_Woo_Order_Downloads();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.10
 */
function fusion_component_woo_order_downloads() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'Fusion_Woo_Order_Downloads',
			[
				'name'      => esc_attr__( 'Woo Order Downloads', 'fusion-builder' ),
				'shortcode' => 'fusion_woo_order_downloads',
				'icon'      => 'fusiona-woo-order-downloads',
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
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Heading Cell Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 1px 1px 15px 1px value.', 'fusion-builder' ),
						'param_name'       => 'h_cell_pad',
						'value'            => [
							'h_cell_pad_top'    => '',
							'h_cell_pad_right'  => '',
							'h_cell_pad_bottom' => '',
							'h_cell_pad_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Body Cell Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 25px 0 25px 0 value.', 'fusion-builder' ),
						'param_name'       => 'b_cell_pad',
						'value'            => [
							'b_cell_pad_top'    => '',
							'b_cell_pad_right'  => '',
							'b_cell_pad_bottom' => '',
							'b_cell_pad_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the heading cell background color. ', 'fusion-builder' ),
						'param_name'  => 'h_cell_bg',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Body Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the table cell background color. ', 'fusion-builder' ),
						'param_name'  => 'b_cell_bg',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],

					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Table Border', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border style of the table.', 'fusion-builder' ),
						'param_name'  => 'border_s',
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
						'heading'     => esc_attr__( 'Table Border Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the width of the border.', 'fusion-builder' ),
						'param_name'  => 'border_w',
						'value'       => '1',
						'min'         => '0',
						'max'         => '15',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'border_s',
								'value'    => 'none',
								'operator' => '!=',
							],
						],
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Table Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the table border, ex: #000.', 'fusion-builder' ),
						'param_name'  => 'border_c',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'border_s',
								'value'    => 'none',
								'operator' => '!=',
							],
						],
					],

					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Heading Cell Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the typography of the heading. Leave empty for the global font family.', 'fusion-builder' ) ),
						'param_name'       => 'table_h_typo',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'table_h_typo',
							'font-size'      => 'table_h_typo_font_size',
							'line-height'    => 'table_h_typo_line_height',
							'letter-spacing' => 'table_h_typo_letter_spacing',
							'text-transform' => 'table_h_typo_text_transform',
							'color'          => 'table_h_typo_color',
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
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Body Cell Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the typography of the content text. Leave empty for the global font family.', 'fusion-builder' ) ),
						'param_name'       => 'table_item_typo',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'table_item_typo',
							'font-size'      => 'table_item_typo_font_size',
							'line-height'    => 'table_item_typo_line_height',
							'letter-spacing' => 'table_item_typo_letter_spacing',
							'text-transform' => 'table_item_typo_text_transform',
							'color'          => 'table_item_typo_color',
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
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Body Cell Link Hover Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the color of the link on hover.', 'fusion-builder' ),
						'param_name'  => 'item_link_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_hover_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],

					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-woo-order-downloads',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_order_downloads' );
