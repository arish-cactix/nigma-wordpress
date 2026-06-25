<?php // phpcs:ignore
/**
 * Main destinations page.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Assets;
use WPMUDEV\Snapshot4\Helper\Settings;

$assets = new Assets();
wp_nonce_field( 'save_snapshot_settings', '_wpnonce-save_snapshot_settings' );
wp_nonce_field( 'snapshot_list_backups', '_wpnonce-list-backups' );
wp_nonce_field( 'snapshot_get_storage', '_wpnonce-snapshot_get_storage' );
wp_nonce_field( 'snapshot_get_destinations', '_wpnonce-snapshot-get-destinations' );
wp_nonce_field( 'snapshot_delete_destination', '_wpnonce-snapshot-delete-destination' );
wp_nonce_field( 'snapshot_get_schedule', '_wpnonce-get-schedule' );
?>
<div class="sui-wrap snapshot-page-destinations">
	<?php
	$this->render( 'common/header' );

	$this->render(
		'common/doc-button',
		array(
			'header_title' => __( 'Destinations', 'snapshot' ),
			'utm_tags'     => 'snapshot_destinations_docs#destinations',
		)
	);

	$this->render(
		'common/v3-prompt',
		array(
			'active_v3'          => $active_v3,
			'v3_local'           => $v3_local,
			'assets'             => $assets,
			'is_branding_hidden' => $is_branding_hidden,
		)
	);
	?>

	<div class="sui-box sui-summary snapshot-destinations-summary<?php echo esc_html( $sui_branding_class ); ?>">

		<div class="sui-summary-image-space" aria-hidden="true"
			style="background-image: url( '<?php echo esc_url( apply_filters( 'wpmudev_branding_hero_image', '' ) ); ?>' )">
		</div>

		<div class="sui-summary-segment">

			<div class="sui-summary-details">

				<span class="sui-summary-large" style="visibility: hidden;">1</span>
				<span class="sui-icon-loader sui-loading" aria-hidden="true"
					style="position: relative; left: -25px;"></span>
				<span class="sui-summary-sub"><span
						class="singular"><?php esc_html_e( 'Destination', 'snapshot' ); ?></span><span class="plural"
						style="display: none;"><?php esc_html_e( 'Destinations', 'snapshot' ); ?></span></span>

			</div>

		</div>

		<div class="sui-summary-segment">

			<ul class="sui-list">

				<li>
					<span class="sui-list-label"><?php esc_html_e( 'Last backup destination', 'snapshot' ); ?></span>
					<span class="sui-list-detail"><i class="sui-icon-loader sui-loading snapshot-loading"
							aria-hidden="true"></i><span class="snapshot-last-destination"></span></span>
				</li>

				<li>
					<span class="sui-list-label">
						<?php
						/* translators: %s - plugin custom name */
						echo esc_html( sprintf( __( '%s storage space', 'snapshot' ), $plugin_custom_name ) );
						?>
					</span>
					<!--<span class="sui-list-detail">-->
					<div class="snapshot-current-stats">
						<div class="sui-progress">
							<span class="sui-icon-loader sui-loading snapshot-storage-loading"
								aria-hidden="true"></span>
							<div class="sui-progress-bar wpmudev-snapshot-storage" aria-hidden="true"
								style="display: none;">
								<span style="width: 0%;"></span>
							</div>
						</div>
						<div class="used-space" style="display: none;"></div>
					</div>
					<!--</span>-->
				</li>

			</ul>

		</div>

	</div>

	<div class="sui-box snapshot-destinations">

		<div class="sui-box-header">
			<h3 class="sui-box-title"><?php esc_html_e( 'Destinations', 'snapshot' ); ?></h3>
			<div class="sui-actions-right">

				<button class="sui-button sui-button-blue" id="snapshot-add-destination">
					<span class="sui-icon-plus" aria-hidden="true"></span>
					<?php esc_html_e( 'Add destination', 'snapshot' ); ?>
				</button>

			</div>
		</div>

		<div class="sui-box-body">
			<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
			<p><?php esc_html_e( 'View and manage your available destinations. After each backup, a full backup copy will be sent to all enabled third-party destinations.', 'snapshot' ); ?>
			</p>
			<?php } else { ?>
			<p>
				<?php
				/* translators: %s plugin custom name */
				echo esc_html( sprintf( __( 'View and manage your available destinations. After each backup, the %s API will send a full site backup to all enabled third-party destinations.', 'snapshot' ), $plugin_custom_name ) );
				?>
			</p>
			<?php } ?>
		</div>
		<table class="sui-table sui-table-flushed">
			<thead>
				<tr class="sui-hidden-xs sui-hidden-sm">
					<th><?php esc_html_e( 'Name', 'snapshot' ); ?></th>
					<th><?php esc_html_e( 'Directory', 'snapshot' ); ?></th>
					<th><?php esc_html_e( 'Schedule', 'snapshot' ); ?></th>
					<th><?php esc_html_e( 'Exported Backups', 'snapshot' ); ?></th>
					<th width="60"></th>
				</tr>
				<tr class="sui-hidden-md sui-hidden-lg">
					<th colspan="6" style="height: 0; padding: 0;"></th>
				</tr>
			</thead>
			<tbody>
				<tr class="destination-row">
					<td class="sui-table-item-title sui-hidden-xs sui-hidden-sm row-icon row-icon-wpmudev">
						<div class="tooltip-container">
							<?php
							if ( isset( $plugin_icon_details['icon_url'] ) ) {
								?>
							<div class="tooltip-background"
								style="background-image: url( <?php echo esc_url( $plugin_icon_details['icon_url'] ); ?>);">
							</div>
								<?php
							} else {
								?>
							<div class="tooltip-background <?php echo 'sui-icon-wpmudev-logo' === $plugin_icon_details['icon_class'] ? '' : 'custom-icon ' . esc_attr( $plugin_icon_details['icon_class'] ); ?>"
								aria-hidden="true">
							</div>
								<?php
							}
							?>
							<div class="tooltip-block"></div><?php echo esc_html( $plugin_custom_name ); ?>
						</div>
					</td>

					<td class="sui-hidden-xs sui-hidden-sm"></td>
					<td class="sui-hidden-xs sui-hidden-sm"><span
							class="sui-icon-loader sui-loading snapshot-loading-schedule"
							aria-hidden="true"></span><span class="destination-schedule-text"></span></td>
					<td class="sui-hidden-xs sui-hidden-sm"><span class="sui-icon-loader sui-loading snapshot-loading"
							aria-hidden="true"></span><span class="wpmudev-backup-count"></span></td>

					<td colspan="5" class="sui-table-item-title first-child sui-hidden-md sui-hidden-lg mobile-row">
						<div class="destination-name"><span class="sui-icon-wpmudev-logo"
								aria-hidden="true"></span><?php echo esc_html( $plugin_custom_name ); ?></div>
						<div class="sui-row destination-cells">
							<div class="sui-col-xs-6">
								<div class="sui-table-item-title"><?php esc_html_e( 'Directory', 'snapshot' ); ?></div>
								<div class="sui-table-item-title destination-path">
									<!--span class="sui-icon-folder sui-md" aria-hidden="true"></span><span></span-->
								</div>
							</div>

							<div class="sui-col-xs-6">
								<div class="sui-table-item-title"><?php esc_html_e( 'Schedule', 'snapshot' ); ?></div>
								<div class="sui-table-item-title"><span
										class="sui-icon-loader sui-loading snapshot-loading-schedule"
										aria-hidden="true"></span><span class="destination-schedule-text"></span></div>
							</div>

							<div class="sui-col-xs-6">
								<div class="sui-table-item-title"><?php esc_html_e( 'Exported Backups', 'snapshot' ); ?>
								</div>
								<div class="sui-table-item-title backup-count"><span
										class="sui-icon-loader sui-loading snapshot-loading"
										aria-hidden="true"></span><span class="wpmudev-backup-count"></span></div>
							</div>
						</div>
					</td>

					<td></td>
				</tr>
			</tbody>
		</table>


		<div class="sui-box-footer">
			<div class="snapshot-loader">
				<p><span class="sui-icon-loader sui-loading" aria-hidden="true"></span><span
						class="loader-text"><?php esc_html_e( 'Loading destinations...', 'snapshot' ); ?></span></p>
			</div>

			<div class="api-error" style="display: none;">
				<div class="sui-notice sui-notice-error">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-warning-alert sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'We were unable to list the destinations due to a connection problem. Give it another try below, or contact support if the problem persists.', 'snapshot' ); ?>
							</p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'We were unable to list the destinations due to a connection problem. Give it another try below, or <a href="%s" target="_blank">contact our support team</a> if the problem persists.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?>
							</p>
							<?php } ?>
						</div>
					</div>
				</div>
				<button class="sui-button sui-button-ghost" role="button" id="button-reload-destinations"><span
						class="sui-icon-refresh"
						aria-hidden="true"></span><?php esc_html_e( 'Reload', 'snapshot' ); ?></button>
			</div>
		</div>

	</div>

	<?php

	// Snapshot getting started dialog.
	$this->render(
		'modals/welcome-activation',
		array(
			'errors'             => $errors,
			'welcome_modal'      => $welcome_modal,
			'welcome_modal_alt'  => $welcome_modal_alt,
			'is_branding_hidden' => $is_branding_hidden,
		)
	);
	$this->render( 'modals/confirm-wpmudev-password' );

	$this->render( 'modals/confirm-v3-uninstall' );
	$this->render(
		'modals/add-destination',
		array(
			'auth_url'          => $auth_url,
			'dropbox_auth_url'  => $dropbox_auth_url,
			'onedrive_auth_url' => $onedrive_auth_url,
		)
	);
	$this->render( 'modals/destinations-delete' );
	$this->render( 'modals/destination-s3-edit' );
	$this->render( 'modals/destination-gdrive-edit' );
	$this->render( 'modals/destinations/dropbox/edit' );
	$this->render( 'modals/destinations/ftp/edit' );
	$this->render( 'modals/destinations/onedrive/edit' );
	$this->render( 'modals/destinations/azure/edit' );
	$this->render( 'common/footer' );
	?>

</div> <?php
// .sui-wrap ?>