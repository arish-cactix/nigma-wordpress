<?php
/**
 * Child theme functions and definitions.
 *
 * @package Avada-Child-Theme
 */

/**
 * Enqueue child theme styles and scripts.
 */
function theme_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array(), '1.1' );
	wp_enqueue_style( 'child-assets-style', get_stylesheet_directory_uri() . '/assets/css/style.css', array(), '1.0' );
	wp_enqueue_style( 'child-bootstrap', get_stylesheet_directory_uri() . '/assets/css/bootstrap.min.css', array(), '1.0' );
	wp_enqueue_style( 'child-owl-carousel', get_stylesheet_directory_uri() . '/assets/css/owl.carousel.min.css', array(), '1.0' );
	wp_enqueue_style( 'child-slicknav', get_stylesheet_directory_uri() . '/assets/css/slicknav.css', array(), '1.0' );
	wp_enqueue_style( 'child-flaticon', get_stylesheet_directory_uri() . '/assets/css/flaticon.css', array(), '1.0' );
	wp_enqueue_style( 'child-animate', get_stylesheet_directory_uri() . '/assets/css/animate.min.css', array(), '1.0' );
	wp_enqueue_style( 'child-animated-headline', get_stylesheet_directory_uri() . '/assets/css/animated-headline.css', array(), '1.0' );
	wp_enqueue_style( 'child-magnific-popup', get_stylesheet_directory_uri() . '/assets/css/magnific-popup.css', array(), '1.0' );
	wp_enqueue_style( 'child-fontawesome', get_stylesheet_directory_uri() . '/assets/css/fontawesome-all.min.css', array(), '1.0' );
	wp_enqueue_style( 'child-themify-icons', get_stylesheet_directory_uri() . '/assets/css/themify-icons.css', array(), '1.0' );
	wp_enqueue_style( 'child-slick', get_stylesheet_directory_uri() . '/assets/css/slick.css', array(), '1.0' );
	wp_enqueue_style( 'child-nice-select', get_stylesheet_directory_uri() . '/assets/css/nice-select.css', array(), '1.0' );
	wp_enqueue_style( 'child-progressbar-barfiller', get_stylesheet_directory_uri() . '/assets/css/progressbar_barfiller.css', array(), '1.0' );
	wp_enqueue_style( 'child-gijgo', get_stylesheet_directory_uri() . '/assets/css/gijgo.css', array(), '1.0' );

	wp_enqueue_script( 'child-modernizr', get_stylesheet_directory_uri() . '/assets/js/vendor/modernizr-3.5.0.min.js', array(), '1.0', true );
	wp_enqueue_script( 'child-popper', get_stylesheet_directory_uri() . '/assets/js/popper.min.js', array(), '1.0', true );
	wp_enqueue_script( 'child-bootstrap', get_stylesheet_directory_uri() . '/assets/js/bootstrap.min.js', array(), '1.0', true );
	wp_enqueue_script( 'child-slicknav', get_stylesheet_directory_uri() . '/assets/js/jquery.slicknav.min.js', array(), '1.0', true );
	wp_enqueue_script( 'child-owl-carousel', get_stylesheet_directory_uri() . '/assets/js/owl.carousel.min.js', array(), '1.0', true );
	wp_enqueue_script( 'child-slick', get_stylesheet_directory_uri() . '/assets/js/slick.min.js', array(), '1.0', true );
	wp_enqueue_script( 'child-wow', get_stylesheet_directory_uri() . '/assets/js/wow.min.js', array(), '1.0', true );
	wp_enqueue_script( 'child-animated-headline', get_stylesheet_directory_uri() . '/assets/js/animated.headline.js', array(), '1.0', true );
	wp_enqueue_script( 'child-magnific-popup', get_stylesheet_directory_uri() . '/assets/js/jquery.magnific-popup.js', array(), '1.0', true );
	wp_enqueue_script( 'child-plugins', get_stylesheet_directory_uri() . '/assets/js/plugins.js', array(), '1.0', true );
	wp_enqueue_script( 'child-particles', get_stylesheet_directory_uri() . '/assets/js/particles.min.js', array(), '1.0', true );
	wp_enqueue_script( 'child-particle', get_stylesheet_directory_uri() . '/assets/js/particle.js', array(), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

/**
 * Load child theme text domain.
 */
function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );
