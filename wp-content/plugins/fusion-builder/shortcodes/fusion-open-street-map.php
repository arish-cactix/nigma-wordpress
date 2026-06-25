<?php
/**
 * Add the OpenStreetMap element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.10
 */

if ( fusion_is_element_enabled( 'fusion_openstreetmap' ) ) {

	if ( ! class_exists( 'FusionSC_OpenStreetMap' ) ) {

		/**
		 * Shortcode class.
		 *
		 * @since 3.10
		 */
		class FusionSC_OpenStreetMap extends Fusion_Element {

			/**
			 * The one, true instance of this object.
			 *
			 * @static
			 * @access private
			 * @since 3.10
			 * @var object
			 */
			private static $instance;

			/**
			 * The number of instance of this element. Working as an id.
			 *
			 * @since 3.10
			 * @var int
			 */
			protected $element_counter = 1;

			/**
			 * The number of instance of the children elements. Working as an id.
			 *
			 * @since 3.10
			 * @var int
			 */
			protected $child_element_counter = 1;

			/**
			 * The parent arguments.
			 *
			 * @since 3.10
			 * @var array
			 */
			protected $parent_args = [];

			/**
			 * The Map Box API URL.
			 *
			 * @since 3.10
			 * @var string
			 */
			protected $mapbox_api_url = 'https://api.mapbox.com/styles/v1/mapbox/%s/tiles/{z}/{x}/{y}?access_token=%s';

			/**
			 * The Map Box Token.
			 *
			 * @since 3.10
			 * @var string
			 */
			protected $mapbox_token = '';

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.10
			 */
			public function __construct() {
				parent::__construct();

				add_shortcode( 'fusion_openstreetmap', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_openstreetmap_marker', [ $this, 'render_child' ] );

				add_filter( 'fusion_attr_openstreetmap-element-attr', [ $this, 'element_attr' ] );

				add_filter( 'fusion_attr_openstreetmap-child-element-attr', [ $this, 'child_elem_attr' ] );
				add_filter( 'fusion_attr_openstreetmap-child-element-icon-attr', [ $this, 'child_elem_icon_attr' ] );

				$fusion_settings = awb_get_fusion_settings();

				// Set MapBox token.
				$this->mapbox_token = $fusion_settings->get( 'openstreetmap_mapbox_access_token' );
			}

			/**
			 * Creates or returns an instance of this class.
			 *
			 * @static
			 * @access public
			 * @since 3.10
			 */
			public static function get_instance() {

				// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
				if ( null === self::$instance ) {
					self::$instance = new FusionSC_OpenStreetMap();
				}
				return self::$instance;
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.10
			 * @param string $context Whether we want parent or child.
			 *                        Returns array( parent, child ) if empty.
			 * @return array
			 */
			public static function get_element_defaults( $context = '' ) {
				$fusion_settings = awb_get_fusion_settings();
				$border_radius   = Fusion_Builder_Border_Radius_Helper::get_border_radius_array_with_fallback_value( $fusion_settings->get( 'icon_border_radius' ) );

				$parent = [
					'element_content'                      => '',
					'hide_on_mobile'                       => fusion_builder_default_visibility( 'string' ),
					'class'                                => '',
					'id'                                   => '',
					'map_style'                            => $fusion_settings->get( 'openstreetmap_map_style' ),
					'map_type'                             => 'marker',
					'fitbounds'                            => 'yes',
					'width'                                => '100%',
					'height'                               => '300px',
					'zoom'                                 => '14',
					'zoom_snap'                            => '1',
					'zoom_control'                         => 'yes',
					'scrollwheel'                          => 'yes',
					'dragging'                             => 'yes',
					'touchzoom'                            => 'yes',
					'dbclickzoom'                          => 'yes',
					'action'                               => 'popup',
					'items_animation'                      => 'none',
					'shape_color'                          => 'var(--awb-color5)',
					'shape_size'                           => '3',
					'popup_background_color'               => 'var(--awb-color1)',
					'popup_close_btn_color'                => 'var(--awb-color7)',
					'fusion_font_family_popup_title_font'  => '',
					'fusion_font_variant_popup_title_font' => '',
					'popup_title_font_size'                => '',
					'popup_title_line_height'              => '',
					'popup_title_letter_spacing'           => '',
					'popup_title_text_transform'           => '',
					'popup_title_color'                    => $fusion_settings->get( 'h5_typography', 'color' ),
					'popup_title_margin_bottom'            => '',
					'popup_title_margin_top'               => '',
					'popup_title_alignment'                => '',
					'fusion_font_family_popup_content_font' => '',
					'fusion_font_variant_popup_content_font' => '',
					'popup_content_font_size'              => '',
					'popup_content_line_height'            => '',
					'popup_content_letter_spacing'         => '',
					'popup_content_text_transform'         => '',
					'popup_content_alignment'              => '',
					'popup_content_color'                  => $fusion_settings->get( 'body_typography', 'color' ),
					'popup_padding_top'                    => '',
					'popup_padding_right'                  => '',
					'popup_padding_bottom'                 => '',
					'popup_padding_left'                   => '',
					'margin_top'                           => '',
					'margin_right'                         => '',
					'margin_bottom'                        => '',
					'margin_left'                          => '',
					'animation_direction'                  => 'left',
					'animation_offset'                     => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                      => '',
					'animation_delay'                      => '',
					'animation_type'                       => '',
					'animation_color'                      => '',
				];

				$child = [
					'icon'                       => 'fa-map-marker-alt fas',
					'title'                      => '',
					'action'                     => '',
					'address'                    => '',
					'latitude'                   => '',
					'longitude'                  => '',
					'tooltip_content'            => '',
					'size'                       => '22',
					'color'                      => $fusion_settings->get( 'icon_color' ),
					'background_color'           => $fusion_settings->get( 'icon_circle_color' ),
					'hover_color'                => $fusion_settings->get( 'icon_color_hover' ),
					'hover_background_color'     => $fusion_settings->get( 'icon_circle_color_hover' ),
					'border_radius_top_left'     => $border_radius['top_left'],
					'border_radius_top_right'    => $border_radius['top_right'],
					'border_radius_bottom_right' => $border_radius['bottom_right'],
					'border_radius_bottom_left'  => $border_radius['bottom_left'],
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
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 3.10
			 * @param 'parent'|'child' $context Can be 'parent' or 'child'.
			 * @return array
			 */
			public static function settings_to_params( $context ) {
				$parent = [
					'openstreetmap_map_style' => 'map_style',
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				}
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 3.10
			 * @return array $sections Button settings.
			 */
			public function add_options() {
				$api_key_link        = 'https://docs.mapbox.com/help/getting-started/access-tokens/';
				$map_styles          = $this->get_map_styles();
				$map_styles_option   = [];
				$link_tile_providers = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', 'https://wiki.openstreetmap.org/wiki/Raster_tile_providers', esc_html__( 'Raster Tile Providers ', 'fusion-builder' ) );
				
				foreach ( $map_styles as $key => $style ) {
					$map_styles_option[ $key ] = $style['label'];
				}

				return [
					'openstreetmap_shortcode_section' => [
						'label'  => esc_html__( 'OpenStreetMap', 'fusion-builder' ),
						'id'     => 'openstreetmap_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-check-empty',
						'fields' => [
							'openstreetmap_map_style' => [
								'type'        => 'select',
								'label'       => esc_attr__( 'Map Style', 'fusion-builder' ),
								/* translators: %s - Link to raster tile providers of OSM. */
								'description' => sprintf( esc_html__( 'Select the map style. For more information about licensing, please see the documentation %s. Using Mapbox Style will required API Access Token.', 'fusion-builder' ), $link_tile_providers ),
								'id'          => 'openstreetmap_map_style',
								'choices'     => $map_styles_option,
								'default'     => 'osm-carto',
								'transport'   => 'postMessage',
							],
							'openstreetmap_mapbox_access_token' => [
								'label'       => esc_html__( 'MapBox Access Token', 'fusion-builder' ),
								/* translators: %s - Link to documentation. */
								'description' => sprintf( __( 'Enter your MapBox Access Token to use custom MapBox tiles. For more information please see <a href="%s" target="_blank">MapBox Access Token Guide</a>.', 'fusion-builder' ), $api_key_link ),
								'id'          => 'openstreetmap_mapbox_access_token',
								'default'     => '',
								'type'        => 'text',
							],
						],
					],
				];
			}

			/**
			 * Get styles for Map tile.
			 *
			 * @since 3.10
			 * @return array
			 */
			public function get_map_styles() {
				$styles = [
					'osm-carto'                => [
						'label'     => esc_html__( 'OSM Carto', 'fusion-builder' ),
						'url'       => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
						'attribute' => __( '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>', 'fusion-builder' ),
					],
					'osm-fr'                   => [
						'label'     => esc_html__( 'OSM France', 'fusion-builder' ),
						'url'       => 'https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',
						'attribute' => __( '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>', 'fusion-builder' ),
					],
					'osm-de'                   => [
						'label'     => esc_html__( 'OSM Deutschland', 'fusion-builder' ),
						'url'       => 'https://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png',
						'attribute' => __( '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>', 'fusion-builder' ),
					],
					'stamen-toner'             => [
						'label'     => esc_html__( 'Stamen Toner', 'fusion-builder' ),
						'url'       => 'https://stamen-tiles.a.ssl.fastly.net/toner/{z}/{x}/{y}.png',
						'attribute' => __( 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, Data by <a href="http://openstreetmap.org">OpenStreetMap</a>', 'fusion-builder' ),
					],
					'stamen-terrain'           => [
						'label'     => esc_html__( 'Stamen Terrain', 'fusion-builder' ),
						'url'       => 'https://stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg',
						'attribute' => __( 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, Data by <a href="http://openstreetmap.org">OpenStreetMap</a>', 'fusion-builder' ),
					],
					'stamen-watercolor'        => [
						'label'     => esc_html__( 'Stamen Watercolor', 'fusion-builder' ),
						'url'       => 'https://stamen-tiles.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.jpg',
						'attribute' => __( 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, Data by <a href="http://openstreetmap.org">OpenStreetMap</a>', 'fusion-builder' ),
					],

					'topomap'                  => [
						'label'     => esc_html__( 'Topography', 'fusion-builder' ),
						'url'       => 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
						'attribute' => __( 'Map tiles by <a href="http://opentopomap.org">Open Topomap</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under ODbL.', 'fusion-builder' ),
					],
					'carto-db'                 => [
						'label'     => esc_html__( 'Carto Light', 'fusion-builder' ),
						'url'       => 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png',
						'attribute' => __( 'Map tiles by <a href="https://carto.com/attributions">CARTO</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under ODbL.', 'fusion-builder' ),
					],
					'carto-dark'               => [
						'label'     => esc_html__( 'Carto Dark', 'fusion-builder' ),
						'url'       => ' 	https://cartodb-basemaps-{s}.global.ssl.fastly.net/dark_all/{z}/{x}/{y}.png',
						'attribute' => __( 'Map tiles by <a href="https://carto.com/attributions">CARTO</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under ODbL.', 'fusion-builder' ),
					],					
					'esri-world'               => [
						'label'     => esc_html__( 'Esri World Street', 'fusion-builder' ),
						'url'       => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}',
						'attribute' => __( 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, &copy; Esri', 'fusion-builder' ),
					],
					'esri-imagery'             => [
						'label'     => esc_html__( 'Esri Imagery', 'fusion-builder' ),
						'url'       => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
						'attribute' => __( 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, &copy; Esri', 'fusion-builder' ),
					],
					'esri-topo'                => [
						'label'     => esc_html__( 'Esri Topo Map', 'fusion-builder' ),
						'url'       => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
						'attribute' => __( 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, &copy; Esri', 'fusion-builder' ),
					],
					'mb-streets-v12'           => [
						'label'     => esc_html__( 'Mapbox Streets', 'fusion-builder' ),
						'url'       => $this->get_mapbox_tile_url( 'streets-v12' ),
						'attribute' => __( 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>', 'fusion-builder' ),
					],
					'mb-outdoors-v12'          => [
						'label'     => esc_html__( 'Mapbox Outdoors', 'fusion-builder' ),
						'url'       => $this->get_mapbox_tile_url( 'outdoors-v12' ),
						'attribute' => __( 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>', 'fusion-builder' ),
					],
					'mb-light-v11'             => [
						'label'     => esc_html__( 'Mapbox Light', 'fusion-builder' ),
						'url'       => $this->get_mapbox_tile_url( 'light-v11' ),
						'attribute' => __( 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>', 'fusion-builder' ),
					],
					'mb-dark-v11'              => [
						'label'     => esc_html__( 'Mapbox Dark', 'fusion-builder' ),
						'url'       => $this->get_mapbox_tile_url( 'dark-v11' ),
						'attribute' => __( 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>', 'fusion-builder' ),
					],
					'mb-satellite-v9'          => [
						'label'     => esc_html__( 'Mapbox Satellite', 'fusion-builder' ),
						'url'       => $this->get_mapbox_tile_url( 'satellite-v9' ),
						'attribute' => __( 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>', 'fusion-builder' ),
					],
					'mb-satellite-streets-v12' => [
						'label'     => esc_html__( 'Mapbox Satellite Streets', 'fusion-builder' ),
						'url'       => $this->get_mapbox_tile_url( 'satellite-streets-v12' ),
						'attribute' => __( 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>', 'fusion-builder' ),
					],
					'mb-navigation-day-v1'     => [
						'label'     => esc_html__( 'Mapbox Navigation Day', 'fusion-builder' ),
						'url'       => $this->get_mapbox_tile_url( 'navigation-day-v1' ),
						'attribute' => __( 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>', 'fusion-builder' ),
					],
					'mb-navigation-night-v1'   => [
						'label'     => esc_html__( 'Mapbox Navigation Night', 'fusion-builder' ),
						'url'       => $this->get_mapbox_tile_url( 'navigation-night-v1' ),
						'attribute' => __( 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>', 'fusion-builder' ),
					],
				];

				return $styles;
			}

			/**
			 * Get mapbox tile URL.
			 *
			 * @access public
			 * @since 3.10
			 * @param string $style_id Style ID.
			 */
			public function get_mapbox_tile_url( $style_id ) {
				return sprintf( $this->mapbox_api_url, $style_id, $this->mapbox_token );
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.10
			 * @param array  $args Shortcode parameters.
			 * @param string $content The content inside the shortcode.
			 * @return string HTML output.
			 */
			public function render_parent( $args, $content = '' ) {
				$this->defaults    = self::get_element_defaults( 'parent' );
				$this->args        = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_openstreetmap' );
				$this->parent_args = $this->args;

				$html  = '<div ' . FusionBuilder::attributes( 'openstreetmap-element-attr' ) . '>';
				$html .= do_shortcode( $content );
				$html .= '</div>';

				$this->on_render();
				$this->element_counter++;

				return $html;
			}

			/**
			 * Creates the element attributes.
			 *
			 * @since 3.10
			 * @return array
			 */
			public function element_attr() {
				$attr = [
					'style' => $this->get_inline_style(),
					'class' => 'awb-openstreet-map',
				];

				$attr['class'] .= ' ' . $this->get_base_class();

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

				$attr['data-map-style']       = $this->args['map_style'];
				$attr['data-map-type']        = $this->args['map_type'];
				$attr['data-zoom']            = $this->args['zoom'];
				$attr['data-zoomsnap']        = $this->args['zoom_snap'];
				$attr['data-zoomcontrol']     = 'yes' === $this->args['zoom_control'];
				$attr['data-scrollwheelzoom'] = 'yes' === $this->args['scrollwheel'];
				$attr['data-dragging']        = 'yes' === $this->args['dragging'];
				$attr['data-touchzoom']       = 'yes' === $this->args['touchzoom'];
				$attr['data-dbclickzoom']     = 'yes' === $this->args['dbclickzoom'];
				$attr['data-fitbounds']       = 'yes' === $this->args['fitbounds'];
				$attr['data-shape-color']     = Fusion_Color::new_color( $this->args['shape_color'] )->toCss( 'rgba' );
				$attr['data-shape-weight']    = $this->args['shape_size'];
				return $attr;
			}

			/**
			 * Get the inline style for element.
			 *
			 * @since 3.10
			 * @return string
			 */
			public function get_inline_style() {
				$css_vars_options = [
					'width',
					'height',
					'popup_background_color'       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'popup_close_btn_color'        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'margin_top'                   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_right'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_left'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_title_font_size'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_title_line_height',
					'popup_title_letter_spacing'   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_title_text_transform',
					'popup_title_color'            => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'popup_title_margin_top'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_title_margin_bottom'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_title_alignment',
					'popup_content_font_size'      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_content_line_height',
					'popup_content_letter_spacing' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_content_text_transform',
					'popup_content_alignment',
					'popup_content_color'          => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'popup_padding_top'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_padding_right'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_padding_bottom'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'popup_padding_left'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];

				return $this->get_css_vars_for_options( $css_vars_options ) . $this->get_font_styling_vars( 'popup_title_font' ) . $this->get_font_styling_vars( 'popup_content_font' );
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 3.10
			 * @param array  $args Shortcode parameters.
			 * @param string $content The content inside the shortcode.
			 * @return string HTML output.
			 */
			public function render_child( $args, $content = '' ) {
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_openstreetmap_marker' );
				$this->defaults = self::get_element_defaults( 'child' );

				$html = '<div ' . FusionBuilder::attributes( 'openstreetmap-child-element-attr' ) . '>';

				// Icon HTML.
				if ( ! empty( $this->args['icon'] ) ) {
					$animation_class = $this->animation_to_class_name( $this->parent_args['items_animation'] );
					$animation_class = $animation_class ? ' ' . $animation_class : '';

					$html .= '<div class="awb-openstreet-map-marker-icon-wrapper' . $animation_class . '" style="' . $this->get_child_inline_style() . '">';
					$html .= '<i ' . FusionBuilder::attributes( 'openstreetmap-child-element-icon-attr' ) . '></i>';
					$html .= '</div>';
				}

				// Content HTML.
				$html .= '<div class="awb-openstreet-map-content-wrapper">';
				if ( ! $this->is_default( 'title' ) ) {
					$html .= '<h5 class="awb-openstreet-map-marker-title">';
					$html .= esc_html( $this->args['title'] );
					$html .= '</h5>';
				}
				if ( ! $this->is_default( 'tooltip_content' ) ) {
					$html .= '<div class="awb-openstreet-map-marker-content">';
					$html .= trim( fusion_decode_if_needed( $this->args['tooltip_content'] ) );
					$html .= '</div>';
				}
				$html .= '</div>';

				$html .= '</div>';

				$this->child_element_counter++;
				return $html;
			}

			/**
			 * Get the inline style for element.
			 *
			 * @since 3.10
			 * @return string
			 */
			protected function get_child_inline_style() {
				$css_vars_options = [
					'size'                       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'color'                      => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'background_color'           => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'hover_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'hover_background_color'     => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'border_radius_top_left'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_top_right'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_bottom_right' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_bottom_left'  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];

				return $this->get_css_vars_for_options( $css_vars_options );
			}

			/**
			 * Creates the child element attributes.
			 *
			 * @since 3.10
			 * @return array
			 */
			public function child_elem_attr() {
				$attr = [
					'class' => 'awb-openstreet-map-marker awb-openstreet-map-marker-' . $this->child_element_counter,
				];

				$attr['data-latitude']  = $this->args['latitude'];
				$attr['data-longitude'] = $this->args['longitude'];
				$attr['data-icon']      = fusion_font_awesome_name_handler( $this->args['icon'] );
				$attr['data-action']    = $this->is_default( 'action' ) ? $this->parent_args['action'] : $this->args['action'];

				return $attr;
			}

			/**
			 * Creates the child icon element attributes.
			 *
			 * @since 3.10
			 * @return array
			 */
			public function child_elem_icon_attr() {
				$attr = [
					'class' => fusion_font_awesome_name_handler( $this->args['icon'] ),
				];

				return $attr;
			}

			/**
			 * Fetch map styles select.
			 *
			 * @access protected
			 * @since 3.10
			 * @return string
			 */
			public function fetch_map_styles_option() {
				$styles              = $this->get_map_styles();
				$fusion_settings     = awb_get_fusion_settings();
				$options             = [
					'' => esc_html__( 'Default', 'fusion-builder' ),
				];
				$link_tile_providers = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', 'https://wiki.openstreetmap.org/wiki/Raster_tile_providers', esc_html__( 'Raster Tile Providers ', 'fusion-builder' ) );
				$link_go             = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', $fusion_settings->get_setting_link( 'openstreetmap_mapbox_access_token' ), esc_html__( 'Global Options', 'fusion-builder' ) );
				
				if ( is_array( $styles ) ) {
					foreach ( $styles as $key => $style ) {
						$options[ $key ] = ucwords( esc_html( $style['label'] ) );
					}
				}

				return [
					'type'        => 'select',
					'heading'     => esc_html__( 'Map Style', 'fusion-builder' ),
					/* translators: %1$s - Link to raster tile providers of OSM, %2$s - Link to Global Options. */
					'description' => sprintf( esc_html__( 'Select map style. For more information about licensing, please see the documentation %1$s. Using Mapbox Style will required API Access Token in the %2$s.', 'fusion-builder' ), $link_tile_providers, $link_go ),
					'param_name'  => 'map_style',
					'default'     => '',
					'value'       => $options,
				];
			}

			/**
			 * Get the class name with an unique id among elements.
			 *
			 * @since 3.10
			 * @return string
			 */
			public function get_base_class() {
				return 'awb-openstreet-map-' . $this->element_counter;
			}

			/**
			 * Get the animation class corresponding with the animation id.
			 *
			 * @since 3.10
			 * @param string $animation_name The animation name.
			 * @return string Empty string if do not exist.
			 */
			protected function animation_to_class_name( $animation_name ) {
				if ( 'pumping' === $animation_name ) {
					return 'awb-openstreet-map-marker-anim-pumping';
				}

				if ( 'pulsating' === $animation_name ) {
					return 'awb-openstreet-map-marker-anim-pulsating';
				}

				if ( 'showing' === $animation_name ) {
					return 'awb-openstreet-map-marker-anim-showing';
				}

				if ( 'sonar' === $animation_name ) {
					return 'awb-openstreet-map-marker-anim-sonar';
				}

				if ( 'pumping_showing' === $animation_name ) {
					return 'awb-openstreet-map-marker-anim-pump-showing';
				}

				return '';
			}

			/**
			 * Function that runs only on the first render.
			 *
			 * @since 3.10
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::register_script(
					'fusion-leaflet',
					FusionBuilder::$js_folder_url . '/library/leaflet.js',
					FusionBuilder::$js_folder_path . '/library/leaflet.js',
					[],
					FUSION_BUILDER_VERSION,
					true
				);

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-open-street-map',
					FusionBuilder::$js_folder_url . '/general/fusion-open-street-map.js',
					FusionBuilder::$js_folder_path . '/general/fusion-open-street-map.js',
					[ 'jquery', 'fusion-leaflet' ],
					FUSION_BUILDER_VERSION,
					true
				);

				$localize_vars = [
					'tiles'          => $this->get_map_styles(),
					'default_coords' => [ '40.71613', '-73.81646' ],
				];

				Fusion_Dynamic_JS::localize_script(
					'fusion-open-street-map',
					'awbOpenStreetMap',
					apply_filters( 'fusion_openstreetmap_localize_vars', $localize_vars )
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @since 3.10
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/leaflet.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/open-street-map.min.css' );
			}
		}
	}

	/**
	 * Instantiates the class.
	 *
	 * @since 3.9
	 * @return object FusionSC_OpenStreetMap
	 */
	function fusion_openstreetmap() { // phpcs:ignore WordPress.NamingConventions
		return FusionSC_OpenStreetMap::get_instance();
	}

	// Instantiate stripe button.
	fusion_openstreetmap();
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 3.10
 */
function fusion_element_openstreetmap() {
	$fusion_settings   = awb_get_fusion_settings();
	$editing           = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
	$map_styles_option = [];

	if ( function_exists( 'fusion_openstreetmap' ) && $editing ) {
		$map_styles_option = fusion_openstreetmap()->fetch_map_styles_option();
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_OpenStreetMap',
			[
				'name'          => esc_attr__( 'OpenStreetMap', 'fusion-builder' ),
				'shortcode'     => 'fusion_openstreetmap',
				'multi'         => 'multi_element_parent',
				'icon'          => 'fusiona-open_street_map',
				'child_ui'      => true,
				'element_child' => 'fusion_openstreetmap_marker',
				'subparam_map'  => [
					'fusion_font_family_popup_title_font'  => 'popup_title_fonts',
					'fusion_font_variant_popup_title_font' => 'popup_title_fonts',
					'popup_title_font_size'                => 'popup_title_fonts',
					'popup_title_line_height'              => 'popup_title_fonts',
					'popup_title_letter_spacing'           => 'popup_title_fonts',
					'popup_title_text_transform'           => 'popup_title_fonts',
					'popup_title_color'                    => 'popup_title_fonts',
					'popup_title_margin_top'               => 'popup_title_fonts',
					'popup_title_margin_bottom'            => 'popup_title_fonts',
					'fusion_font_family_popup_content_font' => 'popup_content_fonts',
					'fusion_font_variant_popup_content_font' => 'popup_content_fonts',
					'popup_content_font_size'              => 'popup_content_fonts',
					'popup_content_line_height'            => 'popup_content_fonts',
					'popup_content_letter_spacing'         => 'popup_content_fonts',
					'popup_content_text_transform'         => 'popup_content_fonts',
					'popup_content_color'                  => 'popup_content_fonts',
				],
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this content box.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_openstreetmap_marker][/fusion_openstreetmap_marker]',
					],
					$map_styles_option,
					[
						'type'        => 'select',
						'heading'     => esc_html__( 'Map Type', 'fusion-builder' ),
						'description' => esc_html__( 'Select map type. Polyline will connect markers through a line. Polygon needs at least 3 markers to work, will connect them through lines, and add a semit-transparent background color to the enclosed area.', 'fusion-builder' ),
						'param_name'  => 'map_type',
						'default'     => 'marker',
						'value'       => [
							'marker'   => esc_html__( 'Marker', 'fusion-builder' ),
							'polyline' => esc_html__( 'Polyline', 'fusion-builder' ),
							'polygon'  => esc_html__( 'Polygon', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Center On Markers', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the map\'s view to show all markers. If "Yes" will hide Zoom Level & Zoom Snap option.', 'fusion-builder' ),
						'param_name'  => 'fitbounds',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Map Dimensions', 'fusion-builder' ),
						'description'      => __( 'Map dimensions in percentage, pixels or ems. <strong>NOTE:</strong> Height does not accept percentage value.', 'fusion-builder' ),
						'param_name'       => 'map_dimensions',
						'value'            => [
							'width'  => '100%',
							'height' => '300px',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Zoom Level', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the zoom level for the map. 0 corresponds to a map of the earth fully zoomed out, and larger zoom levels zoom in at a higher resolution.', 'fusion-builder' ),
						'param_name'  => 'zoom',
						'value'       => '14',
						'min'         => '0',
						'max'         => '25',
						'step'        => '0.1',
						'dependency'  => [
							[
								'element'  => 'fitbounds',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Zoom Snap', 'fusion-builder' ),
						'description' => esc_attr__( 'Zoom snap controls the interval for the zoom level. If 0 is used for the zoom snap the zoom level will not snap after centering on markers.', 'fusion-builder' ),
						'param_name'  => 'zoom_snap',
						'value'       => '1',
						'min'         => '0',
						'max'         => '1',
						'step'        => '0.1',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Zoom Control on Map', 'fusion-builder' ),
						'description' => esc_attr__( 'Display zoom control.', 'fusion-builder' ),
						'param_name'  => 'zoom_control',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Map Dragging', 'fusion-builder' ),
						'description' => esc_attr__( 'Enable dragging on map.', 'fusion-builder' ),
						'param_name'  => 'dragging',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Touch Zoom', 'fusion-builder' ),
						'description' => esc_attr__( 'Enable zooming using touch gesture.', 'fusion-builder' ),
						'param_name'  => 'touchzoom',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Double-Click Zoom', 'fusion-builder' ),
						'description' => esc_attr__( 'Enable zooming using double click event.', 'fusion-builder' ),
						'param_name'  => 'dbclickzoom',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Scrollwheel', 'fusion-builder' ),
						'description' => esc_attr__( 'Enable zooming using the mouse scroll wheel.', 'fusion-builder' ),
						'param_name'  => 'scrollwheel',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'         => 'radio_button_set',
						'heading'      => esc_attr__( 'Popup Display Type', 'fusion-builder' ),
						'description'  => esc_attr__( 'Choose popup display type.' ),
						'param_name'   => 'action',
						'value'        => [
							'popup'            => esc_attr__( 'Popup', 'fusion-builder' ),
							'tooltip'          => esc_attr__( 'Tooltip', 'fusion-builder' ),
							'static_close_on'  => esc_attr__( 'Static with Close', 'fusion-builder' ),
							'static_close_off' => esc_attr__( 'Static without Close', 'fusion-builder' ),
							'none'             => esc_attr__( 'None', 'fusion-builder' ),
						],
						'default'      => 'popup',
						'dynamic_data' => true,
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
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
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Marker Animation', 'fusion-builder' ),
						'description' => esc_html__( 'Select an animation for the marker point.', 'fusion-builder' ),
						'param_name'  => 'items_animation',
						'default'     => 'none',
						'value'       => [
							'none'            => esc_attr__( 'None', 'fusion-builder' ),
							'pumping'         => esc_attr__( 'Pumping', 'fusion-builder' ),
							'pulsating'       => esc_attr__( 'Pulsating', 'fusion-builder' ),
							'showing'         => esc_attr__( 'Showing', 'fusion-builder' ),
							/* translators: Name of an HTML element animation. */
							'sonar'           => esc_attr__( 'Sonar', 'fusion-builder' ),
							'pumping_showing' => esc_attr__( 'Pumping + Showing', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Connecting Line Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the connecting line color.', 'fusion-builder' ),
						'param_name'  => 'shape_color',
						'value'       => '',
						'default'     => 'var(--awb-color5)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'map_type',
								'value'    => 'marker',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Connecting Line Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the size of the connecting line. In pixels.', 'fusion-builder' ),
						'param_name'  => 'shape_size',
						'value'       => '3',
						'min'         => '1',
						'max'         => '250',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'map_type',
								'value'    => 'marker',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Popup Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the popup title. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'popup_title_fonts',
						'choices'          => [
							'font-family'    => 'popup_title_font',
							'font-size'      => 'popup_title_font_size',
							'text-transform' => 'popup_title_text_transform',
							'line-height'    => 'popup_title_line_height',
							'letter-spacing' => 'popup_title_letter_spacing',
							'color'          => 'popup_title_color',
							'margin-top'     => 'popup_title_margin_top',
							'margin-bottom'  => 'popup_title_margin_bottom',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'h5_typography', 'color' ),
							'margin-top'     => '',
							'margin-bottom'  => '',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Popup Title Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the popup title alignment.', 'fusion-builder' ),
						'param_name'  => 'popup_title_alignment',
						'default'     => '',
						'value'       => [
							''        => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'    => esc_attr__( 'Left', 'fusion-builder' ),
							'center'  => esc_attr__( 'Center', 'fusion-builder' ),
							'right'   => esc_attr__( 'Right', 'fusion-builder' ),
							'justify' => esc_attr__( 'Justify', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Popup Content Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the popup content. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'popup_content_fonts',
						'choices'          => [
							'font-family'    => 'popup_content_font',
							'font-size'      => 'popup_content_font_size',
							'text-transform' => 'popup_content_text_transform',
							'line-height'    => 'popup_content_line_height',
							'letter-spacing' => 'popup_content_letter_spacing',
							'color'          => 'popup_content_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'body_typography', 'color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Popup Content Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the popup content alignment.', 'fusion-builder' ),
						'param_name'  => 'popup_content_alignment',
						'default'     => '',
						'value'       => [
							''        => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'    => esc_attr__( 'Left', 'fusion-builder' ),
							'center'  => esc_attr__( 'Center', 'fusion-builder' ),
							'right'   => esc_attr__( 'Right', 'fusion-builder' ),
							'justify' => esc_attr__( 'Justify', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popup Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background color of the popup.', 'fusion-builder' ),
						'param_name'  => 'popup_background_color',
						'value'       => '',
						'default'     => 'var(--awb-color1)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popup Close Button Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background color of the popup close button.', 'fusion-builder' ),
						'param_name'  => 'popup_close_btn_color',
						'value'       => '',
						'default'     => 'var(--awb-color7)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Popup Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'popup_padding',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'popup_padding_top'    => '',
							'popup_padding_right'  => '',
							'popup_padding_bottom' => '',
							'popup_padding_left'   => '',
						],
					],
					'fusion_margin_placeholder'    => [
						'param_name' => 'margin',
						'heading'    => esc_attr__( 'Margin', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-openstreet-map',
					],
				],
				'parent',
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_openstreetmap' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_openstreetmap_marker() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_OpenStreetMap',
			[
				'name'              => esc_attr__( 'Marker', 'fusion-builder' ),
				'description'       => esc_attr__( 'Select the options for this openstreetmap marker.', 'fusion-builder' ),
				'shortcode'         => 'fusion_openstreetmap_marker',
				'hide_from_builder' => true,
				'allow_generator'   => false,
				'inline_editor'     => false,
				'show_ui'           => false,
				'subparam_map'      => [
					'border_radius_top_left'     => 'border_radius',
					'border_radius_top_right'    => 'border_radius',
					'border_radius_bottom_right' => 'border_radius',
					'border_radius_bottom_left'  => 'border_radius',
				],
				'params'            => [
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Title', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter the text to be displayed on the tooltip title.', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'          => 'nominatim_search',
						'heading'       => esc_attr__( 'Address', 'fusion-builder' ),
						'description'   => __( 'Enter the address then click search button. The latitude and longitude for the closest match will be added.', 'fusion-builder' ),
						'param_name'    => 'address',
						'value'         => '',
						'target_fields' => [
							'latitude',
							'longitude',
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Latitude', 'fusion-builder' ),
						'description'  => __( 'Enter the latitude of the address. You can use the address field above or use an online service to find coordinates. You can also adjust the location by dragging the marker.', 'fusion-builder' ),
						'param_name'   => 'latitude',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Longitude', 'fusion-builder' ),
						'description'  => __( 'Enter the longitude of the address. You can use the address field above or use an online service to find coordinates. You can also adjust the location by dragging the marker.', 'fusion-builder' ),
						'param_name'   => 'longitude',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'raw_textarea',
						'heading'      => esc_attr__( 'Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Enter the content.', 'fusion-builder' ),
						'param_name'   => 'tooltip_content',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Marker Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Select an icon to be displayed inside the map.', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => 'fa-map-marker-alt fas',
					],
					[
						'type'         => 'radio_button_set',
						'heading'      => esc_attr__( 'Popup Display Type', 'fusion-builder' ),
						'description'  => esc_attr__( 'Choose popup display type. Default will use parent option.' ),
						'param_name'   => 'action',
						'value'        => [
							''                 => esc_attr__( 'Default', 'fusion-builder' ),
							'popup'            => esc_attr__( 'Popup', 'fusion-builder' ),
							'tooltip'          => esc_attr__( 'Tooltip', 'fusion-builder' ),
							'static_close_on'  => esc_attr__( 'Static with Close', 'fusion-builder' ),
							'static_close_off' => esc_attr__( 'Static without Close', 'fusion-builder' ),
							'none'             => esc_attr__( 'None', 'fusion-builder' ),
						],
						'default'      => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icon Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the size of the icon. In pixels.', 'fusion-builder' ),
						'param_name'  => 'size',
						'value'       => '22',
						'min'         => '0',
						'max'         => '250',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the color of the text and the icon.', 'fusion-builder' ),
						'param_name'  => 'color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the background color of the marker icon.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_circle_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Hover Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the hover color of the text and the icon.', 'fusion-builder' ),
						'param_name'  => 'hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_color_hover' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Hover Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Select the background hover color of the marker icon.', 'fusion-builder' ),
						'param_name'  => 'hover_background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'icon_circle_color_hover' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_openstreetmap_marker' );
