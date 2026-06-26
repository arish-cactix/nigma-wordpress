<?php

/**
 * Enqueue child theme styles and scripts.
 */
function theme_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [], '1.1' );
	wp_enqueue_style( 'child-assets-style', get_stylesheet_directory_uri() . '/assets/css/style.css', [] );
	wp_enqueue_style( 'child-bootstrap', get_stylesheet_directory_uri() . '/assets/css/bootstrap.min.css', [] );
	wp_enqueue_style( 'child-owl-carousel', get_stylesheet_directory_uri() . '/assets/css/owl.carousel.min.css', [] );
	wp_enqueue_style( 'child-slicknav', get_stylesheet_directory_uri() . '/assets/css/slicknav.css', [] );
	wp_enqueue_style( 'child-flaticon', get_stylesheet_directory_uri() . '/assets/css/flaticon.css', [] );
	wp_enqueue_style( 'child-animate', get_stylesheet_directory_uri() . '/assets/css/animate.min.css', [] );
	wp_enqueue_style( 'child-animated-headline', get_stylesheet_directory_uri() . '/assets/css/animated-headline.css', [] );
	wp_enqueue_style( 'child-magnific-popup', get_stylesheet_directory_uri() . '/assets/css/magnific-popup.css', [] );
	wp_enqueue_style( 'child-fontawesome', get_stylesheet_directory_uri() . '/assets/css/fontawesome-all.min.css', [] );
	wp_enqueue_style( 'child-themify-icons', get_stylesheet_directory_uri() . '/assets/css/themify-icons.css', [] );
	wp_enqueue_style( 'child-slick', get_stylesheet_directory_uri() . '/assets/css/slick.css', [] );
	wp_enqueue_style( 'child-nice-select', get_stylesheet_directory_uri() . '/assets/css/nice-select.css', [] );
	wp_enqueue_style( 'child-progressbar-barfiller', get_stylesheet_directory_uri() . '/assets/css/progressbar_barfiller.css', [] );
	wp_enqueue_style( 'child-gijgo', get_stylesheet_directory_uri() . '/assets/css/gijgo.css', [] );

	wp_enqueue_script( 'child-modernizr', get_stylesheet_directory_uri() . '/assets/js/vendor/modernizr-3.5.0.min.js', [], '', true );
	wp_enqueue_script( 'child-popper', get_stylesheet_directory_uri() . '/assets/js/popper.min.js', [], '', true );
	wp_enqueue_script( 'child-bootstrap', get_stylesheet_directory_uri() . '/assets/js/bootstrap.min.js', [], '', true );
	wp_enqueue_script( 'child-slicknav', get_stylesheet_directory_uri() . '/assets/js/jquery.slicknav.min.js', [], '', true );
	wp_enqueue_script( 'child-owl-carousel', get_stylesheet_directory_uri() . '/assets/js/owl.carousel.min.js', [], '', true );
	wp_enqueue_script( 'child-slick', get_stylesheet_directory_uri() . '/assets/js/slick.min.js', [], '', true );
	wp_enqueue_script( 'child-wow', get_stylesheet_directory_uri() . '/assets/js/wow.min.js', [], '', true );
	wp_enqueue_script( 'child-animated-headline', get_stylesheet_directory_uri() . '/assets/js/animated.headline.js', [], '', true );
	wp_enqueue_script( 'child-magnific-popup', get_stylesheet_directory_uri() . '/assets/js/jquery.magnific-popup.js', [], '', true );
	wp_enqueue_script( 'child-plugins', get_stylesheet_directory_uri() . '/assets/js/plugins.js', [], '', true );
	wp_enqueue_script( 'child-particles', get_stylesheet_directory_uri() . '/assets/js/particles.min.js', [], '', true );
	wp_enqueue_script( 'child-particle', get_stylesheet_directory_uri() . '/assets/js/particle.js', [], '', true );
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
