<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.10
 */

if ( fusion_is_element_enabled( 'fusion_woo_order_table' ) ) {

	if ( ! class_exists( 'Fusion_Woo_Order_Table' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.10
		 */
		class Fusion_Woo_Order_Table extends Fusion_Woo_Component {

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
			 * Whether or not to show purchase note.
			 *
			 * @var bool
			 */
			private $show_purchase_note = false;

			/**
			 * Constructor.
			 *
			 * @since 3.10
			 */
			public function __construct() {
				parent::__construct( 'fusion_woo_order_table' );
				add_filter( 'fusion_attr_fusion_woo_order_table-shortcode', [ $this, 'attr' ] );
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
					'display_meta_downloads'              => 'yes',
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

					'f_cell_pad_top'                      => '',
					'f_cell_pad_right'                    => '',
					'f_cell_pad_bottom'                   => '',
					'f_cell_pad_left'                     => '',

					'h_cell_bg'                           => '',
					'b_cell_bg'                           => '',
					'f_cell_bg'                           => '',

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

					'fusion_font_family_table_footer_h'   => '',
					'fusion_font_variant_table_footer_h'  => '',
					'table_footer_h_font_size'            => '',
					'table_footer_h_line_height'          => '',
					'table_footer_h_letter_spacing'       => '',
					'table_footer_h_text_transform'       => '',
					'table_footer_h_color'                => '',

					'fusion_font_family_table_footer_i'   => '',
					'fusion_font_variant_table_footer_i'  => '',
					'table_footer_i_font_size'            => '',
					'table_footer_i_line_height'          => '',
					'table_footer_i_letter_spacing'       => '',
					'table_footer_i_text_transform'       => '',
					'table_footer_i_color'                => '',

					'fusion_font_family_table_total'      => '',
					'fusion_font_variant_table_total'     => '',
					'table_total_font_size'               => '',
					'table_total_line_height'             => '',
					'table_total_letter_spacing'          => '',
					'table_total_text_transform'          => '',
					'table_total_color'                   => '',

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
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_woo_order_table' );
				$this->wc_order = $this->get_order_object( false );

				// Return if order doesn't exist.
				if ( false === $this->wc_order ) {
					return '';
				}

				$html = ' <section ' . FusionBuilder::attributes( 'fusion_woo_order_table-shortcode' ) . '> ';

				$html .= $this->get_the_table();

				$html .= ' </section> ';

				$this->counter++;
				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Get the table element.
			 *
			 * @return string
			 */
			private function get_the_table() {
				$order_items              = $this->wc_order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
				$this->show_purchase_note = $this->wc_order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', [ 'completed', 'processing' ] ) );

				$html  = ' <div class="avada-order-details"> ';
				$html .= ' <table class="shop_table order_details"> ';

				$html .= ' <thead> ';
				$html .= ' <tr> ';
				$html .= ' <th class="product-name">' . esc_html__( 'Product', 'woocommerce' ) . '</th> ';
				$html .= ' <th class="product-total">' . esc_html__( 'Total', 'woocommerce' ) . '</th> ';
				$html .= ' </tr> ';
				$html .= ' </thead> ';

				$html .= ' <tbody> ';

				ob_start();
				do_action( 'woocommerce_order_details_before_order_table_items', $this->wc_order );
				$html .= ob_get_clean();

				foreach ( $order_items as $item_id => $item ) {
					$html .= $this->get_table_item_row( $item, $item_id );
				}

				ob_start();
				do_action( 'woocommerce_order_details_after_order_table_items', $this->wc_order );
				$html .= ob_get_clean();

				$html .= ' </tbody> ';

				$html .= ' <tfoot> ';

				$rows = $this->wc_order->get_order_item_totals();
				end( $rows ); // move the internal pointer to the end of the array.
				$last_key = key( $rows );
				foreach ( $rows as $key => $total ) {
					$class = 'product-total';
					if ( $last_key === $key ) {
						$class .= ' awb-woo-order-table__total';
					}

					$html .= ' <tr> ';
					$html .= ' <th scope="row">' . $total['label'] . '</th> ';
					$html .= ' <td class="' . $class . '">' . $total['value'] . '</td> ';
					$html .= ' </tr> ';
				}
				if ( $this->wc_order->get_customer_note() ) {
					$html .= ' <tr> ';
					$html .= ' <th scope="row">' . esc_html__( 'Note:', 'woocommerce' ) . '</th> ';
					$html .= ' <td class="product-total awb-woo-order-table__note">' . wp_kses_post( nl2br( wptexturize( $this->wc_order->get_customer_note() ) ) ) . '</td> ';
					$html .= ' </tr> ';
				}

				$html .= ' </tfoot> ';

				$html .= ' </table> ';

				ob_start();
				do_action( 'woocommerce_order_details_after_order_table', $this->wc_order );
				$html .= ob_get_clean();

				ob_start();
				do_action( 'woocommerce_after_order_details', $this->wc_order );
				$html .= ob_get_clean();

				$html .= ' </div> ';

				return $html;
			}

			/**
			 * Get the HTML for table item row.
			 *
			 * @param mixed $item The order product item.
			 * @param int   $item_id The order product item id.
			 * @return string
			 */
			private function get_table_item_row( $item, $item_id ) {
				$product           = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
				$purchase_note     = ( $product ) ? $product->get_purchase_note() : '';
				$is_visible        = $product && $product->is_visible();
				$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $this->wc_order );

				$html = '<tr class="' . esc_attr( apply_filters( 'woocommerce_order_item_class', 'awb-woo-order-table__order-item order_item', $item, $this->wc_order ) ) . '">';

				$html .= '<td class="product-name">';
				$html .= '<div class="fusion-product-name-wrapper">';
				if ( $is_visible ) {
					$html     .= '<span class="product-thumbnail">';
					$thumbnail = $product->get_image();
					if ( ! $product_permalink ) {
						$html .= $thumbnail;
					} else {
						$html .= '<a href="' . esc_url( $product_permalink ) . '">' . $thumbnail . '</a>';
					}
					$html .= '</span>';
				}

				$html .= '<div class="product-info">';
				$html .= wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) );
				$html .= apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', esc_html( $item->get_quantity() ) ) . '</strong>', $item );

				// Meta data.
				ob_start();
				do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $this->wc_order, false );
				$html .= ob_get_clean();

				ob_start();
				wc_display_item_meta( $item );
				if ( 'yes' === $this->args['display_meta_downloads'] ) {
					wc_display_item_downloads( $item );
				}

				$html .= ob_get_clean();

				ob_start();
				do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $this->wc_order, false );
				$html .= ob_get_clean();

				$html .= '</div>';
				$html .= '</div>';
				$html .= '</td>';

				$html .= '<td class="product-total">';
				$html .= $this->wc_order->get_formatted_line_subtotal( $item );
				$html .= '</td>';

				$html .= '</tr>';

				if ( $this->show_purchase_note && $purchase_note ) {
					$html .= '<tr class="product-purchase-note">';
					$html .= '<td colspan="3">' . wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ) . '</td>';
					$html .= '</tr>';
				}

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
					'class' => 'awb-woo-order-table awb-woo-order-table--' . $this->counter,
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

					'f_cell_pad_top'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'f_cell_pad_right'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'f_cell_pad_bottom'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'f_cell_pad_left'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'h_cell_bg'                      => [ 'callback' => 'Fusion_Sanitize::color' ],
					'b_cell_bg'                      => [ 'callback' => 'Fusion_Sanitize::color' ],
					'f_cell_bg'                      => [ 'callback' => 'Fusion_Sanitize::color' ],

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

					'table_footer_h_font_size'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_footer_h_letter_spacing'  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_footer_h_line_height',
					'table_footer_h_text_transform',
					'table_footer_h_color'           => [ 'callback' => 'Fusion_Sanitize::color' ],

					'table_footer_i_font_size'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_footer_i_letter_spacing'  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_footer_i_line_height',
					'table_footer_i_text_transform',
					'table_footer_i_color'           => [ 'callback' => 'Fusion_Sanitize::color' ],

					'table_total_font_size'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_total_letter_spacing'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'table_total_line_height',
					'table_total_text_transform',
					'table_total_color'              => [ 'callback' => 'Fusion_Sanitize::color' ],

				];
				$custom_vars = [];

				if ( ! empty( $this->args['f_cell_pad_right'] ) ) { // By default footer heading cell has custom right padding, make sure to also overwrite that.
					$custom_vars['f_heading_cell_pad_right'] = Fusion_Sanitize::get_value_with_unit( $this->args['f_cell_pad_right'] );
				}

				$font_family_vars = $this->get_font_styling_vars( 'table_h_typo' ) . $this->get_font_styling_vars( 'table_item_typo' ) . $this->get_font_styling_vars( 'table_footer_h' ) . $this->get_font_styling_vars( 'table_footer_i' ) . $this->get_font_styling_vars( 'table_total' );

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars ) . $font_family_vars;

				return $styles;
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-order-table.min.css' );
			}
		}
	}

	new Fusion_Woo_Order_Table();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.10
 */
function fusion_component_woo_order_table() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'Fusion_Woo_Order_Table',
			[
				'name'      => esc_attr__( 'Woo Order Table', 'fusion-builder' ),
				'shortcode' => 'fusion_woo_order_table',
				'icon'      => 'fusiona-woo-order-received-table',
				'component' => true,
				'templates' => [ 'content' ],
				'params'    => [

					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Meta Downloads Links for Digital Products', 'fusion-builder' ),
						'description' => esc_attr__( 'Whether or not to display the download links under the products. This is a more straightforward approach instead of using Woo Order Downloads element. The downside is that the information for expiring download date, and number of downloads remaining are missing from here, unlike Woo Order Downloads element.', 'fusion-builder' ),
						'param_name'  => 'display_meta_downloads',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
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
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Footer Cell Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 10px 0 10px 0 value.', 'fusion-builder' ),
						'param_name'       => 'f_cell_pad',
						'value'            => [
							'f_cell_pad_top'    => '',
							'f_cell_pad_right'  => '',
							'f_cell_pad_bottom' => '',
							'f_cell_pad_left'   => '',
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
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Footer Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the footer table cell background color. ', 'fusion-builder' ),
						'param_name'  => 'f_cell_bg',
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
						'default'     => 'var(--link_hover_color)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],

					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Footer Heading Cell Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the typography of the heading in footer. Leave empty for the global font family.', 'fusion-builder' ) ),
						'param_name'       => 'table_footer_h',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'table_footer_h',
							'font-size'      => 'table_footer_h_font_size',
							'line-height'    => 'table_footer_h_line_height',
							'letter-spacing' => 'table_footer_h_letter_spacing',
							'text-transform' => 'table_footer_h_text_transform',
							'color'          => 'table_footer_h_color',
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
						'heading'          => esc_attr__( 'Footer Data Cell Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the typography of the data in footer. Leave empty for the global font family.', 'fusion-builder' ) ),
						'param_name'       => 'table_footer_i',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'table_footer_i',
							'font-size'      => 'table_footer_i_font_size',
							'line-height'    => 'table_footer_i_line_height',
							'letter-spacing' => 'table_footer_i_letter_spacing',
							'text-transform' => 'table_footer_i_text_transform',
							'color'          => 'table_footer_i_color',
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
						'heading'          => esc_attr__( 'Total Price Cell Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the typography of the total price. Leave empty for the global font family.', 'fusion-builder' ) ),
						'param_name'       => 'table_total',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'table_total',
							'font-size'      => 'table_total_font_size',
							'line-height'    => 'table_total_line_height',
							'letter-spacing' => 'table_total_letter_spacing',
							'text-transform' => 'table_total_text_transform',
							'color'          => 'table_total_color',
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

					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-woo-order-table',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_order_table' );
