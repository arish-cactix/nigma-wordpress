<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_fontawesome' ) ) {

	if ( ! class_exists( 'FusionSC_FontAwesome' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FontAwesome extends Fusion_Element {

			/**
			 * The icon counter.
			 *
			 * @access private
			 * @since 2.2
			 * @var int
			 */
			private $icon_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_fontawesome-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_fontawesome', [ $this, 'render' ] );
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
				$border_radius   = Fusion_Builder_Border_Radius_Helper::get_border_radius_array_with_fallback_value( $fusion_settings->get( 'icon_border_radius' ) );

				return [
					'hide_on_mobile'             => fusion_builder_default_visibility( 'string' ),
					'sticky_display'             => '',
					'class'                      => '',
					'id'                         => '',
					'alignment'                  => '',
					'alignment_medium'           => '',
					'alignment_small'            => '',
					'circle'                     => $fusion_settings->get( 'icon_circle' ),
					'bg_size'                    => '-1',
					'circlebordersize'           => $fusion_settings->get( 'icon_border_size' ),
					'circlecolor'                => $fusion_settings->get( 'icon_circle_color' ),
					'circlecolor_hover'          => $fusion_settings->get( 'icon_circle_color_hover' ),
					'circlebordercolor'          => $fusion_settings->get( 'icon_border_color' ),
					'circlebordercolor_hover'    => $fusion_settings->get( 'icon_border_color_hover' ),
					'border_radius_top_left'     => $border_radius['top_left'],
					'border_radius_top_right'    => $border_radius['top_right'],
					'border_radius_bottom_right' => $border_radius['bottom_right'],
					'border_radius_bottom_left'  => $border_radius['bottom_left'],
					'flip'                       => '',
					'icon'                       => '',
					'icon_hover_type'            => $fusion_settings->get( 'icon_hover_type' ),
					'iconcolor'                  => $fusion_settings->get( 'icon_color' ),
					'iconcolor_hover'            => $fusion_settings->get( 'icon_color_hover' ),
					'link'                       => '',
					'linktarget'                 => '_self',
					'link_attributes'            => '',
					'margin_bottom'              => '',
					'margin_left'                => '',
					'margin_right'               => '',
					'margin_top'                 => '',
					'rotate'                     => '',
					'size'                       => $fusion_settings->get( 'icon_size' ),
					'spin'                       => 'no',
					'animation_type'             => '',
					'animation_direction'        => 'down',
					'animation_speed'            => '0.1',
					'animation_delay'            => '',
					'animation_offset'           => $fusion_settings->get( 'animation_offset' ),
					'animation_color'            => '',
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
					'icon_border_size'                 => 'circlebordersize',
					'icon_size'                        => 'size',
					'icon_circle'                      => 'circle',
					'icon_circle_color'                => 'circlecolor',
					'icon_circle_color_hover'          => 'circlecolor_hover',
					'icon_border_color'                => 'circlebordercolor',
					'icon_border_color_hover'          => 'circlebordercolor_hover',
					'icon_color'                       => 'iconcolor',
					'icon_color_hover'                 => 'iconcolor_hover',
					'icon_hover_type'                  => 'icon_hover_type',
					'animation_offset'                 => 'animation_offset',
					'icon_border_radius[top_left]'     => 'border_radius_top_left',
					'icon_border_radius[top_right]'    => 'border_radius_top_right',
					'icon_border_radius[bottom_right]' => 'border_radius_bottom_right',
					'icon_border_radius[bottom_left]'  => 'border_radius_bottom_left',
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
				$this->set_element_id( $this->icon_counter );

				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_fontawesome' );
				$this->args     = $this->backwards_compatibility( $this->args, $args );

				$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_fontawesome', $args );

				// Determine line-height and margin from font size.
				$this->args['font_size']            = FusionBuilder::validate_shortcode_attr_value( $this->convert_deprecated_sizes( $this->args['size'] ), '' );
				$this->args['circle_yes_font_size'] = isset( $this->args['bg_size'] ) && '-1' != $this->args['bg_size'] ? $this->args['font_size'] : $this->args['font_size'] * 0.88; // phpcs:ignore Universal.Operators.StrictComparisons
				$this->args['height']               = isset( $this->args['bg_size'] ) && '-1' != $this->args['bg_size'] ? (int) $this->args['bg_size'] : $this->args['font_size'] * 1.76; // phpcs:ignore Universal.Operators.StrictComparisons
				$this->args['line_height']          = $this->args['height'] - ( 2 * (int) $this->args['circlebordersize'] );

				// Get border size is desired format.
				$this->args['circlebordersize'] = FusionBuilder::validate_shortcode_attr_value( $this->args['circlebordersize'], 'px' );

				// Check if an old icon shortcode is used, where no margin option is present, or if all margins were left empty.
				$this->args['legacy_icon'] = false;
				if ( '' === $this->args['margin_top'] && '' === $this->args['margin_right'] && '' === $this->args['margin_bottom'] && '' === $this->args['margin_left'] ) {
					$this->args['legacy_icon'] = true;
				}

				$tag  = $this->args['link'] ? 'a' : 'i';
				$html = '<' . $tag . ' ' . FusionBuilder::attributes( 'fontawesome-shortcode' ) . '>' . do_shortcode( $content ) . '</' . $tag . '>';

				if ( $this->args['alignment'] && ! fusion_element_rendering_is_flex() ) {
					$html = '<div class="fusion-fa-align-' . $this->args['alignment'] . '">' . $html . '</div>';
				}

				$this->icon_counter++;
				$this->on_render();
				return apply_filters( 'fusion_element_fontawesome_content', $html, $args );
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
						'class' => 'fb-icon-element-' . $this->element_id . ' fb-icon-element fontawesome-icon ' . fusion_font_awesome_name_handler( $this->args['icon'] ) . ' circle-' . $this->args['circle'],
					]
				);

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( '' === $this->args['alignment'] ) {
					$attr['class'] .= ' fusion-text-flow';
				} elseif ( fusion_element_rendering_is_flex() ) {
					// Fallback to correct margin on flex containers.
					$this->args['margin_top']    = $this->args['margin_top'] ? $this->args['margin_top'] : '0px';
					$this->args['margin_right']  = $this->args['margin_right'] ? $this->args['margin_right'] : '0px';
					$this->args['margin_bottom'] = $this->args['margin_bottom'] ? $this->args['margin_bottom'] : '0px';
					$this->args['margin_left']   = $this->args['margin_left'] ? $this->args['margin_left'] : '0px';
				}

				$attr['style'] = $this->get_style_vars();

				if ( $this->args['rotate'] ) {
					$attr['class'] .= ' fa-rotate-' . $this->args['rotate'];
				}

				if ( 'yes' === $this->args['spin'] ) {
					$attr['class'] .= ' fa-spin';
				}

				if ( $this->args['flip'] ) {
					$attr['class'] .= ' fa-flip-' . $this->args['flip'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				// Link related parameters.
				if ( $this->args['link'] ) {
					$attr['class']     .= ' fusion-link';
					$attr['href']       = $this->args['link'];
					$attr['aria-label'] = apply_filters( 'fusion_fontawesome_aria', esc_attr__( 'Link to', 'fusion-builder' ) . ' ' . esc_url( $this->args['link'] ), $this->args['link'] );
					$attr['target']     = $this->args['linktarget'];

					if ( '_blank' === $this->args['linktarget'] ) {
						$attr['rel'] = 'noopener noreferrer';
					}

					// Add additional, custom link attributes correctly formatted to the anchor.
					$attr = fusion_get_link_attributes( $this->args, $attr );
				}

				if ( 'pulsate' === $this->args['icon_hover_type'] || 'slide' === $this->args['icon_hover_type'] ) {
					$attr['class'] .= ' icon-hover-animation-' . $this->args['icon_hover_type'];
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
			 * Get style variables.
			 *
			 * @since 3.9
			 * @return string
			 */
			public function get_style_vars() {
				$css_vars        = [
					'iconcolor',
					'iconcolor_hover',
				];
				$custom_css_vars = [];

				if ( 'yes' === $this->args['circle'] ) {
					array_push( $css_vars, 'circlecolor' );
					array_push( $css_vars, 'circlecolor_hover' );
					array_push( $css_vars, 'circlebordercolor' );
					array_push( $css_vars, 'circlebordercolor_hover' );
				} else {
					array_push( $css_vars, 'circlecolor' );
				}

				if ( 'yes' === $this->args['circle'] ) {
					$custom_css_vars['font_size'] = $this->args['circle_yes_font_size'] . 'px';
					$custom_css_vars['width']     = $this->args['height'] . 'px';
					$custom_css_vars['height']    = $this->args['height'] . 'px';

					$custom_css_vars['line_height'] = $this->args['line_height'] . 'px';
					array_push( $css_vars, 'circlebordersize' );

					if ( ! $this->is_default( 'border_radius_top_left' ) ) {
						$custom_css_vars['border_radius_top_l'] = $this->args['border_radius_top_left'];
					}
					if ( ! $this->is_default( 'border_radius_top_right' ) ) {
						$custom_css_vars['border_radius_top_r'] = $this->args['border_radius_top_right'];
					}
					if ( ! $this->is_default( 'border_radius_bottom_right' ) ) {
						$custom_css_vars['border_radius_bot_r'] = $this->args['border_radius_bottom_right'];
					}
					if ( ! $this->is_default( 'border_radius_bottom_left' ) ) {
						$custom_css_vars['border_radius_bot_l'] = $this->args['border_radius_bottom_left'];
					}
				} else {
					$custom_css_vars['font_size'] = $this->args['font_size'] . 'px';
				}

				$margin = '';
				// Legacy icon, where no margin option was present: use the old default margin calcs.
				if ( $this->args['legacy_icon'] ) {
					$icon_margin = $this->args['font_size'] * 0.5;

					if ( 'left' === $this->args['alignment'] ) {
						$icon_margin_position = 'right';
					} elseif ( 'right' === $this->args['alignment'] ) {
						$icon_margin_position = 'left';
					} else {
						$icon_margin_position = ( is_rtl() ) ? 'left' : 'right';
					}

					// Fallback to correct margin on flex containers.
					if ( '' !== $this->args['alignment'] && fusion_element_rendering_is_flex() ) {
						$custom_css_vars['margin_top']    = 0;
						$custom_css_vars['margin_right']  = 0;
						$custom_css_vars['margin_bottom'] = 0;
						$custom_css_vars['margin_left']   = 0;
					}

					if ( 'center' !== $this->args['alignment'] ) {
						$custom_css_vars[ 'margin_' . $icon_margin_position ] = $icon_margin . 'px';
					}
				} else {
					$margin = Fusion_Builder_Margin_Helper::get_margin_vars( $this->args );
				}

				// Responsive Alignment.
				if ( fusion_element_rendering_is_flex() ) {
					foreach ( [ 'large', 'medium', 'small' ] as $size ) {
						$align_styles = '';
						$align_key    = 'large' === $size ? 'alignment' : 'alignment_' . $size;
						if ( '' !== $this->args[ $align_key ] ) {
							// RTL adjust.
							if ( is_rtl() && 'center' !== $this->args[ $align_key ] ) {
								$this->args[ $align_key ] = 'left' === $this->args[ $align_key ] ? 'right' : 'left';
							}
							if ( 'left' === $this->args[ $align_key ] ) {
								$align_styles = 'flex-start';
							} elseif ( 'right' === $this->args[ $align_key ] ) {
								$align_styles = 'flex-end';
							} else {
								$align_styles = 'center';
							}
						}

						if ( '' === $align_styles ) {
							continue;
						}

						if ( 'large' === $size ) {
							$custom_css_vars['align-self'] = $align_styles;
						} elseif ( 'medium' === $size ) {
							$custom_css_vars['md-align-self'] = $align_styles;
						} elseif ( 'small' === $size ) {
							$custom_css_vars['sm-align-self'] = $align_styles;
						}
					}
				}

				return $this->get_css_vars_for_options( $css_vars ) . $this->get_custom_css_vars( $custom_css_vars ) . $margin;
			}

			/**
			 * Checks for presence of args and if not applied BC alterations.
			 *
			 * @access public
			 * @since 2.2
			 * @param array $defaults The element combined params..
			 * @param array $args The element arguments.
			 * @return array
			 */
			public function backwards_compatibility( $defaults, $args ) {
				if ( ! isset( $args['iconcolor_hover'] ) ) {
					$defaults['iconcolor_hover'] = $defaults['iconcolor'];
				}
				if ( ! isset( $args['circlecolor_hover'] ) ) {
					$defaults['circlecolor_hover'] = $defaults['circlecolor'];
				}
				if ( ! isset( $args['circlebordercolor_hover'] ) ) {
					$defaults['circlebordercolor_hover'] = $defaults['circlebordercolor'];
				}
				return $defaults;
			}

			/**
			 * Converts deprecated image sizes to their new names.
			 *
			 * @access public
			 * @since 1.0
			 * @param  string $size The name of the old image-size.
			 * @return string       The name of the new image-size.
			 */
			public function convert_deprecated_sizes( $size ) {
				switch ( $size ) {
					case 'small':
						$size = '10px';
						break;
					case 'medium':
						$size = '18px';
						break;
					case 'large':
						$size = '40px';
						break;
					default:
						break;
				}

				return $size;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Icon settings.
			 */
			public function add_options() {

				return [
					'icon_shortcode_section' => [
						'label'       => esc_html__( 'Icon', 'fusion-builder' ),
						'description' => '',
						'id'          => 'icon_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-flag',
						'fields'      => [
							'icon_size'               => [
								'label'       => esc_html__( 'Icon Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the size of the icon.', 'fusion-builder' ),
								'id'          => 'icon_size',
								'default'     => '32',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '250',
									'step' => '1',
								],
							],
							'icon_color'              => [
								'label'       => esc_html__( 'Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the icon.', 'fusion-builder' ),
								'id'          => 'icon_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--icon_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'icon_color_hover'        => [
								'label'       => esc_html__( 'Icon Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the icon on hover.', 'fusion-builder' ),
								'id'          => 'icon_color_hover',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--icon_color_hover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'icon_circle'             => [
								'label'       => esc_html__( 'Icon Background', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display a background behind the icon.', 'fusion-builder' ),
								'id'          => 'icon_circle',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_html__( 'Yes', 'fusion-builder' ),
									'no'  => esc_html__( 'No', 'fusion-builder' ),
								],
							],
							'icon_circle_color'       => [
								'label'       => esc_html__( 'Icon Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the background.', 'fusion-builder' ),
								'id'          => 'icon_circle_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--icon_circle_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'icon_circle_color_hover' => [
								'label'       => esc_html__( 'Icon Hover Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the background on hover.', 'fusion-builder' ),
								'id'          => 'icon_circle_color_hover',
								'default'     => 'var(--awb-color4)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name' => '--icon_circle_color_hover',
									],
								],
							],
							'icon_border_size'        => [
								'label'       => esc_html__( 'Icon Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the icon background.', 'fusion-builder' ),
								'id'          => 'icon_border_size',
								'default'     => '1',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--icon_border_size',
										'callback' => [ 'sanitize_color' ],
									],
								],
								'choices'     => [
									'min'  => '0',
									'max'  => '20',
									'step' => '1',
								],
							],
							'icon_border_color'       => [
								'label'       => esc_html__( 'Icon Background Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the background.', 'fusion-builder' ),
								'id'          => 'icon_border_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--icon_border_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'icon_border_color_hover' => [
								'label'       => esc_html__( 'Icon Hover Background Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the background on hover.', 'fusion-builder' ),
								'id'          => 'icon_border_color_hover',
								'default'     => 'var(--awb-color4)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--icon_border_color_hover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'icon_border_radius'      => [
								'label'       => esc_attr__( 'Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Set the border radius.', 'fusion-builder' ),
								'id'          => 'icon_border_radius',
								'choices'     => [
									'top_left'     => true,
									'top_right'    => true,
									'bottom_right' => true,
									'bottom_left'  => true,
									'units'        => [ 'px', '%', 'em' ],
								],
								'default'     => [
									'top_left'     => '50%',
									'top_right'    => '50%',
									'bottom_right' => '50%',
									'bottom_left'  => '50%',
								],
								'type'        => 'border_radius',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'   => '--icon_border_top_l_rad',
										'choice' => 'top_left',
									],
									[
										'name'   => '--icon_border_top_r_rad',
										'choice' => 'top_right',
									],
									[
										'name'   => '--icon_border_bot_r_rad',
										'choice' => 'bottom_right',
									],
									[
										'name'   => '--icon_border_bot_l_rad',
										'choice' => 'bottom_left',
									],
								],
							],
							'icon_hover_type'         => [
								'label'       => esc_html__( 'Icon Hover Animation Type', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the hover effect of the icon.', 'fusion-builder' ),
								'id'          => 'icon_hover_type',
								'default'     => 'fade',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'fade'    => esc_html__( 'Fade', 'fusion-builder' ),
									'slide'   => esc_html__( 'Slide', 'fusion-builder' ),
									'pulsate' => esc_html__( 'Pulsate', 'fusion-builder' ),
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

				Fusion_Dynamic_JS::enqueue_script( 'fusion-animations' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/icon.min.css' );

				Fusion_Media_Query_Scripts::$media_query_assets[] = [
					'avada-icon-md',
					FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/icon-md.min.css',
					[],
					FUSION_BUILDER_VERSION,
					Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-medium' ),
				];
				Fusion_Media_Query_Scripts::$media_query_assets[] = [
					'avada-icon-sm',
					FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/icon-sm.min.css',
					[],
					FUSION_BUILDER_VERSION,
					Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-small' ),
				];
			}
		}
	}

	new FusionSC_FontAwesome();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_font_awesome() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FontAwesome',
			[
				'name'       => esc_attr__( 'Icon', 'fusion-builder' ),
				'shortcode'  => 'fusion_fontawesome',
				'icon'       => 'fusiona-flag',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-font-awesome-preview.php',
				'preview_id' => 'fusion-builder-block-module-font-awesome-preview-template',
				'help_url'   => 'https://avada.com/documentation/icon-element/',
				'params'     => [
					[
						'type'         => 'iconpicker',
						'heading'      => esc_attr__( 'Select Icon', 'fusion-builder' ),
						'param_name'   => 'icon',
						'value'        => 'fa-flag fas',
						'description'  => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icon Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the size of the icon. In pixels.', 'fusion-builder' ),
						'param_name'  => 'size',
						'value'       => '',
						'min'         => '0',
						'max'         => '250',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'icon_size' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Flip Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to flip the icon.', 'fusion-builder' ),
						'param_name'  => 'flip',
						'value'       => [
							''           => esc_attr__( 'None', 'fusion-builder' ),
							'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
							'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Rotate Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to rotate the icon.', 'fusion-builder' ),
						'param_name'  => 'rotate',
						'value'       => [
							''    => esc_attr__( 'None', 'fusion-builder' ),
							'90'  => '90',
							'180' => '180',
							'270' => '270',
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Spinning Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to let the icon spin.', 'fusion-builder' ),
						'param_name'  => 'spin',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Link', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the url the icon should link to.', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => esc_html__( 'Controls how the link will open.', 'fusion-builder' ),
						'param_name'  => 'linktarget',
						'value'       => [
							'_self'  => esc_html__( 'Same Window/Tab', 'fusion-builder' ),
							'_blank' => esc_html__( 'New Window/Tab', 'fusion-builder' ),
						],
						'default'     => '_self',
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => __( 'Spacing around the icon. In px, em or %, e.g. 10px. <strong>NOTE:</strong> Leave empty for automatic margin calculation, based on alignment and icon size.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'          => 'colorpickeralpha',
						'heading'       => esc_attr__( 'Icon Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Controls the color of the icon. ', 'fusion-builder' ),
						'param_name'    => 'iconcolor',
						'value'         => '',
						'default'       => $fusion_settings->get( 'icon_color' ),
						'group'         => esc_attr__( 'Design', 'fusion-builder' ),
						'states'        => [
							'hover' => [
								'label'   => __( 'Hover', 'fusion-builder' ),
								'default' => $fusion_settings->get( 'icon_color_hover' ),
								'preview' => [
									'selector' => '.fontawesome-icon',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'circlecolor', 'circlebordercolor' ],

					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Background', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to display a background behind the icon.', 'fusion-builder' ),
						'param_name'  => 'circle',
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
						'heading'     => esc_attr__( 'Icon Background Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background size of the icon. Set to -1 to use default calculations. In pixels, ex: 35.', 'fusion-builder' ),
						'param_name'  => 'bg_size',
						'value'       => '-1',
						'min'         => '-1',
						'max'         => '1000',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'circle',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'          => 'colorpickeralpha',
						'heading'       => esc_attr__( 'Icon Background Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Controls the color of the icon background. ', 'fusion-builder' ),
						'param_name'    => 'circlecolor',
						'value'         => '',
						'default'       => $fusion_settings->get( 'icon_circle_color' ),
						'group'         => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'    => [
							[
								'element'  => 'circle',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'states'        => [
							'hover' => [
								'label'   => __( 'Hover', 'fusion-builder' ),
								'default' => $fusion_settings->get( 'icon_circle_color_hover' ),
								'preview' => [
									'selector' => '.fontawesome-icon',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'iconcolor', 'circlebordercolor' ],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icon Background Border Size', 'fusion-builder' ),
						'description' => '',
						'param_name'  => 'circlebordersize',
						'value'       => '',
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'icon_border_size' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'circle',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'          => 'colorpickeralpha',
						'heading'       => esc_attr__( 'Icon Background Border Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Controls the color of the background border. ', 'fusion-builder' ),
						'param_name'    => 'circlebordercolor',
						'value'         => '',
						'default'       => $fusion_settings->get( 'icon_border_color' ),
						'group'         => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'    => [
							[
								'element'  => 'circle',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'circlebordersize',
								'value'    => '0',
								'operator' => '!=',
							],
						],
						'states'        => [
							'hover' => [
								'label'   => __( 'Hover', 'fusion-builder' ),
								'default' => $fusion_settings->get( 'icon_border_color_hover' ),
								'preview' => [
									'selector' => '.fontawesome-icon',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'iconcolor', 'circlecolor' ],
					],
					'fusion_border_radius_placeholder'     => [
						'dependency' => [
							[
								'element'  => 'circle',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Icon Hover Animation Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the animation type for icon on hover. Select default for Global Options selection.', 'fusion-builder' ),
						'param_name'  => 'icon_hover_type',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'fade'    => esc_attr__( 'Fade', 'fusion-builder' ),
							'slide'   => esc_attr__( 'Slide', 'fusion-builder' ),
							'pulsate' => esc_attr__( 'Pulsate', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'preview'     => [
							'selector' => '.fontawesome-icon',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the icon's alignment.", 'fusion-builder' ),
						'param_name'  => 'alignment',
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => '',
						'responsive'  => [
							'state' => 'large',
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
						'preview_selector' => '.fontawesome-icon',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_font_awesome' );
