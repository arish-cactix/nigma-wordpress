<?php // phpcs:ignore
/**
 * Modal for confirming the v3 uninstall.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Settings;
?>
<div class="sui-modal sui-modal-sm">
	<div
		role="dialog"
		id="modal-confirm-v3-uninstall"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-confirm-v3-uninstall-title"
		aria-describedby="modal-confirm-v3-uninstall-description"
	>
		<div class="sui-box">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">
				<button class="sui-button-icon sui-button-float--right" data-modal-close="">
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close', 'snapshot' ); ?></span>
				</button>

				<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
					<h3 id="modal-confirm-v3-uninstall-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Your local backups will be removed!', 'snapshot' ); ?></h3>
					<p id="modal-confirm-v3-uninstall-description" class="sui-description"><?php esc_html_e( 'Are you sure you want to uninstall the old Snapshot plugin? All your local backups will be removed after you uninstall the plugin.', 'snapshot' ); ?></p>
				<?php } else { ?>
					<h3 id="modal-confirm-v3-uninstall-title" class="sui-box-title sui-lg"><?php esc_html_e( 'Your local Snapshots will be removed!', 'snapshot' ); ?></h3>
					<p id="modal-confirm-v3-uninstall-description" class="sui-description"><?php esc_html_e( 'Are you sure you want to uninstall the old Snapshot plugin? All your local Snapshots will be removed after you uninstall the plugin.', 'snapshot' ); ?></p>
				<?php } ?>

			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">
				<button class="sui-button sui-button-ghost" data-modal-close=""><?php esc_html_e( 'Cancel', 'snapshot' ); ?></button>
				<button class="sui-button sui-button-red snapshot-uninstall-v3" id="snapshot-confirm-v3-uninstall" onclick="jQuery(window).trigger('snapshot:uninstall_snapshot_v3')">
					<span class="sui-button-text-default"><?php esc_html_e( 'Uninstall now', 'snapshot' ); ?></span>
					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						<?php esc_html_e( 'Uninstalling', 'snapshot' ); ?>
					</span>
				</button>
			</div>

		</div>
	</div>
</div>