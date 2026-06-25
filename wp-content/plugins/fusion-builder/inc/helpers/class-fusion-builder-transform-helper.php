<?php
/**
 * Avada Builder Transform Helper class.
 *
 * @package Avada-Builder
 * @since 3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Transform Helper class.
 *
 * @since 3.8
 */
class Fusion_Builder_Transform_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 3.8
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Get transform params.
	 *
	 * @since 3.8
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		$selector_base = isset( $args['selector_base'] ) ? $args['selector_base'] : '';

		$states            = [ 'regular', 'hover' ];
		$transform_options = [
			[
				'type'             => 'subgroup',
				'heading'          => esc_attr__( 'Transform', 'fusion-builder' ),
				'description'      => esc_attr__( 'Use transform options to scale, translate, rotate and skew the element.', 'fusion-builder' ),
				'param_name'       => 'transform_type',
				'default'          => 'regular',
				'group'            => esc_attr__( 'Extras', 'fusion-builder' ),
				'remove_from_atts' => true,
				'value'            => [
					'regular' => esc_attr__( 'Regular', 'fusion-builder' ),
					'hover'   => esc_attr__( 'Hover', 'fusion-builder' ),
				],
				'icons'            => [
					'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
					'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
				],
			],
		];

		$transform_options[] = [
			'type'        => 'select',
			'heading'     => esc_attr__( 'Hover Element', 'fusion-builder' ),
			'description' => esc_attr__( 'Select which element should be hovered to apply the transform hover options.', 'fusion-builder' ),
			'param_name'  => 'transform_hover_element',
			'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
			'default'     => 'self',
			'value'       => [
				'self'   => esc_attr__( 'Self', 'fusion-builder' ),
				'parent' => esc_attr__( 'Parent', 'fusion-builder' ),
			],
			'subgroup'    => [
				'name' => 'transform_type',
				'tab'  => 'hover',
			],
			'callback'    => [
				'function' => 'fusion_update_transform_style',
				'args'     => [
					'selector_base' => $selector_base,
				],
			],
		];

		foreach ( $states as $key ) {
			$transform_options = array_merge(
				$transform_options,
				[
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Scale X', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the scale in the horizontal direction.', 'fusion-builder' ),
						'param_name'  => 'transform_scale_x' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '1',
						'min'         => '0',
						'max'         => '2',
						'step'        => '0.01',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Scale Y', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the scale in the vertical direction.', 'fusion-builder' ),
						'param_name'  => 'transform_scale_y' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '1',
						'min'         => '0',
						'max'         => '2',
						'step'        => '0.01',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Translate X', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the translate in the horizontal direction. in pixels.', 'fusion-builder' ),
						'param_name'  => 'transform_translate_x' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-300',
						'max'         => '300',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Translate Y', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the translate in the vertical direction. in pixels.', 'fusion-builder' ),
						'param_name'  => 'transform_translate_y' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-300',
						'max'         => '300',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Rotate', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the rotation of the element.', 'fusion-builder' ),
						'param_name'  => 'transform_rotate' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-360',
						'max'         => '360',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Skew X', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the skew in the horizontal direction.', 'fusion-builder' ),
						'param_name'  => 'transform_skew_x' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-100',
						'max'         => '100',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
					[
						'type'        => 'range',
						'reset'       => true,
						'heading'     => esc_attr__( 'Skew Y', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the skew in the vertical direction.', 'fusion-builder' ),
						'param_name'  => 'transform_skew_y' . ( 'regular' !== $key ? '_' . $key : '' ),
						'value'       => '0',
						'min'         => '-100',
						'max'         => '100',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'transform_type',
							'tab'  => $key,
						],
						'callback'    => [
							'function' => 'fusion_update_transform_style',
							'args'     => [
								'selector_base' => $selector_base,
							],
						],
					],
				]
			);
		}

		$transform_options[] = [
			'type'        => 'image_focus_point',
			'heading'     => esc_attr__( 'Transform Origin', 'fusion-builder' ),
			'description' => esc_attr__( 'Set the location of origin point for transform.', 'fusion-builder' ),
			'param_name'  => 'transform_origin',
			'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
			'mode'        => 'position',
			'subgroup'    => [
				'name' => 'transform_type',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_update_transform_style',
				'args'     => [
					'selector_base' => $selector_base,
				],
			],
		];

		return $transform_options;
	}

	/**
	 * Get transform styles
	 *
	 * @since 3.8
	 * @access public
	 * @param array  $atts The transform parameters.
	 * @param string $state Element state, regular or hover.
	 * @return string
	 */
	public static function get_transform_styles( $atts, $state = 'regular' ) {

		$state_suffix       = 'regular' === $state ? '' : '_hover';
		$other_state_suffix = 'regular' === $state ? '_hover' : '';

		$transforms = [
			'transform_scale_x'     => [
				'property' => 'scaleX',
				'unit'     => '',
				'default'  => '1',
			],
			'transform_scale_y'     => [
				'property' => 'scaleY',
				'unit'     => '',
				'default'  => '1',
			],
			'transform_translate_x' => [
				'property' => 'translateX',
				'unit'     => 'px',
				'default'  => '0',
			],
			'transform_translate_y' => [
				'property' => 'translateY',
				'unit'     => 'px',
				'default'  => '0',
			],
			'transform_rotate'      => [
				'property' => 'rotate',
				'unit'     => 'deg',
				'default'  => '0',
			],
			'transform_skew_x'      => [
				'property' => 'skewX',
				'unit'     => 'deg',
				'default'  => '0',
			],
			'transform_skew_y'      => [
				'property' => 'skewY',
				'unit'     => 'deg',
				'default'  => '0',
			],
		];

		$transform_style = '';
		foreach ( $transforms as $transform_id => $transform ) {
			$transform_id_state = $transform_id . $state_suffix;
			$transform_id_other = $transform_id . $other_state_suffix;
			if ( floatval( $transform['default'] ) !== floatval( $atts[ $transform_id_state ] ) || floatval( $transform['default'] ) !== floatval( $atts[ $transform_id_other ] ) ) {
				$transform_style .= $transform['property'] . '(' . $atts[ $transform_id_state ] . $transform['unit'] . ') ';
			}
		}

		return trim( $transform_style );
	}

	/**
	 * Get the transform variables.
	 *
	 * @param array  $atts The arguments/options of the element.
	 * @param string $var_name The variable name.
	 * @param string $var_name_hover The hover variable name.
	 * @param string $var_name_parent_hover The parent hover variable name.
	 * @return string
	 */
	public static function get_transform_style_vars( $atts, $var_name, $var_name_hover, $var_name_parent_hover ) {
		$transform_var        = self::get_transform_styles( $atts, 'regular' );
		$transform_var_hover  = self::get_transform_styles( $atts, 'hover' );
		$transform_vars_hover = '';

		if ( '' !== $transform_var ) {
			$transform_var = $var_name . ':' . $transform_var . ';';
		}
		if ( '' !== $transform_var_hover ) {
			$transform_vars_hover = $var_name_hover . ':' . $transform_var_hover . ';';

			if ( 'parent' === $atts['transform_hover_element'] ) {
				$transform_vars_hover .= $var_name_parent_hover . ':' . $transform_var_hover . ';';
			}
		}

		return $transform_var . $transform_vars_hover;
	}
}
