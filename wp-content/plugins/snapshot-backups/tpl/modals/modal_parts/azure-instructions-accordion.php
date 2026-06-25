<?php // phpcs:ignore
/**
 * Accordion with instructions on how to get Azure credentials.
 *
 * @package snapshot
 */

?>
<div class="sui-accordion sui-accordion-flushed snapshot-azure-credentials-howto">

	<div class="sui-accordion-item">

		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title" style="flex: auto;"><span aria-hidden="true" class="sui-icon-warning-alert" style=" font-size: 12px; "></span>
			<?php esc_html_e( 'How to get Microsoft Azure credentials?', 'snapshot' ); ?>
			</div>

			<div>
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
			</div>
		</div>

		<div class="sui-accordion-item-body">
			<div class="sui-box">
				<div class="sui-box-body">
					<?php /* translators: %s - PHP version */ ?>
					<p class="sui-description instructions-heading"><strong><?php esc_html_e( 'Follow these instructions to retrieve the Connection String and Container Name.', 'snapshot' ); ?></strong></p>


						<div>
							<div class="destination_howto_accordion">
								<ol style=" margin-left: 0px; list-style-position: inside; margin-top:30px">
									<?php /* translators: %s - Link for Azure docs */ ?>
									<li><?php echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Sign in</a> to the Microsoft Azure Portal as the root user.', 'snapshot' ), 'https://portal.azure.com/signin/index/' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'Navigate to <strong>Storage Accounts</strong> by selecting Storage accounts in the left-hand menu. Then, choose the <strong>Storage account</strong> for which you want to retrieve the credentials.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'In the side menu, under the <strong>Security + Networking</strong> section, select <strong>Access keys</strong>. Your account access keys appear, as well as the <strong>Azure Storage Account name</strong>.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'Select Show keys to view your access keys.', 'snapshot' ) ); ?></li>
								</ol>
							</div>
						</div>

					<?php /* translators: %s - Link for Azure docs */ ?>
					<p style="text-align: left; margin-bottom: 0;"><?php echo wp_kses_post( sprintf( __( 'You can find more info in the <a href="%s" target="_blank">Azure Documentation</a>', 'snapshot' ), 'https://learn.microsoft.com/en-us/azure/storage/common/storage-account-keys-manage?tabs=azure-portal#view-account-access-keys' ) ); ?></p>
				</div>

			</div>
		</div>

	</div>
</div>