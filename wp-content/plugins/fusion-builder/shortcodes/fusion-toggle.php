<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_accordion' ) ) {

	if ( ! class_exists( 'FusionSC_Toggle' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Toggle extends Fusion_Element {

			/**
			 * Counter for accordians.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $accordian_counter = 1;

			/**
			 * Counter for collapsed items.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $collapse_counter = 1;

			/**
			 * The ID of the collapsed item.
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $collapse_id;

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
				add_filter( 'fusion_attr_toggle-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-panelgroup', [ $this, 'panelgroup_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-panel', [ $this, 'panel_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-title', [ $this, 'title_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-fa-active-icon', [ $this, 'fa_active_icon_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-fa-inactive-icon', [ $this, 'fa_inactive_icon_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-data-toggle', [ $this, 'data_toggle_attr' ] );
				add_filter( 'fusion_attr_toggle-shortcode-collapse', [ $this, 'collapse_attr' ] );

				add_shortcode( 'fusion_accordion', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_toggle', [ $this, 'render_child' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param 'parent'|'child' $context Whether we want parent or child.
			 * @return array
			 */
			public static function get_element_defaults( $context ) {
				$fusion_settings = awb_get_fusion_settings();

				$parent = [
					'background_color'                 => ( '' !== $fusion_settings->get( 'accordian_background_color' ) ) ? $fusion_settings->get( 'accordian_background_color' ) : '#ffffff',
					'border_color'                     => ( '' !== $fusion_settings->get( 'accordian_border_color' ) ) ? $fusion_settings->get( 'accordian_border_color' ) : '#cccccc',
					'border_size'                      => intval( $fusion_settings->get( 'accordion_border_size' ) ) . 'px',
					'boxed_mode'                       => ( '' !== $fusion_settings->get( 'accordion_boxed_mode' ) ) ? $fusion_settings->get( 'accordion_boxed_mode' ) : 'no',
					'divider_line'                     => $fusion_settings->get( 'accordion_divider_line' ),
					'divider_color'                    => $fusion_settings->get( 'accordion_divider_color' ),
					'divider_hover_color'              => $fusion_settings->get( 'accordion_divider_hover_color' ),
					'active_icon'                      => '',
					'inactive_icon'                    => '',
					'margin_top'                       => '',
					'margin_bottom'                    => '',
					'padding_top'                      => '',
					'padding_right'                    => '',
					'padding_bottom'                   => '',
					'padding_left'                     => '',
					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'hover_color'                      => ( '' !== $fusion_settings->get( 'accordian_hover_color' ) ) ? $fusion_settings->get( 'accordian_hover_color' ) : fusion_library()->sanitize->color( $fusion_settings->get( 'link_hover_color' ) ),
					'icon_alignment'                   => ( '' !== $fusion_settings->get( 'accordion_icon_align' ) ) ? $fusion_settings->get( 'accordion_icon_align' ) : 'left',
					'icon_boxed_mode'                  => ( '' !== $fusion_settings->get( 'accordion_icon_boxed' ) ) ? $fusion_settings->get( 'accordion_icon_boxed' ) : 'no',
					'icon_box_color'                   => $fusion_settings->get( 'accordian_inactive_color' ),
					'icon_color'                       => ( '' !== $fusion_settings->get( 'accordian_icon_color' ) ) ? $fusion_settings->get( 'accordian_icon_color' ) : '#ffffff',
					'icon_size'                        => ( '' !== $fusion_settings->get( 'accordion_icon_size' ) ) ? $fusion_settings->get( 'accordion_icon_size' ) : '13px',
					'fusion_font_family_title_font'    => $fusion_settings->get( 'accordion_title_typography', 'font-family' ),
					'fusion_font_variant_title_font'   => $fusion_settings->get( 'accordion_title_typography', 'font-weight' ),
					'title_color'                      => $fusion_settings->get( 'accordion_title_typography', 'color' ),
					'title_tag'                        => 'h4',
					'title_font_size'                  => $fusion_settings->get( 'accordion_title_typography', 'font-size' ),
					'title_text_transform'             => '',
					'title_line_height'                => $fusion_settings->get( 'accordion_title_typography', 'line-height' ),
					'title_letter_spacing'             => $fusion_settings->get( 'accordion_title_typography', 'letter-spacing' ),
					'fusion_font_family_content_font'  => $fusion_settings->get( 'accordion_content_typography', 'font-family' ),
					'fusion_font_variant_content_font' => $fusion_settings->get( 'accordion_content_typography', 'font-weight' ),
					'content_color'                    => $fusion_settings->get( 'accordion_content_typography', 'color' ),
					'content_font_size'                => $fusion_settings->get( 'accordion_content_typography', 'font-size' ),
					'content_text_transform'           => '',
					'content_line_height'              => '',
					'content_letter_spacing'           => '',
					'toggle_hover_accent_color'        => $fusion_settings->get( 'accordian_active_color' ),
					'toggle_active_accent_color'       => $fusion_settings->get( 'accordian_active_accent_color' ),
					'type'                             => ( '' !== $fusion_settings->get( 'accordion_type' ) ) ? $fusion_settings->get( 'accordion_type' ) : 'accordions',
					'class'                            => '',
					'id'                               => '',
					'dynamic_params'                   => '',
				];

				$child = [
					'open'                             => 'no',
					'title'                            => '',
					'fusion_font_family_title_font'    => '',
					'fusion_font_variant_title_font'   => '',
					'title_color'                      => '',
					'title_font_size'                  => '',
					'title_text_transform'             => '',
					'title_line_height'                => '',
					'title_letter_spacing'             => '',
					'fusion_font_family_content_font'  => '',
					'fusion_font_variant_content_font' => '',
					'content_color'                    => '',
					'content_font_size'                => '',
					'content_text_transform'           => '',
					'content_line_height'              => '',
					'content_letter_spacing'           => '',
					'class'                            => '',
					'id'                               => '',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
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
					'accordion_type'                      => 'type',
					'accordion_boxed_mode'                => 'boxed_mode',
					'accordion_border_size'               => 'border_size',
					'accordian_border_color'              => 'border_color',
					'accordian_hover_color'               => 'hover_color',
					'accordian_background_color'          => 'background_color',
					'accordion_divider_line'              => 'divider_line',
					'accordion_divider_color'             => 'divider_color',
					'accordion_divider_hover_color'       => 'divider_hover_color',
					'accordion_title_typography[font-family]' => 'title_font',
					'accordion_title_typography[font-size]' => 'title_font_size',
					'accordion_title_typography[color]'   => 'title_color',
					'accordion_title_typography[line-height]' => 'title_line_height',
					'accordion_title_typography[letter-spacing]' => 'title_letter_spacing',
					'accordion_content_typography[font-family]' => 'content_font',
					'accordion_content_typography[font-size]' => 'content_font_size',
					'accordion_content_typography[color]' => 'content_color',
					'accordion_icon_size'                 => 'icon_size',
					'accordian_icon_color'                => 'icon_color',
					'accordion_icon_boxed'                => 'icon_boxed_mode',
					'accordion_icon_align'                => 'icon_alignment',
					'accordian_inactive_color'            => 'icon_box_color',
					'accordian_active_color'              => 'toggle_hover_accent_color',
					'accordian_active_accent_color'       => 'toggle_active_accent_color',
				];

				$child = [
					'accordion_title_typography[font-family]' => 'title_font',
					'accordion_title_typography[font-size]' => 'title_font_size',
					'accordion_title_typography[color]'   => 'title_color',
					'accordion_title_typography[line-height]' => 'title_line_height',
					'accordion_title_typography[letter-spacing]' => 'title_letter_spacing',
					'accordion_content_typography[font-family]' => 'content_font',
					'accordion_content_typography[font-size]' => 'content_font_size',
					'accordion_content_typography[color]' => 'content_color',
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
			 * Render the parent shortcode
			 *
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_accordion' );

				$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['icon_size']   = FusionBuilder::validate_shortcode_attr_value( $defaults['icon_size'], 'px' );

				$this->parent_args = $defaults;

				$dynamic_data = json_decode( fusion_decode_if_needed( $this->parent_args['dynamic_params'] ), true );

				if ( isset( $dynamic_data['parent_dynamic_content'] ) ) {
					$content = self::get_acf_repeater( $dynamic_data['parent_dynamic_content'], $this->parent_args, $content );
				}

				$html = sprintf(
					'<div %s><div %s>%s</div></div>',
					FusionBuilder::attributes( 'toggle-shortcode' ),
					FusionBuilder::attributes( 'toggle-shortcode-panelgroup' ),
					do_shortcode( $content )
				);

				$this->accordian_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_toggles_parent_content', $html, $args );
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
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'accordian fusion-accordian',
						'style' => '',
					]
				);

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->parent_args );

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				$attr['style'] .= $this->get_parent_style_variables();

				return $attr;
			}

			/**
			 * Builds the panel-group attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function panelgroup_attr() {
				$attr = [
					'class' => 'panel-group',
					'id'    => 'accordion-' . get_the_ID() . '-' . $this->accordian_counter,
				];

				if ( 'right' === $this->parent_args['icon_alignment'] ) {
					$attr['class'] .= ' fusion-toggle-icon-right';
				}

				if ( '0' === $this->parent_args['icon_boxed_mode'] || 0 === $this->parent_args['icon_boxed_mode'] || 'no' === $this->parent_args['icon_boxed_mode'] ) {
					$attr['class'] .= ' fusion-toggle-icon-unboxed';
				} else {
					$attr['class'] .= ' fusion-toggle-icon-boxed';
				}

				return $attr;
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_toggle' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_toggle', $args );

				$this->child_args                 = $defaults;
				$this->child_args['toggle_class'] = '';

				if ( 'yes' === $this->child_args['open'] ) {
					$this->child_args['toggle_class'] = 'in';
				}

				$this->collapse_id = substr( md5( sprintf( 'collapse-%s-%s-%s', get_the_ID(), $this->accordian_counter, $this->collapse_counter ) ), 15 );
				$title_tag         = ! empty( $this->parent_args['title_tag'] ) ? $this->parent_args['title_tag'] : 'h4';

				$html = sprintf(
					'<div %s><div %s><%s %s><a %s><span %s><i %s></i><i %s></i></span><span %s>%s</span></a></%s></div><div %s><div %s>%s</div></div></div>',
					FusionBuilder::attributes( 'toggle-shortcode-panel' ),
					FusionBuilder::attributes( 'panel-heading' ),
					$title_tag,
					FusionBuilder::attributes( 'toggle-shortcode-title' ),
					FusionBuilder::attributes( 'toggle-shortcode-data-toggle' ),
					FusionBuilder::attributes(
						'fusion-toggle-icon-wrapper',
						[
							'class'       => 'fusion-toggle-icon-wrapper',
							'aria-hidden' => 'true',
						]
					),
					FusionBuilder::attributes( 'toggle-shortcode-fa-active-icon' ),
					FusionBuilder::attributes( 'toggle-shortcode-fa-inactive-icon' ),
					FusionBuilder::attributes( 'fusion-toggle-heading' ),
					$this->child_args['title'],
					$title_tag,
					FusionBuilder::attributes( 'toggle-shortcode-collapse' ),
					FusionBuilder::attributes( 'panel-body toggle-content fusion-clearfix' ),
					do_shortcode( $content )
				);

				$this->collapse_counter++;

				$html = $html;

				return apply_filters( 'fusion_element_toggles_child_content', $html, $args );
			}

			/**
			 * Builds the panel attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function panel_attr() {

				$attr = [
					'class' => 'fusion-panel panel-default',
					'style' => '',
				];

				if ( $this->child_args['class'] ) {
					$attr['class'] .= ' ' . $this->child_args['class'];
				}

				if ( $this->child_args['id'] ) {
					$attr['id'] = $this->child_args['id'];
				}

				$attr['class'] .= ' panel-' . $this->collapse_id;

				if ( '1' === $this->parent_args['boxed_mode'] || 1 === $this->parent_args['boxed_mode'] || 'yes' === $this->parent_args['boxed_mode'] ) {
					$attr['class'] .= ' fusion-toggle-no-divider fusion-toggle-boxed-mode';
				} else {
					if ( '0' === $this->parent_args['divider_line'] || 0 === $this->parent_args['divider_line'] || 'no' === $this->parent_args['divider_line'] ) {
						$attr['class'] .= ' fusion-toggle-no-divider';
					} else {
						$attr['class'] .= ' fusion-toggle-has-divider';
					}
				}

				$attr['style'] .= $this->get_child_style_variables();

				return $attr;
			}

			/**
			 * Builds the font-awesome icon attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function fa_active_icon_attr() {
				$attr = [
					'class'       => 'fa-fusion-box active-icon ',
					'aria-hidden' => 'true',
				];

				$attr['class'] .= ( '' !== $this->parent_args['active_icon'] ) ? fusion_font_awesome_name_handler( $this->parent_args['active_icon'] ) : 'awb-icon-minus';

				return $attr;
			}

			/**
			 * Builds the font-awesome icon attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function fa_inactive_icon_attr() {
				$attr = [
					'class'       => 'fa-fusion-box inactive-icon ',
					'aria-hidden' => 'true',
				];

				$attr['class'] .= ( '' !== $this->parent_args['inactive_icon'] ) ? fusion_font_awesome_name_handler( $this->parent_args['inactive_icon'] ) : 'awb-icon-plus'; // here.

				return $attr;
			}

			/**
			 * Builds the panel title attributes
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function title_attr() {
				$attr = [
					'class' => 'panel-title toggle',
					'id'    => 'toggle_' . $this->collapse_id,
				];

				return $attr;
			}

			/**
			 * Builds the data-toggle attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function data_toggle_attr() {

				$attr = [];

				if ( 'yes' === $this->child_args['open'] ) {
					$attr['class'] = 'active';
				}

				// Accessibility enhancements.
				$attr['aria-expanded'] = ( 'yes' === $this->child_args['open'] ) ? 'true' : 'false';
				$attr['aria-controls'] = $this->collapse_id;
				$attr['role']          = 'button';

				$attr['data-toggle'] = 'collapse';
				if ( 'toggles' !== $this->parent_args['type'] ) {
					$attr['data-parent'] = sprintf( '#accordion-%s-%s', get_the_ID(), $this->accordian_counter );
				}
				$attr['data-target'] = '#' . $this->collapse_id;
				$attr['href']        = '#' . $this->collapse_id;

				return $attr;
			}

			/**
			 * Builds the collapse attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function collapse_attr() {
				return [
					'id'              => $this->collapse_id,
					'class'           => 'panel-collapse collapse ' . $this->child_args['toggle_class'],
					'aria-labelledby' => 'toggle_' . $this->collapse_id,
				];
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Toggles settings.
			 */
			public function add_options() {

				$fusion_settings = awb_get_fusion_settings();

				return [
					'toggles_shortcode_section' => [
						'label'  => esc_html__( 'Toggles', 'fusion-builder' ),
						'id'     => 'toggles_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-expand-alt',
						'fields' => [
							'accordion_type'               => [
								'label'       => esc_html__( 'Toggles or Accordions', 'fusion-builder' ),
								'description' => esc_html__( 'Toggles allow several items to be open at a time. Accordions only allow one item to be open at a time.', 'fusion-builder' ),
								'id'          => 'accordion_type',
								'default'     => 'accordions',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'toggles'    => esc_html__( 'Toggles', 'fusion-builder' ),
									'accordions' => esc_html__( 'Accordions', 'fusion-builder' ),
								],
							],
							'accordion_boxed_mode'         => [
								'label'       => esc_html__( 'Toggle Boxed Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display items in boxed mode. Toggle divider line must be disabled for this option to work.', 'fusion-builder' ),
								'id'          => 'accordion_boxed_mode',
								'default'     => '0',
								'type'        => 'switch',
							],
							'accordion_border_size'        => [
								'label'           => esc_html__( 'Toggle Boxed Mode Border Width', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the border size of the toggle item.', 'fusion-builder' ),
								'id'              => 'accordion_border_size',
								'default'         => '1',
								'type'            => 'slider',
								'soft_dependency' => true,
								'choices'         => [
									'min'  => '0',
									'max'  => '20',
									'step' => '1',
								],
							],
							'accordian_border_color'       => [
								'label'           => esc_html__( 'Toggle Boxed Mode Border Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the border color of the toggle item.', 'fusion-builder' ),
								'id'              => 'accordian_border_color',
								'default'         => 'var(--awb-color3)',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
							],
							'accordian_background_color'   => [
								'label'           => esc_html__( 'Toggle Boxed Mode Background Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the background color of the toggle item.', 'fusion-builder' ),
								'id'              => 'accordian_background_color',
								'default'         => 'var(--awb-color1)',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
							],
							'accordian_hover_color'        => [
								'label'           => esc_html__( 'Toggle Boxed Mode Background Hover Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the background hover color of the toggle item.', 'fusion-builder' ),
								'id'              => 'accordian_hover_color',
								'default'         => 'var(--awb-color2)',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
							],
							'accordion_divider_line'       => [
								'label'           => esc_html__( 'Toggle Divider Line', 'fusion-builder' ),
								'description'     => esc_html__( 'Turn on to display a divider line between each item.', 'fusion-builder' ),
								'id'              => 'accordion_divider_line',
								'default'         => '1',
								'type'            => 'switch',
								'soft_dependency' => true,
							],
							'accordion_divider_color'      => [
								'label'       => esc_html__( 'Divider Line Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of toggle divider line.', 'fusion-builder' ),
								'id'          => 'accordion_divider_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--accordion_divider_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'accordion_divider_hover_color' => [
								'label'       => esc_html__( 'Divider Line Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the hover color of toggle divider line.', 'fusion-builder' ),
								'id'          => 'accordion_divider_hover_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--accordion_divider_hover_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'accordion_title_typography'   => [
								'id'          => 'accordion_title_typography',
								'label'       => esc_html__( 'Toggle Title Typography', 'fusion-builder' ),
								'description' => esc_html__( 'Choose the typography for all toggle titles.', 'fusion-builder' ),
								'type'        => 'typography',
								'global'      => true,
								'choices'     => [
									'font-family'    => true,
									'font-weight'    => true,
									'font-size'      => true,
									'text-transform' => true,
									'line-height'    => true,
									'letter-spacing' => true,
									'color'          => true,
								],
								'default'     => [
									'font-family'    => 'var(--awb-typography1-font-family)',
									'font-weight'    => $fusion_settings->get( 'h4_typography', 'font-weight' ),
									'font-size'      => '16px',
									'text-transform' => '',
									'line-height'    => $fusion_settings->get( 'h4_typography', 'line-height' ),
									'letter-spacing' => $fusion_settings->get( 'h4_typography', 'letter-spacing' ),
									'color'          => 'var(--awb-color8)',
								],
							],
							'accordion_icon_size'          => [
								'label'       => esc_html__( 'Toggle Icon Size', 'fusion-builder' ),
								'description' => esc_html__( 'Set the size of the icon.', 'fusion-builder' ),
								'id'          => 'accordion_icon_size',
								'default'     => '16',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '1',
									'max'  => '40',
									'step' => '1',
								],
							],
							'accordian_icon_color'         => [
								'label'       => esc_html__( 'Toggle Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of icon in toggle box.', 'fusion-builder' ),
								'id'          => 'accordian_icon_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
							],
							'accordion_icon_boxed'         => [
								'label'       => esc_html__( 'Toggle Icon Boxed Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display toggle icon in boxed mode.', 'fusion-builder' ),
								'id'          => 'accordion_icon_boxed',
								'default'     => '1',
								'type'        => 'switch',
							],
							'accordian_inactive_color'     => [
								'label'           => esc_html__( 'Toggle Icon Inactive Box Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the inactive toggle box.', 'fusion-builder' ),
								'id'              => 'accordian_inactive_color',
								'default'         => 'var(--awb-color8)',
								'type'            => 'color-alpha',
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name' => '--accordian_inactive_color',
									],
								],
							],
							'accordion_content_typography' => [
								'id'          => 'accordion_content_typography',
								'label'       => esc_html__( 'Toggle Content Typography', 'fusion-builder' ),
								'description' => esc_html__( 'Choose the typography for all toggle content.', 'fusion-builder' ),
								'type'        => 'typography',
								'global'      => true,
								'choices'     => [
									'font-family'    => true,
									'font-weight'    => true,
									'font-size'      => true,
									'text-transform' => true,
									'line-height'    => true,
									'letter-spacing' => true,
									'color'          => true,
								],
								'default'     => [
									'font-family'    => 'var(--awb-typography4-font-family)',
									'font-weight'    => $fusion_settings->get( 'body_typography', 'font-weight' ),
									'font-size'      => 'var(--awb-typography4-font-size)',
									'text-transform' => '',
									'line-height'    => '',
									'letter-spacing' => '',
									'color'          => 'var(--awb-color8)',
								],
							],
							'accordian_active_color'       => [
								'label'       => esc_html__( 'Toggle Hover Accent Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the accent color on hover for icon box and title.', 'fusion-builder' ),
								'id'          => 'accordian_active_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--accordian_active_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'accordian_active_accent_color' => [
								'label'       => esc_html__( 'Toggle Active Accent Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the accent color on active for icon box and title.', 'fusion-builder' ),
								'id'          => 'accordian_active_accent_color',
								'default'     => '',
								'type'        => 'color-alpha',
							],
							'accordion_icon_align'         => [
								'label'       => esc_html__( 'Toggle Icon Alignment', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the alignment of toggle icon.', 'fusion-builder' ),
								'id'          => 'accordion_icon_align',
								'default'     => 'left',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'left'  => esc_html__( 'Left', 'fusion-builder' ),
									'right' => esc_html__( 'Right', 'fusion-builder' ),
								],
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {

				Fusion_Dynamic_JS::enqueue_script( 'fusion-toggles' );
			}

			/**
			 * Get the style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_parent_style_variables() {
				$this->args = $this->parent_args;
				// Todo: set $this->defaults.

				$css_vars_options = [
					'margin_top'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_top'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_right'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_bottom'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_left'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_size'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'icon_size'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_font_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_text_transform',
					'content_line_height',
					'icon_alignment',
				];

				$custom_vars = [];
				if ( $this->parent_args['hover_color'] ) {
					$custom_vars['hover_color'] = $this->parent_args['hover_color'];
				}

				if ( $this->parent_args['border_color'] ) {
					$custom_vars['border_color'] = $this->parent_args['border_color'];
				}
				if ( $this->parent_args['background_color'] ) {
					$custom_vars['background_color'] = $this->parent_args['background_color'];
				}
				if ( $this->parent_args['divider_color'] ) {
					$custom_vars['divider_color'] = $this->parent_args['divider_color'];
				}
				if ( $this->parent_args['divider_hover_color'] ) {
					$custom_vars['divider_hover_color'] = $this->parent_args['divider_hover_color'];
				}
				if ( $this->parent_args['icon_color'] ) {
					$custom_vars['icon_color'] = $this->parent_args['icon_color'];
				}
				if ( $this->parent_args['title_color'] ) {
					$custom_vars['title_color'] = $this->parent_args['title_color'];
				}
				if ( $this->parent_args['content_color'] ) {
					$custom_vars['content_color'] = $this->parent_args['content_color'];
				}
				if ( $this->parent_args['icon_box_color'] ) {
					$custom_vars['icon_box_color'] = $this->parent_args['icon_box_color'];
				}
				if ( $this->parent_args['toggle_hover_accent_color'] ) {
					$custom_vars['toggle_hover_accent_color'] = $this->parent_args['toggle_hover_accent_color'];
				}
				if ( $this->parent_args['toggle_active_accent_color'] ) {
					$custom_vars['toggle_active_accent_color'] = $this->parent_args['toggle_active_accent_color'];
				}

				$title_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->parent_args, 'title_font', 'array' );

				if ( ! empty( $title_typography['font-family'] ) ) {
					$custom_vars['title_font_family'] = $title_typography['font-family'];
				}

				if ( ! empty( $title_typography['font-weight'] ) ) {
					$custom_vars['title_font_weight'] = $title_typography['font-weight'];
				}

				if ( ! empty( $title_typography['font-style'] ) ) {
					$custom_vars['title_font_style'] = $title_typography['font-style'];
				}

				if ( ! empty( $this->parent_args['title_font_size'] ) ) {
					$custom_vars['title_font_size'] = fusion_library()->sanitize->get_value_with_unit( $this->parent_args['title_font_size'] );
				}

				if ( ! empty( $this->parent_args['title_letter_spacing'] ) ) {
					$custom_vars['title_letter_spacing'] = fusion_library()->sanitize->get_value_with_unit( $this->parent_args['title_letter_spacing'] );
				}

				if ( ! empty( $this->parent_args['title_line_height'] ) ) {
					$custom_vars['title_line_height'] = $this->parent_args['title_line_height'];
				}

				if ( ! empty( $this->parent_args['title_text_transform'] ) ) {
					$custom_vars['title_text_transform'] = $this->parent_args['title_text_transform'];
				}

				if ( ! empty( $this->parent_args['title_color'] ) ) {
					$custom_vars['title_color'] = $this->parent_args['title_color'];
				}

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars ) . $this->get_font_styling_vars( 'content_font' );

				return $styles;
			}

			/**
			 * Get child style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_child_style_variables() {
				$this->args = $this->child_args;
				// Todo: set $this->defaults.

				$css_vars_options = [
					'content_font_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_text_transform',
					'content_line_height',
				];

				$custom_vars = [];

				$title_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->child_args, 'title_font', 'array' );

				if ( ! empty( $title_typography['font-family'] ) ) {
					$custom_vars['title_font_family'] = $title_typography['font-family'];
				}

				if ( ! empty( $title_typography['font-weight'] ) ) {
					$custom_vars['title_font_weight'] = $title_typography['font-weight'];
				}

				if ( ! empty( $title_typography['font-style'] ) ) {
					$custom_vars['title_font_style'] = $title_typography['font-style'];
				}

				if ( ! empty( $this->child_args['title_font_size'] ) ) {
					$custom_vars['title_font_size'] = fusion_library()->sanitize->get_value_with_unit( $this->child_args['title_font_size'] );
				}

				if ( ! empty( $this->child_args['title_letter_spacing'] ) ) {
					$custom_vars['title_letter_spacing'] = fusion_library()->sanitize->get_value_with_unit( $this->child_args['title_letter_spacing'] );
				}

				if ( ! empty( $this->child_args['title_line_height'] ) ) {
					$custom_vars['title_line_height'] = $this->child_args['title_line_height'];
				}

				if ( ! empty( $this->child_args['title_text_transform'] ) ) {
					$custom_vars['title_text_transform'] = $this->child_args['title_text_transform'];
				}

				if ( ! empty( $this->child_args['title_color'] ) ) {
					$custom_vars['title_color'] = $this->child_args['title_color'];
				}

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_font_styling_vars( 'content_font' ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/toggles.min.css' );
			}
		}
	}

	new FusionSC_Toggle();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_accordion() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Toggle',
			[
				'name'          => esc_attr__( 'Toggles', 'fusion-builder' ),
				'shortcode'     => 'fusion_accordion',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_toggle',
				'icon'          => 'fusiona-expand-alt',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-toggles-preview.php',
				'preview_id'    => 'fusion-builder-block-module-toggles-preview-template',
				'help_url'      => 'https://avada.com/documentation/toggles-element/',
				'subparam_map'  => [
					'fusion_font_family_title_font'    => 'title_fonts',
					'fusion_font_variant_title_font'   => 'title_fonts',
					'title_font_size'                  => 'title_fonts',
					'title_text_transform'             => 'title_fonts',
					'title_line_height'                => 'title_fonts',
					'title_letter_spacing'             => 'title_fonts',
					'title_color'                      => 'title_fonts',
					'fusion_font_family_content_font'  => 'content_fonts',
					'fusion_font_variant_content_font' => 'content_fonts',
					'content_font_size'                => 'content_fonts',
					'content_text_transform'           => 'content_fonts',
					'content_line_height'              => 'content_fonts',
					'content_letter_spacing'           => 'content_fonts',
					'content_color'                    => 'content_fonts',
				],
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
						'description' => esc_attr__( 'Enter some content for this toggles element.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_toggle title="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" open="no" ]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_toggle]',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Toggles or Accordions', 'fusion-builder' ),
						'description' => esc_attr__( 'Toggles allow several items to be open at a time. Accordions only allow one item to be open at a time.', 'fusion-builder' ),
						'param_name'  => 'type',
						'value'       => [
							''           => esc_attr__( 'Default', 'fusion-builder' ),
							'toggles'    => esc_attr__( 'Toggles', 'fusion-builder' ),
							'accordions' => esc_attr__( 'Accordions', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Boxed Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display items in boxed mode.', 'fusion-builder' ),
						'param_name'  => 'boxed_mode',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Boxed Mode Border Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the border width for toggle item. In pixels.', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'value'       => $fusion_settings->get( 'accordion_border_size' ),
						'default'     => $fusion_settings->get( 'accordion_border_size' ),
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Boxed Mode Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the border color for toggle item.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_border_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Boxed Mode Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the background color for toggle item.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_background_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'states'      => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'param_name' => 'hover_color',
								'default'    => $fusion_settings->get( 'accordian_hover_color' ),
								'preview'    => [
									'selector' => '.fusion-builder-live-child-element,.panel-title>a',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Divider Line', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display a divider line between each item.', 'fusion-builder' ),
						'param_name'  => 'divider_line',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Divider Line Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the color for divider line.', 'fusion-builder' ),
						'param_name'  => 'divider_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordion_divider_color' ),
						'dependency'  => [
							[
								'element'  => 'boxed_mode',
								'value'    => 'yes',
								'operator' => '!=',
							],
							[
								'element'  => 'divider_line',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'states'      => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'param_name' => 'divider_hover_color',
								'default'    => $fusion_settings->get( 'accordion_divider_hover_color' ),
								'preview'    => [
									'selector' => '.fusion-panel,.panel-title>a',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Toggle Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Set the padding for toggle items. Enter values including px or em units, ex: 20px, 2.5em.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Title Tag', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the toggle title, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'title_tag',
						'value'       => [
							'h1'  => 'H1',
							'h2'  => 'H2',
							'h3'  => 'H3',
							'h4'  => 'H4',
							'h5'  => 'H5',
							'h6'  => 'H6',
							'div' => 'DIV',
						],
						'default'     => 'h4',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the title text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'title_fonts',
						'choices'          => [
							'font-family'    => 'title_font',
							'font-size'      => 'title_font_size',
							'text-transform' => 'title_text_transform',
							'line-height'    => 'title_line_height',
							'letter-spacing' => 'title_letter_spacing',
							'color'          => 'title_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'text-transform' => '',
							'line-height'    => $fusion_settings->get( 'accordion_title_typography', 'line-height' ),
							'letter-spacing' => $fusion_settings->get( 'accordion_title_typography', 'letter-spacing' ),
							'color'          => $fusion_settings->get( 'accordion_title_typography', 'color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Inactive Icon', 'fusion-builder' ),
						'param_name'  => 'inactive_icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Active Icon', 'fusion-builder' ),
						'param_name'  => 'active_icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'heading'     => esc_html__( 'Toggle Icon Size', 'fusion-builder' ),
						'description' => esc_html__( 'Set the size of the icon. In pixels, ex: 13px.', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'default'     => $fusion_settings->get( 'accordion_icon_size' ),
						'min'         => '1',
						'max'         => '40',
						'step'        => '1',
						'type'        => 'range',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Toggle Icon Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the color of icon in toggle box.', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_icon_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Toggle Icon Boxed Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display icon in boxed mode.', 'fusion-builder' ),
						'param_name'  => 'icon_boxed_mode',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Toggle Icon Inactive Box Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the inactive toggle box.', 'fusion-builder' ),
						'param_name'  => 'icon_box_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_inactive_color' ),
						'dependency'  => [
							[
								'element'  => 'icon_boxed_mode',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Toggle Icon Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the alignment of toggle icon.', 'fusion-builder' ),
						'param_name'  => 'icon_alignment',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Content Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the content text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'content_fonts',
						'choices'          => [
							'font-family'    => 'content_font',
							'font-size'      => 'content_font_size',
							'text-transform' => 'content_text_transform',
							'line-height'    => 'content_line_height',
							'letter-spacing' => 'content_letter_spacing',
							'color'          => 'content_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'accordion_content_typography', 'color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Toggle Hover Accent Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the accent color on hover for icon box and title.', 'fusion-builder' ),
						'param_name'  => 'toggle_hover_accent_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_active_color' ),
						'preview'     => [
							'selector' => '.panel-title>a,.fusion-toggle-boxed-mode',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Toggle Active Accent Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the accent color on active for icon box and title.', 'fusion-builder' ),
						'param_name'  => 'toggle_active_accent_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'accordian_active_accent_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_bottom' => '',
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
add_action( 'fusion_builder_before_init', 'fusion_element_accordion' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_toggle() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Toggle',
			[
				'name'                     => esc_attr__( 'Toggle', 'fusion-builder' ),
				'shortcode'                => 'fusion_toggle',
				'hide_from_builder'        => true,
				'allow_generator'          => true,
				'inline_editor'            => true,
				'inline_editor_shortcodes' => true,
				'subparam_map'             => [
					'fusion_font_family_title_font'    => 'title_fonts',
					'fusion_font_variant_title_font'   => 'title_fonts',
					'title_font_size'                  => 'title_fonts',
					'title_text_transform'             => 'title_fonts',
					'title_line_height'                => 'title_fonts',
					'title_letter_spacing'             => 'title_fonts',
					'title_color'                      => 'title_fonts',
					'fusion_font_family_content_font'  => 'content_fonts',
					'fusion_font_variant_content_font' => 'content_fonts',
					'content_font_size'                => 'content_fonts',
					'content_text_transform'           => 'content_fonts',
					'content_line_height'              => 'content_fonts',
					'content_letter_spacing'           => 'content_fonts',
					'content_color'                    => 'content_fonts',
				],
				'params'                   => [
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Title', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert the toggle title.', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the title text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'title_fonts',
						'choices'          => [
							'font-family'    => 'title_font',
							'font-size'      => 'title_font_size',
							'text-transform' => 'title_text_transform',
							'line-height'    => 'title_line_height',
							'letter-spacing' => 'title_letter_spacing',
							'color'          => 'title_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'accordion_title_typography', 'color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Open by Default', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have the toggle open when page loads.', 'fusion-builder' ),
						'param_name'  => 'open',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Toggle Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert the toggle content.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Content Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the content text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'content_fonts',
						'choices'          => [
							'font-family'    => 'content_font',
							'font-size'      => 'content_font_size',
							'text-transform' => 'content_text_transform',
							'line-height'    => 'content_line_height',
							'letter-spacing' => 'content_letter_spacing',
							'color'          => 'content_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'accordion_content_typography', 'color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping child HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping child HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_toggle' );
