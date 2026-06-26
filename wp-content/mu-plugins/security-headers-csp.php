<?php
/**
 * Plugin Name: NIGMA Content Security Policy
 * Description: Sends Content-Security-Policy-Report-Only on all frontend responses.
 *              Review violations in browser DevTools (F12 → Console).
 *              Switch header name to Content-Security-Policy to enforce once clean.
 * Version:     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'send_headers', 'nigma_send_csp_header' );

function nigma_send_csp_header() {
	if ( is_admin() ) {
		return;
	}

	$policy = implode( '; ', nigma_csp_directives() );

	// Report-only: logs violations to the browser console, blocks nothing.
	// When violations are resolved, change the header name to:
	//   Content-Security-Policy
	header( 'Content-Security-Policy-Report-Only: ' . $policy );
}

function nigma_csp_directives() {
	return [

		// Fallback for any fetch directive not listed below.
		"default-src 'self'",

		// Scripts
		// 'unsafe-inline' and 'unsafe-eval' are required by Avada builder and WordPress core.
		// GTM loads Google Analytics and other tags dynamically from googletagmanager.com.
		// YouTube embeds load iframe_api from youtube.com and player JS from s.ytimg.com.
		// reCAPTCHA loads api.js from google.com and the library from gstatic.com.
		"script-src 'self' 'unsafe-inline' 'unsafe-eval'"
			. ' https://www.googletagmanager.com'
			. ' https://www.google-analytics.com'
			. ' https://ssl.google-analytics.com'
			. ' https://www.youtube.com'
			. ' https://s.ytimg.com'
			. ' https://www.google.com'
			. ' https://www.gstatic.com',

		// Styles
		// 'unsafe-inline' is required by Avada (extensive inline styles).
		// fonts.googleapis.com serves the Google Fonts CSS stylesheet.
		"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",

		// Fonts
		// fonts.gstatic.com serves the woff2 font files referenced in Avada's compiled CSS.
		// data: is used by some icon fonts embedded as base64.
		"font-src 'self' https://fonts.gstatic.com data:",

		// Images
		// data: is used by WordPress core (e.g. inline SVGs, editor).
		// blob: is used by the media uploader.
		// https: covers all external HTTPS images including Gravatar (secure.gravatar.com)
		//   on production. Locally, WordPress generates http:// Gravatar URLs because the
		//   local environment is HTTP — this triggers a report-only violation that will not
		//   appear on production.
		"img-src 'self' data: blob: https:",

		// Fetch / XHR
		// GA4 sends analytics beacons to multiple Google domains.
		"connect-src 'self'"
			. ' https://www.google-analytics.com'
			. ' https://analytics.google.com'
			. ' https://stats.g.doubleclick.net'
			. ' https://www.googletagmanager.com',

		// Iframes
		// GTM uses a noscript iframe fallback.
		// YouTube and Vimeo for any embedded video elements.
		// google.com covers Google Maps and reCAPTCHA iframes.
		"frame-src 'self'"
			. ' https://www.googletagmanager.com'
			. ' https://www.youtube.com'
			. ' https://player.vimeo.com'
			. ' https://www.google.com',

		// Media
		// blob: is used by Avada's self-hosted video background player.
		"media-src 'self' blob:",

		// Workers
		// blob: is used by some JS libraries for inline workers.
		"worker-src 'self' blob:",

		// Blocks all plugin objects (Flash etc.) — nothing legitimate uses these.
		"object-src 'none'",

		// Restricts the <base> tag to the same origin — prevents base-tag hijacking.
		"base-uri 'self'",

		// Form submissions must target the same origin.
		"form-action 'self'",

	];
}
