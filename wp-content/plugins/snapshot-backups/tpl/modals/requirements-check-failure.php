<?php // phpcs:ignore
/**
 * Modal for requirements check failure, before allowing backups.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Assets;
use WPMUDEV\Snapshot4\Helper\Settings;

$assets = new Assets();
?>

<div class="sui-modal sui-modal-md">
	<?php
	wp_nonce_field( 'snapshot_recheck_requirements', '_wpnonce-snapshot_recheck_requirements' );
	?>
	<div
		role="dialog"
		id="modal-snapshot-requirements-check-failure"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-snapshot-requirements-check-failure-title"
		aria-describedby="modal-snapshot-requirements-check-failure-description"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center">
				<figure class="sui-box-banner" role="banner" aria-hidden="true">
					<img
						src="<?php echo esc_attr( $assets->get_asset( 'img/modal-requirement-fail.png' ) ); ?>"
						srcset="<?php echo esc_attr( $assets->get_asset( 'img/modal-requirement-fail.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/modal-requirement-fail@2x.png' ) ); ?> 2x"
					/>
				</figure>
				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>
				<h3 class="sui-box-title sui-lg" ><?php esc_html_e( 'Requirement failed', 'snapshot' ); ?></h3>
				<span class="sui-description"><?php esc_html_e( 'You have 1 requirement warning. It is likely that the backup will fail due to the issue. Please fix the isssue to run a backup.', 'snapshot' ); ?></span>
			</div>

			<div class="sui-accordion sui-accordion-flushed" style=" margin-top: 35px; ">

				<div class="sui-accordion-item sui-error sui-accordion-item--open">

					<div class="sui-accordion-item-header">
						<div class="sui-accordion-item-title"><span aria-hidden="true" class="sui-icon-warning-alert sui-error"></span>
						<?php esc_html_e( 'PHP v.7.0 or newer required', 'snapshot' ); ?>
						</div>

						<div>
							<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
						</div>
					</div>

					<div class="sui-accordion-item-body">
						<div class="sui-box">
							<div class="sui-box-body">
								<?php /* translators: %s - PHP version */ ?>
								<span class="sui-description"><?php printf( esc_html__( 'Your site is running on PHP v%s, and Snapshot requires v7.0 or newer. Update your PHP version to proceed. If you use a managed host, contact them directly to have it updated.', 'snapshot' ), esc_html( phpversion() ) ); ?></span>
							</div>
							<div class="sui-box-footer" style="justify-content: space-between;padding-top: 0;border-top: none;">
								<?php if ( ! Settings::get_branding_hide_doc_link() ) { ?>
									<a href="https://wpmudev.com/docs/wpmu-dev-plugins/snapshot-4-0/" target="_blank" class="sui-button sui-button-ghost"><span class="sui-icon-academy" aria-hidden="true"></span><?php esc_html_e( 'Documentation', 'snapshot' ); ?></a>
								<?php } ?>
								<button role="button" class="sui-button sui-button-ghost snapshot-recheck-requirements" aria-live="polite">
									<span class="sui-button-text-default">
										<span class="sui-icon-update" aria-hidden="true"></span><?php esc_html_e( 'Recheck', 'snapshot' ); ?>
									</span>
									<span class="sui-button-text-onload">
										<span class="sui-icon-loader sui-loading" aria-hidden="true"></span><?php esc_html_e( 'Rechecking', 'snapshot' ); ?>
									</span>
								</button>
							</div>
						</div>
					</div>

				</div>

			</div>

			<div class="sui-box-body">
				<div class="sui-block-content-center">
					<button role="button" class="sui-button snapshot-recheck-requirements" aria-live="polite">
						<span class="sui-button-text-default">
							<span class="sui-icon-update" aria-hidden="true"></span><?php esc_html_e( 'Recheck', 'snapshot' ); ?>
						</span>
						<span class="sui-button-text-onload">
							<span class="sui-icon-loader sui-loading" aria-hidden="true"></span><?php esc_html_e( 'Rechecking', 'snapshot' ); ?>
						</span>
					</button>
				</div>
			</div>

		</div>
	</div>
</div>