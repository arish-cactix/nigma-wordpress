<?php // phpcs:ignore
/**
 * Accordion with instructions on how to get Wasabi credentials.
 *
 * @package snapshot
 */

?>
<div class="sui-accordion sui-accordion-flushed snapshot-wasabi-credentials-howto" style="display: none;">

	<div class="sui-accordion-item">

		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title" style="flex: auto;"><span aria-hidden="true" class="sui-icon-warning-alert" style=" font-size: 12px; "></span>
			<?php esc_html_e( 'How to get Wasabi credentials?', 'snapshot' ); ?>
			</div>

			<div>
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
			</div>
		</div>

		<div class="sui-accordion-item-body">
			<div class="sui-box">
				<div class="sui-box-body">
					<p class="sui-description"><strong><?php esc_html_e( 'Follow these instructions to retrieve the Wasabi credentials.', 'snapshot' ); ?></strong></p>
						<ol style=" margin-left: 0px; list-style-position: inside; margin-bottom: 25px;">
							<?php /* translators: %s - Link for Wasabi login */ ?>
							<li><?php echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Sign in</a> to your Wasabi account.', 'snapshot' ), 'https://console.wasabisys.com' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'If you haven’t created a bucket yet, you can create one by clicking on <strong>Create Bucket</strong>. Remember the region you have chosen here, since this is an important piece of information needed to successfully connect Wasabi.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Next, go to the <strong>Access Keys tab</strong> on the left sidebar and click <strong>Create New Access Key</strong>.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'This will generate your <strong>access key</strong> and <strong>secret key</strong> credentials. Be sure to download the key file now because you will not be able to retrieve your access key again.', 'snapshot' ) ); ?></li>
						</ol>

					<p class="sui-description"><strong><?php esc_html_e( 'Where to find the Region?', 'snapshot' ); ?></strong></br><?php echo wp_kses_post( __( 'Go to the <strong>Buckets</strong> section to view the Region in your Bucket list.', 'snapshot' ) ); ?></p>
				</div>

			</div>
		</div>

	</div>
</div>