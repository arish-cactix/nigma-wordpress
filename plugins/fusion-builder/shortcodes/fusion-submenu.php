<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.0
 */

if ( fusion_is_element_enabled( 'fusion_submenu' ) ) {

	if ( ! class_exists( 'FusionSC_SubMenu' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.0
		 */
		class FusionSC_SubMenu extends Fusion_Element {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.0
			 * @var array
			 */
			protected $defaults;

			/**
			 * Counter for elements.
			 *
			 * @access protected
			 * @since 3.0
			 * @var int
			 */
			protected $count = 0;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_submenu-shortcode', [ $this, 'attr' ] );

				add_shortcode( 'fusion_submenu', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_submenu', [ $this, 'ajax_query' ] );
			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 3.0
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults   = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $defaults, 'fusion_submenu' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$return_data['menu_markup'] = wp_nav_menu( $this->fetch_menu_args() );

				echo wp_json_encode( $return_data );

				wp_die();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'active_bg'                          => 'rgba(0,0,0,0)',
					'active_border_color'                => 'rgba(0,0,0,0)',
					'active_border_bottom'               => '0px',
					'active_border_left'                 => '0px',
					'active_border_right'                => '0px',
					'active_border_top'                  => '0px',
					'active_color'                       => '',
					'align_items'                        => 'stretch',
					'animation_direction'                => 'left',
					'animation_offset'                   => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                    => '',
					'animation_type'                     => '',
					'animation_delay'                    => '',
					'animation_color'                    => '',
					'bg'                                 => 'rgba(0,0,0,0)',
					'border_color'                       => 'rgba(0,0,0,0)',
					'border_bottom'                      => '0px',
					'border_right'                       => '0px',
					'border_top'                         => '0px',
					'border_left'                        => '0px',
					'border_radius_bottom_left'          => '0px',
					'border_radius_bottom_right'         => '0px',
					'border_radius_top_left'             => '0px',
					'border_radius_top_right'            => '0px',
					'box_shadow'                         => 'no',
					'box_shadow_blur'                    => '',
					'box_shadow_color'                   => '',
					'box_shadow_horizontal'              => '',
					'box_shadow_spread'                  => '',
					'box_shadow_style'                   => '',
					'box_shadow_vertical'                => '',
					'class'                              => '',
					'color'                              => '#212934',
					'direction'                          => 'row',
					'dropdown_carets'                    => 'yes',
					'expand_direction'                   => 'right',
					'expand_method'                      => 'hover',
					'expand_transition'                  => 'fade',
					'stacked_expand_method'              => 'click',
					'stacked_click_mode'                 => 'toggle',
					'stacked_submenu_indent'             => '',
					'submenu_mode'                       => 'dropdown',
					'font_size'                          => '16px',
					'fusion_font_family_submenu_typography' => 'inherit',
					'fusion_font_family_typography'      => 'inherit',
					'fusion_font_variant_submenu_typography' => '400',
					'fusion_font_variant_typography'     => '400',
					'gap'                                => '0px',
					'hide_on_mobile'                     => fusion_builder_default_visibility( 'string' ),
					'icons_color'                        => '#212934',
					'icons_hover_color'                  => '#65bc7b',
					'icons_position'                     => 'left',
					'icons_size'                         => '16',
					'id'                                 => '',
					'items_padding_bottom'               => '0px',
					'items_padding_left'                 => '0px',
					'items_padding_right'                => '0px',
					'items_padding_top'                  => '0px',
					'justify_content'                    => 'flex-start',
					'margin_bottom'                      => '0px',
					'margin_top'                         => '0px',
					'menu'                               => false,
					'min_height'                         => '4em',
					'sticky_display'                     => '',
					'sticky_min_height'                  => '',
					'submenu_max_width'                  => '',
					'submenu_active_bg'                  => '#f9f9fb',
					'submenu_active_color'               => '#212934',
					'flyout_close_color'                 => '#212934',
					'flyout_active_close_color'          => '#212934',
					'submenu_bg'                         => '#fff',
					'submenu_border_radius_bottom_left'  => '0px',
					'submenu_border_radius_bottom_right' => '0px',
					'submenu_border_radius_top_left'     => '0px',
					'submenu_border_radius_top_right'    => '0px',
					'submenu_color'                      => '#212934',
					'submenu_font_size'                  => '14px',
					'submenu_items_padding_bottom'       => '12px',
					'submenu_items_padding_left'         => '20px',
					'submenu_items_padding_right'        => '20px',
					'submenu_items_padding_top'          => '12px',
					'submenu_sep_color'                  => '#e2e2e2',
					'submenu_space'                      => '0px',
					'submenu_text_transform'             => '',
					'submenu_line_height'                => '',
					'submenu_letter_spacing'             => '',
					'sub_justify_content'                => 'space-between',
					'text_transform'                     => '',
					'line_height'                        => '',
					'thumbnail_size_height'              => '14px',
					'thumbnail_size_width'               => '26px',
					'transition_time'                    => '300',
					'transition_type'                    => 'fade',
					'main_justify_content'               => 'left',
					'letter_spacing'                     => '',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public static function settings_to_params() {
				return [];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$this->defaults = self::get_element_defaults();
				$defaults       = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_submenu' );
				$content        = apply_filters( 'fusion_shortcode_content', $content, 'fusion_submenu', $args );
				$this->args     = $defaults;
				$html           = '';

				// Use stacked expand method if menu is vertical and submenu mode is stacked.
				if ( 'column' === $this->args['direction'] && 'stacked' === $this->args['submenu_mode'] ) {
					$this->args['expand_method'] = $this->args['stacked_expand_method'];

					// Force click expand mode if submenu stacked is enabled and expand method is always.
					if ( 'always' === $this->args['expand_method'] ) {
						$this->args['expand_method'] = 'click';
					}
				}

				// Force opacity submenu transition for vertical menus.
				$this->args['expand_transition'] = 'row' !== $this->args['direction'] ? 'fade' : $this->args['expand_transition'];

				// For any variable font families, the variant comes from that.
				foreach ( [ 'typography', 'submenu_typography' ] as $typo_var ) {
					if ( false !== strpos( $this->args[ 'fusion_font_family_' . $typo_var ], 'var(' ) ) {
						$this->args[ 'fusion_font_variant_' . $typo_var ] = AWB_Global_Typography()->get_var_string( $this->args[ 'fusion_font_family_' . $typo_var ], 'font-weight' );
					}
				}

				if ( $this->args['menu'] ) {
					$menu = wp_get_nav_menus( $this->args['menu'] );

					$html .= '<nav ' . FusionBuilder::attributes( 'submenu-shortcode' ) . '>';

					$menu_markup = wp_nav_menu( $this->fetch_menu_args() );

					// Add the menu.
					$html .= $menu_markup;
					$html .= '</nav>';
				}

				$this->count++;

				$this->on_render();

				return apply_filters( 'fusion_element_submenu_content', $html, $args );
			}

			/**
			 * Fetch args for menu.
			 *
			 * @access public
			 * @since 3.0
			 * @param array $menu_args The menu arguments.
			 * @return array
			 */
			public function fetch_menu_args( $menu_args = [] ) {

				// Click mode with carets and no item spacing, we will need to add 0.5em space between caret and anchor.
				$click_mode_spacing = false;
				if ( 'yes' === $this->args['dropdown_carets'] && 'click' === $this->args['expand_method'] ) {
					$side = 'right';
					if ( 'column' !== $this->args['direction'] ) {
						$side = is_rtl() ? 'left' : 'right';
					} elseif ( $this->args['expand_direction'] ) {
						$side = $this->args['expand_direction'];
					}

					// Its empty, we need that 0.5em.
					if ( in_array( $this->args[ 'items_padding_' . $side ], [ '', '0', '0px' ], true ) ) {
						$click_mode_spacing = true;
					}
				}
				$direction             = isset( $menu_args['direction'] ) ? $menu_args['direction'] : $this->args['direction'];
				$submenu_mode          = isset( $this->args['submenu_mode'] ) ? $this->args['submenu_mode'] : 'dropdown';
				$expand_method         = isset( $menu_args['method'] ) ? $menu_args['method'] : $this->args['expand_method'];
				$stacked_expand_method = isset( $menu_args['stacked_method'] ) ? $menu_args['stacked_method'] : $this->args['stacked_expand_method'];

				if ( 'column' === $direction && 'stacked' === $submenu_mode ) {
					$expand_method = $stacked_expand_method;

					if ( 'always' === $expand_method ) {
						$expand_method = 'click';
					}
				}

				$main_menu_args = [
					'menu'         => $this->args['menu'],
					'depth'        => 5,
					'menu_class'   => 'fusion-menu awb-submenu__main-ul awb-submenu__main-ul_' . $this->args['direction'],
					'items_wrap'   => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'fallback_cb'  => 'AWB_Nav_Walker::fallback',
					'walker'       => new AWB_Nav_Walker(
						[
							'transition_type'       => $this->args['transition_type'],
							'menu_icon_position'    => $this->args['icons_position'],
							'click_spacing'         => $click_mode_spacing,
							'submenu'               => true,
							'direction'             => $direction,
							'submenu_mode'          => $submenu_mode,
							'expand_method'         => $expand_method,
							'stacked_expand_method' => $stacked_expand_method,
						]
					),
					'container'    => false,
					'item_spacing' => 'discard',
					'echo'         => false,
				];

				return $main_menu_args;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class'                => '',
					'style'                => '',
					'aria-label'           => 'Menu',
					'data-count'           => esc_attr( $this->count ),
					'data-transition-type' => esc_attr( $this->args['transition_type'] ),
					'data-transition-time' => esc_attr( $this->args['transition_time'] ),
				];

				$nav_classes = [
					'awb-submenu',
					'awb-submenu_' . $this->args['direction'],
					'awb-submenu_em-' . $this->args['expand_method'],
					'awb-submenu_icons-' . $this->args['icons_position'],
					'awb-submenu_dc-' . $this->args['dropdown_carets'],
					'awb-submenu_transition-' . $this->args['expand_transition'],
				];

				if ( 'stacked' === $this->args['submenu_mode'] && 'column' === $this->args['direction'] ) {
					$nav_classes[] = 'awb-submenu_v-stacked';

					if ( 'always' === $this->args['stacked_expand_method'] ) {
						$nav_classes[] = 'awb-submenu_em-always';
					}

					if ( 'click' === $this->args['stacked_expand_method'] ) {
						$nav_classes[] = 'awb-submenu_cm_' . $this->args['stacked_click_mode'];
					}
				} else {
					$nav_classes[] = 'awb-submenu_dropdown';
					$nav_classes[] = 'awb-submenu_expand-' . $this->args['expand_direction'];
				}

				$attr['class'] .= implode( ' ', $nav_classes );
				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['menu'] ) {
					$menu = wp_get_nav_menus( $this->args['menu'] );

					if ( is_object( $menu ) && isset( $menu->name ) ) {
							$attr['aria-label'] = $menu->name;
					}
				}

				$attr['data-breakpoint'] = '0';

				if ( '' !== $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( '' !== $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$this->args['main_justify_content'] = str_replace( [ 'left', 'right' ], [ 'flex-start', 'flex-end' ], $this->args['main_justify_content'] );
				$this->args['sub_justify_content']  = str_replace( [ 'left', 'right' ], [ 'flex-start', 'flex-end' ], $this->args['sub_justify_content'] );

				$variables = [
					'line_height',
					'transition_time',
					'text_transform',
					'align_items',
					'justify_content',
					'submenu_items_padding_top',
					'submenu_items_padding_right',
					'submenu_items_padding_bottom',
					'submenu_items_padding_left',
					'submenu_space',
					'submenu_text_transform',
					'submenu_line_height',
					'submenu_letter_spacing',
					'submenu_max_width',
					'icons_size',
					'main_justify_content',
					'sub_justify_content',
					'sticky_min_height',
					'stacked_submenu_indent',

					'bg'                                 => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'border_color'                       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'color'                              => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'active_color'                       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'active_bg'                          => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'active_border_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_color'                      => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_bg'                         => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_sep_color'                  => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_active_bg'                  => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_active_color'               => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icons_color'                        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icons_hover_color'                  => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'margin_top'                         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'items_padding_top'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'items_padding_bottom'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'items_padding_left'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'items_padding_right'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'gap'                                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'font_size'                          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'min_height'                         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_top'                         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_bottom'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_left'                        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_right'                       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_border_top'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_border_bottom'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_border_left'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_border_right'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_top_left'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_top_right'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_bottom_right'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_bottom_left'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_border_radius_top_left'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_border_radius_top_right'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_border_radius_bottom_right' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_border_radius_bottom_left'  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_space'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_items_padding_top'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_items_padding_bottom'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_items_padding_left'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_items_padding_right'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_font_size'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'thumbnail_size_width'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'thumbnail_size_height'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];

				$attr['style'] .= $this->get_css_vars_for_options( $variables );

				$css_vars_options = [];

				// Add box shadow as a full string.
				if ( 'yes' === $this->args['box_shadow'] ) {
					$css_vars_options['box_shadow'] = Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles( $this->args );
				}

				$typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'typography', 'array' );
				foreach ( $typography as $rule => $value ) {
					$css_vars_options[ 'fusion-' . $rule . '-typography' ] = $value;
				}

				$typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'submenu_typography', 'array' );
				foreach ( $typography as $rule => $value ) {
					$css_vars_options[ 'fusion-' . $rule . '-submenu-typography' ] = $value;
				}

				$attr['style'] .= $this->get_custom_css_vars( $css_vars_options );

				return $attr;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				$fusion_settings = awb_get_fusion_settings();
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/submenu.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/submenu-vertical.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/submenu-stacked.min.css' );
			}
		}
	}

	new FusionSC_SubMenu();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.0
 */
function fusion_element_submenu() {

	// Whether we are actually on an edit screen.
	$builder_status  = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
	$menu_options    = [];
	$menu_edit_items = [];

	// If we are on edit screen, fetch menu options.
	if ( $builder_status ) {
		$menus = wp_get_nav_menus();
		foreach ( $menus as $menu ) {
			$menu_options[ $menu->slug ]    = $menu->name;
			$menu_edit_items[ $menu->slug ] = $menu->term_id;
		}
	}

	$preview_active_root = [
		'selector' => '.awb-submenu__main-li_regular',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	$preview_active_submenu = [
		'selector' => '.awb-submenu__main-li_regular.menu-item-has-children, .awb-submenu__open-nav-submenu_click',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	$preview_active_submenu_item = [
		'selector' => '.awb-submenu__sub-a, .awb-submenu__sub-li',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	$params = [
		[
			'type'        => 'select',
			'heading'     => esc_html__( 'Menu', 'fusion-builder' ),
			'description' => esc_html__( 'Select the menu which you want to use.', 'fusion-builder' ),
			'param_name'  => 'menu',
			'value'       => $menu_options,
			'default'     => array_key_first( $menu_options ), // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_key_firstFound
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
			'quick_edit'  => [
				'label' => esc_html__( 'Edit Menu', 'fusion-builder' ),
				'type'  => 'menu',
				'items' => $menu_edit_items,
			],
		],
		[
			'type'        => 'checkbox_button_set',
			'heading'     => esc_html__( 'Element Visibility', 'fusion-builder' ),
			'param_name'  => 'hide_on_mobile',
			'value'       => fusion_builder_visibility_options( 'full' ),
			'default'     => fusion_builder_default_visibility( 'array' ),
			'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
		],
		'fusion_sticky_visibility_placeholder' => [],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Direction', 'fusion-builder' ),
			'param_name'  => 'direction',
			'value'       => [
				'row'    => esc_html__( 'Horizontal', 'fusion-builder' ),
				'column' => esc_html__( 'Vertical', 'fusion-builder' ),
			],
			'default'     => 'column',
			'description' => esc_html__( 'Choose to have a horizontal or a vertical menu.', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
		],
		'fusion_margin_placeholder'            => [
			'param_name'  => 'margin',
			'description' => esc_html__( 'Spacing above and below the section. Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
			'group'       => esc_html__( 'General', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
				'args'     => [

					'dimension' => true,
				],
			],
		],
		[
			'type'        => 'range',
			'heading'     => esc_html__( 'Transition Time', 'fusion-builder' ),
			'description' => esc_html__( 'Set the time for submenu expansion and all other hover transitions. In milliseconds.', 'fusion-builder' ),
			'param_name'  => 'transition_time',
			'value'       => '300',
			'min'         => '0',
			'max'         => '1000',
			'step'        => '1',
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Space Between Main Menu and Submenu', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the space between the main menu and dropdowns.', 'fusion-builder' ),
			'param_name'  => 'submenu_space',
			'value'       => '',
			'default'     => '',
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'CSS Class', 'fusion-builder' ),
			'param_name'  => 'class',
			'value'       => '',
			'description' => esc_html__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'CSS ID', 'fusion-builder' ),
			'param_name'  => 'id',
			'value'       => '',
			'description' => esc_html__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Minimum Height', 'fusion-builder' ),
			'description' => esc_html__( 'The minimum height for the main menu. Use any valid CSS unit.', 'fusion-builder' ),
			'param_name'  => 'min_height',
			'value'       => '',
			'default'     => '',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Sticky Minimum Height', 'fusion-builder' ),
			'description' => esc_html__( 'The minimum height for main menu links when the container is sticky. Use any valid CSS unit. ', 'fusion-builder' ),
			'param_name'  => 'sticky_min_height',
			'value'       => '',
			'dependency'  => [
				[
					'element'  => 'fusion_builder_container',
					'param'    => 'sticky',
					'value'    => 'on',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Align Items', 'fusion-builder' ),
			'description' => esc_html__( 'Select how main menu items will be aligned. Defines the default behavior for how flex items are laid out along the cross axis on the current line (perpendicular to the main-axis).', 'fusion-builder' ),
			'param_name'  => 'align_items',
			'default'     => 'stretch',
			'grid_layout' => true,
			'back_icons'  => true,
			'value'       => [
				'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'     => esc_html__( 'Center', 'fusion-builder' ),
				'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
				'stretch'    => esc_html__( 'Stretch', 'fusion-builder' ),
			],
			'icons'       => [
				'flex-start' => '<span class="fusiona-align-top-columns"></span>',
				'center'     => '<span class="fusiona-align-center-columns"></span>',
				'flex-end'   => '<span class="fusiona-align-bottom-columns"></span>',
				'stretch'    => '<span class="fusiona-full-height"></span>',
			],
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'row',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Justification', 'fusion-builder' ),
			'description' => esc_html__( 'Select how main menu items will be justified.', 'fusion-builder' ),
			'param_name'  => 'justify_content',
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
				// We use "start/end" terminology because flex direction changes depending on RTL/LTR.
				'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'        => esc_html__( 'Center', 'fusion-builder' ),
				'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
				'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
				'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
				'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
			],
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'row',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'             => 'typography',
			'heading'          => esc_attr__( 'Main Menu Typography', 'fusion-builder' ),
			'description'      => esc_html__( 'Controls the typography of the main menu item. Leave empty for the global font family.', 'fusion-builder' ),
			'param_name'       => 'main_menu_fonts',
			'choices'          => [
				'font-family'    => 'typography',
				'font-size'      => 'font_size',
				'text-transform' => 'text_transform',
				'line-height'    => 'line_height',
				'letter-spacing' => 'letter_spacing',
			],
			'default'          => [
				'font-family' => '',
				'variant'     => '400',
			],
			'remove_from_atts' => true,
			'global'           => true,
			'group'            => esc_attr__( 'Main', 'fusion-builder' ),
			'callback'         => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Main Menu Item Text Align', 'fusion-builder' ),
			'description' => esc_html__( 'Select if main menu items should be aligned to the left, right or centered.', 'fusion-builder' ),
			'param_name'  => 'main_justify_content',
			'grid_layout' => true,
			'back_icons'  => true,
			'value'       => [
				'left'          => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'        => esc_html__( 'Center', 'fusion-builder' ),
				'right'         => esc_html__( 'Flex End', 'fusion-builder' ),
				'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
			],
			'icons'       => [
				'left'          => '<span class="fusiona-horizontal-flex-start"></span>',
				'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
				'right'         => '<span class="fusiona-horizontal-flex-end"></span>',
				'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
			],
			'default'     => 'left',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'row',
					'operator' => '!=',
				],
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Main Menu Item Padding', 'fusion-builder' ),
			'description' => esc_html__( 'Select the padding for main menu items. Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
			'param_name'  => 'items_padding',
			'value'       => [
				'items_padding_top'    => '',
				'items_padding_right'  => '',
				'items_padding_bottom' => '',
				'items_padding_left'   => '',
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Main Menu Item Spacing', 'fusion-builder' ),
			'description' => esc_html__( 'The gap between main menu items. Use any valid CSS value, including its unit (10px, 4%, 1em etc).', 'fusion-builder' ),
			'param_name'  => 'gap',
			'value'       => '',
			'default'     => '',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_html__( 'Main Menu Item Border Radius', 'fusion-builder' ),
			'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'       => 'border_radius',
			'value'            => [
				'border_radius_top_left'     => '',
				'border_radius_top_right'    => '',
				'border_radius_bottom_right' => '',
				'border_radius_bottom_left'  => '',
			],
			'group'            => esc_html__( 'Main', 'fusion-builder' ),
			'callback'         => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'select',
			'heading'     => esc_html__( 'Main Menu Hover Transition', 'fusion-builder' ),
			'param_name'  => 'transition_type',
			'value'       => [
				'bottom-vertical' => esc_html__( 'Bottom', 'fusion-builder' ),
				'center'          => esc_html__( 'Center Horizontal', 'fusion-builder' ),
				'center-grow'     => esc_html__( 'Center Grow', 'fusion-builder' ),
				'center-vertical' => esc_html__( 'Center Vertical', 'fusion-builder' ),
				'fade'            => esc_html__( 'Fade', 'fusion-builder' ),
				'left'            => esc_html__( 'Left', 'fusion-builder' ),
				'right'           => esc_html__( 'Right', 'fusion-builder' ),
				'top-vertical'    => esc_html__( 'Top', 'fusion-builder' ),
			],
			'default'     => 'fade',
			'description' => esc_html__( 'Select the animation type when hovering the main menu items. This animation is applied to the background-color and borders.', 'fusion-builder' ),
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_update_menu_transition',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Main Menu Icon Position', 'fusion-builder' ),
			'param_name'  => 'icons_position',
			'value'       => [
				'top'    => esc_html__( 'Top', 'Avada' ),
				'right'  => esc_html__( 'Right', 'Avada' ),
				'bottom' => esc_html__( 'Bottom', 'Avada' ),
				'left'   => esc_html__( 'Left', 'Avada' ),
			],
			'default'     => 'left',
			'description' => esc_html__( 'Controls the main menu icon position.', 'fusion-builder' ),
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'range',
			'heading'     => esc_html__( 'Main Menu Icon Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the size of the main menu icons.', 'fusion-builder' ),
			'param_name'  => 'icons_size',
			'value'       => '16',
			'min'         => '10',
			'max'         => '100',
			'step'        => '1',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'             => 'subgroup',
			'heading'          => esc_html__( 'Main Menu Item Styling', 'fusion-builder' ),
			'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
			'param_name'       => 'main_styling',
			'default'          => 'regular',
			'group'            => esc_html__( 'Main', 'fusion-builder' ),
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
			'heading'     => esc_html__( 'Main Menu Item Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for main menu items.', 'fusion-builder' ),
			'param_name'  => 'bg',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0)',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the color for main menu item text color.', 'fusion-builder' ),
			'param_name'  => 'color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for main menu items hover / active states.', 'fusion-builder' ),
			'param_name'  => 'active_bg',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0)',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the color for main menu item text color hover / active states.', 'fusion-builder' ),
			'param_name'  => 'active_color',
			'value'       => '',
			'default'     => '#65bc7b',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Main Menu Item Border Size', 'fusion-builder' ),
			'description' => esc_html__( 'Select the border size for main menu items. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'  => 'border',
			'value'       => [
				'border_top'    => '',
				'border_right'  => '',
				'border_bottom' => '',
				'border_left'   => '',
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Border Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the border-color for main menu items.', 'fusion-builder' ),
			'param_name'  => 'border_color',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0)',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Border Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the border size for main menu items hover / active states. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'  => 'active_border',
			'value'       => [
				'active_border_top'    => '',
				'active_border_right'  => '',
				'active_border_bottom' => '',
				'active_border_left'   => '',
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Border Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the border-color for main menu items hover / active states.', 'fusion-builder' ),
			'param_name'  => 'active_border_color',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0)',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Icon Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the main menu icon color.', 'fusion-builder' ),
			'param_name'  => 'icons_color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Icon Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the main menu icon hover / active color.', 'fusion-builder' ),
			'param_name'  => 'icons_hover_color',
			'value'       => '',
			'default'     => '#65bc7b',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Dropdown Carets', 'fusion-builder' ),
			'description' => esc_html__( 'Select whether dropdown carets should show as submenu indicator.', 'fusion-builder' ),
			'param_name'  => 'dropdown_carets',
			'value'       => [
				'yes' => esc_html__( 'Yes', 'fusion-builder' ),
				'no'  => esc_html__( 'No', 'fusion-builder' ),
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
			'default'     => 'yes',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Submenu Mode', 'fusion-builder' ),
			'param_name'  => 'submenu_mode',
			'value'       => [
				'dropdown' => esc_html__( 'Dropdown', 'fusion-builder' ),
				'stacked'  => esc_html__( 'Stacked', 'fusion-builder' ),
			],
			'default'     => 'dropdown',
			'description' => esc_html__( 'Select whether you want a classic dropdown, or a stacked.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'column',
					'operator' => '==',
				],
			],

			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Expand Method', 'fusion-builder' ),
			'param_name'  => 'expand_method',
			'value'       => [
				'hover' => esc_html__( 'Hover', 'fusion-builder' ),
				'click' => esc_html__( 'Click', 'fusion-builder' ),
			],
			'default'     => 'hover',
			'description' => esc_html__( 'Select how submenus will expand. If carets are enabled, then they will become clickable.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Expand Method', 'fusion-builder' ),
			'param_name'  => 'stacked_expand_method',
			'value'       => [
				'hover'  => esc_html__( 'Hover', 'fusion-builder' ),
				'click'  => esc_html__( 'Click', 'fusion-builder' ),
				'always' => esc_html__( 'Always', 'fusion-builder' ),
			],
			'default'     => 'click',
			'description' => esc_html__( 'Select how submenus will expand. If carets are enabled, then they will become clickable.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'column',
					'operator' => '==',
				],
				[
					'element'  => 'submenu_mode',
					'value'    => 'stacked',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Click Method Mode', 'fusion-builder' ),
			'param_name'  => 'stacked_click_mode',
			'value'       => [
				'toggle'    => esc_html__( 'Toggle', 'fusion-builder' ),
				'accordion' => esc_html__( 'Accordion', 'fusion-builder' ),
			],
			'default'     => 'toggle',
			'description' => esc_html__( 'Select how the submenus should open. Toggle allows several items to be open at a time. Accordion only allows one item to be open at a time.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'column',
					'operator' => '==',
				],
				[
					'element'  => 'submenu_mode',
					'value'    => 'stacked',
					'operator' => '==',
				],
				[
					'element'  => 'stacked_expand_method',
					'value'    => 'click',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_submenu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Submenu Indent', 'fusion-builder' ),
			'param_name'  => 'stacked_submenu_indent',
			'description' => esc_html__( 'Set submenu indent. Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'column',
					'operator' => '==',
				],
				[
					'element'  => 'submenu_mode',
					'value'    => 'stacked',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Submenu Expand Direction', 'fusion-builder' ),
			'param_name'  => 'expand_direction',
			'value'       => [
				'left'  => esc_html__( 'Left', 'fusion-builder' ),
				'right' => esc_html__( 'Right', 'fusion-builder' ),
			],
			'default'     => ( is_rtl() ) ? 'left' : 'right',
			'description' => esc_html__( 'Changes the expand direction for submenus and vertical menus.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Submenu Expand Transition', 'fusion-builder' ),
			'param_name'  => 'expand_transition',
			'value'       => [
				'fade'       => esc_html__( 'Fade', 'fusion-builder' ),
				'slide_up'   => esc_html__( 'Slide Up', 'fusion-builder' ),
				'slide_down' => esc_html__( 'Slide Down', 'fusion-builder' ),
			],
			'default'     => 'fade',
			'description' => esc_html__( 'Changes the expand transition for submenus.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'row',
					'operator' => '==',
				],
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Submenu Maximum Width', 'fusion-builder' ),
			'description' => esc_html__( 'The maximum width for submenus. Use any valid CSS value.', 'fusion-builder' ),
			'param_name'  => 'submenu_max_width',
			'value'       => '',
			'default'     => '',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
		],
		[
			'type'             => 'typography',
			'heading'          => esc_attr__( 'Submenu Typography', 'fusion-builder' ),
			'description'      => esc_html__( 'Controls the typography of the submenu items. Leave empty for the global font family.', 'fusion-builder' ),
			'param_name'       => 'submenu_fonts',
			'choices'          => [
				'font-family'    => 'submenu_typography',
				'font-size'      => 'submenu_font_size',
				'text-transform' => 'submenu_text_transform',
				'line-height'    => 'submenu_line_height',
				'letter-spacing' => 'submenu_letter_spacing',
			],
			'default'          => [
				'font-family' => '',
				'variant'     => '400',
			],
			'remove_from_atts' => true,
			'global'           => true,
			'group'            => esc_attr__( 'Submenu', 'fusion-builder' ),
			'callback'         => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Submenu Item Text Align', 'fusion-builder' ),
			'description' => esc_html__( 'Select how the submenu text should be aligned.', 'fusion-builder' ),
			'param_name'  => 'sub_justify_content',
			'grid_layout' => true,
			'back_icons'  => true,
			'value'       => [
				'left'          => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'        => esc_html__( 'Center', 'fusion-builder' ),
				'right'         => esc_html__( 'Flex End', 'fusion-builder' ),
				'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
			],
			'icons'       => [
				'left'          => '<span class="fusiona-horizontal-flex-start"></span>',
				'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
				'right'         => '<span class="fusiona-horizontal-flex-end"></span>',
				'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
			],
			'default'     => 'space-between',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Submenu Item Padding', 'fusion-builder' ),
			'description' => esc_html__( 'Select the padding for submenu items. Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
			'param_name'  => 'submenu_items_padding',
			'value'       => [
				'submenu_items_padding_top'    => '',
				'submenu_items_padding_right'  => '',
				'submenu_items_padding_bottom' => '',
				'submenu_items_padding_left'   => '',
			],
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'preview'     => $preview_active_submenu,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_html__( 'Submenu Border Radius', 'fusion-builder' ),
			'description'      => __( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'       => 'submenu_border_radius',
			'value'            => [
				'submenu_border_radius_top_left'     => '',
				'submenu_border_radius_top_right'    => '',
				'submenu_border_radius_bottom_right' => '',
				'submenu_border_radius_bottom_left'  => '',
			],
			'group'            => esc_html__( 'Submenu', 'fusion-builder' ),
			'preview'          => $preview_active_submenu,
			'callback'         => [
				'function' => 'fusion_submenu',
			],
		],
		'fusion_box_shadow_placeholder'        => [
			'group'      => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency' => [
				[
					'element'  => 'box_shadow',
					'value'    => 'yes',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Submenu Thumbnail Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the width and height of the submenu image thumbnails. Use "auto" for automatic resizing if you added either width or height.', 'fusion-builder' ),
			'param_name'  => 'thumbnail_size',
			'value'       => [
				'thumbnail_size_width'  => '',
				'thumbnail_size_height' => '',
			],
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Separator Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the color for the submenu items separator. Set to transparent for no separator.', 'fusion-builder' ),
			'param_name'  => 'submenu_sep_color',
			'value'       => '',
			'default'     => '#e2e2e2',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'preview'     => $preview_active_submenu,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'             => 'subgroup',
			'heading'          => esc_html__( 'Submenu Item Styling', 'fusion-builder' ),
			'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
			'param_name'       => 'submenu_styling',
			'default'          => 'regular',
			'group'            => esc_html__( 'Submenu', 'fusion-builder' ),
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
			'heading'     => esc_html__( 'Submenu Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for submenu dropdowns.', 'fusion-builder' ),
			'param_name'  => 'submenu_bg',
			'value'       => '',
			'default'     => '#ffffff',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'regular',
			],
			'preview'     => $preview_active_submenu,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the text color for submenu dropdowns.', 'fusion-builder' ),
			'param_name'  => 'submenu_color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'regular',
			],
			'preview'     => $preview_active_submenu,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Hover / Active Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for submenu items hover / active states.', 'fusion-builder' ),
			'param_name'  => 'submenu_active_bg',
			'value'       => '',
			'default'     => '#f9f9fb',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_submenu_item,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Hover / Active Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the text color for submenu items hover / active states', 'fusion-builder' ),
			'param_name'  => 'submenu_active_color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_submenu_item,
			'callback'    => [
				'function' => 'fusion_submenu',
			],
		],
		'fusion_animation_placeholder'         => [
			'preview_selector' => '.awb-submenu',
		],
	];

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_SubMenu',
			[
				'name'         => esc_html__( 'Submenu', 'fusion-builder' ),
				'shortcode'    => 'fusion_submenu',
				'icon'         => 'fusiona-bars',
				'templates'    => [ 'mega_menus' ],
				'params'       => $params,
				'help_url'     => 'https://avada.com/documentation/submenu-element/',
				'subparam_map' => [
					'margin_top'                           => 'margin',
					'margin_bottom'                        => 'margin',
					'items_padding_top'                    => 'items_padding',
					'items_padding_right'                  => 'items_padding',
					'items_padding_bottom'                 => 'items_padding',
					'items_padding_left'                   => 'items_padding',
					'border_radius_top_left'               => 'border_radius',
					'border_radius_top_right'              => 'border_radius',
					'border_radius_bottom_right'           => 'border_radius',
					'border_radius_bottom_left'            => 'border_radius',
					'thumbnail_size_width'                 => 'thumbnail_size',
					'thumbnail_size_height'                => 'thumbnail_size',
					'border_top'                           => 'border',
					'border_right'                         => 'border',
					'border_bottom'                        => 'border',
					'border_left'                          => 'border',
					'submenu_items_padding_top'            => 'submenu_items_padding',
					'submenu_items_padding_right'          => 'submenu_items_padding',
					'submenu_items_padding_bottom'         => 'submenu_items_padding',
					'submenu_items_padding_left'           => 'submenu_items_padding',
					'submenu_border_radius_top_left'       => 'submenu_border_radius',
					'submenu_border_radius_top_right'      => 'submenu_border_radius',
					'submenu_border_radius_bottom_right'   => 'submenu_border_radius',
					'submenu_border_radius_bottom_left'    => 'submenu_border_radius',
					'trigger_padding_top'                  => 'trigger_padding',
					'trigger_padding_right'                => 'trigger_padding',
					'trigger_padding_bottom'               => 'trigger_padding',
					'trigger_padding_left'                 => 'trigger_padding',
					'font_size'                            => 'main_menu_fonts',
					'fusion_font_family_typography'        => 'main_menu_fonts',
					'fusion_font_variant_typography'       => 'main_menu_fonts',
					'letter_spacing'                       => 'main_menu_fonts',
					'text_transform'                       => 'main_menu_fonts',
					'line_height'                          => 'main_menu_fonts',
					'submenu_font_size'                    => 'submenu_fonts',
					'fusion_font_family_submenu_typography' => 'submenu_fonts',
					'fusion_font_variant_submenu_typography' => 'submenu_fonts',
					'submenu_text_transform'               => 'submenu_fonts',
					'submenu_line_height'                  => 'submenu_fonts',
					'submenu_letter_spacing'               => 'submenu_fonts',
					'mobile_font_size'                     => 'mobile_fonts',
					'fusion_font_family_mobile_typography' => 'mobile_fonts',
					'fusion_font_variant_mobile_typography' => 'mobile_fonts',
					'mobile_text_transform'                => 'mobile_fonts',
					'mobile_line_height'                   => 'mobile_fonts',
					'mobile_letter_spacing'                => 'mobile_fonts',
				],
				'callback'     => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_submenu',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_submenu' );
