<?php // phpcs:ignore
/**
 * Accordion with instructions on how to get Directory Id for Google Drive destinations.
 *
 * @package snapshot
 */

?>
<div class="sui-accordion sui-accordion-flushed snapshot-gd-credentials-howto">

	<div class="sui-accordion-item">

		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title" style="flex: auto;"><span aria-hidden="true" class="sui-icon-warning-alert" style=" font-size: 12px; "></span>
			<?php esc_html_e( 'How to get your Directory ID?', 'snapshot' ); ?>
			</div>

			<div>
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
			</div>
		</div>

		<div class="sui-accordion-item-body">
			<div class="sui-box">
				<div class="sui-box-body">
					<?php /* translators: %s - PHP version */ ?>
					<p class="sui-description"><strong><?php esc_html_e( 'Follow these instructions to retrieve your Google Drive Directory ID', 'snapshot' ); ?></strong></p>

						<ol style=" margin-left: 0px; list-style-position: inside; word-wrap: break-word; ">
							<?php /* translators: %s - Link for Google Drive login */ ?>
							<li><?php echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Sign in</a> to your Google Drive account.', 'snapshot' ), 'https://drive.google.com' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Navigate to the folder where you want to upload the Snapshot archives or create a new one.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Copy the Directory ID found in the URL. This is everything that comes after "folders/" in the URL.', 'snapshot' ) ); ?></br>
							<?php echo wp_kses_post( __( '<strong>For example</strong>, if the URL is "https://drive.google.com/drive/folders/1dyUEebJaFnWa3Z4n0BFMVAXQ7mfUH11g", then the Directory ID would be "1dyUEebJaFnWa3Z4n0BFMVAXQ7mfUH11g".', 'snapshot' ) ); ?></li>
						</ol>

				</div>

			</div>
		</div>

	</div>
</div>