<?php // phpcs:ignore
/**
 * Modal for preflight check progress.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
?>

<div class="sui-modal sui-modal-lg">
	<div
		role="dialog"
		id="snapshot-modal-preflight-check"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-snapshot-preflight-check-title"
		aria-describedby="modal-snapshot-preflight-check-description"
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
				<button class="sui-button-icon sui-button-float--right snapshot-pf-confirm-cancel" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>
				<h3 class="sui-box-title sui-lg"><?php esc_html_e( 'Pre-Backup Inspection', 'snapshot' ); ?></h3>
				<span id="modal-snapshot-preflight-description" class="sui-description"><?php esc_html_e( 'We will thoroughly scan your website for potential issues that may impact the backup creation process.', 'snapshot' ); ?></span>
			</div>

			<div class="sui-box-body">
				<div class="sui-block-content-center">
						<div class="progressing">
							<div class="sui-progress-block">
								<div class="sui-progress">

									<span class="sui-progress-icon" aria-hidden="true">
										<span class="sui-icon-loader sui-loading"></span>
									</span>

									<span id="preflight-done" class="sui-progress-text" aria-live="polite"><?php echo '0%'; ?></span>

									<div class="sui-progress-bar" aria-hidden="true">
										<span id="preflight-progress-bar" style="width: 1%"></span>
									</div>

								</div>
							</div>
							<div class="sui-progress-state">
								<span id="snapshot-pf-active-process" aria-live="polite"> <?php esc_html_e( 'Checking System...', 'snapshot' ); ?></span>
							</div>
						</div>
						<div class="preflight-stages">
							<div id="snapshot-pf-system" data-checking="<?php esc_attr_e( 'Checking System', 'snapshot' ); ?>">
								<span>
									<?php esc_html_e( 'System', 'snapshot' ); ?>
								</span>
								<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							</div>
							<div id="snapshot-pf-files" data-checking="<?php esc_attr_e( 'Checking Files', 'snapshot' ); ?>">
								<span>
									<?php esc_html_e( 'Files', 'snapshot' ); ?>
								</span>
								<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							</div>
							<div id="snapshot-pf-database" data-checking="<?php esc_attr_e( 'Checking Database', 'snapshot' ); ?>">
								<span>
									<?php esc_html_e( 'Database', 'snapshot' ); ?>
								</span>
								<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							</div>
						</div>

						<?php wp_nonce_field( 'snapshot_preflight_check', '_wpnonce-snapshot_preflight_check' ); ?>
							<button class="sui-button sui-button-ghost sui-button-blue snapshot-pf-skip-to-bakup">
								<span class="sui-button-text-default">
									<?php esc_html_e( 'SKIP TO BACKUP', 'snapshot' ); ?>
								</span>
							</button>
				</div>
			</div>

		</div>
	</div>
</div>