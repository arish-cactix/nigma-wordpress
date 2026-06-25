<?php
/**
 * Portfolio Template.
 *
 * @package Avada-Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
if ( ! class_exists( 'Avada' ) ) {
	exit( 'This feature requires the Avada theme.' );
}
?>
<?php get_header(); ?>
	<section id="content" class="<?php echo esc_attr( apply_filters( 'awb_content_tag_class', '' ) ); ?>" style="<?php echo esc_attr( apply_filters( 'awb_content_tag_style', '' ) ); ?>">
		<?php require 'portfolio-archive-layout.php'; ?>
	</section>
	<?php do_action( 'avada_after_content' ); ?>
<?php
get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
