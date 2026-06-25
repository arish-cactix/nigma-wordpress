<?php // phpcs:ignore
/**
 * Modal for creating a manual backup.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
?>

<div class="sui-modal sui-modal-lg">
	<div
		role="dialog"
		id="modal-snapshot-create-manual-backup"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-snapshot-create-manual-backup-title"
		aria-describedby="modal-snapshot-create-manual-backup-description"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center header-logo-center">
				<div class="sui-box-banner banner-logo-center" role="banner" aria-hidden="true">
					<?php
					if ( isset( $plugin_icon_details['icon_url'] ) ) {
						?>
						<img class="icon-header-img" src="<?php echo esc_url( $plugin_icon_details['icon_url'] ); ?>" />
									<?php
					} elseif ( 'sui-icon-wpmudev-logo' === $plugin_icon_details['icon_class'] ) {
						?>
						<img
						src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-snapshot.png' ) ); ?>"
						/>
						<?php
					} elseif ( 'sui-no-icon' !== $plugin_icon_details['icon_class'] ) {
						?>
						<span
							class="custom-logo-icon <?php echo 'custom-icon-lg ' . esc_attr( $plugin_icon_details['icon_class'] ); ?>"
							aria-hidden="true">
						</span>
						<?php
					}
					?>
				</div>
				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>
				<h3 class="sui-box-title sui-lg"><?php esc_html_e( 'Create Backup', 'snapshot' ); ?></h3>
				<span class="sui-description"><?php esc_html_e( 'Add a title to your backup to distinguish it from other backups.', 'snapshot' ); ?></span>
			</div>

			<div class="sui-box-body">
				<form method="post" id="form-snapshot-create-manual-backup">

					<?php wp_nonce_field( 'snapshot_backup_create_manual', '_wpnonce-snapshot_backup_create_manual' ); ?>
					<div class="sui-form-field">
						<label class="sui-label" for="manual-backup-name">
							<?php esc_html_e( 'Backup title', 'snapshot' ); ?> (<?php esc_html_e( 'Optional', 'snapshot' ); ?>)
						</label>
						<input autocomplete="off" type="text" name="backup_name" class="sui-form-control" id="manual-backup-name" placeholder="<?php esc_html_e( 'E.g. Snapshot', 'snapshot' ); ?>">
					</div>

					<div class="sui-form-field" style="display: none;">
						<label for="manual-backup-comment" class="sui-label">
							<?php esc_html_e( 'Backup Comment', 'snapshot' ); ?> (<?php esc_html_e( 'Optional', 'snapshot' ); ?>)
						</label>
						<textarea name="backup_description" id="manual-backup-comment" rows="3" class="sui-form-control" placeholder="<?php esc_attr_e( 'E.g. Backup before changing site design', 'snapshot' ); ?>"></textarea>
					</div>

					<div class="sui-block-content-center">
						<div class="centre-checkboxes">
							<label for="snapshot-manual-pre-check" class="sui-checkbox sui-checkbox-stacked centre-checkboxes pre-check">
								<input type="checkbox" id="snapshot-manual-pre-check" name="run_pre_check">
								<span aria-hidden="true"></span>
								<span>
									<small>
										<?php
											esc_html_e( 'Run Pre-Backup Inspection', 'snapshot' );
										?>
									</small>
								</span>
							</label>
							<label for="snapshot-manual-apply-exclusions" class="sui-checkbox sui-checkbox-stacked centre-checkboxes apply-exclusions">
								<input type="checkbox" id="snapshot-manual-apply-exclusions" name="apply_exclusions" checked>
								<span aria-hidden="true"></span>
								<span>
									<small>
										<?php
											printf(
												/* translators: %s - Settings page anchor */
												esc_html__(
													'Apply global file exclusions set in %s page.',
													'snapshot'
												),
												'<a id="snapshot-button-backups-settings" href="#">' . esc_html__( 'Settings', 'snapshot' ) . '</a>'
											);
											?>
											<span style="margin-left: 5px;" class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_attr_e( 'You can exclude the files you don\'t want in the backup by adding them as global exclusions in the Settings page.', 'snapshot' ); ?>"><span class="sui-notice-icon sui-icon-info sui-sm" aria-hidden="true"></span></span>
									</small>
								</span>
							</label>
						</div>

					<div id="snapshot-notice-tpd-backup" class="sui-notice" >
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

								<p>
									<?php
										echo wp_kses_post(
											sprintf(
											/* translators: %s - Link for Destination page */
												__(
													'Note: The full backup copy will be exported to all the destinations that are connected and active on the <a href="%s">Destinations page</a>.',
													'snapshot'
												),
												network_admin_url() . 'admin.php?page=snapshot-destinations'
											)
										);
										?>
								</p>

							</div>
						</div>
					</div>

						<button type="submit" class="sui-button sui-button-blue">
							<span class="sui-button-text-default">
								<?php esc_html_e( 'Run backup', 'snapshot' ); ?>
							</span>
							<span class="sui-button-text-onload">
								<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
								<?php esc_html_e( 'Starting backup', 'snapshot' ); ?>
							</span>
						</button>
					</div>

				</form>
			</div>
		</div>
	</div>
</div>