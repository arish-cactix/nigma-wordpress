<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_imageframe' ) ) {

	if ( ! class_exists( 'FusionSC_Imageframe' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Imageframe extends Fusion_Element {

			/**
			 * The image-frame counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $imageframe_counter = 1;

			/**
			 * The image data.
			 *
			 * @access private
			 * @since 1.0
			 * @var false|array
			 */
			private $image_data = false;

			/**
			 * The lightbox image data.
			 *
			 * @access private
			 * @since 1.7
			 * @var false|array
			 */
			private $lightbox_image_data = false;

			/**
			 * An array of wrapper attributes.
			 *
			 * @access protected
			 * @since 3.0
			 * @var array
			 */
			protected $wrapper_attr = [
				'class' => '',
				'style' => '',
			];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_image-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_image-shortcode-link', [ $this, 'link_attr' ] );
				add_filter( 'fusion_attr_image-shortcode-tag-element', [ $this, 'tag_element_attr' ] );
				add_filter( 'fusion_attr_image-shortcode-special-container', [ $this, 'special_container_attr' ] );
				add_filter( 'fusion_attr_image-shortcode-responsive-container', [ $this, 'responsive_container_attr' ] );
				add_filter( 'fusion_attr_image-shortcode-caption', [ $this, 'caption_attr' ] );
				add_filter( 'fusion_attr_image-magnify', [ $this, 'image_magnify' ] );
				add_filter( 'fusion_attr_image-scroll', [ $this, 'image_scroll' ] );

				add_shortcode( 'fusion_imageframe', [ $this, 'render' ] );
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
					'src'                                  => '',
					'align'                                => '',
					'align_medium'                         => '',
					'align_small'                          => '',
					'margin_bottom'                        => '',
					'margin_left'                          => '',
					'margin_right'                         => '',
					'margin_top'                           => '',
					'margin_bottom_medium'                 => '',
					'margin_left_medium'                   => '',
					'margin_right_medium'                  => '',
					'margin_top_medium'                    => '',
					'margin_bottom_small'                  => '',
					'margin_left_small'                    => '',
					'margin_right_small'                   => '',
					'margin_top_small'                     => '',
					'alt'                                  => '',
					'animation_direction'                  => 'left',
					'animation_offset'                     => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                      => '',
					'animation_delay'                      => '',
					'animation_type'                       => '',
					'animation_color'                      => '',
					'blur'                                 => $fusion_settings->get( 'imageframe_blur' ),
					'bordercolor'                          => $fusion_settings->get( 'imgframe_border_color' ),
					'borderradius'                         => intval( $fusion_settings->get( 'imageframe_border_radius' ) ) . 'px',
					'bordersize'                           => $fusion_settings->get( 'imageframe_border_size' ),
					'class'                                => '',
					'gallery_id'                           => '',
					'hide_on_mobile'                       => fusion_builder_default_visibility( 'string' ),
					'sticky_display'                       => '',
					'hover_type'                           => 'none',
					'magnify_full_img'                     => '',
					'magnify_duration'                     => '1',
					'scroll_height'                        => '100',
					'scroll_speed'                         => '1',
					'image_id'                             => '',
					'id'                                   => '',
					'lightbox'                             => 'no',
					'lightbox_image'                       => '',
					'lightbox_image_id'                    => '',
					'link'                                 => '',
					'link_attributes'                      => '',
					'linktarget'                           => '_self',
					'max_width'                            => '',
					'skip_lazy_load'                       => '',
					'sticky_max_width'                     => '',
					'stylecolor'                           => $fusion_settings->get( 'imgframe_style_color' ),
					'style_type'                           => $fusion_settings->get( 'imageframe_style_type' ),
					'z_index'                              => '',

					// Filters.
					'filter_hue'                           => '0',
					'filter_saturation'                    => '100',
					'filter_brightness'                    => '100',
					'filter_contrast'                      => '100',
					'filter_invert'                        => '0',
					'filter_sepia'                         => '0',
					'filter_opacity'                       => '100',
					'filter_blur'                          => '0',
					'filter_hover_element'                 => 'self',
					'filter_hue_hover'                     => '0',
					'filter_saturation_hover'              => '100',
					'filter_brightness_hover'              => '100',
					'filter_contrast_hover'                => '100',
					'filter_invert_hover'                  => '0',
					'filter_sepia_hover'                   => '0',
					'filter_opacity_hover'                 => '100',
					'filter_blur_hover'                    => '0',

					// Caption params.
					'caption_style'                        => 'off',
					'caption_title'                        => '',
					'caption_text'                         => '',
					'caption_title_color'                  => '',
					'caption_title_size'                   => '',
					'caption_title_line_height'            => '',
					'caption_title_letter_spacing'         => '',
					'caption_title_tag'                    => '2',
					'fusion_font_family_caption_title_font' => '',
					'fusion_font_variant_caption_title_font' => '',
					'caption_text_color'                   => '',
					'caption_text_size'                    => '',
					'caption_text_line_height'             => '',
					'caption_text_letter_spacing'          => '',
					'fusion_font_family_caption_text_font' => '',
					'fusion_font_variant_caption_text_font' => '',
					'caption_border_color'                 => '',
					'caption_overlay_color'                => $fusion_settings->get( 'primary_color' ),
					'caption_background_color'             => '',
					'caption_margin_top'                   => '',
					'caption_margin_right'                 => '',
					'caption_margin_bottom'                => '',
					'caption_margin_left'                  => '',
					'caption_title_transform'              => '',
					'caption_text_transform'               => '',
					'caption_align'                        => 'none',
					'caption_align_medium'                 => 'none',
					'caption_align_small'                  => 'none',

					// Mask.
					'mask'                                 => '',
					'custom_mask'                          => '',
					'mask_size'                            => '',
					'mask_custom_size'                     => '',
					'mask_position'                        => '',
					'mask_custom_position'                 => '',
					'mask_repeat'                          => '',

					// aspect ratio.
					'aspect_ratio'                         => '',
					'custom_aspect_ratio'                  => '',
					'aspect_ratio_position'                => '',

					// Deprecated params.
					'style'                                => '',
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
					'animation_offset'         => 'animation_offset',
					'imageframe_blur'          => 'blur',
					'imgframe_border_color'    => 'bordercolor',
					'imageframe_border_radius' => 'borderradius',
					'imageframe_border_size'   => 'bordersize',
					'imgframe_style_color'     => 'stylecolor',
					'imageframe_style_type'    => 'style_type',
					'lazy_load'                => 'lazy_load',
				];
			}

			/**
			 * Sets the args from the attributes.
			 *
			 * @access public
			 * @since 3.0
			 * @param array $args Element attributes.
			 * @return void
			 */
			public function set_args( $args ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_imageframe' );
			}

			/**
			 * Validate the arguments into correct format.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function validate_args() {
				$this->args['blur']         = FusionBuilder::validate_shortcode_attr_value( $this->args['blur'], 'px' );
				$this->args['borderradius'] = FusionBuilder::validate_shortcode_attr_value( $this->args['borderradius'], 'px' );
				$this->args['bordersize']   = FusionBuilder::validate_shortcode_attr_value( $this->args['bordersize'], 'px' );

				// Validate margin values.
				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {
						$margin_key                = 'large' === $size ? 'margin_' . $direction : 'margin_' . $direction . '_' . $size;
						$this->args[ $margin_key ] = FusionBuilder::validate_shortcode_attr_value( $this->args[ $margin_key ], 'px' );
					}
				}

				// Validate caption color.
				foreach ( [ 'caption_title_color', 'caption_text_color', 'caption_border_color', 'caption_overlay_color', 'caption_background_color' ] as $key ) {
					if ( ! $this->is_default( $key ) ) {
						$this->args[ $key ] = fusion_library()->sanitize->color( $this->args[ $key ] );
					}
				}
			}

			/**
			 * Sets the extra args.
			 *
			 * @access public
			 * @since 3.0
			 * @param string $content Shortcode content.
			 * @return void
			 */
			public function set_extra_args( $content ) {
				if ( ! $this->args['style'] ) {
					$this->args['style'] = $this->args['style_type'];

					// If caption style used then disable style type.
					if ( ! in_array( $this->args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
						$this->args['style'] = 'none';
					}

					// If mask used then disable style type.
					if ( '' !== $this->args['mask'] ) {
						$this->args['style'] = 'none';
					}
				}

				if ( $this->args['borderradius'] && 'bottomshadow' === $this->args['style'] ) {
					$this->args['borderradius'] = '0';
				}

				if ( 'round' === $this->args['borderradius'] ) {
					$this->args['borderradius'] = '50%';
				}

				$this->args['border_radius'] = '';

				if ( '0' !== $this->args['borderradius'] && 0 !== $this->args['borderradius'] && '0px' !== $this->args['borderradius'] ) {
					$this->args['border_radius'] .= "border-radius:{$this->args['borderradius']};";
				}

				// The URL is added as element content, but where image ID was not available.
				if ( false === strpos( $content, '<img' ) && $content ) {
					$this->args['src'] = $content;
				} elseif ( empty( $this->args['src'] ) ) {

					// Old version, where the img tag was added in element contant.
					preg_match( '/(src=["\'](.*?)["\'])/', $content, $this->args['src'] );
					if ( array_key_exists( '2', $this->args['src'] ) ) {
						$this->args['src'] = $this->args['src'][2];
					}
				}

				if ( $this->args['src'] ) {
					$this->args['src'] = str_replace( '&#215;', 'x', $this->args['src'] );
				}

				$this->args['pic_link'] = $this->args['lightbox_image'] ? $this->args['lightbox_image'] : $this->args['src'];
			}

			/**
			 * Sets the image data.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function set_image_data() {
				if ( $this->args['lightbox_image'] ) {
					$this->lightbox_image_data = fusion_library()->images->get_attachment_data_by_helper( $this->args['lightbox_image_id'], $this->args['lightbox_image'] );
				}

				if ( is_array( $this->args['src'] ) && empty( $this->args['src'] ) ) {
					$this->args['image_id'] = false;
				}

				$this->image_data = fusion_library()->images->get_attachment_data_by_helper( $this->args['image_id'], $this->args['src'] );
			}

			/**
			 * Builds the image element attributes array.
			 *
			 * @access public
			 * @since 3.0
			 * @param string $content Shortcode content.
			 * @return array
			 */
			public function tag_element_attr( $content ) {
				$attr = [
					'width'  => $this->image_data['width'],
					'height' => $this->image_data['height'],
					'alt'    => $this->image_data['alt'],
					'title'  => $this->image_data['title_attribute'],
					'src'    => $this->args['src'] ? $this->args['src'] : $this->image_data['url'],
				];

				// For pre 5.0 shortcodes extract the alt tag.
				preg_match( '/(alt=["\'](.*?)["\'])/', $content, $legacy_alt );
				if ( array_key_exists( '2', $legacy_alt ) && '' !== $legacy_alt[2] ) {
					$attr['alt'] = $legacy_alt[2];
				} elseif ( ! empty( $this->args['alt'] ) ) {
					$attr['alt'] = $this->args['alt'];
				}

				if ( ! ( 'no' === $this->args['lightbox'] && ! $this->args['link'] ) ) {
					unset( $attr['title'] );
				}

				if ( '' !== $this->args['aspect_ratio'] ) {
					$attr['class'] = 'img-with-aspect-ratio';
				}
				return $attr;
			}

			/**
			 * Builds the special container attributes array.
			 *
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public function special_container_attr() {
				$attr = [
					'class' => 'awb-image-frame awb-image-frame-' . $this->element_id,
					'style' => '',
				];

				if ( 'liftup' === $this->args['hover_type'] && in_array( $this->args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
					$attr['class'] .= ' imageframe-liftup';

					if ( ! fusion_element_rendering_is_flex() ) {
						if ( 'left' === $this->args['align'] ) {
							$attr['class'] .= ' fusion-imageframe-liftup-left';
						} elseif ( 'right' === $this->args['align'] ) {
							$attr['class'] .= ' fusion-imageframe-liftup-right';
						}
					}

					if ( $this->args['border_radius'] ) {
						$attr['class'] .= ' imageframe-' . $this->element_id;
					}
				}

				if ( 'bottomshadow' === $this->args['style'] ) {
					$attr['class'] .= ' awb-bottomshadow';
				}

				if ( '' !== $this->args['hover_type'] && '' !== $this->args['mask'] ) {
					$attr['class'] .= ' hover-with-mask';
				}

				if ( ! fusion_element_rendering_is_flex() ) {
					$attr['class'] = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr['class'] );
				}

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				$attr['style'] = '';
				if ( $this->args['max_width'] && '' === $this->args['aspect_ratio'] ) {
					$attr['style'] = 'max-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['max_width'] ) . ';';

					if ( 'bottomshadow' === $this->args['style'] ) {
						$attr['style'] .= 'display:inline-block;';
					}
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}				

				return $attr;
			}

			/**
			 * Builds the responsive container attributes array.
			 *
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public function responsive_container_attr() {
				$attr = [
					'class' => 'fusion-image-element ',
					'style' => '',
				];

				$align_large = ! empty( $this->args['align'] ) && 'none' !== $this->args['align'] ? $this->args['align'] : false;
				if ( $align_large ) {
					if ( ! fusion_element_rendering_is_flex() ) {
						$attr['class'] .= ' fusion-image-align-' . $this->args['align'];
					}
					$attr['style'] .= 'text-align:' . $this->args['align'] . ';';
				}

				$align_medium = ! empty( $this->args['align_medium'] ) && 'none' !== $this->args['align_medium'] ? $this->args['align_medium'] : false;
				if ( $align_medium && $align_large !== $align_medium ) {
					$attr['class'] .= ' md-text-align-' . $align_medium;
				}

				$align_small = ! empty( $this->args['align_small'] ) && 'none' !== $this->args['align_small'] ? $this->args['align_small'] : false;
				if ( $align_small && $align_large !== $align_small ) {
					$attr['class'] .= ' sm-text-align-' . $align_small;
				}

				if ( in_array( $this->args['caption_style'], [ 'above', 'below' ], true ) ) {
					$attr['class'] .= ' awb-imageframe-style awb-imageframe-style-' . $this->args['caption_style'] . ' awb-imageframe-style-' . $this->element_id;
				}

				// Responsive visiblity.

				$attr['class'] = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr['class'] );

				// Sticky normal visiblity.
				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				// Special variables.
				if ( 'liftup' === $this->args['hover_type'] && '' === $this->args['mask'] ) {
					if ( $this->args['borderradius'] ) {
						$attr['style'] .= '--awb-liftup-border-radius:' . fusion_library()->sanitize->get_value_with_unit( $this->args['borderradius'] ) . ';';
					}
				}

				$stylecolor = ( '#' === $this->args['stylecolor'][0] ) ? Fusion_Color::new_color( $this->args['stylecolor'] )->get_new( 'alpha', '0.4' )->to_css_var_or_rgba() : Fusion_Color::new_color( $this->args['stylecolor'] )->to_css_var_or_rgba();

				if ( 'bottomshadow' === $this->args['style'] ) {
					$attr['style'] .= '--awb-bottom-shadow-color:' . $stylecolor . ';';
				}

				if ( ! fusion_element_rendering_is_flex() ) {
					$attr['class'] .= ' in-legacy-container';
				}

				$attr['style'] .= $this->get_aspect_ratio_vars();
				$attr['style'] .= $this->get_mask_vars();

				$attr['style'] .= $this->get_style_variables();

				return $attr;
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$this->set_element_id( $this->imageframe_counter );

				$this->set_args( $args );

				$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_imageframe', $args );

				$this->validate_args();

				$this->set_extra_args( $content );

				$this->set_image_data();

				$is_logo_images = false;

				if ( is_array( $this->image_data ) ) {
					$image_array = json_decode( $this->image_data['url'], true );
					if ( is_array( $image_array ) ) {
						$content        = $this->get_logo_images( $image_array );
						$is_logo_images = true;
					} else {
						$atts    = FusionBuilder::attributes( 'image-shortcode-tag-element', $content );
						$atts    = false === strpos( $atts, 'alt=' ) ? $atts . ' alt' : $atts;
						$content = '<img ' . $atts . ' />';
					}
				}

				if ( ! $is_logo_images ) {
					$img_classes = 'img-responsive';

					if ( ! empty( $this->image_data['id'] ) ) {
						$img_classes .= ' wp-image-' . $this->image_data['id'];
					}

					// Get custom classes from the img tag.
					preg_match( '/(class=["\'](.*?)["\'])/', $content, $classes );

					if ( ! empty( $classes ) ) {
						$img_classes .= ' ' . $classes[2];
					}

					if ( 'skip' === $this->args['skip_lazy_load'] ) {
						$img_classes .= ' disable-lazyload';
					}

					$img_classes = 'class="' . $img_classes . '"';

					// Add custom and responsive class to the img tag.
					if ( ! empty( $classes ) ) {
						$content = str_replace( $classes[0], $img_classes, $content );
					} else {
						$content = str_replace( '/>', $img_classes . '/>', $content );
					}
				}

				fusion_library()->images->set_grid_image_meta(
					[
						'layout'  => 'large',
						'columns' => '1',
					]
				);

				$content = fusion_add_responsive_image_markup( $content );

				$image_id = false;

				if ( isset( $this->image_data['id'] ) ) {
					$image_id = $this->image_data['id'];
				}

				if ( 'skip' !== $this->args['skip_lazy_load'] ) {
					$content = fusion_library()->images->apply_lazy_loading( $content, null, $image_id, 'full' );
				}

				fusion_library()->images->set_grid_image_meta( [] );

				$output        = do_shortcode( $content );
				$html          = '';
				$image_magnify = '';
				$image_scroll  = '';

				if ( ! empty( $output ) ) {
					if ( ! in_array( $this->args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
						$output .= $this->render_caption();
					}

					if ( 'magnify' === $this->args['hover_type'] ) {
						$image_magnify = ' ' . FusionBuilder::attributes( 'image-magnify' );
					}
					if ( 'scroll' === $this->args['hover_type'] ) {
						$image_scroll = ' ' . FusionBuilder::attributes( 'image-scroll' );
					}

					if ( 'yes' === $this->args['lightbox'] || $this->args['link'] ) {
						$output = '<a ' . FusionBuilder::attributes( 'image-shortcode-link' ) . $image_magnify . '>' . $output . '</a>';
						$html   = '<span ' . FusionBuilder::attributes( 'image-shortcode' ) . $image_scroll . '>' . $output . '</span>';
					} else {
						$html = '<span ' . FusionBuilder::attributes( 'image-shortcode' ) . $image_magnify . $image_scroll . '>' . $output . '</span>';
					}

					if ( 'bottomshadow' === $this->args['style'] ) {
						$html .= '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="100%" viewBox="0 0 600 28" preserveAspectRatio="none"><g clip-path="url(#a)"><mask id="b" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="600" height="28"><path d="M0 0h600v28H0V0Z" fill="#fff"/></mask><g filter="url(#c)" mask="url(#b)"><path d="M16.439-18.667h567.123v30.8S438.961-8.4 300-8.4C161.04-8.4 16.438 12.133 16.438 12.133v-30.8Z" fill="#000"/></g></g><defs><clipPath id="a"><path fill="#fff" d="M0 0h600v28H0z"/></clipPath><filter id="c" x="5.438" y="-29.667" width="589.123" height="52.8" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feBlend in="SourceGraphic" in2="BackgroundImageFix" result="shape"/><feGaussianBlur stdDeviation="5.5" result="effect1_foregroundBlur_3983_183"/></filter></defs></svg>';
					}

					if ( 'liftup' === $this->args['hover_type'] || 'bottomshadow' === $this->args['style'] ) {
						$html = '<div ' . FusionBuilder::attributes( 'image-shortcode-special-container' ) . '>' . $html . '</div>';
					}

					if ( in_array( $this->args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
						$html = 'above' === $this->args['caption_style'] ? $this->render_caption() . $html : $html . $this->render_caption();
					}

					if ( '' !== $this->args['max_width'] && '' !== $this->args['aspect_ratio'] ) {
						$aspect_ratio_wrapper_style  = 'display:inline-block;';
						$aspect_ratio_wrapper_style .= 'width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['max_width'] ) . ';';
						if ( ! fusion_element_rendering_is_flex() ) {
							if ( 'left' === $this->args['align'] ) {
								$aspect_ratio_wrapper_style .= 'float:left;';
							} elseif ( 'right' === $this->args['align'] ) {
								$aspect_ratio_wrapper_style .= 'float:right;';
							}
						}
						$html = '<div style="' . esc_attr( $aspect_ratio_wrapper_style ) . '">' . $html . '</div>';
					}

					if ( 'center' === $this->args['align'] && ! fusion_element_rendering_is_flex() ) {
						$html = '<div ' . FusionBuilder::attributes( 'imageframe-align-center' ) . '>' . $html . '</div>';
					}

					$html = '<div ' . FusionBuilder::attributes( 'image-shortcode-responsive-container' ) . '>' . $html . '</div>';
				}

				$this->imageframe_counter++;
				$this->lightbox_image_data = false;
				$this->wrapper_attr        = [
					'class' => '',
					'style' => '',
				];

				$this->maybe_enqeue_on_render();

				$this->on_render();
				return apply_filters( 'fusion_element_image_content', $html, $args );
			}

			/**
			 * Render the caption.
			 *
			 * @access public
			 * @since 3.5
			 * @return string HTML output.
			 */
			public function render_caption() {
				if ( 'off' === $this->args['caption_style'] ) {
					return '';
				}
				$output  = '<div ' . FusionBuilder::attributes( 'image-shortcode-caption' ) . '><div class="awb-imageframe-caption">';
				$title   = '';
				$caption = '';

				if ( $this->image_data ) {
					if ( '' !== $this->image_data['title'] ) {
						$title = $this->image_data['title'];
					}
					if ( '' !== $this->image_data['caption'] ) {
						$caption = $this->image_data['caption'];
					}
				}

				if ( ! $this->is_default( 'caption_title' ) ) {
					$title = $this->args['caption_title'];
				}
				if ( ! $this->is_default( 'caption_text' ) ) {
					$caption = $this->args['caption_text'];
				}

				if ( '' !== $title ) {
					$title_tag = 'div' === $this->args['caption_title_tag'] ? 'div' : 'h' . $this->args['caption_title_tag'];
					$output   .= sprintf( '<%1$s class="awb-imageframe-caption-title">%2$s</%1$s>', $title_tag, $title );
				}
				if ( '' !== $caption ) {
					$output .= sprintf( '<p class="awb-imageframe-caption-text">%1$s</p>', $caption );
				}
				$output .= '</div></div>';
				return $output;
			}

			/**
			 * Generate mask styles.
			 *
			 * @access public
			 * @since 7.6
			 * @return string CSS vars.
			 */
			public function get_mask_vars() {
				if ( '' === $this->args['mask'] ) {
					return '';
				}

				// Mask image.
				$mask_url = 'custom' === $this->args['mask'] ? $this->args['custom_mask'] : FUSION_BUILDER_PLUGIN_URL . '/assets/images/masks/' . $this->args['mask'] . '.svg';
				$style    = '';

				if ( '' !== $mask_url ) {
					$style .= '--awb-mask-url: url(' . $mask_url . ');';
				}

				// Mask size.
				if ( ! $this->is_default( 'mask_size' ) ) {
					$mask_size = $this->args['mask_size'];
					if ( 'fit' === $mask_size ) {
						$style .= '--awb-mask-size: contain;';
					}

					if ( 'fill' === $mask_size ) {
						$style .= '--awb-mask-size: cover;';
					}

					if ( 'custom' === $mask_size ) {
						$style .= '--awb-mask-size: ' . $this->args['mask_custom_size'] . ';';
					}
				}

				// Mask position.
				if ( ! $this->is_default( 'mask_position' ) ) {
					$mask_position = 'custom' !== $this->args['mask_position'] ? str_replace( '-', ' ', $this->args['mask_position'] ) : $this->args['mask_custom_position'];
					$style        .= '--awb-mask-position: ' . $mask_position . ';';
				}

				// Mask Repeat.
				if ( ! $this->is_default( 'mask_repeat' ) ) {
					$style .= '--awb-mask-repeat: ' . $this->args['mask_repeat'] . ';';
				}

				return $style;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$bordercolor  = $this->args['bordercolor'];
				$stylecolor   = ( '#' === $this->args['stylecolor'][0] ) ? Fusion_Color::new_color( $this->args['stylecolor'] )->get_new( 'alpha', '0.3' )->to_css_var_or_rgba() : Fusion_Color::new_color( $this->args['stylecolor'] )->to_css_var_or_rgba();
				$blur         = $this->args['blur'];
				$blur_radius  = ( (int) $blur + 4 ) . 'px';
				$bordersize   = $this->args['bordersize'];
				$borderradius = $this->args['borderradius'];
				$style        = $this->args['style'];
				$img_styles   = '';

				$this->wrapper_attr['class'] .= ' fusion-imageframe';

				// Border style only if not using mask.
				if ( '' === $this->args['mask'] ) {
					if ( '0' !== $bordersize && 0 !== $bordersize && '0px' !== $bordersize ) {
						$img_styles .= "border:{$bordersize} solid {$bordercolor};";
					}

					if ( '0' !== $borderradius && 0 !== $borderradius && '0px' !== $borderradius ) {
						$img_styles .= "border-radius:{$borderradius};";
					}
				}

				if ( 'glow' === $style ) {
					$img_styles .= "-webkit-box-shadow: 0 0 {$blur} {$stylecolor};box-shadow: 0 0 {$blur} {$stylecolor};";
				} elseif ( 'dropshadow' === $style ) {
					$img_styles .= "-webkit-box-shadow: {$blur} {$blur} {$blur_radius} {$stylecolor};box-shadow: {$blur} {$blur} {$blur_radius} {$stylecolor};";
				}
				if ( 'scroll' === $this->args['hover_type'] ) {
					$img_styles .= '' !== $this->args['scroll_height'] ? 'height:' . $this->args['scroll_height'] . 'px;' : '';
				}
				if ( $img_styles ) {
					$this->wrapper_attr['style'] .= $img_styles;
				}

				$this->wrapper_attr['class'] .= ' imageframe-' . $this->args['style'] . ' imageframe-' . $this->element_id;

				if ( $this->args['z_index'] ) {
					$this->wrapper_attr['style'] .= 'z-index:' . $this->args['z_index'] . ';';
				}

				if ( 'liftup' !== $this->args['hover_type'] && ( 'bottomshadow' !== $this->args['style'] && ( 'zoomin' !== $this->args['hover_type'] || 'zoomout' !== $this->args['hover_type'] ) ) ) {

					if ( ! fusion_element_rendering_is_flex() ) {
						if ( 'left' === $this->args['align'] ) {
							$this->wrapper_attr['style'] .= 'margin-right:25px;float:left;';
						} elseif ( 'right' === $this->args['align'] ) {
							$this->wrapper_attr['style'] .= 'margin-left:25px;float:right;';
						}
					}
				}

				if ( 'liftup' !== $this->args['hover_type'] && in_array( $this->args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
					$this->wrapper_attr['class'] .= ' hover-type-' . $this->args['hover_type'];
				}

				// Caption style.
				if ( ! in_array( $this->args['caption_style'], [ 'off', 'above', 'below' ], true ) ) {
					$this->wrapper_attr['class'] .= ' awb-imageframe-style awb-imageframe-style-' . $this->args['caption_style'];
				}

				if ( '' !== $this->args['mask'] ) {
					$this->wrapper_attr['class'] .= ' has-mask';
				}

				if ( '' !== $this->args['aspect_ratio'] ) {
					$this->wrapper_attr['class'] .= ' has-aspect-ratio';
				}

				if ( $this->args['class'] ) {
					$this->wrapper_attr['class'] .= ' ' . $this->args['class'];
				}
				if ( 'magnify' === $this->args['hover_type'] && 'no' === $this->args['lightbox'] && '' === $this->args['link'] ) {
					$this->wrapper_attr['class'] .= ' has-image-magnify';
				}
				if ( 'scroll' === $this->args['hover_type'] ) {
					$this->wrapper_attr['class'] .= ' has-image-scroll';
				}
				if ( ( 'scroll' === $this->args['hover_type'] || 'magnify' === $this->args['hover_type'] ) && '' !== $this->args['mask'] ) {
					$this->wrapper_attr['class'] .= ' wrapper-mask';
				}
				if ( $this->args['id'] ) {
					$this->wrapper_attr['id'] = $this->args['id'];
				}

				if ( $this->args['animation_type'] && ! ( 'liftup' === $this->args['hover_type'] || 'bottomshadow' === $this->args['style'] ) ) {
					$this->wrapper_attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $this->wrapper_attr );
				}

				return $this->wrapper_attr;
			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function link_attr() {

				$attr = [];

				if ( 'yes' === $this->args['lightbox'] ) {
					$this->args['pic_link'] = is_string( $this->args['pic_link'] ) ? $this->args['pic_link'] : '';

					$attr['href']  = $this->args['pic_link'];
					$attr['class'] = 'fusion-lightbox';

					if ( 'magnify' === $this->args['hover_type'] ) {
						$attr['class'] .= ' has-image-magnify';
					}
					if ( $this->args['gallery_id'] || '0' === $this->args['gallery_id'] ) {
						$attr['data-rel'] = 'iLightbox[' . $this->args['gallery_id'] . ']';
					} else {
						$attr['data-rel'] = 'iLightbox[' . substr( md5( $this->args['pic_link'] ), 13 ) . ']';
					}

					if ( $this->lightbox_image_data ) {
						$attr['data-caption'] = $this->lightbox_image_data['caption'];
						$attr['data-title']   = $this->lightbox_image_data['title'];
					} elseif ( $this->image_data ) {
						$attr['data-caption'] = $this->image_data['caption'];
						$attr['data-title']   = $this->image_data['title'];
					}

					if ( $this->image_data ) {
						$attr['title'] = $this->image_data['title'];
					}
				} elseif ( $this->args['link'] ) {
					$attr['class'] = 'fusion-no-lightbox';
					if ( 'magnify' === $this->args['hover_type'] ) {
						$attr['class'] .= ' has-image-magnify';
					}
					$attr['href']       = $this->args['link'];
					$attr['target']     = $this->args['linktarget'];
					$attr['aria-label'] = ( $this->image_data ) ? $this->image_data['title'] : '';
					if ( '_blank' === $this->args['linktarget'] ) {
						$attr['rel'] = 'noopener noreferrer';
					}

					// Add additional, custom link attributes correctly formatted to the anchor.
					$attr = fusion_get_link_attributes( $this->args, $attr );
				}

				return $attr;
			}

			/**
			 * Builds the magnify attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function image_magnify() {

				$attr = [
					'data-magnify-img'      => '',
					'data-magnify-duration' => '',
				];

				// Image magnify attributes.
				if ( 'magnify' === $this->args['hover_type'] ) {
					$attr['data-magnify-img']      = '' !== $this->args['magnify_full_img'] ? $this->args['magnify_full_img'] : '';
					$attr['data-magnify-duration'] = '' !== $this->args['magnify_duration'] ? $this->args['magnify_duration'] : '';
				}

				return $attr;
			}

			/**
			 * Builds the magnify attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function image_scroll() {

				$attr = [
					'data-scroll-height' => '',
					'data-scroll-speed'  => '',
				];

				// Image scroll attributes.
				if ( 'scroll' === $this->args['hover_type'] ) {
					$attr['data-scroll-height'] = '' !== $this->args['scroll_height'] ? $this->args['scroll_height'] : '';
					$attr['data-scroll-speed']  = '' !== $this->args['scroll_speed'] ? $this->args['scroll_speed'] : '';
				}

				return $attr;
			}

			/**
			 * Builds the caption attributes array.
			 *
			 * @access public
			 * @since 3.5
			 * @return array
			 */
			public function caption_attr() {

				$attr = [
					'class' => 'awb-imageframe-caption-container',
					'style' => '',
				];

				if ( ! fusion_element_rendering_is_flex() ) {
					return $attr;
				}

				if ( in_array( $this->args['caption_style'], [ 'above', 'below' ], true ) ) {
					// Responsive alignment.
					foreach ( [ 'large', 'medium', 'small' ] as $size ) {
						$key = 'caption_align' . ( 'large' === $size ? '' : '_' . $size );

						$align = ! empty( $this->args[ $key ] ) && 'none' !== $this->args[ $key ] ? $this->args[ $key ] : false;
						if ( $align ) {
							if ( 'large' === $size ) {
								$attr['style'] .= 'text-align:' . $this->args[ $key ] . ';';
							} else {
								$attr['class'] .= ( 'medium' === $size ? ' md-text-align-' : ' sm-text-align-' ) . $this->args[ $key ];
							}
						}
					}
				}

				return $attr;
			}

			/**
			 * Generate logos images markup.
			 *
			 * @access public
			 * @since 3.0
			 * @param  array $images Array of images.
			 * @return string        HTML output.
			 */
			public function get_logo_images( $images ) {
				$content    = '';
				$normal_url = isset( $images['default']['normal']['url'] ) && '' !== $images['default']['normal']['url'];
				$sticky_url = isset( $images['sticky']['normal']['url'] ) && '' !== $images['sticky']['normal']['url'];
				$mobile_url = isset( $images['mobile']['normal']['url'] ) && '' !== $images['mobile']['normal']['url'];

				if ( $normal_url ) {
					$content .= $this->get_logo_image( $images['default'], 'fusion-standard-logo' );
				}
				if ( $sticky_url ) {
					$content .= $this->get_logo_image( $images['sticky'], 'fusion-sticky-logo' );
				}
				if ( $mobile_url ) {
					$content .= $this->get_logo_image( $images['mobile'], 'fusion-mobile-logo' );
				}

				return $content;
			}

			/**
			 * Generate logos image markup.
			 *
			 * @access public
			 * @since 3.0
			 * @param  array  $data  Array of image data.
			 * @param  string $class CSS class for item.
			 * @return string        HTML output.
			 */
			public function get_logo_image( $data, $class = '' ) {

				$logo_data = [
					'src'        => '',
					'srcset'     => '',
					'style'      => '',
					'retina_url' => false,
					'width'      => '',
					'height'     => '',
					'class'      => 'img-responsive ' . $class,
					'alt'        => apply_filters( 'fusion_logo_alt_tag', get_bloginfo( 'name', 'display' ) . ' ' . __( 'Logo', 'fusion-builder' ) ),
				];

				if ( ! empty( $data['id'] ) ) {
					$logo_data['class'] .= ' wp-image-' . $data['id'];
				}

				if ( 'skip' === $this->args['skip_lazy_load'] ) {
					$logo_data['class'] .= ' disable-lazyload';
				}

				$retina_url = isset( $data['retina']['url'] ) ? $data['retina']['url'] : '';
				$content    = '';

				$logo_url            = set_url_scheme( $data['normal']['url'] );
				$logo_data['srcset'] = $logo_url . ' 1x';

				// Get retina logo, if default one is not set.
				if ( '' === $logo_url ) {
					$logo_url            = set_url_scheme( $retina_url );
					$logo_data['srcset'] = $logo_url . ' 1x';
					$logo_data['src']    = $logo_url;
					$logo_data['width']  = $data['retina']['width'];
					$logo_data['height'] = $data['retina']['height'];

					if ( '' !== $logo_data['width'] ) {
						$logo_data['style'] = 'max-height:' . $logo_data['height'] . 'px;height:auto;';
					}
				} else {
					$logo_data['src']    = $logo_url;
					$logo_data['width']  = isset( $data['normal']['width'] ) ? $data['normal']['width'] : '';
					$logo_data['height'] = isset( $data['normal']['height'] ) ? $data['normal']['height'] : '';
				}

				if ( $data['normal'] && '' !== $data['normal'] && '' !== $logo_data['width'] && '' !== $logo_data['height'] ) {
					$retina_logo             = set_url_scheme( $retina_url );
					$logo_data['srcset']    .= ', ' . $retina_logo . ' 2x';
					$logo_data['retina_url'] = $retina_logo;

					if ( '' !== $logo_data['width'] ) {
						$logo_data['style'] = 'max-height:' . $logo_data['height'] . 'px;height:auto;';
					}
				}

				$content = '<img ' . FusionBuilder::attributes( 'fusion-logo-attributes', $logo_data ) . ' />';

				$this->wrapper_attr['class'] .= ' has-' . $class;

				return $content;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Image Frame settings.
			 */
			public function add_options() {

				return [
					'imageframe_shortcode_section' => [
						'label'       => esc_html__( 'Image', 'fusion-builder' ),
						'description' => '',
						'id'          => 'imageframe_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-image',
						'fields'      => [
							'imageframe_style_type'    => [
								'label'       => esc_html__( 'Image Style Type', 'fusion-builder' ),
								'description' => esc_html__( 'Select the style type.', 'fusion-builder' ),
								'id'          => 'imageframe_style_type',
								'default'     => 'none',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'none'         => esc_attr__( 'None', 'fusion-builder' ),
									'glow'         => esc_attr__( 'Glow', 'fusion-builder' ),
									'dropshadow'   => esc_attr__( 'Drop Shadow', 'fusion-builder' ),
									'bottomshadow' => esc_attr__( 'Bottom Shadow', 'fusion-builder' ),
								],
							],
							'imageframe_blur'          => [
								'label'           => esc_html__( 'Image Glow / Drop Shadow Blur', 'fusion-builder' ),
								'description'     => esc_html__( 'Choose the amount of blur added to glow or drop shadow effect.', 'fusion-builder' ),
								'id'              => 'imageframe_blur',
								'default'         => '3',
								'type'            => 'slider',
								'transport'       => 'postMessage',
								'choices'         => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
								'soft_dependency' => true,
							],
							'imgframe_style_color'     => [
								'label'           => esc_html__( 'Image Style Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the style color for all style types except border. Hex colors will use a subtle auto added alpha level to produce a nice effect.', 'fusion-builder' ),
								'id'              => 'imgframe_style_color',
								'default'         => 'var(--awb-color7)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'imageframe_border_size'   => [
								'label'       => esc_html__( 'Image Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the image.', 'fusion-builder' ),
								'id'          => 'imageframe_border_size',
								'default'     => '0',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
							],
							'imgframe_border_color'    => [
								'label'           => esc_html__( 'Image Border Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the border color of the image.', 'fusion-builder' ),
								'id'              => 'imgframe_border_color',
								'default'         => 'var(--awb-color3)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'imageframe_border_radius' => [
								'label'       => esc_html__( 'Image Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border radius of the image.', 'fusion-builder' ),
								'id'          => 'imageframe_border_radius',
								'default'     => '0px',
								'type'        => 'dimension',
								'choices'     => [ 'px', '%' ],
								'transport'   => 'postMessage',
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
				$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
				Fusion_Dynamic_JS::enqueue_script( 'fusion-animations' );
				Fusion_Dynamic_JS::enqueue_script( 'fusion-lightbox' );

				if ( $is_builder ) {
					Fusion_Dynamic_JS::enqueue_script(
						'fusion-magnify',
						FusionBuilder::$js_folder_url . '/library/jquery.zoom.js',
						FusionBuilder::$js_folder_path . '/library/jquery.zoom.js',
						[],
						FUSION_BUILDER_VERSION
					);

					Fusion_Dynamic_JS::enqueue_script(
						'fusion-image',
						FusionBuilder::$js_folder_url . '/general/fusion-image.js',
						FusionBuilder::$js_folder_path . '/general/fusion-image.js',
						[],
						FUSION_BUILDER_VERSION
					);
				}
			}
			/**
			 * Enqueue scripts conditionally.
			 *
			 * @access public
			 * @since 3.10
			 * @return void
			 */
			public function maybe_enqeue_on_render() {

				if ( 'magnify' === $this->args['hover_type'] ) {
					Fusion_Dynamic_JS::enqueue_script(
						'fusion-magnify',
						FusionBuilder::$js_folder_url . '/library/jquery.zoom.js',
						FusionBuilder::$js_folder_path . '/library/jquery.zoom.js',
						[],
						FUSION_BUILDER_VERSION,
						true
					);
				}

				if ( 'magnify' === $this->args['hover_type'] || 'scroll' === $this->args['hover_type'] ) {
					Fusion_Dynamic_JS::enqueue_script(
						'fusion-image',
						FusionBuilder::$js_folder_url . '/general/fusion-image.js',
						FusionBuilder::$js_folder_path . '/general/fusion-image.js',
						[],
						FUSION_BUILDER_VERSION,
						true
					);
				}
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'visibility_large'  => $fusion_settings->get( 'visibility_large' ),
					'visibility_medium' => $fusion_settings->get( 'visibility_medium' ),
					'visibility_small'  => $fusion_settings->get( 'visibility_small' ),
				];
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
					'margin_top'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_top_medium'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right_medium'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom_medium'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left_medium'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_top_small'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right_small'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom_small'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left_small'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'caption_title_color',
					'caption_text_color',
					'caption_text_size'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'caption_text_transform',
					'caption_text_line_height',
					'caption_text_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'caption_border_color',
					'caption_overlay_color',
					'caption_background_color',

					'caption_margin_top'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'caption_margin_right'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'caption_margin_bottom'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'caption_margin_left'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'sticky_max_width'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];

				$custom_vars = [];

				$caption_title_tag         = $this->args['caption_title_tag'];
				$caption_title_def_prefix  = ! is_numeric( $caption_title_tag ) ? 'var(--body_typography-' : 'var(--h' . $caption_title_tag . '_typography-';
				$caption_title_def_postfix = ')';

				if ( $this->args['max_width'] && 'liftup' !== $this->args['hover_type'] && 'bottomshadow' !== $this->args['style'] ) {
					$css_vars_options['max_width'] = [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ];
				}

				$title_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'caption_title_font', 'array' );

				if ( ! empty( $title_typography['font-family'] ) ) {
					$custom_vars['caption_title_font_family'] = $title_typography['font-family'];
				} else {
					$custom_vars['caption_title_font_family'] = $caption_title_def_prefix . 'font-family' . $caption_title_def_postfix;
				}

				if ( ! empty( $title_typography['font-weight'] ) ) {
					$custom_vars['caption_title_font_weight'] = $title_typography['font-weight'];
				} else {
					$custom_vars['caption_title_font_weight'] = $caption_title_def_prefix . 'font-weight' . $caption_title_def_postfix;
				}

				if ( ! empty( $title_typography['font-style'] ) ) {
					$custom_vars['caption_title_font_style'] = $title_typography['font-style'];
				} else {
					$custom_vars['caption_title_font_style'] = $caption_title_def_prefix . 'font-style' . $caption_title_def_postfix;
				}

				$custom_vars['caption_title_size'] = ! empty( $this->args['caption_title_size'] ) ? fusion_library()->sanitize->get_value_with_unit( $this->args['caption_title_size'] ) : $caption_title_def_prefix . 'font-size' . $caption_title_def_postfix;

				$custom_vars['caption_title_transform'] = ! empty( $this->args['caption_title_transform'] ) ? $this->args['caption_title_transform'] : $caption_title_def_prefix . 'text-transform' . $caption_title_def_postfix;

				$custom_vars['caption_title_line_height'] = ! empty( $this->args['caption_title_line_height'] ) ? $this->args['caption_title_line_height'] : $caption_title_def_prefix . 'line-height' . $caption_title_def_postfix;

				$custom_vars['caption_title_letter_spacing'] = ! empty( $this->args['caption_title_letter_spacing'] ) ? fusion_library()->sanitize->get_value_with_unit( $this->args['caption_title_letter_spacing'] ) : $caption_title_def_prefix . 'letter-spacing' . $caption_title_def_postfix;

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars ) . $this->get_font_styling_vars( 'caption_text_font' ) . Fusion_Builder_Filter_Helper::get_filter_vars( $this->args );

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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/image.min.css' );

				if ( class_exists( 'Avada' ) ) {
					$version = Avada::get_theme_version();
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-image-md',
						FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/image-md.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-medium' ),
					];
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-image-sm',
						FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/image-sm.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-small' ),
					];
				}
			}
		}
	}

	new FusionSC_Imageframe();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_image() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Imageframe',
			[
				'name'         => esc_attr__( 'Image', 'fusion-builder' ),
				'shortcode'    => 'fusion_imageframe',
				'icon'         => 'fusiona-image',
				'preview'      => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-image-frame-preview.php',
				'preview_id'   => 'fusion-builder-block-module-image-frame-preview-template',
				'help_url'     => 'https://avada.com/documentation/image-element/',
				'subparam_map' => [
					/* Caption title */
					'fusion_font_family_caption_title_font' => 'caption_title_fonts',
					'fusion_font_variant_caption_title_font' => 'caption_title_fonts',
					'caption_title_size'                   => 'caption_title_fonts',
					'caption_title_transform'              => 'caption_title_fonts',
					'caption_title_line_height'            => 'caption_title_fonts',
					'caption_title_letter_spacing'         => 'caption_title_fonts',
					'caption_title_color'                  => 'caption_title_fonts',

					/* Caption text */
					'fusion_font_family_caption_text_font' => 'caption_text_fonts',
					'fusion_font_variant_caption_text_font' => 'caption_text_fonts',
					'caption_text_size'                    => 'caption_text_fonts',
					'caption_text_transform'               => 'caption_text_fonts',
					'caption_text_line_height'             => 'caption_text_fonts',
					'caption_text_letter_spacing'          => 'caption_text_fonts',
					'caption_text_color'                   => 'caption_text_fonts',
				],
				'params'       => [
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'Image', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload an image to display.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Image Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Select an aspect ratio for the image.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio',
						'value'       => [
							''       => esc_attr__( 'Automatic', 'fusion-builder' ),
							'1-1'    => esc_attr__( '1:1', 'fusion-builder' ),
							'2-1'    => esc_attr__( '2:1', 'fusion-builder' ),
							'2-3'    => esc_attr__( '2:3', 'fusion-builder' ),
							'3-1'    => esc_attr__( '3:1', 'fusion-builder' ),
							'3-2'    => esc_attr__( '3:2', 'fusion-builder' ),
							'4-1'    => esc_attr__( '4:1', 'fusion-builder' ),
							'4-3'    => esc_attr__( '4:3', 'fusion-builder' ),
							'5-4'    => esc_attr__( '5:4', 'fusion-builder' ),
							'16-9'   => esc_attr__( '16:9', 'fusion-builder' ),
							'9-16'   => esc_attr__( '9:16', 'fusion-builder' ),
							'21-9'   => esc_attr__( '21:9', 'fusion-builder' ),
							'9-21'   => esc_attr__( '9:21', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Custom Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Set a custom aspect ratio for the image.', 'fusion-builder' ),
						'param_name'  => 'custom_aspect_ratio',
						'min'         => 1,
						'max'         => 500,
						'value'       => 100,
						'dependency'  => [
							[
								'element'  => 'aspect_ratio',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'image_focus_point',
						'heading'     => esc_attr__( 'Image Focus Point', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the image focus point by dragging the blue dot.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio_position',
						'image'       => 'element_content',
						'image_id'    => 'image_id',
						'dependency'  => [
							[
								'element'  => 'aspect_ratio',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'select',
						'heading'          => esc_attr__( 'Lazy Load', 'fusion-builder' ),
						'description'      => esc_attr__( 'Select your preferred lazy loading method.', 'fusion-builder' ),
						'param_name'       => 'lazy_load',
						'value'            => [
							'avada'     => esc_attr__( 'Avada', 'fusion-builder' ),
							'wordpress' => esc_attr__( 'WordPress', 'fusion-builder' ),
							'none'      => esc_attr__( 'None', 'fusion-builder' ),
						],
						'default'          => $fusion_settings->get( 'lazy_load' ),
						'hidden'           => true,
						'remove_from_atts' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Skip Lazy Loading', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to skip lazy loading on this image or not.', 'fusion-builder' ),
						'param_name'  => 'skip_lazy_load',
						'default'     => '',
						'value'       => [
							'skip' => esc_attr__( 'Yes', 'fusion-builder' ),
							''     => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'lazy_load',
								'value'    => 'avada',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Image Lightbox', 'fusion-builder' ),
						'description' => esc_attr__( 'Show image in lightbox. Lightbox must be enabled in Global Options or the image will open up in the same tab by itself.', 'fusion-builder' ),
						'param_name'  => 'lightbox',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Gallery ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Set a name for the lightbox gallery this image should belong to.', 'fusion-builder' ),
						'param_name'  => 'gallery_id',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'lightbox',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Lightbox Image', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload an image that will show up in the lightbox.', 'fusion-builder' ),
						'param_name'  => 'lightbox_image',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'lightbox',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Lightbox Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Lightbox Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'lightbox_image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Image Alt Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'The alt attribute provides alternative information if an image cannot be viewed.', 'fusion-builder' ),
						'param_name'   => 'alt',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Image Link URL', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the URL the image will link to, ex: http://example.com.', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'lightbox',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
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
						'dependency'  => [
							[
								'element'  => 'lightbox',
								'value'    => 'yes',
								'operator' => '!=',
							],
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					// design.
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum width the image should take up. Enter value including any valid CSS unit, ex: 200px. Leave empty to use full image width.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'max_width',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Image Sticky Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum width the image should take up when its parent container is sticky. Enter value including any valid CSS unit, ex: 200px. Leave empty to use full image width.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'sticky_max_width',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'fusion_builder_container',
								'param'    => 'sticky',
								'value'    => 'on',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how to align the image.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'align',
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							'none'   => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
						],
						'default'     => 'none',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Mask', 'fusion-builder' ),
						'description' => esc_attr__( 'Select an image mask.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'mask',
						'value'       => [
							''        => esc_attr__( 'None', 'fusion-builder' ),
							'mask-1'  => esc_attr__( 'Mask 1', 'fusion-builder' ),
							'mask-2'  => esc_attr__( 'Mask 2', 'fusion-builder' ),
							'mask-3'  => esc_attr__( 'Mask 3', 'fusion-builder' ),
							'mask-4'  => esc_attr__( 'Mask 4', 'fusion-builder' ),
							'mask-5'  => esc_attr__( 'Mask 5', 'fusion-builder' ),
							'mask-6'  => esc_attr__( 'Mask 6', 'fusion-builder' ),
							'mask-7'  => esc_attr__( 'Mask 7', 'fusion-builder' ),
							'mask-8'  => esc_attr__( 'Mask 8', 'fusion-builder' ),
							'mask-9'  => esc_attr__( 'Mask 9', 'fusion-builder' ),
							'mask-10' => esc_attr__( 'Mask 10', 'fusion-builder' ),
							'mask-11' => esc_attr__( 'Mask 11', 'fusion-builder' ),
							'mask-12' => esc_attr__( 'Mask 12', 'fusion-builder' ),
							'mask-13' => esc_attr__( 'Mask 13', 'fusion-builder' ),
							'mask-14' => esc_attr__( 'Mask 14', 'fusion-builder' ),
							'mask-15' => esc_attr__( 'Mask 15', 'fusion-builder' ),
							'mask-16' => esc_attr__( 'Mask 16', 'fusion-builder' ),
							'mask-17' => esc_attr__( 'Mask 17', 'fusion-builder' ),
							'mask-18' => esc_attr__( 'Mask 18', 'fusion-builder' ),
							'custom'  => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'icons'       => [
							''        => '<span class="fusiona-minus"></span>',
							'mask-1'  => '<span class="fusiona-mask-1"></span>',
							'mask-2'  => '<span class="fusiona-mask-2"></span>',
							'mask-3'  => '<span class="fusiona-mask-3"></span>',
							'mask-4'  => '<span class="fusiona-mask-4"></span>',
							'mask-5'  => '<span class="fusiona-mask-5"></span>',
							'mask-6'  => '<span class="fusiona-mask-6"></span>',
							'mask-7'  => '<span class="fusiona-mask-7"></span>',
							'mask-8'  => '<span class="fusiona-mask-8"></span>',
							'mask-9'  => '<span class="fusiona-mask-9"></span>',
							'mask-10' => '<span class="fusiona-mask-10"></span>',
							'mask-11' => '<span class="fusiona-mask-11"></span>',
							'mask-12' => '<span class="fusiona-mask-12"></span>',
							'mask-13' => '<span class="fusiona-mask-13"></span>',
							'mask-14' => '<span class="fusiona-mask-14"></span>',
							'mask-15' => '<span class="fusiona-mask-15"></span>',
							'mask-16' => '<span class="fusiona-mask-16"></span>',
							'mask-17' => '<span class="fusiona-mask-17"></span>',
							'mask-18' => '<span class="fusiona-mask-18"></span>',
							'custom'  => '<span class="fusiona-cog"></span>',
						],
						'grid_layout' => true,
						'back_icons'  => true,
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Custom Mask', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload a custom mask image. The image should be in SVG or PNG format with transparent background.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'custom_mask',
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => 'custom',
								'operator' => '==',
							],
						],

					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Mask Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the mask size.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'mask_size',
						'value'       => [
							''       => esc_attr__( 'Fit', 'fusion-builder' ),
							'fill'   => esc_attr__( 'Fill', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '!=',
							],
						],

					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Custom Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the size of the image mask. Enter value including any valid CSS unit ex. 60% or 200px.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'mask_custom_size',
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'mask_size',
								'value'    => 'custom',
								'operator' => '==',
							],
						],

					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Mask Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Set image mask position.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'mask_position',
						'value'       => [

							'top-center'    => esc_attr__( 'Top Center', 'fusion-builder' ),
							'top-left'      => esc_attr__( 'Top Left', 'fusion-builder' ),
							'top-right'     => esc_attr__( 'Top Right', 'fusion-builder' ),

							''              => esc_attr__( 'Center Center', 'fusion-builder' ),
							'center-left'   => esc_attr__( 'Center Left', 'fusion-builder' ),
							'center-right'  => esc_attr__( 'Center Right', 'fusion-builder' ),

							'bottom-center' => esc_attr__( 'Bottom Center', 'fusion-builder' ),
							'bottom-left'   => esc_attr__( 'Bottom Left', 'fusion-builder' ),
							'bottom-right'  => esc_attr__( 'Bottom Right', 'fusion-builder' ),

							'custom'        => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '!=',
							],
						],

					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Custom Mask Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Set a custom image mask position. Enter value including any valid CSS unit in pair first for X axis second for Y axis ex. 60% 50px.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'mask_custom_position',
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'mask_position',
								'value'    => 'custom',
								'operator' => '==',
							],
						],

					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Mask Repeat', 'fusion-builder' ),
						'description' => esc_attr__( 'Select how the image mask repeats.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'mask_repeat',
						'value'       => [

							''         => esc_attr__( 'No Repeat', 'fusion-builder' ),
							'repeat'   => esc_attr__( 'Repeat', 'fusion-builder' ),
							'repeat-x' => esc_attr__( 'Repeat X', 'fusion-builder' ),
							'repeat-y' => esc_attr__( 'Repeat Y', 'fusion-builder' ),
							'space'    => esc_attr__( 'Space', 'fusion-builder' ),
							'round'    => esc_attr__( 'Round', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '!=',
							],
						],

					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Style Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the style type. Style type will be disabled when using mask or caption styles other than "Above" or "Below" are chosen.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'style_type',
						'value'       => [
							''             => esc_attr__( 'Default', 'fusion-builder' ),
							'none'         => esc_attr__( 'None', 'fusion-builder' ),
							'glow'         => esc_attr__( 'Glow', 'fusion-builder' ),
							'dropshadow'   => esc_attr__( 'Drop Shadow', 'fusion-builder' ),
							'bottomshadow' => esc_attr__( 'Bottom Shadow', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '==',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Glow / Drop Shadow Blur', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the amount of blur added to glow or drop shadow effect. In pixels.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'blur',
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'imageframe_blur' ),
						'dependency'  => [
							[
								'element'  => 'style_type',
								'value'    => 'none',
								'operator' => '!=',
							],
							[
								'element'  => 'style_type',
								'value'    => 'bottomshadow',
								'operator' => '!=',
							],
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '==',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Style Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the style color for all style types except border. Hex colors will use a subtle auto added alpha level to produce a nice effect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'stylecolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'imgframe_style_color' ),
						'dependency'  => [
							[
								'element'  => 'style_type',
								'value'    => 'none',
								'operator' => '!=',
							],
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '==',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the hover effect type. Hover Type will be disabled when caption styles other than Above or Below are chosen. Hover type scroll will not work when style type is set to bottom shadow.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'hover_type',
						'value'       => [
							'none'    => esc_attr__( 'None', 'fusion-builder' ),
							'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
							'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
							'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
							'magnify' => esc_attr__( 'Magnify', 'fusion-builder' ),
							'scroll'  => esc_attr__( 'Scroll', 'fusion-builder' ),
						],
						'default'     => 'none',
						'preview'     => [
							'selector' => '.fusion-imageframe',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'Image to Magnify', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload the full image to magnify. If not added the default image will be used.', 'fusion-builder' ),
						'group'        => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'   => 'magnify_full_img',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'hover_type',
								'value'    => 'magnify',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Magnify Duration', 'fusion-builder' ),
						'description' => esc_attr__( 'The fade in and fade out speed of the large image.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'magnify_duration',
						'value'       => '120',
						'min'         => '100',
						'max'         => '5000',
						'step'        => '100',
						'dependency'  => [
							[
								'element'  => 'hover_type',
								'value'    => 'magnify',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Image Frame Height', 'fusion-builder' ),
						'description' => esc_attr__( 'The height of the frame in pixels in which the image will be scrolled.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'scroll_height',
						'value'       => '100',
						'min'         => '1',
						'max'         => '1000',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'hover_type',
								'value'    => 'scroll',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Scroll Speed', 'fusion-builder' ),
						'description' => esc_attr__( 'The scroll speed of the image in seconds', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'scroll_speed',
						'value'       => '1',
						'min'         => '0.5',
						'max'         => '10',
						'step'        => '0.5',
						'dependency'  => [
							[
								'element'  => 'hover_type',
								'value'    => 'scroll',
								'operator' => '==',
							],
						],
					],
					'fusion_margin_placeholder'            => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'responsive' => [
							'state' => 'large',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'bordersize',
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'imageframe_border_size' ),
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color. ', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'imgframe_border_color' ),
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '==',
							],
							[
								'element'  => 'bordersize',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the image border radius. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'borderradius',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'mask',
								'value'    => '',
								'operator' => '==',
							],
							[
								'element'  => 'style_type',
								'value'    => 'bottomshadow',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Z Index', 'fusion-builder' ),
						'description' => esc_attr__( 'Value for the z-index CSS property of the image, can be both positive or negative.', 'fusion-builder' ),
						'param_name'  => 'z_index',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],                  // design end.
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Caption', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the caption style.', 'fusion-builder' ),
						'param_name'  => 'caption_style',
						'value'       => [
							'off'      => esc_attr__( 'Off', 'fusion-builder' ),
							'above'    => esc_attr__( 'Above', 'fusion-builder' ),
							'below'    => esc_attr__( 'Below', 'fusion-builder' ),
							'navin'    => esc_attr__( 'Navin', 'fusion-builder' ),
							'dario'    => esc_attr__( 'Dario', 'fusion-builder' ),
							'resa'     => esc_attr__( 'Resa', 'fusion-builder' ),
							'schantel' => esc_attr__( 'Schantel', 'fusion-builder' ),
							'dany'     => esc_attr__( 'Dany', 'fusion-builder' ),
						],
						'default'     => 'off',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Title / Caption Align', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how to align the caption.', 'fusion-builder' ),
						'param_name'  => 'caption_align',
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							'none'   => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
						],
						'default'     => 'none',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Image Title', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter title text to be displayed on image.', 'fusion-builder' ),
						'param_name'   => 'caption_title',
						'value'        => '',
						'dynamic_data' => true,
						'group'        => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'   => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Image Caption', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter caption text to be displayed on image.', 'fusion-builder' ),
						'param_name'   => 'caption_text',
						'value'        => '',
						'dynamic_data' => true,
						'group'        => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'   => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Image Title Heading Tag', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the image title, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'caption_title_tag',
						'value'       => [
							'1'   => 'H1',
							'2'   => 'H2',
							'3'   => 'H3',
							'4'   => 'H4',
							'5'   => 'H5',
							'6'   => 'H6',
							'div' => 'DIV',
						],
						'default'     => '2',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Image Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the image title. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'caption_title_fonts',
						'choices'          => [
							'font-family'    => 'caption_title_font',
							'font-size'      => 'caption_title_size',
							'text-transform' => 'caption_title_transform',
							'line-height'    => 'caption_title_line_height',
							'letter-spacing' => 'caption_title_letter_spacing',
							'color'          => 'caption_title_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => '',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Image Caption Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the caption.', 'fusion-builder' ),
						'param_name'  => 'caption_background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'above',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'below',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Image Caption Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the image caption. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'caption_text_fonts',
						'choices'          => [
							'font-family'    => 'caption_text_font',
							'font-size'      => 'caption_text_size',
							'text-transform' => 'caption_text_transform',
							'line-height'    => 'caption_text_line_height',
							'letter-spacing' => 'caption_text_letter_spacing',
							'color'          => 'caption_text_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => '',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Caption Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the caption border.', 'fusion-builder' ),
						'param_name'  => 'caption_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'default'     => 'var(--awb-color1)',
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'above',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'below',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Image Overlay Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the image overlay.', 'fusion-builder' ),
						'param_name'  => 'caption_overlay_color',
						'value'       => '',
						'group'       => esc_attr__( 'Caption', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'primary_color' ),
						'dependency'  => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'above',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'below',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Caption Area Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'caption_margin',
						'value'            => [
							'caption_margin_top'    => '',
							'caption_margin_right'  => '',
							'caption_margin_bottom' => '',
							'caption_margin_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'group'            => esc_attr__( 'Caption', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'caption_style',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'schantel',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dany',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'navin',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'dario',
								'operator' => '!=',
							],
							[
								'element'  => 'caption_style',
								'value'    => 'resa',
								'operator' => '!=',
							],
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-imageframe',
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
					'fusion_filter_placeholder'            => [
						'selector_base' => 'imageframe-cid',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_image' );
