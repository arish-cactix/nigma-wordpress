<?php // phpcs:ignore
/**
 * Modal for preflight check result screen.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
?>

<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="snapshot-modal-preflight-cancel"
		class="sui-modal-content preflight-modal"
		aria-modal="true"
		aria-labelledby="modal-snapshot-preflight-cancel-title"
		aria-describedby="modal-snapshot-preflight-cancel-description"
	>
		<div class="sui-box snapshot-pf-cancel-box">

			<div class="sui-box-header sui-flatten sui-content-center header-logo-center">
				<h3 class="sui-box-title sui-lg"><?php esc_html_e( 'Cancel Backup process', 'snapshot' ); ?></h3>
				<span id="modal-snapshot-preflight-description" class="sui-description"><?php esc_html_e( 'Are you sure you want to cancel the backup creation process?', 'snapshot' ); ?></span>
			</div>

			<div class="sui-box-body">
				<div id="preflight-cancel-container" class="text-center">
					<button id="snapshot-pf-go-back" class="sui-button sui-button-ghost">
						<span class="sui-button-text-default">
							<?php esc_html_e( 'GO BACK', 'snapshot' ); ?>
						</span>
					</button>
					<button id="snapshot-pf-cancel" class="sui-button sui-button-ghost sui-button-blue snapshot-pf-cancel">
						<span class="sui-button-text-default">
							<?php esc_html_e( 'CANCEL BACKUP', 'snapshot' ); ?>
						</span>
					</button>
				</div>
			</div>

		</div>
	</div>
</div>