<?php // phpcs:ignore
/**
 * Dashboard page.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Model\Env;
use WPMUDEV\Snapshot4\Helper\Assets;
use WPMUDEV\Snapshot4\Helper\Settings;

$assets = new Assets();
wp_nonce_field( 'snapshot_list_backups', '_wpnonce-list-backups' );
wp_nonce_field( 'save_snapshot_settings', '_wpnonce-save_snapshot_settings' );
wp_nonce_field( 'snapshot_get_storage', '_wpnonce-snapshot_get_storage' );
wp_nonce_field( 'snapshot_list_hosting_backups', '_wpnonce-list-hosting-backups' );
wp_nonce_field( 'snapshot_get_destinations', '_wpnonce-snapshot-get-destinations' );
wp_nonce_field( 'snapshot_update_destination', '_wpnonce-snapshot-update-destination' );
wp_nonce_field( 'snapshot_tutorials_slider_seen', '_wpnonce-tutorials_slider_seen' );

$has_hosting_backups = Env::is_wpmu_hosting();
?>
<div class="sui-wrap snapshot-page-dashboard">
	<?php
	$this->render( 'common/header' );

	$this->render(
		'common/doc-button',
		array(
			'header_title' => __( 'Dashboard', 'snapshot' ),
			'utm_tags'     => 'snapshot_dash_docs#dashboard',
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

	$this->render( 'notices/reauthenticate-google-drive' );
	?>
	<div class="sui-box sui-summary snapshot-dashboard-summary<?php echo esc_html( $sui_branding_class ); ?>">

		<div class="sui-summary-image-space" aria-hidden="true"
			style="background-image: url( '<?php echo esc_url( apply_filters( 'wpmudev_branding_hero_image', '' ) ); ?>' )">
		</div>

		<div class="sui-summary-segment">

			<div class="sui-summary-details snapshot-backups-number">

				<span class="sui-summary-large"></span>
				<span class="sui-icon-loader sui-loading snapshot-loading" aria-hidden="true"></span>
				<span class="sui-summary-sub"><?php esc_html_e( 'Backups available', 'snapshot' ); ?></span>

			</div>

		</div>

		<div class="sui-summary-segment">

			<ul class="sui-list">

				<li>
					<span class="sui-list-label"><?php esc_html_e( 'Active destinations', 'snapshot' ); ?></span>
					<span class="sui-list-detail"><span
							class="sui-icon-loader sui-loading snapshot-destinations-number-loading"
							aria-hidden="true"></span><span class="snapshot-destinations-number"></span></span>
				</li>

				<li>
					<span class="sui-list-label"><?php esc_html_e( 'Last backup', 'snapshot' ); ?></span>
					<span class="sui-list-detail"><span class="sui-icon-loader sui-loading snapshot-loading"
							aria-hidden="true"></span><span class="snapshot-last-backup"></span></span>
				</li>

				<li>
					<span class="sui-list-label">
						<?php
						/* translators: %s - plugin name */
						echo esc_html( sprintf( __( '%s storage space', 'snapshot' ), $plugin_custom_name ) );
						?>
					</span>
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
				</li>

			</ul>

		</div>

	</div>

	<?php if ( ! Settings::get_snapshot_tutorials_seen() && ! Settings::get_branding_hide_doc_link() ) { ?>
	<div class="sui-row">
		<div class="sui-col">
			<div id="snapshot-tutorials-slider">
			</div>
		</div>
	</div>
	<?php } ?>

	<div class="sui-row">
		<div class="sui-col-lg-6">
			<div class="sui-box snapshot-dashboard-backups">

				<div class="sui-box-header">
					<h3 class="sui-box-title">
						<span class="sui-icon-snapshot" aria-hidden="true"></span>
						<?php echo esc_html( ( Settings::get_brand_name() === 'WPMU DEV' || 'WPMU DEV' !== __( 'WPMU DEV', 'snapshot' ) ) ? __( 'Snapshot Backups', 'snapshot' ) : __( 'Backups', 'snapshot' ) ); ?>
					</h3>
				</div>

				<div class="sui-box-body api-error">
					<div class="sui-notice sui-notice-error">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-warning-alert sui-md" aria-hidden="true"></span>
								<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
								<p><?php esc_html_e( 'We were unable to fetch backup data from the API due to a connection problem. Give it another try below, or contact support if the problem persists.', 'snapshot' ); ?>
								</p>
								<?php } else { ?>
									<?php /* translators: %s - Link for support */ ?>
								<p><?php echo wp_kses_post( sprintf( __( 'We were unable to fetch backup data from the API due to a connection problem. Give it another try below, or <a href="%s" target="_blank">contact our support team</a> if the problem persists.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
								</p>
								<?php } ?>
							</div>
						</div>
					</div>
					<button class="sui-button sui-button-ghost" role="button" id="button-reload-backups"><span
							class="sui-icon-refresh"
							aria-hidden="true"></span><?php esc_html_e( 'Reload', 'snapshot' ); ?></button>
				</div>

				<div class="sui-message snapshot-backup-list-loader snapshot-loading">
					<div class="sui-message-content">
						<p><span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							<?php esc_html_e( 'Loading backups...', 'snapshot' ); ?></p>
					</div>
				</div>

				<div class="sui-box-body">
					<p>
						<?php esc_html_e( 'Here are your latest backups. Check backups page for the full list of available backups.', 'snapshot' ); ?>
					</p>
				</div>
				<div class="snapshot-listed-backups">
					<table class="sui-table sui-table-flushed">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Title', 'snapshot' ); ?></th>
								<th><?php esc_html_e( 'Destination', 'snapshot' ); ?></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="sui-box-footer">
					<a href="<?php echo esc_attr( network_admin_url() . 'admin.php?page=snapshot-backups' ); ?>"
						class="sui-button sui-button-ghost"><span class="sui-icon-eye"
							aria-hidden="true"></span><?php esc_html_e( 'View all', 'snapshot' ); ?></a>
					<a <?php echo $disable_backup_button ? 'disabled' : ''; ?>
						href="?page=snapshot-backups#create-backup"
						class="sui-button sui-button-blue snapshot-not-cooldown"><?php esc_html_e( 'Backup now', 'snapshot' ); ?></a>
					<div class="sui-tooltip sui-tooltip-constrained sui-tooltip-top-left-mobile snapshot-cooldown"
						style="--tooltip-width: 174px; display: none;"
						data-tooltip="<?php Settings::get_branding_hide_doc_link() ? esc_html_e( 'The backup plugin is just catching his breath. You can run another backup in a minute.', 'snapshot' ) : esc_html_e( 'Snapshot is just catching his breath. You can run another backup in a minute.', 'snapshot' ); ?>">
						<button class="sui-button sui-button-blue" disabled>
							<?php esc_html_e( 'Backup now', 'snapshot' ); ?>
						</button>
					</div>
				</div>

				<div class="sui-box sui-message snapshot-listed-backups-empty">

					<img src="<?php echo esc_attr( $assets->get_asset( 'img/no-backups-icon.svg' ) ); ?>"
						class="sui-image snapshot-no-backups-hero"
						aria-hidden="true" />

					<div class="sui-message-content">
						<p><?php esc_html_e( 'Create full backups and send them to your connected destinations. Let\'s go!', 'snapshot' ); ?>
						</p>
						<p>
							<a href="?page=snapshot-backups#create-backup" class="sui-button sui-button-blue">
								<?php
								esc_html_e( 'Backup now', 'snapshot' );
								?>
							</a>
						</p>
					</div>

				</div>
			</div>
			<?php if ( ! $has_hosting_backups ) { ?>

			<div class="sui-box snapshot-dashboard-configs">
				<div id="snapshot-dashboard-configs"></div>
			</div>
		</div>
		<div class="sui-col-lg-6">
			<?php } ?>
			<div class="sui-box snapshot-dashboard-destinations">

				<div class="sui-box-header">
					<h3 class="sui-box-title">
						<span class="sui-icon-cloud" aria-hidden="true"></span>
						<?php esc_html_e( 'Destinations', 'snapshot' ); ?>
					</h3>
				</div>

				<div class="sui-box-body">
					<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
					<p><?php esc_html_e( 'View and manage your available destinations. After each backup, a full backup copy will be sent to all enabled third-party destinations.', 'snapshot' ); ?>
					</p>
					<?php } else { ?>
					<p>
						<?php
						/* translators: %s - plugin name */
						echo esc_html( sprintf( __( 'View and manage your available destinations. After each backup, the %s API will send a full site backup to all enabled third-party destinations.', 'snapshot' ), $plugin_custom_name ) );
						?>
					</p>
					<?php } ?>
				</div>

				<div>
					<table class="sui-table sui-table-flushed">
						<thead>
							<tr class="sui-hidden-xs sui-hidden-sm">
								<th><?php esc_html_e( 'Destination', 'snapshot' ); ?></th>
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
										<div class="tooltip-block"></div>
										<?php
											echo esc_html( $plugin_custom_name );
										?>
									</div>
								</td>

								<td colspan="5"
									class="sui-table-item-title first-child sui-hidden-md sui-hidden-lg mobile-row">
									<div class="destination-name"><span class="sui-icon-wpmudev-logo"
											aria-hidden="true"></span><?php echo esc_html( $plugin_custom_name ); ?>
									</div>
								</td>

								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="snapshot-loader">
					<p><span class="sui-icon-loader sui-loading" aria-hidden="true"></span><span
							class="loader-text"><?php esc_html_e( 'Loading destinations...', 'snapshot' ); ?></span></p>
				</div>

				<div class="sui-box-footer sui-space-between">
					<a href="<?php echo esc_attr( network_admin_url() . 'admin.php?page=snapshot-destinations' ); ?>"
						class="sui-button sui-button-ghost">
						<span class="sui-icon-eye" aria-hidden="true"></span>
						<?php esc_html_e( 'View all', 'snapshot' ); ?>
					</a>

					<a href="<?php echo esc_attr( network_admin_url() . 'admin.php?page=snapshot-destinations#add-destination' ); ?>"
						class="sui-button sui-button-blue">
						<span class="sui-icon-plus" aria-hidden="true"></span>
						<?php esc_html_e( 'Add Destination', 'snapshot' ); ?>
					</a>
				</div>

			</div>
			<?php if ( $has_hosting_backups ) { ?>
		</div>
		<div class="sui-col-lg-6">
			<div class="sui-box snapshot-dashboard-hosting-backups">
				<div class="sui-box-header">
					<h3 class="sui-box-title">
						<span class="sui-icon-cloud" aria-hidden="true"></span>
						<?php esc_html_e( 'Hosting Backups', 'snapshot' ); ?>
					</h3>
				</div>
				<div class="sui-box-body">
					<p class="body-description" style="display: none; margin-bottom: 5px;">
						<?php esc_html_e( 'Here are your latest hosting backups. Check the Hosting Backups page for the full list of available backups.', 'snapshot' ); ?>
					</p>

					<div class="api-error" style="display: none;">
						<div class="sui-notice sui-notice-error" style="margin-bottom: 10px;">
							<div class="sui-notice-content">
								<div class="sui-notice-message">
									<span class="sui-notice-icon sui-icon-warning-alert sui-md"
										aria-hidden="true"></span>
									<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
									<p><?php esc_html_e( 'We were unable to fetch backup data from the API due to a connection problem. Give it another try below, or contact support if the problem persists.', 'snapshot' ); ?>
									</p>
									<?php } else { ?>
										<?php /* translators: %s - Link for support */ ?>
									<p><?php echo wp_kses_post( sprintf( __( 'We were unable to fetch backup data from the API due to a connection problem. Give it another try below, or <a href="%s" target="_blank">contact our support team</a> if the problem persists.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
									</p>
									<?php } ?>
								</div>
							</div>
						</div>
						<button class="sui-button sui-button-ghost" role="button"
							id="button-reload-hosting-backups"><span class="sui-icon-refresh"
								aria-hidden="true"></span><?php esc_html_e( 'Reload', 'snapshot' ); ?></button>
					</div>

					<div class="sui-message snapshot-hosting-backup-list-loader snapshot-loading">
						<div class="sui-message-content">
							<p><span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
								<?php esc_html_e( 'Loading backups...', 'snapshot' ); ?></p>
						</div>
					</div>
				</div>

				<table class="sui-table sui-table-flushed snapshot-listed-hosting-backups" style="display: none;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Title', 'snapshot' ); ?></th>
							<th><?php esc_html_e( 'Destination', 'snapshot' ); ?></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>

				<div class="sui-box-footer" style="display: none;">
					<a class="sui-button sui-button-ghost"
						href="<?php echo esc_attr( network_admin_url() . 'admin.php?page=snapshot-hosting-backups' ); ?>">
						<span class="sui-icon-eye" aria-hidden="true"></span>
						<?php esc_html_e( 'View all', 'snapshot' ); ?>
					</a>
				</div>
			</div>

			<div class="sui-box snapshot-dashboard-configs">
				<div id="snapshot-dashboard-configs"></div>
			</div>

				<?php
			}//end if
			?>


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
	$this->render( 'common/footer' );

	if ( ! Settings::get_whats_new_seen() &&
		! Settings::is_branding_docs_hidden()
	) {
		$this->render( 'modals/whats-new' );
	}
	?>

</div> <?php
// .sui-wrap ?>