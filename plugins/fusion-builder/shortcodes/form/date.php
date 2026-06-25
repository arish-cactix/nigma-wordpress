<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_date' ) ) {

	if ( ! class_exists( 'FusionForm_Date' ) ) {

		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Date extends Fusion_Form_Component {

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.1
			 */
			public function __construct() {
				parent::__construct( 'fusion_form_date' );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function get_element_defaults() {

				return [
					'label'            => '',
					'name'             => '',
					'picker'           => 'custom',
					'format'           => '',
					'required'         => '',
					'empty_notice'     => '',
					'min'              => '',
					'max'              => '',
					'disabled_days'    => '',
					'placeholder'      => '',
					'input_field_icon' => '',
					'tab_index'        => '',
					'class'            => '',
					'id'               => '',
					'logics'           => '',
					'value'            => '',
					'tooltip'          => '',
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
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-date-picker',
					FusionBuilder::$js_folder_url . '/library/flatpickr.js',
					FusionBuilder::$js_folder_path . '/library/flatpickr.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);
			}

			/**
			 * Load flat pickr.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/flatpickr.min.css' );
			}

			/**
			 * Render form field html.
			 *
			 * @access public
			 * @since 3.1
			 * @param string $content The content.
			 * @return string
			 */
			public function render_input_field( $content ) {
				$html = '';

				$element_data = $this->create_element_data( $this->args );

				if ( '' !== $this->args['tooltip'] ) {
					$element_data['label'] .= $this->get_field_tooltip( $this->args );
				}

				// If we are using a script the autocomplete popup blocks selection.
				$auto_complete = 'native' !== $this->args['picker'] ? 'autocomplete="no"' : '';
				$start_of_week = get_option( 'start_of_week', 0 );

				// Input markup.
				$element_html  = '<input autocomplete="no" type="date"';
				$element_html .= '' !== $element_data['empty_notice'] ? ' data-empty-notice="' . $element_data['empty_notice'] . '" ' : '';
				$element_html .= '' !== $this->args['min'] ? ' min="' . $this->args['min'] . '" ' : '';
				$element_html .= '' !== $this->args['max'] ? ' max="' . $this->args['max'] . '" ' : '';
				$element_html .= '' !== $this->args['disabled_days'] ? ' data-disabled-days="' . $this->args['disabled_days'] . '" ' : '';
				$element_html .= '' !== $start_of_week ? ' data-first-day="' . $start_of_week . '" ' : '';
				$element_html .= ' data-format="' . $this->args['format'] . '" tabindex="' . $this->args['tab_index'] . '" id="' . $this->args['name'] . '" name="' . $this->args['name'] . '" data-type="' . esc_attr( $this->args['picker'] ) . '" value="' . $this->args['value'] . '"' . $element_data['holds_private_data'] . $element_data['class'] . $element_data['required'] . $element_data['placeholder'] . $element_data['style'] . $auto_complete . '/>';

				if ( isset( $this->args['input_field_icon'] ) && '' !== $this->args['input_field_icon'] ) {
					$icon_html     = '<div class="fusion-form-input-with-icon">';
					$icon_html    .= '<i class="' . fusion_font_awesome_name_handler( $this->args['input_field_icon'] ) . '"></i>';
					$element_html  = $icon_html . $element_html;
					$element_html .= '</div>';
				}

				if ( 'above' === $this->params['form_meta']['label_position'] ) {
					$html .= $element_data['label'] . $element_html;
				} else {
					$html .= $element_html . $element_data['label'];
				}

				return $html;
			}
		}
	}

	new FusionForm_Date();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_date() {
	$start_of_week    = get_option( 'start_of_week', 0 );
	$days_of_the_week = [
		'sunday'    => esc_attr__( 'Sunday', 'fusion-builder' ),
		'monday'    => esc_attr__( 'Monday', 'fusion-builder' ),
		'tuesday'   => esc_attr__( 'Tuesday', 'fusion-builder' ),
		'wednesday' => esc_attr__( 'Wednesday', 'fusion-builder' ),
		'thursday'  => esc_attr__( 'Thursday', 'fusion-builder' ),
		'friday'    => esc_attr__( 'Friday', 'fusion-builder' ),
		'saturday'  => esc_attr__( 'Saturday', 'fusion-builder' ),
	];

	// Get first and second half of week days and merge them.
	$first_half       = array_slice( $days_of_the_week, $start_of_week, 7 - $start_of_week );
	$second_half      = array_diff( $days_of_the_week, $first_half );
	$days_of_the_week = array_merge( $first_half, $second_half );

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Date',
			[
				'name'           => esc_attr__( 'Date Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_date',
				'icon'           => 'fusiona-af-date',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'params'         => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Field Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the label for the input field. This is how users will identify individual fields.', 'fusion-builder' ),
						'param_name'  => 'label',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Field Name', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the field name. Please use only lowercase alphanumeric characters, dashes, and underscores.', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => '',
						'placeholder' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Required Field', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to ensure that this field is completed before allowing the user to submit the form.', 'fusion-builder' ),
						'param_name'  => 'required',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Empty Input Notice', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter text validation notice that should display if data input is empty.', 'fusion-builder' ),
						'param_name'  => 'empty_notice',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'required',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Placeholder Text', 'fusion-builder' ),
						'param_name'  => 'placeholder',
						'value'       => '',
						'description' => __( 'The placeholder text to display as hint for the input type. Note, this will only show for the custom picker. <strong>NOTE:</strong> The placeholder will only be displayed on screen sizes that have the custom picker enabled.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tooltip Text', 'fusion-builder' ),
						'param_name'  => 'tooltip',
						'value'       => '',
						'description' => esc_attr__( 'The text to display as tooltip hint for the input.', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Custom Picker', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to enable a lightweight custom picker on mobile only, mobile and desktop or set to never to use browser native.', 'fusion-builder' ),
						'param_name'  => 'picker',
						'default'     => 'custom',
						'value'       => [
							'native'  => esc_attr__( 'Never', 'fusion-builder' ),
							'desktop' => esc_attr__( 'Desktop Only', 'fusion-builder' ),
							'custom'  => esc_attr__( 'Always', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'date_time_picker',
						'time'        => false,
						'heading'     => esc_attr__( 'Minimum Date', 'fusion-builder' ),
						'param_name'  => 'min',
						'value'       => '',
						'description' => esc_attr__( 'Set the minimum date.', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'picker',
								'value'    => 'native',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'date_time_picker',
						'time'        => false,
						'heading'     => esc_attr__( 'Maximum Date', 'fusion-builder' ),
						'param_name'  => 'max',
						'value'       => '',
						'description' => esc_attr__( 'Set the maximum date.', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'picker',
								'value'    => 'native',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Disabled Days', 'fusion-builder' ),
						'description' => esc_attr__( 'Disables the days of week you want to exlcude from selection.', 'fusion-builder' ),
						'param_name'  => 'disabled_days',
						'default'     => '',
						'choices'     => $days_of_the_week,
						'dependency'  => [
							[
								'element'  => 'picker',
								'value'    => 'native',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Date Format', 'fusion-builder' ),
						'param_name'  => 'format',
						'value'       => '',
						/* translators: The link. */
						'description' => sprintf( __( 'Enter the date format you need. You can check the complete list of available formatting tokens <a href="%s" target="_blank">here</a>.' ), 'https://flatpickr.js.org/formatting/' ),
						'dependency'  => [
							[
								'element'  => 'picker',
								'value'    => 'native',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Input Field Icon', 'fusion-builder' ),
						'param_name'  => 'input_field_icon',
						'value'       => '',
						'description' => esc_attr__( 'Select an icon for the input field, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tab Index', 'fusion-builder' ),
						'param_name'  => 'tab_index',
						'value'       => '',
						'description' => esc_attr__( 'Tab index for the form field.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class for the form field.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID for the form field.', 'fusion-builder' ),
					],
					'fusion_form_logics_placeholder' => [],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_date' );
