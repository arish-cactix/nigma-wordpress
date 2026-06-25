<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_text' ) ) {

	if ( ! class_exists( 'FusionSC_FusionText' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FusionText extends Fusion_Element {

			/**
			 * The text counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $text_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_text-element-wrapper', [ $this, 'wrapper_attr' ] );

				add_shortcode( 'fusion_text', [ $this, 'render' ] );

				add_filter( 'fusion_text_content', 'shortcode_unautop' );
				add_filter( 'fusion_text_content', 'do_shortcode' );
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
					'animation_direction'           => 'left',
					'animation_offset'              => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'               => '',
					'animation_delay'               => '',
					'animation_type'                => '',
					'animation_color'               => '',
					'class'                         => '',
					'columns'                       => $fusion_settings->get( 'text_columns' ),
					'column_min_width'              => $fusion_settings->get( 'text_column_min_width' ),
					'column_spacing'                => $fusion_settings->get( 'text_column_spacing' ),
					'font_size'                     => '',
					'fusion_font_family_text_font'  => '',
					'fusion_font_variant_text_font' => '',
					'line_height'                   => '',
					'letter_spacing'                => '',
					'text_color'                    => '',
					'text_transform'                => '',
					'hide_on_mobile'                => fusion_builder_default_visibility( 'string' ),
					'sticky_display'                => '',
					'id'                            => '',
					'rule_color'                    => $fusion_settings->get( 'text_rule_color' ),
					'rule_size'                     => $fusion_settings->get( 'text_rule_size' ),
					'rule_style'                    => $fusion_settings->get( 'text_rule_style' ),
					'content_alignment'             => '',
					'content_alignment_medium'      => '',
					'content_alignment_small'       => '',
					'margin_bottom'                 => '',
					'margin_left'                   => '',
					'margin_right'                  => '',
					'margin_top'                    => '',
					'logics'                        => '',
					'user_select'                   => '',
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
					'text_rule_style'       => 'rule_style',
					'text_rule_size'        => 'rule_size',
					'text_rule_color'       => 'rule_color',
					'text_user_select'      => 'user_select',
					'text_column_spacing'   => 'column_spacing',
					'text_column_min_width' => 'column_min_width',
					'text_columns'          => 'columns',
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
				$fusion_settings = awb_get_fusion_settings();

				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_text' );

				$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_text', $args );

				$this->set_element_id( $this->text_counter );

				if ( 'default' === $this->args['rule_style'] ) {
					$this->args['rule_style'] = $fusion_settings->get( 'text_rule_style' );
				}

				if ( '' !== $this->args['logics'] ) {
					// Add form element data to a form.
					$this->add_field_data_to_form();
				}

				$html = '<div ' . FusionBuilder::attributes( 'text-element-wrapper' ) . '>' . wpautop( $content, false ) . '</div>';

				fusion_element_rendering_elements( true );
				$html = apply_filters( 'fusion_text_content', $html, $content );
				fusion_element_rendering_elements( false );

				$this->text_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_text_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = [
					'class' => 'fusion-text fusion-text-' . $this->element_id,
					'style' => $this->get_style_vars(),
				];

				if ( fusion_builder_container()->is_flex() ) {

					if ( ! empty( $this->args['content_alignment_medium'] ) && $this->args['content_alignment'] !== $this->args['content_alignment_medium'] ) {
						$attr['class'] .= ' md-text-align-' . $this->args['content_alignment_medium'];
					}

					if ( ! empty( $this->args['content_alignment_small'] ) && $this->args['content_alignment'] !== $this->args['content_alignment_small'] ) {
						$attr['class'] .= ' sm-text-align-' . $this->args['content_alignment_small'];
					}
				}

				// Only add styling if more than one column is used.
				if ( 1 < $this->args['columns'] ) {
					$attr['class'] .= ' awb-text-cols fusion-text-columns-' . $this->args['columns'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( '' !== $this->args['margin_bottom'] ) {
					$attr['class'] .= ' fusion-text-no-margin';
				}

				// Hide field if it has got logics.
				if ( isset( $this->args['logics'] ) && '' !== $this->args['logics'] && '[]' !== base64_decode( $this->args['logics'] ) ) {
					$attr['data-form-element-name'] = 'fusion_text_' . $this->element_id;
					$attr['class']                 .= ' fusion-form-field-hidden';
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
			 * Get the style vars.
			 *
			 * @return string
			 */
			public function get_style_vars() {
				$sanitize        = fusion_library()->sanitize;
				$css_vars        = [
					'content_alignment',
					'font_size'      => [ 'callback' => [ $sanitize, 'get_value_with_unit' ] ],
					'line_height'    => [ 'callback' => [ $sanitize, 'size' ] ],
					'letter_spacing' => [ 'callback' => [ $sanitize, 'get_value_with_unit' ] ],
					'text_transform',
					'text_color'     => [ 'callback' => [ $sanitize, 'color' ] ],
					'user_select',
				];
				$custom_css_vars = [];

				// Only add styling if more than one column is used.
				if ( 1 < $this->args['columns'] ) {
					array_push( $css_vars, 'columns' );

					if ( $this->args['column_spacing'] ) {
						$custom_css_vars['column_spacing'] = FusionBuilder::validate_shortcode_attr_value( $this->args['column_spacing'], 'px' );
					}

					if ( $this->args['column_min_width'] ) {
						$custom_css_vars['column_min_width'] = FusionBuilder::validate_shortcode_attr_value( $this->args['column_min_width'], 'px' );
					}

					if ( 'none' !== $this->args['rule_style'] ) {
						$custom_css_vars['rule_style'] = $this->args['rule_size'] . 'px ' . $this->args['rule_style'] . ' ' . $this->args['rule_color'];
					}
				}

				$margin    = Fusion_Builder_Margin_Helper::get_margin_vars( $this->args );
				$font_vars = $this->get_font_styling_vars( 'text_font' );

				return $this->get_css_vars_for_options( $css_vars ) . $this->get_custom_css_vars( $custom_css_vars ) . $margin . $font_vars;
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'visibility_medium' => $fusion_settings->get( 'visibility_medium' ),
					'visibility_small'  => $fusion_settings->get( 'visibility_small' ),
				];
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/text.min.css' );
			}

			/**
			 * Adds field data to the form.
			 *
			 * @access public
			 * @since 3.10.2
			 * @return void
			 */
			public function add_field_data_to_form() {
				global $fusion_form;

				if ( ! isset( $fusion_form['form_fields'] ) ) {
					$fusion_form['form_fields'] = [];
				}

				$fusion_form['form_fields'][] = 'fusion_text';

				if ( isset( $this->args['label'] ) ) {
					$fusion_form['field_labels'][ $this->args['name'] ] = $this->args['label'];
				}

				$field_name = str_replace( 'fusion_form_', '', 'fusion_text' );
				$name       = isset( $this->args['name'] ) ? $this->args['name'] : $field_name . '_' . $this->text_counter;

				if ( isset( $this->args['logics'] ) ) {
					$fusion_form['field_logics'][ $name ] = base64_decode( $this->args['logics'] );
				}
				$fusion_form['field_types'][ $name ] = $field_name;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.5
			 * @return array $sections Title settings.
			 */
			public function add_options() {
				return [
					'text_shortcode_section' => [
						'label'       => esc_html__( 'Text Block', 'fusion-builder' ),
						'description' => '',
						'id'          => 'text_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-font',
						'fields'      => [
							'text_columns'          => [
								'label'       => esc_html__( 'Number Of Inline Columns', 'fusion-builder' ),
								'description' => __( 'Set the number of columns the text should be broken into.<br />IMPORTANT: This feature is designed to be used for running text, images, dropcaps and other inline content. While some block elements will work, their usage is not recommended and others can easily break the layout.', 'fusion-builder' ),
								'id'          => 'text_columns',
								'default'     => '1',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '6',
									'step' => '1',
								],
								'css_vars'    => [
									[
										'name' => '--text_columns',
									],
								],
							],
							'text_column_min_width' => [
								'label'           => esc_html__( 'Column Min Width', 'fusion-builder' ),
								'description'     => esc_html__( 'Set the minimum width for each column, this allows your columns to gracefully break into the selected size as the screen width narrows. Leave this option empty if you wish to keep the same amount of columns from desktop to mobile.', 'fusion-builder' ),
								'id'              => 'text_column_min_width',
								'default'         => '100px',
								'type'            => 'dimension',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name' => '--text_column_min_width',
									],
								],
							],
							'text_column_spacing'   => [
								'label'           => esc_html__( 'Column Spacing', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the column spacing between one column to the next.', 'fusion-builder' ),
								'id'              => 'text_column_spacing',
								'default'         => '2em',
								'type'            => 'dimension',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name' => '--text_column_spacing',
									],
								],
							],
							'text_rule_style'       => [
								'label'           => esc_html__( 'Rule Style', 'fusion-builder' ),
								'description'     => esc_html__( 'Select the style of the vertical line between columns. Some of the styles depend on the rule size and color.', 'fusion-builder' ),
								'id'              => 'text_rule_style',
								'default'         => 'none',
								'transport'       => 'postMessage',
								'type'            => 'select',
								'choices'         => [
									'none'   => esc_html__( 'None', 'fusion-builder' ),
									'solid'  => esc_html__( 'Solid', 'fusion-builder' ),
									'dashed' => esc_html__( 'Dashed', 'fusion-builder' ),
									'dotted' => esc_html__( 'Dotted', 'fusion-builder' ),
									'double' => esc_html__( 'Double', 'fusion-builder' ),
									'groove' => esc_html__( 'Groove', 'fusion-builder' ),
									'ridge'  => esc_html__( 'Ridge', 'fusion-builder' ),
								],
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name' => '--text_rule_style',
									],
								],
							],
							'text_rule_size'        => [
								'label'           => esc_html__( 'Rule Size', 'fusion-builder' ),
								'description'     => esc_attr__( 'Sets the size of the vertical line between columns. The rule is rendered as "below" spacing and columns, so it can span over the gap between columns if it is larger than the column spacing amount.', 'fusion-builder' ),
								'id'              => 'text_rule_size',
								'default'         => '1',
								'type'            => 'slider',
								'transport'       => 'postMessage',
								'choices'         => [
									'min'  => '1',
									'max'  => '50',
									'step' => '1',
								],
								'soft_dependency' => true,
							],
							'text_rule_color'       => [
								'label'           => esc_html__( 'Rule Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the vertical line between columns.', 'fusion-builder' ),
								'id'              => 'text_rule_color',
								'default'         => 'var(--awb-color3)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'text_user_select'  => [
								'type'        => 'radio-buttonset',
								'label'       => esc_html__( 'User Text Select', 'fusion-builder' ),
								'description' => esc_html__( 'Controls how and if the text can be selected.', 'fusion-builder' ),
								'id'          => 'text_user_select',
								'default'     => 'auto',
								'transport'   => 'postMessage',
								'choices'     => [
									'auto'  => esc_html__( 'Selectable', 'fusion-builder' ),
									'all'   => esc_html__( 'All', 'fusion-builder' ),
									'none'  => esc_html__( 'Not Selectable', 'fusion-builder' ),
								],
								'css_vars'        => [
									[
										'name' => '--text_user_select',
									],
								],								
							],							
						],
					],
				];
			}
		}
	}

	new FusionSC_FusionText();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_text() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FusionText',
			[
				'name'            => esc_attr__( 'Text Block', 'fusion-builder' ),
				'shortcode'       => 'fusion_text',
				'icon'            => 'fusiona-font',
				'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-text-preview.php',
				'preview_id'      => 'fusion-builder-block-module-text-preview-template',
				'allow_generator' => true,
				'inline_editor'   => true,
				'help_url'        => 'https://avada.com/documentation/text-block-element/',
				'subparam_map'    => [
					'fusion_font_family_text_font'  => 'main_typography',
					'fusion_font_variant_text_font' => 'main_typography',
					'font_size'                     => 'main_typography',
					'line_height'                   => 'main_typography',
					'letter_spacing'                => 'main_typography',
					'text_transform'                => 'main_typography',
				],
				'params'          => [
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number Of Inline Columns', 'fusion-builder' ),
						'description' => __( 'Set the number of columns the text should be broken into.<br />IMPORTANT: This feature is designed to be used for running text, images, dropcaps and other inline content. While some block elements will work, their usage is not recommended and others can easily break the layout.', 'fusion-builder' ),
						'param_name'  => 'columns',
						'default'     => $fusion_settings->get( 'text_columns' ),
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Column Min Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the minimum width for each column, this allows your columns to gracefully break into the selected size as the screen width narrows. Leave this option empty if you wish to keep the same amount of columns from desktop to mobile. Enter value including any valid CSS unit, ex: 200px.', 'fusion-builder' ),
						'param_name'  => 'column_min_width',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => '1',
								'operator' => '>',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the column spacing between one column to the next. Enter value including any valid CSS unit besides % which does not work for inline columns, ex: 2em.', 'fusion-builder' ),
						'param_name'  => 'column_spacing',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => '1',
								'operator' => '>',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Rule Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the style of the vertical line between columns. Some of the styles depend on the rule size and color.', 'fusion-builder' ),
						'param_name'  => 'rule_style',
						'value'       => [
							''       => esc_html__( 'Default', 'fusion-builder' ),
							'none'   => esc_attr__( 'None', 'fusion-builder' ),
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
							'double' => esc_attr__( 'Double', 'fusion-builder' ),
							'groove' => esc_attr__( 'Groove', 'fusion-builder' ),
							'ridge'  => esc_attr__( 'Ridge', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => '1',
								'operator' => '>',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Rule Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Sets the size of the vertical line between columns. The rule is rendered as "below" spacing and columns, so it can span over the gap between columns if it is larger than the column spacing amount. In pixels.', 'fusion-builder' ),
						'param_name'  => 'rule_size',
						'default'     => $fusion_settings->get( 'text_rule_size' ),
						'min'         => '1',
						'max'         => '50',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => '1',
								'operator' => '>',
							],
							[
								'element'  => 'rule_style',
								'value'    => 'none',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Rule Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the vertical line between columns.', 'fusion-builder' ),
						'param_name'  => 'rule_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'text_rule_color' ),
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => '1',
								'operator' => '>',
							],
							[
								'element'  => 'rule_style',
								'value'    => 'none',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'User Text Select', 'fusion-builder' ),
						'description' => esc_html__( 'Controls how and if the text can be selected.', 'fusion-builder' ),
						'param_name'  => 'user_select',
						'default'     => '',
						'value'       => [
							''      => esc_html__( 'Default', 'fusion-builder' ),
							'auto'  => esc_html__( 'Selectable', 'fusion-builder' ),
							'all'   => esc_html__( 'All', 'fusion-builder' ),
							'none'  => esc_html__( 'Not Selectable', 'fusion-builder' ),

						],
					],					
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter some content for this text block.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
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
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the text typography.', 'fusion-builder' ),
						'param_name'       => 'main_typography',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'text_font',
							'font-size'      => 'font_size',
							'line-height'    => 'line_height',
							'letter-spacing' => 'letter_spacing',
							'text-transform' => 'text_transform',
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
						'heading'     => esc_attr__( 'Font Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the text, ex: #000.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the text alignment.', 'fusion-builder' ),
						'param_name'  => 'content_alignment',
						'default'     => '',
						'responsive'  => [
							'state'         => 'large',
							'default_value' => true,
						],
						'value'       => [
							''        => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'    => esc_attr__( 'Left', 'fusion-builder' ),
							'center'  => esc_attr__( 'Center', 'fusion-builder' ),
							'right'   => esc_attr__( 'Right', 'fusion-builder' ),
							'justify' => esc_attr__( 'Justify', 'fusion-builder' ),
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-text',
					],
					[
						'type'        => 'fusion_logics',
						'heading'     => esc_html__( 'Conditional Logic', 'fusion-builder' ),
						'param_name'  => 'logics',
						'description' => esc_html__( 'Add conditional logic when the element is used within a form.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'placeholder' => [
							'id'          => 'placeholder',
							'title'       => esc_html__( 'Select A Field', 'fusion-builder' ),
							'type'        => 'text',
							'comparisons' => [
								'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
								'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
								'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
								'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
								'contains'     => esc_attr__( 'Contains', 'fusion-builder' ),
							],
						],
						'comparisons' => [
							'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
							'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
							'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
							'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
							'contains'     => esc_attr__( 'Contains', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => '_post_type_edited',
								'value'    => 'fusion_form',
								'operator' => '==',
							],
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
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_text' );
