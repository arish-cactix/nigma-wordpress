<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_breadcrumbs' ) ) {

	if ( ! class_exists( 'FusionSC_Breadcrumbs' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionSC_Breadcrumbs extends Fusion_Element {

			/**
			 * The breadcrumbs counter.
			 *
			 * @access private
			 * @since 2.2
			 * @var int
			 */
			private $breadcrumbs_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since  2.2
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_breadcrumbs-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_pipe_seprator_shortcodes', [ $this, 'allow_separator' ] );

				add_shortcode( 'fusion_breadcrumbs', [ $this, 'render' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_breadcrumbs', [ $this, 'ajax_render' ] );
			}

			/**
			 * Enables pipe separator for short code.
			 *
			 * @access public
			 * @since 2.4
			 * @param array $shortcodes The shortcodes array.
			 * @return array
			 */
			public function allow_separator( $shortcodes ) {
				if ( is_array( $shortcodes ) ) {
					array_push( $shortcodes, 'fusion_breadcrumbs' );
				}

				return $shortcodes;
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'prefix'              => $fusion_settings->get( 'breacrumb_prefix' ),
					'separator'           => $fusion_settings->get( 'breadcrumb_separator' ),
					'show_categories'     => $fusion_settings->get( 'breadcrumb_show_categories' ),
					'post_type_archive'   => $fusion_settings->get( 'breadcrumb_show_post_type_archive' ),
					'show_leaf'           => $fusion_settings->get( 'breadcrumb_show_leaf' ),
					'alignment'           => '',
					'font_size'           => $fusion_settings->get( 'breadcrumbs_font_size' ),
					'text_color'          => $fusion_settings->get( 'breadcrumbs_text_color' ),
					'text_hover_color'    => $fusion_settings->get( 'breadcrumbs_text_hover_color' ),
					'margin_bottom'       => '',
					'margin_left'         => '',
					'margin_right'        => '',
					'margin_top'          => '',
					'class'               => '',
					'id'                  => '',
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'animation_direction' => 'left',
					'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'     => '',
					'animation_delay'     => '',
					'animation_type'      => '',
					'animation_color'     => '',
					'sticky_display'      => '',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'breacrumb_prefix'                  => 'prefix',
					'breadcrumb_separator'              => 'separator',
					'breadcrumbs_font_size'             => 'font_size',
					'breadcrumbs_text_color'            => 'text_color',
					'breadcrumbs_text_hover_color'      => 'text_hover_color',
					'breadcrumb_show_categories'        => 'show_categories',
					'breadcrumb_show_post_type_archive' => 'post_type_archive',
					'breadcrumb_show_leaf'              => 'show_leaf',
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$live_request = false;

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$return_data  = [];
					$live_request = true;
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				if ( class_exists( 'Fusion_App' ) && $live_request ) {
					Fusion_App()->set_data();
					Fusion_App()->emulate_wp_query();
					do_action( 'fusion_filter_data' );

					$args = [
						'home_prefix'            => $defaults['prefix'],
						'separator'              => $defaults['separator'],
						'show_post_type_archive' => ( '1' === $defaults['post_type_archive'] || 'yes' === $defaults['post_type_archive'] ? true : false ),
						'show_terms'             => ( '1' === $defaults['show_categories'] || 'yes' === $defaults['show_categories'] ? true : false ),
						'show_leaf'              => ( '1' === $defaults['show_leaf'] || 'yes' === $defaults['show_leaf'] ? true : false ),
					];

					$breadcrumbs                = new Fusion_Breadcrumbs( $args );
					$return_data['breadcrumbs'] = $breadcrumbs->get_element_breadcrumbs();
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since  2.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_breadcrumbs' );

				$args = [
					'home_prefix'            => $this->args['prefix'],
					'separator'              => $this->args['separator'],
					'show_post_type_archive' => ( '1' === $this->args['post_type_archive'] || 'yes' === $this->args['post_type_archive'] ? true : false ),
					'show_terms'             => ( '1' === $this->args['show_categories'] || 'yes' === $this->args['show_categories'] ? true : false ),
					'show_leaf'              => ( '1' === $this->args['show_leaf'] || 'yes' === $this->args['show_leaf'] ? true : false ),
				];

				$breadcrumbs = new Fusion_Breadcrumbs( $args );

				$html  = '<nav ' . FusionBuilder::attributes( 'breadcrumbs-shortcode' ) . '>';
				$html .= $breadcrumbs->get_element_breadcrumbs();
				$html .= '</nav>';

				$this->breadcrumbs_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_breadcrumbs_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public function attr() {
				$css_vars = [
					'margin_top',
					'margin_right',
					'margin_bottom',
					'margin_left',
					'alignment',
					'font_size',
					'text_hover_color',
					'text_color',
				];

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-breadcrumbs',
						'style' => $this->get_css_vars_for_options( $css_vars ),
					]
				);

				$this->args['separator'] = '\\' === $this->args['separator'] ? '\\\\' : $this->args['separator'];
				$attr['style']          .= '--awb-breadcrumb-sep:\'' . $this->args['separator'] . '\';';

				$attr['class'] = function_exists( 'yoast_breadcrumb' ) ? $attr['class'] . ' awb-yoast-breadcrumbs' : $attr['class'];
				$attr['class'] = ( 'fusion-breadcrumbs' !== $attr['class'] && function_exists( 'rank_math_get_breadcrumbs' ) ) ? $attr['class'] . ' awb-rankmath-breadcrumbs' : $attr['class'];

				$attr['class'] .= ' fusion-breadcrumbs-' . $this->breadcrumbs_counter;

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				$attr['aria-label'] = esc_attr__( 'Breadcrumb', 'fusion-buider' );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( '' !== $this->args['alignment'] ) {
					$attr['style'] .= 'text-align:' . $this->args['alignment'] . ';';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/breadcrumbs.min.css' );
			}
		}
	}

	new FusionSC_Breadcrumbs();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 2.2
 */
function fusion_element_breadcrumbs() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Breadcrumbs',
			[
				'name'        => esc_attr__( 'Breadcrumbs', 'fusion-builder' ),
				'shortcode'   => 'fusion_breadcrumbs',
				'icon'        => 'fusiona-breadcrumb',
				'escape_html' => true,
				'help_url'    => 'https://avada.com/documentation/breadcrumbs-element/',
				'params'      => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Prefix', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text before the breadcrumb menu.', 'fusion-builder' ),
						'param_name'  => 'prefix',
						'value'       => '',
						'callback'    => [
							'function' => 'fusion_update_breadcrumbs_prefix',
							'args'     => [
								'selector' => '.fusion-breadcrumbs',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Separator', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the type of separator between each breadcrumb.', 'fusion-builder' ),
						'param_name'  => 'separator',
						'value'       => '',
						'escape_html' => true,
						'callback'    => [
							'function' => 'fusion_update_breadcrumbs_separator',
							'args'     => [
								'selector' => '.fusion-breadcrumbs',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the beadcrumbs alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the breadcrumbs text. Enter value including CSS unit (px, em, rem), ex: 10px', 'fusion-builder' ),
						'param_name'  => 'font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the breadcrumbs font.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'breadcrumbs_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'states'      => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'param_name' => 'text_hover_color',
								'default'    => $fusion_settings->get( 'breadcrumbs_text_hover_color' ),
								'preview'    => [
									'selector' => '.fusion-breadcrumbs span a',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
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
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Post Categories/Terms', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to display the post categories/terms in the breadcrumbs path.', 'fusion-builder' ),
						'param_name'  => 'show_categories',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_breadcrumbs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Post Type Archives', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to display post type archives in the breadcrumbs path.', 'fusion-builder' ),
						'param_name'  => 'post_type_archive',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_breadcrumbs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Post Name', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to display the post name in the breadcrumbs path.', 'fusion-builder' ),
						'param_name'  => 'show_leaf',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_breadcrumbs',
							'ajax'     => true,
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
					'fusion_sticky_visibility_placeholder' => [],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-breadcrumbs',
					],
				],
				'callback'    => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_breadcrumbs',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_breadcrumbs' );
