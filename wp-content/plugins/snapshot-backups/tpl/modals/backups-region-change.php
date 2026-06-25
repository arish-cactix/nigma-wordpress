<?php // phpcs:ignore
/**
 * Modal for confirming the region change.
 *
 * @package snapshot
 */

?>
<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="modal-backups-region-change"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-backups-region-change-title"
		aria-describedby="modal-backups-region-change-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right cancel-region-change" data-modal-close="">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'snapshot' ); ?></span>
				</button>

				<h3 id="modal-backups-region-change-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Change backup region', 'snapshot' ); ?></h3>
				<p id="modal-backups-region-change-description" class="sui-description"><?php esc_html_e( 'Are you sure you want to change the backup region? All existing backups will be removed after changing the region.', 'snapshot' ); ?></p>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
				<button class="sui-button sui-button-ghost cancel-region-change" data-modal-close=""><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>
				<button class="sui-button" id="snapshot-backups-change-region">
					<?php esc_html_e( 'Change region', 'snapshot' ); ?>
				</button>
			</div>

		</div>
	</div>
</div>