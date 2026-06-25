<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_post_cards' ) ) {

	if ( fusion_is_element_enabled( 'fusion_tb_post_card_archives' ) ) {

		if ( ! class_exists( 'FusionTB_Post_Card_Archives' ) ) {
			/**
			 * Shortcode class.
			 *
			 * @since 3.3
			 */
			class FusionTB_Post_Card_Archives extends Fusion_Component {

				/**
				 * The element counter.
				 *
				 * @access private
				 * @since 3.11.10
				 * @var int
				 */
				private $element_counter = 1;

				/**
				 * Flag to indicate are we on archive page.
				 *
				 * @access protected
				 * @since 3.3
				 * @var bool
				 */
				protected $is_archive = false;

				/**
				 * Holds the main vars of the origina $wp_query.
				 *
				 * @access protected
				 * @since 3.11.10
				 * @var array
				 */
				protected $original_query_vars = [];

				/**
				 * Constructor.
				 *
				 * @access public
				 * @since 3.3
				 */
				public function __construct() {
					parent::__construct( 'fusion_tb_post_card_archives' );

					// Ajax mechanism for query related part.
					add_action( "wp_ajax_get_{$this->shortcode_handle}", [ $this, 'ajax_query' ] );

					add_filter( 'fusion_tb_component_check', [ $this, 'component_check' ] );

					add_action( 'pre_get_posts', [ $this, 'alter_search_loop' ] );

					add_filter( "fusion_attr_{$this->shortcode_handle}", [ $this, 'attr' ] );

					add_action( 'wp', [ $this, 'set_is_archive' ] );
				}

				/**
				 * Check if we're on archive page.
				 * Needs done early, before global query is changed.
				 *
				 * @access public
				 * @since 3.3
				 * @return void
				 */
				public function set_is_archive() {
					$this->is_archive = is_search() || is_archive() || isset( $_GET['awb-studio-content'] ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				/**
				 * Check if component should render
				 *
				 * @access public
				 * @since 3.3
				 * @return boolean
				 */
				public function should_render() {
					return $this->is_archive;
				}

				/**
				 * Checks and returns post type for archives component.
				 *
				 * @since 3.3
				 * @access public
				 * @param  array $defaults current params array.
				 * @return array $defaults Updated params array.
				 */
				public function archives_type( $defaults ) {
					$defaults = Fusion_Template_Builder()->archives_type( $defaults );

					// Check for taxonomy type.
					return Fusion_Template_Builder()->taxonomy_type( $defaults );
				}

				/**
				 * Gets the query data.
				 *
				 * @static
				 * @access public
				 * @since 3.3
				 * @param array $defaults An array of defaults.
				 * @return void
				 */
				public function ajax_query( $defaults ) {
					check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

					$type = 'archives';
					if ( isset( $_POST['fusion_meta'] ) && isset( $_POST['post_id'] ) ) {
						$meta = fusion_string_to_array( $_POST['fusion_meta'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						$type = ! isset( $meta['_fusion']['dynamic_content_preview_type'] ) || in_array( $meta['_fusion']['dynamic_content_preview_type'], [ 'search', 'archives' ], true ) ? $meta['_fusion']['dynamic_content_preview_type'] : $type;
					}

					add_filter( 'fusion_post_cards_shortcode_query_args', [ $this, 'archives_type' ] );
					do_action( 'wp_ajax_get_fusion_post_cards', $defaults );
				}

				/**
				 * Gets the default values.
				 *
				 * @static
				 * @access public
				 * @since 3.3
				 * @return array
				 */
				public static function get_element_defaults() {
					global $post;

					$defaults = FusionSC_PostCards::get_element_defaults();

					$defaults['post_type'] = get_post_type( $post );

					return $defaults;
				}

				/**
				 * Used to set any other variables for use on front-end editor template.
				 *
				 * @static
				 * @access public
				 * @since 3.3
				 * @return array
				 */
				public static function get_element_extras() {
					return FusionSC_PostCards::get_element_extras();
				}

				/**
				 * Renders fusion post cards shortcode
				 *
				 * @access public
				 * @since 3.3
				 * @return string
				 */
				public function render_card() {
					global $shortcode_tags;

					$this->args['post_card_archives'] = true;

					if ( 'terms' === $this->args['source'] ) {
						$queried                          = get_queried_object();
						$this->args['post_card_archives'] = false;
						if ( 'WP_Term' === get_class( $queried ) ) {
							$terms = get_terms(
								[
									'taxonomy'   => $queried->taxonomy,
									'hide_empty' => false,
									'parent'     => $queried->term_id,
									'fields'     => 'ids',
									'number'     => max( (int) $this->args['number_posts'], 0 ),
								]
							);

							if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								$this->args['parent_term']                          = $queried->term_id;
								$this->args[ 'include_term_' . $queried->taxonomy ] = implode( ',', $terms );
								$this->args = wp_parse_args(
									[
										'terms_by' => $queried->taxonomy,
									],
									$this->args
								);
							}
						}
					}

					return call_user_func( $shortcode_tags['fusion_post_cards'], $this->args, '', 'fusion_post_cards' );
				}

				/**
				 * Filters the current query
				 *
				 * @access public
				 * @since 3.3
				 * @param array $query The query.
				 * @return array
				 */
				public function fusion_post_cards_shortcode_query_override( $query ) {
					global $wp_query;

					// If post card display = terms then don't override the query.
					if ( 'terms' === $this->args['source'] ) {
						return $query;
					}

					// If there is several Post Card Archives elements.
					if ( ! empty( $wp_query->get( 'awb_pc_archives' ) ) && 0 < $wp_query->found_posts ) {
						if ( ! empty( $this->original_query_vars ) ) {

							// Reset the main query to the original posts.
							$wp_query->posts = $this->original_query_vars['posts'];
						} else {

							// On first element, make sure we store the original vars.
							$this->original_query_vars = [
								'posts'         => $wp_query->posts,
								'found_posts'   => $wp_query->found_posts,
								'max_num_pages' => $wp_query->max_num_pages,
							];
						}

						$posts_per_page       = -1 === (int) $this->args['number_posts'] ? $this->original_query_vars['found_posts'] : $this->args['number_posts'];
						$wp_query->posts      = (int) $this->args['offset'] < count( $wp_query->posts ) ? array_slice( $wp_query->posts, (int) $this->args['offset'], $posts_per_page ) : [];
						$wp_query->post_count = count( $wp_query->posts );

						// Last Post Card Archives element. Reset data, so that pagination works.
						if ( $wp_query->get( 'awb_pc_archives' ) === $this->element_counter ) {
							$wp_query->found_posts   = $this->original_query_vars['found_posts'];
							$wp_query->max_num_pages = ceil( $wp_query->found_posts / ( (int) $this->args['offset'] + $posts_per_page ) );

							$this->original_query_vars = [];
						} else {

							// Any but last Post Card Archoves element. Set vars, so that only posts that we want will be rendered and that pagination is skipped.
							$wp_query->found_posts   = count( $wp_query->posts );
							$wp_query->max_num_pages = count( $wp_query->posts );
						}

						$wp_query->rewind_posts();
					}

					return $wp_query;
				}

				/**
				 * Render the shortcode
				 *
				 * @access public
				 * @since 3.3
				 * @param  array  $args    Shortcode parameters.
				 * @param  string $content Content between shortcode.
				 * @return string          HTML output.
				 */
				public function render( $args, $content = '' ) {
					global $post, $wp_query;

					$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, $this->shortcode_handle );

					$option = isset( $post->ID ) ? fusion_get_page_option( 'dynamic_content_preview_type', $post->ID ) : '';
					$option = '' !== $option ? $option : 'archives';
					$html   = '<div ' . FusionBuilder::attributes( $this->shortcode_handle ) . ' >';

					// Handle empty results.
					if ( ! fusion_is_preview_frame() && ! $post ) {
						$html .= apply_filters( 'fusion_shortcode_content', '<h2 class="fusion-nothing-found">' . $content . '</h2>', $this->shortcode_handle, $args );

					} elseif ( fusion_is_preview_frame() && ! in_array( $option, [ 'search', 'archives', 'term' ], true ) ) {

						// Invalid source selection, return empty so view placeholder shows.
						$this->element_counter++;
						return '';

					} elseif ( ! fusion_is_preview_frame() && ! isset( $_GET['awb-studio-content'] ) && $this->should_render() ) { // phpcs:ignore WordPress.Security.NonceVerification

						// Pass main query to Post Card element.
						add_filter( 'fusion_post_cards_shortcode_query_override', [ $this, 'fusion_post_cards_shortcode_query_override' ] );
						$cards = $this->render_card();
						if ( empty( $cards ) && 'terms' !== $this->args['source'] ) {
							$html .= apply_filters( 'fusion_shortcode_content', '<h2 class="fusion-nothing-found">' . $content . '</h2>', $this->shortcode_handle, $args );
						} else {
							$html .= $cards;
						}
						remove_filter( 'fusion_post_cards_shortcode_query_override', [ $this, 'fusion_post_cards_shortcode_query_override' ] );
					} elseif ( fusion_is_preview_frame() || isset( $_GET['awb-studio-content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						add_filter( 'fusion_post_cards_shortcode_query_args', [ $this, 'archives_type' ] );
						$cards = $this->render_card();
						remove_filter( 'fusion_post_cards_shortcode_query_args', [ $this, 'archives_type' ] );

						// No cards, mean none of post type, display placeholder message.
						if ( empty( $cards ) && current_user_can( 'manage_options' ) ) {
							$this->element_counter++;
							return '<div class="fusion-builder-placeholder">' . esc_html__( 'No posts found.', 'fusion-builder' ) . '</div>';
						}

						// We do have cards, add to markup.
						$html .= $cards;
					}

					$html .= '</div>';

					$this->on_render();

					$this->element_counter++;

					return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
				}

				/**
				 * Apply post per page on search pages.
				 *
				 * @access public
				 * @since 3.3
				 * @return array The attribute array
				 */
				public function attr() {
					$attr = [
						'class' => 'fusion-post-cards-archives-tb',
					];

					$attr['data-infinite-post-class'] = $this->args['post_type'];

					return $attr;
				}

				/**
				 * Apply post per page on search pages.
				 *
				 * @access public
				 * @since 3.3
				 * @param  object $query The WP_Query object.
				 * @return  void
				 */
				public function alter_search_loop( $query ) {
					if ( ! is_admin() && $query->is_main_query() && ( $query->is_search() || $query->is_archive() ) ) {
						$search_override = Fusion_Template_Builder::get_instance()->get_search_override( $query );

						if ( is_object( $search_override ) ) {
							$content                = $this->get_shortcodes_from_global_elements( $search_override->post_content );
							$has_archives_component = $search_override && has_shortcode( $content, 'fusion_tb_post_card_archives' );

							if ( $has_archives_component ) {
								$pattern = get_shortcode_regex( [ 'fusion_tb_post_card_archives' ] );

								if ( preg_match_all( '/' . $pattern . '/s', $content, $matches )
									&& array_key_exists( 2, $matches )
									&& in_array( 'fusion_tb_post_card_archives', $matches[2], true ) ) {

									$number_of_post_card_archives = count( $matches[3] );

									$search_atts  = shortcode_parse_atts( $matches[3][ $number_of_post_card_archives - 1 ] );
									$number_posts = ( isset( $_GET['product_count'] ) ) ? (int) $_GET['product_count'] : $search_atts['number_posts']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

									if ( ! empty( $search_atts['offset'] ) ) {

										if ( 1 < $number_of_post_card_archives ) {

											// We have more than on Post Card Archives element. Add offset to posts per page and don't set the offset.
											$number_posts = (int) $search_atts['offset'] + (int) $number_posts;

											$query->set( 'awb_pc_archives', $number_of_post_card_archives );
										} else {
											$query->set( 'offset', $search_atts['offset'] );
										}
									}

									if ( $number_posts ) {
										$query->set( 'posts_per_page', $number_posts );
									}

									$query->set( 'paged', ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1 );
								}
							}
						}
					}
				}

				/**
				 * Get shortcodes from global elements.
				 *
				 * @access private
				 * @since 3.11
				 * @param  string $content TThe post content to be checked.
				 * @return string The post content string where global elements have been replaced.
				 */
				private function get_shortcodes_from_global_elements( $content ) {
					$pattern = '\[(\[?)(fusion_global)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';

					// Get all globals and replace them with their contents.
					if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'fusion_global', $matches[2], true ) ) {

						// Loop through matches.
						foreach ( $matches[0] as $key => $value ) {
							$result = shortcode_parse_atts( $matches[3][ $key ] );

							// Get content of the global element and replace the global shortcode with its content.
							if ( isset( $result['id'] ) && ! empty( $result['id'] ) ) {
								$result['id'] = apply_filters( 'wpml_object_id', $result['id'], 'fusion_element', true );
								$post         = get_post( $result['id'] );
								$content      = str_replace( $matches[0][ $key ], $post->post_content, $content );
							}
						}
					}

					return $content;
				}
			}
		}

		new FusionTB_Post_Card_Archives();
	}

	/**
	 * Map shortcode to Avada Builder
	 *
	 * @since 3.3
	 */
	function fusion_component_post_card_archives() {
		$fusion_settings = awb_get_fusion_settings();

		$editing           = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
		$layouts_permalink = [];
		$layouts           = [
			'0' => esc_attr__( 'None', 'fusion-builder' ),
		];

		// If builder get custom layout options.
		if ( $editing && function_exists( 'Fusion_Builder_Library' ) ) {
			// In case taxonomy is not registered yet, register.
			Fusion_Builder_Library()->register_layouts();

			$post_cards = get_posts(
				[
					'post_type'      => 'fusion_element',
					'posts_per_page' => '-1',
					'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery
						[
							'taxonomy' => 'element_category',
							'field'    => 'slug',
							'terms'    => 'post_cards',
						],
					],
				]
			);

			if ( $post_cards ) {
				foreach ( $post_cards as $post_card ) {
					$layouts[ $post_card->ID ]           = $post_card->post_title;
					$layouts_permalink[ $post_card->ID ] = $post_card->guid;
				}
			}
		}

		$library_link = '<a href="' . admin_url( 'admin.php?page=avada-library' ) . '" target="_blank">' . esc_attr__( 'Avada Library', 'fusion-builder' ) . '</a>';

		// Navigation section.
		$arrows_dependency = [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'masonry',
				'operator' => '!=',
			],
			[
				'element'  => 'show_nav',
				'value'    => 'no',
				'operator' => '!=',
			],
			[
				'element'  => 'show_nav',
				'value'    => 'dots',
				'operator' => '!=',
			],
		];
		$dots_dependency   = [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'masonry',
				'operator' => '!=',
			],
			[
				'element'  => 'show_nav',
				'value'    => 'no',
				'operator' => '!=',
			],
			[
				'element'  => 'show_nav',
				'value'    => 'yes',
				'operator' => '!=',
			],
		];

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionTB_Post_Card_Archives',
				[
					'name'         => esc_attr__( 'Post Card Archives', 'fusion-builder' ),
					'shortcode'    => 'fusion_tb_post_card_archives',
					'icon'         => 'fusiona-product-grid-and-archives',
					'subparam_map' => [
						'separator_width' => 'dimensions_width',
					],
					'component'    => true,
					'templates'    => [ 'content' ],
					'params'       => [
						[
							'type'        => 'select',
							'heading'     => esc_attr__( 'Post Card', 'fusion-builder' ),
							'group'       => esc_attr__( 'General', 'fusion-builder' ),

							/* translators: The Avada Library link. */
							'description' => sprintf( __( 'Select a saved Post Card design to use. Create new or edit existing Post Cards in the %s.', 'fusion-builder' ), $library_link ),
							'param_name'  => 'post_card',
							'default'     => '0',
							'value'       => $layouts,
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_tb_post_card_archives',
								'ajax'     => true,
							],
							'quick_edit'  => [
								'label' => esc_html__( 'Edit Post Card', 'fusion-builder' ),
								'type'  => 'post_card',
								'items' => $layouts_permalink,
							],
						],
						[
							'type'        => 'select',
							'heading'     => esc_attr__( 'Post Card List View', 'fusion-builder' ),
							'group'       => esc_attr__( 'General', 'fusion-builder' ),

							/* translators: The Avada Library link. */
							'description' => sprintf( __( 'This post card will be used in the list view which can be triggered with the sorting element (WooCommerce). Post cards can be created in the %s.', 'fusion-builder' ), $library_link ),
							'param_name'  => 'post_card_list_view',
							'default'     => '0',
							'value'       => $layouts,
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_tb_post_card_archives',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Post Cards Display', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose what to display on post cards page.', 'fusion-builder' ),
							'param_name'  => 'source',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_tb_post_card_archives',
								'ajax'     => true,
							],
							'value'       => [
								'posts' => esc_attr__( 'Posts', 'fusion-builder' ),
								'terms' => esc_attr__( 'Terms', 'fusion-builder' ),
							],
							'default'     => 'posts',
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Posts Per Page / Per Element', 'fusion-builder' ),
							'description' => sprintf(
								/* translators: %1$s: Portfolio Link. %2$s: Products Link. */
								esc_attr__( 'Select number of posts per page, or per Post Card Archives element if there are several elements within one layout section. Set to -1 to display all. Set to 0 to use the post type default number of posts. For %1$s and %2$s this comes from the global options. For all others Settings > Reading.', 'fusion-builder' ),
								'<a href="' . admin_url( 'themes.php?page=avada_options#portfolio_archive_items' ) . '" target="_blank">' . esc_attr__( 'portfolio', 'fusion-builder' ) . '</a>',
								'<a href="' . admin_url( 'themes.php?page=avada_options#woo_items' ) . '" target="_blank">' . esc_attr__( 'products', 'fusion-builder' ) . '</a>'
							),
							'param_name'  => 'number_posts',
							'value'       => 0,
							'min'         => '-1',
							'max'         => '50',
							'step'        => '1',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_tb_post_card_archives',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Posts Offset', 'fusion-builder' ),
							'description' => esc_attr__( 'The number of posts to skip. ex: 1.', 'fusion-builder' ),
							'param_name'  => 'offset',
							'value'       => '0',
							'min'         => '0',
							'max'         => '24',
							'step'        => '1',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_tb_post_card_archives',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Pagination Type', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the type of pagination.', 'fusion-builder' ),
							'param_name'  => 'scrolling',
							'default'     => 'pagination',
							'value'       => [
								'pagination'       => esc_html__( 'Pagination', 'fusion-builder' ),
								'infinite'         => esc_html__( 'Infinite Scroll', 'fusion-builder' ),
								'load_more_button' => esc_html__( 'Load More Button', 'fusion-builder' ),
							],
							'dependency'  => [
								[
									'element'  => 'source',
									'value'    => 'terms',
									'operator' => '!=',
								],
							],
						],
						[
							'type'         => 'tinymce',
							'heading'      => esc_attr__( 'Nothing Found Message', 'fusion-builder' ),
							'description'  => esc_attr__( 'Replacement text when no results are found.', 'fusion-builder' ),
							'param_name'   => 'element_content',
							'value'        => esc_html__( 'Nothing Found', 'fusion-builder' ),
							'placeholder'  => true,
							'dynamic_data' => true,
							'dependency'   => [
								[
									'element'  => 'source',
									'value'    => 'terms',
									'operator' => '!=',
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
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
							'description' => esc_attr__( 'Select how you want Post Cards to display.', 'fusion-builder' ),
							'param_name'  => 'layout',
							'value'       => [
								'grid'     => esc_attr__( 'Grid', 'fusion-builder' ),
								'carousel' => esc_attr__( 'Carousel', 'fusion-builder' ),
								'slider'   => esc_attr__( 'Slider', 'fusion-builder' ),
								'masonry'  => esc_attr__( 'Masonry', 'fusion-builder' ),
							],
							'default'     => 'grid',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Post Card Alignment', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the Post Cards alignment within rows.', 'fusion-builder' ),
							'param_name'  => 'flex_align_items',
							'back_icons'  => true,
							'grid_layout' => true,
							'value'       => [
								'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
								'center'     => esc_attr__( 'Center', 'fusion-builder' ),
								'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
								'stretch'    => esc_attr__( 'Stretch', 'fusion-builder' ),
							],
							'icons'       => [
								'flex-start' => '<span class="fusiona-align-top-columns"></span>',
								'center'     => '<span class="fusiona-align-center-columns"></span>',
								'flex-end'   => '<span class="fusiona-align-bottom-columns"></span>',
								'stretch'    => '<span class="fusiona-full-height"></span>',
							],
							'default'     => 'flex-start',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'masonry',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'slider',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
							'description' => esc_attr__( 'Set the number of columns per row.', 'fusion-builder' ),
							'param_name'  => 'columns',
							'value'       => '4',
							'min'         => '0',
							'max'         => '6',
							'step'        => '1',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'responsive'  => [
								'state'        => 'large',
								'values'       => [
									'small'  => '0',
									'medium' => '0',
								],
								'descriptions' => [
									'small'  => esc_attr__( 'Set the number of columns per row. Leave at 0 for automatic column breaking', 'fusion-builder' ),
									'medium' => esc_attr__( 'Set the number of columns per row. Leave at 0 for automatic column breaking', 'fusion-builder' ),
								],
							],
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'slider',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
							'description' => esc_attr__( "Insert the amount of horizontal spacing between items without 'px'. ex: 40.", 'fusion-builder' ),
							'param_name'  => 'column_spacing',
							'value'       => '40',
							'min'         => '1',
							'max'         => '300',
							'step'        => '1',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => [
								[
									'element'  => 'columns',
									'value'    => '1',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'slider',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Row Spacing', 'fusion-builder' ),
							'description' => esc_attr__( "Insert the amount of vertical spacing between items without 'px'. ex: 40.", 'fusion-builder' ),
							'param_name'  => 'row_spacing',
							'value'       => '40',
							'min'         => '1',
							'max'         => '300',
							'step'        => '1',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'carousel',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'slider',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'select',
							'heading'     => esc_attr__( 'Separator', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose the horizontal separator line style. This will only be used on single column grids or list view.', 'fusion-builder' ),
							'param_name'  => 'separator_style_type',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_post_card_separator',
								'ajax'     => false,
							],
							'value'       => [
								'none'          => esc_attr__( 'None', 'fusion-builder' ),
								'single solid'  => esc_attr__( 'Single Border Solid', 'fusion-builder' ),
								'double solid'  => esc_attr__( 'Double Border Solid', 'fusion-builder' ),
								'single|dashed' => esc_attr__( 'Single Border Dashed', 'fusion-builder' ),
								'double|dashed' => esc_attr__( 'Double Border Dashed', 'fusion-builder' ),
								'single|dotted' => esc_attr__( 'Single Border Dotted', 'fusion-builder' ),
								'double|dotted' => esc_attr__( 'Double Border Dotted', 'fusion-builder' ),
							],
							'default'     => 'none',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Separator Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the separator color.', 'fusion-builder' ),
							'param_name'  => 'separator_sep_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'sep_color' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_post_card_separator',
								'ajax'     => false,
							],
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '==',
								],
								[
									'element'  => 'separator_style_type',
									'value'    => 'none',
									'operator' => '!=',
								],
							],
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Separator Width', 'fusion-builder' ),
							'param_name'       => 'dimensions_width',
							'value'            => [
								'separator_width' => '',
							],
							'description'      => esc_attr__( 'In pixels (px or %), ex: 1px, ex: 50%. Leave blank for full width.', 'fusion-builder' ),
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'         => [
								'function' => 'fusion_post_card_separator',
								'ajax'     => false,
							],
							'dependency'       => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '==',
								],
								[
									'element'  => 'separator_style_type',
									'value'    => 'none',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Separator Alignment', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the separator alignment; only works when a width is specified.', 'fusion-builder' ),
							'param_name'  => 'separator_alignment',
							'value'       => [
								'center' => esc_attr__( 'Center', 'fusion-builder' ),
								'left'   => esc_attr__( 'Left', 'fusion-builder' ),
								'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							],
							'default'     => 'center',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_post_card_separator',
								'ajax'     => false,
							],
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '==',
								],
								[
									'element'  => 'separator_style_type',
									'value'    => 'none',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Separator Border Size', 'fusion-builder' ),
							'param_name'  => 'separator_border_size',
							'value'       => '',
							'min'         => '0',
							'max'         => '50',
							'step'        => '1',
							'default'     => $fusion_settings->get( 'separator_border_size' ),
							'description' => esc_attr__( 'In pixels. ', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_post_card_separator',
								'ajax'     => false,
							],
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '==',
								],
								[
									'element'  => 'separator_style_type',
									'value'    => 'none',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Autoplay', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to autoplay the items.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'  => 'autoplay',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'masonry',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Loop', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to enable continuous loop mode.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'  => 'loop',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'yes',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'masonry',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Scroll Items', 'fusion-builder' ),
							'description' => esc_attr__( 'Insert the amount of items to scroll. Leave empty to scroll number of visible items.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'  => 'scroll_items',
							'min'         => '1',
							'max'         => '50',
							'step'        => '1',
							'value'       => '0',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'carousel',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Mouse Scroll', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to enable mouse drag control on the carousel. IMPORTANT: For easy draggability, when mouse scroll is activated, links will be disabled.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'  => 'mouse_scroll',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'masonry',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Mouse Pointer', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to enable mouse drag custom cursor.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'  => 'mouse_pointer',
							'value'       => [
								'default' => esc_attr__( 'Default', 'fusion-builder' ),
								'custom'  => esc_attr__( 'Custom', 'fusion-builder' ),
							],
							'default'     => 'default',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'masonry',
									'operator' => '!=',
								],
								[
									'element'  => 'mouse_scroll',
									'value'    => 'yes',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Cursor Color Mode', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose cursor color mode.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'  => 'cursor_color_mode',
							'value'       => [
								'auto'   => esc_attr__( 'Automatic', 'fusion-builder' ),
								'custom' => esc_attr__( 'Custom Color', 'fusion-builder' ),
							],
							'default'     => 'auto',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'masonry',
									'operator' => '!=',
								],
								[
									'element'  => 'mouse_scroll',
									'value'    => 'yes',
									'operator' => '==',
								],
								[
									'element'  => 'mouse_pointer',
									'value'    => 'custom',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Cursor Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the color of cursor.', 'fusion-builder' ),
							'param_name'  => 'cursor_color',
							'value'       => '',
							'default'     => '',
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'masonry',
									'operator' => '!=',
								],
								[
									'element'  => 'mouse_scroll',
									'value'    => 'yes',
									'operator' => '==',
								],
								[
									'element'  => 'mouse_pointer',
									'value'    => 'custom',
									'operator' => '==',
								],
								[
									'element'  => 'cursor_color_mode',
									'value'    => 'custom',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Navigation', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show navigation buttons on the carousel / slider.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'  => 'show_nav',
							'value'       => [
								'no'          => esc_attr__( 'None', 'fusion-builder' ),
								'yes'         => esc_attr__( 'Arrows', 'fusion-builder' ),
								'dots'        => esc_attr__( 'Dots', 'fusion-builder' ),
								'arrows_dots' => esc_attr__( 'Arrows & Dots', 'fusion-builder' ),
							],
							'default'     => 'yes',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'masonry',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'dimension',
							'heading'     => esc_attr__( 'Arrow Box Dimensions', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the width and height of the arrow box. Enter values including any valid CSS unit.', 'fusion-builder' ),
							'param_name'  => 'arrow_box',
							'value'       => [
								'arrow_box_width'  => '',
								'arrow_box_height' => '',
							],
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => $arrows_dependency,
						],
						[
							'type'        => 'textfield',
							'heading'     => esc_attr__( 'Arrow Icon Size', 'fusion-builder' ),
							'description' => esc_attr__( 'Set the arrow icon size. Enter value including any valid CSS unit, ex: 14px.', 'fusion-builder' ),
							'param_name'  => 'arrow_size',
							'value'       => '',
							'default'     => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => $arrows_dependency,
						],
						[
							'type'        => 'iconpicker',
							'heading'     => esc_attr__( 'Previous Icon', 'fusion-builder' ),
							'param_name'  => 'prev_icon',
							'value'       => '',
							'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => $arrows_dependency,
						],
						[
							'type'        => 'iconpicker',
							'heading'     => esc_attr__( 'Next Icon', 'fusion-builder' ),
							'param_name'  => 'next_icon',
							'value'       => '',
							'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => $arrows_dependency,
						],
						[
							'type'        => 'dimension',
							'heading'     => esc_attr__( 'Arrow Position', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the position of the arrow. Enter value including any valid CSS unit, ex: 14px.', 'fusion-builder' ),
							'param_name'  => 'arrow_position',
							'value'       => [
								'arrow_position_horizontal' => '',
								'arrow_position_vertical' => '',
							],
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => $arrows_dependency,
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Arrow Border Radius', 'fusion-builder' ),
							'description'      => __( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
							'param_name'       => 'arrow_border_radius',
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'value'            => [
								'arrow_border_radius_top_left'     => '',
								'arrow_border_radius_top_right'    => '',
								'arrow_border_radius_bottom_right' => '',
								'arrow_border_radius_bottom_left'  => '',
							],
							'dependency'       => array_merge( $arrows_dependency ),
						],
						[
							'type'             => 'subgroup',
							'heading'          => esc_html__( 'Arrows Styling', 'fusion-builder' ),
							'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
							'param_name'       => 'arrow_styling',
							'default'          => 'regular',
							'group'            => esc_html__( 'Design', 'fusion-builder' ),
							'remove_from_atts' => true,
							'value'            => [
								'regular' => esc_html__( 'Regular', 'fusion-builder' ),
								'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
							],
							'icons'            => [
								'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
								'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
							],
							'dependency'       => $arrows_dependency,
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Arrow Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the background color of arrow.', 'fusion-builder' ),
							'param_name'  => 'arrow_bgcolor',
							'value'       => '',
							'default'     => $fusion_settings->get( 'carousel_nav_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'subgroup'    => [
								'name' => 'arrow_styling',
								'tab'  => 'regular',
							],
							'dependency'  => $arrows_dependency,
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Arrow Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the color of arrow.', 'fusion-builder' ),
							'param_name'  => 'arrow_color',
							'value'       => '',
							'default'     => '#fff',
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'subgroup'    => [
								'name' => 'arrow_styling',
								'tab'  => 'regular',
							],
							'dependency'  => $arrows_dependency,
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Arrow Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the color of arrow.', 'fusion-builder' ),
							'param_name'  => 'arrow_hover_bgcolor',
							'value'       => '',
							'default'     => $fusion_settings->get( 'carousel_hover_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'subgroup'    => [
								'name' => 'arrow_styling',
								'tab'  => 'hover',
							],
							'dependency'  => $arrows_dependency,
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Arrow Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the color of arrow.', 'fusion-builder' ),
							'param_name'  => 'arrow_hover_color',
							'value'       => '',
							'default'     => '#fff',
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'subgroup'    => [
								'name' => 'arrow_styling',
								'tab'  => 'hover',
							],
							'dependency'  => $arrows_dependency,
						],

						// Dots section.
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Dots Position', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the position of the dots. Enter value including any valid CSS unit, ex: 14px.', 'fusion-builder' ),
							'param_name'  => 'dots_position',
							'value'       => [
								'above'  => esc_attr__( 'Above', 'fusion-builder' ),
								'top'    => esc_attr__( 'Top', 'fusion-builder' ),
								'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
								'below'  => esc_attr__( 'Below', 'fusion-builder' ),
							],
							'default'     => 'bottom',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => $dots_dependency,
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Dots Spacing', 'fusion-builder' ),
							'param_name'  => 'dots_spacing',
							'value'       => '4',
							'min'         => '0',
							'max'         => '100',
							'step'        => '1',
							'description' => esc_attr__( 'In pixels. ', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => $dots_dependency,
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Dots Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
							'param_name'       => 'dots_margin',
							'value'            => [
								'dots_margin_top'    => '',
								'dots_margin_bottom' => '',
							],
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'       => $dots_dependency,
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Dots Alignment', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the border style of the arrow.', 'fusion-builder' ),
							'param_name'  => 'dots_align',
							'value'       => [
								'left'   => esc_attr__( 'Left', 'fusion-builder' ),
								'center' => esc_attr__( 'Center', 'fusion-builder' ),
								'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							],
							'default'     => 'center',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'dependency'  => $dots_dependency,
						],
						[
							'type'             => 'subgroup',
							'heading'          => esc_html__( 'Dots Styling', 'fusion-builder' ),
							'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
							'param_name'       => 'dots_styling',
							'default'          => 'regular',
							'group'            => esc_html__( 'Design', 'fusion-builder' ),
							'remove_from_atts' => true,
							'value'            => [
								'regular' => esc_html__( 'Regular', 'fusion-builder' ),
								'hover'   => esc_html__( 'Active', 'fusion-builder' ),
							],
							'icons'            => [
								'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
								'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
							],
							'dependency'       => $dots_dependency,
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Dots Size', 'fusion-builder' ),
							'param_name'  => 'dots_size',
							'value'       => '8',
							'min'         => '0',
							'max'         => '100',
							'step'        => '1',
							'description' => esc_attr__( 'In pixels. ', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'subgroup'    => [
								'name' => 'dots_styling',
								'tab'  => 'regular',
							],
							'dependency'  => $dots_dependency,
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Dots Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the color of arrow.', 'fusion-builder' ),
							'param_name'  => 'dots_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'carousel_hover_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'subgroup'    => [
								'name' => 'dots_styling',
								'tab'  => 'regular',
							],
							'dependency'  => $dots_dependency,
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Dots Size', 'fusion-builder' ),
							'param_name'  => 'dots_active_size',
							'value'       => '8',
							'min'         => '0',
							'max'         => '100',
							'step'        => '1',
							'description' => esc_attr__( 'In pixels. ', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'subgroup'    => [
								'name' => 'dots_styling',
								'tab'  => 'hover',
							],
							'dependency'  => $dots_dependency,
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Dots Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the color of arrow.', 'fusion-builder' ),
							'param_name'  => 'dots_active_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'carousel_nav_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'subgroup'    => [
								'name' => 'dots_styling',
								'tab'  => 'hover',
							],
							'dependency'  => $dots_dependency,
						],

						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Navigation Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'Controls the space between content and navigation. Enter value including any valid CSS unit, ex: -40px.', 'fusion-builder' ),
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'       => 'nav_margin',
							'value'            => [
								'nav_margin_bottom' => '',
							],
							'dependency'       => [
								[
									'element'  => 'layout',
									'value'    => 'slider',
									'operator' => '==',
								],
								[
									'element'  => 'show_nav',
									'value'    => 'yes',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Slider Animation', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose the slider animation style.', 'fusion-builder' ),
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'param_name'  => 'slider_animation',
							'value'       => [
								'fade'  => esc_attr__( 'Fade', 'fusion-builder' ),
								'slide' => esc_attr__( 'Slide', 'fusion-builder' ),
							],
							'default'     => 'fade',
							'dependency'  => [
								[
									'element'  => 'layout',
									'value'    => 'slider',
									'operator' => '==',
								],
							],
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
							'type'             => 'subgroup',
							'heading'          => esc_html__( 'Load More - Button Styling', 'fusion-builder' ),
							'description'      => esc_html__( 'Customize "Load More" button colors.', 'fusion-builder' ),
							'param_name'       => 'load_more_button',
							'default'          => 'regular',
							'group'            => esc_html__( 'Design', 'fusion-builder' ),
							'remove_from_atts' => true,
							'value'            => [
								'regular' => esc_html__( 'Regular', 'fusion-builder' ),
								'active'  => esc_html__( 'Active', 'fusion-builder' ),
							],
							'icons'            => [
								'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
								'active'  => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
							],
							'dependency'       => [
								[
									'element'  => 'scrolling',
									'value'    => 'load_more_button',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the button text color.', 'fusion-builder' ),
							'param_name'  => 'load_more_btn_color',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'value'       => '',
							'default'     => 'var(--awb-color8)',
							'subgroup'    => [
								'name' => 'load_more_button',
								'tab'  => 'regular',
							],
							'dependency'  => [
								[
									'element'  => 'scrolling',
									'value'    => 'load_more_button',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the button background color.', 'fusion-builder' ),
							'param_name'  => 'load_more_btn_bg_color',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'value'       => '',
							'default'     => 'var(--awb-color3)',
							'subgroup'    => [
								'name' => 'load_more_button',
								'tab'  => 'regular',
							],
							'dependency'  => [
								[
									'element'  => 'scrolling',
									'value'    => 'load_more_button',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Hover Text Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the button hover text color.', 'fusion-builder' ),
							'param_name'  => 'load_more_btn_hover_color',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'value'       => '',
							'default'     => 'var(--awb-color1)',
							'subgroup'    => [
								'name' => 'load_more_button',
								'tab'  => 'active',
							],
							'dependency'  => [
								[
									'element'  => 'scrolling',
									'value'    => 'load_more_button',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Hover Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the button hover background color.', 'fusion-builder' ),
							'param_name'  => 'load_more_btn_hover_bg_color',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'value'       => '',
							'default'     => 'var(--awb-color5)',
							'subgroup'    => [
								'name' => 'load_more_button',
								'tab'  => 'active',
							],
							'dependency'  => [
								[
									'element'  => 'scrolling',
									'value'    => 'load_more_button',
									'operator' => '==',
								],
							],
						],
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-post-cards',
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Animation Delay', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the delay of animation between each element in a set. In seconds.', 'fusion-builder' ),
							'param_name'  => 'animation_delay',
							'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
							'min'         => '0',
							'max'         => '1',
							'step'        => '0.1',
							'value'       => '0',
							'dependency'  => [
								[
									'element'  => 'animation_type',
									'value'    => '',
									'operator' => '!=',
								],
								[
									'element'  => 'layout',
									'value'    => 'grid',
									'operator' => '==',
								],
							],
							'preview'     => [
								'selector' => '.fusion-post-cards',
								'type'     => 'animation',
							],
						],
					],
					'callback'     => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_tb_post_card_archives',
						'ajax'     => true,
					],
				]
			)
		);
	}
	add_action( 'fusion_builder_before_init', 'fusion_component_post_card_archives' );
}
