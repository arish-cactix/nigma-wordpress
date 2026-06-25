<?php // phpcs:ignore
/**
 * Modal for viewing backup's log.
 *
 * @package snapshot
 */

?>
<div class="sui-modal sui-modal-lg">
	<div
		role="dialog"
		id="snapshot-modal-log"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-title-snapshot-modal-log"
		aria-describedby="modal-description-snapshot-modal-log"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-spacing-top--15 sui-spacing-bottom--15">
				<h3 class="sui-box-title"><?php esc_html_e( 'View Logs', 'snapshot' ); ?></h3>
				<div class="sui-actions-right">
					<button class="sui-button-icon" data-modal-close>
						<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					</button>
				</div>
			</div>
			<div class="sui-box-body">
				<div class="sui-border-frame">
					<div class="log-container">
					</div>
					<div class="log-loader">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>