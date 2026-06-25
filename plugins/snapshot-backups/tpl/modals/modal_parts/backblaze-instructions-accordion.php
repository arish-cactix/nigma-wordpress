<?php // phpcs:ignore
/**
 * Accordion with instructions on how to get Backblaze credentials.
 *
 * @package snapshot
 */

?>
<div class="sui-accordion sui-accordion-flushed snapshot-backblaze-credentials-howto" style="display: none;">

	<div class="sui-accordion-item">

		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title" style="flex: auto;"><span aria-hidden="true" class="sui-icon-warning-alert" style=" font-size: 12px; "></span>
			<?php esc_html_e( 'How to get Backblaze credentials?', 'snapshot' ); ?>
			</div>

			<div>
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
			</div>
		</div>

		<div class="sui-accordion-item-body">
			<div class="sui-box">
				<div class="sui-box-body">
					<?php /* translators: %s - PHP version */ ?>
					<p class="sui-description"><strong><?php esc_html_e( 'Follow these instructions to retrieve the Backblaze credentials.', 'snapshot' ); ?></strong></p>

						<ol style=" margin-left: 0px; list-style-position: inside; margin-bottom: 25px;">
							<?php /* translators: %s - Link for Backblaze login */ ?>
							<li><?php echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Sign in</a> to your Blackbaze account.', 'snapshot' ), 'https://secure.backblaze.com/user_signin.htm' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Go to <strong>My account/Buckets</strong> section.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'Go to <strong>App Keys</strong> and click <strong>Generate New Master Application Key</strong>.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'You will then be asked if you are sure you want to generate a new key. Click <strong>Yes! Generate Master Key</strong>.', 'snapshot' ) ); ?></li>
							<li><?php echo wp_kses_post( __( 'This will generate your credentials. Use the <strong>keyID</strong> and <strong>applicationKey</strong> to connect your Backblaze destination.', 'snapshot' ) ); ?></li>
						</ol>

					<p class="sui-description"><strong><?php esc_html_e( 'Where to find the Region?', 'snapshot' ); ?></strong></br><?php echo wp_kses_post( __( 'Go to <strong>My Account/My settings</strong> to view your Region.', 'snapshot' ) ); ?></p>

				</div>

			</div>
		</div>

	</div>
</div>