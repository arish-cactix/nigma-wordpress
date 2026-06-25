<?php // phpcs:ignore
/**
 * Third screen of Add Destination modal - Azure.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal-slide sui-loaded azure-screen" id="snapshot-add-destination-dialog-slide-3-azure" data-modal-size="md">
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
				data-modal-slide="snapshot-add-destination-dialog-slide-2-azure">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back', 'snapshot' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">
			<div class="sui-side-tabs sui-tabs sui-tabs-flushed snapshot-azure-selection">
				<div data-tabs>
					<div data-formtype="existing" class="active snapshot-azure-tab"><?php esc_html_e( 'Use Existing Container', 'snapshot' ); ?></div>
					<div data-formtype="new" class="snapshot-azure-tab"><?php esc_html_e( 'Create a New Container', 'snapshot' ); ?></div>
				</div>

				<div data-panes='azure'>
					<div class="sui-tab-boxed snapshot-destination-formbox snapshot-azure-exist-tab active">
						<div class="box-content container-existing-tab">

							<div role="alert" id="snapshot-correct-azure-details-existing" class="sui-notice sui-notice-success" aria-live="assertive" style="display:none;">
								<div class="sui-notice-content">
									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php /* translators: %s - Link for support */ ?>
										<p><?php echo esc_html( __( 'The testing results were successful. Your account has been verified and we successfully accessed the container. You’re good to proceed with the current settings. Click "Next" to continue.', 'snapshot' ) ); ?></p>

									</div>
								</div>
							</div>

							<div role="alert" id="snapshot-wrong-azure-existing-creds" class="sui-notice sui-notice-error"
								aria-live="assertive" style="display:none;">
								<div class="sui-notice-content">
									<div class="sui-notice-message">
										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
										<p><?php esc_html_e( 'It appears the authorization credentials you used were invalid. Please enter your credentials again or follow the given instructions to find them. If you run into further issues, you can contact support for help.', 'snapshot' ); ?>
										</p>
										<?php } else { ?>
											<?php /* translators: %s - Link for support */ ?>
										<p><?php echo wp_kses_post( sprintf( __( 'It appears the authorization credentials you used were invalid. Please enter your credentials again or follow the given instructions to find them. If you run into further issues, you can <a href="%s" target="_blank">contact our Support</a> team for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?>
										</p>
										<?php } ?>

									</div>
								</div>
							</div>

							<div role="alert" id="snapshot-duplicate-azure-existing" class="sui-notice sui-notice-error"
								aria-live="assertive">
								<div class="sui-notice-content">
									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
										<p><?php esc_html_e( 'The destination already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact support for help.', 'snapshot' ); ?>
										</p>
										<?php } else { ?>
										<p>
											<?php
											echo wp_kses_post(
												sprintf(
													/* translators: %s - Link for support */
													__( 'The destination already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ),
													'https://wpmudev.com/hub2/support#get-support'
												)
											);
											?>
										</p>
										<?php } ?>

									</div>
								</div>
							</div>

							<div role="alert" id="snapshot-azure-no-containers" class="sui-notice sui-notice-error"
								aria-live="assertive" style="display:none;">
								<div class="sui-notice-content">
									<div class="sui-notice-message">
										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<p><?php echo wp_kses_post( sprintf( __( 'It appears there are no containers available in your Microsoft Azure account. Please create a new container in Azure or use the <strong>Create a New Container</strong> tab above to proceed.', 'snapshot' ) ) ); ?>
										</p>
									</div>
								</div>
							</div>

							<form method="post" id="snapshot-test-azure-existing-container" data-type="container_exists">
								<input type="hidden" name="tpd_action" value="test_connection_final">
								<input type="hidden" name="tpd_save" value="0">
								<input type="hidden" name="tpd_secretkey" />
								<input type="hidden" name="tpd_accesskey" />

								<div class="sui-form-field">
									<label for="azure-connection-container" id="label-azure-connection-container" class="sui-label">
									<span
									class="azure-connection-container-label hidden" ><?php echo esc_html( __( 'Container Name', 'snapshot' ) ); ?></span>
										<?php echo esc_html( __( 'Azure Available Containers', 'snapshot' ) ); ?><span style="margin-left: 3px; ">*</span>
									</label>

									<select id="azure-connection-container" class="sui-select"
										aria-labelledby="label-azure-connection-container" name="tpd_container">

										<option value=" ">
											<?php echo esc_html( __( 'Choose Container', 'snapshot' ) ); ?>
										</option>
									</select>
									<span id="error-azure-connection-container" class="sui-error-message"
										style="display: none;  text-align: right;" role="alert"></span>

								</div>
								<div class="sui-form-field">
									<label for="azure-connection-path" id="label-azure-connection-path" class="sui-label">
										<?php echo esc_html( __( 'Azure Folder Path (optional)', 'snapshot' ) ); ?>
									</label>

									<input
										placeholder="Folder path"
										id="azure-connection-path"
										class="sui-form-control"
										name="tpd_path"
										aria-labelledby="label-azure-connection-path"
										aria-describedby="error-azure-connection-path description-azure-path"
									/>

									<span id="error-azure-path" class="sui-error-message" style="display: none; text-align:right;" role="alert"></span>
								</div>

								<div class="sui-form-field">
									<label for="azure-limit" id="label-azure-limit" class="sui-label">
										<?php echo esc_html( __( 'Export Backup Storage Limit', 'snapshot' ) ); ?>
									</label>

									<input
										type="number"
										min="1"
										id="azure-limit"
										class="sui-form-control sui-input-sm"
										name="tpd_limit"
										aria-labelledby="label-azure-limit"
										aria-describedby="error-azure-limit description-azure-limit"
										value="30"
									/>

									<span id="error-azure-limit" class="sui-error-message" style="display: none;" role="alert"></span>
								</div>
							</form>
						</div>
					</div>

					<div class="sui-tab-boxed snapshot-destination-formbox snapshot-azure-create-tab">
						<div class="box-content container-create-tab">

							<div role="alert" id="snapshot-correct-azure-details-new" class="sui-notice sui-notice-success" aria-live="assertive" style="display:none;">
								<div class="sui-notice-content">
									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php /* translators: %s - Link for support */ ?>
										<p><?php echo esc_html( __( 'The testing results were successful. Your account has been verified and we successfully accessed the container. You’re good to proceed with the current settings. Click "Next" to continue.', 'snapshot' ) ); ?></p>

									</div>
								</div>
							</div>

							<div role="alert" id="snapshot-wrong-azure-new-creds"
								class="sui-notice sui-notice-error" aria-live="assertive" style="display:none;">
								<div class="sui-notice-content">
									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
										<p><?php esc_html_e( 'It appears the authorization credentials you used were invalid. Follow the instructions below for guidance and add the credentials again. If you run into further issues, you can contact support for help. ', 'snapshot' ); ?>
										</p>
										<?php } else { ?>
											<?php /* translators: %s - Link for support */ ?>
										<p><?php echo wp_kses_post( sprintf( __( 'It appears the authorization credentials you used were invalid. Follow the instructions below for guidance and add the credentials again. If you run into further issues, you can <a href="%s" target="_blank">contact our Support</a> team for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?>
										</p>
										<?php } ?>

									</div>
								</div>
							</div>

							<div role="alert" id="snapshot-duplicate-azure-new" class="sui-notice sui-notice-error"
								aria-live="assertive">
								<div class="sui-notice-content">
									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
										<p><?php esc_html_e( 'The destination already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact support for help.', 'snapshot' ); ?>
										</p>
										<?php } else { ?>
										<p>
											<?php
											echo wp_kses_post(
												sprintf(
													/* translators: %s - Link for support */
													__( 'The destination already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ),
													'https://wpmudev.com/hub2/support#get-support'
												)
											);
											?>
										</p>
										<?php } ?>

									</div>
								</div>
							</div>

							<form method="post" id="snapshot-test-azure-new-container" data-type="create_container">
								<input type="hidden" name="tpd_action" value="test_connection_final">
								<input type="hidden" name="tpd_save" value="0">
								<input type="hidden" class="azure-connection-secretkey" name="tpd_secretkey" />
								<input type="hidden" class="azure-connection-accesskey" name="tpd_accesskey" />


								<div class="sui-form-field">
									<label for="azure-connection-new-container" id="label-azure-new-container" class="sui-label">
									<span
									class="azure-connection-new-container-label"><?php echo esc_html( __( 'New Container Name', 'snapshot' ) ); ?></span>
										<span>*</span>
									</label>

									<input
										placeholder="<?php esc_attr_e( 'Place Container Name', 'snapshot' ); ?>"
										id="azure-connection-new-container"
										class="sui-form-control"
										name="tpd_new_container"
										aria-labelledby="label-azure-new-container"
										aria-describedby="error-azure-new-container description-azure-new-container"
									/>

									<span id="error-azure-connection-new-container" class="sui-error-message" style="display: none; text-align:right;" role="alert"></span>
								</div>

								<div class="sui-form-field">
									<label for="azure-new-path" id="label-azure-new-path" class="sui-label">
										<?php echo esc_html( __( 'Azure Folder Path (optional)', 'snapshot' ) ); ?>
									</label>

									<input
										placeholder="Folder path"
										id="azure-new-path"
										class="sui-form-control"
										name="tpd_new_path"
										aria-labelledby="label-azure-new-path"
										aria-describedby="error-azure-new-path description-azure-new-path"
									/>

									<span id="error-azure-new-path" class="sui-error-message" style="display: none; text-align:right;" role="alert"></span>
								</div>

								<div class="sui-form-field">
									<label for="azure-new-limit" id="label-azure-new-limit" class="sui-label">
										<?php echo esc_html( __( 'Export Backup Storage Limit', 'snapshot' ) ); ?>
									</label>

									<input
										type="number"
										min="1"
										id="azure-new-limit"
										class="sui-form-control sui-input-sm"
										name="tpd_new_limit"
										aria-labelledby="label-azure-new-limit"
										aria-describedby="error-azure-new-limit description-azure-new-limit"
										value="30"
									/>

									<span id="error-azure-new-limit" class="sui-error-message" style="display: none;" role="alert"></span>
								</div>
							</form>

						</div>

					</div>

				</div>
			</div>
		</div>

		<div class="sui-box-footer sui-lg sui-content-separated">

			<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-2-azure">
				<span class="sui-icon-arrow-left" aria-hidden="true"></span>
				<?php esc_html_e( 'Back', 'snapshot' ); ?>
			</button>

			<div class="sui-actions-right">
				<button class="sui-button sui-button-ghost" id="snapshot-test-azure-connection-path" data-formtype="existing" >
					<span class="sui-button-text-default">
						<?php echo esc_html( 'Test Connection' ); ?>
					</span>
					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						<?php echo esc_html( 'Testing...' ); ?>
					</span>
				</button>
				<button class="sui-button sui-button-icon-right snapshot-next-destination-screen"
					id="snapshot-submit-azure-connection-path" data-formtype="existing">
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