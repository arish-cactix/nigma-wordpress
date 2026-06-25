<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_testimonials' ) ) {

	if ( ! class_exists( 'FusionSC_Testimonials' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Testimonials extends Fusion_Element {

			/**
			 * The testimonials counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $testimonials_counter = 1;

			/**
			 * The testimonials child counter.
			 *
			 * @access private
			 * @since 3.4
			 * @var int
			 */
			private $testimonials_child_counter = 1;

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
				add_filter( 'fusion_attr_testimonials-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-testimonials', [ $this, 'testimonials_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-blockquote', [ $this, 'blockquote_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-quote', [ $this, 'quote_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-quote-content', [ $this, 'quote_content_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-review', [ $this, 'review_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-thumbnail', [ $this, 'thumbnail_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-image', [ $this, 'image_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-author', [ $this, 'author_attr' ] );
				add_filter( 'fusion_attr_testimonials-shortcode-pagination', [ $this, 'pagination_attr' ] );

				add_shortcode( 'fusion_testimonials', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_testimonial', [ $this, 'render_child' ] );
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
				$border_radius   = Fusion_Builder_Border_Radius_Helper::get_border_radius_array_with_fallback_value( $fusion_settings->get( 'testimonial_border_radius' ) );

				$parent = [
					'margin_top'                           => '',
					'margin_right'                         => '',
					'margin_bottom'                        => '',
					'margin_left'                          => '',
					'hide_on_mobile'                       => fusion_builder_default_visibility( 'string' ),
					'class'                                => '',
					'id'                                   => '',
					'backgroundcolor'                      => strtolower( $fusion_settings->get( 'testimonial_bg_color' ) ),
					'design'                               => 'classic',
					'navigation'                           => '',
					'speed'                                => $fusion_settings->get( 'testimonials_speed' ),
					'random'                               => $fusion_settings->get( 'testimonials_random' ),
					'name_company_text_color'              => '',
					'fusion_font_family_name_company_font' => '',
					'fusion_font_variant_name_company_font' => '',
					'name_company_font_size'               => '',
					'name_company_line_height'             => '',
					'name_company_letter_spacing'          => '',
					'name_company_text_transform'          => '',
					'navigation_size'                      => '',
					'navigation_color'                     => '',
					'textcolor'                            => strtolower( $fusion_settings->get( 'testimonial_text_color' ) ),
					'fusion_font_family_testimonial_text_font' => '',
					'fusion_font_variant_testimonial_text_font' => '',
					'testimonial_text_font_size'           => '',
					'testimonial_text_line_height'         => '',
					'testimonial_text_letter_spacing'      => '',
					'testimonial_text_text_transform'      => '',
					'testimonial_border_top'               => $fusion_settings->get( 'testimonial_border_width', 'top' ),
					'testimonial_border_right'             => $fusion_settings->get( 'testimonial_border_width', 'right' ),
					'testimonial_border_bottom'            => $fusion_settings->get( 'testimonial_border_width', 'bottom' ),
					'testimonial_border_left'              => $fusion_settings->get( 'testimonial_border_width', 'left' ),
					'testimonial_border_style'             => $fusion_settings->get( 'testimonial_border_style' ),
					'testimonial_border_color'             => $fusion_settings->get( 'testimonial_border_color' ),
					'border_radius_top_left'               => $border_radius['top_left'],
					'border_radius_top_right'              => $border_radius['top_right'],
					'border_radius_bottom_right'           => $border_radius['bottom_right'],
					'border_radius_bottom_left'            => $border_radius['bottom_left'],
					'testimonial_speech_bubble'            => 'show',
				];

				$child = [
					'alignment_classic'          => '',
					'avatar'                     => 'male',
					'avatar_position'            => 'above',
					'avatar_size'                => '',
					'company'                    => '',
					'image'                      => '',
					'image_id'                   => '',
					'image_border_radius'        => '',
					'link'                       => '',
					'name'                       => '',
					'target'                     => '_self',
					'testimonial_icon'           => '',
					'testimonial_icon_alignment' => 'left',
					'gender'                     => '',  // Deprecated.
				];

				return fusion_get_context_specific_values( $context, $parent, $child );
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
				$fusion_settings = awb_get_fusion_settings();

				$parent = [
					'testimonial_bg_color'                 => 'backgroundcolor',
					'testimonials_random'                  => 'random',
					'testimonial_text_color'               => 'textcolor',
					'testimonial_border_width[top]'        => 'testimonial_border_top',
					'testimonial_border_width[right]'      => 'testimonial_border_right',
					'testimonial_border_width[bottom]'     => 'testimonial_border_bottom',
					'testimonial_border_width[left]'       => 'testimonial_border_left',
					'testimonial_border_style'             => 'testimonial_border_style',
					'testimonial_border_color'             => 'testimonial_border_color',
					'testimonial_border_radius[top_left]'  => 'border_radius_top_left',
					'testimonial_border_radius[top_right]' => 'border_radius_top_right',
					'testimonial_border_radius[bottom_right]' => 'border_radius_bottom_right',
					'testimonial_border_radius[bottom_left]' => 'border_radius_bottom_left',
				];

				return fusion_get_context_specific_values( $context, $parent );
			}

			/**
			 * Render the parent shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args     Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				$defaults   = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_testimonials' );
				$this->args = $defaults;

				if ( 'yes' === $defaults['random'] || '1' === $defaults['random'] ) {
					$defaults['random'] = 1;
				} else {
					$defaults['random'] = 0;
				}

				if ( 'clean' === $defaults['design'] && '' === $defaults['navigation'] ) {
					$defaults['navigation'] = 'yes';
				} elseif ( 'classic' === $defaults['design'] && '' === $defaults['navigation'] ) {
					$defaults['navigation'] = 'no';
				}

				extract( $defaults );

				$this->parent_args = $defaults;

				$pagination = '';
				if ( 'yes' === $this->parent_args['navigation'] ) {
					preg_match_all( '/\[fusion_testimonial [^\/]*\/\]|\[fusion_testimonial .*\].*(.|\s|\S)*\/fusion_testimonial\]/U', $content, $single_testimonials );

					if ( isset( $single_testimonials[0] ) ) {
						$pagination = '<div ' . FusionBuilder::attributes( 'testimonials-shortcode-pagination' ) . '>';
						$count      = count( $single_testimonials[0] );

						if ( 1 < $count ) {
							for ( $i = 0; $i < $count; $i++ ) {
								$active_class = 0 === $i ? ' class="activeSlide"' : '';
								$pagination  .= '<a href="#" aria-label="' . esc_attr__( 'Testimonial Pagination', 'fusion-builder' ) . '" ' . $active_class . '></a>';
							}
						}
						$pagination .= '</div>';
					}
				}

				if ( $this->parent_args['random'] ) {
					if ( ! isset( $single_testimonials[0] ) ) {
						preg_match_all( '/\[fusion_testimonial [^\/]*\/\]|\[fusion_testimonial .*\].*(.|\s|\S)*\/fusion_testimonial\]/U', $content, $single_testimonials );
					}

					if ( isset( $single_testimonials[0] ) ) {
						shuffle( $single_testimonials[0] );
						$content = implode( '', $single_testimonials[0] );
					}
				}

				fusion_element_rendering_elements( true );
				$html = sprintf(
					'<div %s><div %s>%s</div>%s</div>',
					FusionBuilder::attributes( 'testimonials-shortcode' ),
					FusionBuilder::attributes( 'testimonials-shortcode-testimonials' ),
					do_shortcode( $content ),
					$pagination
				);
				fusion_element_rendering_elements( false );

				$this->testimonials_counter++;
				$this->testimonials_child_counter = 1;

				$this->on_render();

				return apply_filters( 'fusion_element_testimonials_parent_content', $html, $args );
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
						'class' => 'fusion-testimonials ' . $this->parent_args['design'] . ' awb-speech-bubble-' . $this->parent_args['testimonial_speech_bubble'] . ' fusion-testimonials-' . $this->testimonials_counter,
						'style' => '',
					]
				);

				$attr['data-random'] = $this->parent_args['random'];
				$attr['data-speed']  = $this->parent_args['speed'];

				$attr['style'] .= $this->get_style_variables();

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
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_style_variables() {
				$css_vars_options = [
					'name_company_text_color'         => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'name_company_font_size'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'name_company_line_height'        => [ 'callback' => [ 'Fusion_Sanitize', 'size' ] ],
					'name_company_letter_spacing'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'name_company_text_transform',
					'textcolor'                       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'testimonial_text_font_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'testimonial_text_line_height'    => [ 'callback' => [ 'Fusion_Sanitize', 'size' ] ],
					'testimonial_text_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'testimonial_text_text_transform',
					'backgroundcolor'                 => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'margin_top'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'                    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'                   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'testimonial_border_style',
					'testimonial_border_color'        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'navigation_size'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'navigation_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
				];

				$styles  = $this->get_css_vars_for_options( $css_vars_options );
				$styles .= Fusion_Builder_Border_Radius_Helper::get_border_radius_vars( $this->parent_args );

				$custom_vars = [
					'testimonial-border-width-top'    => fusion_library()->sanitize->get_value_with_unit( $this->args['testimonial_border_top'] ),
					'testimonial-border-width-right'  => fusion_library()->sanitize->get_value_with_unit( $this->args['testimonial_border_right'] ),
					'testimonial-border-width-bottom' => fusion_library()->sanitize->get_value_with_unit( $this->args['testimonial_border_bottom'] ),
					'testimonial-border-width-left'   => fusion_library()->sanitize->get_value_with_unit( $this->args['testimonial_border_left'] ),
				];

				$styles .= $this->get_custom_css_vars( $custom_vars, false );

				return $styles . $this->get_font_styling_vars( 'testimonial_text_font' ) . $this->get_font_styling_vars( 'name_company_font' );
			}

			/**
			 * Builds the testimonials attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function testimonials_attr() {
				return [
					'class' => 'reviews',
				];
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_testimonial' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_testimonial', $args );

				$defaults['image_border_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['image_border_radius'], 'px' );

				if ( 'round' === $defaults['image_border_radius'] ) {
					$defaults['image_border_radius'] = '50%';
				}

				extract( $defaults );

				$this->child_args = $defaults;

				// Check for deprecated.
				if ( $gender ) {
					$this->child_args['avatar'] = $gender;
				}

				if ( 'clean' === $this->parent_args['design'] ) {
					$html = $this->render_child_clean( $content );
				} else {
					$html = $this->render_child_classic( $content );
				}

				$this->testimonials_child_counter++;

				return apply_filters( 'fusion_element_testimonials_child_content', $html, $args );
			}

			/**
			 * Render classic design.
			 *
			 * @access private
			 * @since 1.0
			 * @param string $content The content.
			 * @return string
			 */
			private function render_child_classic( $content ) {

				$inner_content = $thumbnail = $pic = '';

				if ( 'image' === $this->child_args['avatar'] && $this->child_args['image'] ) {

					$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->child_args['image_id'], $this->child_args['image'] );

					$this->child_args['image_width']  = $image_data['width'];
					$this->child_args['image_height'] = $image_data['height'];
					$this->child_args['image_alt']    = $image_data['alt'];

					$pic = sprintf( '<img %s />', FusionBuilder::attributes( 'testimonials-shortcode-image' ) );
				}

				if ( 'image' === $this->child_args['avatar'] && ! $this->child_args['image'] ) {
					$this->child_args['avatar'] = 'none';
				}

				if ( 'none' !== $this->child_args['avatar'] ) {
					$thumbnail = sprintf( '<span %s>%s</span>', FusionBuilder::attributes( 'testimonials-shortcode-thumbnail' ), $pic );
				}

				$inner_content .= sprintf( '<div %s>%s<span %s>', FusionBuilder::attributes( 'testimonials-shortcode-author' ), $thumbnail, FusionBuilder::attributes( 'company-name' ) );

				if ( $this->child_args['name'] ) {
					$inner_content .= sprintf( '<strong>%s</strong>', $this->child_args['name'] );
				}

				if ( $this->child_args['name'] && $this->child_args['company'] ) {
					$inner_content .= '<span>, </span>';
				}

				if ( $this->child_args['company'] ) {

					if ( ! empty( $this->child_args['link'] ) && $this->child_args['link'] ) {

						$combined_attribs = 'target="' . $this->child_args['target'] . '"';
						if ( '_blank' === $this->child_args['target'] ) {
							$combined_attribs = 'target="' . $this->child_args['target'] . '" rel="noopener noreferrer"';
						}
						$inner_content .= sprintf( '<a href="%s" %s>%s</a>', $this->child_args['link'], $combined_attribs, sprintf( '<span>%s</span>', $this->child_args['company'] ) );

					} else {

						$inner_content .= sprintf( '<span>%s</span>', $this->child_args['company'] );

					}
				}

				$inner_content .= '</span></div>';

				$icon = '' !== $this->child_args['testimonial_icon'] ? '<i ' . FusionBuilder::attributes( 'testimonials-shortcode-icon' ) . '></i>' : '';

				$triangle = 'show' === $this->parent_args['testimonial_speech_bubble'] ? '<span class="awb-triangle"></span>' : '';

				$html = sprintf(
					'<div %s><blockquote><div %s>%s<div %s>%s</div></div>%s</blockquote>%s</div>',
					FusionBuilder::attributes( 'testimonials-shortcode-review' ),
					FusionBuilder::attributes( 'testimonials-shortcode-quote' ),
					$icon,
					FusionBuilder::attributes( 'testimonials-shortcode-quote-content' ),
					do_shortcode( $content ),
					$triangle,
					$inner_content
				);

				return $html;
			}

			/**
			 * Render clean design.
			 *
			 * @access private
			 * @since 1.0
			 * @param string $content The content.
			 * @return string
			 */
			private function render_child_clean( $content ) {

				$thumbnail = $pic = $author = '';

				if ( 'image' === $this->child_args['avatar'] && $this->child_args['image'] ) {

					$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->child_args['image_id'], $this->child_args['image'] );

					$this->child_args['image_width']  = $image_data['width'];
					$this->child_args['image_height'] = $image_data['height'];
					$this->child_args['image_alt']    = $image_data['alt'];

					if ( ! $this->child_args['image_id'] ) {
						$this->child_args['image_id'] = $image_data['id'];
					}

					$pic = sprintf( '<img %s />', FusionBuilder::attributes( 'testimonials-shortcode-image' ) );
				}

				if ( 'image' === $this->child_args['avatar'] && ! $this->child_args['image'] ) {
					$this->child_args['avatar'] = 'none';
				}

				if ( 'none' !== $this->child_args['avatar'] ) {
					$thumbnail = sprintf( '<div %s>%s</div>', FusionBuilder::attributes( 'testimonials-shortcode-thumbnail' ), $pic );
				}

				$author .= sprintf( '<div %s><span %s>', FusionBuilder::attributes( 'testimonials-shortcode-author' ), FusionBuilder::attributes( 'company-name' ) );

				if ( $this->child_args['name'] ) {
					$author .= sprintf( '<strong>%s</strong>', $this->child_args['name'] );
				}

				if ( $this->child_args['name'] && $this->child_args['company'] ) {
					$author .= ', ';
				}

				if ( $this->child_args['company'] ) {

					if ( ! empty( $this->child_args['link'] ) && $this->child_args['link'] ) {
						$combined_attribs = 'target="' . $this->child_args['target'] . '"';
						if ( '_blank' === $this->child_args['target'] ) {
							$combined_attribs = 'target="' . $this->child_args['target'] . '" rel="noopener noreferrer"';
						}
						$author .= sprintf( '<a href="%s" %s>%s</a>', $this->child_args['link'], $combined_attribs, sprintf( '<span>%s</span>', $this->child_args['company'] ) );
					} else {
						$author .= sprintf( '<span>%s</span>', $this->child_args['company'] );
					}
				}

				$author .= '</span></div>';

				$icon = '' !== $this->child_args['testimonial_icon'] ? '<i ' . FusionBuilder::attributes( 'testimonials-shortcode-icon' ) . '></i>' : '';

				if ( 'below' === $this->child_args['avatar_position'] ) {
					$testimonial_html = '<div %1$s><blockquote %3$s><div %4$s>%8$s<div %5$s>%6$s</div></div></blockquote>%2$s%7$s</div>';
				} else {
					$testimonial_html = '<div %1$s>%2$s<blockquote %3$s><div %4$s>%8$s<div %5$s>%6$s</div></div></blockquote>%7$s</div>';
				}

				$html = sprintf(
					$testimonial_html,
					FusionBuilder::attributes( 'testimonials-shortcode-review' ),
					$thumbnail,
					FusionBuilder::attributes( 'testimonials-shortcode-blockquote' ),
					FusionBuilder::attributes( 'testimonials-shortcode-quote' ),
					FusionBuilder::attributes( 'testimonials-shortcode-quote-content' ),
					do_shortcode( $content ),
					$author,
					$icon
				);

				return $html;
			}

			/**
			 * Get the child style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_style_variables_child() {
				$this->args = $this->child_args;

				$css_vars_options = [
					'avatar_size' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];

				$styles = $this->get_css_vars_for_options( $css_vars_options );

				return $styles;
			}

			/**
			 * Builds the blockquote attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function blockquote_attr() {
				$attr = [];

				if ( Fusion_Color::new_color( $this->parent_args['backgroundcolor'] )->is_color_transparent() && 'none' !== $this->child_args['avatar'] ) {
					$attr['class'] = 'has-transparent-color';
				}

				return $attr;
			}

			/**
			 * Builds the quotes attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function quote_attr() {
				$attr = [
					'class' => 'awb-quote',
				];

				if ( '' !== $this->child_args['testimonial_icon'] ) {
					$attr['class'] .= ' awb-testimonial-icon';
				}

				return $attr;
			}

			/**
			 * Builds the quotes content attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function quote_content_attr() {
				$attr = [
					'class' => 'awb-quote-content',
				];

				return $attr;
			}

			/**
			 * Builds the quotes attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {
				$attr = [
					'class' => 'awb-t-icon-' . $this->child_args['testimonial_icon_alignment'] . ' ' . $this->child_args['testimonial_icon'],
				];

				return $attr;
			}

			/**
			 * Builds the review attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function review_attr() {

				$attr = [
					'class' => 'review ',
					'style' => '',
				];

				if ( 1 === $this->testimonials_child_counter ) {
					$attr['class'] .= 'active-testimonial';
				}

				if ( 'classic' === $this->parent_args['design'] ) {
					if ( $this->child_args['alignment_classic'] ) {
						$attr['class'] .= ' alignment-' . $this->child_args['alignment_classic'];
					}
				} else {
					$attr['class'] .= ' avatar-' . $this->child_args['avatar_position'];
				}

				if ( 'none' === $this->child_args['avatar'] ) {
					$attr['class'] .= ' no-avatar';
				} elseif ( 'image' === $this->child_args['avatar'] ) {
					$attr['class'] .= ' avatar-image';
				} else {
					$attr['class'] .= ' ' . $this->child_args['avatar'];
				}

				$attr['style'] .= $this->get_style_variables_child();

				return $attr;
			}

			/**
			 * Builds the thumbnail attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function thumbnail_attr() {

				$attr = [
					'class' => 'testimonial-thumbnail',
				];

				if ( 'image' !== $this->child_args['avatar'] ) {
					$attr['class'] .= ' doe';
				}

				return $attr;
			}

			/**
			 * Builds the image attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function image_attr() {
				$attr = [
					'class'  => 'testimonial-image',
					'src'    => $this->child_args['image'],
					'width'  => $this->child_args['image_width'],
					'height' => $this->child_args['image_height'],
					'alt'    => $this->child_args['image_alt'],
					'style'  => '',
				];

				if ( $this->child_args['image_border_radius'] ) {
					$custom_vars['border-radius'] = $this->child_args['image_border_radius'];

					$attr['style'] .= $this->get_custom_css_vars( $custom_vars );
				}

				$attr = fusion_library()->images->lazy_load_attributes( $attr, $this->child_args['image_id'] );

				return $attr;
			}

			/**
			 * Builds the author attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function author_attr() {
				return [
					'class' => 'author',
				];
			}

			/**
			 * Builds the pagination attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function pagination_attr() {
				return [
					'class' => 'testimonial-pagination',
					'id'    => 'fusion-testimonials-' . $this->testimonials_counter,
				];
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Testimonials settings.
			 */
			public function add_options() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'testimonials_shortcode_section' => [
						'label'       => esc_html__( 'Testimonials', 'fusion-builder' ),
						'description' => '',
						'id'          => 'testimonials_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-bubbles',
						'fields'      => [
							'testimonial_bg_color'      => [
								'label'       => esc_html__( 'Testimonial Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the testimonial background.', 'fusion-builder' ),
								'id'          => 'testimonial_bg_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--testimonial_bg_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'testimonial_border_width'  => [
								'label'       => esc_html__( 'Testimonial Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Set the border size of the testimonial.', 'fusion-builder' ),
								'id'          => 'testimonial_border_width',
								'choices'     => [
									'top'    => true,
									'right'  => true,
									'bottom' => true,
									'left'   => true,
								],
								'default'     => [
									'top'    => '0px',
									'bottom' => '0px',
									'left'   => '0px',
									'right'  => '0px',
								],
								'type'        => 'spacing',
								'css_vars'    => [
									[
										'name'   => '--testimonial-border-width-top',
										'choice' => 'top',
										'po'     => false,
									],
									[
										'name'   => '--testimonial-border-width-right',
										'choice' => 'right',
										'po'     => false,
									],
									[
										'name'   => '--testimonial-border-width-bottom',
										'choice' => 'bottom',
										'po'     => false,
									],
									[
										'name'   => '--testimonial-border-width-left',
										'choice' => 'left',
										'po'     => false,
									],
								],
							],
							'testimonial_border_style'  => [
								'type'        => 'radio-buttonset',
								'label'       => esc_html__( 'Testimonial Border Style', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border style of the testimonial.', 'fusion-builder' ),
								'id'          => 'testimonial_border_style',
								'default'     => 'solid',
								'transport'   => 'postMessage',
								'choices'     => [
									'solid'  => esc_html__( 'Solid', 'fusion-builder' ),
									'dashed' => esc_html__( 'Dashed', 'fusion-builder' ),
									'dotted' => esc_html__( 'Dotted', 'fusion-builder' ),
								],
								'css_vars'    => [
									[
										'name'    => '--awb-testimonial-border-style-default',
										'element' => 'body',
									],
								],
							],
							'testimonial_border_color'  => [
								'type'        => 'color-alpha',
								'label'       => esc_html__( 'Testimonial Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the testimonial.', 'fusion-builder' ),
								'id'          => 'testimonial_border_color',
								'default'     => 'var(--awb-color3)',
								'css_vars'    => [
									[
										'name'     => '--awb-testimonial-border-color-default',
										'element'  => 'body',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'testimonial_border_radius' => [
								'label'       => esc_html__( 'Testimonial Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Set the border radius of the testimonial.', 'fusion-builder' ),
								'id'          => 'testimonial_border_radius',
								'choices'     => [
									'top_left'     => true,
									'top_right'    => true,
									'bottom_right' => true,
									'bottom_left'  => true,
									'units'        => [ 'px', '%', 'em' ],
								],
								'default'     => [
									'top_left'     => '0px',
									'top_right'    => '0px',
									'bottom_right' => '0px',
									'bottom_left'  => '0px',
								],
								'type'        => 'border_radius',
								'css_vars'    => [
									[
										'name'    => '--awb-testimonial-border-top-left-radius-default',
										'choice'  => 'top_left',
										'element' => 'body',
									],
									[
										'name'    => '--awb-testimonial-border-top-right-radius-default',
										'choice'  => 'top_right',
										'element' => 'body',
									],
									[
										'name'    => '--awb-testimonial-border-bottom-right-radius-default',
										'choice'  => 'bottom_right',
										'element' => 'body',
									],
									[
										'name'    => '--awb-testimonial-border-bottom-left-radius-default',
										'choice'  => 'bottom_left',
										'element' => 'body',
									],
								],

								// Could update variable here, but does not look necessary as set inline.
								'transport'   => 'postMessage',
							],
							'testimonial_text_color'    => [
								'label'       => esc_html__( 'Testimonial Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the testimonial text.', 'fusion-builder' ),
								'id'          => 'testimonial_text_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--testimonial_text_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'testimonials_speed'        => [
								'label'       => esc_html__( 'Testimonials Speed', 'fusion-builder' ),
								'description' => __( 'Controls the speed of the testimonial slider. ex: 1000 = 1 second. <strong>IMPORTANT:</strong> Setting speed to 0 will disable autoplay for testimonials slider.', 'fusion-builder' ),
								'id'          => 'testimonials_speed',
								'default'     => '4000',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'max'  => '20000',
									'step' => '250',
								],
							],
							'testimonials_random'       => [
								'label'       => esc_html__( 'Random Order', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display testimonials in a random order.', 'fusion-builder' ),
								'id'          => 'testimonials_random',
								'default'     => '0',
								'type'        => 'switch',
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
				$fusion_settings = awb_get_fusion_settings();

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-testimonials',
					FusionBuilder::$js_folder_url . '/general/fusion-testimonials.js',
					FusionBuilder::$js_folder_path . '/general/fusion-testimonials.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);
				Fusion_Dynamic_JS::localize_script(
					'fusion-testimonials',
					'fusionTestimonialVars',
					[
						'testimonials_speed' => intval( $fusion_settings->get( 'testimonials_speed' ) ),
					]
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/testimonials.min.css' );
			}
		}
	}

	new FusionSC_Testimonials();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_testimonials() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Testimonials',
			[
				'name'          => esc_attr__( 'Testimonials', 'fusion-builder' ),
				'shortcode'     => 'fusion_testimonials',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_testimonial',
				'icon'          => 'fusiona-bubbles',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-testimonials-preview.php',
				'preview_id'    => 'fusion-builder-block-module-testimonials-preview-template',
				'child_ui'      => true,
				'sortable'      => false,
				'help_url'      => 'https://avada.com/documentation/testimonials-element/',
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_html__( 'Enter some content for this testimonial element.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_testimonial name="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" avatar="male" image="" image_border_radius="" company="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" link="" target="_self"]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_testimonial]',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Design', 'fusion-builder' ),
						'description' => esc_html__( 'Choose a design for the element.', 'fusion-builder' ),
						'param_name'  => 'design',
						'value'       => [
							'classic' => esc_html__( 'Classic', 'fusion-builder' ),
							'clean'   => esc_html__( 'Clean', 'fusion-builder' ),
						],
						'default'     => 'classic',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Navigation Bullets', 'fusion-builder' ),
						'description' => esc_html__( 'Select to show navigation bullets.', 'fusion-builder' ),
						'param_name'  => 'navigation',
						'value'       => [
							'yes' => esc_html__( 'Show', 'fusion-builder' ),
							'no'  => esc_html__( 'Hide', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'range',
						'heading'     => esc_html__( 'Testimonials Speed', 'fusion-builder' ),
						'description' => __( 'Set the speed of the testimonial slider. ex: 1000 = 1 second. <strong>IMPORTANT:</strong> Setting speed to 0 will disable autoplay for testimonials slider.', 'fusion-builder' ),
						'param_name'  => 'speed',
						'default'     => $fusion_settings->get( 'testimonials_speed' ),
						'min'         => '0',
						'max'         => '20000',
						'step'        => '250',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Testimonial Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background color of the testimonial. ', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'testimonial_bg_color' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Testimonial Border Size', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the border size of the testimonial. In pixels.', 'fusion-builder' ),
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'       => 'testimonial_border_width',
						'value'            => [
							'testimonial_border_top'    => '',
							'testimonial_border_right'  => '',
							'testimonial_border_bottom' => '',
							'testimonial_border_left'   => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Testimonial Border Style', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border style of the testimonial.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'  => 'testimonial_border_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
						],
						'default'     => $fusion_settings->get( 'testimonial_border_style' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Testimonial Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border color of the testimonial. ', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'  => 'testimonial_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'testimonial_border_color' ),
					],
					'fusion_border_radius_placeholder' => [
						'heading' => esc_html__( 'Testimonial Border Radius', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Testimonial Speech Bubble Style', 'fusion-builder' ),
						'description' => esc_html__( 'Enable or disable the testimonial speech bubble triangle.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'  => 'testimonial_speech_bubble',
						'default'     => 'show',
						'value'       => [
							'show' => esc_html__( 'Show', 'fusion-builder' ),
							'hide' => esc_html__( 'Hide', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'design',
								'value'    => 'classic',
								'operator' => '=',
							],
						],
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_html__( 'Testimonial Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the testimonial text typography.', 'fusion-builder' ),
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'       => 'testimonial_typography',
						'choices'          => [
							'font-family'    => 'testimonial_text_font',
							'font-size'      => 'testimonial_text_font_size',
							'line-height'    => 'testimonial_text_line_height',
							'letter-spacing' => 'testimonial_text_letter_spacing',
							'text-transform' => 'testimonial_text_text_transform',
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
						'heading'     => esc_html__( 'Testimonial Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the text color.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'  => 'textcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'testimonial_text_color' ),
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_html__( 'Name / Company Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the name and company typography.', 'fusion-builder' ),
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'       => 'name_company_typography',
						'choices'          => [
							'font-family'    => 'name_company_font',
							'font-size'      => 'name_company_font_size',
							'line-height'    => 'name_company_line_height',
							'letter-spacing' => 'name_company_letter_spacing',
							'text-transform' => 'name_company_text_transform',
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
						'heading'     => esc_html__( 'Name / Company Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the name / company text color.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'  => 'name_company_text_color',
						'value'       => '',
						'default'     => '',
					],
					[
						'type'        => 'range',
						'heading'     => esc_html__( 'Navigation Bullet Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the size of the navigation bullets. In pixels.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'  => 'navigation_size',
						'value'       => '12',
						'min'         => '0',
						'max'         => '100',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'navigation',
								'value'    => 'yes',
								'operator' => '=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Navigation Bullet Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the navigation bullets.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'param_name'  => 'navigation_color',
						'default'     => 'var(--awb-textcolor)',
						'dependency'  => [
							[
								'element'  => 'navigation',
								'value'    => 'yes',
								'operator' => '=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Random Order', 'fusion-builder' ),
						'description' => esc_html__( 'Turn on to display testimonials in a random order.' ),
						'param_name'  => 'random',
						'value'       => [
							''    => esc_html__( 'Default', 'fusion-builder' ),
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					'fusion_margin_placeholder'        => [
						'param_name' => 'margin',
						'group'      => esc_html__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_html__( 'Element Visibility', 'fusion-builder' ),
						'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_html__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_html__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_html__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_html__( 'General', 'fusion-builder' ),
					],
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_testimonials' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_testimonial() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Testimonials',
			[
				'name'              => esc_html__( 'Testimonial', 'fusion-builder' ),
				'shortcode'         => 'fusion_testimonial',
				'hide_from_builder' => true,
				'allow_generator'   => true,
				'params'            => [
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Name', 'fusion-builder' ),
						'description' => esc_html__( 'Insert the name of the person.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => esc_html__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Avatar', 'fusion-builder' ),
						'description' => esc_html__( 'Choose which kind of Avatar to be displayed.', 'fusion-builder' ),
						'param_name'  => 'avatar',
						'value'       => [
							'none'   => esc_html__( 'None', 'fusion-builder' ),
							'male'   => esc_html__( 'Male', 'fusion-builder' ),
							'female' => esc_html__( 'Female', 'fusion-builder' ),
							'image'  => esc_html__( 'Image', 'fusion-builder' ),
						],
						'icons'       => [
							'male'   => '<svg width="18" height="18" viewBox="0 0 1024 1024"><path d="M889.366 737.92c-44.8-58.454-98.986-96.426-176.618-117.952l-72.748 254.698c0 23.466-19.2 42.666-42.666 42.666s-42.666-19.2-42.666-42.666v-202.666c0-29.44-23.894-53.334-53.334-53.334s-53.334 23.894-53.334 53.334v202.666c0 23.466-19.2 42.666-42.666 42.666s-42.666-19.2-42.666-42.666l-72.746-254.698c-77.654 21.76-131.84 59.498-176.64 117.952-17.708 23.040-27.308 69.334-27.948 94.080v106.666c0 47.146 38.186 85.334 85.334 85.334h661.334c47.146 0 85.334-38.186 85.334-85.334v-106.666c-0.642-24.746-10.242-71.040-27.97-94.080zM501.334 533.334c143.786 0 224-183.040 224-307.628s-100.268-225.706-224-225.706-224 101.12-224 225.706 77.652 307.628 224 307.628z"></path></svg>',
							'female' => '<svg width="18" height="18" viewBox="0 0 1024 1024"><path d="M889.366 737.92c-24.96-32.618-52.886-58.88-86.4-79.552-51.82 114.966-167.446 194.966-301.632 194.966s-249.814-80-301.674-194.986c-33.28 20.694-61.418 46.934-86.378 79.552-17.708 23.060-27.33 69.354-27.948 94.1 0.214 6.4 0 106.666 0 106.666 0 47.146 38.186 85.334 85.334 85.334h661.334c47.146 0 85.334-38.186 85.334-85.334 0 0-0.214-100.266 0-106.666-0.642-24.746-10.242-71.040-27.97-94.080zM385.472 602.666c-17.898 1.92-34.986 4.266-51.178 7.040-18.56 4.694-32.64 21.546-32.64 41.6 0 8.32 2.346 15.766 6.4 22.4 44.8 57.388 114.752 94.294 193.28 94.294 76.586 0 144.854-34.986 189.866-89.814 5.952-7.254 9.366-16.64 9.366-26.88 0-20.906-15.146-38.4-35.2-42.026-16.618-3.008-34.134-5.568-52.886-7.488-24.106-4.458-42.24-21.526-42.24-47.126 0-23.466 17.472-41.366 40.96-44.374 2.326-0.426 7.872-0.618 7.872-0.618 114.794-8.128 192.874-31.382 244.266-75.754 7.062-7.466 11.328-17.494 11.328-28.586 0-22.186-16.854-40.32-38.4-42.454-63.36-12.8-110.932-65.28-110.932-128.214v-8.96c0-124.586-100.268-225.706-224-225.706s-224 101.12-224 225.706v8.96c0 62.934-47.574 115.414-110.934 128.214-21.546 2.134-38.4 20.266-38.4 42.454 0 11.094 4.266 21.12 11.286 28.586 51.648 44.374 129.706 67.626 244.714 75.754 3.414 0 6.634 0.214 9.6 0.618 23.872 3.414 38.806 20.48 38.806 44.374 0.020 27.308-20.438 44.8-46.934 48z"></path></svg>',
							'image'  => '<span class="fusiona-image" style="font-size:18px;"></span>',
						],
						'default'     => 'male',
					],
					[
						'type'        => 'upload',
						'heading'     => esc_html__( 'Custom Avatar', 'fusion-builder' ),
						'description' => esc_html__( 'Upload a custom avatar image.', 'fusion-builder' ),
						'param_name'  => 'image',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'avatar',
								'value'    => 'image',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Avatar Image ID', 'fusion-builder' ),
						'description' => esc_html__( 'Avatar Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Avatar Position', 'fusion-builder' ),
						'description' => esc_html__( 'Controls whether the Avatar will be shown above or below the testimonial content.', 'fusion-builder' ),
						'param_name'  => 'avatar_position',
						'value'       => [
							'above' => esc_html__( 'Above', 'fusion-builder' ),
							'below' => esc_html__( 'Below', 'fusion-builder' ),
						],
						'default'     => 'above',
						'dependency'  => [
							[
								'element'  => 'parent_design',
								'value'    => 'clean',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_html__( 'Avatar Size', 'fusion-builder' ),
						'description' => esc_html__( 'Set the size of the testimonial avatar.', 'fusion-builder' ),
						'param_name'  => 'avatar_size',
						'default'     => '',
						'min'         => '0',
						'max'         => '400',
						'step'        => '1',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Avatar Border Radius', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the radius of the testimonial avatar. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'param_name'  => 'image_border_radius',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'avatar',
								'value'    => 'image',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Company', 'fusion-builder' ),
						'description' => esc_html__( 'Insert the name of the company.', 'fusion-builder' ),
						'param_name'  => 'company',
						'value'       => esc_html__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'link_selector',
						'heading'     => esc_html__( 'Link', 'fusion-builder' ),
						'description' => esc_html__( 'Add the URL the company name will link to.', 'fusion-builder' ),
						'param_name'  => 'link',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Link Target', 'fusion-builder' ),
						'description' => esc_html__( 'Controls how the link will open.', 'fusion-builder' ),
						'param_name'  => 'target',
						'value'       => [
							'_self'  => esc_html__( 'Same Window/Tab', 'fusion-builder' ),
							'_blank' => esc_html__( 'New Window/Tab', 'fusion-builder' ),
						],
						'default'     => '_self',
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'tinymce',
						'heading'     => esc_html__( 'Testimonial Content', 'fusion-builder' ),
						'description' => esc_html__( 'Add the testimonial content.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => esc_html__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the testimonial alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment_classic',
						'value'       => [
							'left'  => esc_html__( 'Left', 'fusion-builder' ),
							'right' => esc_html__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'dependency'  => [
							[
								'element'  => 'parent_design',
								'value'    => 'classic',
								'operator' => '==',
							],
						],
					],
					[
						'type'         => 'iconpicker',
						'heading'      => esc_html__( 'Background Icon', 'fusion-builder' ),
						'description'  => esc_html__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'param_name'   => 'testimonial_icon',
						'value'        => '',

						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Icon Alignment', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the alignment of the icon on the testimonial.', 'fusion-builder' ),
						'param_name'  => 'testimonial_icon_alignment',
						'value'       => [
							'left'  => esc_html__( 'Left', 'fusion-builder' ),
							'right' => esc_html__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',

						'dependency'  => [
							[
								'element'  => 'testimonial_icon',
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
add_action( 'fusion_builder_before_init', 'fusion_element_testimonial' );
