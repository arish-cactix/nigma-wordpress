<?php
/**
 * Template used for pages.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<?php get_header(); ?>
<section id="content" style="<?php echo esc_attr( apply_filters( 'awb_content_tag_style', '' ) ); ?>">
	<div class="post-content">
		<?php
			$dummy_post = Fusion_Dummy_Post::get_dummy_post();
			echo do_shortcode( $dummy_post->post_content );
			do_action( 'awb_off_canvas_preview_content' );
		?>
	</div>
</section>
<?php do_action( 'avada_after_content' ); ?>
<?php
get_footer();
