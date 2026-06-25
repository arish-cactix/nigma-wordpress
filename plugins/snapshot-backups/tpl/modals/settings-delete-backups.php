<?php // phpcs:ignore
/**
 * Modal for confirming the bulk backup delete.
 *
 * @package snapshot
 */

?>
<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="modal-settings-delete-backups"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-settings-delete-backups-title"
		aria-describedby="modal-settings-delete-backups-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'snapshot' ); ?></span>
				</button>

				<h3 id="modal-settings-delete-backups-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Delete Backups', 'snapshot' ); ?></h3>
				<p id="modal-settings-delete-backups-description" class="sui-description"><?php esc_html_e( 'Are you sure you want to delete all your remote backups?', 'snapshot' ); ?></p>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
				<button class="sui-button sui-button-ghost" data-modal-close=""><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>
				<button class="sui-button sui-button-ghost sui-button-red" id="snapshot-settings-delete-backups">
					<span class="sui-icon-trash" aria-hidden="true"></span>
					<?php esc_html_e( 'Delete', 'snapshot' ); ?>
				</button>
			</div>

		</div>
	</div>
</div>