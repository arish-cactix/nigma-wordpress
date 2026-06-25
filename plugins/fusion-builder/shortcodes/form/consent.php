<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_consent' ) ) {

	if ( ! class_exists( 'FusionForm_Consent' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Consent extends Fusion_Form_Component {

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.1
			 */
			public function __construct() {
				parent::__construct( 'fusion_form_consent' );
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
					'label'             => '',
					'name'              => '',
					'consent_type'      => 'checkbox',
					'description'       => '',
					'default'           => 'unchecked',
					'required'          => 'no',
					'placeholder'       => '',
					'form_field_layout' => '',
					'options'           => '',
					'tab_index'         => '',
					'class'             => '',
					'id'                => '',
					'logics'            => '',
					'tooltip'           => '',
				];
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
				global $fusion_form;

				$options = '';
				$html    = '';

				$element_data              = $this->create_element_data( $this->args );
				$this->args['description'] = fusion_decode_if_needed( $this->args['description'] );

				if ( 'checkbox' === $this->args['consent_type'] ) {
					$checked = 'checked' === $this->args['default'] ? ' checked ' : '';
					$name    = empty( $this->args['name'] ) ? $this->args['label'] : $this->args['name'];
					$option  = '<div class="fusion-form-checkbox option-inline">';
					$option .= '<input ';
					$option .= 'tabindex="' . $this->args['tab_index'] . '" id="' . $this->args['name'] . '" type="checkbox" value="1" name="' . $this->args['name'] . '"' . $element_data['class'] . $element_data['required'] . $checked . '/>';
					$option .= '<label for="' . $this->args['name'] . '">';
					$option .= $this->args['description'] . '</label>';
					$option .= '</div>';
				} else {
					$option = '<p class="label">' . $this->args['description'] . '</p><input name="' . $this->args['name'] . '" type="hidden" value="1"/>';
				}
				$element_html = $option;

				if ( '' !== $this->args['tooltip'] ) {
					$element_data['label'] .= $this->get_field_tooltip( $this->args );
				}

				if ( '' !== $element_data['label'] ) {
					$element_data['label'] = '<div class="fusion-form-label-wrapper">' . $element_data['label'] . '</div>';
				}

				if ( 'above' === $fusion_form['form_meta']['label_position'] ) {
					$html .= $element_data['label'] . $element_html;
				} else {
					$html .= $element_html . $element_data['label'];
				}

				return $html;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/form/checkbox.min.css' );
			}
		}
	}

	new FusionForm_Consent();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_consent() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Consent',
			[
				'name'           => esc_attr__( 'Consent Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_consent',
				'icon'           => 'fusiona-af-checkbox',
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
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tooltip Text', 'fusion-builder' ),
						'param_name'  => 'tooltip',
						'value'       => '',
						'description' => esc_attr__( 'The text to display as tooltip hint for the input.', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Consent Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if you would like an explicit checkbox consent or implicit consent.', 'fusion-builder' ),
						'param_name'  => 'consent_type',
						'default'     => 'checkbox',
						'value'       => [
							'checkbox' => esc_attr__( 'Checkbox', 'fusion-builder' ),
							'implicit' => esc_attr__( 'Implicit', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Default State', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if the checkbox should be checked by default or unchecked.', 'fusion-builder' ),
						'param_name'  => 'default',
						'default'     => 'unchecked',
						'value'       => [
							'checked'   => esc_attr__( 'Checked', 'fusion-builder' ),
							'unchecked' => esc_attr__( 'Unchecked', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'consent_type',
								'value'    => 'checkbox',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Required Field', 'fusion-builder' ),
						'description' => esc_attr__( 'If the field is required, it can only be submitted if this field is checked.', 'fusion-builder' ),
						'param_name'  => 'required',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'consent_type',
								'value'    => 'checkbox',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'raw_textarea',
						'heading'     => esc_attr__( 'Description', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter a description for what the consent is for.', 'fusion-builder' ),
						'param_name'  => 'description',
						'value'       => '',
						'placeholder' => true,
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
add_action( 'fusion_builder_before_init', 'fusion_form_consent' );
