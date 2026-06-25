<?php
/**
 * Template used for mega menu.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$styles  = false;
$classes = [];
$id      = get_queried_object_id();
$width   = fusion_data()->post_meta( $id )->get( 'megamenu_wrapper_width' );

$css_var = 'var(--site_width)';
if ( 'viewport_width' === $width ) {
	$css_var   = 'calc(100vw - var(--awb-scrollbar-width,10px))';
	$classes[] = 'has-viewport-width';
} elseif ( 'custom_width' === $width ) {
	$max_width = fusion_data()->post_meta( $id )->get( 'megamenu_wrapper_max_width' );
	$css_var   = ! empty( $max_width ) ? Fusion_Sanitize::number( $max_width ) . 'px' : '1200px';
}
?>
<?php get_header(); ?>
<style>
	.post,
	#main,
	.awb-mega-menu-content.has-viewport-width {
		padding: 0 !important;
		margin: 0 !important;
	}
	.avada-page-titlebar-wrapper,
	#sliders-container,
	.fusion-footer,
	.fusion-header-wrapper {
		display: none !important;
	}
	.awb-mega-menu-content {
		margin: auto;
	}
	.awb-mega-menu-content .fusion-fullwidth {
		width: 100%;
	}
	#main > .fusion-row,
	.awb-mega-menu-content .fusion-fullwidth .fusion-row {
		max-width: 100vw !important;
	}
</style>
<section id="content" style="<?php echo esc_attr( apply_filters( 'awb_content_tag_style', '' ) ); ?>">
	<div class="post-content awb-mega-menu-content <?php echo esc_attr( join( ' ', $classes ) ); ?>" style="width:<?php echo esc_attr( $css_var ); ?>; margin: auto;">
		<?php
			global $post, $wp_query;
			$mega_menu                         = $post;
			$target                            = Fusion_Template_Builder()->get_target_example( $post->ID );
			$option                            = fusion_get_page_option( 'dynamic_content_preview_type', $post->ID );
			FusionBuilder()->editing_mega_menu = true;

			add_filter(
				'fusion_dynamic_post_id',
				function () use ( $target ) {
					if ( property_exists( $target, 'term_id' ) ) {
						return $target->term_id;
					}
					return $target->ID;
				},
				10
			);

			add_filter(
				'fusion_component_element_target',
				function () use ( $target ) {
					return $target;
				},
				10
			);

			add_filter(
				'fusion_app_preview_data',
				function ( $data ) {
					$data['is_fusion_element']   = true;
					$data['fusion_element_type'] = 'mega_menus';
					$data['template_category']   = 'mega_menus';

					return $data;
				},
				20
			);

			if ( property_exists( $target, 'term_id' ) ) {
				$GLOBALS['wp_query']->is_tax         = true;
				$GLOBALS['wp_query']->is_archive     = true;
				$GLOBALS['wp_query']->queried_object = $target;
			} else {
				$post = $target;
				$wp_query->setup_postdata( $target );
			}

			FusionBuilder()->mega_menu_data['is_rendering'] = true;
			Fusion_Template_Builder()->render_content( $mega_menu, true );
			FusionBuilder()->mega_menu_data['is_rendering'] = false;
			?>
	</div>
</section>
<?php do_action( 'avada_after_content' ); ?>
<?php get_footer(); ?>
