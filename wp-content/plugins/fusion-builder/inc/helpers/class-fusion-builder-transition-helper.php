<?php
/**
 * Avada Builder Transition Helper class.
 *
 * @package Avada-Builder
 * @since 3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Transition Helper class.
 *
 * @since 3.8
 */
class Fusion_Builder_Transition_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 3.8
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Get transition params.
	 *
	 * @since 3.8
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		$selector_base      = isset( $args['selector_base'] ) ? $args['selector_base'] : '';
		$transition_options = [];

		$transition_options[] = [
			'type'        => 'range',
			'heading'     => esc_attr__( 'Transition Duration', 'fusion-builder' ),
			'description' => esc_attr__( 'Set transition duration in milliseconds.', 'fusion-builder' ),
			'param_name'  => 'transition_duration',
			'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
			'value'       => '300',
			'min'         => '0',
			'step'        => '25',
			'max'         => '2000',
			'callback'    => [
				'function' => 'fusion_update_transition_style',
				'args'     => [
					'selector_base' => $selector_base,
				],
			],
		];

		$transition_options[] = [
			'type'        => 'select',
			'heading'     => esc_attr__( 'Transition Easing', 'fusion-builder' ),
			'description' => esc_attr__( 'Select transition easing.', 'fusion-builder' ),
			'param_name'  => 'transition_easing',
			'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
			'default'     => 'ease',
			'value'       => [
				'ease'        => esc_attr__( 'Ease', 'fusion-builder' ),
				'ease-in'     => esc_attr__( 'Ease In', 'fusion-builder' ),
				'ease-out'    => esc_attr__( 'Ease Out', 'fusion-builder' ),
				'ease-in-out' => esc_attr__( 'Ease In Out', 'fusion-builder' ),
				'linear'      => esc_attr__( 'Linear', 'fusion-builder' ),
				'custom'      => esc_attr__( 'Custom', 'fusion-builder' ),
			],
			'callback'    => [
				'function' => 'fusion_update_transition_style',
				'args'     => [
					'selector_base' => $selector_base,
				],
			],
		];

		$transition_options[] = [
			'type'        => 'textfield',
			'heading'     => esc_attr__( 'Transition Custom Easing', 'fusion-builder' ),
			/* translators: %s - The URL. */
			'description' => sprintf( __( 'Set transition custom easing, use <a href="%s" target="_blank">this website</a> to create custom easing.', 'fusion-builder' ), 'https://cubic-bezier.com/' ),
			'param_name'  => 'transition_custom_easing',
			'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_update_transition_style',
				'args'     => [
					'selector_base' => $selector_base,
				],
			],
			'dependency'  => [
				[
					'element'  => 'transition_easing',
					'value'    => 'custom',
					'operator' => '==',
				],
			],
		];

		return $transition_options;
	}

	/**
	 * Get transition styles.
	 *
	 * @since 3.8
	 * @param array $atts The transition parameters.
	 * @return string
	 */
	public static function get_transition_styles( $atts ) {
		$transition_duration = $atts['transition_duration'] . 'ms';
		$transition_easing   = 'custom' === $atts['transition_easing'] ? $atts['transition_custom_easing'] : $atts['transition_easing'];

		$transition_style = 'filter ' . $transition_duration . ' ' . $transition_easing . ', transform ' . $transition_duration . ' ' . $transition_easing . ', background-color ' . $transition_duration . ' ' . $transition_easing . ', border-color ' . $transition_duration . ' ' . $transition_easing;
		return $transition_style;
	}
}
