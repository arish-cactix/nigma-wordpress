<?php
/**
 * Partial file for backup export progress bar
 *
 * @since 4.27.0
 * @package Snapshot
 */
?>

<div class="snapshot-export--progress__bar__wrap d-none">
	<div class="sui-box snapshot-backup--export is-exporting step-0" tabindex="0">
		<div class="sui-box-body sui-hidden-xs">
			<section>
				<div class="progressbar-container">
					<div class="progressbar-status">
						<div role="alert" class="sui-screen-reader-text" aria-live="assertive">
							<p><?php esc_html_e( 'Export progress at 0%', 'snapshot' ); ?></p>
						</div>
					</div>
					<ul class="progress-circles" aria-hidden="true">
						<li class="circle sui-tooltip ci-step-1" data-tooltip="<?php esc_attr_e( 'Preparing for Export', 'snapshot' ); ?>">
							<span class="sui-icon-check"></span>
						</li>
						<li class="circle sui-tooltip ci-step-2" data-tooltip="<?php esc_attr_e( 'Collecting files', 'snapshot' ); ?>">
							<span class="sui-icon-check"></span>
						</li>
						<li class="circle sui-tooltip ci-step-3" data-tooltip="<?php esc_attr_e( 'Collecting database files', 'snapshot' ); ?>">
							<span class="sui-icon-check"></span>
						</li>
					</ul>
				</div>
			</section>

			<div class="progress-title">
				<p><?php esc_html_e( 'Preparing for Export', 'snapshot' ); ?></p>
				<p id="collecting-files"><?php esc_html_e( 'Collecting files', 'snapshot' ); ?></p>
				<p id="collecting-database-files"><?php esc_html_e( 'Collecting database files', 'snapshot' ); ?></p>
				<p id="finalising-export"><?php esc_html_e( 'Finalising Export', 'snapshot' ); ?></p>
			</div>
		</div>
	</div>
</div>