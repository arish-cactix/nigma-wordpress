<?php
/**
 * Plugin Name: NIGMA Content Security Policy
 * Description: Applies Content-Security-Policy header on all frontend responses.
 * Version:     1.0
 *
 * @package NIGMA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'send_headers', 'nigma_send_csp_header' );

/**
 * Send the Content-Security-Policy header on all frontend responses.
 */
function nigma_send_csp_header() {
	if ( is_admin() ) {
		return;
	}

	$policy = implode( '; ', nigma_csp_directives() );

	header( 'Content-Security-Policy: ' . $policy );
}

/**
 * Return the list of CSP directives.
 *
 * @return array
 */
function nigma_csp_directives() {
	return array(

		// Fallback for any fetch directive not listed below.
		"default-src 'self'",

		/*
		 * Scripts: 'unsafe-inline' and 'unsafe-eval' required by Avada builder and WordPress core.
		 * GTM loads GA and other tags from googletagmanager.com.
		 * YouTube embeds load iframe_api from youtube.com and player JS from s.ytimg.com.
		 * reCAPTCHA loads api.js from google.com and the library from gstatic.com.
		 */
		"script-src 'self' 'unsafe-inline' 'unsafe-eval'"
			. ' https://www.googletagmanager.com'
			. ' https://www.google-analytics.com'
			. ' https://ssl.google-analytics.com'
			. ' https://www.youtube.com'
			. ' https://s.ytimg.com'
			. ' https://www.google.com'
			. ' https://www.gstatic.com',

		// Styles: 'unsafe-inline' required by Avada. fonts.googleapis.com serves Google Fonts CSS.
		"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",

		// Fonts: fonts.gstatic.com serves woff2 files. data: used by some icon fonts as base64.
		"font-src 'self' https://fonts.gstatic.com data:",

		// Images: data: for inline SVGs, blob: for media uploader, https: for all external images.
		"img-src 'self' data: blob: https:",

		// Fetch/XHR: GA4 beacons go to multiple Google domains.
		"connect-src 'self'"
			. ' https://www.google-analytics.com'
			. ' https://analytics.google.com'
			. ' https://stats.g.doubleclick.net'
			. ' https://www.googletagmanager.com',

		// Iframes: GTM noscript fallback, YouTube/Vimeo embeds, Google Maps and reCAPTCHA.
		"frame-src 'self'"
			. ' https://www.googletagmanager.com'
			. ' https://www.youtube.com'
			. ' https://player.vimeo.com'
			. ' https://www.google.com',

		// Media: blob: used by Avada self-hosted video background player.
		"media-src 'self' blob:",

		// Workers: blob: used by some JS libraries for inline workers.
		"worker-src 'self' blob:",

		// Blocks all plugin objects (Flash etc.) — nothing legitimate uses these.
		"object-src 'none'",

		// Restricts the base tag to same origin, prevents base-tag hijacking.
		"base-uri 'self'",

		// Form submissions must target the same origin.
		"form-action 'self'",

	);
}
