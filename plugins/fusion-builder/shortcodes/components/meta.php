<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.4
 */

if ( fusion_is_element_enabled( 'fusion_tb_meta' ) ) {

	if ( ! class_exists( 'FusionTB_Meta' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.4
		 */
		class FusionTB_Meta extends Fusion_Component {

			/**
			 * The word counter.
			 *
			 * @access private
			 * @since 5.9
			 * @var int
			 */
			private $word_count = 0;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 2.4
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.4
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_meta' );
				add_filter( 'fusion_attr_fusion_tb_meta-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_pipe_seprator_shortcodes', [ $this, 'allow_separator' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_meta', [ $this, 'ajax_render' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 2.4
			 * @return boolean
			 */
			public function should_render() {
				return is_singular() || wp_is_json_request();
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
					array_push( $shortcodes, 'fusion_tb_meta' );
				}

				return $shortcodes;
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'meta'                     => 'author,published_date,categories,comments,tags',
					'layout'                   => 'floated',
					'display_element_labels'   => 'yes',
					'separator'                => '',
					'font_size'                => $fusion_settings->get( 'meta_font_size' ),
					'text_color'               => $fusion_settings->get( 'link_color' ),
					'link_color'               => '',
					'text_hover_color'         => $fusion_settings->get( 'link_hover_color' ),
					'border_size'              => null,
					'border_color'             => $fusion_settings->get( 'sep_color' ),
					'alignment'                => 'flex-start',
					'alignment_medium'         => '',
					'alignment_small'          => '',
					'stacked_vertical_align'   => 'flex-start',
					'stacked_horizontal_align' => 'flex-start',
					'height'                   => '33',
					'margin_bottom'            => '',
					'margin_left'              => '',
					'margin_right'             => '',
					'margin_top'               => '',
					'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
					'class'                    => '',
					'id'                       => '',
					'animation_type'           => '',
					'animation_direction'      => 'down',
					'animation_speed'          => '0.1',
					'animation_delay'          => '',
					'animation_offset'         => $fusion_settings->get( 'animation_offset' ),
					'animation_color'          => '',
					'padding_bottom'           => '',
					'padding_left'             => '',
					'padding_right'            => '',
					'padding_top'              => '',
					'border_bottom'            => '1px',
					'border_left'              => '0px',
					'border_right'             => '0px',
					'border_top'               => '1px',
					'event_venue_link'         => 'venue_page',
					'event_organizer_link'     => 'organizer_page',
					'read_time'                => 200,
					'reading_time_decimal'     => 'yes',
					'background_color'         => '',
					'item_background_color'    => '',
					'item_border_color'        => $fusion_settings->get( 'sep_color' ),
					'item_padding_bottom'      => '',
					'item_padding_left'        => '',
					'item_padding_right'       => '',
					'item_padding_top'         => '',
					'item_border_bottom'       => '',
					'item_border_left'         => '',
					'item_border_right'        => '',
					'item_border_top'          => '',
					'item_margin_bottom'       => '',
					'item_margin_left'         => '',
					'item_margin_right'        => '',
					'item_margin_top'          => '',
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
					$this->args   = $defaults;
					$return_data  = [];
					$live_request = true;
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				if ( class_exists( 'Fusion_App' ) && $live_request ) {

					$this->emulate_post();

					$return_data['meta'] = $this->get_meta_elements( $defaults, true );
					$this->restore_post();
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 2.4
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_meta' );

				if ( null !== $defaults['border_size'] ) {
					$defaults['border_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
					$defaults['border_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				}

				$defaults['height'] = FusionBuilder::validate_shortcode_attr_value( $defaults['height'], 'px' );

				$this->args     = $defaults;
				$this->defaults = self::get_element_defaults();

				$this->emulate_post();

				$html = $this->get_meta_elements( $this->args, false );

				$this->restore_post();

				$html = '<div ' . FusionBuilder::attributes( 'fusion_tb_meta-shortcode' ) . '>' . $html . '</div>';

				$this->word_count = 0;

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.4
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-meta-tb fusion-meta-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= $this->get_style_variables();

				if ( '' !== $this->args['layout'] ) {
					$attr['class'] .= ' ' . $this->args['layout'];
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
			 * Get the style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_style_variables() {
				$custom_vars = [];

				foreach ( [ 'medium', 'small' ] as $size ) {
					$key = 'alignment_' . $size;

					if ( '' === $this->args[ $key ] ) {
						continue;
					}

					$custom_vars[ $key ] = $this->args[ $key ];
				}

				$css_vars_options = [
					'border_bottom'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_top'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_left'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_right'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_border_bottom'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_border_top'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_border_left'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_border_right'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_padding_top'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_padding_bottom'   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_padding_left'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_padding_right'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_margin_top'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_margin_bottom'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_margin_left'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'item_margin_right'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'height'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'font_size'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_top'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_bottom'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_left'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_right'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'padding_top'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'text_color'            => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'link_color'            => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'text_hover_color'      => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'border_color'          => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'item_border_color'     => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'item_background_color' => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'background_color'      => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'alignment',
					'stacked_vertical_align',
					'stacked_horizontal_align',
				];

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @return array
			 */
			public static function settings_to_params() {
				// Todo: border_color should also probably be changed by sep_color,
				// but because wqe can change only one, item_border_color is changed only.
				return [
					'sep_color'        => 'item_border_color',
					'link_color'       => 'text_color',
					'link_hover_color' => 'text_hover_color',
					'meta_font_size'   => 'font_size',
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 3.6
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
			 * Builds HTML for meta elements.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @param array $args    The arguments.
			 * @param bool  $is_live If it's live editor request or not.
			 * @return array
			 */
			public function get_meta_elements( $args, $is_live ) {
				global $product;

				$options     = explode( ',', $args['meta'] );
				$post_id     = $this->get_post_id();
				$content     = '';
				$date_format = fusion_library()->get_option( 'date_format' );
				$date_format = $date_format ? $date_format : get_option( 'date_format' );
				$separator   = '<span class="fusion-meta-tb-sep">' . $args['separator'] . '</span>';
				$post_type   = get_post_type( $post_id );
				$author_id   = -99 === $post_id ? get_post_field( 'post_author' ) : get_post_field( 'post_author', $post_id );
				$is_builder  = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				foreach ( $options as $index => $option ) {
					switch ( $option ) {
						case 'author':
							$link = sprintf(
								'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
								esc_url( get_author_posts_url( $author_id ) ),
								/* translators: %s: Author's display name. */
								esc_attr( sprintf( __( 'Posts by %s' ), get_the_author_meta( 'display_name', $author_id ) ) ),
								get_the_author_meta( 'display_name', $author_id )
							);
							/* Translators: %s: The author. */
							$element  = 'no' === $args['display_element_labels'] ? '<span>' . $link . '</span>' : sprintf( esc_html__( 'By %s', 'fusion-builder' ), '<span>' . $link . '</span>' );
							$content .= '<span class="fusion-tb-author">' . $element . '</span>' . $separator;
							break;
						case 'published_date':
							/* Translators: %s: Date. */
							$element  = 'no' === $args['display_element_labels'] ? get_the_time( $date_format ) : sprintf( esc_html__( 'Published On: %s', 'fusion-builder' ), get_the_time( $date_format ) );
							$content .= '<span class="fusion-tb-published-date">' . $element . '</span>' . $separator;
							break;
						case 'modified_date':
							/* Translators: %s: Date. */
							$element  = 'no' === $args['display_element_labels'] ? get_the_modified_date( $date_format ) : sprintf( esc_html__( 'Last Updated: %s', 'fusion-builder' ), get_the_modified_date( $date_format ) );
							$content .= '<span class="fusion-tb-modified-date">' . $element . '</span>' . $separator;
							break;
						case 'categories':
							$categories = '';
							$taxonomies = [
								'avada_portfolio' => 'portfolio_category',
								'avada_faq'       => 'faq_category',
								'product'         => 'product_cat',
								'tribe_events'    => 'tribe_events_cat',
							];

							if ( 'post' === $post_type || isset( $taxonomies[ $post_type ] ) ) {
								$categories = 'post' === $post_type ? get_the_category_list( ', ', '', $post_id ) : get_the_term_list( $post_id, $taxonomies[ $post_type ], '', ', ' );
							}

							if ( ! is_wp_error( $categories ) && $categories ) {
								/* Translators: %s: List of categories. */
								$element  = 'no' === $args['display_element_labels'] ? $categories : sprintf( esc_html__( 'Categories: %s', 'fusion-builder' ), $categories );
								$content .= '<span class="fusion-tb-categories">' . $element . '</span>' . $separator;
							} else {
								$content .= '';
							}
							break;
						case 'comments':
							$screen_reader          = '<span class="screen-reader-text"> ' . esc_html__( 'on', 'fusion-builder' ) . ' ' . get_the_title( $post_id ) . '</span>';
							$screen_reader_singular = '<span class="screen-reader-text"> ' . esc_html__( 'comment on', 'fusion-builder' ) . ' ' . get_the_title( $post_id ) . '</span>';
							$screen_reader_plural   = '<span class="screen-reader-text"> ' . esc_html__( 'comments on', 'fusion-builder' ) . ' ' . get_the_title( $post_id ) . '</span>';
							$screen_reader_off      = '<span class="screen-reader-text"> ' . esc_html__( 'Comments off on', 'fusion-builder' ) . ' ' . get_the_title( $post_id ) . '</span>';
							ob_start();
							if ( 'no' === $args['display_element_labels'] ) {
								comments_popup_link( '<i class="awb-icon-bubbles"></i> 0' . $screen_reader_plural, '<i class="awb-icon-bubbles"></i> 1' . $screen_reader_singular, '<i class="awb-icon-bubbles"></i> %' . $screen_reader_plural, '', '<i class="awb-icon-bubbles"></i> ' . esc_html__( 'Off', 'fusoin-builder' ) . $screen_reader_off );
							} else {
								comments_popup_link( esc_html__( '0 Comments', 'fusion-builder' ) . $screen_reader, esc_html__( '1 Comment', 'fusion-builder' ) . $screen_reader, esc_html__( '% Comments', 'fusion-builder' ) . $screen_reader );
							}
							$comments = ob_get_clean();
							$content .= '<span class="fusion-tb-comments">' . $comments . '</span>' . $separator;
							break;
						case 'tags':
							$tags       = '';
							$taxonomies = [
								'avada_portfolio' => 'portfolio_tags',
								'product'         => 'product_tag',
							];

							if ( 'post' === $post_type || isset( $taxonomies[ $post_type ] ) ) {
								$tags = isset( $taxonomies[ $post_type ] ) ? get_the_term_list( $post_id, $taxonomies[ $post_type ], '', ', ', '' ) : get_the_tag_list( '', ', ', '' );
							}

							if ( ! is_wp_error( $tags ) && $tags ) {
								/* Translators: %s: List of tags. */
								$element  = 'no' === $args['display_element_labels'] ? $tags : sprintf( esc_html__( 'Tags: %s', 'fusion-builder' ), $tags );
								$content .= '<span class="fusion-tb-tags">' . $element . '</span>' . $separator;
							} else {
								$content .= '';
							}
							break;
						case 'skills':
							$skills = '';
							if ( 'avada_portfolio' === $post_type ) {
								$skills = get_the_term_list( $post_id, 'portfolio_skills', '', ', ', '' );
							}

							/* Translators: %s: List of skills. */
							$element  = 'no' === $args['display_element_labels'] ? $skills : sprintf( esc_html__( 'Skills Needed: %s', 'fusion-builder' ), $skills );
							$content .= $skills ? apply_filters( 'fusion_portfolio_post_skills_label', '<span class="fusion-tb-skills">' . $skills . '</span>' ) . $separator : '';
							break;
						case 'sku':
							$sku_can_be_displayed = ( function_exists( 'wc_product_sku_enabled' ) && wc_product_sku_enabled() && is_object( $product ) && ( '' !== $product->get_sku() || $product->is_type( 'variable' ) ) );

							if ( $sku_can_be_displayed || $is_live || $is_builder ) {
								$sku_is_not_empty            = ( is_object( $product ) && $product->get_sku() );
								$need_random_sku_for_builder = ( ( $is_live || $is_builder ) && ! $sku_is_not_empty );

								$sku      = ( $sku_is_not_empty ? $product->get_sku() : esc_html__( 'N/A', 'fusion-builder' ) );
								$sku      = $need_random_sku_for_builder ? wp_rand( 10000, 99999 ) : $sku;
								$element  = 'no' === $args['display_element_labels'] ? '<span class="sku">' . $sku . '</span>' : esc_html__( 'SKU:', 'fusion-builder' ) . ' <span class="sku">' . $sku . '</span>';
								$content .= '<span class="fusion-tb-sku product_meta">' . $element . '</span>' . $separator;
							}
							break;
						case 'event_date':
							$event_date = $this->get_event_date();
							if ( $event_date ) {
								$content .= '<span class="fusion-tb-event-date">' . $event_date . '</span>' . $separator;
							}
							break;
						case 'event_start_date':
							$event_start_date = $this->get_event_start_date();
							if ( $event_start_date ) {
								$content .= '<span class="fusion-tb-event-start-date">' . $event_start_date . '</span>' . $separator;
							}
							break;
						case 'event_end_date':
							$event_end_date = $this->get_event_end_date();
							if ( $event_end_date ) {
								$content .= '<span class="fusion-tb-event-end-date">' . $event_end_date . '</span>' . $separator;
							}
							break;
						case 'event_venue':
							$event_venue = function_exists( 'tribe_get_venue' ) ? tribe_get_venue( $post_id ) : '';
							if ( $event_venue ) {
								$event_venue_link = '';
								if ( 'venue_page' === $args['event_venue_link'] ) {
									$event_venue_link = tribe_get_venue_link( $post_id, false );
								} elseif ( 'venue_website' === $args['event_venue_link'] ) {
									$event_venue_link = tribe_get_venue_website_url( $post_id );
								}

								if ( $event_venue_link ) {
									$event_venue = '<a href="' . esc_url( $event_venue_link ) . '" target="_blank" aria-label="' . esc_attr( $event_venue ) . '">' . $event_venue . '</a>';
								}

								$content .= '<span class="fusion-tb-event-venue">' . $event_venue . '</span>' . $separator;
							}
							break;
						case 'event_venue_address':
							$event_address = function_exists( 'tribe_get_venue_single_line_address' ) ? tribe_get_venue_single_line_address( $post_id, false ) : '';
							if ( $event_address ) {
								$event_venue   = function_exists( 'tribe_get_venue' ) ? tribe_get_venue( $post_id ) . _x( ', ', 'Address separator', 'the-events-calendar' ) : '';
								$event_address = str_replace( $event_venue, '', $event_address );
								$content      .= '<span class="fusion-tb-event-address">' . $event_address . '</span>' . $separator;
							}
							break;
						case 'event_organizer':
							$event_organizer = function_exists( 'tribe_get_organizer' ) ? tribe_get_organizer( $post_id ) : '';
							if ( $event_organizer ) {
								$event_organizer_link = '';
								if ( 'organizer_page' === $args['event_organizer_link'] ) {
									$event_organizer_link = tribe_get_organizer_link( $post_id, false );
								} elseif ( 'organizer_website' === $args['event_organizer_link'] ) {
									$event_organizer_link = tribe_get_organizer_website_url( $post_id );
								} elseif ( 'organizer_email' === $args['event_organizer_link'] ) {
									$event_organizer_link = 'mailto:' . tribe_get_organizer_email( $post_id );
								}

								if ( $event_organizer_link ) {
									$event_organizer = '<a href="' . esc_url( $event_organizer_link ) . '" target="_blank" aria-label="' . esc_attr( $event_organizer ) . '">' . $event_organizer . '</a>';
								}

								$content .= '<span class="fusion-tb-event-organizer">' . $event_organizer . '</span>' . $separator;
							}
							break;
						case 'event_cost':
							$event_cost = function_exists( 'tribe_get_formatted_cost' ) ? tribe_get_formatted_cost( $post_id ) : '';
							if ( $event_cost ) {
								$content .= '<span class="fusion-tb-event-cost">' . $event_cost . '</span>' . $separator;
							}
							break;
						case 'word_count':
							$this->set_word_count();

							/* Translators: %s: number of words. */
							$element  = 'no' === $args['display_element_labels'] ? $this->word_count : sprintf( esc_html__( '%s words', 'fusion-builder' ), $this->word_count );
							$content .= '<span class="fusion-tb-published-word-count">' . $element . '</span>' . $separator;
							break;
						case 'read_time':
							$this->set_word_count();

							$reading_time_args = [
								'reading_speed'         => $this->args['read_time'],
								'use_decimal_precision' => $this->args['reading_time_decimal'],
							];

							$reading_time = awb_get_reading_time_for_display( $post_id, $reading_time_args, $this->word_count );
							/* Translators: %s: minutes of read. */
							$element  = 'no' === $args['display_element_labels'] ? sprintf( esc_html__( '%s min', 'fusion-builder' ), $reading_time ) : sprintf( esc_html__( '%s min read', 'fusion-builder' ), $reading_time );
							$content .= '<span class="fusion-tb-published-read-time">' . $element . '</span>' . $separator;
							break;
						case 'total_views':
							$total_views_num = avada_get_post_views( $post_id );

							$both_views_are_displayed = in_array( 'today_views', $options, true );
							if ( $both_views_are_displayed ) {
								/* Translators: %s: number of total views of a post. */
								$element     = 'no' === $args['display_element_labels'] ? $total_views_num : sprintf( esc_html__( 'Total Views: %s', 'fusion-builder' ), $total_views_num );
								$total_views = $element;
							} else {
								/* Translators: %s: number of total views of a post. */
								$element     = 'no' === $args['display_element_labels'] ? $total_views_num : sprintf( esc_html__( 'Views: %s', 'fusion-builder' ), $total_views_num );
								$total_views = $element;
							}

							$content .= '<span class="fusion-tb-total-views">' . $total_views . '</span>' . $separator;
							break;
						case 'today_views':
							$today_views_num = avada_get_today_post_views( $post_id );
							/* Translators: %s: number of daily views. */
							$element     = 'no' === $args['display_element_labels'] ? $today_views : sprintf( esc_html__( 'Daily Views: %s', 'fusion-builder' ), $today_views_num );
							$today_views = $element;
							$content    .= '<span class="fusion-tb-today-views">' . $today_views . '</span>' . $separator;
							break;
						case 'post_type':
							$post_type = get_post_type_object( get_post_type() );
							$post_type = is_object( $post_type ) ? $post_type->labels->singular_name : '';

							if ( $post_type ) {
								$content .= '<span class="fusion-tb-post-type">' . $post_type . '</span>' . $separator;
							}
							break;
					}
				}

				return $content;
			}

			/**
			 * Set the word count.
			 *
			 * @since 3.9
			 * @access private
			 * @return void
			 */
			private function set_word_count() {
				if ( ! $this->word_count ) {
					$post_id = $this->get_post_id();
					$post    = ( -99 === $post_id || '-99' === $post_id ) ? Fusion_Dummy_Post::get_dummy_post() : get_post( $post_id );

					$this->word_count = awb_get_post_content_word_count( $post );
				}
			}

			/**
			 * Get the event date.
			 *
			 * @since 3.5
			 * @return string Empty string if the post is not an event.
			 */
			public function get_event_date() {
				$event_id = $this->get_post_id();
				$event    = get_post( $event_id );

				if ( ! $event instanceof WP_Post || ! function_exists( 'tribe_events_event_schedule_details' ) ) {
					return '';
				}

				$post_is_event_type = ( 'tribe_events' === $event->post_type ? true : false );
				if ( ! $post_is_event_type ) {
					return '';
				}

				add_filter( 'tribe_events_recurrence_tooltip', [ $this, 'remove_event_recurring_info' ], 999 );
				$date = tribe_events_event_schedule_details( $event_id );
				remove_filter( 'tribe_events_recurrence_tooltip', [ $this, 'remove_event_recurring_info' ], 999 );

				return $date;
			}

			/**
			 * Remove the recurring event after the meta, since it will take a
			 * lot of space.
			 *
			 * @param string $tooltip The recurring tooltip.
			 * @return string Empty string, containing no tooltip.
			 */
			public static function remove_event_recurring_info( $tooltip ) {
				return '';
			}

			/**
			 * Get the event start date.
			 *
			 * @since 3.5
			 * @return string Empty string if the post is not an event.
			 */
			public function get_event_start_date() {
				$event_id = $this->get_post_id();
				$event    = get_post( $event_id );

				if ( ! $event instanceof WP_Post || ! function_exists( 'tribe_get_start_date' ) ) {
					return '';
				}

				$post_is_event_type = ( 'tribe_events' === $event->post_type ? true : false );
				if ( ! $post_is_event_type ) {
					return '';
				}

				/* translators: %s: a date, representing the start date of an event. */
				$element = 'no' === $this->args['display_element_labels'] ? tribe_get_start_date( $event_id ) : sprintf( __( 'Start Date: %s', 'fusion-builder' ), tribe_get_start_date( $event_id ) );
				return $element;
			}

			/**
			 * Get the event end date.
			 *
			 * @since 3.5
			 * @return string Empty string if the post is not an event.
			 */
			public function get_event_end_date() {
				$event_id = $this->get_post_id();
				$event    = get_post( $event_id );

				if ( ! $event instanceof WP_Post || ! function_exists( 'tribe_get_end_date' ) ) {
					return '';
				}

				$post_is_event_type = ( 'tribe_events' === $event->post_type ? true : false );
				if ( ! $post_is_event_type ) {
					return '';
				}

				/* translators: %s: a date, representing the end date of an event. */
				$element = 'no' === $this->args['display_element_labels'] ? tribe_get_end_date( $event_id ) : sprintf( __( 'End Date: %s', 'fusion-builder' ), tribe_get_end_date( $event_id ) );
				return $element;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/meta.min.css' );

				if ( class_exists( 'Avada' ) ) {
					$version = Avada::get_theme_version();

					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'awb-meta-md',
						FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/meta-md.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-medium' ),
					];
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'awb-meta-sm',
						FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/meta-sm.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-small' ),
					];
				}
			}
		}
	}

	new FusionTB_Meta();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.4
 */
function fusion_component_meta() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Meta',
			[
				'name'      => esc_attr__( 'Post Meta', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_meta',
				'icon'      => 'fusiona-meta-data',
				'component' => true,
				'templates' => [ 'content', 'post_cards', 'page_title_bar' ],
				'params'    => [
					[
						'type'        => 'connected_sortable',
						'heading'     => esc_attr__( 'Meta Elements', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the order of meta elements.', 'fusion-builder' ),
						'param_name'  => 'meta',
						'default'     => 'author,published_date,categories,comments,tags',
						'choices'     => [
							'author'              => esc_attr__( 'Author', 'fusion-builder' ),
							'published_date'      => esc_attr__( 'Published Date', 'fusion-builder' ),
							'modified_date'       => esc_attr__( 'Modified Date', 'fusion-builder' ),
							'categories'          => esc_attr__( 'Categories', 'fusion-builder' ),
							'comments'            => esc_attr__( 'Comments', 'fusion-builder' ),
							'tags'                => esc_attr__( 'Tags', 'fusion-builder' ),
							'skills'              => esc_attr__( 'Portfolio Skills', 'fusion-builder' ),
							'sku'                 => esc_attr__( 'Product SKU', 'fusion-builder' ),
							'event_date'          => esc_attr__( 'Event Full Date', 'fusion-builder' ),
							'event_start_date'    => esc_attr__( 'Event Start Date', 'fusion-builder' ),
							'event_end_date'      => esc_attr__( 'Event End Date', 'fusion-builder' ),
							'event_venue'         => esc_attr__( 'Event Venue', 'fusion-builder' ),
							'event_venue_address' => esc_attr__( 'Event Venue Address', 'fusion-builder' ),
							'event_organizer'     => esc_attr__( 'Event Organizer', 'fusion-builder' ),
							'event_cost'          => esc_attr__( 'Event Cost', 'fusion-builder' ),
							'word_count'          => esc_attr__( 'Word Count', 'fusion-builder' ),
							'read_time'           => esc_attr__( 'Reading Time', 'fusion-builder' ),
							'total_views'         => esc_attr__( 'Total Views', 'fusion-builder' ),
							'today_views'         => esc_attr__( 'Daily Views', 'fusion-builder' ),
							'post_type'           => esc_attr__( 'Post Type', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Choose if meta items should be stacked and full width, or if they should be floated.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'default'     => 'floated',
						'value'       => [
							'stacked' => esc_html__( 'Stacked', 'fusion-builder' ),
							'floated' => esc_html__( 'Floated', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Element Labels', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls whether the labels of chosen elements should be displayed.', 'fusion-builder' ),
						'param_name'  => 'display_element_labels',
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Separator', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the type of separator between each meta item.', 'fusion-builder' ),
						'param_name'  => 'separator',
						'escape_html' => true,
						'callback'    => [
							'function' => 'fusion_update_tb_meta_separator',
							'args'     => [
								'selector' => '.fusion-meta-tb',
							],
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'stacked',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Event Venue Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Select which link should be used on the event venue.', 'fusion-builder' ),
						'param_name'  => 'event_venue_link',
						'value'       => [
							'venue_page'    => esc_html__( 'Venue Page', 'fusion-builder' ),
							'venue_website' => esc_html__( 'Venue Website', 'fusion-builder' ),
							'none'          => esc_html__( 'None', 'fusion-builder' ),
						],
						'default'     => 'venue_page',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'meta',
								'value'    => 'event_venue',
								'operator' => 'contains',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Event Organizer Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Select which link should be used on the event organizer.', 'fusion-builder' ),
						'param_name'  => 'event_organizer_link',
						'value'       => [
							'organizer_page'    => esc_html__( 'Organizer Page', 'fusion-builder' ),
							'organizer_website' => esc_html__( 'Organizer Website', 'fusion-builder' ),
							'organizer_email'   => esc_html__( 'Organizer Email', 'fusion-builder' ),
							'none'              => esc_html__( 'None', 'fusion-builder' ),
						],
						'default'     => 'organizer_page',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'meta',
								'value'    => 'event_venue',
								'operator' => 'contains',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Reading Time', 'fusion-builder' ),
						'description' => esc_attr__( 'Average words read per minute. The default value is 200.', 'fusion-builder' ),
						'param_name'  => 'read_time',
						'value'       => '200',
						'default'     => '200',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'meta',
								'value'    => 'read_time',
								'operator' => 'contains',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Reading Time Decimal Precision', 'fusion-builder' ),
						'description' => esc_attr__( 'Whether to use(Ex: 2.3 min) or not(Ex: 2 min) decimal precision in reading time.', 'fusion-builder' ),
						'param_name'  => 'reading_time_decimal',
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'meta',
								'value'    => 'read_time',
								'operator' => 'contains',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Event Venue', 'fusion-builder' ),
						'description' => esc_attr__( 'Average words read per minute. The default value is 200.', 'fusion-builder' ),
						'param_name'  => 'read_time',
						'value'       => '200',
						'default'     => '200',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'meta',
								'value'    => 'read_time',
								'operator' => 'contains',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the meta alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => 'flex-start',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
							'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
							'space-around'  => '<span class="fusiona-horizontal-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-horizontal-space-evenly"></span>',
						],
						'value'       => [
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'stacked',
								'operator' => '!=',
							],
						],
						'responsive'  => [
							'state' => 'large',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Vertical Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Defines how the meta content should align vertically.', 'fusion-builder' ),
						'param_name'  => 'stacked_vertical_align',
						'default'     => 'flex-start',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'flex-start'    => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_attr__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_attr__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_attr__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_attr__( 'Space Evenly', 'fusion-builder' ),
						],
						'icons'       => [
							'flex-start'    => '<span class="fusiona-align-top-vert"></span>',
							'center'        => '<span class="fusiona-align-center-vert"></span>',
							'flex-end'      => '<span class="fusiona-align-bottom-vert"></span>',
							'space-between' => '<span class="fusiona-space-between"></span>',
							'space-around'  => '<span class="fusiona-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-space-evenly"></span>',
						],
						'grid_layout' => true,
						'back_icons'  => true,
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'floated',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Horizontal Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Defines how the meta content should align horizontally.  Overrides what is set on the container.', 'fusion-builder' ),
						'param_name'  => 'stacked_horizontal_align',
						'default'     => 'flex-start',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'grid_layout' => true,
						'back_icons'  => true,
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'floated',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Height', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the Meta section height. In pixels.', 'fusion-builder' ),
						'param_name'  => 'height',
						'value'       => '36',
						'min'         => '0',
						'max'         => '500',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Text Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the meta text. Enter value including CSS unit (px, em, rem), ex: 10px', 'fusion-builder' ),
						'param_name'  => 'font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the meta section text.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link color of the meta section text.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'states'      => [
							'hover' => [
								'label'      => __( 'Hover', 'fusion-builder' ),
								'param_name' => 'text_hover_color',
								'default'    => $fusion_settings->get( 'link_hover_color' ),
								'preview'    => [
									'selector' => 'a',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of element wrapper.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size of the element wrapper. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_sizes',
						'value'            => [
							'border_top'    => '',
							'border_right'  => '',
							'border_bottom' => '',
							'border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the element wrapper.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
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
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Meta Item Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the meta item.', 'fusion-builder' ),
						'param_name'  => 'item_background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Meta Item Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size of the meta item. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'item_border_sizes',
						'value'            => [
							'item_border_top'    => '',
							'item_border_right'  => '',
							'item_border_bottom' => '',
							'item_border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Meta Item Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the meta item.', 'fusion-builder' ),
						'param_name'  => 'item_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Meta Item Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'item_padding',
						'value'            => [
							'item_padding_top'    => '',
							'item_padding_right'  => '',
							'item_padding_bottom' => '',
							'item_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Meta Item Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'item_margin',
						'value'            => [
							'item_margin_top'    => '',
							'item_margin_right'  => '',
							'item_margin_bottom' => '',
							'item_margin_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
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
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-meta-tb',
					],
				],
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_meta',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_meta' );
