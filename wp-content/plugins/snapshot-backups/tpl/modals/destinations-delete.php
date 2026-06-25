<?php // phpcs:ignore
/**
 * Modal for confirming destination delete.
 *
 * @package snapshot
 */

?>
<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="modal-destinations-delete"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-destinations-delete-title"
		aria-describedby="modal-destinations-delete-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right" data-modal-close="">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'snapshot' ); ?></span>
				</button>

				<h3 id="modal-destinations-delete-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Delete destination', 'snapshot' ); ?></h3>
				<p id="modal-destinations-delete-description" class="sui-description"><?php echo wp_kses_post( __( 'Are you sure you want to delete the <strong>Amazon S3</strong> destination?', 'snapshot' ) ); ?></p>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
				<button class="sui-button sui-button-ghost" data-modal-close=""><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>
				<button class="sui-button sui-button-ghost sui-button-red" id="modal-destinations-delete-button">
					<span class="sui-icon-trash" aria-hidden="true"></span>
					<?php esc_html_e( 'Delete', 'snapshot' ); ?>
				</button>
			</div>

		</div>
	</div>
</div>