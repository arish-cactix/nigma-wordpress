<?php
/**
 * Snapshot constants definition file. Internal constants, not to be overridden.
 *
 * @package Snapshot
 * @since   4.4.0
 */

if ( ! defined( 'SNAPSHOT4_BACKUP_TIMEOUT' ) ) {
	define( 'SNAPSHOT4_BACKUP_TIMEOUT', 30 * 60 );
}

if ( ! defined( 'SNAPSHOT_IS_TEST_ENV' ) ) {
	define( 'SNAPSHOT_IS_TEST_ENV', false );
}

if ( ! defined( 'SNAPSHOT_SUI' ) ) {
	define( 'SNAPSHOT_SUI', '2-12-23' );
}

if ( file_exists( __DIR__ . DIRECTORY_SEPARATOR . 'constants-dev.php' ) ) {
	require __DIR__ . DIRECTORY_SEPARATOR . 'constants-dev.php';
}

if ( ! defined( 'SNAPSHOT4_SERVICE_API_URL' ) ) {
	define( 'SNAPSHOT4_SERVICE_API_URL', 'https://bbna4i2zbe.execute-api.us-east-1.amazonaws.com/prod/' );
}

if ( ! defined( 'SNAPSHOT_DROPBOX_APP_ID' ) ) {
	define( 'SNAPSHOT_DROPBOX_APP_ID', 'uh4tk1uxdft1ik3' );
}

if ( ! defined( 'SNAPSHOT_DROPBOX_APP_FOLDER_NAME' ) ) {
	define( 'SNAPSHOT_DROPBOX_APP_FOLDER_NAME', 'Snapshot-Backups' );
}

if ( ! defined( 'SNAPSHOT_DROPBOX_REDIRECT_URI' ) ) {
	define( 'SNAPSHOT_DROPBOX_REDIRECT_URI', 'https://wpmudev.com/api/snapshot/v2/dropbox-handler' );
}

if ( ! defined( 'SNAPSHOT_DROPBOX_VIEW_BASE_URL' ) ) {
	define( 'SNAPSHOT_DROPBOX_VIEW_BASE_URL', 'https://www.dropbox.com/home' );
}

if ( ! defined( 'SNAPSHOT_DROPBOX_VIEW_URL' ) ) {
	define( 'SNAPSHOT_DROPBOX_VIEW_URL', SNAPSHOT_DROPBOX_VIEW_BASE_URL );
}

/**
 * Google Drive Redirect URI.
 */
if ( ! defined( 'SNAPSHOT_GDRIVE_REDIRECT_URI' ) ) {
	define( 'SNAPSHOT_GDRIVE_REDIRECT_URI', 'https://wpmudev.com/api/snapshot/v2/gdrive-handler' );
}

if ( ! defined( 'SNAPSHOT_ONEDRIVE_APP_ID' ) ) {
	define( 'SNAPSHOT_ONEDRIVE_APP_ID', 'e91deeaf-cbee-446f-bec9-7c27688f0d6b' );
}

if ( ! defined( 'SNAPSHOT_ONEDRIVE_APP_SCOPE' ) ) {
	define( 'SNAPSHOT_ONEDRIVE_APP_SCOPE', 'openid profile User.Read offline_access files.readwrite.all' );
}

if ( ! defined( 'SNAPSHOT_ONEDRIVE_REDIRECT_URI' ) ) {
	define( 'SNAPSHOT_ONEDRIVE_REDIRECT_URI', 'https://wpmudev.com/api/snapshot/v2/onedrive-handler' );
}

if ( ! defined( 'SNAPSHOT_ONEDRIVE_AUTHORIZE_URL' ) ) {
	define( 'SNAPSHOT_ONEDRIVE_AUTHORIZE_URL', 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize' );
}

if ( ! defined( 'SNAPSHOT_ONEDRIVE_PROMPT' ) ) {
	define( 'SNAPSHOT_ONEDRIVE_PROMPT', 'consent' );
}

if ( ! defined( 'SNAPSHOT_TROUBLESHOOT_MODE' ) ) {
	define( 'SNAPSHOT_TROUBLESHOOT_MODE', false );
}

if ( ! defined( 'SNAPSHOT_BYPASS_IP_ALLOWLIST_CHECK' ) ) {
	define( 'SNAPSHOT_BYPASS_IP_ALLOWLIST_CHECK', false );
}

if ( ! defined( 'SNAPSHOT_WPMUDEV_DOCS' ) ) {
	define( 'SNAPSHOT_WPMUDEV_DOCS', 'https://wpmudev.com/docs/wpmu-dev-plugins/snapshot-4-0/' );
}