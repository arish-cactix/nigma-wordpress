<?php // phpcs:ignore
/**
 * Modal for confirming the resetting of the settings.
 *
 * @package snapshot
 */

?>
<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="modal-settings-reset-settings"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-title-modal-settings-reset-settings"
		aria-describedby="modal-description-modal-settings-reset-settings"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right" data-modal-close="">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'snapshot' ); ?></span>
				</button>

				<h3 id="modal-settings-reset-settings-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Reset settings', 'snapshot' ); ?></h3>
				<p id="modal-settings-reset-settings-description" class="sui-description"><?php esc_html_e( 'Are you sure you want to reset Snapshot\'s settings back to the factory defaults?', 'snapshot' ); ?></p>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
				<button class="sui-button sui-button-ghost" data-modal-close=""><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>
				<button class="sui-button sui-button-ghost sui-button-red" id="snapshot-settings-reset-settings">
					<span class="sui-icon-undo" aria-hidden="true"></span>
					<?php esc_html_e( 'Reset', 'snapshot' ); ?>
				</button>
			</div>

		</div>
	</div>
</div>