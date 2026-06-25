<?php
/**
 * Avada Builder background slider Helper class.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder background slider Helper class.
 *
 * @since 2.2
 */
class Fusion_Builder_Background_Slider_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Get background slider params.
	 *
	 * @since 3.8
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {
		return [
			[
				'type'        => 'upload_images',
				'heading'     => esc_attr__( 'Slider Images', 'fusion-builder' ),
				'description' => esc_attr__( 'Upload background slider Images.', 'fusion-builder' ),
				'param_name'  => 'background_slider_images',
				'value'       => '',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
			],
			[
				'type'        => 'image_focus_point',
				'heading'     => esc_attr__( 'Background Position', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose the position of the background slider images.', 'fusion-builder' ),
				'param_name'  => 'background_slider_position',
				'default'     => '50% 50%',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Skip Lazy Loading', 'fusion-builder' ),
				'description' => esc_attr__( 'Select whether you want to skip lazy loading on background slider images or not.', 'fusion-builder' ),
				'param_name'  => 'background_slider_skip_lazy_loading',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'default'     => 'no',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Loop', 'fusion-builder' ),
				'description' => esc_attr__( 'Enable background slider Loop.', 'fusion-builder' ),
				'param_name'  => 'background_slider_loop',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'default'     => 'yes',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Pause On Hover', 'fusion-builder' ),
				'description' => esc_attr__( 'Enable to pause background slider on hover.', 'fusion-builder' ),
				'param_name'  => 'background_slider_pause_on_hover',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'default'     => 'no',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Slideshow Speed', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the speed of the slideshow. 1000 = 1 second.', 'fusion-builder' ),
				'param_name'  => 'background_slider_slideshow_speed',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'step'        => 100,
				'value'       => 5000,
				'min'         => 100,
				'max'         => 10000,
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'select',
				'heading'     => esc_attr__( 'Animation', 'fusion-builder' ),
				'description' => esc_attr__( 'Select background slider animation.', 'fusion-builder' ),
				'param_name'  => 'background_slider_animation',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'default'     => 'fade',
				'value'       => [
					'fade'           => esc_attr__( 'Fade', 'fusion-builder' ),
					'slide'          => esc_attr__( 'Slide', 'fusion-builder' ),
					'stack'          => esc_attr__( 'Stack', 'fusion-builder' ),
					'zoom'           => esc_attr__( 'Zoom', 'fusion-builder' ),
					'slide-zoom-in'  => esc_attr__( 'Slide Zoom In', 'fusion-builder' ),
					'slide-zoom-out' => esc_attr__( 'Slide Zoom Out', 'fusion-builder' ),
				],
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'select',
				'heading'     => esc_attr__( 'Slider Direction', 'fusion-builder' ),
				'description' => esc_attr__( 'Select slide animation direction.', 'fusion-builder' ),
				'param_name'  => 'background_slider_direction',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'default'     => 'up',
				'value'       => [
					'up'    => esc_attr__( 'Up', 'fusion-builder' ),
					'down'  => esc_attr__( 'Down', 'fusion-builder' ),
					'right' => esc_attr__( 'Right', 'fusion-builder' ),
					'left'  => esc_attr__( 'Left', 'fusion-builder' ),
				],
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
					[
						'element'  => 'background_slider_animation',
						'value'    => 'slide',
						'operator' => '==',
					],
				],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Animation Speed', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the speed of slide transition from slide to slide. 1000 = 1 second.', 'fusion-builder' ),
				'param_name'  => 'background_slider_animation_speed',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'step'        => 100,
				'value'       => 800,
				'max'         => 2000,
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'select',
				'heading'     => esc_attr__( 'Blend Mode', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose how blending should work for background slider.', 'fusion-builder' ),
				'param_name'  => 'background_slider_blend_mode',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'value'       => [
					''            => esc_attr__( 'Default', 'fusion-builder' ),
					'multiply'    => esc_attr__( 'Multiply', 'fusion-builder' ),
					'screen'      => esc_attr__( 'Screen', 'fusion-builder' ),
					'overlay'     => esc_attr__( 'Overlay', 'fusion-builder' ),
					'darken'      => esc_attr__( 'Darken', 'fusion-builder' ),
					'lighten'     => esc_attr__( 'Lighten', 'fusion-builder' ),
					'color-dodge' => esc_attr__( 'Color Dodge', 'fusion-builder' ),
					'color-burn'  => esc_attr__( 'Color Burn', 'fusion-builder' ),
					'hard-light'  => esc_attr__( 'Hard Light', 'fusion-builder' ),
					'soft-light'  => esc_attr__( 'Soft Light', 'fusion-builder' ),
					'difference'  => esc_attr__( 'Difference', 'fusion-builder' ),
					'exclusion'   => esc_attr__( 'Exclusion', 'fusion-builder' ),
					'hue'         => esc_attr__( 'Hue', 'fusion-builder' ),
					'saturation'  => esc_attr__( 'Saturation', 'fusion-builder' ),
					'color'       => esc_attr__( 'Color', 'fusion-builder' ),
					'luminosity'  => esc_attr__( 'Luminosity', 'fusion-builder' ),
				],
				'default'     => '',
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'slider',
				],
				'dependency'  => [
					[
						'element'  => 'background_slider_images',
						'value'    => '',
						'operator' => '!=',
					],
				],
			],
		];
	}

	/**
	 * Get pattern element.
	 *
	 * @since 3.8
	 * @access public
	 * @param array $atts The element attributes.
	 * @return array
	 */
	public static function get_element( $atts, $type = 'container' ) {

		// Early exit if no pattern selected.
		if ( empty( $atts['background_slider_images'] ) ) {
			return;
		}

		$images     = explode( ',', $atts['background_slider_images'] );
		$slides     = '';
		$attributes = [];

		$attributes[] = 'data-type="' . $type . '"';
		$attributes[] = 'data-loop="' . $atts['background_slider_loop'] . '"';

		if ( ! empty( $atts['background_slider_animation'] ) ) {
			$attributes[] = 'data-animation="' . esc_attr( $atts['background_slider_animation'] ) . '"';
		}

		if ( ! empty( $atts['background_slider_slideshow_speed'] ) ) {
			$attributes[] = 'data-slideshow-speed="' . esc_attr( $atts['background_slider_slideshow_speed'] ) . '"';
		}

		if ( ! empty( $atts['background_slider_animation_speed'] ) ) {
			$attributes[] = 'data-animation-speed="' . esc_attr( $atts['background_slider_animation_speed'] ) . '"';
		}

		if ( ! empty( $atts['background_slider_direction'] ) ) {
			$attributes[] = 'data-direction="' . esc_attr( $atts['background_slider_direction'] ) . '"';
		}

		if ( 'yes' === $atts['background_slider_pause_on_hover'] ) {
			$attributes[] = 'data-pause_on_hover="' . esc_attr( $atts['background_slider_pause_on_hover'] ) . '"';
		}

		$inline_style = '';
		if ( ! empty( $atts['background_slider_position'] ) ) {
			$inline_style .= '--awb-image-position:' . $atts['background_slider_position'] . ';';
		}
		if ( ! empty( $atts['background_slider_blend_mode'] ) ) {
			$inline_style .= 'mix-blend-mode:' . $atts['background_slider_blend_mode'] . ';';
		}
		if ( '' !== $inline_style ) {
			$attributes[] = 'style="' . $inline_style . '"';
		}
		$image_attrs = [ 'class' => 'awb-background-slider__image' ];
		if ( 'yes' === $atts['background_slider_skip_lazy_loading'] ) {
			$image_attrs['skip-lazyload'] = true;
		}

		foreach ( $images as $id ) {
			$slides .= '<div class="swiper-slide">';
			$slides .= wp_get_attachment_image( $id, 'full', false, $image_attrs );
			$slides .= '</div>';
		}

		$element = '';
		if ( 'column' === $type ) {
			$column_inline_style = '';
			if ( ! empty( $atts['background_slider_blend_mode'] ) ) {
				$background_color_var = 'var(--awb-bg-color)';
				$background_image_var = 'var(--awb-bg-image)';
				$hover_or_link        = ( 'none' !== $atts['hover_type'] && ! empty( $atts['hover_type'] ) ) || ! empty( $atts['link'] );

				if ( $hover_or_link ) {
					$background_color_var = 'var(--awb-inner-bg-color)';
					$background_image_var = 'var(--awb-inner-bg-image)';
				}
				$column_inline_style .= 'background-color:' . $background_color_var . ';';

				$background_image = 'background-image: ' . $background_image_var . ';';
				if ( $atts['background_image'] ) {
					$background_image = 'background-image:' . $background_image_var . ', url(' . $atts['background_image'] . ');';
				}

				$column_inline_style .= $background_image;
			}
			$column_inline_style = '' !== $column_inline_style ? 'style="' . $column_inline_style . '"' : '';
			$element            .= '<div class="awb-column__background-slider" ' . $column_inline_style . '>';
		}

		$element .= '<div class="awb-background-slider" ' . join( ' ', $attributes ) . '><div class="swiper-wrapper">' . $slides . '</div></div>';

		if ( 'column' === $type ) {
			$element .= '</div>';
		}

		return $element;
	}
}
