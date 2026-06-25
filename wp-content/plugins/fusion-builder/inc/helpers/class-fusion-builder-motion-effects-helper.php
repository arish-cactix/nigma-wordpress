<?php
/**
 * Avada Builder Motion Effects Helper class.
 *
 * @package Avada-Builder
 * @since 3.3
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


/**
 * Avada Builder Motion Effects Helper class.
 *
 * @since 3.10
 */
class Fusion_Builder_Motion_Effects_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 3.10
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Motion effects params.
	 *
	 * @since 3.10
	 * @access public
	 * @return array
	 */
	public static function get_params() {

		$params = [
			[
				'type'           => 'repeater',
				'heading'        => esc_html__( 'Motion Effects', 'fusion-builder' ),
				'param_name'     => 'motion_effects',
				'description'    => __( 'Add Motion Effects for the element.', 'fusion-builder' ),
				'group'          => esc_attr__( 'Extras', 'fusion-builder' ),
				'row_add'        => esc_html__( 'Add Motion Effect', 'Avada' ),
				'row_title'      => esc_html__( 'Motion Effect', 'Avada' ),
				'bind_title'     => 'type',
				'skip_empty_row' => true,
				'fields'         => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Effect Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the motion effect type.', 'fusion-builder' ),
						'param_name'  => 'type',
						'value'       => [
							''         => esc_attr__( 'None', 'fusion-builder' ),
							'scroll'   => esc_attr__( 'Scroll Effect', 'fusion-builder' ),
							'mouse'    => esc_attr__( 'Mouse Effect', 'fusion-builder' ),
							'infinite' => esc_attr__( 'Infinite Animation', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Scroll Effect type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the scroll effect type.', 'fusion-builder' ),
						'param_name'  => 'scroll_type',
						'value'       => [
							'transition' => esc_attr__( 'Transition', 'fusion-builder' ),
							'fade'       => esc_attr__( 'Fade', 'fusion-builder' ),
							'scale'      => esc_attr__( 'Scale', 'fusion-builder' ),
							'rotate'     => esc_attr__( 'Rotate', 'fusion-builder' ),
							'blur'       => esc_attr__( 'Blur', 'fusion-builder' ),
						],
						'default'     => 'transition',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the element transition direction.', 'fusion-builder' ),
						'param_name'  => 'scroll_direction',
						'value'       => [
							'up'    => esc_attr__( 'Up', 'fusion-builder' ),
							'down'  => esc_attr__( 'Down', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
						],
						'default'     => 'up',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'transition',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Speed', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the transition speed.', 'fusion-builder' ),
						'param_name'  => 'transition_speed',
						'value'       => '1',
						'min'         => '1',
						'max'         => '10',
						'step'        => '0.1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'transition',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Fade Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the element fade type.', 'fusion-builder' ),
						'param_name'  => 'fade_type',
						'value'       => [
							'in'  => esc_attr__( 'Fade In', 'fusion-builder' ),
							'out' => esc_attr__( 'Fade Out', 'fusion-builder' ),
						],
						'default'     => 'in',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'fade',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Scale Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the element scale type.', 'fusion-builder' ),
						'param_name'  => 'scale_type',
						'value'       => [
							'up'   => esc_attr__( 'Scale Up', 'fusion-builder' ),
							'down' => esc_attr__( 'Scale Down', 'fusion-builder' ),
						],
						'default'     => 'up',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'scale',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Initial Scale', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the initial scale value.', 'fusion-builder' ),
						'param_name'  => 'initial_scale',
						'value'       => '1',
						'min'         => '0',
						'max'         => '5',
						'step'        => '0.1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'scale',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Maximum Scale', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum scale value.', 'fusion-builder' ),
						'param_name'  => 'max_scale',
						'value'       => '1.5',
						'min'         => '0',
						'max'         => '5',
						'step'        => '0.1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'scale',
								'operator' => '==',
							],
							[
								'element'  => 'scale_type',
								'value'    => 'up',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Minimum Scale', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the minimum scale value.', 'fusion-builder' ),
						'param_name'  => 'min_scale',
						'value'       => '0.5',
						'min'         => '0',
						'max'         => '5',
						'step'        => '0.1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'scale',
								'operator' => '==',
							],
							[
								'element'  => 'scale_type',
								'value'    => 'down',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Initial Rotate', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the element initial rotate.', 'fusion-builder' ),
						'param_name'  => 'initial_rotate',
						'value'       => '0',
						'min'         => '-360',
						'max'         => '360',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'rotate',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'End Rotate', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the element end rotate.', 'fusion-builder' ),
						'param_name'  => 'end_rotate',
						'value'       => '30',
						'min'         => '-360',
						'max'         => '360',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'rotate',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Initial Blur', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the element initial blur.', 'fusion-builder' ),
						'param_name'  => 'initial_blur',
						'value'       => '0',
						'min'         => '0',
						'max'         => '100',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'blur',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'End Blur', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the element end blur.', 'fusion-builder' ),
						'param_name'  => 'end_blur',
						'value'       => '3',
						'min'         => '0',
						'max'         => '100',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
							[
								'element'  => 'scroll_type',
								'value'    => 'blur',
								'operator' => '==',
							],
						],
					],
					[
						'type'       => 'select',
						'heading'    => esc_attr__( 'Start When', 'fusion-builder' ),
						'param_name' => 'start_element',
						'value'      => [
							'top'    => esc_attr__( 'Top Of Element', 'fusion-builder' ),
							'center' => esc_attr__( 'Middle Of Element', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom Of Element', 'fusion-builder' ),
						],
						'default'    => 'top',
						'dependency' => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
						],
					],
					[
						'type'       => 'select',
						'heading'    => esc_attr__( 'Hits The', 'fusion-builder' ),
						'param_name' => 'start_viewport',
						'value'      => [
							'top'    => esc_attr__( 'Top Of viewport', 'fusion-builder' ),
							'center' => esc_attr__( 'Middle Of Viewport', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom Of Viewport', 'fusion-builder' ),
						],
						'default'    => 'bottom',
						'dependency' => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
						],
					],
					[
						'type'       => 'select',
						'heading'    => esc_attr__( 'End When', 'fusion-builder' ),
						'param_name' => 'end_element',
						'value'      => [
							'top'    => esc_attr__( 'Top Of Element', 'fusion-builder' ),
							'center' => esc_attr__( 'Middle Of Element', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom Of Element', 'fusion-builder' ),
						],
						'default'    => 'bottom',
						'dependency' => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
						],
					],
					[
						'type'       => 'select',
						'heading'    => esc_attr__( 'Hits The', 'fusion-builder' ),
						'param_name' => 'end_viewport',
						'value'      => [
							'top'    => esc_attr__( 'Top Of viewport', 'fusion-builder' ),
							'center' => esc_attr__( 'Middle Of Viewport', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom Of Viewport', 'fusion-builder' ),
						],
						'default'    => 'top',
						'dependency' => [
							[
								'element'  => 'type',
								'value'    => 'scroll',
								'operator' => '==',
							],
						],
					],

					/* Mouse effects */
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Mouse Effect Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose mouse effect type.', 'fusion-builder' ),
						'param_name'  => 'mouse_effect',
						'value'       => [
							'track' => esc_attr__( 'Tracking', 'fusion-builder' ),
							'tilt'  => esc_attr__( '3D Tilt', 'fusion-builder' ),
						],
						'default'     => 'track',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'mouse',
								'operator' => '==',
							],
						],
					],
					[
						'type'       => 'select',
						'heading'    => esc_attr__( 'Direction', 'fusion-builder' ),
						'param_name' => 'mouse_effect_direction',
						'value'      => [
							'same'     => esc_attr__( 'Same', 'fusion-builder' ),
							'opposite' => esc_attr__( 'Opposite', 'fusion-builder' ),
						],
						'default'    => 'opposite',
						'dependency' => [
							[
								'element'  => 'type',
								'value'    => 'mouse',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Speed', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the effect speed.', 'fusion-builder' ),
						'param_name'  => 'mouse_effect_speed',
						'value'       => '2',
						'min'         => '1',
						'max'         => '10',
						'step'        => '0.1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'mouse',
								'operator' => '==',
							],
						],
					],
					[
						'type'       => 'select',
						'heading'    => esc_attr__( 'Animation', 'fusion-builder' ),
						'param_name' => 'infinite_animation',
						'value'      => [
							'float'  => esc_attr__( 'Float', 'fusion-builder' ),
							'pulse'  => esc_attr__( 'Pulse', 'fusion-builder' ),
							'rotate' => esc_attr__( 'Rotate', 'fusion-builder' ),
							'wiggle' => esc_attr__( 'Wiggle', 'fusion-builder' ),
						],
						'default'    => 'float',
						'dependency' => [
							[
								'element'  => 'type',
								'value'    => 'infinite',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Speed', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the effect speed.', 'fusion-builder' ),
						'param_name'  => 'infinite_animation_speed',
						'value'       => '2',
						'min'         => '1',
						'max'         => '10',
						'step'        => '0.1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'infinite',
								'operator' => '==',
							],
						],
					],

				],
			],
			[
				'type'        => 'checkbox_button_set',
				'heading'     => esc_html__( 'Apply Motion Scroll Effects On', 'fusion-builder' ),
				'description' => esc_html__( 'Choose which devices the scroll effects will be applied to.', 'fusion-builder' ),
				'param_name'  => 'scroll_motion_devices',
				'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
				'value'       => fusion_builder_visibility_options( 'full' ),
				'default'     => fusion_builder_default_visibility( 'array' ),
			],
		];

		return $params;
	}

	/**
	 * Motion effects data attribute.
	 *
	 * @since 3.10
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @param array $attr The attributes.
	 * @return array
	 */
	public static function get_data_attr( $args, $attr ) {

		// Early exit if no motion effects.
		if ( ! is_array( $args ) || empty( $args['motion_effects'] ) ) {
			return;
		}

		$effects       = json_decode( base64_decode( $args['motion_effects'] ), true );
		$effects_array = [];

		if ( is_array( $effects ) && ! empty( $effects ) ) {
			foreach ( $effects as $effect ) {
				if ( ! empty( $effect['type'] ) ) {
					$effects_array[] = self::clean_empty( $effect );
				}
			}
		}

		if ( ! empty( $effects_array ) ) {
			$attr['data-motion-effects'] = wp_json_encode( $effects_array );
			Fusion_Dynamic_JS::enqueue_script( 'fusion-motion-effects' );
		}

		if ( ! empty( $args['scroll_motion_devices'] ) ) {
			$attr['data-scroll-devices'] = $args['scroll_motion_devices'];
		}

		return $attr;
	}

	/**
	 * Clean empty arguments.
	 *
	 * @since 4.0
	 * @access public
	 * @param array $args array.
	 * @return array
	 */
	public static function clean_empty( $args = [] ) {
		foreach ( (array) $args as $key => $value ) {
			if ( empty( $value ) && '0' !== $value && 0 !== $value ) {
				unset( $args[ $key ] );
			}
		}
		return $args;
	}
}
