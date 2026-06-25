<?php // phpcs:ignore
/**
 * Modal for confirming the backup delete.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="snapshot-modal-delete-backup"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="snapshot-modal-delete-backup-title"
		aria-describedby="snapshot-modal-delete-backup-description"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'snapshot' ); ?></span>
				</button>

				<h3 id="snapshot-modal-delete-backup-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Delete Backup', 'snapshot' ); ?></h3>
				<p id="snapshot-modal-delete-backup-description" class="sui-description" style="margin-bottom: 0;"><?php esc_html_e( 'Are you sure you want to delete the backup?', 'snapshot' ); ?></p>

				<div role="alert" id="snapshot-modal-delete-backup-error-notice" class="sui-notice sui-notice-error" aria-live="assertive" style="margin-top: 23px; text-align: left;">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
								<p><?php esc_html_e( 'We were unable to delete the backup due to the API connection problem. Please try again below, or contact support if the issue persists.', 'snapshot' ); ?></p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
								<p><?php echo wp_kses_post( sprintf( __( 'We were unable to delete the backup due to the API connection problem. Please try again below, or <a href="%s" target="_blank">contact our support team</a> if the issue persists.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?></p>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
				<button type="button" class="sui-button sui-button-ghost" data-modal-close=""><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>

				<button type="button" class="sui-button sui-button-ghost sui-button-red" aria-live="polite" id="snapshot-delete-backup-button">
					<span class="sui-button-text-default">
						<span class="sui-icon-trash" aria-hidden="true"></span>
						<?php esc_html_e( 'Delete', 'snapshot' ); ?>
					</span>
					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						<?php esc_html_e( 'Deleting backup', 'snapshot' ); ?>
					</span>
				</button>
			</div>

		</div>
	</div>
</div>