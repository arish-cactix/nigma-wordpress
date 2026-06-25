<?php // phpcs:ignore
/**
 * Modal for updating the manual backup comment.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
?>

<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="modal-snapshot-update-backup-comment"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-snapshot-edit-backup-comment-title"
		aria-describedby="modal-snapshot-edit-backup-comment-description"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center">
				<figure class="sui-box-banner" role="banner" aria-hidden="true">
					<img src="<?php echo esc_attr( $assets->get_asset( 'img/comment-logo.svg' ) ); ?>" />
				</figure>
				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>
				<h3 class="sui-box-title sui-lg" id="modal-snapshot-edit-backup-comment-title"><?php esc_html_e( 'Comment' ); ?></h3>
				<span id="modal-snapshot-edit-backup-comment-description" class="sui-description"><?php esc_html_e( 'Add a comment to your backup to distinguish it from other backups.' ); ?></span>
			</div>

			<div class="sui-box-body">
				<div
					id="notice-no-backup-id"
					class="sui-notice sui-notice-error"
					style="display: none;"
				>
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'Something went wrong. Please try again later.', 'snapshot' ); ?></p>
						</div>
						<div class="sui-notice-actions">
							<div class="sui-tooltip" data-tooltip="Dismiss">
								<button class="sui-button-icon" data-notice-close="notice-no-backup-id">
									<span class="sui-icon-check" aria-hidden="true"></span>
									<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this notice', 'snapshot' ); ?></span>
								</button>
							</div>
						</div>
					</div>
				</div>
				<form method="post" id="form-snapshot-edit-manual-backup-comment">
					<?php wp_nonce_field( 'snapshot_update_backup_comment', '_wpnonce-snapshot_update_backup_comment' ); ?>
					<input type="hidden" name="backup_id" id="comment-backup-id">
					<div class="sui-form-field">
						<label for="manual-backup-comment" id="manual-backup-comment-label" class="sui-label">
							<?php esc_html_e( 'Comment on this backup', 'snapshot' ); ?>
						</label>
						<textarea
							name="backup_description"
							id="manual-backup-comment-modal"
							rows="5"
							class="sui-form-control"
							placeholder="<?php esc_attr_e( 'E.g. Backup before changing site design', 'snapshot' ); ?>"
							aria-labelledby="manual-backup-comment-label"
							aria-describedby="error-manual-backup"
							></textarea>
					</div>

					<div class="sui-block-content-center">
						<button type="submit" class="sui-button sui-button-blue">
							<span class="sui-button-text-default sui-loading-text">
								<?php esc_html_e( 'Edit Comment', 'snapshot' ); ?>
							</span>

							<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						</button>
					</div>
				</form>
			</div>

		</div>
	</div>
</div>