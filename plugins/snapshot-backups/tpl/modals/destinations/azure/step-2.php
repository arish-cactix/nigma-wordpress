<?php // phpcs:ignore
/**
 * Second screen of Add Destination modal - Azure.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal-slide sui-loaded azure-screen" id="snapshot-add-destination-dialog-slide-2-azure" data-modal-size="md">
	<div class="sui-box">

		<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60 sui-spacing-bottom--30">

			<figure class="sui-box-logo" aria-hidden="true">
				<img src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-azure.png' ) ); ?>"
					srcset="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-azure.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/header-logo-azure@2x.png' ) ); ?> 2x" />
			</figure>

			<button class="sui-button-icon sui-button-float--right" data-modal-close>
				<span class="sui-icon-close sui-md" aria-hidden="true"></span>
			</button>

			<h3 class="sui-box-title sui-lg"><?php echo esc_html( __( 'Connect Microsoft Azure', 'snapshot' ) ); ?></h3>

			<span class="sui-description">
			<?php
				esc_html_e( 'Easily connect with Azure to authorize Snapshot and store your backups in their directory.', 'snapshot' );
			?>
			</span>

			<button class="sui-button-icon sui-button-float--left"
				data-modal-slide="snapshot-add-destination-dialog-slide-1">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back', 'snapshot' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">

			<div class="sui-side-tabs sui-tabs sui-tabs-flushed snapshot-azure-selection">

				<div class="snapshot-destination-formbox">
					<div class="box-content">
						<span class="sui-description" data-type='azure'>
							<?php
								echo wp_kses_post(
									sprintf(
										/* translators: %s - Class name to expand instructions */
										__( 'Unsure how to get your Azure credentials? <span class="%s">Follow the instructions</span> below.', 'snapshot' ),
										'snapshot-expand-instructions-link'
									)
								);
								?>
						</span>
						<div role="alert" id="snapshot-wrong-azure-creds" class="sui-notice sui-notice-error"
							aria-live="assertive" style="display:none;">
							<div class="sui-notice-content">
								<div class="sui-notice-message">
									<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

									<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
									<p><?php esc_html_e( 'It appears the authorization credentials you used were invalid. Please enter your credentials again or follow the instructions below to find them. If you run into further issues, you can contact support for help.', 'snapshot' ); ?>
									</p>
									<?php } else { ?>
										<?php /* translators: %s - Link for support */ ?>
									<p><?php echo wp_kses_post( sprintf( __( 'It appears the authorization credentials you used were invalid. Please enter your credentials again or follow the instructions below to find them. If you run into further issues, you can <a href="%s" target="_blank">contact our Support</a> team for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?>
									</p>
									<?php } ?>

								</div>
							</div>
						</div>

						<form method="post" id="snapshot-test-azure-connection-initial" data-type="load_containers">
							<input type="hidden" name="tpd_action" value="load_containers">
							<input type="hidden" name="tpd_type" value="azure">

							<div class="sui-form-field">
								<label for="azure-connection-secretkey"
									id="label-azure-connection-secretkey" class="sui-label">
									<span
										class="azure-connection-secretkey-label"><?php echo esc_html( __( 'Azure Account Name', 'snapshot' ) ); ?></span><span
										style=" margin-left: 3px; ">*</span>
								</label>

								<input
									placeholder="<?php echo esc_attr__( 'Place Account Name', 'snapshot' ); ?>"
									id="azure-connection-secretkey" class="sui-form-control"
									name="tpd_secretkey" aria-labelledby="label-azure-connection-secretkey" />
								<span id="error-azure-connection-secretkey" class="sui-error-message"
									style="display: none; text-align: right;" role="alert"></span>
							</div>

							<div class="sui-form-field">
								<label for="azure-connection-accesskey"
									id="label-azure-connection-accesskey" class="sui-label">
									<span
										class="azure-connection-accesskey-label"><?php echo esc_html( __( 'Azure Access Key', 'snapshot' ) ); ?></span><span
										style=" margin-left: 3px; ">*</span>
								</label>

								<input
									placeholder="<?php echo esc_attr__( 'Place Access Key', 'snapshot' ); ?>"
									id="azure-connection-accesskey" class="sui-form-control"
									name="tpd_accesskey" aria-labelledby="label-azure-connection-accesskey" />
								<span id="error-azure-connection-accesskey" class="sui-error-message"
									style="display: none; text-align: right;" role="alert"></span>
							</div>
						</form>
					</div>
					<?php
						$this->render(
							'modals/modal_parts/azure-instructions-accordion',
							array()
						);
						?>
				</div>
			</div>
		</div>

		<div class="sui-box-footer sui-flatten sui-lg sui-content-separated">

			<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-1">
				<span class="sui-icon-arrow-left" aria-hidden="true"></span>
				<?php esc_html_e( 'Back', 'snapshot' ); ?>
			</button>

			<div class="sui-actions-right">
				<button class="sui-button sui-button-icon-right snapshot-next-destination-screen"
					id="snapshot-submit-azure-connection-test">
					<span class="sui-button-text-default">
						<?php esc_html_e( 'Next', 'snapshot' ); ?>
						<span class="sui-icon-arrow-right" aria-hidden="true"></span>
					</span>

					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						<?php esc_html_e( 'Connecting...', 'snapshot' ); ?>
					</span>
				</button>
			</div>
		</div>

	</div>
</div>

<script type="text/html" id="snapshot-azure-compatible-destination--other">
	<div class="sui-form-field azure-compatible-endpoint">
		<label for="azure-compatible-connection-endpoint"
			id="label-azure-compatible-connection-endpoint" class="sui-label">
			<span
				class="azure-compatible-connection-endpoint-label"><?php echo esc_html( __( 'Endpoint', 'snapshot' ) ); ?></span><span
				style=" margin-left: 3px; ">*</span>
		</label>

		<input
			placeholder="<?php echo esc_attr__( 'Place Endpoint here', 'snapshot' ); ?>"
			id="azure-compatible-connection-endpoint" class="sui-form-control"
			name="tpd_endpoint" aria-labelledby="label-azure-compatible-connection-endpoint"  />
		<span id="error-azure-compatible-connection-endpoint" class="sui-error-message"
			style="display: none; text-align: right;" role="alert"></span>
	</div>
</script>