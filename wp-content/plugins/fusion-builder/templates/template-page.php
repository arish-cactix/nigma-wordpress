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
	<?php if ( have_posts() && ! is_search() && ! is_404() && ! is_archive() && ! ( ! is_front_page() && is_home() ) && ! awb_is_woo_order_received_page() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<div class="post-content">
					<?php do_action( 'fusion_template_content' ); ?>
				</div>
			</div>
		<?php endwhile; ?>
	<?php else : ?>
		<div class="post-content">
			<?php do_action( 'fusion_template_content' ); ?>
		</div>
	<?php endif; ?>
</section>
<?php do_action( 'avada_after_content' ); ?>
<?php get_footer(); ?>
