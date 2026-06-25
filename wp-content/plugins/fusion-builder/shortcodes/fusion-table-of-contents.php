<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.9
 */

if ( fusion_is_element_enabled( 'fusion_table_of_contents' ) ) {
	if ( ! class_exists( 'FusionSC_Table_Of_Contents' ) ) {

		/**
		 * Shortcode class.
		 *
		 * @since 3.9
		 */
		class FusionSC_Table_Of_Contents extends Fusion_Element {

			/**
			 * The number of instance of this element. Working as an id.
			 *
			 * @since 3.9
			 * @var int
			 */
			protected $element_counter = 1;

			/**
			 * Holds the meta key name of the TOC cache.
			 *
			 * @var string
			 */
			public static $meta_tree_cache_key = 'awb_toc_trees_cache';

			/**
			 * Cache the allowed title tags, to not construct every time.
			 *
			 * @var array
			 */
			public static $allowed_title_tags = [];

			/**
			 * Constructor.
			 *
			 * @since 3.9
			 */
			public function __construct() {
				parent::__construct();

				add_filter( 'fusion_attr_toc-shortcode-attr', [ $this, 'attr' ] );

				add_shortcode( 'fusion_table_of_contents', [ $this, 'render' ] );

				add_action( 'wp_footer', [ $this, 'clean_post_meta_garbage' ] );

				add_action( 'wp_ajax_nopriv_awb_save_toc_tree', __CLASS__ . '::save_toc_tree' );
				add_action( 'wp_ajax_awb_save_toc_tree', __CLASS__ . '::save_toc_tree' );
			}

			/**
			 * Gets the default values.
			 *
			 * @since 3.9
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'limit_container'                 => 'all',
					'select_custom_headings'          => '',
					'ignore_headings_by_classes'      => '',
					'ignore_headings_by_words'        => '',
					'allowed_heading_tags'            => 'h2,h3,h4',
					'enable_cache'                    => 'yes',
					'hide_hidden_titles'              => 'no',
					'highlight_current_heading'       => 'no',
					'hide_on_mobile'                  => fusion_builder_default_visibility( 'string' ),
					'class'                           => '',
					'id'                              => '',

					'margin_top'                      => '',
					'margin_right'                    => '',
					'margin_bottom'                   => '',
					'margin_left'                     => '',
					'padding_top'                     => '',
					'padding_right'                   => '',
					'padding_bottom'                  => '',
					'padding_left'                    => '',

					'counter_type'                    => 'none',
					'counter_separator'               => 'dot',
					'icon'                            => 'fa-flag fas',
					'custom_counter_separator'        => '',

					'item_text_overflow'              => 'no',
					'list_indent'                     => '20px',
					'fusion_font_family_item_font'    => '',
					'fusion_font_variant_item_font'   => '',
					'item_font_size'                  => '',
					'item_line_height'                => '',
					'item_letter_spacing'             => '',
					'item_text_transform'             => '',
					'item_color'                      => '',
					'item_color_hover'                => '',
					'item_bg_color_hover'             => '',
					'counter_color'                   => '',
					'hover_counter_color'             => '',

					'item_highlighted_bg_color'       => '',
					'item_hover_highlighted_bg_color' => '',
					'item_highlighted_color'          => '',
					'item_hover_highlighted_color'    => '',
					'highlighted_counter_color'       => '',
					'highlighted_hover_counter_color' => '',

					'item_padding_top'                => '0',
					'item_padding_right'              => '10px',
					'item_padding_bottom'             => '0',
					'item_padding_left'               => '10px',
					'item_radius_top_left'            => '',
					'item_radius_top_right'           => '',
					'item_radius_bottom_right'        => '',
					'item_radius_bottom_left'         => '',
					'item_margin_top'                 => '2px',
					'item_margin_bottom'              => '2px',

					'animation_type'                  => '',
					'animation_direction'             => 'down',
					'animation_speed'                 => '',
					'animation_delay'                 => '',
					'animation_offset'                => $fusion_settings->get( 'animation_offset' ),
					'animation_color'                 => '',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @since 3.9
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$html           = '';
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_table_of_contents' );

				$html .= '<div ' . FusionBuilder::attributes( 'toc-shortcode-attr' ) . '>';

				$html .= '<div class="awb-toc-el__content">';
				$html .= $this->maybe_create_html_for_headings();
				$html .= '</div>';

				$html .= '</div>';

				$this->on_render();
				$this->element_counter++;

				return apply_filters( 'fusion_element_toc_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @since 3.9
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class'                => 'awb-toc-el awb-toc-el--' . $this->element_counter,
					'data-awb-toc-id'      => (string) $this->element_counter,
					'data-awb-toc-options' => esc_attr( $this->get_toc_options_attribute() ),
					'style'                => $this->get_inline_style(),
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( 'li_default' === $this->args['counter_type'] ) {
					$attr['class'] .= ' awb-toc-el--default-list-type';
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return apply_filters( 'fusion_element_toc_attr', $attr, $this->args );
			}

			/**
			 * Get the HTML attribute for the options to generate the table of contents.
			 *
			 * @since 3.9
			 * @return string
			 */
			public function get_toc_options_attribute() {
				$options              = [];
				$allowed_heading_tags = array_flip( explode( ',', $this->args['allowed_heading_tags'] ) );

				$options['allowed_heading_tags']      = $allowed_heading_tags;
				$options['ignore_headings']           = $this->args['ignore_headings_by_classes'];
				$options['ignore_headings_words']     = $this->args['ignore_headings_by_words'];
				$options['enable_cache']              = $this->args['enable_cache'];
				$options['highlight_current_heading'] = $this->args['highlight_current_heading'];
				$options['hide_hidden_titles']        = $this->args['hide_hidden_titles'];
				$options['limit_container']           = $this->args['limit_container'];
				$options['select_custom_headings']    = $this->args['select_custom_headings'];
				$options['icon']                      = esc_attr( fusion_font_awesome_name_handler( $this->args['icon'] ) );
				$options['counter_type']              = $this->args['counter_type'];

				return wp_json_encode( $options, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
			}

			/**
			 * Get the inline style for element.
			 *
			 * @return string
			 */
			public function get_inline_style() {
				$custom_vars = [];

				$item_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'item_font', 'array' );
				foreach ( $item_typography as $rule => $value ) {
					$custom_vars[ 'item-' . $rule ] = $value;
				}

				if ( ! $this->is_default( 'counter_type' ) && 'li_default' !== $this->args['counter_type'] && 'custom_icon' !== $this->args['counter_type'] ) {
					$counter_separator = '';
					if ( 'dot' === $this->args['counter_separator'] ) {
						$counter_separator = '.';
					} elseif ( 'comma' === $this->args['counter_separator'] ) {
						$counter_separator = ',';
					} elseif ( 'custom' === $this->args['counter_separator'] ) {
						$counter_separator = addslashes( html_entity_decode( $this->args['custom_counter_separator'], ENT_QUOTES ) );
					}

					$value                       = 'counters(awb-toc, "' . $counter_separator . '", ' . $this->args['counter_type'] . ') "' . $counter_separator . ' "';
					$custom_vars['counter_type'] = $value;
				}

				$css_vars_options = [
					'margin_top',
					'margin_right',
					'margin_bottom',
					'margin_left',
					'padding_top',
					'padding_right',
					'padding_bottom',
					'padding_left',
					'item_font_size',
					'item_line_height',
					'item_letter_spacing',
					'item_text_transform',
					'item_color',
					'item_color_hover',
					'item_bg_color_hover',
					'counter_color',
					'hover_counter_color',
					'list_indent',
					'item_highlighted_bg_color',
					'item_hover_highlighted_bg_color',
					'item_highlighted_color',
					'item_hover_highlighted_color',
					'highlighted_counter_color',
					'highlighted_hover_counter_color',
					'item_padding_top',
					'item_padding_right',
					'item_padding_bottom',
					'item_padding_left',
					'item_radius_top_left',
					'item_radius_top_right',
					'item_radius_bottom_right',
					'item_radius_bottom_left',
					'item_margin_top',
					'item_margin_bottom',
				];

				if ( 'yes' === $this->args['item_text_overflow'] ) {
					$custom_vars['item-overflow']      = 'hidden';
					$custom_vars['item-white-space']   = 'nowrap';
					$custom_vars['item-text-overflow'] = 'ellipsis';
				}

				return $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );
			}

			/**
			 * Try to create the predefined HTML for the toc element from post meta.
			 *
			 * @since 3.9
			 * @return string
			 */
			public function maybe_create_html_for_headings() {
				global $post;
				$post_id = $post->ID;

				if ( ! is_int( $post_id ) || ! $post_id > 0 ) {
					return '';
				}

				$trees          = get_post_meta( $post_id, self::$meta_tree_cache_key, true );
				$trees          = apply_filters( 'awb_toc_headings_cache_trees', $trees, $this->element_counter, $this->args );
				$current_toc_id = $this->element_counter;

				if ( ! empty( $trees[ $current_toc_id ] ) && is_array( $trees[ $current_toc_id ] ) ) {
					return $this->create_html_for_headings( $trees[ $current_toc_id ] );
				}

				return '';
			}

			/**
			 * Generate HTML for headings if there are some content store in database.
			 *
			 * @since 3.9
			 * @param array $headings_tree The headings tree.
			 * @param int   $current_indent Defaults to 0.
			 * @return string
			 */
			public function create_html_for_headings( $headings_tree, $current_indent = 0 ) {
				/*
					Important! This function needs to be the same with JS function
					from front-end that generates HTML. Meaning that a change here
					will likely means a change there.  If you do not meet same HTML,
					then AJAX will fire on each page load(please also test for this).
				*/

				$list_classes = 'awb-toc-el__list awb-toc-el__list--' . $current_indent;
				$html         = '';
				$icon         = '';

				if ( 'custom_icon' === $this->args['counter_type'] && $this->args['icon'] ) {
					$icon = '<span class="awb-toc-el__item-icon ' . esc_attr( fusion_font_awesome_name_handler( $this->args['icon'] ) ) . '"></span>';
				}

				$html .= '<ul class="' . $list_classes . '">';

				$headings_tree_count = count( $headings_tree );
				for ( $i = 0; $i < $headings_tree_count; $i++ ) {
					$html .= '<li class="awb-toc-el__list-item">';

					if ( $headings_tree[ $i ]['title'] ) {
						$html .= '<a class="awb-toc-el__item-anchor" href="#' . $headings_tree[ $i ]['id'] . '">' . $icon . $headings_tree[ $i ]['title'] . '</a>';
					}

					if ( ! empty( $headings_tree[ $i ]['children'] ) && is_array( $headings_tree[ $i ]['children'] ) ) {
						$html .= $this->create_html_for_headings( $headings_tree[ $i ]['children'], $current_indent + 1 );
					}

					$html .= '</li>';
				}

				$html .= '</ul>';

				return $html;
			}

			/**
			 * Try to determine very fast if the current page/post has a meta with the toc
			 * searching in cache, and if no toc element was executed, then delete that meta.
			 *
			 * @return void
			 */
			public function clean_post_meta_garbage() {
				global $post;

				if ( ! is_object( $post ) || ! $post->ID || ! is_int( $post->ID ) || ! $post->ID > 0 ) {
					return;
				}

				$meta_cache = wp_cache_get( $post->ID, 'post_meta' );
				if ( is_array( $meta_cache ) && isset( $meta_cache[ self::$meta_tree_cache_key ] ) && 1 === $this->element_counter && ! fusion_is_builder_frame() ) {
					delete_post_meta( $post->ID, self::$meta_tree_cache_key );
				}
			}

			/**
			 * Load base CSS.
			 *
			 * @since 3.5
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/table-of-contents.min.css' );
			}

			/**
			 * Function that runs only on the first render.
			 *
			 * @since 3.9
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-table-of-contents-js',
					FusionBuilder::$js_folder_url . '/general/fusion-table-of-contents.js',
					FusionBuilder::$js_folder_path . '/general/fusion-table-of-contents.js',
					[ 'jquery', 'bootstrap-scrollspy', 'fusion-scroll-to-anchor' ],
					FUSION_BUILDER_VERSION,
					true
				);

				Fusion_Dynamic_JS::localize_script(
					'fusion-table-of-contents-js',
					'awbTOCElementVars',
					[
						'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					]
				);
			}

			/**
			 * Save the Toc tree into a cache meta.
			 *
			 * @since 3.9
			 * @return void
			 */
			public static function save_toc_tree() {
				$post_id = 0;
				if ( ! isset( $_POST['postId'], $_POST['trees'] ) || ! (int) $_POST['postId'] > 0 || ! is_array( $_POST['trees'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					return;
				}
				$post_id = (int) $_POST['postId']; // phpcs:ignore WordPress.Security.NonceVerification

				$trees_of_elements = wp_unslash( $_POST['trees'] ); // phpcs:ignore WordPress.Security

				if ( is_array( $trees_of_elements ) && count( $trees_of_elements ) ) {
					foreach ( $trees_of_elements as $key => $tree ) {
						$trees_of_elements[ $key ] = self::sanitize_cache_trees( $trees_of_elements[ $key ] );
					}

					update_post_meta( $post_id, self::$meta_tree_cache_key, $trees_of_elements );
				} else {
					delete_post_meta( $post_id, self::$meta_tree_cache_key );
				}
			}

			/**
			 * Sanitize Toc Trees received from ajax.
			 *
			 * @since 3.9
			 * @param array $trees The trees to sanitize.
			 * @return array
			 */
			public static function sanitize_cache_trees( $trees ) {
				$tree_count = count( $trees );
				for ( $i = 0; $i < $tree_count; $i++ ) {
					if ( empty( $trees[ $i ]['id'] ) ) {
						$trees[ $i ]['id'] = '';
					} else {
						$trees[ $i ]['id'] = preg_replace( '/[^\w]/', '', $trees[ $i ]['id'] );
					}

					if ( ! empty( $trees[ $i ]['title'] ) ) {
						$trees[ $i ]['title'] = wp_kses( $trees[ $i ]['title'], self::get_allowed_title_tags() );
					} else {
						$trees[ $i ]['title'] = '';
					}

					if ( ! empty( $trees[ $i ]['children'] ) && is_array( $trees[ $i ]['children'] ) ) {
						$trees[ $i ]['children'] = self::sanitize_cache_trees( $trees[ $i ]['children'] );
					} else {
						unset( $trees[ $i ]['children'] );
					}
				}

				return $trees;
			}

			/**
			 * Get the KSES tags allowed in title. This is meant to prevent XSS by letting only non-JS related tags and attributes.
			 *
			 * @return array
			 */
			public static function get_allowed_title_tags() {
				if ( ! empty( self::$allowed_title_tags ) ) {
					return self::$allowed_title_tags;
				}

				$allowed_tags = wp_kses_allowed_html();

				// Add more safe html tags, that could be in title.
				$additional_allowed_safe_tags           = [ 'br', 'kbd', 'mark', 'p', 'pre', 'rp', 'rt', 'ruby', 'samp', 'span', 'small', 'sub', 'sup', 'u', 'var' ];
				$additional_allowed_safe_tags_with_attr = [
					'dfn' => [ 'title' ],
					'ins' => [ 'datetime', 'cite' ],
				];

				$allowed_tags = array_merge( $allowed_tags, $additional_allowed_safe_tags_with_attr );
				foreach ( $additional_allowed_safe_tags as $key ) {
					$allowed_tags[ $key ] = [];
				}
				// a tag is not needed, since we strip it also in JS.
				unset( $allowed_tags['a'], $allowed_tags['script'] );

				// Allow class attribute.
				foreach ( $allowed_tags as $key => $value ) {
					$allowed_additional_attr = [
						'class' => true,
						'style' => true,
					];
					$allowed_tags[ $key ]    = array_merge( $allowed_tags[ $key ], $allowed_additional_attr );
				}

				self::$allowed_title_tags = $allowed_tags;
				return self::$allowed_title_tags;
			}
		}

		new FusionSC_Table_Of_Contents();
	}
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.9
 */
function fusion_element_table_of_contents() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Table_Of_Contents',
			[
				'name'                     => esc_attr__( 'Table Of Contents', 'fusion-builder' ),
				'shortcode'                => 'fusion_table_of_contents',
				'icon'                     => 'fusiona-table-of-content',
				'allow_generator'          => false,
				'inline_editor'            => false,
				'inline_editor_shortcodes' => false,
				'params'                   => fusion_get_table_of_contents_params(),
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_table_of_contents' );

/**
 * Table of contents settings params.
 *
 * @since 3.9
 */
function fusion_get_table_of_contents_params() {
	$fusion_settings = awb_get_fusion_settings();
	/* translators: %s represents open and close of an html link tag. */
	$all_css_selector_text = ' ' . sprintf( esc_html__( 'A list of all selectors is found %s here %s.', 'fusion-builder' ), '<a href="https://www.w3schools.com/cssref/css_selectors.asp" target="_blank">', '</a>' ); // phpcs:ignore WordPress.WP.I18n.UnorderedPlaceholdersText

	return [
		[
			'type'        => 'multiple_select',
			'heading'     => esc_attr__( 'Accepted Headings', 'fusion-builder' ),
			'description' => esc_attr__( 'Select which HTML headings tags should be indexed.', 'fusion-builder' ),
			'param_name'  => 'allowed_heading_tags',
			'value'       => [
				'h1' => esc_attr__( 'H1' ),
				'h2' => esc_attr__( 'H2' ),
				'h3' => esc_attr__( 'H3' ),
				'h4' => esc_attr__( 'H4' ),
				'h5' => esc_attr__( 'H5' ),
				'h6' => esc_attr__( 'H6' ),
			],
			'default'     => 'h2,h3,h4',
		],
		[
			'type'        => 'select',
			'heading'     => esc_attr__( 'Limit To Parent', 'fusion-builder' ),
			'description' => esc_html__( 'Controls which headings to show, depending on the parent.', 'fusion-builder' ),
			'param_name'  => 'limit_container',
			'value'       => [
				'all'          => esc_html__( 'All', 'fusion-builder' ),
				'post_content' => esc_html__( 'Post Content Element', 'fusion-builder' ),
				'page_content' => esc_html__( 'Layout/Page Content', 'fusion-builder' ),
				'custom'       => esc_html__( 'Custom', 'fusion-builder' ),
			],
			'default'     => 'all',
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_attr__( 'Limit Heading Selection By CSS Selectors', 'fusion-builder' ),

			'description' => esc_attr__( 'Choose to imit the indexing to certain containers. You can limit indexing to all headings within a container using ".container *". To choose multiple containers, use comma separation: ".container1 *, .container2 *".', 'fusion-builder' ) . $all_css_selector_text,
			'param_name'  => 'select_custom_headings',
			'value'       => '',
			'dependency'  => [
				[
					'element'  => 'limit_container',
					'value'    => 'custom',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_attr__( 'Ignore Headings By CSS Selector', 'fusion-builder' ),
			'description' => esc_attr__( 'Ignore headings that match the following CSS selector(s). Classes should have "." before. Separate multiple headings by comma. Defaults to ".awb-exclude-from-toc, .awb-exclude-from-toc *", which will ignore any title with "awb-exclude-from-toc" class, or any title inside a parent with same class.', 'fusion-builder' ) . $all_css_selector_text,
			'param_name'  => 'ignore_headings_by_classes',
			'value'       => '',
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_attr__( 'Ignore Headings By Words', 'fusion-builder' ),
			'description' => esc_attr__( 'Ignore headings that contains a specific word or a group of words. Separate multiple settings by "|", For example "sofa|soft chair" will ignore all headings that contains "sofa", but also the headings that contains "soft chair". These matches are case insensitive.', 'fusion-builder' ),
			'param_name'  => 'ignore_headings_by_words',
			'value'       => '',
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Hide Hidden Titles', 'fusion-builder' ),
			'description' => esc_attr__( 'Select whether or not to hide titles that are not visible when page loads.', 'fusion-builder' ),
			'param_name'  => 'hide_hidden_titles',
			'value'       => [
				'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
				'no'  => esc_attr__( 'No', 'fusion-builder' ),
			],
			'default'     => 'yes',
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Highlight Current Heading', 'fusion-builder' ),
			'description' => esc_attr__( 'Select whether to highlight the current heading which is viewed. Usually used while the element is positioned sticky in a column or container.', 'fusion-builder' ),
			'param_name'  => 'highlight_current_heading',
			'value'       => [
				'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
				'no'  => esc_attr__( 'No', 'fusion-builder' ),
			],
			'default'     => 'no',
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Cache Content (For SEO)', 'fusion-builder' ),
			'description' => esc_attr__( 'If the cache is used, the TOC content will be indexable by search engines, because the post/page will be served with the TOC element content already in place, rather than it being generated after page load. The TOC cache will be auto-updated after page load if the post/page content has been changed.', 'fusion-builder' ),
			'param_name'  => 'enable_cache',
			'value'       => [
				'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
				'no'  => esc_attr__( 'No', 'fusion-builder' ),
			],
			'default'     => 'yes',
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
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
			'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
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
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
			'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
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
			'type'             => 'typography',
			'remove_from_atts' => true,
			'global'           => true,
			'heading'          => esc_attr__( 'Item Typography', 'fusion-builder' ),
			'description'      => esc_html__( 'Controls the item text typography.', 'fusion-builder' ),
			'param_name'       => 'item_typography',
			'group'            => esc_attr__( 'Design', 'fusion-builder' ),
			'choices'          => [
				'font-family'    => 'item_font',
				'font-size'      => 'item_font_size',
				'line-height'    => 'item_line_height',
				'letter-spacing' => 'item_letter_spacing',
				'text-transform' => 'item_text_transform',
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
			'type'        => 'select',
			'heading'     => esc_attr__( 'Counter Type', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the type of the counter.', 'fusion-builder' ),
			'param_name'  => 'counter_type',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'value'       => [
				'none'                  => esc_html__( 'None', 'fusion-builder' ),
				'custom_icon'           => esc_html__( 'Custom Icon', 'fusion-builder' ),
				'li_default'            => esc_html__( 'Default Bullets', 'fusion-builder' ),
				'decimal'               => esc_html_x( 'Numeric', 'CSS property', 'fusion-builder' ),
				'decimal-leading-zero'  => esc_html_x( 'Decimal with Leading Zero', 'CSS property', 'fusion-builder' ),
				'upper-alpha'           => esc_html_x( 'Upper Alpha', 'CSS property', 'fusion-builder' ),
				'lower-alpha'           => esc_html_x( 'Lower Alpha', 'CSS property', 'fusion-builder' ),
				'upper-roman'           => esc_html_x( 'Upper Roman', 'CSS property', 'fusion-builder' ),
				'lower-roman'           => esc_html_x( 'Lower Roman', 'CSS property', 'fusion-builder' ),
				'lower-greek'           => esc_html_x( 'Lower Greek', 'CSS property', 'fusion-builder' ),
				'arabic-indic'          => esc_html_x( 'Arabic Indic', 'CSS property', 'fusion-builder' ),
				'armenian'              => esc_html_x( 'Armenian', 'CSS property', 'fusion-builder' ),
				'bengali'               => esc_html_x( 'Bengali', 'CSS property', 'fusion-builder' ),
				'cambodian'             => esc_html_x( 'Cambodian', 'CSS property', 'fusion-builder' ),
				'devanagari'            => esc_html_x( 'Devanagari', 'CSS property', 'fusion-builder' ),
				'georgian'              => esc_html_x( 'Georgian', 'CSS property', 'fusion-builder' ),
				'gujarati'              => esc_html_x( 'Gujarati', 'CSS property', 'fusion-builder' ),
				'gurmukhi'              => esc_html_x( 'Gurmukhi', 'CSS property', 'fusion-builder' ),
				'hebrew'                => esc_html_x( 'Hebrew', 'CSS property', 'fusion-builder' ),
				'hiragana'              => esc_html_x( 'Hiragana', 'CSS property', 'fusion-builder' ),
				'kannada'               => esc_html_x( 'Kannada', 'CSS property', 'fusion-builder' ),
				'katakana'              => esc_html_x( 'Katakana', 'CSS property', 'fusion-builder' ),
				'korean-hangul-formal'  => esc_html_x( 'Korean Hangul Formal', 'CSS property', 'fusion-builder' ),
				'korean-hanja-formal'   => esc_html_x( 'Korean Hanja Formal', 'CSS property', 'fusion-builder' ),
				'korean-hanja-informal' => esc_html_x( 'Korean Hanja Informal', 'CSS property', 'fusion-builder' ),
				'lao'                   => esc_html_x( 'Lao', 'CSS property', 'fusion-builder' ),
				'lower-armenian'        => esc_html_x( 'Lower Armenian', 'CSS property', 'fusion-builder' ),
				'malayalam'             => esc_html_x( 'Malayalam', 'CSS property', 'fusion-builder' ),
				'myanmar'               => esc_html_x( 'Myanmar', 'CSS property', 'fusion-builder' ),
				'oriya'                 => esc_html_x( 'Oriya', 'CSS property', 'fusion-builder' ),
				'persian'               => esc_html_x( 'Persian', 'CSS property', 'fusion-builder' ),
				'simp-chinese-formal'   => esc_html_x( 'Simplified Chinese Formal', 'CSS property', 'fusion-builder' ),
				'simp-chinese-informal' => esc_html_x( 'Simplified Chinese Informal', 'CSS property', 'fusion-builder' ),
				'telugu'                => esc_html_x( 'Telugu', 'CSS property', 'fusion-builder' ),
				'thai'                  => esc_html_x( 'Thai', 'CSS property', 'fusion-builder' ),
			],
			'default'     => 'none',
		],
		[
			'type'        => 'iconpicker',
			'heading'     => esc_attr__( 'Select Icon', 'fusion-builder' ),
			'param_name'  => 'icon',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'value'       => 'fa-flag fas',
			'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'counter_type',
					'value'    => 'custom_icon',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'select',
			'heading'     => esc_attr__( 'Counter Separator', 'fusion-builder' ),
			'description' => esc_html__( 'Select the separator between the counters.', 'fusion-builder' ),
			'param_name'  => 'counter_separator',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'value'       => [
				'none'   => esc_html__( 'None', 'fusion-builder' ),
				'dot'    => esc_html__( 'Dot', 'fusion-builder' ),
				'comma'  => esc_html__( 'Comma', 'fusion-builder' ),
				'custom' => esc_html__( 'Custom', 'fusion-builder' ),
			],
			'default'     => 'dot',
			'dependency'  => [
				[
					'element'  => 'counter_type',
					'value'    => 'none',
					'operator' => '!=',
				],
				[
					'element'  => 'counter_type',
					'value'    => 'li_default',
					'operator' => '!=',
				],
				[
					'element'  => 'counter_type',
					'value'    => 'custom_icon',
					'operator' => '!=',
				],
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_attr__( 'Custom Counter Separator', 'fusion-builder' ),
			'description' => esc_html__( 'Choose the custom separator between the counters.', 'fusion-builder' ),
			'param_name'  => 'custom_counter_separator',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => '',
			'dependency'  => [
				[
					'element'  => 'counter_type',
					'value'    => 'none',
					'operator' => '!=',
				],
				[
					'element'  => 'counter_type',
					'value'    => 'li_default',
					'operator' => '!=',
				],
				[
					'element'  => 'counter_type',
					'value'    => 'custom_icon',
					'operator' => '!=',
				],
				[
					'element'  => 'counter_separator',
					'value'    => 'custom',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_attr__( 'List Indent', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the list padding(distance) between different hierarchy items. Ex: "10px".', 'fusion-builder' ),
			'param_name'  => 'list_indent',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'value'       => '',
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Text On Single Line', 'fusion-builder' ),
			'description' => esc_attr__( 'Prevent item text from exceeding one line. If it exceeds, then "..." will show up instead. Very useful if the element is placed in a sidebar-like container.', 'fusion-builder' ),
			'param_name'  => 'item_text_overflow',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'value'       => [
				'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
				'no'  => esc_attr__( 'No', 'fusion-builder' ),
			],
			'default'     => 'no',
		],
		[
			'type'             => 'subgroup',
			'heading'          => esc_html__( 'Item Styling', 'fusion-builder' ),
			'description'      => esc_html__( 'Select the colors for the items.', 'fusion-builder' ),
			'param_name'       => 'item_styling',
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
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Item Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the item color. Defaults to link color.', 'fusion-builder' ),
			'param_name'  => 'item_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => $fusion_settings->get( 'link_color' ),
			'subgroup'    => [
				'name' => 'item_styling',
				'tab'  => 'regular',
			],
		],

		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Counter Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the color for the counter. Leave empty to inherit from item color.', 'fusion-builder' ),
			'param_name'  => 'counter_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => '',
			'dependency'  => [
				[
					'element'  => 'counter_type',
					'value'    => 'none',
					'operator' => '!=',
				],
			],
			'subgroup'    => [
				'name' => 'item_styling',
				'tab'  => 'regular',
			],
		],

		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Hover Item Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the item color on hover. Defaults to primary color.', 'fusion-builder' ),
			'param_name'  => 'item_color_hover',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => $fusion_settings->get( 'link_hover_color' ),
			'subgroup'    => [
				'name' => 'item_styling',
				'tab'  => 'hover',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Hover Item Background Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the item background color on hover. Defaults to transparent.', 'fusion-builder' ),
			'param_name'  => 'item_bg_color_hover',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => '',
			'subgroup'    => [
				'name' => 'item_styling',
				'tab'  => 'hover',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Hover Counter Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the color for the counter. Leave empty to inherit from item color.', 'fusion-builder' ),
			'param_name'  => 'hover_counter_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => '',
			'dependency'  => [
				[
					'element'  => 'counter_type',
					'value'    => 'none',
					'operator' => '!=',
				],
			],
			'subgroup'    => [
				'name' => 'item_styling',
				'tab'  => 'hover',
			],
		],

		[
			'type'             => 'subgroup',
			'heading'          => esc_html__( 'Highlight Item Styling', 'fusion-builder' ),
			'description'      => esc_html__( 'Select the colors for the highlighted items.', 'fusion-builder' ),
			'param_name'       => 'highlight_styling',
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
			'dependency'       => [
				[
					'element'  => 'highlight_current_heading',
					'value'    => 'yes',
					'operator' => '==',
				],
			],
		],

		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Highlighted Item Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the color for the highlighted item.', 'fusion-builder' ),
			'param_name'  => 'item_highlighted_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => $fusion_settings->get( '--awb-color1' ),
			'subgroup'    => [
				'name' => 'highlight_styling',
				'tab'  => 'regular',
			],
			'dependency'  => [
				[
					'element'  => 'highlight_current_heading',
					'value'    => 'yes',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Highlighted Item Background Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the background color for the highlighted item.', 'fusion-builder' ),
			'param_name'  => 'item_highlighted_bg_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => $fusion_settings->get( 'primary_color' ),
			'subgroup'    => [
				'name' => 'highlight_styling',
				'tab'  => 'regular',
			],
			'dependency'  => [
				[
					'element'  => 'highlight_current_heading',
					'value'    => 'yes',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Highlighted Item Counter Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the color for the highlighted counter. Leave empty to inherit from item color.', 'fusion-builder' ),
			'param_name'  => 'highlighted_counter_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => '',
			'subgroup'    => [
				'name' => 'highlight_styling',
				'tab'  => 'regular',
			],
			'dependency'  => [
				[
					'element'  => 'highlight_current_heading',
					'value'    => 'yes',
					'operator' => '==',
				],
				[
					'element'  => 'counter_type',
					'value'    => 'none',
					'operator' => '!=',
				],
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Highlighted Hover Item Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the color for the highlighted item when the mouse is over.', 'fusion-builder' ),
			'param_name'  => 'item_hover_highlighted_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => $fusion_settings->get( '--awb-color2' ),
			'subgroup'    => [
				'name' => 'highlight_styling',
				'tab'  => 'hover',
			],
			'dependency'  => [
				[
					'element'  => 'highlight_current_heading',
					'value'    => 'yes',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Highlighted Hover Item Background Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the background color for the highlighted item when the mouse is over.', 'fusion-builder' ),
			'param_name'  => 'item_hover_highlighted_bg_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => $fusion_settings->get( 'link_hover_color' ),
			'subgroup'    => [
				'name' => 'highlight_styling',
				'tab'  => 'hover',
			],
			'dependency'  => [
				[
					'element'  => 'highlight_current_heading',
					'value'    => 'yes',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_attr__( 'Highlighted Hover Item Counter Color', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the color for the highlighted counter when the mouse is over. Leave empty to inherit from item color.', 'fusion-builder' ),
			'param_name'  => 'highlighted_hover_counter_color',
			'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			'default'     => '',
			'subgroup'    => [
				'name' => 'highlight_styling',
				'tab'  => 'hover',
			],
			'dependency'  => [
				[
					'element'  => 'highlight_current_heading',
					'value'    => 'yes',
					'operator' => '==',
				],
				[
					'element'  => 'counter_type',
					'value'    => 'none',
					'operator' => '!=',
				],
			],
		],

		[
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_attr__( 'Item Padding', 'fusion-builder' ),
			'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%. Defaults to 0 10px 0 10px.', 'fusion-builder' ),
			'param_name'       => 'item_padding',
			'group'            => esc_attr__( 'Design', 'fusion-builder' ),
			'value'            => [
				'item_padding_top'    => '',
				'item_padding_right'  => '10px',
				'item_padding_bottom' => '',
				'item_padding_left'   => '10px',
			],
		],
		[
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_attr__( 'Item Padding', 'fusion-builder' ),
			'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%. Defaults to 0 5px 0 5px.', 'fusion-builder' ),
			'param_name'       => 'item_padding',
			'group'            => esc_attr__( 'Design', 'fusion-builder' ),
			'value'            => [
				'item_padding_top'    => '',
				'item_padding_right'  => '5px',
				'item_padding_bottom' => '',
				'item_padding_left'   => '5px',
			],
		],
		[
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_attr__( 'Item Border Radius', 'fusion-builder' ),
			'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
			'param_name'       => 'item_border_radius',
			'group'            => esc_attr__( 'Design', 'fusion-builder' ),
			'value'            => [
				'item_radius_top_left'     => '',
				'item_radius_top_right'    => '',
				'item_radius_bottom_right' => '',
				'item_radius_bottom_left'  => '',
			],
		],
		[
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_attr__( 'Item Margin', 'fusion-builder' ),
			'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%. Defaults to 2px 2px.', 'fusion-builder' ),
			'param_name'       => 'item_margin',
			'group'            => esc_attr__( 'Design', 'fusion-builder' ),
			'value'            => [
				'item_margin_top'    => '2px',
				'item_margin_bottom' => '2px',
			],
		],

		'fusion_animation_placeholder' => [
			'preview_selector' => '.awb-toc-el',
		],
	];
}
