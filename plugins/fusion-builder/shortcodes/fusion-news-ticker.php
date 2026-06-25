<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.5
 */

if ( fusion_is_element_enabled( 'fusion_news_ticker' ) ) {

	if ( ! class_exists( 'Fusion_News_Ticker' ) && class_exists( 'Fusion_Element' ) ) {

		/**
		 * Shortcode class.
		 *
		 * @since 3.5
		 */
		class Fusion_News_Ticker extends Fusion_Element {

			/**
			 * The number of instance of this element. Working as an id.
			 *
			 * @access protected
			 * @since 3.5
			 * @var int
			 */
			protected $element_id = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.5
			 */
			public function __construct() {
				parent::__construct();

				add_shortcode( 'fusion_news_ticker', [ $this, 'render' ] );
				add_filter( 'fusion_pipe_seprator_shortcodes', [ $this, 'allow_separator' ] );

				add_filter( 'fusion_attr_news-ticker-element', [ $this, 'ticker_attr' ] );
				add_filter( 'fusion_attr_news-ticker-title', [ $this, 'ticker_title_attr' ] );
				add_filter( 'fusion_attr_news-ticker-bar', [ $this, 'ticker_bar_attr' ] );
				add_filter( 'fusion_attr_news-ticker-items-list', [ $this, 'ticker_items_list_attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_news_ticker_posts', [ $this, 'ajax_query' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'ticker_title'                    => '',
					'post_type'                       => 'post',
					'pull_by'                         => '',
					'orderby'                         => 'date',
					'order'                           => 'DESC',
					'posts_number'                    => '7',

					'show_date'                       => 'no',
					'date_format'                     => '',

					'ticker_type'                     => 'marquee',
					'ticker_speed'                    => '75',
					'posts_distance'                  => '',
					'separator'                       => '',
					'carousel_display_time'           => '6',
					'link_target'                     => '_self',

					'hide_on_mobile'                  => fusion_builder_default_visibility( 'string' ),
					'class'                           => '',
					'id'                              => '',

					// Design.
					'fusion_font_family_ticker_font'  => '',
					'fusion_font_variant_ticker_font' => '',
					'font_size'                       => '',
					'line_height'                     => '2.5',
					'letter_spacing'                  => '',
					'text_transform'                  => '',

					'ticker_height'                   => '',
					'title_font_color'                => '',
					'title_background_color'          => '',
					'title_shape'                     => 'none',

					'ticker_font_color'               => '',
					'ticker_hover_font_color'         => '',
					'ticker_background_hover_color'   => '',
					'ticker_background_color'         => '',
					'ticker_indicators_color'         => '',
					'ticker_indicators_hover_color'   => '',
					'carousel_bar_height'             => '3',
					'carousel_arrows_style'           => 'none',
					'carousel_btn_border_radius'      => '',

					'title_padding_right'             => '',
					'title_padding_left'              => '',

					'ticker_padding_right'            => '',
					'ticker_padding_left'             => '',

					'btn_padding_top'                 => '',
					'btn_padding_right'               => '',
					'btn_padding_bottom'              => '',
					'btn_padding_left'                => '',

					'margin_top'                      => '',
					'margin_right'                    => '',
					'margin_bottom'                   => '',
					'margin_left'                     => '',

					'border_radius_top_left'          => '',
					'border_radius_top_right'         => '',
					'border_radius_bottom_right'      => '',
					'border_radius_bottom_left'       => '',

					'box_shadow'                      => '',
					'box_shadow_blur'                 => '',
					'box_shadow_color'                => '',
					'box_shadow_horizontal'           => '',
					'box_shadow_spread'               => '',
					'box_shadow_style'                => '',
					'box_shadow_vertical'             => '',

					// Extra.
					'animation_direction'             => 'left',
					'animation_offset'                => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                 => '',
					'animation_delay'                 => '',
					'animation_type'                  => '',
					'animation_color'                 => '',
				];
			}

			/**
			 * Enables pipe separator for short code.
			 *
			 * @since 3.5
			 * @param array $shortcodes The shortcodes array.
			 * @return array
			 */
			public function allow_separator( $shortcodes ) {
				if ( is_array( $shortcodes ) ) {
					array_push( $shortcodes, 'fusion_news_ticker' );
				}

				return $shortcodes;
			}

			/**
			 * Render the shortcode.
			 *
			 * @since 3.5
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();

				// We need dynamic defaults for taxonomies.
				if ( isset( $args['pull_by'] ) ) {
					$this->defaults[ 'dynamic_tax_include_' . $args['pull_by'] ] = '';
					$this->defaults[ 'dynamic_tax_exclude_' . $args['pull_by'] ] = '';
				}
				$this->args = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_news_ticker' );

				$html  = '';
				$html .= '<div ' . FusionBuilder::attributes( 'news-ticker-element' ) . '>';

				if ( ! empty( $this->args['ticker_title'] ) ) {
					$html .= '<div ' . FusionBuilder::attributes( 'news-ticker-title' ) . '>';
					$html .= $this->args['ticker_title'];
					if ( 'triangle' === $this->args['title_shape'] ) {
						$html .= '<div class="awb-news-ticker-title-decorator awb-news-ticker-title-decorator-triangle"></div>';
					}
					$html .= '</div>';
				}

				$html .= '<div ' . FusionBuilder::attributes( 'news-ticker-bar' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'news-ticker-items-list' ) . '>';
				$html .= $this->get_ticker_items_html( $this->get_ticker_items_query_args() );
				$html .= '</div>';
				$html .= $this->get_carousel_buttons_if_necessary();
				$html .= '</div>';

				$html .= '</div>';

				$this->element_id++;

				$this->on_render();

				return $html;
			}

			/**
			 * Get the ticker element attributes.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function ticker_attr() {
				$attr = [
					'class' => 'awb-news-ticker awb-news-ticker-' . $this->element_id,
					'role'  => 'marquee',
					'style' => $this->get_inline_style(),
				];

				if ( 'marquee' === $this->args['ticker_type'] ) {
					$attr['class'] .= ' awb-news-ticker-marquee';
				} elseif ( 'carousel' === $this->args['ticker_type'] ) {
					$attr['class'] .= ' awb-news-ticker-carousel';
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				return $attr;
			}

			/**
			 * Get the ticker title attributes.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function ticker_title_attr() {
				$attr = [
					'class' => 'awb-news-ticker-title',
				];

				if ( 'rounded' === $this->args['title_shape'] ) {
					$attr['class'] .= ' awb-news-ticker-title-rounded';
				}

				return $attr;
			}

			/**
			 * Get the ticker bar attributes.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function ticker_bar_attr() {
				$attr = [
					'class' => 'awb-news-ticker-bar',
				];

				if ( 'marquee' === $this->args['ticker_type'] ) {
					$attr['class'] .= ' awb-news-ticker-bar-marquee';
				} elseif ( 'carousel' === $this->args['ticker_type'] ) {
					$attr['class'] .= ' awb-news-ticker-bar-carousel';
				}

				return $attr;
			}

			/**
			 * Get the attributes for the ticker items wrapper.
			 *
			 * @since 3.5
			 * @return array
			 */
			public function ticker_items_list_attr() {
				$attr = [
					'class' => 'awb-news-ticker-item-list',
				];

				if ( 'marquee' === $this->args['ticker_type'] ) {
					$attr['class']                .= ' awb-news-ticker-item-list-run';
					$attr['data-awb-ticker-speed'] = $this->args['ticker_speed'];
				} elseif ( 'carousel' === $this->args['ticker_type'] ) {
					$attr['class']                            .= ' awb-news-ticker-item-list-carousel';
					$attr['data-awb-news-ticker-display-time'] = $this->args['carousel_display_time'];
				}

				return $attr;
			}

			/**
			 * Get the inline style.
			 *
			 * @since 3.5
			 * @return string
			 */
			public function get_inline_style() {
				$css_vars_options = [
					'font_size',
					'letter_spacing',
					'text_transform',
					'ticker_height',
					'title_font_color',
					'title_background_color',
					'ticker_font_color',
					'ticker_hover_font_color',
					'ticker_background_color',
					'ticker_background_hover_color',
					'ticker_indicators_color',
					'ticker_indicators_hover_color',
					'title_padding_right',
					'title_padding_left',
					'carousel_btn_border_radius',
					'btn_padding_top',
					'btn_padding_right',
					'btn_padding_bottom',
					'btn_padding_left',
					'ticker_padding_right',
					'ticker_padding_left',
					'margin_top',
					'margin_right',
					'margin_bottom',
					'margin_left',
					'border_radius_top_left',
					'border_radius_top_right',
					'border_radius_bottom_right',
					'border_radius_bottom_left',
				];

				if ( $this->args['line_height'] ) {
					$css_vars_options [] = 'line_height';
				}

				$custom_vars = [];

				$typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'ticker_font', 'array' );
				foreach ( $typography as $rule => $value ) {
					$custom_vars[ $rule ] = $value;
				}

				if ( ! $this->is_default( 'carousel_bar_height' ) ) {
					$custom_vars['carousel_bar_height'] = $this->args['carousel_bar_height'] . 'px';
				}
				// Increase padding a little on triangle shape.
				if ( 'carousel' === $this->args['ticker_type'] && 'triangle' === $this->args['title_shape'] ) {
					if ( ! is_rtl() && $this->is_default( 'ticker_padding_left' ) ) {
						$custom_vars['ticker_padding_left'] = '17px';
					} elseif ( $this->is_default( 'ticker_padding_right' ) ) {
						$custom_vars['ticker_padding_right'] = '17px';
					}
				}
				if ( ! $this->is_default( 'carousel_display_time' ) ) {
					$custom_vars['carousel_display_time'] = $this->args['carousel_display_time'] . 's';
				}

				if ( $this->args['posts_distance'] && 'marquee' === $this->args['ticker_type'] ) {
					$custom_vars['posts_distance'] = round( $this->args['posts_distance'] / 2, 1 ) . 'px';
				}

				$box_shadow = Fusion_Builder_Box_Shadow_Helper::get_box_shadow_css_var(
					'--awb-box-shadow',
					[
						'box_shadow'            => $this->args['box_shadow'],
						'box_shadow_horizontal' => $this->args['box_shadow_horizontal'],
						'box_shadow_vertical'   => $this->args['box_shadow_vertical'],
						'box_shadow_blur'       => $this->args['box_shadow_blur'],
						'box_shadow_spread'     => $this->args['box_shadow_spread'],
						'box_shadow_color'      => $this->args['box_shadow_color'],
						'box_shadow_style'      => $this->args['box_shadow_style'],
					]
				);

				return $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars ) . $box_shadow;
			}

			/**
			 * Get the query arguments for ticker items.
			 *
			 * @since 3.5
			 * @param array $args Other arguments, that will replace $this->args.
			 * @return array
			 */
			public function get_ticker_items_query_args( $args = null ) {
				if ( null === $args ) {
					$args = $this->args;
				}

				$posts_options = [];

				// Post Type Argument.
				$post_type = 'post';
				if ( $args['post_type'] ) {
					$post_type = $args['post_type'];
				}
				$posts_options['post_type'] = $post_type;

				// Number of posts argument.
				$number_of_posts = 7;
				if ( $args['posts_number'] && is_numeric( $args['posts_number'] ) ) {
					$number_of_posts = $args['posts_number'];
				}
				$posts_options['posts_per_page'] = $number_of_posts;

				// Order & orderby.
				if ( ! empty( $args['orderby'] ) && 'upcoming_event' !== $args['orderby'] ) {
					$posts_options['orderby'] = $args['orderby'];
					if ( ! empty( $args['order'] ) ) {
						$posts_options['order'] = $args['order'];
					}
				}

				$posts_options = array_merge( $posts_options, $this->get_posts_taxonomy_filters( $args ) );

				return apply_filters( 'awb_news_ticker_posts_options', $posts_options, $this->element_id );
			}

			/**
			 * Returns an array that contains the taxonomy arguments, ready to be merged with the wp query array.
			 *
			 * @since 3.5
			 * @param array $args The arguments.
			 * @return array
			 */
			private function get_posts_taxonomy_filters( $args ) {
				$post_type = $args['post_type'];
				$taxonomy  = $args['pull_by'];
				$wp_query  = [];

				if ( 'all' === $taxonomy || empty( $taxonomy ) || empty( $post_type ) ) {
					return $wp_query;
				}

				// Return if taxonomy does not exist in the post type.
				$post_type_taxonomies = get_object_taxonomies( $post_type, 'objects' );
				if ( ! isset( $post_type_taxonomies[ $taxonomy ] ) ) {
					return $wp_query;
				}

				$include_query_args = [];
				$exclude_query_args = [];
				if ( ! empty( $args[ 'dynamic_tax_include_' . $taxonomy ] ) ) {
					$include_query_args = [
						[
							'taxonomy'         => $taxonomy,
							'terms'            => explode( ',', $args[ 'dynamic_tax_include_' . $taxonomy ] ),
							'include_children' => true,
						],
					];
				}
				if ( ! empty( $args[ 'dynamic_tax_exclude_' . $taxonomy ] ) ) {
					$exclude_query_args = [
						'taxonomy'         => $taxonomy,
						'terms'            => explode( ',', $args[ 'dynamic_tax_exclude_' . $taxonomy ] ),
						'include_children' => true,
						'operator'         => 'NOT IN',
					];
				}

				$tax_query = [];
				if ( ! empty( $include_query_args ) && ! empty( $exclude_query_args ) ) {
					$tax_query['relation'] = 'AND';
					array_push( $tax_query, $include_query_args );
					array_push( $tax_query, $exclude_query_args );
				} elseif ( ! empty( $include_query_args ) ) {
					array_push( $tax_query, $include_query_args );
				} elseif ( ! empty( $exclude_query_args ) ) {
					array_push( $tax_query, $exclude_query_args );
				}

				if ( ! empty( $tax_query ) ) {
					// phpcs:ignore WordPress.DB.SlowDBQuery
					$wp_query['tax_query'] = $tax_query;
				}

				return $wp_query;
			}

			/**
			 * Get the HTML of ticker items.
			 *
			 * @since 3.5
			 * @param array $query_args Wp_Query arguments to retrieve the posts.
			 * @param array $args Other arguments, that will replace $this->args.
			 * @return string
			 */
			public function get_ticker_items_html( $query_args, $args = null ) {
				if ( null === $args ) {
					$args = $this->args;
				}

				$query_needs_custom_event_calendar_function = 'tribe_events' === $args['post_type'] && function_exists( 'tribe_get_events' ) && 'upcoming_events' === $args['orderby'];
				if ( $query_needs_custom_event_calendar_function ) {
					$query_args['ends_after'] = 'now';
					$query_args['orderby']    = 'event_date';
					$query_args['order']      = 'ASC';

					$ticker_posts = tribe_get_events( $query_args );
				} else {
					$ticker_posts = get_posts( $query_args );
				}

				$separator = '';
				if ( $args['separator'] ) {
					$separator = $args['separator'];
				}

				$html                = '';
				$displayed_posts_num = count( $ticker_posts );
				$carousel_mode       = ( 'carousel' === $args['ticker_type'] ? true : false );
				$display_separator   = ( ! $carousel_mode );
				$link_target         = ( '_blank' === $args['link_target'] ? ' target="_blank"' : '' );
				foreach ( $ticker_posts as $index => $ticker_post ) {
					$additional_item_class = '';
					if ( 0 === $index && $carousel_mode ) {
						$additional_item_class = ' awb-news-ticker-item-active';
					}

					$text = get_the_title( $ticker_post );
					if ( 'yes' === $args['show_date'] ) {
						$date_format = 'M j: ';
						if ( $args['date_format'] ) {
							$date_format = $args['date_format'];
						}

						if ( get_post_type( $ticker_post ) === 'tribe_events' && function_exists( 'tribe_get_start_date' ) ) {
							$date_text = tribe_get_start_date( $ticker_post, false, $date_format );
						} else {
							$date_text = get_the_date( $date_format, $ticker_post );
						}

						$text = '<span class="awb-news-ticker-title-date">' . $date_text . '</span>' . $text;
					}

					$html .= '<div class="awb-news-ticker-item' . $additional_item_class . '">';
					$html .= '<a class="awb-news-ticker-link" href="' . get_the_permalink( $ticker_post ) . '"' . $link_target . '>' . $text . '</a>';
					$html .= '</div>';

					$is_last_item = ( ( $displayed_posts_num - 1 ) === $index ? true : false );
					if ( $display_separator && ! $is_last_item ) {
						$html .= '<div class="awb-news-ticker-item-separator">' . $separator . '</div>';
					}
				}

				return $html;
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
					// We need dynamic defaults for taxonomies.
					if ( isset( $args['pull_by'] ) ) {
						$defaults[ 'dynamic_tax_include_' . $args['pull_by'] ] = '';
						$defaults[ 'dynamic_tax_exclude_' . $args['pull_by'] ] = '';
					}
					$args = FusionBuilder::set_shortcode_defaults( $defaults, $args, 'fusion_news_ticker' );
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$query_args = $this->get_ticker_items_query_args( $args );
				echo wp_json_encode( $this->get_ticker_items_html( $query_args, $args ) );
				wp_die();
			}

			/**
			 * Get the carousel buttons HTML if necessary.
			 *
			 * @return string
			 */
			private function get_carousel_buttons_if_necessary() {
				$html = '';
				if ( 'carousel' === $this->args['ticker_type'] ) {
					$additional_btn_classes = '';
					if ( 'border' === $this->args['carousel_arrows_style'] ) {
						$additional_btn_classes = ' awb-news-ticker-btn-border';
					}

					$previous_aria_label = esc_attr__( 'Previous', 'fusion-builder' );
					$next_aria_label     = esc_attr__( 'Next', 'fusion-builder' );

					$html .= '<div class="awb-news-ticker-items-buttons">';
					$html .= '<div class="awb-news-ticker-btn-wrapper"><button class="awb-news-ticker-prev-btn' . $additional_btn_classes . '" aria-label="' . $previous_aria_label . '"><span class="awb-news-ticker-btn-arrow">&#xf104;</span></button></div>';
					$html .= '<div class="awb-news-ticker-btn-wrapper"><button class="awb-news-ticker-next-btn' . $additional_btn_classes . '" aria-label="' . $next_aria_label . '"><span class="awb-news-ticker-btn-arrow">&#xf105;</span></button></div>';
					$html .= '</div>';

					$html .= '<div class="awb-news-ticker-carousel-indicator"></div>';
				}

				return $html;
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_extras() {
				return [
					'is_rtl' => is_rtl(),
				];
			}

			/**
			 * Load base CSS.
			 *
			 * @since 3.5
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/news-ticker.min.css' );
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @since 3.5
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-news-ticker',
					FusionBuilder::$js_folder_url . '/general/fusion-news-ticker.js',
					FusionBuilder::$js_folder_path . '/general/fusion-news-ticker.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);
			}
		}
	}

	new Fusion_News_Ticker();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.5
 */
function fusion_news_ticker_map() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			// Class reference.
			'Fusion_News_Ticker',
			[
				'name'      => esc_attr__( 'News Ticker', 'fusion-builder' ),
				'shortcode' => 'fusion_news_ticker',
				'icon'      => 'fusiona-af-text',
				'params'    => fusion_news_ticker_get_param_settings(),
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_news_ticker_posts',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_wp_loaded', 'fusion_news_ticker_map' );

/**
 * Get the shortcode param settings.
 *
 * @return array
 */
function fusion_news_ticker_get_param_settings() {
	$builder_status       = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
	$php_date_format_link = '<a href="' . esc_url( 'https://www.php.net/manual/en/datetime.format.php' ) . '">' . esc_html__( 'link', 'fusion-builder' ) . '</a>';
	$post_types           = $builder_status ? awb_get_post_types() : [];

	$param = [];

	$param[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'Ticker Title', 'fusion-builder' ),
		'description' => esc_attr__( 'Set the ticker title.', 'fusion-builder' ),
		'param_name'  => 'ticker_title',
	];

	$param[] = [
		'type'        => 'select',
		'heading'     => esc_attr__( 'Posts Type', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the type of posts displayed in the ticker.', 'fusion-builder' ),
		'param_name'  => 'post_type',
		'default'     => 'post',
		'value'       => $post_types,
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
	];

	$param = array_merge( $param, fusion_news_ticker_get_taxonomies_settings() );

	$param = array_merge( $param, fusion_news_ticker_get_order_by_settings() );

	$param[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Number Of Posts', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the maximum number of posts to be displayed in the ticker.', 'fusion-builder' ),
		'param_name'  => 'posts_number',
		'value'       => '7',
		'min'         => '3',
		'max'         => '15',
		'step'        => '1',
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
	];

	$param[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Show Date', 'fusion-builder' ),
		'description' => esc_attr__( 'Select whether or not to show the date before the post.', 'fusion-builder' ),
		'param_name'  => 'show_date',
		'default'     => 'no',
		'value'       => [
			'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
			'no'  => esc_attr__( 'No', 'fusion-builder' ),
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
	];

	$param[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'Date Format', 'fusion-builder' ),
		/* translators: %s: a link. */
		'description' => sprintf( esc_attr__( 'Select the date format, including the separator. By default "M j: ". You can find a list of date format placeholders here: %s.', 'fusion-builder' ), $php_date_format_link ),
		'param_name'  => 'date_format',
		'value'       => '',
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
		'dependency'  => [
			[
				'element'  => 'show_date',
				'value'    => 'yes',
				'operator' => '==',
			],
		],
	];

	$param[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Ticker Type', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the ticker type to display posts.', 'fusion-builder' ),
		'param_name'  => 'ticker_type',
		'default'     => 'marquee',
		'value'       => [
			'marquee'  => esc_attr__( 'Running Ticker', 'fusion-builder' ),
			'carousel' => esc_attr__( 'One At A Time', 'fusion-builder' ),
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
	];

	$param[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Ticker Speed', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the ticker speed, in pixels per second.', 'fusion-builder' ),
		'param_name'  => 'ticker_speed',
		'value'       => '75',
		'min'         => '50',
		'max'         => '150',
		'step'        => '1',
		'dependency'  => [
			[
				'element'  => 'ticker_type',
				'value'    => 'marquee',
				'operator' => '==',
			],
		],
	];

	$param[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Distance Between Posts', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the distance between posts.', 'fusion-builder' ),
		'param_name'  => 'posts_distance',
		'value'       => '50',
		'min'         => '25',
		'max'         => '200',
		'step'        => '1',
		'dependency'  => [
			[
				'element'  => 'ticker_type',
				'value'    => 'marquee',
				'operator' => '==',
			],
		],
	];

	$param[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'Separator', 'fusion-builder' ),
		'description' => esc_attr__( 'Enter the separator text between the posts.', 'fusion-builder' ),
		'param_name'  => 'separator',
		'value'       => '',
		'dependency'  => [
			[
				'element'  => 'ticker_type',
				'value'    => 'marquee',
				'operator' => '==',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
	];

	$param[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Display Duration', 'fusion-builder' ),
		'description' => esc_attr__( 'Select how much time a post should be displayed, before switching to the next post. In seconds.', 'fusion-builder' ),
		'param_name'  => 'carousel_display_time',
		'value'       => '6',
		'min'         => '0.1',
		'max'         => '20',
		'step'        => '0.1',
		'dependency'  => [
			[
				'element'  => 'ticker_type',
				'value'    => 'carousel',
				'operator' => '==',
			],
		],
	];

	$param[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
		'description' => esc_html__( 'Controls how the link will open.', 'fusion-builder' ),
		'param_name'  => 'link_target',
		'value'       => [
			'_self'  => esc_html__( 'Same Window/Tab', 'fusion-builder' ),
			'_blank' => esc_html__( 'New Window/Tab', 'fusion-builder' ),
		],
		'default'     => '_self',
	];

	$param[] = [
		'type'        => 'checkbox_button_set',
		'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
		'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
		'param_name'  => 'hide_on_mobile',
		'value'       => fusion_builder_visibility_options( 'full' ),
		'default'     => fusion_builder_default_visibility( 'array' ),
	];

	$param[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
		'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
		'param_name'  => 'class',
		'value'       => '',
	];

	$param[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
		'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
		'param_name'  => 'id',
		'value'       => '',
	];

	// Design.

	$param[] = [
		'type'             => 'typography',
		'remove_from_atts' => true,
		'global'           => true,
		'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
		'description'      => esc_html__( 'Controls the typography.', 'fusion-builder' ),
		'param_name'       => 'ticker_typography',
		'group'            => esc_attr__( 'Design', 'fusion-builder' ),
		'choices'          => [
			'font-family'    => 'ticker_font',
			'font-size'      => 'font_size',
			'line-height'    => 'line_height',
			'letter-spacing' => 'letter_spacing',
			'text-transform' => 'text_transform',
		],
		'default'          => [
			'font-family'    => '',
			'variant'        => '',
			'font-size'      => '',
			'line-height'    => '2.5',
			'letter-spacing' => '',
			'text-transform' => '',
		],
	];

	$param[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'Height', 'fusion-builder' ),
		'description' => esc_attr__( 'Enter value including any valid CSS unit, ex: 20px. The height can also be changed only from typography line-height setting, and needs to be higher than computed value of line-height.', 'fusion-builder' ),
		'param_name'  => 'ticker_height',
		'value'       => '',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
	];

	$param[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Title Font Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the font color of the title.', 'fusion-builder' ),
		'param_name'  => 'title_font_color',
		'value'       => '',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
	];

	$param[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Title Background Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the background color of the title.', 'fusion-builder' ),
		'param_name'  => 'title_background_color',
		'value'       => '',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
	];

	$param[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Title Shape', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the title shape.', 'fusion-builder' ),
		'param_name'  => 'title_shape',
		'default'     => 'none',
		'value'       => [
			'none'     => esc_attr__( 'None', 'fusion-builder' ),
			'rounded'  => esc_attr__( 'Rounded', 'fusion-builder' ),
			'triangle' => esc_attr__( 'Triangle', 'fusion-builder' ),
		],
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
	];

	$param[] = [
		'type'             => 'dimension',
		'remove_from_atts' => true,
		'heading'          => esc_attr__( 'Title Padding', 'fusion-builder' ),
		'description'      => esc_attr__( 'Enter padding for the title.', 'fusion-builder' ),
		'param_name'       => 'title_padding',
		'group'            => esc_html__( 'Design', 'fusion-builder' ),
		'value'            => [
			'title_padding_left'  => '',
			'title_padding_right' => '',
		],
	];

	$param[] = [
		'type'          => 'colorpickeralpha',
		'heading'       => esc_attr__( 'Ticker Font Color', 'fusion-builder' ),
		'description'   => esc_attr__( 'Select the font color of the ticker.', 'fusion-builder' ),
		'param_name'    => 'ticker_font_color',
		'value'         => '',
		'group'         => esc_attr__( 'Design', 'fusion-builder' ),
		'states'        => [
			'hover' => [
				'label'      => __( 'Hover', 'fusion-builder' ),
				'param_name' => 'ticker_hover_font_color',
				'preview'    => [
					'selector' => '.awb-news-ticker-link',
					'type'     => 'class',
					'toggle'   => 'hover',
				],
			],
		],
		'connect-state' => [ 'ticker_background_color', 'ticker_indicators_color' ],
	];

	$param[] = [
		'type'          => 'colorpickeralpha',
		'heading'       => esc_attr__( 'Ticker Background Color', 'fusion-builder' ),
		'description'   => esc_attr__( 'Select the background color of the ticker.', 'fusion-builder' ),
		'param_name'    => 'ticker_background_color',
		'value'         => '',
		'group'         => esc_attr__( 'Design', 'fusion-builder' ),
		'states'        => [
			'hover' => [
				'label'      => __( 'Hover', 'fusion-builder' ),
				'param_name' => 'ticker_background_hover_color',
				'preview'    => [
					'selector' => '.awb-news-ticker',
					'type'     => 'class',
					'toggle'   => 'hover',
				],
			],
		],
		'connect-state' => [ 'ticker_font_color', 'ticker_indicators_color' ],
	];

	$param[] = [
		'type'          => 'colorpickeralpha',
		'heading'       => esc_attr__( 'Ticker Indicators Color', 'fusion-builder' ),
		'description'   => esc_attr__( 'Select the color of the ticker indicators and next/previous arrows.', 'fusion-builder' ),
		'param_name'    => 'ticker_indicators_color',
		'value'         => '',
		'group'         => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'    => [
			[
				'element'  => 'ticker_type',
				'value'    => 'carousel',
				'operator' => '==',
			],
		],
		'states'        => [
			'hover' => [
				'label'      => __( 'Hover', 'fusion-builder' ),
				'param_name' => 'ticker_indicators_hover_color',
				'preview'    => [
					'selector' => '.awb-news-ticker-next-btn, .awb-news-ticker-prev-btn',
					'type'     => 'class',
					'toggle'   => 'hover',
				],
			],
		],
		'connect-state' => [ 'ticker_background_color', 'ticker_font_color' ],
	];

	$param[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Progress Indicator Bar Height', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the height of the indicator bar.', 'fusion-builder' ),
		'param_name'  => 'carousel_bar_height',
		'value'       => '3',
		'min'         => '1',
		'max'         => '10',
		'step'        => '1',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'ticker_type',
				'value'    => 'carousel',
				'operator' => '==',
			],
		],
	];

	$param[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Previous/Next Arrows Style', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the style of the carousel arrows.', 'fusion-builder' ),
		'param_name'  => 'carousel_arrows_style',
		'default'     => 'none',
		'value'       => [
			'none'   => esc_attr__( 'Normal', 'fusion-builder' ),
			'border' => esc_attr__( 'Bordered', 'fusion-builder' ),
		],
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'ticker_type',
				'value'    => 'carousel',
				'operator' => '==',
			],
		],
	];

	$param[] = [
		'type'             => 'dimension',
		'remove_from_atts' => true,
		'heading'          => esc_attr__( 'Arrow Buttons Padding', 'fusion-builder' ),
		'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%. You can see the padding, by triggering bordering arrow style in the upper setting.', 'fusion-builder' ),
		'param_name'       => 'button_padding',
		'group'            => esc_attr__( 'Design', 'fusion-builder' ),
		'value'            => [
			'btn_padding_top'    => '',
			'btn_padding_right'  => '',
			'btn_padding_bottom' => '',
			'btn_padding_left'   => '',
		],
		'dependency'       => [
			[
				'element'  => 'ticker_type',
				'value'    => 'carousel',
				'operator' => '==',
			],
		],
	];

	$param[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'Arrows Border Radius', 'fusion-builder' ),
		'description' => esc_attr__( 'Enter value including any valid CSS unit, ex: 20px, or 50% to be perfectly rounded.', 'fusion-builder' ),
		'param_name'  => 'carousel_btn_border_radius',
		'value'       => '',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'ticker_type',
				'value'    => 'carousel',
				'operator' => '==',
			],
			[
				'element'  => 'carousel_arrows_style',
				'value'    => 'border',
				'operator' => '==',
			],
		],
	];

	$param[] = [
		'type'             => 'dimension',
		'remove_from_atts' => true,
		'heading'          => esc_attr__( 'Ticker Padding', 'fusion-builder' ),
		'description'      => esc_attr__( 'Controls the padding of the ticker.', 'fusion-builder' ),
		'param_name'       => 'ticker_padding',
		'group'            => esc_html__( 'Design', 'fusion-builder' ),
		'value'            => [
			'ticker_padding_left'  => '',
			'ticker_padding_right' => '',
		],
		'dependency'       => [
			[
				'element'  => 'ticker_type',
				'value'    => 'carousel',
				'operator' => '==',
			],
		],
	];

	$param['fusion_margin_placeholder'] = [
		'param_name' => 'margin',
		'heading'    => esc_attr__( 'Element Margin', 'fusion-builder' ),
		'value'      => [
			'margin_top'    => '',
			'margin_right'  => '',
			'margin_bottom' => '',
			'margin_left'   => '',
		],
	];

	$param['fusion_border_radius_placeholder'] = [
		'heading' => esc_attr__( 'Element Border Radius', 'fusion-builder' ),
	];

	$param['fusion_box_shadow_placeholder'] = [];

	$param['fusion_animation_placeholder'] = [
		'preview_selector' => '.awb-news-ticker',
	];

	return $param;
}

/**
 * Create the dynamic shortcode settings for categories and tags.
 *
 * @return array
 */
function fusion_news_ticker_get_taxonomies_settings() {
	$builder_status = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
	$post_types     = $builder_status ? awb_get_post_types() : [];
	$settings       = [];

	$taxonomy_map    = [];
	$post_taxonomies = [];

	if ( is_array( $post_types ) ) {
		foreach ( $post_types as $post_type => $post_type_label ) {
			$new_taxonomies             = get_object_taxonomies( $post_type, 'objects' );
			$taxonomy_map[ $post_type ] = [ 'all' ];
			foreach ( $new_taxonomies as $new_taxonomy ) {
				$post_taxonomies[ $new_taxonomy->name ] = $new_taxonomy;
				$taxonomy_map[ $post_type ][]           = $new_taxonomy->name;
			}
		}
	}
	$taxonomy_options = [
		'all' => esc_html__( 'All', 'fusion-builder' ),
	];
	foreach ( $post_taxonomies as $taxonomy ) {
		$taxonomy_options[ $taxonomy->name ] = ucwords( esc_html( $taxonomy->label ) );
	}

	$settings [] = [
		'type'        => 'select',
		'heading'     => esc_attr__( 'Pull Posts By', 'fusion-builder' ),
		'description' => esc_attr__( 'Choose the taxonomy to pull posts by.', 'fusion-builder' ),
		'param_name'  => 'pull_by',
		'default'     => 'all',
		'value'       => $taxonomy_options,
		'conditions'  => [
			'option' => 'post_type',
			'map'    => $taxonomy_map,
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
	];

	// Foreach taxonomy, add 2 options, one to include terms, and one to exclude.
	foreach ( $taxonomy_options as $taxonomy_name => $taxonomy_label ) {
		if ( 'all' === $taxonomy_name ) {
			continue;
		}

		$field_type  = 'ajax_select';
		$ajax        = 'fusion_search_query';
		$ajax_params = [
			'taxonomy' => $taxonomy_name,
		];
		$selection   = [];

		if ( 25 > wp_count_terms( $taxonomy_name ) ) {
			$ajax       = '';
			$field_type = 'multiple_select';
			$terms      = get_terms(
				[
					'taxonomy'   => $taxonomy_name,
					'hide_empty' => true,
				]
			);

			// All terms.
			foreach ( $terms as $term ) {
				$selection[ $term->term_id ] = $term->name;
			}
		}

		$settings [] = [
			'type'        => $field_type,
			/* translators: %s - a taxonomy name. */
			'heading'     => sprintf( esc_html__( 'Include %s', 'fusion-builder' ), $taxonomy_label ),
			'description' => esc_html__( 'Select the taxonomies to include, or leave blank for all. If the taxonomy is hierarchical, it will also include posts within children taxonomy.', 'fusion-builder' ),
			'placeholder' => ucwords( $taxonomy_label ),
			'param_name'  => 'dynamic_tax_include_' . $taxonomy_name,
			'value'       => $selection,
			'default'     => '',
			'ajax'        => $ajax,
			'ajax_params' => $ajax_params,
			'dependency'  => [
				[
					'element'  => 'pull_by',
					'value'    => $taxonomy_name,
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_news_ticker_posts',
				'ajax'     => true,
			],
		];

		$settings [] = [
			'type'        => $field_type,
			/* translators: %s - a taxonomy name. */
			'heading'     => sprintf( esc_html__( 'Exclude %s', 'fusion-builder' ), $taxonomy_label ),
			'description' => esc_html__( 'Select the taxonomies to exclude, or leave blank for none. If the taxonomy is hierarchical, it will also exclude posts within children taxonomy.', 'fusion-builder' ),
			'placeholder' => ucwords( $taxonomy_label ),
			'param_name'  => 'dynamic_tax_exclude_' . $taxonomy_name,
			'value'       => $selection,
			'default'     => '',
			'ajax'        => $ajax,
			'ajax_params' => $ajax_params,
			'dependency'  => [
				[
					'element'  => 'pull_by',
					'value'    => $taxonomy_name,
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_news_ticker_posts',
				'ajax'     => true,
			],
		];
	}

	return $settings;
}

/**
 * Get the orderby settings.
 *
 * @since 3.5
 * @return array
 */
function fusion_news_ticker_get_order_by_settings() {
	$settings       = [];
	$builder_status = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
	$post_types     = $builder_status ? awb_get_post_types() : [];

	$normal_posts_orderby      = [
		'date'          => esc_attr__( 'Date', 'fusion-builder' ),
		'title'         => esc_attr__( 'Post Title', 'fusion-builder' ),
		'name'          => esc_attr__( 'Post Slug', 'fusion-builder' ),
		'author'        => esc_attr__( 'Author', 'fusion-builder' ),
		'comment_count' => esc_attr__( 'Number of Comments', 'fusion-builder' ),
		'modified'      => esc_attr__( 'Last Modified', 'fusion-builder' ),
		'rand'          => esc_attr__( 'Random', 'fusion-builder' ),
	];
	$normal_posts_orderby_keys = array_keys( $normal_posts_orderby );

	$additional_event_posts_orderby      = [
		'upcoming_events' => esc_attr__( 'Upcoming Events', 'fusion-builder' ),
	];
	$additional_event_posts_orderby_keys = array_keys( $additional_event_posts_orderby );

	$conditions_map = [];
	foreach ( $post_types as $post_type => $post_type_label ) {
		if ( 'tribe_events' === $post_type ) {
			$conditions_map[ $post_type ] = array_merge( $additional_event_posts_orderby_keys, $normal_posts_orderby_keys );
		} else {
			$conditions_map[ $post_type ] = $normal_posts_orderby_keys;
		}
	}

	$settings[] = [
		'type'        => 'select',
		'heading'     => esc_attr__( 'Order By', 'fusion-builder' ),
		'description' => esc_attr__( 'Defines how posts should be ordered. Note that custom post types like "Events", can have additionally ordering choices.', 'fusion-builder' ),
		'param_name'  => 'orderby',
		'default'     => 'date',
		'conditions'  => [
			'option' => 'post_type',
			'map'    => $conditions_map,
		],
		'value'       => array_merge( $additional_event_posts_orderby, $normal_posts_orderby ),
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
	];

	$settings[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Order', 'fusion-builder' ),
		'description' => esc_attr__( 'Define the sorting order of posts.', 'fusion-builder' ),
		'param_name'  => 'order',
		'default'     => 'DESC',
		'value'       => [
			'DESC' => esc_attr__( 'Descending', 'fusion-builder' ),
			'ASC'  => esc_attr__( 'Ascending', 'fusion-builder' ),
		],
		'dependency'  => [
			[
				'element'  => 'orderby',
				'value'    => 'rand',
				'operator' => '!=',
			],
			[
				'element'  => 'orderby',
				'value'    => 'upcoming_events',
				'operator' => '!=',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_news_ticker_posts',
			'ajax'     => true,
		],
	];

	return $settings;
}
