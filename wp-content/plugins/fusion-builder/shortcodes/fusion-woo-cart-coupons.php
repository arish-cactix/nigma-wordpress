<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooCartCoupons' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_WooCartCoupons extends Fusion_Element {

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
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_woo-cart-coupons-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_woo_cart_coupons', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_fusion_get_woo_cart_coupons', [ $this, 'ajax_query' ] );
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
				$this->args = $_POST['model']['params']; // phpcs:ignore WordPress.Security
				$html       = $this->generate_element_content();

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
					'margin_top'                => '',
					'margin_right'              => '',
					'margin_bottom'             => '',
					'margin_left'               => '',

					'hide_on_mobile'            => fusion_builder_default_visibility( 'string' ),
					'class'                     => '',
					'id'                        => '',

					// Fields.
					'field_bg_color'            => $fusion_settings->get( 'form_bg_color' ),
					'field_text_color'          => $fusion_settings->get( 'form_text_color' ),
					'field_border_color'        => $fusion_settings->get( 'form_border_color' ),
					'field_border_focus_color'  => $fusion_settings->get( 'form_focus_border_color' ),

					// Animation.
					'animation_type'            => '',
					'animation_direction'       => 'down',
					'animation_speed'           => '0.1',
					'animation_delay'           => '',
					'animation_offset'          => $fusion_settings->get( 'animation_offset' ),
					'animation_color'           => '',

					// Alignment.
					'buttons_layout'            => '',
					'stacked_buttons_alignment' => '',
					'button_span'               => '',

					// Button margin.
					'button_margin_top'         => '',
					'button_margin_right'       => '',
					'button_margin_bottom'      => '',
					'button_margin_left'        => '',
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
				if ( ! wc_coupons_enabled() || ! is_object( WC()->cart ) || ( WC()->cart->is_empty() && ! fusion_is_preview_frame() ) ) {
					return;
				}
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_woo_cart_totals' );

				ob_start();
				?>
				<div <?php echo FusionBuilder::attributes( 'woo-cart-coupons-shortcode' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo $this->generate_element_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php
				$html = ob_get_clean();
				if ( is_checkout() ) {
					$html = '<form  class="fusion-woo-cart-coupons-checkout-form checkout_coupon">' . $html . '</form>';
				}

				$this->on_render();
				$this->counter++;
				return apply_filters( 'fusion_element_cart_coupons_content', $html, $args );
			}


			/**
			 * Generates element content
			 *
			 *  * @access public
			 *
			 * @since 3.3
			 * @return string
			 */
			public function generate_element_content() {
				$submit_button_class = ! is_checkout() ? ' fusion-apply-coupon' : '';
				ob_start();
				?>
					<div class="avada-coupon-fields">
						<label for="avada_coupon_code"><?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?></label>
						<input type="text" name="coupon_code" class="input-text" id="avada_coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
						<button type="submit" class="fusion-button button-default fusion-button-default-size button<?php echo $submit_button_class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?></button>
					</div>
					<?php do_action( 'woocommerce_cart_coupon' ); ?>
				<?php
				return ob_get_clean();
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
					'button_margin_top'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_margin_bottom'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_margin_left'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'button_margin_right'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'stacked_buttons_alignment',
				];

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'coupon fusion-woo-cart_coupons fusion-woo-cart_coupons-' . $this->counter,
						'style' => '',
					]
				);

				if ( $this->args['class'] ) {
					$attr['class'] .= '  ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( '' !== $this->args['buttons_layout'] ) {
					$attr['class'] .= '  buttons-layout-' . $this->args['buttons_layout'];
				}

				if ( 'yes' === $this->args['button_span'] ) {
					$attr['class'] .= '  buttons-span-yes';
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= $this->get_style_variables();

				return $attr;
			}


			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-cart-coupons.min.css' );
			}
		}
	}

	new FusionSC_WooCartCoupons();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_cart_coupons() {
	$fusion_settings = awb_get_fusion_settings();
	if ( class_exists( 'WooCommerce' ) ) {
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooCartCoupons',
				[
					'name'          => esc_attr__( 'Woo Cart Coupons', 'fusion-builder' ),
					'shortcode'     => 'fusion_woo_cart_coupons',
					'icon'          => 'fusiona-cart-coupons',
					'help_url'      => '',
					'inline_editor' => true,
					'params'        => [
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
							'param_name'       => 'margin',
							'callback'         => [
								'function' => 'fusion_style_block',
								'args'     => [

									'dimension' => true,
								],
							],
							'value'            => [
								'margin_top'    => '',
								'margin_right'  => '',
								'margin_bottom' => '',
								'margin_left'   => '',
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
									'element'  => 'buttons_layout',
									'value'    => 'stacked',
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
									'element'  => 'button_span',
									'value'    => 'yes',
									'operator' => '!=',
								],
							],

						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Button Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
							'param_name'       => 'buttonsmargin',
							'value'            => [
								'button_margin_top'    => '',
								'button_margin_right'  => '',
								'button_margin_bottom' => '',
								'button_margin_left'   => '',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Form Field Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the background color of the form input fields.', 'fusion-builder' ),
							'param_name'  => 'field_bg_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'form_bg_color' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Form Field Text Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the text color of the form input fields.', 'fusion-builder' ),
							'param_name'  => 'field_text_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'form_text_color' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Field Border Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the border color of the form input fields.', 'fusion-builder' ),
							'param_name'  => 'field_border_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'form_border_color' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Field Border Color On Focus', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the border color of the form input fields on focus.', 'fusion-builder' ),
							'param_name'  => 'field_border_focus_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'form_focus_border_color' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						],
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-woo-cart_coupons',
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
						'action'   => 'fusion_get_woo_cart_coupons',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'fusion_builder_wp_loaded', 'fusion_element_woo_cart_coupons' );
