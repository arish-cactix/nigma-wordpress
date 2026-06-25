<?php
/**
 * Notice tempalte file to re-authenticate google drive
 *
 * @package Snapshot_Backups
 * @since 4.24.0
 */

$exports_failed = get_site_option( 'snapshot_failed_third_party_destination_exports', array() );

if ( empty( $exports_failed['google_drive'] ) ) {
	return;
}

$notice_status = get_site_option( 'snapshot_reconnect_google_drive_notice_status' );

if ( $notice_status ) {
	return;
}
$assets            = new \WPMUDEV\Snapshot4\Helper\Assets();
$drive_id          = $exports_failed['google_drive'][0];
$destinations_page = network_admin_url() . 'admin.php?page=snapshot-destinations';
?>

<div class="sui-box snapshot-connect-google-drive--notice" style="border-left: 3px solid #17A8E3;">
	<div class="sui-box-image-space" aria-hidden="true">
		<img
		src="<?php echo esc_url( $assets->get_asset( 'img/google-drive-icon.png' ) ); ?>"
		srcset="<?php echo esc_url( $assets->get_asset( 'img/google-drive-icon.png' ) ); ?> 1x, <?php echo esc_url( $assets->get_asset( 'img/google-drive-icon@2x.png' ) ); ?> 2x"
		alt="<?php esc_attr_e( 'Google drive icon', 'snapshot' ); ?>"
		>
	</div>

	<div class="sui-summary-segment">
		<div class="sui-summary-details">
			<h3 class="no-spacing"><?php esc_html_e( 'Reconnect Google Drive', 'snapshot' ); ?></h3>
			<div class="snapshot-block-text"><?php esc_html_e( 'It appears that Google Drive is not syncing with Snapshot. Please reauthenticate your Google Drive to ensure it works properly.', 'snapshot' ); ?></div>
			<a href="<?php echo esc_url( $destinations_page ); ?>" class="sui-button sui-button-blue"><span class="sui-icon-link" aria-hidden="true"></span> <?php esc_html_e( 'Reconnect Now', 'snapshot' ); ?></a>
			<a class="sui-button sui-button-light-gray do-not--nag__me--gdrive" data-nonce="<?php echo wp_create_nonce( 'snapshot_gdrive_reconnect_nag' ); ?>"><?php esc_html_e( 'Don\'t remind me again', 'snapshot' ); ?></a>
		</div>
	</div>
</div>