<?php
/**
 * WPMUDEV HUB
 *
 * @package     Wpmudev_Hub_Plugin
 * @author      WPMU DEV
 * @copyright   2020-2024 WPMU DEV
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:  The Hub Client
 * Plugin URI:   https://wpmudev.com/
 * Description:  Whitelabel Hub
 * Version:      2.2.2
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Author:       WPMU DEV
 * Author URI:   https://wpmudev.com/
 * Text Domain:  thc
 * Domain Path:  /i18n/languages
 * License:      GPL v2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.txt
 * Network:      true
 * WDP ID:       3779636
 */

define( 'WPMUDEV_HUB_VERSION', '2.2.2' );

if ( ! defined( 'WPMUDEV_HUB_MIN_PHP_VERSION' ) ) {
	define( 'WPMUDEV_HUB_MIN_PHP_VERSION', '7.4' );
}

/**
 * Display admin notice and prevent plugin code execution, if the server is
 * using old/insecure PHP version.
 */
if ( version_compare( phpversion(), WPMUDEV_HUB_MIN_PHP_VERSION, '<' ) ) {
	if ( ! function_exists( 'wpmudev_hub_unsupported_php_version_notice' ) ) {
		/**
		 * Display admin notice, if the site is using unsupported PHP version.
		 */
		function wpmudev_hub_unsupported_php_version_notice() {

			?>
			<div class="notice notice-error">
				<p>
					<?php
					$allowed_html_tags = array(
						'strong' => array(),
						'a'      => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					);
					printf(
						wp_kses(
						/* translators: %1$s - URL to an article about our hosting benefits. */
							__(
								'Your site is running an outdated version of PHP that is no longer supported or receiving security updates. Please update PHP to at least version %1$s at your current hosting provider in order to activate The Hub Client, or consider switching to <a href="%2$s" target="_blank" rel="noopener noreferrer">WPMU DEV Hosting</a>.',
								'thc'
							),
							$allowed_html_tags
						),
						esc_html( WPMUDEV_HUB_MIN_PHP_VERSION ),
						'https://wpmudev.com/hosting/'
					);
					?>
				</p>
			</div>

			<?php

			// In case this is on plugin activation.
			if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
				unset( $_GET['activate'] ); //phpcs:ignore
			}
		}
	}
	add_action( 'admin_notices', 'wpmudev_hub_unsupported_php_version_notice' );

	return;
}

define( 'WPMUDEV_HUB_PLUGIN_FILE', __FILE__ );
// passed above, init
require_once plugin_dir_path( __FILE__ ) . 'init.php';