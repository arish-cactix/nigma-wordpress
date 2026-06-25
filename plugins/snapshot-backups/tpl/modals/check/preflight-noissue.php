<?php // phpcs:ignore
/**
 * Modal for preflight check result screen.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();
?>

<div class="sui-modal sui-modal-lg">
	<div
		role="dialog"
		id="snapshot-modal-preflight-noissue"
		class="sui-modal-content preflight-modal"
		aria-modal="true"
		aria-labelledby="modal-snapshot-preflight-noissue-title"
		aria-describedby="modal-snapshot-preflight-noissue-description"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center header-logo-center">
				<div class="sui-box-banner banner-logo-center" role="banner" aria-hidden="true">
				<?php
				if ( isset( $plugin_icon_details['icon_url'] ) ) {
					?>
						<img class="icon-header-img" src="<?php echo esc_url( $plugin_icon_details['icon_url'] ); ?>" />
								<?php
				} elseif ( 'sui-icon-wpmudev-logo' === $plugin_icon_details['icon_class'] ) {
					?>
						<img
						src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-snapshot.png' ) ); ?>"
						/>
						<?php
				} elseif ( 'sui-no-icon' !== $plugin_icon_details['icon_class'] ) {
					?>
						<span
							class="custom-logo-icon <?php echo 'custom-icon-lg ' . esc_attr( $plugin_icon_details['icon_class'] ); ?>"
							aria-hidden="true">
						</span>
						<?php
				}
				?>
				</div>
				<button class="sui-button-icon sui-button-float--right snapshot-pf-confirm-cancel" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>
				<h3 class="sui-box-title sui-lg"><?php esc_html_e( 'Pre-Backup Inspection Complete', 'snapshot' ); ?></h3>
				<span id="modal-snapshot-preflight-description" class="sui-description"><?php esc_html_e( 'Preflight check complete. No errors found. Click Continue to proceed to the next step.', 'snapshot' ); ?></span>
			</div>

			<div class="sui-box-body">
				<div id="preflight-result-container" class="text-center">
					<div id="preflight-result">
						<div class="sui-accordion accordion-large-files">
							<div class="sui-accordion-item">
								<div class="sui-accordion-item-header">
									<div class="sui-accordion-item-title sui-accordion-col-4"><span aria-hidden="true" class="sui-icon-check-tick sui-success"></span> <?php esc_html_e( 'No Errors Found', 'snapshot' ); ?></div>
								</div>
							</div>
						</div>
					</div>

					<button class="sui-button sui-button-blue snapshot-pf-skip-to-bakup">
						<span class="sui-button-text-default">
							<?php esc_html_e( 'Continue', 'snapshot' ); ?>
						</span>
					</button>
				</div>
			</div>

		</div>
	</div>
</div>