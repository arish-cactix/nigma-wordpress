<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_tagcloud' ) ) {

	if ( ! class_exists( 'FusionSC_Tagcloud' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Tagcloud extends Fusion_Element {

			/**
			 * The image-frame counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $tagcloud_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_tagcloud-shortcode', [ $this, 'attr' ] );

				add_shortcode( 'fusion_tagcloud', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_tagcloud', [ $this, 'ajax_query' ] );
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
					'taxonomy'                          => 'post_tag',
					'show_count'                        => 'off',
					'style'                             => '',
					'alignment'                         => '',
					'tags_spacing'                      => 10,
					'random_colors'                     => '',
					'background_color'                  => $fusion_settings->get( 'tagcloud_bg' ),
					'background_hover_color'            => $fusion_settings->get( 'tagcloud_bg_hover' ),
					'text_color'                        => $fusion_settings->get( 'tagcloud_color' ),
					'text_hover_color'                  => $fusion_settings->get( 'tagcloud_color_hover' ),
					'border_top'                        => '',
					'border_right'                      => '',
					'border_bottom'                     => '',
					'border_left'                       => '',
					'border_radius_top_left'            => '',
					'border_radius_top_right'           => '',
					'border_radius_bottom_right'        => '',
					'border_radius_bottom_left'         => '',
					'arrows_border_radius_top_right'    => '',
					'arrows_border_radius_bottom_right' => '',
					'border_color'                      => $fusion_settings->get( 'tagcloud_border_color' ),
					'border_hover_color'                => $fusion_settings->get( 'tagcloud_border_color_hover' ),
					'font_size_type'                    => '',
					'font_size'                         => '',
					'letter_spacing'                    => '',

					// padding.
					'padding_top'                       => '',
					'padding_right'                     => '',
					'padding_bottom'                    => '',
					'padding_left'                      => '',

					// margin.
					'margin_top'                        => '',
					'margin_right'                      => '',
					'margin_bottom'                     => '',
					'margin_left'                       => '',
					'margin_top_medium'                 => '',
					'margin_right_medium'               => '',
					'margin_bottom_medium'              => '',
					'margin_left_medium'                => '',
					'margin_top_small'                  => '',
					'margin_right_small'                => '',
					'margin_bottom_small'               => '',
					'margin_left_small'                 => '',

					// css.
					'class'                             => '',
					'id'                                => '',

					// animation.
					'animation_direction'               => 'left',
					'animation_offset'                  => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                   => '',
					'animation_delay'                   => '',
					'animation_type'                    => '',
					'animation_color'                   => '',

					// visibility.
					'hide_on_mobile'                    => fusion_builder_default_visibility( 'string' ),
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
					'tagcloud_bg'                 => 'background_color',
					'tagcloud_bg_hover'           => 'background_hover_color',
					'tagcloud_color'              => 'text_color',
					'tagcloud_color_hover'        => 'text_hover_color',
					'tagcloud_border_color'       => 'border_color',
					'tagcloud_border_color_hover' => 'border_hover_color',
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
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tagcloud' );
			}

			/**
			 * Return 'true' or 'false'
			 * form on and off
			 *
			 * @access public
			 * @since 1.0
			 * @param  string $value   on or off.
			 * @return string           true or false.
			 */
			public function is_true( $value ) {
				if ( 'on' === $value ) {
					return true;
				}
				return false;
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

				$this->set_element_id( $this->tagcloud_counter );

				$this->set_args( $args );

				$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_tagcloud', $args );

				$classes = [];

				$tag_cloud = $this->get_tagcloud_html(
					[
						'taxonomy'       => $this->args['taxonomy'],
						'show_count'     => $this->args['show_count'],
						'style'          => $this->args['style'],
						'random_colors'  => $this->args['random_colors'],
						'font_size_type' => $this->args['font_size_type'],
					]
				);

				$html = '<div ' . FusionBuilder::attributes( 'tagcloud-shortcode' ) . '>' . $tag_cloud . '</div>';

				$this->tagcloud_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_tagcloud_content', $html, $args );
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
					'class' => '',
					'style' => '',
				];

				$attr['id']     = $this->args['id'];
				$attr['class'] .= 'fusion-tagcloud-element fusion-tagcloud-' . $this->element_id . ' ' . $this->args['class'];

				if ( '' !== $this->args['style'] ) {
					$attr['class'] .= ' style-' . $this->args['style'];
				}

				if ( 'variable' === $this->args['font_size_type'] ) {
					$attr['class'] .= ' variable-font-size';
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= $this->get_style_variables();

				// colors.
				$random_colors = ! empty( $this->args['random_colors'] ) ? explode( ',', $this->args['random_colors'] ) : [];

				if ( '' !== $this->args['background_color'] && ! in_array( 'background', $random_colors, true ) ) {
					$attr['style'] .= '--tag-color:' . $this->args['background_color'] . ';';
				}

				if ( '' !== $this->args['text_color'] && ! in_array( 'text', $random_colors, true ) ) {
					$attr['style'] .= '--tag-text-color:' . $this->args['text_color'] . ';';
				}

				if ( 'arrows' !== $this->args['style'] && ! in_array( 'background', $random_colors, true ) && '' !== $this->args['border_color'] ) {
					$attr['style'] .= '--tag-border-color:' . $this->args['border_color'] . ';';
				}

				// Hover.
				if ( '' !== $this->args['background_hover_color'] && ! in_array( 'background', $random_colors, true ) ) {
					$attr['style'] .= '--tag-color-hover:' . $this->args['background_hover_color'] . ';';
				}

				if ( '' !== $this->args['text_hover_color'] && ! in_array( 'text', $random_colors, true ) ) {
					$attr['style'] .= '--tag-text-color-hover:' . $this->args['text_hover_color'] . ';';
				}

				if ( 'arrows' !== $this->args['style'] && ! in_array( 'background', $random_colors, true ) && '' !== $this->args['border_hover_color'] ) {
					$attr['style'] .= '--tag-border-color-hover:' . $this->args['border_hover_color'] . ';';
				}

				return $attr;
			}
			/**
			 * Gets the query data.
			 *
			 * @since 3.5
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$args     = wp_unslash( $_POST['model']['params'] ); // phpcs:ignore WordPress.Security
					$defaults = self::get_element_defaults();

					$args = FusionBuilder::set_shortcode_defaults( $defaults, $args, 'fusion_tagcloud' );
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				echo wp_json_encode( $this->get_tagcloud_html( $args ) );
				wp_die();
			}
			/**
			 * Get tagcloud items in html.
			 *
			 * @access public
			 * @since 3.6
			 * @param Array $args Array with tagcloud options.
			 * @return String
			 */
			public function get_tagcloud_html( $args = null ) {
				$tag_cloud     = wp_tag_cloud(
					[
						'taxonomy'   => $args['taxonomy'],
						'echo'       => false,
						'show_count' => $this->is_true( $args['show_count'] ),
					]
				);
				$random_colors = ! empty( $args['random_colors'] ) ? explode( ',', $args['random_colors'] ) : [];

				// placeholder.
				if ( ! $tag_cloud ) {
					$tag_cloud = sprintf( '<div class="fusion-builder-placeholder">%s</div>', esc_html__( 'No tags to display. Try to select another taxonomy.', 'fusion-builder' ) );
				}

				if ( 'arrows' === $args['style'] ) {
					$tag_cloud = $this->add_arrow_svg( $tag_cloud );
				}

				if ( 'variable' === $args['font_size_type'] ) {
					$tag_cloud = $this->add_size_attr( $tag_cloud );
				}

				if ( ! empty( $random_colors ) ) {
					$tag_cloud = $this->add_random_color( $tag_cloud, $random_colors );
				}

				return $tag_cloud;
			}

			/**
			 * Modify tagcloud link.
			 *
			 * @access public
			 * @since 3.6
			 * @param String $tags Tags html code.
			 * @return String
			 */
			public function add_arrow_svg( $tags ) {
				$arrow_svg = '<svg height="38" viewBox="0 0 20 38" width="20" xmlns="http://www.w3.org/2000/svg"><path d="m20 0v38h-3.6552713l-16.3447287-18.9392923 16.3447287-19.0607077zm-5.432308 15c-2.2987184 0-4.1621977 1.790861-4.1621977 4s1.8634793 4 4.1621977 4c2.2987183 0 4.1621977-1.790861 4.1621977-4s-1.8634794-4-4.1621977-4z" fill="currentColor" fill-rule="evenodd"/></svg>';

				$tags = preg_replace( '/(<a.*?>)/', '$0<span class="text">', $tags );
				$tags = preg_replace( '/(<\/a>)/', '</span>$0', $tags );

				// add the arrow.
				$tags = preg_replace( '/(<a.*?>)/', '$0<span class="arrow">' . $arrow_svg . '</span>', $tags );

				return $tags;
			}
			/**
			 * Add size attr.
			 *
			 * @access public
			 * @since 3.6
			 * @param String $tags Tags HTML code.
			 * @return String
			 */
			public function add_size_attr( $tags ) {
				$tags = preg_replace_callback( '/aria-label="(.*?)"/', [ $this, 'get_size_attr' ], $tags );
				return $tags;
			}
			/**
			 * Get size attr.
			 *
			 * @access public
			 * @since 3.6
			 * @param String $m Matched string.
			 * @return String
			 */
			public function get_size_attr( $m ) {

				preg_match( '/\((\d+)/', $m[1], $o );
				$size = ! empty( $o[1] ) ? $o[1] : 2;
				if ( $size > 9 ) {
					$size = 9;
				}
				return $m[0] . ' data-size="' . $size . '" ';
			}
			/**
			 * Add random color to tags.
			 *
			 * @access public
			 * @since 3.6
			 * @param String $tags Tags HTML code.
			 * @param Array  $random_colors array define elements that use random colors.
			 * @return String
			 */
			public function add_random_color( $tags, $random_colors = [] ) {

				if ( in_array( 'text', $random_colors, true ) && in_array( 'background', $random_colors, true ) ) {
					$tags = preg_replace_callback( '/(<a)/', [ $this, 'get_both_random_colors' ], $tags );
				} else {
					if ( in_array( 'text', $random_colors, true ) ) {
						$tags = preg_replace_callback( '/(<a)/', [ $this, 'get_random_text_color' ], $tags );
					}
					if ( in_array( 'background', $random_colors, true ) ) {
						$tags = preg_replace_callback( '/(<a)/', [ $this, 'get_random_bg_color' ], $tags );
					}
				}
				return $tags;
			}
			/**
			 * Get random colors for text and background.
			 *
			 * @access public
			 * @since 3.6
			 * @param String $m Matched string.
			 * @return String
			 */
			public function get_both_random_colors( $m ) {
					$hue      = wp_rand( 0, 360 );
					$bg       = 'hsla(' . $hue . ',100%,42%,.15)';
					$hover_bg = 'hsla(' . $hue . ',100%,42%,.30)';
					$text     = 'hsl(' . $hue . ',100%,35%)';
					return $m[0] . ' style="--tag-color:' . $bg . ';--tag-text-color:' . $text . ';--tag-color-hover:' . $hover_bg . ';--tag-text-color-hover:' . $text . ';--tag-border-color:' . $bg . ';--tag-border-color-hover:' . $hover_bg . ';" ';
			}
			/**
			 * Get random colors for background only.
			 *
			 * @access public
			 * @since 3.6
			 * @param String $m Matched string.
			 * @return String
			 */
			public function get_random_bg_color( $m ) {
					$hue      = wp_rand( 0, 360 );
					$bg       = 'hsl(' . $hue . ',100%,42%)';
					$hover_bg = 'hsl(' . $hue . ',100%,38%)';
					return $m[0] . ' style="--tag-color:' . $bg . ';--tag-color-hover:' . $hover_bg . ';--tag-border-color:' . $bg . ';--tag-border-color-hover:' . $hover_bg . ';" ';
			}
			/**
			 * Get random colors for text only.
			 *
			 * @access public
			 * @since 3.6
			 * @param String $m Matched string.
			 * @return String
			 */
			public function get_random_text_color( $m ) {
					$hue        = wp_rand( 0, 360 );
					$text       = 'hsl(' . $hue . ',100%,35%)';
					$hover_text = 'hsl(' . $hue . ',100%,45%)';
					return $m[0] . ' style="--tag-text-color:' . $text . ';--tag-text-color-hover:' . $hover_text . ';" ';
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
					'margin_top'                        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'                     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_top_medium'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right_medium'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom_medium'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left_medium'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_top_small'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right_small'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom_small'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left_small'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'alignment',
					'tags_spacing'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'font_size'                         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'letter_spacing'                    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_top'                       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_right'                     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_bottom'                    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_left'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_top'                        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_right'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_bottom'                     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_left'                       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_top_left'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_top_right'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_bottom_left'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_bottom_right'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'arrows_border_radius_top_right'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'arrows_border_radius_bottom_right' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],

				];

				$styles = $this->get_css_vars_for_options( $css_vars_options );

				return $styles;
			}

			/**
			 *
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/tagcloud.min.css' );

				if ( class_exists( 'Avada' ) ) {
					$version = Avada::get_theme_version();
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-tagcloud-md',
						FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/tagcloud-md.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-medium' ),
					];
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-tagcloud-sm',
						FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/tagcloud-sm.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-small' ),
					];
				}
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 3.6
			 * @return array $sections Tag Cloud settings.
			 */
			public function add_options() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'tagcloud_shortcode_section' => [
						'label'       => esc_html__( 'Tag Cloud', 'fusion-builder' ),
						'description' => '',
						'id'          => 'tagcloud_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-list-alt',
						'fields'      => [
							'tagcloud_bg'                 => [
								'label'         => esc_html__( 'Tags Background Color', 'fusion-builder' ),
								'description'   => esc_html__( 'Choose the background color of the tags.', 'fusion-builder' ),
								'id'            => 'tagcloud_bg',
								'default'       => 'rgba(255, 255, 255, 0)',
								'type'          => 'color-alpha',
								'transport'     => 'postMessage',
								'css_vars_temp' => [
									[
										'name'     => '--tag-color',
										'element'  => '.fusion-tagcloud-element a.tag-cloud-link',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tagcloud_bg_hover'           => [
								'label'         => esc_html__( 'Tags Background Hover Color', 'fusion-builder' ),
								'description'   => esc_html__( 'Choose the background hover color of the tags.', 'fusion-builder' ),
								'id'            => 'tagcloud_bg_hover',
								'default'       => 'var(--awb-color4)',
								'type'          => 'color-alpha',
								'transport'     => 'postMessage',
								'css_vars_temp' => [
									[
										'name'     => '--tag-color-hover',
										'element'  => '.fusion-tagcloud-element a.tag-cloud-link',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tagcloud_color'              => [
								'label'         => esc_html__( 'Tags Text Color', 'fusion-builder' ),
								'description'   => esc_html__( 'Choose the text color of the tags.', 'fusion-builder' ),
								'id'            => 'tagcloud_color',
								'default'       => 'hsla(var(--awb-color1-h),var(--awb-color1-s),var(--awb-color1-l),calc(var(--awb-color1-a) - 20%))',
								'type'          => 'color-alpha',
								'transport'     => 'postMessage',
								'css_vars_temp' => [
									[
										'name'     => '--tag-text-color',
										'element'  => '.fusion-tagcloud-element a.tag-cloud-link',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tagcloud_color_hover'        => [
								'label'         => esc_html__( 'Tags Text Hover Color', 'fusion-builder' ),
								'description'   => esc_html__( 'Choose the text hover color of the tags.', 'fusion-builder' ),
								'id'            => 'tagcloud_color_hover',
								'default'       => 'var(--awb-color1)',
								'type'          => 'color-alpha',
								'transport'     => 'postMessage',
								'css_vars_temp' => [
									[
										'name'     => '--tag-text-color-hover',
										'element'  => '.fusion-tagcloud-element a.tag-cloud-link',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tagcloud_border_color'       => [
								'label'         => esc_html__( 'Tags Border Color', 'fusion-builder' ),
								'description'   => esc_html__( 'Choose the boder color of the tags.', 'fusion-builder' ),
								'id'            => 'tagcloud_border_color',
								'default'       => 'var(--awb-color8)',
								'type'          => 'color-alpha',
								'transport'     => 'postMessage',
								'css_vars_temp' => [
									[
										'name'     => '--tag-border-color',
										'element'  => '.fusion-tagcloud-element a.tag-cloud-link',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tagcloud_border_color_hover' => [
								'label'         => esc_html__( 'Tags Border Hover Color', 'fusion-builder' ),
								'description'   => esc_html__( 'Choose the boder hover color of the tags.', 'fusion-builder' ),
								'id'            => 'tagcloud_border_color_hover',
								'default'       => 'var(--awb-color4)',
								'type'          => 'color-alpha',
								'transport'     => 'postMessage',
								'css_vars_temp' => [
									[
										'name'     => '--tag-border-color-hover',
										'element'  => '.fusion-tagcloud-element a.tag-cloud-link',
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

	new FusionSC_Tagcloud();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_tagcloud_element() {
	$taxonomies = get_taxonomies( [ 'show_tagcloud' => true ], 'object' );
	$tax_arr    = [];

	foreach ( $taxonomies as $taxonomy => $tax ) {
		if ( 'link_category' !== $tax->name ) {
			$tax_arr[ $taxonomy ] = $tax->labels->name;
		}
	}

	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Tagcloud',
			[
				'name'      => esc_attr__( 'Tag Cloud', 'fusion-builder' ),
				'shortcode' => 'fusion_tagcloud',
				'icon'      => 'fusiona-tag-cloud',
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tagcloud',
					'ajax'     => true,
				],
				'params'    => [
					[
						'type'        => 'select',
						'param_name'  => 'taxonomy',
						'heading'     => esc_attr__( 'Taxonomy', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the taxonomy you want the tag cloud to display.', 'fusion-builder' ),
						'value'       => $tax_arr,
						'default'     => 'post_tag',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tagcloud',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'show_count',
						'heading'     => esc_attr__( 'Show Count', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if the tag post count should be displayed.', 'fusion-builder' ),
						'default'     => 'off',
						'value'       => [
							'on'  => esc_html__( 'On', 'fusion-builder' ),
							'off' => esc_html__( 'Off', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tagcloud',
							'ajax'     => true,
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
						'type'        => 'radio_button_set',
						'param_name'  => 'style',
						'heading'     => esc_attr__( 'Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the tag style.', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''       => esc_html__( 'Basic', 'fusion-builder' ),
							'arrows' => esc_html__( 'Arrows', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tagcloud',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the tags should align inside the Column.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => 'flex-start',
						'value'       => [
							'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
						],
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'grid_layout' => true,
						'back_icons'  => true,
					],
					[
						'type'        => 'range',
						'param_name'  => 'tags_spacing',
						'heading'     => esc_attr__( 'Tag Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the space between tags.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => 10,
						'min'         => '0',
						'max'         => '300',
						'step'        => '1',
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'font_size_type',
						'heading'     => esc_attr__( 'Font Size Type', 'fusion-builder' ),
						'description' => esc_html__( 'Select font size type for the tags. Variable means more common tags will be larger.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''         => esc_attr__( 'Static', 'fusion-builder' ),
							'variable' => esc_attr__( 'Variable', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tagcloud',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'param_name'  => 'font_size',
						'heading'     => esc_attr__( 'Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the font size of the tag text. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'font_size_type',
								'value'    => 'variable',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'param_name'  => 'letter_spacing',
						'heading'     => esc_attr__( 'Letter Spacing', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the letter spacing of the tag text. Enter value including any valid CSS unit, ex: 2px. Leave empty to use the site default.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Tags Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Set the padding inside the tags. Enter values including px or em units, ex: 20px, 2.5em.', 'fusion-builder' ),
						'param_name'       => 'tags_padding',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Set the border size. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_width',
						'value'            => [
							'border_top'    => '',
							'border_right'  => '',
							'border_bottom' => '',
							'border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'style',
								'value'    => 'arrows',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Set the border radius. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
						'dependency'       => [
							[
								'element'  => 'style',
								'value'    => 'arrows',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Set the border radius. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'arrows_border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'arrows_border_radius_top_right'    => '',
							'arrows_border_radius_bottom_right' => '',
						],
						'dependency'       => [
							[
								'element'  => 'style',
								'value'    => 'arrows',
								'operator' => '==',
							],
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-tagcloud-element',
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
					[
						'type'        => 'checkbox_button_set',
						'param_name'  => 'random_colors',
						'heading'     => esc_attr__( 'Random Colors', 'fusion-builder' ),
						'description' => esc_attr__( 'Select random colors for background, text or both. When selecting both, the background and text will use the same color, but the background will be semi transparent.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'background' => esc_attr__( 'Background', 'fusion-builder' ),
							'text'       => esc_attr__( 'Text', 'fusion-builder' ),
						],
						'default'     => [ '' ],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tagcloud',
							'ajax'     => true,
						],
					],
					[
						'type'          => 'colorpickeralpha',
						'param_name'    => 'background_color',
						'heading'       => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Choose the background color of the tags.', 'fusion-builder' ),
						'group'         => esc_attr__( 'Design', 'fusion-builder' ),
						'default'       => $fusion_settings->get( 'tagcloud_bg' ),
						'callback'      => [
							'function' => 'fusion_style_block',
						],
						'dependency'    => [
							[
								'element'  => 'random_colors',
								'value'    => 'background',
								'operator' => 'not_contain',
							],
						],
						'states'        => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'default'    => $fusion_settings->get( 'tagcloud_bg_hover' ),
								'param_name' => 'background_hover_color',
								'preview'    => [
									'selector' => 'a.tag-cloud-link',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'text_color', 'border_color' ],
					],
					[
						'type'          => 'colorpickeralpha',
						'param_name'    => 'text_color',
						'heading'       => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Choose the text color of the tags.', 'fusion-builder' ),
						'group'         => esc_attr__( 'Design', 'fusion-builder' ),
						'default'       => $fusion_settings->get( 'tagcloud_color' ),
						'callback'      => [
							'function' => 'fusion_style_block',
						],
						'dependency'    => [
							[
								'element'  => 'random_colors',
								'value'    => 'text',
								'operator' => 'not_contain',
							],
						],
						'states'        => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'default'    => $fusion_settings->get( 'tagcloud_color_hover' ),
								'param_name' => 'text_hover_color',
								'preview'    => [
									'selector' => 'a.tag-cloud-link',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'background_color', 'border_color' ],
					],
					[
						'type'          => 'colorpickeralpha',
						'heading'       => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description'   => esc_attr__( 'Choose the border color of the tags.', 'fusion-builder' ),
						'param_name'    => 'border_color',
						'default'       => $fusion_settings->get( 'tagcloud_border_color' ),
						'group'         => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'      => [
							'function' => 'fusion_style_block',
						],
						'dependency'    => [
							[
								'element'  => 'random_colors',
								'value'    => 'background',
								'operator' => 'not_contain',
							],
							[
								'element'  => 'style',
								'value'    => 'arrows',
								'operator' => '!=',
							],
						],
						'states'        => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'default'    => $fusion_settings->get( 'tagcloud_color_hover' ),
								'param_name' => 'border_hover_color',
								'preview'    => [
									'selector' => 'a.tag-cloud-link',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
						'connect-state' => [ 'background_color', 'text_color' ],

					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_wp_loaded', 'fusion_tagcloud_element' );
