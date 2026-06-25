<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.1
 */

if ( fusion_is_element_enabled( 'fusion_form_recaptcha' ) ) {

	if ( ! class_exists( 'FusionForm_Recaptcha' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.1
		 */
		class FusionForm_Recaptcha extends Fusion_Form_Component {

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.1
			 */
			public function __construct() {
				add_filter( 'fusion_attr_recaptcha-shortcode', [ $this, 'attr' ] );

				parent::__construct( 'fusion_form_recaptcha' );
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
				$fusion_settings = awb_get_fusion_settings();
				return [
					'color_theme'    => $fusion_settings->get( 'recaptcha_color_scheme' ),
					'badge_position' => $fusion_settings->get( 'recaptcha_badge_position' ),
					'tab_index'      => '',
					'class'          => '',
					'id'             => '',
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
				$params = [
					'color_theme'    => $this->args['color_theme'],
					'badge_position' => $this->args['badge_position'],
					'tab_index'      => $this->args['tab_index'],
					'counter'        => $this->params['form_number'],
					'element'        => 'form',
					'wrapper_class'  => 'fusion-form-recaptcha-wrapper',
				];

				ob_start();
				?>
				<div <?php echo FusionBuilder::attributes( 'recaptcha-shortcode' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
					<?php class_exists( 'AWB_Google_Recaptcha' ) ? AWB_Google_Recaptcha::get_instance()->render_field( $params ) : ''; ?>
				</div>
				<?php
				$recaptcha_content = ob_get_clean();
				return $recaptcha_content;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => 'form-creator-recaptcha',
				];

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'recaptcha_public'         => $fusion_settings->get( 'recaptcha_public' ),
					'recaptcha_private'        => $fusion_settings->get( 'recaptcha_private' ),
					'recaptcha_version'        => $fusion_settings->get( 'recaptcha_version' ),
					'recaptcha_badge_position' => $fusion_settings->get( 'recaptcha_badge_position' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'recaptcha_public'         => 'recaptcha_public',
					'recaptcha_private'        => 'recaptcha_private',
					'recaptcha_version'        => 'recaptcha_version',
					'recaptcha_badge_position' => 'recaptcha_badge_position',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'recaptcha_color_scheme'   => 'color_theme',
					'recaptcha_badge_position' => 'badge_position',
				];
			}
		}
	}

	new FusionForm_Recaptcha();
}

/**
 * Map shortcode to Fusion Builder
 *
 * @since 3.1
 */
function fusion_form_recaptcha() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionForm_Recaptcha',
			[
				'name'           => esc_attr__( 'reCAPTCHA Field', 'fusion-builder' ),
				'shortcode'      => 'fusion_form_recaptcha',
				'icon'           => 'fusiona-af-recaptcha',
				'form_component' => true,
				'preview'        => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-form-element-preview.php',
				'preview_id'     => 'fusion-builder-block-module-form-element-preview-template',
				'params'         => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'reCAPTCHA Color Scheme', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the reCAPTCHA color scheme.', 'fusion-builder' ),
						'param_name'  => 'color_theme',
						'default'     => '',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'light' => esc_attr__( 'Light', 'fusion-builder' ),
							'dark'  => esc_attr__( 'Dark', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'reCAPTCHA Badge Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose where the reCAPTCHA badge should be displayed.', 'fusion-builder' ),
						'param_name'  => 'badge_position',
						'default'     => '',
						'value'       => [
							''            => esc_attr__( 'Default', 'fusion-builder' ),
							'inline'      => esc_attr__( 'Inline', 'fusion-builder' ),
							'bottomleft'  => esc_attr__( 'Bottom Left', 'fusion-builder' ),
							'bottomright' => esc_attr__( 'Bottom Right', 'fusion-builder' ),
							'hide'        => esc_attr__( 'Hide', 'fusion-builder' ),
						],
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
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_form_recaptcha' );
