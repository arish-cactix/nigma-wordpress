<?php // phpcs:ignore
/**
 * Restore backup modal.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
$sites  = get_sites();
?>
<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="modal-snapshot-restore-backup"
		class="sui-modal-content snapshot-restore--network"
		aria-modal="true"
		aria-labelledby="modal-snapshot-restore-backup-title"
		aria-describedby="modal-snapshot-restore-backup-description"
	>
		<div class="sui-box">
			<form id="form-snapshot-restore-backup">

				<?php wp_nonce_field( 'snapshot_trigger_backup_restore', '_wpnonce-snapshot_trigger_backup_restore' ); ?>
				<?php wp_nonce_field( 'snapshot_cancel_backup_restore', '_wpnonce-snapshot_cancel_backup_restore' ); ?>
				<input type="hidden" name="export_options" value="full_backup" />

				<div class="sui-box-header sui-flatten sui-content-center">
					<figure class="sui-box-banner" role="banner" aria-hidden="true">
						<img
							src="<?php echo esc_attr( $assets->get_asset( 'img/modal-banner-restore-backup-network.png' ) ); ?>"
							srcset="<?php echo esc_attr( $assets->get_asset( 'img/modal-banner-restore-backup-network.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/modal-banner-restore-backup-network@2x.png' ) ); ?> 2x"
						/>
					</figure>
					<button class="sui-button-icon sui-button-float--right" data-modal-close>
						<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					</button>
					<h3 class="sui-box-title sui-lg" id="modal-snapshot-restore-backup-title"><?php esc_html_e( 'Restore backup', 'snapshot' ); ?></h3>
				</div>

				<div class="sui-box-body">
					<input type="hidden" name="backup_id">

					<div class="snapshot-restore--options__body">
						<div class="sui-form-field snapshot-restore--options__wrap">
							<div class="snapshot-restore--download__options">
								<h4><?php esc_html_e( 'What would you like to restore?', 'snapshot' ); ?></h4>

								<div class="snapshot-radio--options" role="radiogroup">
									<label for="snapshot-restore--full__network" class="sui-radio">
										<input
											type="radio"
											name="restore_what"
											id="snapshot-restore--full__network"
											aria-labelledby="label-full-network"
											value="network"
											checked="checked"
										/>
										<span aria-hidden="true"></span>
										<span id="label-full-network">
											<?php esc_html_e( 'Network Site', 'snapshot' ); ?>
										</span>
									</label>

									<label for="snapshot-restore--subsite" class="sui-radio">
										<input
											type="radio"
											name="restore_what"
											id="snapshot-restore--subsite"
											aria-labelledby="label-subsite"
											value="subsite"
										/>
										<span aria-hidden="true"></span>
										<span id="label-subsite">
											<?php esc_html_e( 'Subsite', 'snapshot' ); ?>
										</span>
									</label>
								</div>
							</div>
						</div>

						<div class="sui-form-field snapshot-backup--restore--subsite__list sui-hidden" style="position: relative">
							<label for="snapshot-select--subsite" class="sui-label">
									<?php esc_html_e( 'Select subsite', 'snapshot' ); ?>
							</label>

							<select id="snapshot-select--subsite" class="sui-select" name="subsite_id">
							<?php foreach ( $sites as $site ) : ?>
								<?php
								if ( '1' === $site->blog_id ) {
									continue;
								}
								?>
								<option value="<?php echo esc_attr( $site->blog_id ); ?>">
									<?php echo esc_html( untrailingslashit( $site->domain . $site->path ) ); ?>
								</option>
							<?php endforeach; ?>
							</select>
						</div>

						<div class="sui-form-field snapshot-restore-backup--location__wrap">
							<label for="restore-backup-path" id="restore-backup-path-title" class="sui-label"><?php esc_html_e( 'Default directory', 'snapshot' ); ?></label>
							<input class="sui-form-control" name="restore_rootpath" autocomplete="off" id="restore-backup-path" aria-labelledby="restore-backup-path-title" aria-describedby="restore-backup-path-description" disabled="disabled">
							<span class="sui-icon-folder-open sui-md" aria-hidden="true"></span>
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