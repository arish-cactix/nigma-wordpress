<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_checklist' ) ) {

	if ( ! class_exists( 'FusionSC_Checklist' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Checklist extends Fusion_Element {

			/**
			 * The checklist counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $checklist_counter = 1;

			/**
			 * The checklist counter.
			 *
			 * @access private
			 * @since 3.5
			 * @var int
			 */
			private $checklist_child_counter = 1;

			/**
			 * The CSS class of circle elements.
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $circle_class = 'circle-no';

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $child_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_checklist-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_checklist', [ $this, 'render_parent' ] );

				add_filter( 'fusion_attr_checklist-shortcode-span', [ $this, 'span_attr' ] );
				add_filter( 'fusion_attr_checklist-shortcode-icon', [ $this, 'icon_attr' ] );

				add_shortcode( 'fusion_li_item', [ $this, 'render_child' ] );
			}
			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Whether we want parent or child.
			 *                        Returns array( parent, child ) if empty.
			 * @return array
			 */
			public static function get_element_defaults( $context = '' ) {
				$fusion_settings = awb_get_fusion_settings();
				$parent          = [
					'type'                => 'icons',
					'circle'              => strtolower( $fusion_settings->get( 'checklist_circle' ) ),
					'circlecolor'         => $fusion_settings->get( 'checklist_circle_color' ),
					'class'               => '',
					'divider'             => $fusion_settings->get( 'checklist_divider' ),
					'divider_color'       => $fusion_settings->get( 'checklist_divider_color' ),
					'margin_top'          => '',
					'margin_right'        => '',
					'margin_bottom'       => '',
					'margin_left'         => '',
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'icon'                => 'awb-icon-check',
					'iconcolor'           => $fusion_settings->get( 'checklist_icons_color' ),
					'id'                  => '',
					'size'                => $fusion_settings->get( 'checklist_item_size' ),
					'odd_row_bgcolor'     => $fusion_settings->get( 'checklist_odd_row_bgcolor' ),
					'even_row_bgcolor'    => $fusion_settings->get( 'checklist_even_row_bgcolor' ),
					'item_padding_top'    => '',
					'item_padding_right'  => '',
					'item_padding_bottom' => '',
					'item_padding_left'   => '',
					'textcolor'           => $fusion_settings->get( 'checklist_text_color' ),
					'dynamic_params'      => '',
				];

				$child = [
					'circle'      => '',
					'circlecolor' => '',
					'icon'        => '',
					'iconcolor'   => '',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @param string $context Whether we want parent or child.
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params( $context = '' ) {

				$parent = [
					'checklist_circle'               => 'circle',
					'checklist_circle_color'         => 'circlecolor',
					'checklist_icons_color'          => 'iconcolor',
					'checklist_divider'              => 'divider',
					'checklist_divider_color'        => 'divider_color',
					'checklist_item_size'            => 'size',
					'checklist_odd_row_bgcolor'      => 'odd_row_bgcolor',
					'checklist_even_row_bgcolor'     => 'even_row_bgcolor',
					'checklist_item_padding[top]'    => 'item_padding_top',
					'checklist_item_padding[right]'  => 'item_padding_right',
					'checklist_item_padding[bottom]' => 'item_padding_bottom',
					'checklist_item_padding[left]'   => 'item_padding_left',
					'checklist_text_color'           => 'textcolor',
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
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
					'body_font_size' => $fusion_settings->get( 'body_typography', 'font-size' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'body_typography' => 'body_font_size',
				];
			}

			/**
			 * Render the parent shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render_parent( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults( 'parent' );
				$defaults       = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_checklist' );

				$defaults['size'] = empty( $defaults['size'] ) ? '16px' : $defaults['size'];

				$defaults['size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['size'], 'px' );

				$defaults['circle'] = ( 1 == $defaults['circle'] ) ? 'yes' : $defaults['circle']; // phpcs:ignore Universal.Operators.StrictComparisons

				// Fallbacks for old size parameter and 'px' check.
				if ( 'small' === $defaults['size'] ) {
					$defaults['size'] = '13px';
				} elseif ( 'medium' === $defaults['size'] ) {
					$defaults['size'] = '18px';
				} elseif ( 'large' === $defaults['size'] ) {
					$defaults['size'] = '40px';
				} elseif ( ! strpos( $defaults['size'], 'px' ) ) {
					$defaults['size']  = fusion_library()->sanitize->convert_font_size_to_px( $defaults['size'], fusion_library()->get_option( 'body_typography', 'font-size' ) );
					$defaults['size'] .= 'px';
				}

				// Determine line-height and margin from font size.
				$font_size                        = fusion_library()->sanitize->number( $defaults['size'] );
				$defaults['circle_yes_font_size'] = $font_size * 0.88;
				$defaults['line_height']          = $font_size * 1.7;
				$defaults['icon_margin']          = $font_size * 0.7;
				$defaults['content_margin']       = $defaults['line_height'] + $defaults['icon_margin'];

				extract( $defaults );

				$this->parent_args = $this->args = $defaults;

				$this->parent_args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_bottom'], 'px' );
				$this->parent_args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_left'], 'px' );
				$this->parent_args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_right'], 'px' );
				$this->parent_args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_top'], 'px' );

				if ( $this->parent_args['dynamic_params'] ) {
					$dynamic_data = json_decode( fusion_decode_if_needed( $this->parent_args['dynamic_params'] ), true );

					if ( isset( $dynamic_data['parent_dynamic_content'] ) ) {
						$content = self::get_acf_repeater( $dynamic_data['parent_dynamic_content'], $this->parent_args, $content );
					}
				}

				// Legacy checklist integration.
				if ( strpos( $content, '<li>' ) && strpos( $content, '[fusion_li_item' ) === false ) {
					$content = str_replace( '<ul>', '', $content );
					$content = str_replace( '</ul>', '', $content );
					$content = str_replace( '<li>', '[fusion_li_item]', $content );
					$content = str_replace( '</li>', '[/fusion_li_item]', $content );
				}

				fusion_element_rendering_elements( true );
				$this->checklist_child_counter = 1;
				$html                          = '<ul ' . FusionBuilder::attributes( 'checklist-shortcode' ) . '>' . do_shortcode( $content ) . '</ul>';
				$this->checklist_child_counter = 1;
				fusion_element_rendering_elements( false );

				$html = str_replace( '</li><br />', '</li>', $html );

				$this->checklist_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_checklist_parent_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [
					'style' => $this->get_style_vars(),
				];

				$attr['class'] = 'fusion-checklist fusion-checklist-' . $this->checklist_counter;

				if ( false === strpos( $attr['style'], '--awb-odd-row-bgcolor' ) && false === strpos( $attr['style'], '--awb-item-padding-top' ) ) {
					$attr['class'] .= ' fusion-checklist-default';
				}

				$attr = fusion_builder_visibility_atts( $this->parent_args['hide_on_mobile'], $attr );

				if ( 'yes' === $this->parent_args['divider'] ) {
					$attr['class'] .= ' fusion-checklist-divider';
				}

				if ( '' !== $this->parent_args['type'] ) {
					$attr['class'] .= ' type-' . $this->parent_args['type'];
				}

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;
			}

			/**
			 * Get the style variables.
			 *
			 * @since 3.9
			 * @return string
			 */
			private function get_style_vars() {
				$sanitize   = fusion_library()->sanitize;
				$this->args = $this->parent_args;

				$css_vars = [
					'size',
					'margin_top',
					'margin_right',
					'margin_bottom',
					'margin_left',
					'item_padding_top'    => [ 'callback' => [ $sanitize, 'get_value_with_unit' ] ],
					'item_padding_right'  => [ 'callback' => [ $sanitize, 'get_value_with_unit' ] ],
					'item_padding_bottom' => [ 'callback' => [ $sanitize, 'get_value_with_unit' ] ],
					'item_padding_left'   => [ 'callback' => [ $sanitize, 'get_value_with_unit' ] ],
					'odd_row_bgcolor',
					'even_row_bgcolor',
					'iconcolor',
				];

				if ( 'yes' === $this->parent_args['divider'] ) {
					$css_vars[] = 'divider_color';
				}

				if ( '' !== $this->parent_args['textcolor'] ) {
					$css_vars[] = 'textcolor';
				}

				$custom_vars['line_height']    = $this->parent_args['line_height'] . 'px';
				$custom_vars['icon_width']     = $this->parent_args['line_height'] . 'px';
				$custom_vars['icon_height']    = $this->parent_args['line_height'] . 'px';
				$custom_vars['icon_margin']    = $this->parent_args['icon_margin'] . 'px';
				$custom_vars['content_margin'] = $this->parent_args['content_margin'] . 'px';

				if ( 'yes' === $this->parent_args['circle'] ) {
					$custom_vars['circlecolor']          = $this->parent_args['circlecolor'];
					$custom_vars['circle_yes_font_size'] = $this->parent_args['circle_yes_font_size'] . 'px';
				}

				return $this->get_css_vars_for_options( $css_vars ) . $this->get_custom_css_vars( $custom_vars );
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render_child( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults( 'child' );
				$defaults       = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_li_item' );
				$content        = apply_filters( 'fusion_shortcode_content', $content, 'fusion_li_item', $args );

				$this->child_args = $defaults;

				$html  = '<li class="fusion-li-item" style="' . $this->get_child_css_vars() . '">';
				$html .= '<span ' . FusionBuilder::attributes( 'checklist-shortcode-span' ) . '>';

				if ( 'numbered' === $this->parent_args['type'] ) {
					$html .= $this->checklist_child_counter;
				} else {
					$html .= '<i ' . FusionBuilder::attributes( 'checklist-shortcode-icon' ) . '></i>';
				}

				$html .= '</span>';
				$html .= '<div class="fusion-li-item-content">' . do_shortcode( $content ) . '</div>';
				$html .= '</li>';

				$this->circle_class = 'circle-no';

				$this->checklist_child_counter++;

				return apply_filters( 'fusion_element_checklist_child_content', $html, $args );
			}

			/**
			 * Get child CSS variables.
			 *
			 * @since 3.9
			 * @return string
			 */
			public function get_child_css_vars() {
				$this->args = $this->child_args;
				$css_vars   = [
					'circlecolor',
					'iconcolor',
				];

				return $this->get_css_vars_for_options( $css_vars );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function span_attr() {
				$attr = [
					'style' => '',
				];

				if ( 'yes' === $this->child_args['circle'] || ( 'yes' === $this->parent_args['circle'] && ( 'no' !== $this->child_args['circle'] ) ) ) {
					$this->circle_class = 'circle-yes';
				}

				$attr['class'] = 'icon-wrapper ' . $this->circle_class;

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {
				if ( ! $this->child_args['icon'] ) {
					$icon = fusion_font_awesome_name_handler( $this->parent_args['icon'] );
				} else {
					$icon = fusion_font_awesome_name_handler( $this->child_args['icon'] );
				}

				return [
					'class'       => 'fusion-li-icon ' . $icon,
					'aria-hidden' => 'true',
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/checklist.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Checklist settings.
			 */
			public function add_options() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'checklist_shortcode_section' => [
						'label'       => esc_html__( 'Checklist', 'fusion-builder' ),
						'description' => '',
						'id'          => 'checklist_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-list-ul',
						'fields'      => [
							'checklist_icons_color'      => [
								'label'       => esc_html__( 'Checklist Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the checklist icon.', 'fusion-builder' ),
								'id'          => 'checklist_icons_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--checklist_icons_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'checklist_circle'           => [
								'label'       => esc_html__( 'Checklist Icon Circle', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on if you want to display a circle background for checklists icons.', 'fusion-builder' ),
								'id'          => 'checklist_circle',
								'default'     => '1',
								'type'        => 'switch',
								'transport'   => 'postMessage',
							],
							'checklist_circle_color'     => [
								'label'           => esc_html__( 'Checklist Icon Circle Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the checklist icon circle background.', 'fusion-builder' ),
								'id'              => 'checklist_circle_color',
								'default'         => 'var(--awb-color4)',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name'     => '--checklist_circle_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'checklist_text_color'       => [
								'label'       => esc_html__( 'Checklist Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the checklist text.', 'fusion-builder' ),
								'id'          => 'checklist_text_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--checklist_text_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'checklist_item_size'        => [
								'label'       => esc_attr__( 'Item Font Size', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the font size of the list items.', 'fusion-builder' ),
								'id'          => 'checklist_item_size',
								'default'     => '16px',
								'type'        => 'dimension',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name' => '--checklist_item_size',
									],
								],
							],
							'checklist_item_padding'     => [
								'label'       => esc_html__( 'Item Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the padding size of the list items.', 'fusion-builder' ),
								'id'          => 'checklist_item_padding',
								'choices'     => [
									'top'    => true,
									'right'  => true,
									'bottom' => true,
									'left'   => true,
								],
								'default'     => [
									'top'    => '0.35em',
									'right'  => '0',
									'bottom' => '0.35em',
									'left'   => '0',
								],
								'type'        => 'spacing',
								'css_vars'    => [
									[
										'name'   => '--checklist_item_padding-top',
										'choice' => 'top',
									],
									[
										'name'   => '--checklist_item_padding-bottom',
										'choice' => 'bottom',
									],
									[
										'name'   => '--checklist_item_padding-left',
										'choice' => 'left',
									],
									[
										'name'   => '--checklist_item_padding-right',
										'choice' => 'right',
									],
								],
							],
							'checklist_divider'          => [
								'label'       => esc_attr__( 'Divider Lines', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose if a divider line shows between each list item.', 'fusion-builder' ),
								'id'          => 'checklist_divider',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								],
								'transport'   => 'postMessage',
							],
							'checklist_divider_color'    => [
								'label'           => esc_html__( 'Divider Line Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the divider lines.', 'fusion-builder' ),
								'id'              => 'checklist_divider_color',
								'default'         => 'var(--awb-color3)',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name'     => '--checklist_divider_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'checklist_odd_row_bgcolor'  => [
								'label'       => esc_html__( 'Checklist Odd Row Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color of the checklist odd row.', 'fusion-builder' ),
								'id'          => 'checklist_odd_row_bgcolor',
								'default'     => 'rgba(255,255,255,0)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--checklist_odd_row_bgcolor',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'checklist_even_row_bgcolor' => [
								'label'       => esc_html__( 'Checklist Even Row Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color of the checklist even row.', 'fusion-builder' ),
								'id'          => 'checklist_even_row_bgcolor',
								'default'     => 'rgba(255,255,255,0)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--checklist_even_row_bgcolor',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
						],
					],
				];
			}
		}
	}

	new FusionSC_Checklist();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_checklist() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Checklist',
			[
				'name'          => esc_attr__( 'Checklist', 'fusion-builder' ),
				'shortcode'     => 'fusion_checklist',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_li_item',
				'icon'          => 'fusiona-list-ul',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-checklist-preview.php',
				'preview_id'    => 'fusion-builder-block-module-checklist-preview-template',
				'child_ui'      => true,
				'help_url'      => 'https://avada.com/documentation/checklist-element/',
				'params'        => [
					[
						'type'            => 'textfield',
						'heading'         => esc_attr__( 'Dynamic Content', 'fusion-builder' ),
						'param_name'      => 'parent_dynamic_content',
						'dynamic_data'    => true,
						'dynamic_options' => [ 'acf_repeater_parent' ],
						'group'           => esc_attr__( 'children', 'fusion-builder' ),
					],
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this content box.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_li_item icon=""]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_li_item]',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Checklist Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Global setting for all list items. Make a selection if you want to have numbered list or icons list.', 'fusion-builder' ),
						'param_name'  => 'type',
						'default'     => 'icons',
						'value'       => [
							'icons'    => esc_attr__( 'Icons', 'fusion-builder' ),
							'numbered' => esc_attr__( 'Numbered', 'fusion-builder' ),
						],
					],
					[
						'type'         => 'iconpicker',
						'heading'      => esc_attr__( 'Select Icon', 'fusion-builder' ),
						'param_name'   => 'icon',
						'value'        => '',
						'description'  => esc_attr__( 'Global setting for all list items, this can be overridden individually. Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'type',
								'value'    => 'numbered',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon/Number Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Global setting for all list items. Controls the color of the checklist icon/number.', 'fusion-builder' ),
						'param_name'  => 'iconcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'checklist_icons_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon/Number Circle', 'fusion-builder' ),
						'description' => esc_attr__( 'Global setting for all list items. Turn on if you want to display a circle background for checklists icons/numbers.', 'fusion-builder' ),
						'param_name'  => 'circle',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon/Number Circle Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Global setting for all list items.  Controls the color of the checklist icon/number circle background.', 'fusion-builder' ),
						'param_name'  => 'circlecolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'checklist_circle_color' ),
						'dependency'  => [
							[
								'element'  => 'circle',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Global setting for all list items.  Controls the color of the checklist text.', 'fusion-builder' ),
						'param_name'  => 'textcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'checklist_text_color' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Item Font Size', 'fusion-builder' ),
						'description' => esc_attr__( "Select the list item's font size. Enter value including any valid CSS unit, ex: 14px.", 'fusion-builder' ),
						'param_name'  => 'size',
						'value'       => '',
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Item Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding size of the list items.', 'fusion-builder' ),
						'param_name'       => 'item_padding',
						'value'            => [
							'item_padding_top'    => '',
							'item_padding_right'  => '',
							'item_padding_bottom' => '',
							'item_padding_left'   => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Divider Lines', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if a divider line shows between each list item.', 'fusion-builder' ),
						'param_name'  => 'divider',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Divider Line Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the divider lines.', 'fusion-builder' ),
						'param_name'  => 'divider_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'checklist_divider_color' ),
						'dependency'  => [
							[
								'element'  => 'divider',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Odd Row Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the odd row.', 'fusion-builder' ),
						'param_name'  => 'odd_row_bgcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'checklist_odd_row_bgcolor' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Even Row Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the even row.', 'fusion-builder' ),
						'param_name'  => 'even_row_bgcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'checklist_even_row_bgcolor' ),
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
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_checklist' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_checklist_item() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Checklist',
			[
				'name'              => esc_attr__( 'List Item', 'fusion-builder' ),
				'description'       => esc_attr__( 'Enter some content for this list item.', 'fusion-builder' ),
				'shortcode'         => 'fusion_li_item',
				'hide_from_builder' => true,
				'allow_generator'   => true,
				'inline_editor'     => true,
				'tag_name'          => 'li',
				'selectors'         => [
					'class' => 'fusion-li-item',
				],
				'params'            => [
					[
						'type'         => 'iconpicker',
						'heading'      => esc_attr__( 'Select Icon', 'fusion-builder' ),
						'param_name'   => 'icon',
						'value'        => '',
						'description'  => esc_attr__( 'This setting will override the global setting. ', 'fusion-builder' ),
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'parent_type',
								'value'    => 'numbered',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'List Item Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add list item content.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon/Number Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the checklist icon/number.', 'fusion-builder' ),
						'param_name'  => 'iconcolor',
						'value'       => '',
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon/Number Circle Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the checklist icon/number circle background.', 'fusion-builder' ),
						'param_name'  => 'circlecolor',
						'value'       => '',
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'parent_circle',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],

				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_checklist_item' );
