<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.0
 */

if ( fusion_is_element_enabled( 'fusion_menu' ) ) {

	if ( ! class_exists( 'FusionSC_Menu' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.0
		 */
		class FusionSC_Menu extends Fusion_Element {

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
			 * Has the inline script already been added?
			 *
			 * @static
			 * @access protected
			 * @since 3.0
			 * @var bool
			 */
			protected static $inline_script_added = false;

			/**
			 * Markup for menu's overlay search.
			 *
			 * @static
			 * @access protected
			 * @since 3.0
			 * @var bool
			 */
			public static $overlay_search_markup = '';

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_menu-shortcode', [ $this, 'attr' ] );

				add_shortcode( 'fusion_menu', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_menu', [ $this, 'ajax_query' ] );

				add_action( 'wp_footer', [ $this, 'print_inline_script' ], 1 );
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
					$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $defaults, 'fusion_menu' );
					// Validate arrows.
					if ( is_array( $this->args['arrows'] ) ) {
						$this->args['arrows'] = implode( ',', $this->args['arrows'] );
					}
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$return_data['menu_markup'] = wp_nav_menu( $this->fetch_menu_args() );

				// Add search overlay form as direct child of <nav>.
				if ( '' !== self::$overlay_search_markup ) {
					$return_data['menu_markup'] = self::$overlay_search_markup . $return_data['menu_markup'];

					self::$overlay_search_markup = '';
				}

				$return_data['button_markup']        = $this->get_button();
				$return_data['flyout_button_markup'] = $this->get_flyout_button();

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
					'active_bg'                            => 'rgba(0,0,0,0)',
					'active_border_color'                  => 'rgba(0,0,0,0)',
					'active_border_bottom'                 => '0px',
					'active_border_left'                   => '0px',
					'active_border_right'                  => '0px',
					'active_border_top'                    => '0px',
					'active_color'                         => '',
					'align_items'                          => 'stretch',
					'animation_direction'                  => 'left',
					'animation_offset'                     => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                      => '',
					'animation_type'                       => '',
					'animation_color'                      => '',
					'arrows'                               => [ '' ],
					'arrows_size_height'                   => '12px',
					'arrows_size_width'                    => '23px',
					'bg'                                   => 'rgba(0,0,0,0)',
					'border_color'                         => 'rgba(0,0,0,0)',
					'border_bottom'                        => '0px',
					'border_right'                         => '0px',
					'border_top'                           => '0px',
					'border_left'                          => '0px',
					'border_radius_bottom_left'            => '0px',
					'border_radius_bottom_right'           => '0px',
					'border_radius_top_left'               => '0px',
					'border_radius_top_right'              => '0px',
					'box_shadow'                           => 'no',
					'box_shadow_blur'                      => '',
					'box_shadow_color'                     => '',
					'box_shadow_horizontal'                => '',
					'box_shadow_spread'                    => '',
					'box_shadow_style'                     => '',
					'box_shadow_vertical'                  => '',
					'breakpoint'                           => 'medium',
					'custom_breakpoint'                    => '800',
					'class'                                => '',
					'collapsed_mode'                       => 'dropdown',
					'collapsed_nav_icon_close'             => 'fa-bars fas',
					'collapsed_nav_icon_open'              => 'fa-times fas',
					'collapsed_nav_text'                   => '',
					'color'                                => '',
					'direction'                            => 'row',
					'dropdown_carets'                      => 'yes',
					'expand_direction'                     => 'right',
					'expand_method'                        => 'hover',
					'close_on_outer_click'                 => 'no',
					'close_on_outer_click_stacked'         => 'no',
					'stacked_expand_method'                => 'click',
					'stacked_click_mode'                   => 'toggle',
					'stacked_submenu_indent'               => '',
					'submenu_mode'                         => 'dropdown',
					'submenu_flyout_direction'             => 'fade',
					'expand_transition'                    => 'fade',
					'font_size'                            => '16px',
					'fusion_font_family_mobile_typography' => 'inherit',
					'fusion_font_family_submenu_typography' => 'inherit',
					'fusion_font_family_typography'        => 'inherit',
					'fusion_font_variant_mobile_typography' => '400',
					'fusion_font_variant_submenu_typography' => '400',
					'fusion_font_variant_typography'       => '400',
					'gap'                                  => '0px',
					'hide_on_mobile'                       => fusion_builder_default_visibility( 'string' ),
					'icons_color'                          => '#212934',
					'icons_hover_color'                    => '#65bc7b',
					'icons_position'                       => 'left',
					'icons_size'                           => '16',
					'id'                                   => '',
					'items_padding_bottom'                 => '0px',
					'items_padding_left'                   => '0px',
					'items_padding_right'                  => '0px',
					'items_padding_top'                    => '0px',
					'justify_content'                      => 'flex-start',
					'justify_title'                        => 'center',
					'margin_bottom'                        => '0px',
					'margin_top'                           => '0px',
					'menu'                                 => false,
					'min_height'                           => '4em',
					'mobile_active_bg'                     => '#f9f9fb',
					'mobile_active_color'                  => '#4a4e57',
					'mobile_sep_color'                     => 'rgba(0,0,0,0.1)',
					'mobile_bg'                            => '#ffffff',
					'mobile_color'                         => '#4a4e57',
					'mobile_font_size'                     => '1em',
					'mobile_text_transform'                => '',
					'mobile_line_height'                   => '',
					'mobile_letter_spacing'                => '',
					'mobile_trigger_font_size'             => '1em',
					'mobile_indent_submenu'                => 'on',
					'mobile_nav_button_align_hor'          => 'flex-start',
					'mobile_nav_items_height'              => '65',
					'mobile_nav_mode'                      => 'collapse-to-button',
					'mobile_nav_size'                      => 'full-absolute',
					'mobile_nav_trigger_fullwidth'         => 'off',
					'mobile_nav_trigger_bottom_margin'     => '0px',
					'mobile_trigger_background_color'      => '#ffffff',
					'mobile_trigger_color'                 => '#4a4e57',
					'sticky_display'                       => '',
					'sticky_min_height'                    => '',
					'submenu_max_width'                    => '',
					'submenu_active_bg'                    => '#f9f9fb',
					'submenu_active_color'                 => '#212934',
					'flyout_close_color'                   => '#212934',
					'flyout_active_close_color'            => '#212934',
					'submenu_bg'                           => '#fff',
					'submenu_border_radius_bottom_left'    => '0px',
					'submenu_border_radius_bottom_right'   => '0px',
					'submenu_border_radius_top_left'       => '0px',
					'submenu_border_radius_top_right'      => '0px',
					'submenu_color'                        => '#212934',
					'submenu_font_size'                    => '14px',
					'submenu_items_padding_bottom'         => '12px',
					'submenu_items_padding_left'           => '20px',
					'submenu_items_padding_right'          => '20px',
					'submenu_items_padding_top'            => '12px',
					'submenu_sep_color'                    => '#e2e2e2',
					'submenu_space'                        => '0px',
					'submenu_text_transform'               => '',
					'submenu_line_height'                  => '',
					'submenu_letter_spacing'               => '',
					'sub_justify_content'                  => 'space-between',
					'text_transform'                       => '',
					'line_height'                          => '',
					'thumbnail_size_height'                => '14px',
					'thumbnail_size_width'                 => '26px',
					'transition_time'                      => '300',
					'transition_type'                      => 'fade',
					'trigger_padding_bottom'               => '12px',
					'trigger_padding_left'                 => '20px',
					'trigger_padding_right'                => '20px',
					'trigger_padding_top'                  => '12px',
					'mobile_justify_content'               => 'left',
					'main_justify_content'                 => 'left',
					'letter_spacing'                       => '',
					'mobile_sticky_max_height'             => '',
					'mobile_opening_mode'                  => 'toggle',
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
				$defaults       = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_menu' );
				$content        = apply_filters( 'fusion_shortcode_content', $content, 'fusion_menu', $args );
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

				// Active, inherit from default.
				if ( '' === $this->args['active_color'] && '' !== $this->args['color'] ) {
					$this->args['active_color'] = $this->args['color'];
				}

				// Force click expand mode if submenu flyout is enabled.
				$this->args['expand_method'] = 'flyout' === $this->args['submenu_mode'] ? 'click' : $this->args['expand_method'];

				// Disable box shadow for flyout submenus.
				$this->args['box_shadow'] = 'flyout' === $this->args['submenu_mode'] ? 'no' : $this->args['box_shadow'];

				// Force opacity submenu transition for vertical menus.
				$this->args['expand_transition'] = 'row' !== $this->args['direction'] ? 'fade' : $this->args['expand_transition'];

				// For any variable font families, the variant comes from that.
				foreach ( [ 'typography', 'submenu_typography', 'mobile_typography' ] as $typo_var ) {
					if ( false !== strpos( $this->args[ 'fusion_font_family_' . $typo_var ], 'var(' ) ) {
						$this->args[ 'fusion_font_variant_' . $typo_var ] = AWB_Global_Typography()->get_var_string( $this->args[ 'fusion_font_family_' . $typo_var ], 'font-weight' );
					}
				}

				if ( $this->args['menu'] ) {
					$menu = wp_get_nav_menus( $this->args['menu'] );

					$html .= '<nav ' . FusionBuilder::attributes( 'menu-shortcode' ) . '>';

					$menu_markup = wp_nav_menu( $this->fetch_menu_args() );

					// Add search overlay form as direct child of <nav>.
					if ( '' !== self::$overlay_search_markup ) {
						$html .= self::$overlay_search_markup;

						self::$overlay_search_markup = '';
					}

					// Add button for mobile trigger if mobile is enabled.
					if ( 'never' !== $this->args['breakpoint'] ) {
						$html .= $this->get_button();
					}

					// Add close 'flyout' submenu button.
					if ( 'flyout' === $this->args['submenu_mode'] ) {
						$html .= $this->get_flyout_button();
					}

					// Add the menu.
					$html .= $menu_markup;
					$html .= '</nav>';
				}

				$this->count++;

				$this->on_render();

				return apply_filters( 'fusion_element_menu_content', $html, $args );
			}

			/**
			 * Print inline script.
			 *
			 * We're adding this one inline because it needs to run immediately
			 * before jQuery and other scripts load. Adding the script inline
			 * fixes the menu flashing on initial page-load and properly collapses the menus.
			 * We're using vanilla-JS here since this needs to be executed ASAP.
			 */
			public function print_inline_script() {
				if ( self::$inline_script_added ) {
					return;
				}

				echo '<script type="text/javascript">';
				echo fusion_file_get_contents( FUSION_BUILDER_PLUGIN_DIR . 'assets/js/min/general/fusion-menu-inline.js' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo '</script>';
				self::$inline_script_added = true;
			}

			/**
			 * Get the expand/collapse button.
			 *
			 * @access protected
			 * @since 3.0
			 * @return string
			 */
			protected function get_button() {
				$html = '';

				$trigger_class      = 'awb-menu__m-toggle';
				$collapsed_nav_text = $this->args['collapsed_nav_text'];
				$has_nav_text       = ! empty( $collapsed_nav_text );
				if ( ! $has_nav_text ) {
					$trigger_class      = 'awb-menu__m-toggle awb-menu__m-toggle_no-text';
					$collapsed_nav_text = '<span class="screen-reader-text">' . esc_html__( 'Toggle Navigation', 'fusion-builder' ) . '</span>';
				}

				// Start the button.
				$html .= '<button type="button" class="' . $trigger_class . '" aria-expanded="false" aria-controls="menu-' . $this->args['menu'] . '">';

				// We use a wrapper span because we set it to flex, so RTL & LTR both work properly
				// and the icon changes place automagically depending on language direction.
				$html .= '<span class="awb-menu__m-toggle-inner">';
				// The text.
				$html .= '<span class="collapsed-nav-text">' . $collapsed_nav_text . '</span>';
				// The icons.
				$html .= '<span class="awb-menu__m-collapse-icon' . ( ! $has_nav_text ? ' awb-menu__m-collapse-icon_no-text' : '' ) . '">';
				$html .= '<span class="awb-menu__m-collapse-icon-open ' . ( ! $has_nav_text ? 'awb-menu__m-collapse-icon-open_no-text ' : '' ) . fusion_font_awesome_name_handler( $this->args['collapsed_nav_icon_open'] ) . '"></span>';
				$html .= '<span class="awb-menu__m-collapse-icon-close ' . ( ! $has_nav_text ? 'awb-menu__m-collapse-icon-close_no-text ' : '' ) . fusion_font_awesome_name_handler( $this->args['collapsed_nav_icon_close'] ) . '"></span>';
				$html .= '</span>';
				// Close the wrapper.
				$html .= '</span>';

				// Close the button.
				$html .= '</button>';

				return $html;
			}


			/**
			 * Get the flyout close button.
			 *
			 * @access protected
			 * @since 3.0
			 * @return string
			 */
			protected function get_flyout_button() {
				return '<button type="button" class="awb-menu__flyout-close" onClick="fusionNavCloseFlyoutSub(this);"></button>';
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

				// We have arrows enabled for top level, either main or active and active border color is not transparent.
				$active_arrow_border = false;
				if ( ( false !== strpos( $this->args['arrows'], 'main' ) || false !== strpos( $this->args['arrows'], 'active' ) ) && ! Fusion_Color::new_color( $this->args['active_border_color'] )->is_color_transparent() ) {
					$direction = 'bottom';
					if ( 'column' === $this->args['direction'] ) {
						$direction = $this->args['expand_direction'];
					}

					if ( $this->args[ 'active_border_' . $direction ] && ! in_array( $this->args[ 'active_border_' . $direction ], [ '', '0', '0px' ], true ) ) {
						$active_arrow_border = true;
					}
				}

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
					if ( isset( $this->args[ 'items_padding_' . $side ] ) && in_array( $this->args[ 'items_padding_' . $side ], [ '', '0', '0px' ], true ) ) {
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
					'menu_class'   => 'fusion-menu awb-menu__main-ul awb-menu__main-ul_' . $this->args['direction'],
					'items_wrap'   => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'fallback_cb'  => 'AWB_Nav_Walker::fallback',
					'walker'       => new AWB_Nav_Walker(
						[
							'transition_type'       => $this->args['transition_type'],
							'direction'             => $direction,
							'submenu_mode'          => $submenu_mode,
							'expand_method'         => $expand_method,
							'expand_direction'      => isset( $menu_args['direction'] ) ? $menu_args['direction'] : $this->args['expand_direction'],
							'stacked_expand_method' => $stacked_expand_method,
							'menu_icon_position'    => $this->args['icons_position'],
							'arrows'                => $this->args['arrows'],
							'arrow_border'          => $active_arrow_border,
							'click_spacing'         => $click_mode_spacing,
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
					'data-breakpoint'      => $this->args['custom_breakpoint'],
					'data-count'           => esc_attr( $this->count ),
					'data-transition-type' => esc_attr( $this->args['transition_type'] ),
					'data-transition-time' => esc_attr( $this->args['transition_time'] ),
				];

				$nav_classes = [
					'awb-menu',
					'awb-menu_' . $this->args['direction'],
					'awb-menu_em-' . $this->args['expand_method'],
					'mobile-mode-' . $this->args['mobile_nav_mode'],
					'awb-menu_icons-' . $this->args['icons_position'],
					'awb-menu_dc-' . $this->args['dropdown_carets'],
					'mobile-trigger-fullwidth-' . $this->args['mobile_nav_trigger_fullwidth'],
					'awb-menu_mobile-' . $this->args['mobile_opening_mode'],
				];

				if ( 'on' === $this->args['mobile_indent_submenu'] ) {
					$nav_classes[] = 'awb-menu_indent-' . $this->args['mobile_justify_content'];
				}

				if ( 'on' === $this->args['mobile_nav_trigger_fullwidth'] ) {
					$nav_classes[] = 'awb-menu_mt-fullwidth';
				}

				$nav_classes[] = 'yes' === $this->args['close_on_outer_click_stacked'] || 'yes' === $this->args['close_on_outer_click'] ? 'close-on-outer-click-yes' : '';

				// The size options are only relevant for collapse to button.
				if ( 'collapse-to-button' === $this->args['mobile_nav_mode'] ) {
					$nav_classes[] = 'mobile-size-' . $this->args['mobile_nav_size'];
				}

				// Don't add loading class in live builder, see Fusion-Builder #4365.
				if ( ! ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) ) {
					$nav_classes[] = 'loading mega-menu-loading';
				}

				// If we have a breakpoint and load is a mobile, set to default as collapse-enabled.
				if ( 'never' !== $this->args['breakpoint'] && wp_is_mobile() ) {
					$nav_classes[] = 'collapse-enabled';
				} else {
					$nav_classes[] = 'awb-menu_desktop';
				}
				if ( is_array( $this->args['arrows'] ) ) {
					$this->args['arrows'] = implode( ',', $this->args['arrows'] );
				}

				if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
					$nav_classes[] = 'awb-menu_arrows-active';
				}

				if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
					$nav_classes[] = 'awb-menu_arrows-main';
				}

				if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
					$nav_classes[] = 'awb-menu_arrows-sub';
				}

				if ( 'flyout' === $this->args['submenu_mode'] ) {
					$nav_classes[] = 'awb-menu_flyout';
					$nav_classes[] = 'awb-menu_flyout__' . $this->args['submenu_flyout_direction'];
				} elseif ( 'stacked' === $this->args['submenu_mode'] && 'column' === $this->args['direction'] ) {
					$nav_classes[] = 'awb-menu_v-stacked';

					if ( 'always' === $this->args['stacked_expand_method'] ) {
						$nav_classes[] = 'awb-menu_em-always';
					}

					if ( 'click' === $this->args['stacked_expand_method'] ) {
						$nav_classes[] = 'awb-submenu_cm_' . $this->args['stacked_click_mode'];
					}
				} else {
					$nav_classes[]       = 'awb-menu_dropdown';
					$nav_classes[]       = 'awb-menu_expand-' . $this->args['expand_direction'];
					$attr['data-expand'] = $this->args['expand_direction'];
				}

				if ( 'dropdown' === $this->args['submenu_mode'] ) {
					$nav_classes[] = 'awb-menu_transition-' . $this->args['expand_transition'];
				}

				$attr['class'] .= implode( ' ', $nav_classes );
				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['menu'] ) {
					$menu = wp_get_nav_menus( [ 'slug' => $this->args['menu'] ] );

					if ( isset( $menu[0] ) && isset( $menu[0]->name ) ) {
							$attr['aria-label'] = $menu[0]->name;
					}
				}

				if ( 'never' === $this->args['breakpoint'] ) {
					$attr['data-breakpoint'] = '0';
				} elseif ( 'small' === $this->args['breakpoint'] ) {
					$attr['data-breakpoint'] = fusion_library()->get_option( 'visibility_small' );
				} elseif ( 'medium' === $this->args['breakpoint'] ) {
					$attr['data-breakpoint'] = fusion_library()->get_option( 'visibility_medium' );
				} elseif ( 'large' === $this->args['breakpoint'] ) {
					$attr['data-breakpoint'] = 10000;
				}

				if ( '' !== $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( '' !== $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$this->args['main_justify_content'] = str_replace( [ 'left', 'right' ], [ 'flex-start', 'flex-end' ], $this->args['main_justify_content'] );
				$this->args['sub_justify_content']  = str_replace( [ 'left', 'right' ], [ 'flex-start', 'flex-end' ], $this->args['sub_justify_content'] );

				$attr['style'] .= $this->get_style_variables();

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

				// Mobile justification, does not match the option definition depending on rtl or not.
				$custom_vars['mobile_justify']     = $this->args['mobile_justify_content'];
				$custom_vars['mobile_caret_left']  = 'auto';
				$custom_vars['mobile_caret_right'] = '0';

				if ( is_rtl() ) {
					$custom_vars['mobile_justify'] = str_replace( [ 'left', 'right' ], [ 'flex-end', 'flex-start' ], $custom_vars['mobile_justify'] );
					if ( 'flex-end' !== $custom_vars['mobile_justify'] ) {
						$custom_vars['mobile_caret_left']  = '0';
						$custom_vars['mobile_caret_right'] = 'auto';
					}
				} else {
					$custom_vars['mobile_justify'] = str_replace( [ 'left', 'right' ], [ 'flex-start', 'flex-end' ], $custom_vars['mobile_justify'] );
					if ( 'flex-end' === $custom_vars['mobile_justify'] ) {
						$custom_vars['mobile_caret_left']  = '0';
						$custom_vars['mobile_caret_right'] = 'auto';
					}
				}

				// Add box shadow as a full string.
				if ( 'yes' === $this->args['box_shadow'] ) {
					$custom_vars['box_shadow'] = Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles( $this->args );
				}

				$typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'typography', 'array' );
				foreach ( $typography as $rule => $value ) {
					$custom_vars[ 'fusion-' . $rule . '-typography' ] = $value;
				}

				$typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'submenu_typography', 'array' );
				foreach ( $typography as $rule => $value ) {
					$custom_vars[ 'fusion-' . $rule . '-submenu-typography' ] = $value;
				}

				$typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'mobile_typography', 'array' );
				foreach ( $typography as $rule => $value ) {
					$custom_vars[ 'fusion-' . $rule . '-mobile-typography' ] = $value;
				}

				$css_vars_options = [
					'font_size'                          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'line_height',
					'margin_top'                         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'margin_bottom'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'transition_time',
					'text_transform',
					'min_height'                         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'bg'                                 => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'border_radius_top_left'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_top_right'            => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_bottom_right'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_radius_bottom_left'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'gap'                                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'align_items',
					'justify_content',
					'items_padding_top'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'items_padding_right'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'items_padding_bottom'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'items_padding_left'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_color'                       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'border_top'                         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_right'                       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_bottom'                      => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'border_left'                        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'color'                              => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'letter_spacing'                     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_color'                       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'active_bg'                          => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'active_border_top'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_border_right'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_border_bottom'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_border_left'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'active_border_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_color'                      => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_bg'                         => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_sep_color'                  => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_items_padding_top'          => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_items_padding_right'        => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_items_padding_bottom'       => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_items_padding_left'         => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_border_radius_top_left'     => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_border_radius_top_right'    => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_border_radius_bottom_right' => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_border_radius_bottom_left'  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_active_bg'                  => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_active_color'               => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'submenu_space',
					'submenu_font_size'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_text_transform',
					'submenu_line_height',
					'submenu_letter_spacing'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'submenu_max_width'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'icons_size',
					'icons_color'                        => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'icons_hover_color'                  => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'arrows_size_height'                 => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'arrows_size_width'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'main_justify_content',
					'sub_justify_content',
					'mobile_nav_button_align_hor',
					'mobile_bg'                          => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'mobile_color'                       => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'mobile_nav_items_height'            => [ 'callback' => [ 'Fusion_Sanitize', 'number' ] ],
					'mobile_active_bg'                   => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'mobile_active_color'                => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'mobile_trigger_font_size'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'trigger_padding_top'                => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'trigger_padding_right'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'trigger_padding_bottom'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'trigger_padding_left'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'mobile_trigger_color'               => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'mobile_trigger_background_color'    => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'mobile_nav_trigger_bottom_margin'   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'mobile_font_size'                   => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'mobile_text_transform',
					'mobile_line_height',
					'mobile_letter_spacing'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'mobile_sep_color'                   => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'flyout_close_color'                 => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'flyout_active_close_color'          => [ 'callback' => [ 'Fusion_Sanitize', 'color' ] ],
					'mobile_sticky_max_height'           => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'justify_title',
					'thumbnail_size_width'               => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'thumbnail_size_height'              => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'sticky_min_height'                  => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
					'stacked_submenu_indent'             => [ 'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ] ],
				];

				$styles = $this->get_css_vars_for_options( $css_vars_options ) . $this->get_custom_css_vars( $custom_vars );

				return $styles;
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
					'fusion-menu',
					FusionBuilder::$js_folder_url . '/general/fusion-menu.js',
					FusionBuilder::$js_folder_path . '/general/fusion-menu.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);

				Fusion_Dynamic_JS::enqueue_script(
					'awb-mega-menu',
					FusionBuilder::$js_folder_url . '/general/awb-mega-menu.js',
					FusionBuilder::$js_folder_path . '/general/awb-mega-menu.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);

				if ( $fusion_settings->get( 'disable_megamenu' ) ) {
					Fusion_Dynamic_JS::enqueue_script(
						'fusion-legacy-mega-menu',
						FusionBuilder::$js_folder_url . '/general/fusion-legacy-mega-menu.js',
						FusionBuilder::$js_folder_path . '/general/fusion-legacy-mega-menu.js',
						[ 'jquery', 'fusion-menu' ],
						FUSION_BUILDER_VERSION,
						true
					);
				}

				Fusion_Dynamic_JS::localize_script(
					'fusion-menu',
					'fusionMenuVars',
					[
						/* Translators: The submenu title. */
						'mobile_submenu_open' => esc_attr__( 'Open submenu of %s', 'Avada' ),
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
				$fusion_settings = awb_get_fusion_settings();
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-arrows.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-vertical.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-stacked.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-mobile.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-woo.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-search.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-flyout.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-mega.min.css' );

				if ( $fusion_settings->get( 'disable_megamenu' ) ) {
					FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu-mega-legacy.min.css' );
				}
			}
		}
	}

	new FusionSC_Menu();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.0
 */
function fusion_element_menu() {

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
		'selector' => '.awb-menu__main-li_regular:nth-child(2)',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	$preview_active_submenu = [
		'selector' => '.awb-menu__main-li_regular.menu-item-has-children, .awb-menu__open-nav-submenu_click',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	$preview_active_submenu_item = [
		'selector' => '.awb-menu__sub-a, .awb-menu__sub-li',
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
				'action'   => 'get_fusion_menu',
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
			'default'     => 'row',
			'description' => esc_html__( 'Choose to have a horizontal or a vertical menu.', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
		],
		'fusion_margin_placeholder'            => [
			'param_name'  => 'margin',
			'description' => esc_html__( 'Spacing above and below the section. Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
			'group'       => esc_html__( 'General', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
			'type'        => 'checkbox_button_set',
			'heading'     => esc_html__( 'Menu Arrows', 'fusion-builder' ),
			'param_name'  => 'arrows',
			'value'       => [
				'main'    => esc_html__( 'Main', 'fusion-builder' ),
				'active'  => esc_html__( 'Main Active', 'fusion-builder' ),
				'submenu' => esc_html__( 'Submenu', 'fusion-builder' ),
			],
			'default'     => [ '' ],
			'description' => esc_html__( 'Choose if you want to show dropdown arrows on the main menu and submenus.', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
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
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Arrow Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the width and height of the arrows.', 'fusion-builder' ),
			'param_name'  => 'arrows_size',
			'value'       => [
				'arrows_size_width'  => '',
				'arrows_size_height' => '',
			],
			'dependency'  => [
				[
					'element'  => 'arrows',
					'value'    => '',
					'operator' => '!=',
				],
			],
			'callback'    => [
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
				'action'   => 'get_fusion_menu',
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
				'function' => 'fusion_menu',
			],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Main Menu Item Background Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the background-color for main menu items.', 'fusion-builder' ),
			'param_name'    => 'bg',
			'value'         => '',
			'default'       => 'rgba(0,0,0,0)',
			'group'         => esc_html__( 'Main', 'fusion-builder' ),
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'hover' => [
					'label'      => __( 'Hover / Active', 'fusion-builder' ),
					'param_name' => 'active_bg',
					'preview'    => $preview_active_root,
				],
			],
			'connect-state' => [ 'color', 'border', 'border_color', 'icons_color' ],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Main Menu Item Text Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the color for main menu item text color.', 'fusion-builder' ),
			'param_name'    => 'color',
			'value'         => '',
			'default'       => '#212934',
			'group'         => esc_html__( 'Main', 'fusion-builder' ),
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'hover' => [
					'label'      => __( 'Hover / Active', 'fusion-builder' ),
					'default'    => '#65bc7b',
					'param_name' => 'active_color',
					'preview'    => $preview_active_root,
				],
			],
			'connect-state' => [ 'bg', 'border', 'border_color', 'icons_color' ],
		],
		[
			'type'          => 'dimension',
			'heading'       => esc_html__( 'Main Menu Item Border Size', 'fusion-builder' ),
			'description'   => esc_html__( 'Select the border size for main menu items. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'    => 'border',
			'value'         => [
				'border_top'    => '',
				'border_right'  => '',
				'border_bottom' => '',
				'border_left'   => '',
			],
			'group'         => esc_html__( 'Main', 'fusion-builder' ),
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'hover' => [
					'label'      => __( 'Hover / Active', 'fusion-builder' ),
					'param_name' => 'active_border',
					'preview'    => $preview_active_root,
					'value'      => [
						'active_border_top'    => '',
						'active_border_right'  => '',
						'active_border_bottom' => '',
						'active_border_left'   => '',
					],
					'callback'   => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_menu',
						'ajax'     => true,
					],
				],
			],
			'connect-state' => [ 'bg', 'color', 'border_color', 'icons_color' ],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Main Menu Item Border Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the border-color for main menu items.', 'fusion-builder' ),
			'param_name'    => 'border_color',
			'value'         => '',
			'default'       => 'rgba(0,0,0,0)',
			'group'         => esc_html__( 'Main', 'fusion-builder' ),
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'hover' => [
					'label'      => __( 'Hover / Active', 'fusion-builder' ),
					'param_name' => 'active_border_color',
					'preview'    => $preview_active_root,
					'callback'   => [
						'function' => 'fusion_menu',
					],
				],
			],
			'connect-state' => [ 'bg', 'color', 'border', 'icons_color' ],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Main Menu Item Icon Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the main menu icon color.', 'fusion-builder' ),
			'param_name'    => 'icons_color',
			'value'         => '',
			'default'       => '#212934',
			'group'         => esc_html__( 'Main', 'fusion-builder' ),
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'hover' => [
					'label'      => __( 'Hover / Active', 'fusion-builder' ),
					'param_name' => 'icons_hover_color',
					'default'    => '',
					'preview'    => $preview_active_root,
					'callback'   => [
						'function' => 'fusion_menu',
					],
				],
			],
			'connect-state' => [ 'bg', 'color', 'border', 'border_color' ],
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
				'action'   => 'get_fusion_menu',
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
				'flyout'   => esc_html__( 'Flyout', 'fusion-builder' ),
				'stacked'  => [
					'name'       => esc_html__( 'Stacked', 'fusion-builder' ),
					'dependency' => [
						'element'  => 'direction',
						'value'    => 'column',
						'operator' => '==',
					],
				],
			],
			'default'     => 'dropdown',
			'description' => esc_html__( 'Select whether you want a classic dropdown, or a full-screen flyout.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
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
				'action'   => 'get_fusion_menu',
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
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Close On Outer Click', 'fusion-builder' ),
			'param_name'  => 'close_on_outer_click',
			'value'       => [
				'yes' => esc_html__( 'Yes', 'fusion-builder' ),
				'no'  => esc_html__( 'No', 'fusion-builder' ),
			],
			'default'     => 'no',
			'description' => esc_html__( 'Select if submenu should be closed on click outside the section.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
				[
					'element'  => 'expand_method',
					'value'    => 'click',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Close On Outer Click', 'fusion-builder' ),
			'param_name'  => 'close_on_outer_click_stacked',
			'value'       => [
				'yes' => esc_html__( 'Yes', 'fusion-builder' ),
				'no'  => esc_html__( 'No', 'fusion-builder' ),
			],
			'default'     => 'no',
			'description' => esc_html__( 'Select if submenu should be closed on click outside the section.', 'fusion-builder' ),
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
				'action'   => 'get_fusion_menu',
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
			'description' => esc_html__( 'Changes the expand direction for submenus and vertical menus.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'param_name'  => 'expand_direction',
			'value'       => [
				'left'   => esc_html__( 'Left', 'fusion-builder' ),
				'center' => esc_html__( 'Center', 'fusion-builder' ),
				'right'  => esc_html__( 'Right', 'fusion-builder' ),
			],
			'default'     => ( is_rtl() ) ? 'left' : 'right',
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
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
				[
					'element'  => 'direction',
					'value'    => 'row',
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
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Flyout Direction', 'fusion-builder' ),
			'param_name'  => 'submenu_flyout_direction',
			'value'       => [
				'fade'   => esc_html__( 'Fade', 'Avada' ),
				'left'   => esc_html__( 'Left', 'fusion-builder' ),
				'right'  => esc_html__( 'Right', 'fusion-builder' ),
				'bottom' => esc_html__( 'Bottom', 'Avada' ),
				'top'    => esc_html__( 'Top', 'Avada' ),
			],
			'default'     => 'fade',
			'description' => esc_html__( 'Controls the direction the flyout submenu starts from.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'flyout',
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
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
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
			'dependency'       => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
			'callback'         => [
				'function' => 'fusion_menu',
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
				[
					'element'  => 'submenu_mode',
					'value'    => 'flyout',
					'operator' => '!=',
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
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Legacy Mega Menu Title Justification', 'fusion-builder' ),
			'description' => esc_html__( 'Select how legacy mega menu titles will be justified.', 'fusion-builder' ),
			'param_name'  => 'justify_title',
			'default'     => 'center',
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
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
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
				'function' => 'fusion_menu',
			],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Submenu Background Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the background-color for submenu dropdowns.', 'fusion-builder' ),
			'param_name'    => 'submenu_bg',
			'value'         => '',
			'default'       => '#ffffff',
			'group'         => esc_html__( 'Submenu', 'fusion-builder' ),
			'preview'       => $preview_active_submenu,
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'hover' => [
					'label'      => __( 'Hover / Active', 'fusion-builder' ),
					'param_name' => 'submenu_active_bg',
					'default'    => '#f9f9fb',
					'preview'    => $preview_active_submenu_item,
				],
			],
			'connect-state' => [ 'submenu_color', 'flyout_close_color' ],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Submenu Text Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the text color for submenu dropdowns.', 'fusion-builder' ),
			'param_name'    => 'submenu_color',
			'value'         => '',
			'default'       => '#212934',
			'group'         => esc_html__( 'Submenu', 'fusion-builder' ),
			'preview'       => $preview_active_submenu,
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'hover' => [
					'label'      => __( 'Hover / Active', 'fusion-builder' ),
					'param_name' => 'submenu_active_color',
					'default'    => '#212934',
					'preview'    => $preview_active_submenu_item,
				],
			],
			'connect-state' => [ 'submenu_bg', 'flyout_close_color' ],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Close Icon Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the close icon color for flyout submenu.', 'fusion-builder' ),
			'param_name'    => 'flyout_close_color',
			'value'         => '',
			'default'       => '#212934',
			'group'         => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'    => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'flyout',
					'operator' => '==',
				],
			],
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'hover' => [
					'label'      => __( 'Hover / Active', 'fusion-builder' ),
					'param_name' => 'flyout_active_close_color',
					'default'    => '#212934',
					'preview'    => $preview_active_submenu_item,
				],
			],
			'connect-state' => [ 'submenu_bg', 'submenu_color' ],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Collapse to Mobile Breakpoint', 'fusion-builder' ),
			'description' => esc_html__( 'The breakpoint at which your navigation will collapse to mobile mode.', 'fusion-builder' ),
			'param_name'  => 'breakpoint',
			'value'       => [
				'never'  => esc_html__( 'Never', 'fusion-builder' ),
				'small'  => esc_html__( 'Small Screen', 'fusion-builder' ),
				'medium' => esc_html__( 'Medium Screen', 'fusion-builder' ),
				'large'  => esc_html__( 'Large Screen', 'fusion-builder' ),
				'custom' => esc_html__( 'Custom', 'fusion-builder' ),
			],
			'icons'       => [
				'never'  => '<span class="fusiona-close-fb onlyIcon"></span>',
				'small'  => '<span class="fusiona-mobile onlyIcon"></span>',
				'medium' => '<span class="fusiona-tablet onlyIcon"></span>',
				'large'  => '<span class="fusiona-desktop onlyIcon"></span>',
				'custom' => '<span class="fusiona-cog onlyIcon"></span>',
			],
			'default'     => 'medium',
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'range',
			'heading'     => esc_html__( 'Collapse to Mobile Breakpoint', 'fusion-builder' ),
			'description' => esc_html__( 'The breakpoint at which your menu will collapse to mobile mode. In pixels.', 'fusion-builder' ),
			'param_name'  => 'custom_breakpoint',
			'value'       => '800',
			'min'         => '360',
			'max'         => '2000',
			'step'        => '1',
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'dependency'  => [
				[
					'element'  => 'breakpoint',
					'value'    => 'custom',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Mode', 'fusion-builder' ),
			'description' => esc_html__( 'Choose if you want the mobile menu to be collapsed to a button, or always expanded.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_mode',
			'value'       => [
				'collapse-to-button' => esc_html__( 'Collapsed', 'fusion-builder' ),
				'always-expanded'    => esc_html__( 'Expanded', 'fusion-builder' ),
			],
			'default'     => 'collapse-to-button',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'select',
			'heading'     => esc_html__( 'Mobile Menu Expand Mode', 'fusion-builder' ),
			'description' => esc_html__( 'Change the width and position of expanded mobile menus.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_size',
			'value'       => [
				'column-relative' => esc_html__( 'Within Column - Normal', 'fusion-builder' ),
				'column-absolute' => esc_html__( 'Within Column - Static', 'fusion-builder' ),
				'full-absolute'   => esc_html__( 'Full Width - Static', 'fusion-builder' ),
			],
			'default'     => 'full-absolute',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Opening Mode', 'fusion-builder' ),
			'description' => esc_html__( 'Select how the submenus should open. Toggle allows several items to be open at a time. Accordion only allows one item to be open at a time.', 'fusion-builder' ),
			'param_name'  => 'mobile_opening_mode',
			'value'       => [
				'toggle'    => esc_html__( 'Toggle', 'fusion-builder' ),
				'accordion' => esc_html__( 'Accordion', 'fusion-builder' ),
			],
			'default'     => 'toggle',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Mobile Menu Trigger Padding', 'fusion-builder' ),
			'description' => esc_html__( 'Select the padding for your mobile menu\'s expand / collapse button. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'  => 'trigger_padding',
			'value'       => [
				'trigger_padding_top'    => '',
				'trigger_padding_right'  => '',
				'trigger_padding_bottom' => '',
				'trigger_padding_left'   => '',
			],
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Trigger Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for the mobile menu trigger.', 'fusion-builder' ),
			'param_name'  => 'mobile_trigger_background_color',
			'value'       => '',
			'default'     => '#ffffff',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Trigger Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the text-color for the mobile menu trigger.', 'fusion-builder' ),
			'param_name'  => 'mobile_trigger_color',
			'value'       => '',
			'default'     => '#4a4e57',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Mobile Menu Trigger Text', 'fusion-builder' ),
			'description' => esc_html__( 'The text shown next to the mobile menu trigger icon.', 'fusion-builder' ),
			'param_name'  => 'collapsed_nav_text',
			'value'       => '',
			'default'     => '',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'iconpicker',
			'heading'     => esc_html__( 'Mobile Menu Trigger Expand Icon', 'fusion-builder' ),
			'param_name'  => 'collapsed_nav_icon_open',
			'value'       => 'fa-bars fas',
			'default'     => 'fa-bars fas',
			'description' => esc_html__( 'Select icon for expanding / opening the menu.', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'iconpicker',
			'heading'     => esc_html__( 'Mobile Menu Trigger Collapse Icon', 'fusion-builder' ),
			'param_name'  => 'collapsed_nav_icon_close',
			'value'       => 'fa-times fas',
			'default'     => 'fa-times fas',
			'description' => esc_html__( 'Select icon for collapsing / closing the menu.', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Mobile Menu Trigger Font Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the size of the mobile menu trigger. Font-Size In pixels.', 'fusion-builder' ),
			'param_name'  => 'mobile_trigger_font_size',
			'value'       => '',
			'default'     => '',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Trigger Horizontal Align', 'fusion-builder' ),
			'description' => esc_html__( 'Change the horizontal alignment of the collapse / expand button within its container column.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_button_align_hor',
			'grid_layout' => true,
			'back_icons'  => true,
			'icons'       => [
				'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
				'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
				'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
			],
			'value'       => [
				'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'     => esc_html__( 'Center', 'fusion-builder' ),
				'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
			],
			'default'     => 'flex-start',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Trigger Button Full Width', 'fusion-builder' ),
			'description' => esc_html__( 'Turn on to make the mobile menu trigger button span full-width.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_trigger_fullwidth',
			'value'       => [
				'on'  => esc_html__( 'On', 'fusion-builder' ),
				'off' => esc_html__( 'Off', 'fusion-builder' ),
			],
			'default'     => 'off',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Mobile Menu Trigger Bottom Margin', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the space between the mobile menu trigger and expanded mobile menu.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_trigger_bottom_margin',
			'value'       => '',
			'default'     => '',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'range',
			'heading'     => esc_html__( 'Mobile Menu Item Minimum Height', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the height of each menu item. In pixels.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_items_height',
			'value'       => '65',
			'min'         => '10',
			'max'         => '200',
			'step'        => '1',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Mobile Menu Sticky Maximum Height', 'fusion-builder' ),
			'description' => esc_html__( 'The maximum height for mobile main menu links when the container is sticky. Use any valid CSS unit. ', 'fusion-builder' ),
			'param_name'  => 'mobile_sticky_max_height',
			'value'       => '',
			'dependency'  => [
				[
					'element'  => 'fusion_builder_container',
					'param'    => 'sticky',
					'value'    => 'on',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Text Align', 'fusion-builder' ),
			'description' => esc_html__( 'Select if mobile menu items should be aligned to the left, right or centered.', 'fusion-builder' ),
			'param_name'  => 'mobile_justify_content',
			'value'       => [
				'left'   => esc_html__( 'Left', 'fusion-builder' ),
				'center' => esc_html__( 'Center', 'fusion-builder' ),
				'right'  => esc_html__( 'Right', 'fusion-builder' ),
			],
			'default'     => 'left',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Indent Submenus', 'fusion-builder' ),
			'description' => esc_html__( 'Turn on to enable identation for submenus.', 'fusion-builder' ),
			'param_name'  => 'mobile_indent_submenu',
			'value'       => [
				'on'  => esc_html__( 'On', 'fusion-builder' ),
				'off' => esc_html__( 'Off', 'fusion-builder' ),
			],
			'default'     => 'on',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'             => 'typography',
			'heading'          => esc_attr__( 'Mobile Menu Typography', 'fusion-builder' ),
			'description'      => esc_html__( 'Controls the typography of the mobile menu. Leave empty for the global font family.', 'fusion-builder' ),
			'param_name'       => 'mobile_fonts',
			'choices'          => [
				'font-family'    => 'mobile_typography',
				'font-size'      => 'mobile_font_size',
				'text-transform' => 'mobile_text_transform',
				'line-height'    => 'mobile_line_height',
				'letter-spacing' => 'mobile_letter_spacing',
			],
			'default'          => [
				'font-family' => '',
				'variant'     => '400',
			],
			'remove_from_atts' => true,
			'global'           => true,
			'group'            => esc_attr__( 'Mobile', 'fusion-builder' ),
			'callback'         => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Separator Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the color for mobile menu separators.', 'fusion-builder' ),
			'param_name'  => 'mobile_sep_color',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0.1)',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Mobile Menu Background Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the background color for mobile menus.', 'fusion-builder' ),
			'param_name'    => 'mobile_bg',
			'value'         => '',
			'default'       => '#ffffff',
			'group'         => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'active' => [
					'label'      => __( 'Active', 'fusion-builder' ),
					'param_name' => 'mobile_active_bg',
					'default'    => '#f9f9fb',
				],
			],
			'connect-state' => [ 'mobile_color' ],
		],
		[
			'type'          => 'colorpickeralpha',
			'heading'       => esc_html__( 'Mobile Menu Text Color', 'fusion-builder' ),
			'description'   => esc_html__( 'Controls the text color for mobile menus.', 'fusion-builder' ),
			'param_name'    => 'mobile_color',
			'value'         => '',
			'default'       => '#4a4e57',
			'group'         => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'      => [
				'function' => 'fusion_menu',
			],
			'states'        => [
				'active' => [
					'label'      => __( 'Active', 'fusion-builder' ),
					'param_name' => 'mobile_active_color',
					'default'    => '#4a4e57',
				],
			],
			'connect-state' => [ 'mobile_bg' ],
		],
		'fusion_animation_placeholder'         => [
			'preview_selector' => '.awb-menu',
		],
	];

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Menu',
			[
				'name'         => esc_html__( 'Menu', 'fusion-builder' ),
				'shortcode'    => 'fusion_menu',
				'icon'         => 'fusiona-bars',
				'params'       => $params,
				'help_url'     => 'https://avada.com/documentation/menu-element/',
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
					'action'   => 'get_fusion_menu',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_menu' );
