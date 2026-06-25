<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/style.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/bootstrap.min.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/owl.carousel.min.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/slicknav.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/flaticon.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/animate.min.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/animated-headline.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/magnific-popup.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/fontawesome-all.min.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/themify-icons.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/slick.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/nice-select.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/progressbar_barfiller.css', [] );
	wp_enqueue_style( 'child-style-2', get_stylesheet_directory_uri() . '/assets/css/gijgo.css', [] );
	
	
	/*JQuery*/
	wp_enqueue_script( 'script-1', get_stylesheet_directory_uri() . '/assets/js/vendor/modernizr-3.5.0.min.js', [], '', true );
	 
    /*Jquery, Popper, Bootstrap */
	/*wp_enqueue_script( 'script-2', get_stylesheet_directory_uri() . '/assets/js/vendor/jquery-1.12.4.min.js', [], '', true );*/
	wp_enqueue_script( 'script-3', get_stylesheet_directory_uri() . '/assets/js/popper.min.js', [], '', true );
	wp_enqueue_script( 'script-4', get_stylesheet_directory_uri() . '/assets/js/bootstrap.min.js', [], '', true );
		 
    /* Jquery Mobile Menu */
	wp_enqueue_script( 'script-5', get_stylesheet_directory_uri() . '/assets/js/jquery.slicknav.min.js', [], '', true );

    /* Jquery Slick , Owl-Carousel Plugins */
	wp_enqueue_script( 'script-6', get_stylesheet_directory_uri() . '/assets/js/owl.carousel.min.js', [], '', true );
	wp_enqueue_script( 'script-7', get_stylesheet_directory_uri() . '/assets/js/slick.min.js', [], '', true );
		 
	/*One Page, Animated-HeadLin*/	 
	wp_enqueue_script( 'script-8', get_stylesheet_directory_uri() . '/assets/js/wow.min.js', [], '', true );
	wp_enqueue_script( 'script-9', get_stylesheet_directory_uri() . '/assets/js/animated.headline.js', [], '', true );
	wp_enqueue_script( 'script-10', get_stylesheet_directory_uri() . '/assets/js/jquery.magnific-popup.js', [], '', true );
	
	/*Jquery Plugins, main Jquery*/
	wp_enqueue_script( 'script-11', get_stylesheet_directory_uri() . '/assets/js/plugins.js', [], '', true );
	//wp_enqueue_script( 'script-12', get_stylesheet_directory_uri() . '/assets/js/main.js', [], '', true );
	
	/*Jquery Particles*/
	wp_enqueue_script( 'script-13', get_stylesheet_directory_uri() . '/assets/js/particles.min.js', [], '', true );
	wp_enqueue_script( 'script-14', get_stylesheet_directory_uri() . '/assets/js/particle.js', [], '', true );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );
