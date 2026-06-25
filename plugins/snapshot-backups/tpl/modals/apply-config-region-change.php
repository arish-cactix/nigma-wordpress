<?php // phpcs:ignore
/**
 * Storage region change dialog.
 *
 * @package snapshot
 */
?>

<div class="sui-modal sui-modal-sm">
	<div role="dialog" id="snapshot-configs-apply-region-change-modal" class="sui-modal-content"
		aria-labelledby="sui-config-apply-title">
		<div class="sui-box region-loaded snapshot-region-mismatch">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog', 'snapshot' ); ?></span>
				</button>
				<h2 id="sui-config-edit-title">
					<?php esc_html_e( 'Apply Config', 'snapshot' ); ?>
				</h2>
				<div class="region-match">
					<?php
					/* translators: %s - Config name */
					printf( esc_html__( 'Are you sure you want to apply the %s to this site?', 'snapshot' ), '{{config_name}}' );
					?>
				</div>
			</div>

			<div class="sui-box-body region-mismatch">
				<div id="sui-apply-modal-warning" class="sui-notice sui-active sui-notice-warning">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'The storage region in the selected config doesn\'t match the storage region in your current settings. All existing backups will be deleted after applying this config.', 'snapshot' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

			<div class="sui-box-footer sui-content-center sui-flatten sui-spacing-top--0 sui-spacing-bottom--60">
				<button class="sui-button sui-button-ghost"
					data-modal-close><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>

				<button class="sui-button snapshot-apply-config__confirm-region-change sui-button-blue">
					<span class="sui-loading-text">
						<span class="sui-icon-check" aria-hidden="true"></span>
						<?php esc_html_e( 'Apply', 'snapshot' ); ?>
					</span>

					<!-- Spinning loading icon -->
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
				</button>
			</div>

		</div>
	</div>
</div>