<?php // phpcs:ignore
/**
 * Modal for azure destination edit.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Settings;

$assets = new Helper\Assets();

wp_nonce_field( 'snapshot_update_destination', '_wpnonce-snapshot-update-destination' );

?>
<div class="sui-modal sui-modal-md azure-screen">
	<div role="dialog" id="modal-destination-azure-edit" class="sui-modal-content" aria-modal="true"
		aria-labelledby="modal-destination-azure-edit-title" aria-describedby="modal-destination-azure-edit-description">
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

				<figure class="sui-box-logo" aria-hidden="true">
					<img src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-azure.png' ) ); ?>"
					srcset="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-azure.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/header-logo-azure@2x.png' ) ); ?> 2x" />
				</figure>

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>

				<h3 class="sui-box-title sui-lg"><?php echo wp_kses_post( __( 'Configure Azure', 'snapshot' ) ); ?>
				</h3>

			</div>

			<div class="sui-box-body">

				<div role="alert" class="sui-notice sui-notice-success" aria-live="assertive"
					id="notice-edit-azure-destination-success">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'Destination has been updated successfully.', 'snapshot' ); ?></p>
						</div>
						<div class="sui-notice-actions">
							<button class="sui-button-icon hide-notice">
								<span class="sui-icon-check" aria-hidden="true"></span>
								<span
									class="sui-screen-reader-text"><?php esc_html_e( 'Close this notice', 'snapshot' ); ?></span>
							</button>
						</div>
					</div>
				</div>

				<div role="alert" class="sui-notice sui-notice-success" aria-live="assertive"
					id="snapshot-test-connection-success-azure">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'The testing results were successful. Your account has been verified and we successfully accessed the container.', 'snapshot' ); ?>
							</p>
						</div>
						<div class="sui-notice-actions">
							<button class="sui-button-icon hide-notice">
								<span class="sui-icon-check" aria-hidden="true"></span>
								<span
									class="sui-screen-reader-text"><?php esc_html_e( 'Close this notice', 'snapshot' ); ?></span>
							</button>
						</div>
					</div>
				</div>

				<div role="alert" class="sui-notice sui-notice-error" aria-live="assertive"
					id="notice-edit-azure-destination-error">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'Error occurred while updating the Destination. Please double-check all credentials are correct and try again.', 'snapshot' ); ?>
							</p>
						</div>
						<div class="sui-notice-actions">
							<button class="sui-button-icon hide-notice">
								<span class="sui-icon-check" aria-hidden="true"></span>
								<span
									class="sui-screen-reader-text"><?php esc_html_e( 'Close this notice', 'snapshot' ); ?></span>
							</button>
						</div>
					</div>
				</div>

				<div role="alert" id="notice-edit-azure-duplicate-destination-error" class="sui-notice sui-notice-error"
					aria-live="assertive">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact support for help.', 'snapshot' ); ?>
							</p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
							</p>
							<?php } ?>
						</div>
					</div>
				</div>

				<div role="alert" id="snapshot-test-connection-error-azure" class="sui-notice sui-notice-error"
					aria-live="assertive">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'The testing results have failed. We were unable to authorize your account and access the container. Please check your access credentials and container/folder path again. If you run into further issues, you can contact support for help.', 'snapshot' ); ?>
							</p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'The testing results have failed. We were unable to authorize your account and access the container. Please check your access credentials and container/folder path again. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
							</p>
							<?php } ?>
						</div>
					</div>
				</div>

				<form method="post" id="snapshot-edit-azure-connection">
					<input type="hidden" name="tpd_action" value="update_destination">
					<input type="hidden" name="tpd_id">
					<input type="hidden" name="tpd_region">
					<input type="hidden" name="tpd_type">

					<div class="sui-form-field">
						<label for="edit-azure-connection-name" id="label-edit-azure-connection-name" class="sui-label">
							<span
								class="edit-azure-connection-name-label">
								<?php echo esc_html( __( 'Destination Name', 'snapshot' ) ); ?> </span> <span
								style="margin-left: 3px;">*
						</label>

						<input placeholder="<?php esc_attr_e( 'Place Destination Name here', 'snapshot' ); ?>"
							id="edit-azure-connection-name" class="sui-form-control" name="tpd_name"
							aria-labelledby="label-edit-azure-connection-name" />
						<span id="error-edit-azure-connection-name" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-azure-connection-container" id="label-edit-azure-connection-container" class="sui-label">
						<span
						class="edit-azure-connection-container-label"><?php echo esc_html( __( 'Containers', 'snapshot' ) ); ?></span><span style="margin-left: 3px; ">*</span>
						</label>

						<select id="edit-azure-connection-container" class="sui-select"
							aria-labelledby="label-edit-azure-connection-container" name="tpd_container" aria-hidden="false">

							<option value="">
								<?php echo esc_html( __( 'Choose Container', 'snapshot' ) ); ?>
							</option>
						</select>
						<span id="error-edit-azure-connection-container" class="sui-error-message"
							style="display: none;  text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-azure-connection-path" id="label-edit-azure-connection-path" class="sui-label">
							<span
								class="edit-azure-connection-path-label">
							<?php echo esc_html( __( 'Azure Folder Path', 'snapshot' ) ); ?>
						</label>

						<input
							placeholder="Folder path"
							id="edit-azure-connection-path"
							class="sui-form-control"
							name="tpd_path"
							aria-labelledby="label-edit-azure-connection-path"
							aria-describedby="error-edit-azure-connection-path description-azure-path"
						/>

						<span id="error-edit-azure-connection-path" class="sui-error-message" style="display: none; text-align:right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-azure-connection-secretkey"
							id="label-edit-azure-connection-secretkey" class="sui-label">
							<span
								class="edit-azure-connection-secretkey-label"><?php echo esc_html( __( 'Azure Account Name', 'snapshot' ) ); ?></span><span
								style=" margin-left: 3px; ">*</span>
						</label>

						<input
							placeholder="<?php echo esc_attr__( 'Place Account Name', 'snapshot' ); ?>"
							id="edit-azure-connection-secretkey" class="sui-form-control"
							name="tpd_secretkey" aria-labelledby="label-edit-azure-connection-secretkey" />
						<span id="error-edit-azure-connection-secretkey" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-azure-connection-accesskey"
							id="label-edit-azure-connection-accesskey" class="sui-label">
							<span
								class="edit-azure-connection-accesskey-label"><?php echo esc_html( __( 'Azure Access Key', 'snapshot' ) ); ?></span><span
								style=" margin-left: 3px; ">*</span>
						</label>

						<input
							placeholder="<?php echo esc_attr__( 'Place Access Key', 'snapshot' ); ?>"
							id="edit-azure-connection-accesskey" class="sui-form-control"
							name="tpd_accesskey" aria-labelledby="label-edit-azure-connection-accesskey" />
						<span id="error-edit-azure-connection-accesskey" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-azure-connection-limit" id="label-edit-azure-connection-limit" class="sui-label">
							<span
								class="edit-azure-connection-limit-label">
							<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?></span><span
								style="margin-left: 3px;">*
						</label>

						<input type="number" min="1" id="edit-azure-connection-limit" class="sui-form-control sui-input-sm"
							name="tpd_limit" aria-labelledby="label-edit-azure-connection-limit"
							aria-describedby="error-edit-azure-connection-limit description-edit-azure-connection-limit"
							value="" />

						<span id="error-edit-azure-connection-limit" class="sui-error-message" style="display: none;"
							role="alert"></span>
						<span id="description-edit-azure-connection-limit"
							class="sui-description"><?php echo esc_html_e( 'Set the number of backups you want to store before removing the older ones. It must be greater than 0.', 'snapshot' ); ?></span>
					</div>

					<div class="snapshot-edit-destination-additional--fields"></div>

				</form>

			</div>

			<div class="sui-box-footer sui-lg sui-content-separated">
				<div class="sui-flex-child-right">
					<button class="sui-button sui-button-ghost sui-button-red snapshot-delete-destination-button">
						<span class="sui-icon-trash" aria-hidden="true"></span>
						<?php esc_html_e( 'Delete', 'snapshot' ); ?>
					</button>
				</div>

				<div class="sui-actions-right">
					<button class="sui-button sui-button-icon-right sui-button-ghost snapshot-edit-test-connection"
						data-type=""
						data-nonce="<?php echo esc_html( wp_create_nonce( 'snapshot_azure_connection' ) ); ?>">
						<span class="sui-button-text-default">
							<?php esc_html_e( 'Test Connection', 'snapshot' ); ?>
						</span>
						<span class="sui-button-text-onload">
							<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							<?php esc_html_e( 'Testing...', 'snapshot' ); ?>
						</span>
					</button>
					<button class="sui-button sui-button-blue snapshot-edit-destination-button">
						<span class="sui-button-text-default">
							<span class="sui-icon-save" aria-hidden="true"></span>
							<?php esc_html_e( 'Save changes', 'snapshot' ); ?>
						</span>

						<span class="sui-button-text-onload">
							<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
							<?php esc_html_e( 'Saving...', 'snapshot' ); ?>
						</span>
					</button>
				</div>
			</div>

		</div>
	</div>
</div>

<script type="text/html" id="snapshot-edit-destination--azure_other">
	<div class="sui-form-field">
		<label for="edit-azure-connection-endpoint"
			id="label-edit-azure-connection-endpoint" class="sui-label">
			<?php echo esc_html( __( 'Endpoint', 'snapshot' ) ); ?><span
				style=" margin-left: 3px; ">*</span>
		</label>

		<input placeholder="<?php esc_attr_e( 'Place Endpoint here', 'snapshot' ); ?>"
			id="edit-azure-connection-endpoint" class="sui-form-control" name="tpd_endpoint"
			aria-labelledby="label-edit-azure-connection-endpoint" />
		<span id="error-edit-azure-connection-endpoint" class="sui-error-message"
			style="display: none; text-align: right;" role="alert"></span>
	</div>
</script>