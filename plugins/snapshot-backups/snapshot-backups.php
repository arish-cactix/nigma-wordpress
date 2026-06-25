<?php //phpcs:ignore
/**
 * Snapshot plugin
 *
 * @link         https://wpmudev.com/project/snapshot/
 * @package      snapshot
 *
 * Plugin Name:  Snapshot Pro
 * Plugin URI:   https://wpmudev.com/project/snapshot/
 * Description:  Make and schedule incremental backups of your WordPress websites and store them on secure cloud storage. Snapshot Backups are logged and can be restored with a click or manually with the included installer. Snapshot gives you simple, faster, managed backups that take up less space.
 * Version:      4.30.0
 * Network:      true
 * Text Domain:  snapshot
 * Author:       WPMU DEV
 * Author URI:   https://wpmudev.com
 * WDP ID:       3760011
 * License:      GNU General Public License (Version 2 - GPLv2)
 * Requires PHP: 7.4
 */

/*
Copyright 2007-2024 Incsub (https://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 – GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! defined( 'SNAPSHOT_BACKUPS_VERSION' ) ) {
	define( 'SNAPSHOT_BACKUPS_VERSION', '4.30.0' );
}

if ( ! defined( 'SNAPSHOT_DIR_PATH' ) ) {
	define( 'SNAPSHOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SNAPSHOT_PLUGIN_FILE' ) ) {
	define( 'SNAPSHOT_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'SNAPSHOT_BASE_NAME' ) ) {
	define( 'SNAPSHOT_BASE_NAME', plugin_basename( __FILE__ ) );
}

require_once SNAPSHOT_DIR_PATH . 'lib/constants.php';
require_once SNAPSHOT_DIR_PATH . 'lib/functions.php';
require_once SNAPSHOT_DIR_PATH . 'lib/loader.php';

$snapshot_activation = \WPMUDEV\Snapshot4\Activate::get_instance();

register_activation_hook(
	__FILE__,
	array( $snapshot_activation, 'boot' )
);

if ( ! function_exists( 'snapshot_onload' ) ) {
	/**
	 * Onload Snapshot.
	 */
	function snapshot_onload() {//phpcs:ignore Squiz.WhiteSpace.FunctionSpacing.BeforeFirst
		global $snapshot_activation;
		$plugin_version = get_option( 'snapshot_backups_version', '4.18.0' );

		if ( ! $plugin_version || version_compare( SNAPSHOT_BACKUPS_VERSION, $plugin_version, '>' ) && is_a( $snapshot_activation, 'WPMUDEV\Snapshot4\Activate' ) ) {
			$snapshot_activation->invoke_maybe_create_snapshot_action_logs_table();
			update_option( 'snapshot_backups_version', SNAPSHOT_BACKUPS_VERSION );
		}
	}

	add_action( 'plugins_loaded', 'snapshot_onload' );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WPMUDEV\Snapshot4\Cli::get()->init();
	return;
}

\WPMUDEV\Snapshot4\Main::get()->boot();