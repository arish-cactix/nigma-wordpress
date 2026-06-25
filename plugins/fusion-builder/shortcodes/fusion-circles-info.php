<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.9.0
 */

if ( fusion_is_element_enabled( 'fusion_circles_info' ) ) {

	if ( ! class_exists( 'FusionSC_CirclesInfo' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.9.0
		 */
		class FusionSC_CirclesInfo extends Fusion_Element {

			/**
			 * Circles counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $circles_counter = 1;

			/**
			 * Circle counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $circle_counter = 1;

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 3.9.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @since 3.9.0
			 * @access protected
			 * @var array
			 */
			protected $child_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.9.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_circles-info-shortcode', [ $this, 'parent_attr' ] );
				add_shortcode( 'fusion_circles_info', [ $this, 'render_parent' ] );

				add_filter( 'fusion_attr_circle-info-shortcode', [ $this, 'child_attr' ] );
				add_shortcode( 'fusion_circle_info', [ $this, 'render_child' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.9.0
			 * @param 'parent'|'child' $context Whether we want parent or child.
			 * @return array
			 */
			public static function get_element_defaults( $context = 'parent' ) {

				$fusion_settings = awb_get_fusion_settings();

				$parent = [

					// General.
					'icon'                             => 'fa-flag fas',
					'icons_placement'                  => 'outer-circle',
					'auto_rotation'                    => 'no',
					'auto_rotation_time'               => '2.5',
					'pause_on_hover'                   => 'yes',
					'activation_type'                  => 'mouseover',
					'link_area'                        => 'title',
					'link_target'                      => '_blank',
					'max_width'                        => '',

					// Inner circle.
					'icon_circle_size'                 => '1',
					'icon_circle_border_style'         => 'solid',
					'icon_circle_color'                => 'var(--awb-color3)',

					// Content circle.
					'content_circle_color'             => 'var(--awb-color3)',
					'content_circle_size'              => '1',
					'content_circle_border_style'      => 'solid',
					'content_padding'                  => '50',

					// Icons.
					'icon_size'                        => '16',

					'icon_color'                       => 'var(--awb-color1)',
					'icon_bg_color'                    => 'var(--awb-color4)',
					'icon_border_size'                 => '0',
					'icon_border_color'                => 'var(--awb-color8)',
					'icon_border_style'                => 'solid',

					'icon_active_color'                => 'var(--awb-color1)',
					'icon_bg_active_color'             => 'var(--awb-color8)',
					'icon_active_border_size'          => '0',
					'icon_border_active_color'         => 'var(--awb-color8)',
					'icon_active_border_style'         => 'solid',

					// Icons box shadow.
					'box_shadow'                       => 'yes',
					'box_shadow_blur'                  => '0',
					'box_shadow_color'                 => 'var(--awb-color3)',
					'box_shadow_horizontal'            => '',
					'box_shadow_spread'                => '0',
					'box_shadow_vertical'              => '',

					// Title typography.
					'fusion_font_family_title_font'    => '',
					'fusion_font_variant_title_font'   => '',
					'title_font_size'                  => '',
					'title_line_height'                => '',
					'title_letter_spacing'             => '',
					'title_text_transform'             => '',
					'title_color'                      => 'var(--awb-color8)',
					'title_hover_color'                => 'var(--awb-color4)',

					// Content typography.
					'fusion_font_family_content_font'  => '',
					'fusion_font_variant_content_font' => '',
					'content_font_size'                => '',
					'content_line_height'              => '',
					'content_letter_spacing'           => '',
					'content_text_transform'           => '',
					'content_color'                    => $fusion_settings->get( 'body_typography', 'color' ),

					// Background.
					'background_color'                 => '',
					'gradient_start_color'             => '',
					'gradient_end_color'               => '',
					'gradient_start_position'          => '0',
					'gradient_end_position'            => '100',
					'gradient_type'                    => 'linear',
					'radial_direction'                 => 'center center',
					'linear_angle'                     => '180',
					'background_image'                 => '',
					'background_image_id'              => '',
					'background_position'              => 'left top',
					'background_repeat'                => 'no-repeat',
					'background_blend_mode'            => 'none',

					// General.
					'margin_top'                       => '',
					'margin_right'                     => '',
					'margin_bottom'                    => '',
					'margin_left'                      => '',
					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'class'                            => '',
					'id'                               => '',

					// Animation.
					'animation_direction'              => 'left',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                  => '',
					'animation_delay'                  => '',
					'animation_type'                   => '',
					'animation_color'                  => '',
				];
				$child  = [

					// General.
					'icon'                             => 'fa-flag fas',
					'title'                            => '',
					'link'                             => '',
					'item_content'                     => '',

					// Title typography.
					'fusion_font_family_title_font'    => '',
					'fusion_font_variant_title_font'   => '',
					'title_font_size'                  => '',
					'title_line_height'                => '',
					'title_letter_spacing'             => '',
					'title_text_transform'             => '',
					'title_color'                      => '',
					'title_hover_color'                => '',

					// Content typography.
					'fusion_font_family_content_font'  => '',
					'fusion_font_variant_content_font' => '',
					'content_font_size'                => '',
					'content_line_height'              => '',
					'content_letter_spacing'           => '',
					'content_text_transform'           => '',
					'content_color'                    => '',

					// Background.
					'background_color'                 => '',
					'gradient_start_color'             => '',
					'gradient_end_color'               => '',
					'gradient_start_position'          => '',
					'gradient_end_position'            => '',
					'gradient_type'                    => '',
					'radial_direction'                 => '',
					'linear_angle'                     => '',
					'background_image'                 => '',
					'background_image_id'              => '',
					'background_position'              => '',
					'background_repeat'                => '',
					'background_blend_mode'            => '',

					// General.
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
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.9.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {
				$this->defaults    = self::get_element_defaults( 'parent' );
				$this->parent_args = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_circles_info' );

				// Main wrapper.
				$html  = '<div ' . FusionBuilder::attributes( 'circles-info-shortcode' ) . '>';
				$html .= '<div class="awb-circles-info-wrapper">';

				// Icons.
				$html .= '<div class="awb-circles-info-icons-wrapper">';
				$html .= $this->get_circles_info_icons( $content );
				$html .= '</div>';

				// Content.
				$html .= '<div class="awb-circles-info-content-wrapper">';
				$html .= do_shortcode( $content );
				$html .= '</div>';

				$html .= '</div>';
				$html .= '</div>';

				$this->on_render();

				$this->circles_counter++;
				$this->circle_counter = 1;

				return apply_filters( 'fusion_element_circles_info_parent_content', $html, $args );
			}

			/**
			 * Gets circles info icons.
			 *
			 * @access public
			 * @since 3.9.0
			 * @param  string $content child elements content.
			 * @return string           HTML output.
			 */
			private function get_circles_info_icons( $content ) {
				$html = '';

				$pattern = get_shortcode_regex( [ 'fusion_circle_info' ] );
				$counter = 1;

				if ( preg_match_all( '/' . $pattern . '/s', $content, $matches )
					&& array_key_exists( 2, $matches )
					&& in_array( 'fusion_circle_info', $matches[2], true ) ) {
					foreach ( $matches[3] as $match ) {
						$circle_atts = shortcode_parse_atts( $match );
						$icon        = isset( $circle_atts['icon'] ) && '' !== $circle_atts['icon'] ? $circle_atts['icon'] : $this->parent_args['icon'];
						$html       .= '<div class="awb-circles-info-tab-link" data-id="' . $counter . '">';
						$html       .= '<span><i class="' . fusion_font_awesome_name_handler( $icon ) . '"></i></span>';
						$html       .= '</div>';

						$counter++;
					}
				}

				return $html;
			}

			/**
			 * Builds the parent attributes array.
			 *
			 * @access public
			 * @since 3.9.0
			 * @return array
			 */
			public function parent_attr() {

				$attr           = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'awb-circles-info',
						'style' => '',
					]
				);
				$this->defaults = self::get_element_defaults( 'parent' );

				if ( 'outer-circle' === $this->parent_args['icons_placement'] ) {
					$attr['class'] .= ' icons-on-outer-circle';
				}

				if ( 'yes' === $this->parent_args['auto_rotation'] ) {
					$attr['data-auto-rotation'] = $this->parent_args['auto_rotation'];

					if ( $this->parent_args['auto_rotation_time'] ) {
						$attr['data-auto-rotation-time'] = $this->parent_args['auto_rotation_time'] * 1000;
					}

					if ( '' !== $this->parent_args['pause_on_hover'] ) {
						$attr['data-pause-on-hover'] = $this->parent_args['pause_on_hover'];
					}
				}

				if ( '' !== $this->parent_args['activation_type'] ) {
					$attr['data-activation-type'] = $this->parent_args['activation_type'];
				}

				if ( '' !== $this->parent_args['background_color'] ) {
					$attr['class'] .= ' has-bg-color';
				}

				if ( '' !== $this->parent_args['gradient_start_color'] && '' !== $this->parent_args['gradient_end_color'] ) {
					$attr['class'] .= ' has-bg-gradient';
					$attr['class'] .= ' gradient-type-' . $this->parent_args['gradient_type'];
				}

				if ( '' !== $this->parent_args['background_image'] ) {
					$attr['class'] .= ' has-bg-image';
					$attr['class'] .= ' bg-image-blend-mode-' . $this->parent_args['background_blend_mode'];
				}

				if ( 'yes' === $this->parent_args['box_shadow'] ) {
					$attr['class'] .= ' has-box-shadow';
				}

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				$attr['style'] .= $this->get_parent_style_variables();

				if ( $this->parent_args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->parent_args, $attr );
				}

				return $attr;
			}

			/**
			 * Get the parent style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_parent_style_variables() {
				$custom_vars    = [];
				$this->defaults = self::get_element_defaults( 'parent' );

				// Title typography.
				$title_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->parent_args, 'title_font', 'array' );

				foreach ( $title_typography as $rule => $value ) {
					$custom_vars[ 'title-' . $rule ] = $value;
				}

				// Content typography.
				$content_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->parent_args, 'content_font', 'array' );

				foreach ( $content_typography as $rule => $value ) {
					$custom_vars[ 'content-' . $rule ] = $value;
				}

				if ( '' !== $this->parent_args['radial_direction'] ) {
					$this->parent_args['radial_direction'] = 'circle at ' . $this->parent_args['radial_direction'];
				}

				if ( '' !== $this->parent_args['background_image'] ) {
					$this->parent_args['background_image'] = 'url("' . $this->parent_args['background_image'] . '")';
				}

				if ( '' !== $this->parent_args['gradient_start_position'] ) {
					$this->parent_args['gradient_start_position'] = $this->parent_args['gradient_start_position'] . '%';
				}

				if ( '' !== $this->parent_args['gradient_end_position'] ) {
					$this->parent_args['gradient_end_position'] = $this->parent_args['gradient_end_position'] . '%';
				}

				if ( '' !== $this->parent_args['linear_angle'] ) {
					$this->parent_args['linear_angle'] = $this->parent_args['linear_angle'] . 'deg';
				}

				$css_vars_options = [
					'max_width'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'icon_circle_color'        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icon_circle_size'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_circle_color'     => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'content_circle_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_padding'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'icon_size'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'icon_color'               => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icon_bg_color'            => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icon_border_size'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'icon_border_color'        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icon_active_color'        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icon_bg_active_color'     => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icon_active_border_size'  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'icon_border_active_color' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'box_shadow_horizontal'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'box_shadow_vertical'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'box_shadow_blur'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'box_shadow_spread'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'box_shadow_color'         => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'title_font_size'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'title_letter_spacing'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'title_color'              => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'title_hover_color'        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'content_font_size'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_line_height'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_letter_spacing'   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_color'            => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'background_color'         => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'gradient_start_color'     => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'gradient_end_color'       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'margin_top'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'gradient_start_position',
					'gradient_end_position',
					'linear_angle',
					'radial_direction',
					'background_image',
					'background_position',
					'background_repeat',
					'background_blend_mode',
					'content_text_transform',
					'title_text_transform',
					'title_line_height',
					'icon_active_border_style',
					'icon_border_style',
					'content_circle_border_style',
					'icon_circle_border_style',
				];

				$this->args = $this->parent_args;
				$styles     = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Get the child style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_child_style_variables() {
				$custom_vars    = [];
				$this->defaults = self::get_element_defaults( 'child' );

				// Title typography.
				$title_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->child_args, 'title_font', 'array' );

				foreach ( $title_typography as $rule => $value ) {
					$custom_vars[ 'title-' . $rule ] = $value;
				}

				// Content typography.
				$content_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->child_args, 'content_font', 'array' );

				foreach ( $content_typography as $rule => $value ) {
					$custom_vars[ 'content-' . $rule ] = $value;
				}

				if ( '' !== $this->child_args['radial_direction'] ) {
					$this->child_args['radial_direction'] = 'circle at ' . $this->child_args['radial_direction'];

				}

				if ( '' !== $this->child_args['background_image'] ) {
					$this->child_args['background_image'] = 'url("' . $this->child_args['background_image'] . '")';
				}

				if ( '' !== $this->child_args['gradient_start_position'] ) {
					$this->child_args['gradient_start_position'] = $this->child_args['gradient_start_position'] . '%';
				}

				if ( '' !== $this->child_args['gradient_end_position'] ) {
					$this->child_args['gradient_end_position'] = $this->child_args['gradient_end_position'] . '%';
				}

				if ( '' !== $this->child_args['linear_angle'] ) {
					$this->child_args['linear_angle'] = $this->child_args['linear_angle'] . 'deg';
				}

				$css_vars_options = [
					'title_font_size'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'title_letter_spacing'   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'title_color'            => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'title_hover_color'      => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'content_font_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_line_height'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'content_color'          => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'background_color'       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'gradient_start_color'   => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'gradient_end_color'     => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'gradient_start_position',
					'gradient_end_position',
					'linear_angle',
					'radial_direction',
					'background_image',
					'background_position',
					'background_repeat',
					'background_blend_mode',
					'content_text_transform',
					'title_text_transform',
					'title_line_height',
				];

				$this->args = $this->child_args;
				$styles     = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 3.9.0
			 * @param  array  $args     Shortcode parameters.
			 * @param  string $content  Content between shortcode.
			 * @return string           HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$this->defaults   = self::get_element_defaults( 'child' );
				$this->child_args = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_circle_info' );
				$content          = apply_filters( 'fusion_shortcode_content', $content, 'fusion_circle_info', $args );
				$title            = '' !== $this->child_args['link'] ? '<a href="' . $this->child_args['link'] . '" target="' . $this->parent_args['link_target'] . '">' . $this->child_args['title'] . '</a>' : $this->child_args['title'];

				$html  = '<div ' . FusionBuilder::attributes( 'circle-info-shortcode' ) . '>';
				$html .= '<div class="awb-circles-info-title">' . $title . '</div>';

				if ( ! empty( $this->child_args['item_content'] ) ) {
					$html .= '<div class="awb-circles-info-text">' . str_replace( [ '<p>', '</p>' ], '', $this->child_args['item_content'] ) . '</div>';
				}

				$html .= '</div>';

				$this->circle_counter++;

				return apply_filters( 'fusion_element_circles_info_child_content', $html, $args );
			}

			/**
			 * Builds the child attributes array.
			 *
			 * @access public
			 * @since 3.9.0
			 * @return array
			 */
			public function child_attr() {
				$attr           = [
					'class'   => 'awb-circle-info awb-circles-info-content-area awb-circle-info-' . $this->circle_counter,
					'data-id' => $this->circle_counter,
					'style'   => '',
				];
				$this->defaults = self::get_element_defaults( 'child' );

				$this->child_args['gradient_type'] = '' === $this->child_args['gradient_type'] ? $this->parent_args['gradient_type'] : $this->child_args['gradient_type'];

				if ( $this->child_args['class'] ) {
					$attr['class'] .= ' ' . $this->child_args['class'];
				}

				if ( $this->child_args['id'] ) {
					$attr['id'] = $this->child_args['id'];
				}

				if ( '' !== $this->child_args['background_color'] ) {
					$attr['class'] .= ' has-bg-color';
				}

				if ( 'box' === $this->parent_args['link_area'] && '' !== $this->child_args['link'] ) {
					$attr['data-link']        = $this->child_args['link'];
					$attr['data-link-target'] = $this->parent_args['link_target'];
					$attr['class']           .= ' link-area-box';
				}

				if ( '' !== $this->child_args['gradient_start_color'] && '' !== $this->child_args['gradient_end_color'] ) {
					$attr['class'] .= ' has-bg-gradient';
					$attr['class'] .= ' gradient-type-' . $this->child_args['gradient_type'];
				}

				if ( '' !== $this->child_args['background_image'] ) {
					$attr['class'] .= ' has-bg-image';
					$attr['class'] .= ' bg-image-blend-mode-' . $this->child_args['background_blend_mode'];
				}

				$attr['style'] = $this->get_child_style_variables();

				return $attr;
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.9.0
			 * @return void
			 */
			public function on_first_render() {
				$fusion_settings = awb_get_fusion_settings();

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-circles-info',
					FusionBuilder::$js_folder_url . '/general/fusion-circles-info.js',
					FusionBuilder::$js_folder_path . '/general/fusion-circles-info.js',
					[ 'jquery', 'fusion-animations' ],
					FUSION_BUILDER_VERSION,
					true
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.9.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/circles-info.min.css' );
			}
		}
	}

	new FusionSC_CirclesInfo();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.9.0
 */
function fusion_element_circles_info() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_CirclesInfo',
			[
				'name'          => esc_attr__( 'Circles Info', 'fusion-builder' ),
				'shortcode'     => 'fusion_circles_info',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_circle_info',
				'icon'          => 'fusiona-circle-info',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-circles-info-preview.php',
				'preview_id'    => 'fusion-builder-block-module-circles-info-preview-template',
				'child_ui'      => true,
				'help_url'      => 'https://avada.com/documentation/circles-info-element/',
				'subparam_map'  => [
					'fusion_font_family_title_font'    => 'title_typography',
					'fusion_font_variant_title_font'   => 'title_typography',
					'title_font_size'                  => 'title_typography',
					'title_line_height'                => 'title_typography',
					'title_letter_spacing'             => 'title_typography',
					'title_text_transform'             => 'title_typography',
					'title_color'                      => 'title_typography',

					'fusion_font_family_content_font'  => 'content_typography',
					'fusion_font_variant_content_font' => 'content_typography',
					'content_font_size'                => 'content_typography',
					'content_line_height'              => 'content_typography',
					'content_letter_spacing'           => 'content_typography',
					'content_text_transform'           => 'content_typography',
					'content_color'                    => 'content_typography',
				],
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this Circls info.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_circle_info icon="" title="' . esc_attr__( 'Your Title Goes Here', 'fusion-builder' ) . '" link="" item_content="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" class="" id="" background_image_id="" fusion_font_family_title_font="" fusion_font_variant_title_font="" title_font_size="" title_line_height="" title_letter_spacing="" title_text_transform="" title_color="" hue="" saturation="" lightness="" alpha="" title_hover_color="" fusion_font_family_content_font="" fusion_font_variant_content_font="" content_font_size="" content_line_height="" content_letter_spacing="" content_text_transform="" content_color="" gradient_start_color="" gradient_end_color="" gradient_start_position="" gradient_end_position="" gradient_type="" radial_direction="" linear_angle="" background_color="" background_image="" background_position="" background_repeat="" background_blend_mode=""][/fusion_circle_info]',
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Select Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => 'fa-flag fas',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Auto Rotate', 'fusion-builder' ),
						'description' => esc_attr__( 'Select to enable or disable auto rotation.', 'fusion-builder' ),
						'param_name'  => 'auto_rotation',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Auto Rotate Time', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the delay of rotation between each element in the set. In seconds.', 'fusion-builder' ),
						'param_name'  => 'auto_rotation_time',
						'value'       => '2.5',
						'min'         => '1',
						'max'         => '10',
						'step'        => '0.1',
						'dependency'  => [
							[
								'element'  => 'auto_rotation',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Pause on Hover', 'fusion-builder' ),
						'description' => esc_attr__( 'Select to pause auto rotation on hover.', 'fusion-builder' ),
						'param_name'  => 'pause_on_hover',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'auto_rotation',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Activation Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the activation type.', 'fusion-builder' ),
						'param_name'  => 'activation_type',
						'default'     => 'mouseover',
						'value'       => [
							'mouseover' => esc_attr__( 'Hover', 'fusion-builder' ),
							'click'     => esc_attr__( 'Click', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Link Area', 'fusion-builder' ),
						'description' => esc_attr__( 'Select which area the link will be assigned to. Select default for Global Options selection.', 'fusion-builder' ),
						'param_name'  => 'link_area',
						'value'       => [
							'title' => esc_attr__( 'Title', 'fusion-builder' ),
							'box'   => esc_attr__( 'Entire Content Area', 'fusion-builder' ),
						],
						'default'     => 'title',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => esc_html__( 'Controls how the link will open.', 'fusion-builder' ),
						'param_name'  => 'link_target',
						'value'       => [
							'_self'  => esc_attr__( 'Same Window', 'fusion-builder' ),
							'_blank' => esc_attr__( 'New Window/Tab', 'fusion-builder' ),
						],
						'default'     => '_blank',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum width the element should take up. Enter value including any valid CSS unit, ex: 200px. Leave empty for a default max-width of 500px.', 'fusion-builder' ),
						'param_name'  => 'max_width',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icons Placement', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if icons should be added to content circle or outer circle.', 'fusion-builder' ),
						'param_name'  => 'icons_placement',
						'default'     => 'outer-circle',
						'value'       => [
							'content-circle' => esc_attr__( 'Content Circle', 'fusion-builder' ),
							'outer-circle'   => esc_attr__( 'Outer Circle', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Circle Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Contols the border size of icon circle. In pixels.', 'fusion-builder' ),
						'param_name'  => 'icon_circle_size',
						'value'       => '1',
						'min'         => '0',
						'max'         => '20',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Circle Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon circle border color.', 'fusion-builder' ),
						'param_name'  => 'icon_circle_color',
						'value'       => '',
						'default'     => 'var(--awb-color3)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon_circle_size',
								'value'    => '0',
								'operator' => '>',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Circle Border Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border style of the icon circle.', 'fusion-builder' ),
						'param_name'  => 'icon_circle_border_style',
						'value'       => [
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
							'double' => esc_attr__( 'Double', 'fusion-builder' ),
						],
						'default'     => 'solid',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon_circle_size',
								'value'    => '0',
								'operator' => '>',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Content Circle Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Contols the border size of content circle. In pixels.', 'fusion-builder' ),
						'param_name'  => 'content_circle_size',
						'value'       => '1',
						'min'         => '0',
						'max'         => '20',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Content Circle Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the content circle border color.', 'fusion-builder' ),
						'param_name'  => 'content_circle_color',
						'value'       => '',
						'default'     => 'var(--awb-color3)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'content_circle_size',
								'value'    => '0',
								'operator' => '>',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Content Circle Border Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border style of the content circle.', 'fusion-builder' ),
						'param_name'  => 'content_circle_border_style',
						'value'       => [
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
							'double' => esc_attr__( 'Double', 'fusion-builder' ),
						],
						'default'     => 'solid',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'content_circle_size',
								'value'    => '0',
								'operator' => '>',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Content Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Contols the spacing between circle and content. In pixels.', 'fusion-builder' ),
						'param_name'  => 'content_padding',
						'value'       => '50',
						'min'         => '0',
						'max'         => '200',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the title typography, if left empty will inherit from globals.', 'fusion-builder' ),
						'param_name'       => 'title_typography',
						'choices'          => [
							'font-family'    => 'title_font',
							'font-size'      => 'title_font_size',
							'line-height'    => 'title_line_height',
							'letter-spacing' => 'title_letter_spacing',
							'text-transform' => 'title_text_transform',
							'color'          => 'title_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
							'color'          => 'var(--awb-color8)',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Title Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the title hover color.', 'fusion-builder' ),
						'param_name'  => 'title_hover_color',
						'value'       => '',
						'default'     => 'var(--awb-color4)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Content Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the content typography, if left empty will inherit from globals.', 'fusion-builder' ),
						'param_name'       => 'content_typography',
						'choices'          => [
							'font-family'    => 'content_font',
							'font-size'      => 'content_font_size',
							'line-height'    => 'content_line_height',
							'letter-spacing' => 'content_letter_spacing',
							'text-transform' => 'content_text_transform',
							'color'          => 'content_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
							'color'          => $fusion_settings->get( 'body_typography', 'color' ),
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icon Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Contols the icon size. In pixels.', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'value'       => '16',
						'min'         => '10',
						'max'         => '200',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Icon Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Customize icon styling.', 'fusion-builder' ),
						'param_name'       => 'icon_styles',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'active'  => esc_html__( 'Hover/Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'active'  => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon color.', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'default'     => 'var(--awb-color1)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon background color.', 'fusion-builder' ),
						'param_name'  => 'icon_bg_color',
						'value'       => '',
						'default'     => 'var(--awb-color8)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icon Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Contols the icon border size. In pixels.', 'fusion-builder' ),
						'param_name'  => 'icon_border_size',
						'value'       => '0',
						'min'         => '0',
						'max'         => '20',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon border color.', 'fusion-builder' ),
						'param_name'  => 'icon_border_color',
						'value'       => '',
						'default'     => 'var(--awb-color8)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon_border_size',
								'value'    => '0',
								'operator' => '>',
							],
						],
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Border Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border style of the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_border_style',
						'value'       => [
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
							'double' => esc_attr__( 'Double', 'fusion-builder' ),
						],
						'default'     => 'solid',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon_border_size',
								'value'    => '0',
								'operator' => '>',
							],
						],
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Hover/Active Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon hover/active color.', 'fusion-builder' ),
						'param_name'  => 'icon_active_color',
						'value'       => '',
						'default'     => 'var(--awb-color1)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'active',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Background Hover/Active Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon background hover/active color.', 'fusion-builder' ),
						'param_name'  => 'icon_bg_active_color',
						'value'       => '',
						'default'     => 'var(--awb-color4)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'active',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icon Hover/Active Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Contols the icon hover/active border size. In pixels.', 'fusion-builder' ),
						'param_name'  => 'icon_active_border_size',
						'value'       => '0',
						'min'         => '0',
						'max'         => '20',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'active',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Border Hover/Active Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon border hover color.', 'fusion-builder' ),
						'param_name'  => 'icon_border_active_color',
						'value'       => '',
						'default'     => 'var(--awb-color8)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon_active_border_size',
								'value'    => '0',
								'operator' => '>',
							],
						],
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'active',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Hover/Active Border Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover/active border style of the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_active_border_style',
						'value'       => [
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
							'double' => esc_attr__( 'Double', 'fusion-builder' ),
						],
						'default'     => 'solid',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon_active_border_size',
								'value'    => '0',
								'operator' => '>',
							],
						],
						'subgroup'    => [
							'name' => 'icon_styles',
							'tab'  => 'active',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icons Box Shadow', 'fusion-builder' ),
						'description' => esc_attr__( 'Set to "Yes" to enable box shadows for icons.', 'fusion-builder' ),
						'param_name'  => 'box_shadow',
						'default'     => 'yes',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Icons Box Shadow Position', 'fusion-builder' ),
						'description'      => esc_attr__( 'Set the vertical and horizontal position of the icons box shadow. Positive values put the shadow below and right of the box, negative values put it above and left of the box. In pixels, ex. 5px.', 'fusion-builder' ),
						'param_name'       => 'dimension_box_shadow',
						'value'            => [
							'box_shadow_vertical'   => '',
							'box_shadow_horizontal' => '',
						],
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'box_shadow',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icons Box Shadow Blur Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the blur radius of the icons box shadow. In pixels.', 'fusion-builder' ),
						'param_name'  => 'box_shadow_blur',
						'value'       => '0',
						'min'         => '0',
						'max'         => '100',
						'step'        => '1',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'box_shadow',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icons Box Shadow Spread Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the spread radius of the icons box shadow. A positive value increases the size of the shadow, a negative value decreases the size of the shadow. In pixels.', 'fusion-builder' ),
						'param_name'  => 'box_shadow_spread',
						'value'       => '0',
						'min'         => '-100',
						'max'         => '100',
						'step'        => '1',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'box_shadow',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icons Box Shadow Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the icons box shadow.', 'fusion-builder' ),
						'param_name'  => 'box_shadow_color',
						'value'       => '',
						'default'     => 'var(--awb-color3)',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'box_shadow',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_attr__( 'Content Area Background Type', 'fusion-builder' ),
						'description'      => esc_attr__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'background_type',
						'default'          => 'single',
						'group'            => esc_attr__( 'Background', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'single'   => esc_attr__( 'Color', 'fusion-builder' ),
							'gradient' => esc_attr__( 'Gradient', 'fusion-builder' ),
							'image'    => esc_attr__( 'Image', 'fusion-builder' ),
						],
						'icons'            => [
							'single'   => '<span class="fusiona-fill-drip-solid" style="font-size:18px;"></span>',
							'gradient' => '<span class="fusiona-gradient-fill" style="font-size:18px;"></span>',
							'image'    => '<span class="fusiona-image" style="font-size:18px;"></span>',
							'video'    => '<span class="fusiona-video" style="font-size:18px;"></span>',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Gradient Start Color', 'fusion-builder' ),
						'param_name'  => 'gradient_start_color',
						'default'     => '',
						'description' => esc_attr__( 'Select start color for gradient.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Gradient End Color', 'fusion-builder' ),
						'param_name'  => 'gradient_end_color',
						'default'     => '',
						'description' => esc_attr__( 'Select end color for gradient.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Start Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select start position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_start_position',
						'value'       => '0',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient End Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select end position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_end_position',
						'value'       => '100',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Gradient Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls gradient type.', 'fusion-builder' ),
						'param_name'  => 'gradient_type',
						'default'     => 'linear',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
						'value'       => [
							'linear' => esc_attr__( 'Linear', 'fusion-builder' ),
							'radial' => esc_attr__( 'Radial', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Radial Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Select direction for radial gradient.', 'fusion-builder' ),
						'param_name'  => 'radial_direction',
						'default'     => 'center center',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
						'value'       => [
							'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
							'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
							'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
							'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
							'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
							'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
							'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
							'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
							'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'radial',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Angle', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient angle. In degrees.', 'fusion-builder' ),
						'param_name'  => 'linear_angle',
						'value'       => '180',
						'min'         => '0',
						'max'         => '360',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'linear',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'single',
						],
					],
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'Background Image', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload an image to display in the background.', 'fusion-builder' ),
						'param_name'   => 'background_image',
						'value'        => '',
						'group'        => esc_attr__( 'Background', 'fusion-builder' ),
						'dynamic_data' => true,
						'subgroup'     => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Background Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Background Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'background_image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the background image.', 'fusion-builder' ),
						'param_name'  => 'background_position',
						'default'     => 'left top',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'value'       => [
							'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
							'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
							'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
							'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
							'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
							'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
							'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
							'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
							'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Repeat', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the background image repeats.', 'fusion-builder' ),
						'param_name'  => 'background_repeat',
						'default'     => 'no-repeat',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'value'       => [
							'no-repeat' => esc_attr__( 'No Repeat', 'fusion-builder' ),
							'repeat'    => esc_attr__( 'Repeat Vertically and Horizontally', 'fusion-builder' ),
							'repeat-x'  => esc_attr__( 'Repeat Horizontally', 'fusion-builder' ),
							'repeat-y'  => esc_attr__( 'Repeat Vertically', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Blend Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how blending should work for each background layer.', 'fusion-builder' ),
						'param_name'  => 'background_blend_mode',
						'value'       => [
							'none'        => esc_attr__( 'Disabled', 'fusion-builder' ),
							'multiply'    => esc_attr__( 'Multiply', 'fusion-builder' ),
							'screen'      => esc_attr__( 'Screen', 'fusion-builder' ),
							'overlay'     => esc_attr__( 'Overlay', 'fusion-builder' ),
							'darken'      => esc_attr__( 'Darken', 'fusion-builder' ),
							'lighten'     => esc_attr__( 'Lighten', 'fusion-builder' ),
							'color-dodge' => esc_attr__( 'Color Dodge', 'fusion-builder' ),
							'color-burn'  => esc_attr__( 'Color Burn', 'fusion-builder' ),
							'hard-light'  => esc_attr__( 'Hard Light', 'fusion-builder' ),
							'soft-light'  => esc_attr__( 'Soft Light', 'fusion-builder' ),
							'difference'  => esc_attr__( 'Difference', 'fusion-builder' ),
							'exclusion'   => esc_attr__( 'Exclusion', 'fusion-builder' ),
							'hue'         => esc_attr__( 'Hue', 'fusion-builder' ),
							'saturation'  => esc_attr__( 'Saturation', 'fusion-builder' ),
							'color'       => esc_attr__( 'Color', 'fusion-builder' ),
							'luminosity'  => esc_attr__( 'Luminosity', 'fusion-builder' ),
						],
						'default'     => 'none',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					'fusion_margin_placeholder'    => [
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
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
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
					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-circles-info',
					],
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_circles_info' );

/**
 * Map shortcode to Avada Builder
 */
function fusion_element_circle_info() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_CirclesInfo',
			[
				'name'                     => esc_attr__( 'Cirlce Info', 'fusion-builder' ),
				'description'              => esc_attr__( 'Enter some content for this block.', 'fusion-builder' ),
				'shortcode'                => 'fusion_circle_info',
				'hide_from_builder'        => true,
				'inline_editor'            => true,
				'inline_editor_shortcodes' => false,
				'subparam_map'             => [
					'fusion_font_family_title_font'    => 'title_typography',
					'fusion_font_variant_title_font'   => 'title_typography',
					'title_font_size'                  => 'title_typography',
					'title_line_height'                => 'title_typography',
					'title_letter_spacing'             => 'title_typography',
					'title_text_transform'             => 'title_typography',
					'title_color'                      => 'title_typography',

					'fusion_font_family_content_font'  => 'content_typography',
					'fusion_font_variant_content_font' => 'content_typography',
					'content_font_size'                => 'content_typography',
					'content_line_height'              => 'content_typography',
					'content_letter_spacing'           => 'content_typography',
					'content_text_transform'           => 'content_typography',
					'content_color'                    => 'content_typography',
				],
				'params'                   => [
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Select Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_update_circles_info_icon',
							'args'     => [
								'selector' => '.awb-circles-info-tab-link',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Content Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Title of the content.', 'fusion-builder' ),
						'param_name'  => 'title',
						'value'       => esc_attr__( 'Your Title Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Content Title Link', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'description'  => esc_attr__( 'Add the title link ex: http://example.com.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'         => 'textarea',
						'heading'      => esc_attr__( 'Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert text for circle info.', 'fusion-builder' ),
						'param_name'   => 'item_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
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
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the title typography, if left empty will inherit from globals.', 'fusion-builder' ),
						'param_name'       => 'title_typography',
						'choices'          => [
							'font-family'    => 'title_font',
							'font-size'      => 'title_font_size',
							'line-height'    => 'title_line_height',
							'letter-spacing' => 'title_letter_spacing',
							'text-transform' => 'title_text_transform',
							'color'          => 'title_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
							'color'          => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Title Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the title hover color.', 'fusion-builder' ),
						'param_name'  => 'title_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Content Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the content typography, if left empty will inherit from globals.', 'fusion-builder' ),
						'param_name'       => 'content_typography',
						'choices'          => [
							'font-family'    => 'content_font',
							'font-size'      => 'content_font_size',
							'line-height'    => 'content_line_height',
							'letter-spacing' => 'content_letter_spacing',
							'text-transform' => 'content_text_transform',
							'color'          => 'content_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
							'color'          => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_attr__( 'Content Area Background Type', 'fusion-builder' ),
						'description'      => esc_attr__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'background_type',
						'default'          => 'single',
						'group'            => esc_attr__( 'Background', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'single'   => esc_attr__( 'Color', 'fusion-builder' ),
							'gradient' => esc_attr__( 'Gradient', 'fusion-builder' ),
							'image'    => esc_attr__( 'Image', 'fusion-builder' ),
						],
						'icons'            => [
							'single'   => '<span class="fusiona-fill-drip-solid" style="font-size:18px;"></span>',
							'gradient' => '<span class="fusiona-gradient-fill" style="font-size:18px;"></span>',
							'image'    => '<span class="fusiona-image" style="font-size:18px;"></span>',
							'video'    => '<span class="fusiona-video" style="font-size:18px;"></span>',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Gradient Start Color', 'fusion-builder' ),
						'param_name'  => 'gradient_start_color',
						'default'     => '',
						'description' => esc_attr__( 'Select start color for gradient.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Gradient End Color', 'fusion-builder' ),
						'param_name'  => 'gradient_end_color',
						'default'     => '',
						'description' => esc_attr__( 'Select end color for gradient.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Start Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select start position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_start_position',
						'value'       => '',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient End Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select end position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_end_position',
						'value'       => '',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Gradient Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls gradient type.', 'fusion-builder' ),
						'param_name'  => 'gradient_type',
						'default'     => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'linear' => esc_attr__( 'Linear', 'fusion-builder' ),
							'radial' => esc_attr__( 'Radial', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Radial Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Select direction for radial gradient.', 'fusion-builder' ),
						'param_name'  => 'radial_direction',
						'default'     => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
						'value'       => [
							''              => esc_attr__( 'Default', 'fusion-builder' ),
							'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
							'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
							'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
							'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
							'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
							'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
							'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
							'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
							'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'radial',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Angle', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient angle. In degrees.', 'fusion-builder' ),
						'param_name'  => 'linear_angle',
						'value'       => '',
						'min'         => '0',
						'max'         => '360',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'gradient',
						],
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'linear',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'single',
						],
					],
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'Background Image', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload an image to display in the background.', 'fusion-builder' ),
						'param_name'   => 'background_image',
						'value'        => '',
						'group'        => esc_attr__( 'Background', 'fusion-builder' ),
						'dynamic_data' => true,
						'subgroup'     => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Background Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Background Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'background_image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the background image.', 'fusion-builder' ),
						'param_name'  => 'background_position',
						'default'     => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'value'       => [
							''              => esc_attr__( 'Default', 'fusion-builder' ),
							'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
							'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
							'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
							'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
							'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
							'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
							'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
							'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
							'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Repeat', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the background image repeats.', 'fusion-builder' ),
						'param_name'  => 'background_repeat',
						'default'     => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'no-repeat' => esc_attr__( 'No Repeat', 'fusion-builder' ),
							'repeat'    => esc_attr__( 'Repeat Vertically and Horizontally', 'fusion-builder' ),
							'repeat-x'  => esc_attr__( 'Repeat Horizontally', 'fusion-builder' ),
							'repeat-y'  => esc_attr__( 'Repeat Vertically', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Blend Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how blending should work for each background layer.', 'fusion-builder' ),
						'param_name'  => 'background_blend_mode',
						'value'       => [
							''            => esc_attr__( 'Default', 'fusion-builder' ),
							'none'        => esc_attr__( 'Disabled', 'fusion-builder' ),
							'multiply'    => esc_attr__( 'Multiply', 'fusion-builder' ),
							'screen'      => esc_attr__( 'Screen', 'fusion-builder' ),
							'overlay'     => esc_attr__( 'Overlay', 'fusion-builder' ),
							'darken'      => esc_attr__( 'Darken', 'fusion-builder' ),
							'lighten'     => esc_attr__( 'Lighten', 'fusion-builder' ),
							'color-dodge' => esc_attr__( 'Color Dodge', 'fusion-builder' ),
							'color-burn'  => esc_attr__( 'Color Burn', 'fusion-builder' ),
							'hard-light'  => esc_attr__( 'Hard Light', 'fusion-builder' ),
							'soft-light'  => esc_attr__( 'Soft Light', 'fusion-builder' ),
							'difference'  => esc_attr__( 'Difference', 'fusion-builder' ),
							'exclusion'   => esc_attr__( 'Exclusion', 'fusion-builder' ),
							'hue'         => esc_attr__( 'Hue', 'fusion-builder' ),
							'saturation'  => esc_attr__( 'Saturation', 'fusion-builder' ),
							'color'       => esc_attr__( 'Color', 'fusion-builder' ),
							'luminosity'  => esc_attr__( 'Luminosity', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
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
add_action( 'fusion_builder_before_init', 'fusion_element_circle_info' );
