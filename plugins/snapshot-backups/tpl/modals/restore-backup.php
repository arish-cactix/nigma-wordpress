<?php // phpcs:ignore
/**
 * Restore backup modal.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
?>
<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="modal-snapshot-restore-backup"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-snapshot-restore-backup-title"
		aria-describedby="modal-snapshot-restore-backup-description"
	>
		<div class="sui-box">
			<form id="form-snapshot-restore-backup">

				<?php wp_nonce_field( 'snapshot_trigger_backup_restore', '_wpnonce-snapshot_trigger_backup_restore' ); ?>
				<?php wp_nonce_field( 'snapshot_cancel_backup_restore', '_wpnonce-snapshot_cancel_backup_restore' ); ?>

				<div class="sui-box-header sui-flatten sui-content-center">
					<figure class="sui-box-banner" role="banner" aria-hidden="true">
						<img
							src="<?php echo esc_attr( $assets->get_asset( 'img/modal-banner-restore-backup.png' ) ); ?>"
							srcset="<?php echo esc_attr( $assets->get_asset( 'img/modal-banner-restore-backup.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/modal-banner-restore-backup@2x.png' ) ); ?> 2x"
						/>
					</figure>
					<button class="sui-button-icon sui-button-float--right" data-modal-close>
						<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					</button>
					<h3 class="sui-box-title sui-lg" id="modal-snapshot-restore-backup-title"><?php esc_html_e( 'Restore backup', 'snapshot' ); ?></h3>
					<span id="modal-snapshot-restore-backup-description" class="sui-description"><?php esc_html_e( 'Looking to restore a partial or complete backup?', 'snapshot' ); ?></span>
				</div>

				<div class="sui-box-body">
					<input type="hidden" name="backup_id">

					<div class="sui-border-frame snapshot-restore--options__body">
						<div class="sui-form-field snapshot-restore--options__wrap">
							<div class="snapshot-restore--download__options">
								<h4><?php esc_html_e( 'Restore Options', 'snapshot' ); ?></h4>

								<div class="snapshot-radio--options" role="radiogroup">
									<label for="snapshot-restore--full__backup" class="sui-radio sui-radio-stacked">
										<input
											type="radio"
											name="export_options"
											id="snapshot-restore--full__backup"
											aria-labelledby="label-full-backup"
											value="full_backup"
											checked="checked"
										/>
										<span aria-hidden="true"></span>
										<span id="label-full-backup">
											<?php esc_html_e( 'Files and Database', 'snapshot' ); ?>
											<span class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_attr_e( 'Both files and database will be restored.', 'snapshot' ); ?>">
												<span class="sui-icon-info" aria-hidden="true"></span>
											</span>
										</span>
									</label>

									<label for="snapshot-restore--files__only" class="sui-radio sui-radio-stacked">
										<input
											type="radio"
											name="export_options"
											id="snapshot-restore--files__only"
											aria-labelledby="label-files-only"
											value="files"
										/>
										<span aria-hidden="true"></span>
										<span id="label-files-only">
											<?php esc_html_e( 'Files Only (All files in WP Installation Directory)', 'snapshot' ); ?>
											<span class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_attr_e( 'Only the files will be restored without affecting any databases in the system.', 'snapshot' ); ?>">
												<span class="sui-icon-info" aria-hidden="true"></span>
											</span>
										</span>
									</label>

									<label for="snapshot-restore--database__only" class="sui-radio sui-radio-stacked">
										<input
											type="radio"
											name="export_options"
											id="snapshot-restore--database__only"
											aria-labelledby="label-database-only"
											value="database"
										/>
										<span aria-hidden="true"></span>
										<span id="label-database-only">
											<?php esc_html_e( 'Database Only', 'snapshot' ); ?>
											<span class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_attr_e( 'Only the databases will be restored without affecting any files in the system.', 'snapshot' ); ?>">
												<span class="sui-icon-info" aria-hidden="true"></span>
											</span>
										</span>

									</label>
								</div>
							</div>
						</div>

						<div class="sui-form-field snapshot-restore-backup--location__wrap">
							<label for="restore-backup-path" id="restore-backup-path-title" class="sui-label"><?php esc_html_e( 'Default directory', 'snapshot' ); ?></label>
							<input class="sui-form-control" name="restore_rootpath" autocomplete="off" id="restore-backup-path" aria-labelledby="restore-backup-path-title" aria-describedby="restore-backup-path-description" disabled="disabled">
							<span class="sui-icon-folder-open sui-md" aria-hidden="true"></span>
						</div>

					</div>

					<div class="sui-notice sui-notice-info">
						<div class="sui-notice-content" style="padding: 11px 17px;">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-warning-alert sui-md" aria-hidden="true"></span>
								<p><?php esc_html_e( 'It may take a while to restore the files, depending on the size of your site. Please keep the window open while the restoration is in progress.', 'snapshot' ); ?></p>
							</div>
						</div>
					</div>
				</div>

				<div class="sui-box-footer sui-flatten sui-lg sui-content-center">
					<div class="sui-block-content-center">
						<button type="submit" class="sui-button sui-button-blue"><?php esc_html_e( 'Restore', 'snapshot' ); ?></button>
					</div>
				</div>

			</form>
		</div>
	</div>
</div>