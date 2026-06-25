<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_alert' ) ) {

	if ( ! class_exists( 'FusionSC_Alert' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Alert extends Fusion_Element {

			/**
			 * The alert class.
			 *
			 * @access private
			 * @var string
			 */
			private $alert_class;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.10.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_alert-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_alert-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_alert-shortcode-button', [ $this, 'button_attr' ] );

				add_shortcode( 'fusion_alert', [ $this, 'render' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				$border_radius   = Fusion_Builder_Border_Radius_Helper::get_border_radius_array_with_fallback_value( $fusion_settings->get( 'alert_border_radius' ) );

				return [
					'accent_color'               => '',
					'animation_direction'        => 'left',
					'animation_offset'           => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'            => '',
					'animation_delay'            => '',
					'animation_type'             => '',
					'animation_color'            => '',
					'background_color'           => '',
					'border_radius_top_left'     => $border_radius['top_left'],
					'border_radius_top_right'    => $border_radius['top_right'],
					'border_radius_bottom_right' => $border_radius['bottom_right'],
					'border_radius_bottom_left'  => $border_radius['bottom_left'],
					'border_size'                => $fusion_settings->get( 'alert_border_size' ),
					'box_shadow'                 => ( '' !== $fusion_settings->get( 'alert_box_shadow' ) ) ? strtolower( $fusion_settings->get( 'alert_box_shadow' ) ) : 'no',
					'class'                      => '',
					'dismissable'                => $fusion_settings->get( 'alert_box_dismissable' ),
					'hide_on_mobile'             => fusion_builder_default_visibility( 'string' ),
					'icon'                       => '',
					'id'                         => '',
					'link_color_inheritance'     => $fusion_settings->get( 'alert_box_link_color_inheritance' ),
					'padding_bottom'             => '',
					'padding_left'               => '',
					'padding_right'              => '',
					'padding_top'                => '',
					'margin_bottom'              => '',
					'margin_left'                => '',
					'margin_right'               => '',
					'margin_top'                 => '',
					'text_align'                 => $fusion_settings->get( 'alert_box_text_align' ),
					'text_transform'             => $fusion_settings->get( 'alert_box_text_transform' ),
					'type'                       => 'general',
					'sticky_display'             => '',
					'logics'                     => '',
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
					'animation_offset'                  => 'animation_offset',
					'alert_box_link_color_inheritance'  => 'link_color_inheritance',
					'alert_box_text_align'              => 'text_align',
					'alert_box_text_transform'          => 'text_transform',
					'alert_box_dismissable'             => 'dismissable',
					'alert_border_radius[top_left]'     => 'border_radius_top_left',
					'alert_border_radius[top_right]'    => 'border_radius_top_right',
					'alert_border_radius[bottom_right]' => 'border_radius_bottom_right',
					'alert_border_radius[bottom_left]'  => 'border_radius_bottom_left',
					'alert_border_size'                 => 'border_size',
					'alert_box_shadow'                  => [
						'param'    => 'box_shadow',
						'callback' => 'toLowerCase',
					],
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults          = self::get_element_defaults();
				$defaults                = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_alert' );
				$defaults['border_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['dismissable'] = 'yes' === $defaults['dismissable'] ? 'boxed' : $defaults['dismissable'];
				$content                 = apply_filters( 'fusion_shortcode_content', $content, 'fusion_alert', $args );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$this->args['padding_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['padding_bottom'], 'px' );
				$this->args['padding_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['padding_left'], 'px' );
				$this->args['padding_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['padding_right'], 'px' );
				$this->args['padding_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['padding_top'], 'px' );

				switch ( $this->args['type'] ) {

					case 'general':
						$this->alert_class = 'info';
						if ( ! $this->args['icon'] || 'none' !== $this->args['icon'] ) {
							$this->args['icon'] = 'awb-icon-info-circle';
						}
						break;
					case 'error':
						$this->alert_class = 'danger';
						if ( ! $this->args['icon'] || 'none' !== $this->args['icon'] ) {
							$this->args['icon'] = 'awb-icon-exclamation-triangle';
						}
						break;
					case 'success':
						$this->alert_class = 'success';
						if ( ! $this->args['icon'] || 'none' !== $this->args['icon'] ) {
							$this->args['icon'] = 'awb-icon-check-circle';
						}
						break;
					case 'notice':
						$this->alert_class = 'warning';
						if ( ! $this->args['icon'] || 'none' !== $this->args['icon'] ) {
							$this->args['icon'] = 'awb-icon-cog';
						}
						break;
					case 'blank':
						$this->alert_class = 'blank';
						break;
					case 'custom':
						$this->alert_class = 'custom';
						break;
				}

				if ( '' !== $this->args['logics'] ) {
					// Add form element data to a form.
					$this->add_field_data_to_form();
				}

				$html  = '<div ' . FusionBuilder::attributes( 'alert-shortcode' ) . '>';
				$html .= '<div class="fusion-alert-content-wrapper">';
				if ( $this->args['icon'] && 'none' !== $this->args['icon'] ) {
					$html .= '<span ' . FusionBuilder::attributes( 'alert-icon' ) . '>';
					$html .= '<i ' . FusionBuilder::attributes( 'alert-shortcode-icon' ) . '></i>';
					$html .= '</span>';
				}
				// Make sure the title text is not wrapped with an unattributed p tag.
				$content = preg_replace( '!^<p>(.*?)</p>$!i', '$1', trim( $content ) );

				fusion_element_rendering_elements( true );
				$html .= '<span class="fusion-alert-content">' . do_shortcode( $content ) . '</span>';
				fusion_element_rendering_elements( false );
				$html .= '</div>';
				$html .= ( 'boxed' === $this->args['dismissable'] || 'floated' === $this->args['dismissable'] ) ? '<button ' . FusionBuilder::attributes( 'alert-shortcode-button' ) . '>&times;</button>' : '';
				$html .= '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_alert_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {
				$attr = [];

				$attr['class'] = 'fusion-alert alert ' . $this->args['type'] . ' alert-' . $this->alert_class . ' fusion-alert-' . $this->args['text_align'] . ' ' . $this->args['class'];
				$attr['style'] = $this->get_style_vars();

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				$attr['role'] = 'alert';

				if ( 'capitalize' === $this->args['text_transform'] ) {
					$attr['class'] .= ' fusion-alert-capitalize';
				}

				$attr['class'] .= 'yes' === $this->args['link_color_inheritance'] ? ' awb-alert-inherit-link-color' : ' awb-alert-native-link-color';

				if ( 'boxed' === $this->args['dismissable'] || 'floated' === $this->args['dismissable'] ) {
					$attr['class'] .= ' alert-dismissable awb-alert-close-' . $this->args['dismissable'];
				}

				if ( 'yes' === $this->args['box_shadow'] ) {
					$attr['class'] .= ' alert-shadow';
				}

				// Hide field if it has got logics.
				if ( isset( $this->args['logics'] ) && '' !== $this->args['logics'] && '[]' !== base64_decode( $this->args['logics'] ) ) {
					$attr['data-form-element-name'] = 'fusion_alert_' . $this->counter;
					$attr['class']                 .= ' fusion-form-field-hidden';
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['id'] = $this->args['id'];

				return $attr;
			}

			/**
			 * Get the styling vars.
			 *
			 * @since 3.9
			 * @return string
			 */
			public function get_style_vars() {
				$css_vars           = [];
				$border_radius_vars = '';

				if ( 'custom' === $this->alert_class ) {
					array_push( $css_vars, 'background_color' );
					array_push( $css_vars, 'accent_color' );
					array_push( $css_vars, 'border_size' );

					$border_radius_vars = Fusion_Builder_Border_Radius_Helper::get_border_radius_vars( $this->args );
				}

				$padding_vars = Fusion_Builder_Padding_Helper::get_padding_vars( $this->args );
				$margin_vars  = Fusion_Builder_Margin_Helper::get_margin_vars( $this->args );

				return $this->get_css_vars_for_options( $css_vars ) . $border_radius_vars . $margin_vars . $padding_vars;
			}

			/**
			 * Builds the icon  attributes array.
			 *
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {
				return [
					'class'       => fusion_font_awesome_name_handler( $this->args['icon'] ),
					'aria-hidden' => 'true',
				];
			}

			/**
			 * Builds the button attributes array.
			 *
			 * @since 1.0
			 * @return array
			 */
			public function button_attr() {
				$attr = [];

				if ( 'custom' === $this->alert_class && $this->args['accent_color'] ) {
					$attr['style'] = 'color:' . $this->args['accent_color'] . ';border-color:' . $this->args['accent_color'] . ';';
				}

				$attr['type']         = 'button';
				$attr['class']        = 'close toggle-alert';
				$attr['data-dismiss'] = 'alert';
				$attr['aria-label']   = esc_attr__( 'Close', 'fusion-builder' );

				return $attr;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @since 1.1.6
			 * @return array $sections Blog settings.
			 */
			public function add_options() {
				// Skip alerts within builder for the replacements on change.
				$alert_element_builder       = '.fusion-alert:not(.fusion-live-alert)';
				$alert_element_close_builder = '.fusion-alert:not(.fusion-live-alert) .close';

				return [
					'alert_shortcode_section' => [
						'label'       => esc_attr__( 'Alert', 'fusion-builder' ),
						'description' => '',
						'id'          => 'alert_shortcode_section',
						'default'     => '',
						'icon'        => 'fusiona-exclamation-triangle',
						'type'        => 'accordion',
						'fields'      => [
							'info_bg_color'            => [
								'label'       => esc_attr__( 'General Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the background color for general alert boxes.', 'fusion-builder' ),
								'id'          => 'info_bg_color',
								'css_vars'    => [
									[
										'name'     => '--info_bg_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
							],
							'info_accent_color'        => [
								'label'       => esc_attr__( 'General Accent Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the accent color for general alert boxes.', 'fusion-builder' ),
								'id'          => 'info_accent_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--info_accent_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'danger_bg_color'          => [
								'label'       => esc_attr__( 'Error Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the background color for error alert boxes.', 'fusion-builder' ),
								'id'          => 'danger_bg_color',
								'default'     => 'rgba(219,75,104,0.1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--danger_bg_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'danger_accent_color'      => [
								'label'       => esc_attr__( 'Error Accent Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the accent color for error alert boxes.', 'fusion-builder' ),
								'id'          => 'danger_accent_color',
								'default'     => '#db4b68',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--danger_accent_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'success_bg_color'         => [
								'label'       => esc_attr__( 'Success Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the background color for success alert boxes.', 'fusion-builder' ),
								'id'          => 'success_bg_color',
								'default'     => 'rgba(18,184,120,0.1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--success_bg_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'success_accent_color'     => [
								'label'       => esc_attr__( 'Success Accent Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the accent color for success alert boxes.', 'fusion-builder' ),
								'id'          => 'success_accent_color',
								'default'     => '#12b878',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--success_accent_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'warning_bg_color'         => [
								'label'       => esc_attr__( 'Notice Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the background color for notice alert boxes.', 'fusion-builder' ),
								'id'          => 'warning_bg_color',
								'default'     => 'rgba(241,174,42,0.1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--warning_bg_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'warning_accent_color'     => [
								'label'       => esc_attr__( 'Notice Accent Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the accent color for notice alert boxes.', 'fusion-builder' ),
								'id'          => 'warning_accent_color',
								'default'     => '#f1ae2a',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--warning_accent_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'alert_box_text_align'     => [
								'label'       => esc_attr__( 'Content Alignment', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose how the content should be displayed.', 'fusion-builder' ),
								'id'          => 'alert_box_text_align',
								'type'        => 'radio-buttonset',
								'default'     => 'center',
								'choices'     => [
									'left'   => esc_attr__( 'Left', 'fusion-builder' ),
									'center' => esc_attr__( 'Center', 'fusion-builder' ),
									'right'  => esc_attr__( 'Right', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => $alert_element_builder,
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'fusion-alert-$',
										'remove_attrs'  => [ 'fusion-alert-left', 'fusion-alert-center', 'fusion-alert-right' ],
									],
								],
							],
							'alert_box_text_transform' => [
								'label'       => esc_attr__( 'Text Transform', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose how the text is displayed.', 'fusion-builder' ),
								'id'          => 'alert_box_text_transform',
								'default'     => 'normal',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'normal'     => esc_attr__( 'Normal', 'fusion-builder' ),
									'capitalize' => esc_attr__( 'Uppercase', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => $alert_element_builder,
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'fusion-alert-$',
										'remove_attrs'  => [ 'fusion-alert-capitalize', 'fusion-alert-normal' ],
									],
								],
							],
							'alert_box_link_color_inheritance' => [
								'label'       => esc_attr__( 'Link Color Inheritance', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose if links should inherit the alert box text color.', 'fusion-builder' ),
								'id'          => 'alert_box_link_color_inheritance',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => $alert_element_builder,
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'awb-alert-inherit-link-color',
										'remove_attrs'  => [ 'awb-alert-native-link-color' ],
										'exclude'       => [ 'no' ],
									],
									[
										'element'       => $alert_element_builder,
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'awb-alert-native-link-color',
										'remove_attrs'  => [ 'awb-alert-inherit-link-color' ],
										'exclude'       => [ 'yes' ],
									],
								],
							],
							'alert_box_dismissable'    => [
								'label'       => esc_attr__( 'Dismiss Button', 'fusion-builder' ),
								'description' => esc_attr__( 'Select if the alert box should be dismissable.', 'fusion-builder' ),
								'id'          => 'alert_box_dismissable',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'boxed'   => esc_attr__( 'Boxed', 'fusion-builder' ),
									'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
									'no'      => esc_attr__( 'None', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => $alert_element_close_builder,
										'property'      => 'display',
										'value_pattern' => 'none',
										'exclude'       => [ 'yes' ],
									],
									[
										'element'       => $alert_element_close_builder,
										'property'      => 'display',
										'value_pattern' => 'inline',
										'exclude'       => [ 'no' ],
									],
								],
							],
							'alert_box_shadow'         => [
								'label'       => esc_attr__( 'Box Shadow', 'fusion-builder' ),
								'description' => esc_attr__( 'Display a box shadow below the alert box.', 'fusion-builder' ),
								'id'          => 'alert_box_shadow',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => $alert_element_builder,
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'alert-shadow',
										'remove_attrs'  => [ 'alert-shadow-no' ],
										'exclude'       => [ 'no' ],
									],
									[
										'element'       => $alert_element_builder,
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'alert-shadow-no',
										'remove_attrs'  => [ 'alert-shadow' ],
										'exclude'       => [ 'yes' ],
									],
								],
							],
							'alert_border_size'        => [
								'label'       => esc_html__( 'Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the alert boxes.', 'fusion-builder' ),
								'id'          => 'alert_border_size',
								'default'     => '1',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
								'css_vars'    => [
									[
										'name'          => '--alert_border_size',
										'value_pattern' => '$px',
									],
								],
							],
							'alert_border_radius'      => [
								'label'       => esc_html__( 'Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Set the border radius.', 'fusion-builder' ),
								'id'          => 'alert_border_radius',
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
										'name'    => '--awb-alert-border-top-left-radius-default',
										'choice'  => 'top_left',
										'element' => 'body',
									],
									[
										'name'    => '--awb-alert-border-top-right-radius-default',
										'choice'  => 'top_right',
										'element' => 'body',
									],
									[
										'name'    => '--awb-alert-border-bottom-right-radius-default',
										'choice'  => 'bottom_right',
										'element' => 'body',
									],
									[
										'name'    => '--awb-alert-border-bottom-left-radius-default',
										'choice'  => 'bottom_left',
										'element' => 'body',
									],
								],

								// Could update variable here, but does not look necessary as set inline.
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
			 * @since 1.1
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script( 'fusion-animations' );
				Fusion_Dynamic_JS::enqueue_script( 'fusion-alert' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/alert.min.css' );
			}

			/**
			 * Adds field data to the form.
			 *
			 * @access public
			 * @since 3.10.2
			 * @return void
			 */
			public function add_field_data_to_form() {
				global $fusion_form;

				if ( ! isset( $fusion_form['form_fields'] ) ) {
					$fusion_form['form_fields'] = [];
				}

				$fusion_form['form_fields'][] = 'fusion_alert';

				if ( isset( $this->args['label'] ) ) {
					$fusion_form['field_labels'][ $this->args['name'] ] = $this->args['label'];
				}

				$field_name = str_replace( 'fusion_form_', '', 'fusion_alert' );
				$name       = isset( $this->args['name'] ) ? $this->args['name'] : $field_name . '_' . $this->counter;

				if ( isset( $this->args['logics'] ) ) {
					$fusion_form['field_logics'][ $name ] = base64_decode( $this->args['logics'] );
				}
				$fusion_form['field_types'][ $name ] = $field_name;
			}
		}
	}

	new FusionSC_Alert();
}


/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_alert() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Alert',
			[
				'name'                     => esc_attr__( 'Alert', 'fusion-builder' ),
				'shortcode'                => 'fusion_alert',
				'icon'                     => 'fusiona-exclamation-triangle',
				'preview'                  => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-alert-preview.php',
				'preview_id'               => 'fusion-builder-block-module-alert-preview-template',
				'allow_generator'          => true,
				'inline_editor'            => true,
				'inline_editor_shortcodes' => false,
				'help_url'                 => 'https://avada.com/documentation/alert-element/',
				'params'                   => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Alert Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the type of alert message. Choose custom for advanced color options below.', 'fusion-builder' ),
						'param_name'  => 'type',
						'default'     => 'error',
						'value'       => [
							'general' => esc_attr__( 'General', 'fusion-builder' ),
							'error'   => esc_attr__( 'Error', 'fusion-builder' ),
							'success' => esc_attr__( 'Success', 'fusion-builder' ),
							'notice'  => esc_attr__( 'Notice', 'fusion-builder' ),
							'custom'  => esc_attr__( 'Custom', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Accent Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom setting only. Set the border, text and icon color for custom alert boxes.', 'fusion-builder' ),
						'param_name'  => 'accent_color',
						'value'       => '#808080',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom setting only. Set the background color for custom alert boxes.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '#ffffff',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'default'     => preg_replace( '/[a-z,%]/', '', $fusion_settings->get( 'alert_border_size' ) ),
						'description' => esc_attr__( 'Custom setting only. Set the border size for custom alert boxes. In pixels.', 'fusion-builder' ),
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					'fusion_border_radius_placeholder'     => [
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'dependency' => [
							[
								'element'  => 'type',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Select Custom Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Content Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the content should be displayed.', 'fusion-builder' ),
						'param_name'  => 'text_align',
						'default'     => '',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
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
						'group'            => esc_attr__( 'General', 'fusion-builder' ),
					],
					'fusion_margin_placeholder'            => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Transform', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the text is displayed.', 'fusion-builder' ),
						'param_name'  => 'text_transform',
						'default'     => '',
						'value'       => [
							''           => esc_attr__( 'Default', 'fusion-builder' ),
							'normal'     => esc_attr__( 'Normal', 'fusion-builder' ),
							'capitalize' => esc_attr__( 'Uppercase', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Color Inheritance', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if links should inherit the alert box text color.', 'fusion-builder' ),
						'param_name'  => 'link_color_inheritance',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Dismiss Button', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if the alert box should be dismissable.', 'fusion-builder' ),
						'param_name'  => 'dismissable',
						'default'     => '',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'boxed'   => esc_attr__( 'Boxed', 'fusion-builder' ),
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
							'no'      => esc_attr__( 'None', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Box Shadow', 'fusion-builder' ),
						'description' => esc_attr__( 'Display a box shadow below the alert box.', 'fusion-builder' ),
						'param_name'  => 'box_shadow',
						'default'     => '',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Alert Content', 'fusion-builder' ),
						'description'  => esc_attr__( "Insert the alert's content.", 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_html__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-alert',
					],
					[
						'type'        => 'fusion_logics',
						'heading'     => esc_html__( 'Conditional Logic', 'fusion-builder' ),
						'param_name'  => 'logics',
						'description' => esc_html__( 'Add conditional logic when the element is used within a form.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'placeholder' => [
							'id'          => 'placeholder',
							'title'       => esc_html__( 'Select A Field', 'fusion-builder' ),
							'type'        => 'text',
							'comparisons' => [
								'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
								'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
								'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
								'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
								'contains'     => esc_attr__( 'Contains', 'fusion-builder' ),
							],
						],
						'comparisons' => [
							'equal'        => esc_attr__( 'Equal To', 'fusion-builder' ),
							'not-equal'    => esc_attr__( 'Not Equal To', 'fusion-builder' ),
							'greater-than' => esc_attr__( 'Greater Than', 'fusion-builder' ),
							'less-than'    => esc_attr__( 'Less Than', 'fusion-builder' ),
							'contains'     => esc_attr__( 'Contains', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => '_post_type_edited',
								'value'    => 'fusion_form',
								'operator' => '==',
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
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_alert' );
