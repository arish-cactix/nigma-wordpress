<?php // phpcs:ignore
/**
 * Uninstall file.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Log;
use WPMUDEV\Snapshot4\Main;

// If uninstall not called from WordPress exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( class_exists( Main::class ) ) {
	return;
}

if ( ! empty( get_site_option( 'snapshot_remove_on_uninstall' ) ) ) {
	delete_site_option( 'snapshot_global_exclusions' );
	delete_site_option( 'snapshot_remove_on_uninstall' );
	delete_site_option( 'snapshot_email_settings' );
	delete_site_option( 'snapshot_exclude_large' );
	delete_site_option( 'snapshot_excluded_tables' );

	// Delete configs.
	delete_site_option( 'snapshot-presets_config' );

	update_site_option( 'snapshot_activate_schedule', 0 );

	// Transients related with snapshot backup export notifications.
	delete_site_transient( 'snapshot_download_link_notification' );
	delete_site_transient( 'snapshot_download_link_immediate_notification' );
} else {
	update_site_option( 'snapshot_activate_schedule', 1 );
}

delete_site_option( 'snapshot-show-black-friday' );
delete_site_option( 'snapshot_started_seen' );
delete_site_option( 'snapshot_started_seen_persistent' );
delete_site_option( 'snapshot_latest_backup' );
delete_site_option( 'snapshot_running_backup' );
delete_site_option( 'snapshot_running_backup_status' );
delete_site_option( 'snapshot_whats_new_seen' );
delete_site_option( 'snapshot_manual_backup_trigger_time' );
delete_site_option( 'snapshot_tutorials_slider_seen' );

/**
 * Purge the transients.
 */
delete_transient( 'snapshot_current_stats' );
delete_transient( 'snapshot_listed_backups' );
delete_transient( 'snapshot_extra_security_step' );

require_once trailingslashit( __DIR__ ) . 'lib/snapshot/helper/class-log.php';
Log::remove_log_dir();

/**
 * Deletes Snapshot Backup Schedule
 *
 * @return void
 */
function delete_snapshot_schedule(): void {
	if ( file_exists( trailingslashit( __DIR__ ) . 'lib/constants.php' ) && ! defined( 'SNAPSHOT4_SERVICE_API_URL' ) ) {
		require_once trailingslashit( __DIR__ ) . 'lib/constants.php';
	}

	$data    = get_site_option( 'wdp_un_membership_data', array() );
	$site_id = array_key_exists( 'hub_site_id', $data ) ? $data['hub_site_id'] : 0;

	if ( ! $site_id ) {
		// Site ID is a must for us to proceed further.
		return;
	}

	// Grab the API key required for the request authentication.
	$api_key = get_site_option( 'wpmudev_apikey' );
	if ( ! $api_key ) {
		return;
	}

	$api_url = untrailingslashit( SNAPSHOT4_SERVICE_API_URL );
	$api_url = sprintf( '%s/%s/schedules', $api_url, $site_id );

	// We're only doing a one-way communication.
	wp_remote_request(
		$api_url,
		array(
			'method'   => 'DELETE',
			'blocking' => false,
			'headers'  => array(
				'Snapshot-APIKey' => $api_key,
			),
		)
	);
}

delete_snapshot_schedule();