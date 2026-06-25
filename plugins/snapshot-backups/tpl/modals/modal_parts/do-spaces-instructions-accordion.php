<?php // phpcs:ignore
/**
 * Accordion with instructions on how to get DO Spaces credentials.
 *
 * @package snapshot
 */

?>
<div class="sui-accordion sui-accordion-flushed snapshot-digitalocean-credentials-howto" style="display: none;">

	<div class="sui-accordion-item">

		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title" style="flex: auto;"><span aria-hidden="true" class="sui-icon-warning-alert" style=" font-size: 12px; "></span>
			<?php esc_html_e( 'How to get DigitalOcean Spaces credentials?', 'snapshot' ); ?>
			</div>

			<div>
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
			</div>
		</div>

		<div class="sui-accordion-item-body">
			<div class="sui-box">
				<div class="sui-box-body">
					<?php /* translators: %s - PHP version */ ?>
					<p class="sui-description"><strong><?php esc_html_e( 'Follow these instructions to retrieve the DigitalOcean Spaces credentials.', 'snapshot' ); ?></strong></p>

						<ol style=" margin-left: 0px; list-style-position: inside; margin-bottom: 25px; ">
							<?php /* translators: %s - Link for DigitalOcean login */ ?>
							<li><?php echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Sign in</a> to your DigitalOcean account.', 'snapshot' ), 'https://cloud.digitalocean.com/login/' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'If you don’t already have an existing space, click <strong>Create</strong> and select <strong>Spaces</strong> from the dropdown menu.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Navigate to the <strong>API</strong> section and under Spaces access keys, click <strong>Generate New Key</strong>.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Once you have named your key and have clicked Generate Key, you will see the <strong>Test key (access key)</strong> and <strong>Secret (secret key)</strong> appear. These are the credentials you can use to connect your DigitalOcean Space.', 'snapshot' ) ); ?></li>
						</ol>

					<p class="sui-description"><strong><?php esc_html_e( 'Where to find the Region?', 'snapshot' ); ?></strong></br><?php echo wp_kses_post( __( 'Go to <strong>Spaces</strong> to view the Region chosen for the space.', 'snapshot' ) ); ?></p>
				</div>

			</div>
		</div>

	</div>
</div>