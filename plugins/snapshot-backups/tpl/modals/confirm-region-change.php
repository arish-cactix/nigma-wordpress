<?php // phpcs:ignore
/**
 * Modal for confirmation of configs region mismatch.
 *
 * @package snapshot
 */
?>
<div class="sui-modal sui-modal-sm sui-wrap">
	<div role="dialog"
		id="snapshot-configs-apply-confirm-modal"
		class="sui-modal-content sui-content-fade-in"
		aria-labelledby="snapshot-config-apply-confirm-title"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right" data-modal-close="">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text">
						<?php esc_html_e( 'Close this dialog', 'snapshot' ); ?>
					</span>
				</button>

				<h2 id="snapshot-config-apply-confirm-title">
					<?php esc_html_e( 'Confirm apply config', 'snapshot' ); ?>
				</h2>

				<p id="snapshot-config--description" class="sui-description"></p>
			</div>

			<div class="sui-box-body">
				<div role="alert" id="snapshot-configs-warning" class="sui-notice sui-active sui-notice-error" aria-live="assertive" style="display: block;">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php echo wp_kses_post( __( 'The <strong>storage region</strong> in the selected config doesn\'t match the storage region in your current settings. All existing backups will be deleted after applying this config.', 'snapshot' ) ); ?></p>
						</div>
					</div>
				</div>
			</div>
			<div class="sui-box-footer sui-content-center sui-flatten sui-spacing-top--0 sui-spacing-bottom--60">
				<button class="sui-button sui-button-ghost" data-modal-close="">
					<?php esc_html_e( 'Cancel', 'snapshot' ); ?>
				</button>
				<button id="snapshot-config--confirm" class="sui-button sui-button-blue" data-nonce="" data-config_id="" data-next_step="">
					<!-- Default State Content -->
					<span class="sui-button-text-default">
						<span class="sui-icon-check" aria-hidden="true"></span> <?php esc_html_e( 'Confirm', 'snapshot' ); ?>
					</span>

					<!-- Loading State Content -->
					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</span>
				</button>
			</div>
		</div>
	</div>
</div>