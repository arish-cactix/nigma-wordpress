<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.10
 */

if ( fusion_is_element_enabled( 'fusion_woo_order_details' ) ) {

	if ( ! class_exists( 'Fusion_Woo_Order_Details' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.10
		 */
		class Fusion_Woo_Order_Details extends Fusion_Woo_Component {

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
				parent::__construct( 'fusion_woo_order_details' );
				add_filter( 'fusion_attr_fusion_woo_order_details-shortcode', [ $this, 'attr' ] );
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
					'details_order'                   => 'order_number,order_date,user_email,total_amount,payment_method',
					'failed_message'                  => '',
					'hide_on_mobile'                  => fusion_builder_default_visibility( 'string' ),
					'class'                           => '',
					'id'                              => '',

					'fusion_font_family_item_typo'    => '',
					'fusion_font_variant_item_typo'   => '',
					'item_typo_font_size'             => '',
					'item_typo_line_height'           => '',
					'item_typo_letter_spacing'        => '',
					'item_typo_text_transform'        => '',
					'item_typo_color'                 => '',

					'fusion_font_family_detail_typo'  => '',
					'fusion_font_variant_detail_typo' => '',
					'detail_typo_font_size'           => '',
					'detail_typo_line_height'         => '',
					'detail_typo_letter_spacing'      => '',
					'detail_typo_text_transform'      => '',
					'detail_typo_color'               => '',

					'failed_btn_color'                => '',
					'failed_btn_bg_color'             => '',
					'failed_btn_border_c'             => '',
					'failed_btn_color_hover'          => '',
					'failed_btn_bg_color_hover'       => '',
					'failed_btn_border_c_hover'       => '',

					'btn_padding_top'                 => '',
					'btn_padding_right'               => '',
					'btn_padding_bottom'              => '',
					'btn_padding_left'                => '',

					'failed_btn_border_w'             => '',

					'btn_radius_top_left'             => '',
					'btn_radius_top_right'            => '',
					'btn_radius_bottom_right'         => '',
					'btn_radius_bottom_left'          => '',

					'btn_distance'                    => '',
					'btn_msg_distance'                => '',

					'margin_top'                      => '',
					'margin_right'                    => '',
					'margin_bottom'                   => '',
					'margin_left'                     => '',

					'animation_direction'             => 'left',
					'animation_offset'                => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                 => '',
					'animation_delay'                 => '',
					'animation_type'                  => '',
					'animation_color'                 => '',
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
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_woo_order_details' );
				$this->wc_order = $this->get_order_object( false );

				// Return if order doesn't exist.
				if ( false === $this->wc_order ) {
					return '';
				}

				$html = '<div ' . FusionBuilder::attributes( 'fusion_woo_order_details-shortcode' ) . '>';

				// Note: Spacing added for empty text node, to be similar to Woo template.

				if ( ! $this->wc_order->has_status( 'failed' ) ) {

					$html .= ' <ul class="awb-woo-order-details__list order_details"> ';

					$details_ordering = explode( ',', $this->args['details_order'] );

					foreach ( $details_ordering as $detail ) {
						switch ( $detail ) {
							case 'order_number':
								$html .= $this->get_order_number_el();
								break;
							case 'order_date':
								$html .= $this->get_order_date_el();
								break;
							case 'user_email':
								$html .= $this->get_billing_email_el();
								break;
							case 'total_amount':
								$html .= $this->get_order_total_el();
								break;
							case 'payment_method':
								$html .= $this->get_payment_method_el();
								break;
						}
					}

					$html .= ' </ul> </div>';

				} else {
					$message = ( ! empty( $this->args['failed_message'] ) ? $this->args['failed_message'] : esc_html__( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ) );
					$html   .= ' <p class="awb-woo-order-details__failed">' . $message . '</p> ';

					$html .= ' <p class="awb-woo-order-details__failed-actions"> ';
					$html .= ' <a href="' . esc_url( $this->wc_order->get_checkout_payment_url() ) . '" class="button pay">' . esc_html__( 'Pay', 'woocommerce' ) . '</a> ';
					if ( is_user_logged_in() ) {
						$html .= ' <a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" class="button pay">' . esc_html__( 'My account', 'woocommerce' ) . '</a>';
					}
					$html .= ' </p> ';
				}

				$this->counter++;
				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Get the list item for order number.
			 *
			 * @return string
			 */
			private function get_order_number_el() {
				$order_number = $this->wc_order->get_order_number();
				if ( 0 === $order_number || '0' === $order_number ) { // LE preview number.
					$order_number = 1234;
				}

				$html  = ' <li class="awb-woo-order-details__order order"> ';
				$html .= esc_html__( 'Order number:', 'woocommerce' ) . ' <strong>' . $order_number . '</strong>';
				$html .= ' </li> ';

				return $html;
			}

			/**
			 * Get the list item for order date.
			 *
			 * @return string
			 */
			private function get_order_date_el() {
				$html  = ' <li class="awb-woo-order-details__date date"> ';
				$html .= esc_html__( 'Date:', 'woocommerce' ) . ' <strong>' . wc_format_datetime( $this->wc_order->get_date_created() ) . '</strong>';
				$html .= ' </li> ';

				return $html;
			}

			/**
			 * Get the list item for billing email.
			 *
			 * @return string
			 */
			private function get_billing_email_el() {
				$html = '';

				$show_email = is_user_logged_in() && $this->wc_order->get_user_id() === get_current_user_id() && $this->wc_order->get_billing_email();
				$show_email = apply_filters( 'awb_woo_order_details_show_email', $show_email );
				if ( $show_email ) :
					$html .= ' <li class="awb-woo-order-details__email"> ';
					$html .= esc_html__( 'Email:', 'woocommerce' ) . ' <strong>' . $this->wc_order->get_billing_email() . '</strong>';
					$html .= ' </li> ';
				endif;

				return $html;
			}

			/**
			 * Get the list item for total amount.
			 *
			 * @return string
			 */
			private function get_order_total_el() {
				$html  = ' <li class="awb-woo-order-details__total total"> ';
				$html .= esc_html__( 'Total:', 'woocommerce' ) . ' <strong>' . $this->wc_order->get_formatted_order_total() . '</strong>';
				$html .= ' </li> ';

				return $html;
			}

			/**
			 * Get the list item for payment method.
			 *
			 * @return string
			 */
			private function get_payment_method_el() {
				$html = '';
				if ( $this->wc_order->get_payment_method_title() ) {
					$html .= ' <li class="awb-woo-order-details__method method"> ';
					$html .= esc_html__( 'Payment method:', 'woocommerce' ) . ' <strong>' . wp_kses_post( $this->wc_order->get_payment_method_title() ) . '</strong>';
					$html .= ' </li> ';
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
					'class' => 'awb-woo-order-details awb-woo-order-details--' . $this->counter,
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
					'item_typo_font_size'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_typo_letter_spacing'   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_typo_line_height',
					'item_typo_text_transform',
					'item_typo_color'            => [ 'callback' => 'Fusion_Sanitize::color' ],

					'detail_typo_font_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'detail_typo_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'detail_typo_line_height',
					'detail_typo_text_transform',
					'detail_typo_color'          => [ 'callback' => 'Fusion_Sanitize::color' ],

					'failed_btn_color'           => [ 'callback' => 'Fusion_Sanitize::color' ],
					'failed_btn_bg_color'        => [ 'callback' => 'Fusion_Sanitize::color' ],
					'failed_btn_border_c'        => [ 'callback' => 'Fusion_Sanitize::color' ],
					'failed_btn_color_hover'     => [ 'callback' => 'Fusion_Sanitize::color' ],
					'failed_btn_bg_color_hover'  => [ 'callback' => 'Fusion_Sanitize::color' ],
					'failed_btn_border_c_hover'  => [ 'callback' => 'Fusion_Sanitize::color' ],

					'btn_padding_top'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'btn_padding_right'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'btn_padding_bottom'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'btn_padding_left'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'failed_btn_border_w'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'btn_radius_top_left'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'btn_radius_top_right'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'btn_radius_bottom_right'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'btn_radius_bottom_left'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'btn_distance'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'btn_msg_distance'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

					'margin_top'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];
				$font_family_vars = $this->get_font_styling_vars( 'item_typo' ) . $this->get_font_styling_vars( 'detail_typo' );

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $font_family_vars;

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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-order-details.min.css' );
			}
		}
	}

	new Fusion_Woo_Order_Details();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.10
 */
function fusion_component_woo_order_details() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'Fusion_Woo_Order_Details',
			[
				'name'      => esc_attr__( 'Woo Order Details', 'fusion-builder' ),
				'shortcode' => 'fusion_woo_order_details',
				'icon'      => 'fusiona-woo-order-received-details',
				'component' => true,
				'templates' => [ 'content' ],
				'params'    => [
					[
						'type'        => 'connected_sortable',
						'heading'     => esc_attr__( 'Order of Details', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the order of order received details.', 'fusion-builder' ),
						'empty'       => esc_attr__( 'Drag order details here to disable them.' ),
						'param_name'  => 'details_order',
						'default'     => 'order_number,order_date,user_email,total_amount,payment_method',
						'choices'     => [
							'order_number'   => esc_attr__( 'Order Number', 'fusion-builder' ),
							'order_date'     => esc_attr__( 'Order Date', 'fusion-builder' ),
							'user_email'     => esc_attr__( 'User Email', 'fusion-builder' ),
							'total_amount'   => esc_attr__( 'Total Amount', 'fusion-builder' ),
							'payment_method' => esc_attr__( 'Payment Method', 'fusion-builder' ),
						],
					],

					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Failed Message', 'fusion-builder' ),
						'description' => esc_attr__( 'Show a custom message if the order fails. Leave empty for default Woo message translated in displayed language. You can click preview button to see how this message is displayed in case payment fails.', 'fusion-builder' ),
						'param_name'  => 'failed_message',
						'default'     => '',
						'preview'     => [
							'selector' => '.awb-woo-order-details',
							'type'     => 'class',
							'toggle'   => 'awb-woo-order-details__le-switch-failed',
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
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Item/Failed Message Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the item typography.', 'fusion-builder' ) ),
						'param_name'       => 'item_typo',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'item_typo',
							'font-size'      => 'item_typo_font_size',
							'line-height'    => 'item_typo_line_height',
							'letter-spacing' => 'item_typo_letter_spacing',
							'text-transform' => 'item_typo_text_transform',
							'color'          => 'item_typo_color',
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
						'heading'          => esc_attr__( 'Item Detail Typography', 'fusion-builder' ),
						'description'      => sprintf( esc_html__( 'Controls the item detail(information) typography.', 'fusion-builder' ) ),
						'param_name'       => 'detail_typo',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'detail_typo',
							'font-size'      => 'detail_typo_font_size',
							'line-height'    => 'detail_typo_line_height',
							'letter-spacing' => 'detail_typo_letter_spacing',
							'text-transform' => 'detail_typo_text_transform',
							'color'          => 'detail_typo_color',
						],
						'default'          => [
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
					],

					// buttons group.
					[
						'type'             => 'subgroup',
						'heading'          => esc_attr__( 'Failed Button Colors', 'fusion-builder' ),
						'description'      => esc_attr__( 'Set custom colors for the failed buttons.', 'fusion-builder' ),
						'param_name'       => 'buttons_colors',
						'default'          => 'regular',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_attr__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_attr__( 'Hover', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'preview'          => [
							'selector' => '.awb-woo-order-details',
							'type'     => 'class',
							'toggle'   => 'awb-woo-order-details__le-switch-failed',
						],
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the color of the text of buttons.', 'fusion-builder' ),
						'param_name'  => 'failed_btn_color',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'buttons_colors',
							'tab'  => 'regular',
						],
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the background color of buttons.', 'fusion-builder' ),
						'param_name'  => 'failed_btn_bg_color',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'buttons_colors',
							'tab'  => 'regular',
						],
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the border color of buttons.', 'fusion-builder' ),
						'param_name'  => 'failed_btn_border_c',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'buttons_colors',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'failed_btn_border_w',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Color Hover', 'fusion-builder' ),
						'description' => esc_html__( 'Select the color of the text of buttons on hover.', 'fusion-builder' ),
						'param_name'  => 'failed_btn_color_hover',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'buttons_colors',
							'tab'  => 'hover',
						],
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Background Color Hover', 'fusion-builder' ),
						'description' => esc_html__( 'Select the background color of buttons on hover.', 'fusion-builder' ),
						'param_name'  => 'failed_btn_bg_color_hover',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'buttons_colors',
							'tab'  => 'hover',
						],
					],

					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color Hover', 'fusion-builder' ),
						'description' => esc_html__( 'Select the border color of buttons on hover.', 'fusion-builder' ),
						'param_name'  => 'failed_btn_border_c_hover',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'buttons_colors',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'failed_btn_border_w',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],

					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Button Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'btn_padding',
						'value'            => [
							'btn_padding_top'    => '',
							'btn_padding_right'  => '',
							'btn_padding_bottom' => '',
							'btn_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'preview'          => [
							'selector' => '.awb-woo-order-details',
							'type'     => 'class',
							'toggle'   => 'awb-woo-order-details__le-switch-failed',
						],
					],

					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Button Border Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the width of the button border.', 'fusion-builder' ),
						'param_name'  => 'failed_btn_border_w',
						'value'       => '0',
						'min'         => '0',
						'max'         => '10',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'preview'     => [
							'selector' => '.awb-woo-order-details',
							'type'     => 'class',
							'toggle'   => 'awb-woo-order-details__le-switch-failed',
						],
					],

					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Button Border Radius', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'btn_border_radius',
						'value'            => [
							'btn_radius_top_left'     => '',
							'btn_radius_top_right'    => '',
							'btn_radius_bottom_right' => '',
							'btn_radius_bottom_left'  => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'preview'          => [
							'selector' => '.awb-woo-order-details',
							'type'     => 'class',
							'toggle'   => 'awb-woo-order-details__le-switch-failed',
						],
					],

					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Buttons Distance From Paragraph', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'btn_msg_distance',
						'value'            => [
							'btn_msg_distance' => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'preview'          => [
							'selector' => '.awb-woo-order-details',
							'type'     => 'class',
							'toggle'   => 'awb-woo-order-details__le-switch-failed',
						],
					],

					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Distance Between Buttons', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'btn_distance',
						'value'            => [
							'btn_distance' => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'preview'          => [
							'selector' => '.awb-woo-order-details',
							'type'     => 'class',
							'toggle'   => 'awb-woo-order-details__le-switch-failed',
						],
					],

					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-woo-order-details',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_order_details' );
