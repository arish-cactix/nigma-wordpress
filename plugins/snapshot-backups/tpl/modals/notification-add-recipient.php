<?php // phpcs:ignore
/**
 * Modal for adding email recipient.
 *
 * @package snapshot
 */

?>
<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="modal-notification-add-recipient"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-notification-add-recipient-label"
		aria-describedby="modal-notification-add-recipient-description"
	>
		<div class="sui-box">
			<form id="modal-notification-add-recipient-form">
				<?php wp_nonce_field( 'snapshot_validate_email', '_wpnonce_snapshot_validate_email' ); ?>

				<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
					<button class="sui-button-icon sui-button-float--right" data-modal-close>
						<span class="sui-icon-close sui-md" aria-hidden="true"></span>
						<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog.', 'snapshot' ); ?></span>
					</button>
					<h3 id="modal-notification-add-recipient-label" class="sui-box-title sui-lg"><?php esc_html_e( 'Add Recipient', 'snapshot' ); ?></h3>
					<p id="modal-notification-add-recipient-description" class="sui-description"><?php esc_html_e( 'Add a recipient to send the failed backups emails to.', 'snapshot' ); ?></p>
				</div>

				<div class="sui-box-body">
					<div class="sui-form-field">
						<label class="sui-label" for="modal-notification-add-recipient-input-name" id="modal-notification-add-recipient-label-name"><?php esc_html_e( 'First name', 'snapshot' ); ?></label>
						<input
							autocomplete="off"
							placeholder="<?php esc_attr_e( 'E.g. John', 'snapshot' ); ?>"
							name="name"
							id="modal-notification-add-recipient-input-name"
							class="sui-form-control"
							aria-labelledby="modal-notification-add-recipient-label-name"
						>
					</div>
					<div class="sui-form-field">
						<label class="sui-label" for="modal-notification-add-recipient-input-email" id="modal-notification-add-recipient-label-email"><?php esc_html_e( 'Email address', 'snapshot' ); ?></label>
						<input
							autocomplete="off"
							placeholder="<?php esc_attr_e( 'E.g. john@doe.com', 'snapshot' ); ?>"
							name="email"
							id="modal-notification-add-recipient-input-email"
							class="sui-form-control"
							aria-labelledby="modal-notification-add-recipient-label-email"
						>
						<span
							id="modal-notification-add-recipient-input-email-error"
							class="sui-error-message"
							style="display: none; float: right;"
							role="alert"
							><?php esc_html_e( 'Invalid email address', 'snapshot' ); ?></span>
						<span
							id="modal-notification-add-recipient-input-email-duplicate-error"
							class="sui-error-message"
							style="display: none; float: right;"
							role="alert"
							><?php esc_html_e( 'Recipient already exists', 'snapshot' ); ?></span>
						<div style="clear: both;"></div>
					</div>
				</div>

				<div class="sui-box-footer sui-flatten sui-content-separated">
					<button class="sui-button sui-button-ghost" data-modal-close=""><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>
					<button class="sui-button" id="modal-notification-add-recipient-input-button"><?php esc_html_e( 'Add', 'snapshot' ); ?></button>
				</div>

			</form>
		</div>
	</div>
</div>