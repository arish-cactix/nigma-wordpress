<?php // phpcs:ignore
/**
 * Modal for destination edit.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Helper\Settings;

$assets = new Helper\Assets();

wp_nonce_field( 'snapshot_update_destination', '_wpnonce-snapshot-update-destination' );

?>
<div class="sui-modal sui-modal-md">
	<div role="dialog" id="modal-destination-s3-edit" class="sui-modal-content" aria-modal="true"
		aria-labelledby="modal-destination-s3-edit-title" aria-describedby="modal-destination-s3-edit-description">
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

				<figure class="sui-box-logo" aria-hidden="true">
					<img src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws.png' ) ); ?>"
						srcset="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws@2x.png' ) ); ?> 2x" />
				</figure>

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>

				<h3 class="sui-box-title sui-lg"><?php echo wp_kses_post( __( 'Configure Amazon S3', 'snapshot' ) ); ?>
				</h3>

			</div>

			<div class="sui-box-body">

				<div role="alert" class="sui-notice sui-notice-success" aria-live="assertive"
					id="notice-edit-s3-destination-success">
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
					id="snapshot-test-connection-success-s3">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'The testing results were successful. Your account has been verified and we successfully accessed the bucket.', 'snapshot' ); ?>
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
					id="notice-edit-s3-destination-error">
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

				<div role="alert" id="notice-edit-s3-duplicate-destination-error" class="sui-notice sui-notice-error"
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

				<div role="alert" id="snapshot-test-connection-error-s3" class="sui-notice sui-notice-error"
					aria-live="assertive">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'The testing results have failed. We were unable to authorize your account and access the bucket. Please check your access credentials and bucket/folder path again. If you run into further issues, you can contact support for help.', 'snapshot' ); ?>
							</p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'The testing results have failed. We were unable to authorize your account and access the bucket. Please check your access credentials and bucket/folder path again. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
							</p>
							<?php } ?>
						</div>
					</div>
				</div>

				<form method="post" id="snapshot-edit-s3-connection">
					<input type="hidden" name="tpd_action" value="update_destination">
					<input type="hidden" name="tpd_id">
					<input type="hidden" name="tpd_region">
					<input type="hidden" name="tpd_type">

					<div class="sui-form-field">
						<label for="edit-s3-connection-name" id="label-edit-s3-connection-name" class="sui-label">
							<?php echo esc_html( __( 'Destination Name', 'snapshot' ) ); ?><span
								style="margin-left: 3px;"><?php echo esc_html( '*' ); ?>
						</label>

						<input placeholder="<?php esc_attr_e( 'Place Destination Name here', 'snapshot' ); ?>"
							id="edit-s3-connection-name" class="sui-form-control" name="tpd_name"
							aria-labelledby="label-edit-s3-connection-name" />
						<span id="error-edit-s3-connection-name" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-s3-connection-bucket-input" id="label-edit-s3-connection-bucket" class="sui-label">
							<?php echo esc_html( __( 'Bucket name', 'snapshot' ) ); ?><span
								style="margin-left: 3px;"><?php echo esc_html( '*' ); ?>
						</label>

						<input id="edit-s3-connection-bucket-input" class="sui-form-control"
							aria-labelledby="label-edit-s3-connection-bucket" name="tpd_bucket"
							value="" readonly>

						<span id="error-edit-s3-connection-bucket" class="sui-error-message"
							style="display: none; text-align:right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-s3-connection-path" id="label-edit-s3-connection-path" class="sui-label">
							<?php echo esc_html( __( 'Directory Path', 'snapshot' ) ); ?>
						</label>

						<input placeholder="<?php esc_attr_e( 'Place Directory Path here', 'snapshot' ); ?>"
							id="edit-s3-connection-path" class="sui-form-control" name="tpd_path"
							aria-labelledby="label-edit-s3-connection-path" />
						<span id="error-edit-s3-connection-path" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-s3-connection-access-key-id" id="label-edit-s3-connection-access-key-id"
							class="sui-label">
							<?php echo esc_html( __( 'AWS Access Key ID', 'snapshot' ) ); ?><span
								style="margin-left: 3px; "><?php echo esc_html( '*' ); ?></span>
						</label>

						<input placeholder="<?php esc_attr_e( 'Place Access Key ID here', 'snapshot' ); ?>"
							id="edit-s3-connection-access-key-id" class="sui-form-control" name="tpd_accesskey"
							aria-labelledby="label-edit-s3-connection-access-key-id" />
						<span id="error-edit-s3-connection-access-key-id" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>

					</div>

					<div class="sui-form-field">
						<label for="edit-s3-connection-secret-access-key"
							id="label-edit-s3-connection-secret-access-key" class="sui-label">
							<?php echo esc_html( __( 'AWS Secret Access Key', 'snapshot' ) ); ?><span
								style=" margin-left: 3px; "><?php echo esc_html( '*' ); ?></span>
						</label>

						<input placeholder="<?php esc_attr_e( 'Place Secret Access Key here', 'snapshot' ); ?>"
							id="edit-s3-connection-secret-access-key" class="sui-form-control" name="tpd_secretkey"
							aria-labelledby="label-edit-s3-connection-secret-access-key" />
						<span id="error-edit-s3-connection-secret-access-key" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-s3-connection-limit" id="label-edit-s3-connection-limit" class="sui-label">
							<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?><span
								style="margin-left: 3px;"><?php echo esc_html( '*' ); ?>
						</label>

						<input type="number" min="1" id="edit-s3-connection-limit" class="sui-form-control sui-input-sm"
							name="tpd_limit" aria-labelledby="label-edit-s3-connection-limit"
							aria-describedby="error-edit-s3-connection-limit description-edit-s3-connection-limit"
							value="" />

						<span id="error-edit-s3-connection-limit" class="sui-error-message" style="display: none;"
							role="alert"></span>
						<span id="description-edit-s3-connection-limit"
							class="sui-description"><?php echo esc_html_e( 'Set the number of exported backups you want to store in the third-party destination before removing the older ones. It must be greater than 0.', 'snapshot' ); ?></span>
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
						data-nonce="<?php echo esc_html( wp_create_nonce( 'snapshot_s3_connection' ) ); ?>">
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

<script type="text/html" id="snapshot-edit-destination--s3_other">
	<div class="sui-form-field">
		<label for="edit-s3-connection-endpoint"
			id="label-edit-s3-connection-endpoint" class="sui-label">
			<?php echo esc_html( __( 'Endpoint', 'snapshot' ) ); ?><span
				style=" margin-left: 3px; "><?php echo esc_html( '*' ); ?></span>
		</label>

		<input placeholder="<?php esc_attr_e( 'Place Endpoint here', 'snapshot' ); ?>"
			id="edit-s3-connection-endpoint" class="sui-form-control" name="tpd_endpoint"
			aria-labelledby="label-edit-s3-connection-endpoint" />
		<span id="error-edit-s3-connection-endpoint" class="sui-error-message"
			style="display: none; text-align: right;" role="alert"></span>
	</div>
</script>