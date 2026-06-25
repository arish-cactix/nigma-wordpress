<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.5
 */

if ( fusion_is_element_enabled( 'fusion_builder_form_step' ) ) {

	if ( ! class_exists( 'Fusion_Form_Step' ) && class_exists( 'Fusion_Element' ) ) {

		/**
		 * Shortcode class.
		 *
		 * @since 3.5
		 */
		class Fusion_Form_Step extends Fusion_Element {

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.5
			 */
			public function __construct() {
				parent::__construct();

				add_shortcode( 'fusion_builder_form_step', [ $this, 'render' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_defaults() {
				return [
					'title' => '',
				];
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
				return '';
			}
		}
	}

	new Fusion_Form_Step();
}

/**
 * Map shortcode to Avada Builder
 */
function fusion_builder_element_form_step() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'Fusion_Form_Step',
			[
				'name'              => esc_attr__( 'Form Step', 'fusion-builder' ),
				'shortcode'         => 'fusion_builder_form_step',
				'hide_from_builder' => true,
				'help_url'          => 'https://avada.com/help-center',
				'params'            => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the text to be displayed for the step. The title will either be displayed, or read by screen readers (for accessibility).', 'fusion-builder' ),
						'param_name'  => 'title',
						'value'       => '',
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Select icon for this form step, or leave empty if is not displayed.', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_builder_element_form_step' );
