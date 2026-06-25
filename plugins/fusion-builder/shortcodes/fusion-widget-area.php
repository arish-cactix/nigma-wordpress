<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_widget_area' ) ) {

	if ( ! class_exists( 'FusionSC_WidgetArea' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_WidgetArea extends Fusion_Element {

			/**
			 * Counter for widgets.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $widget_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_widget-area-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_widget_area', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_widget_area', [ $this, 'query' ] );
			}

			/**
			 * Get the shortcode markup.
			 *
			 * @access public
			 * @since 2.0
			 * @return void.
			 */
			public function query() {

				$return_data = [];
				ob_start();
				$name = '';
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && isset( $_POST['model']['params']['name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$name = sanitize_text_field( wp_unslash( $_POST['model']['params']['name'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				}
				if ( '' === $name ) {
					return;
				}
				if ( function_exists( 'dynamic_sidebar' ) ) {
					dynamic_sidebar( $name );
				}
				$return_data[ str_replace( '-', '_', $name ) ] = ob_get_clean();
				echo wp_json_encode( $return_data );
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
					'hide_on_mobile'   => fusion_builder_default_visibility( 'string' ),
					'class'            => '',
					'id'               => '',
					'background_color' => '',
					'name'             => '',
					'margin_top'       => '',
					'margin_right'     => '',
					'margin_bottom'    => '',
					'margin_left'      => '',
					'padding'          => '',
					'title_color'      => $fusion_settings->get( 'widget_area_title_color' ),
					'title_size'       => $fusion_settings->get( 'widget_area_title_size' ),
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'widget_area_title_color' => 'title_color',
					'widget_area_title_size'  => 'title_size',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$defaults = self::get_element_defaults();
				if ( ! isset( $args['padding'] ) ) {
					$padding_values           = [];
					$padding_values['top']    = ( isset( $args['padding_top'] ) && '' !== $args['padding_top'] ) ? $args['padding_top'] : '0px';
					$padding_values['right']  = ( isset( $args['padding_right'] ) && '' !== $args['padding_right'] ) ? $args['padding_right'] : '0px';
					$padding_values['bottom'] = ( isset( $args['padding_bottom'] ) && '' !== $args['padding_bottom'] ) ? $args['padding_bottom'] : '0px';
					$padding_values['left']   = ( isset( $args['padding_left'] ) && '' !== $args['padding_left'] ) ? $args['padding_left'] : '0px';

					$defaults['padding'] = implode( ' ', $padding_values );
				}

				$defaults = FusionBuilder::set_shortcode_defaults( $defaults, $args, 'fusion_widget_area' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_widget_area', $args );

				$defaults['padding'] = FusionBuilder::validate_shortcode_attr_value( $defaults['padding'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$html = '<div ' . FusionBuilder::attributes( 'widget-area-shortcode' ) . '>';

				ob_start();
				if ( function_exists( 'dynamic_sidebar' ) && dynamic_sidebar( $name ) ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
					// All is good, dynamic_sidebar() already called the rendering.
				}
				$html .= ob_get_clean();

				$html .= '<div ' . FusionBuilder::attributes( 'fusion-additional-widget-content' ) . '>';
				$html .= do_shortcode( $content );
				$html .= '</div>';
				$html .= '</div>';

				$this->widget_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_widget_area_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$hide_on_mobile = ( isset( $this->args['hide_on_mobile'] ) ) ? $this->args['hide_on_mobile'] : '';
				$attr           = fusion_builder_visibility_atts(
					$hide_on_mobile,
					[
						'class' => 'fusion-widget-area awb-widget-area-element fusion-widget-area-' . $this->widget_counter . ' fusion-content-widget-area',
						'style' => '',
					]
				);

				$attr['style'] .= $this->get_style_variables();

				if ( isset( $this->args['class'] ) && $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( isset( $this->args['id'] ) && $this->args['id'] ) {
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

				if ( $this->args['padding'] ) {
					if ( strpos( $this->args['padding'], '%' ) === false && strpos( $this->args['padding'], 'px' ) === false ) {
						$this->args['padding'] = $this->args['padding'] . 'px';
					}

					$_padding               = fusion_library()->sanitize->get_value_with_unit( $this->args['padding'] );
					$custom_vars['padding'] = $_padding;
				}

				$css_vars_options = [
					'margin_top'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'title_size'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'background_color' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'title_color'      => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/widget-area.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Widget Area settings.
			 */
			public function add_options() {

				return [
					'widget_area_shortcode_section' => [
						'label'       => esc_html__( 'Widget Area', 'fusion-builder' ),
						'description' => '',
						'id'          => 'widget_area_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-sidebar',
						'fields'      => [
							'widget_area_title_size'  => [
								'label'       => esc_html__( 'Widget Title Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the size of widget titles. In pixels.', 'fusion-builder' ),
								'id'          => 'widget_area_title_size',
								'default'     => apply_filters( 'fusion_builder_widget_area_title_size', '' ),
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'widget_area_title_color' => [
								'label'       => esc_html__( 'Widget Title Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of widget titles.', 'fusion-builder' ),
								'id'          => 'widget_area_title_color',
								'default'     => apply_filters( 'fusion_builder_widget_area_title_color', 'var(--awb-color8)' ),
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}
		}
	}

	new FusionSC_WidgetArea();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_widget_area() {
	$fusion_settings = awb_get_fusion_settings();
	$widget_areas    = AWB_Widget_Framework()->get_widget_areas();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_WidgetArea',
			[
				'name'      => esc_attr__( 'Widget Area', 'fusion-builder' ),
				'shortcode' => 'fusion_widget_area',
				'icon'      => 'fusiona-sidebar',
				'help_url'  => 'https://avada.com/documentation/widget-area-element/',
				'params'    => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Widget Area Name', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the name of the widget area to display.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => $widget_areas,
						'default'     => function_exists( 'fusion_get_array_default' ) ? fusion_get_array_default( $widget_areas ) : '',
						'callback'    => [
							'function' => 'fusion_widget_area',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Widget Title Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the size of widget titles. In pixels ex: 18px.', 'fusion-builder' ),
						'param_name'  => 'title_size',
						'value'       => '',
						'default'     => $fusion_settings->get( 'widget_area_title_size' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Widget Title Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of widget titles.', 'fusion-builder' ),
						'param_name'  => 'title_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'widget_area_title_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a background color for the widget area.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
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
				'callback'  => [
					'function' => 'fusion_widget_area',
					'ajax'     => true,
				],
			]
		)
	);
}

// Later hook to ensure the sidebars are set.
add_action( 'fusion_builder_wp_loaded', 'fusion_element_widget_area' );
