<?php // phpcs:ignore
/**
 * Accordion with instructions on how to get Google Cloud credentials.
 *
 * @package snapshot
 */

?>
<div class="sui-accordion sui-accordion-flushed snapshot-googlecloud-credentials-howto" style="display: none;">

	<div class="sui-accordion-item">

		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title" style="flex: auto;"><span aria-hidden="true" class="sui-icon-warning-alert" style=" font-size: 12px; "></span>
			<?php esc_html_e( 'How to get Google Cloud credentials?', 'snapshot' ); ?>
			</div>

			<div>
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
			</div>
		</div>

		<div class="sui-accordion-item-body">
			<div class="sui-box">
				<div class="sui-box-body">
					<?php /* translators: %s - PHP version */ ?>
					<p class="sui-description"><strong><?php esc_html_e( 'Follow these instructions to retrieve the Access Key and Secret.', 'snapshot' ); ?></strong>

						<ol style="margin: 20px 0 25px;list-style-position: inside;margin-bottom: 25px;">
							<?php /* translators: %s - Link for Google Cloud login docs */ ?>
							<li><?php echo wp_kses_post( sprintf( __( 'Open the <strong>Cloud Storage</strong> browser in the <a href="%s" target="_blank">Google Cloud Console</a>.', 'snapshot' ), 'https://console.cloud.google.com/' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Click <strong>Settings</strong>.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Select the <strong>Interoperability tab</strong>.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Click <strong>+ Create a key for a service account</strong>.', 'snapshot' ) ); ?>
							</li>
							<li><?php echo wp_kses_post( __( 'Select the <strong>service account</strong> you want the HMAC key to be associated with.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Click <strong>Create key</strong>.', 'snapshot' ) ); ?></li>
						</ol>

						<strong><?php esc_html_e( 'Where to find the Region?', 'snapshot' ); ?></strong></br>
						<?php echo wp_kses_post( __( 'Navigate to the <strong>Storage tab</strong> from the main menu to view the region, under the <i>location</i> column, you have chosen for the bucket.', 'snapshot' ) ); ?>
					</p>
				</div>

			</div>
		</div>

	</div>
</div>