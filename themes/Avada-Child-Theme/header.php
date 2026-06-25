<?php
/**
 * Header template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<!DOCTYPE html>
<html class="<?php avada_the_html_class(); ?>" <?php language_attributes(); ?>>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?php Avada()->head->the_viewport(); ?>

	<?php wp_head(); ?>

	<?php
	/**
	 * The setting below is not sanitized.
	 * In order to be able to take advantage of this,
	 * a user would have to gain access to the database
	 * in which case this is the least of your worries.
	 */
	echo apply_filters( 'avada_space_head', Avada()->settings->get( 'space_head' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WQ8LTS8');</script>
<!-- End Google Tag Manager -->

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-Y0PR2QD6KV"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-Y0PR2QD6KV');
    </script>
</head>

<?php
$object_id      = get_queried_object_id();
$c_page_id      = Avada()->fusion_library->get_page_id();
$wrapper_class  = 'fusion-wrapper';
$wrapper_class .= ( is_page_template( 'blank.php' ) ) ? ' wrapper_blank' : '';
?>
<body <?php body_class(); ?> id="top">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WQ8LTS8"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
	<?php do_action( 'avada_before_body_content' ); ?>
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'Avada' ); ?></a>

	<div id="boxed-wrapper">
		<div class="fusion-sides-frame"></div>
		<div id="wrapper" class="<?php echo esc_attr( $wrapper_class ); ?>">
			<div id="home" style="position:relative;top:-1px;"></div>
			<?php avada_header_template( 'below', ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() ) ); ?>
			<?php if ( 'left' === fusion_get_option( 'header_position' ) || 'right' === fusion_get_option( 'header_position' ) ) : ?>
				<?php avada_side_header(); ?>
			<?php endif; ?>

			<?php avada_sliders_container(); ?>

			<?php avada_header_template( 'above', ( is_archive() || Avada_Helper::bbp_is_topic_tag() ) && ! ( class_exists( 'WooCommerce' ) && is_shop() ) ); ?>

			<div class="avada-page-titlebar-wrapper">
				<div class="overlay"></div>
				<?php avada_current_page_title_bar( $c_page_id ); ?>
			</div>

			<?php
			$row_css    = '';
			$main_class = '';

			if ( apply_filters( 'fusion_is_hundred_percent_template', false, $c_page_id ) ) {
				$row_css    = 'max-width:100%;';
				$main_class = 'width-100';
			}

			if ( fusion_get_option( 'content_bg_full' ) && 'no' !== fusion_get_option( 'content_bg_full' ) ) {
				$main_class .= ' full-bg';
			}
			do_action( 'avada_before_main_container' );
			?>
			
			<!-- Video icon -->
			<div class="video-icon btn_up">
				<a title="Go Up" class="btn-icon scroll" href="#top" data-animation="bounceIn" data-delay=".4s"><i class="fas fa-arrow-up"></i></a>
			</div>
			<div class="video-icon">
				<a title="Go Down" class="btn-icon scroll" href="#go-down" data-animation="bounceIn" data-delay=".4s"><i class="fas fa-arrow-down"></i></a>
			</div>
			
			<main id="main" class="clearfix <?php echo esc_attr( $main_class ); ?>">
				<div class="fusion-row" style="<?php echo esc_attr( $row_css ); ?>">
