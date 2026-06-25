<?php // phpcs:ignore
/**
 * Modal for destination edit.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Assets;
use WPMUDEV\Snapshot4\Helper\Settings;

$assets = new Assets();

wp_nonce_field( 'snapshot_update_destination', '_wpnonce-snapshot-update-destination' );

?>
<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="modal-destination-dropbox-edit"
		class="sui-modal-content"
		aria-modal="true"
		aria-labelledby="modal-destination-dropbox-edit-title"
		aria-describedby="modal-destination-dropbox-edit-description"
	>
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

				<figure class="sui-box-logo" aria-hidden="true">
					<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-dropbox-header.svg' ) ); ?>" />
				</figure>

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>

				<h3 class="sui-box-title sui-lg"><?php echo wp_kses_post( __( 'Configure Dropbox', 'snapshot' ) ); ?></h3>

			</div>

			<div class="sui-box-body">

					<div role="alert" class="sui-notice sui-notice-success" aria-live="assertive" id="notice-edit-dropbox-destination-success">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<p><?php esc_html_e( 'Destination has been updated successfully.', 'snapshot' ); ?></p>
							</div>
							<div class="sui-notice-actions">
								<button class="sui-button-icon hide-notice">
									<span class="sui-icon-check" aria-hidden="true"></span>
									<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this notice', 'snapshot' ); ?></span>
								</button>
							</div>
						</div>
					</div>

					<div role="alert" class="sui-notice sui-notice-error" aria-live="assertive" id="notice-edit-dropbox-destination-error">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<p><?php esc_html_e( 'Error occurred while updating the Destination. Please double-check your Directory ID is correct and try again.', 'snapshot' ); ?></p>
							</div>
							<div class="sui-notice-actions">
								<button class="sui-button-icon hide-notice">
									<span class="sui-icon-check" aria-hidden="true"></span>
									<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this notice', 'snapshot' ); ?></span>
								</button>
							</div>
						</div>
					</div>

					<div role="alert" id="notice-edit-dropbox-duplicate-destination-error" class="sui-notice sui-notice-error" aria-live="assertive">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
									<p><?php esc_html_e( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different directory. If you run into further issues, you can contact Support for help.', 'snapshot' ); ?></p>
								<?php } else { ?>
									<?php /* translators: %s - Link for support */ ?>
									<p><?php echo wp_kses_post( sprintf( __( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?></p>
								<?php } ?>
							</div>
						</div>
					</div>

					<div role="alert" id="snapshot-test-connection-success-dropbox" class="sui-notice sui-notice-success" aria-live="assertive">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<p><?php esc_html_e( 'The testing results were successful. Your account has been verified and we successfully accessed the directory.', 'snapshot' ); ?></p>
							</div>
							<div class="sui-notice-actions">
								<button class="sui-button-icon hide-notice">
									<span class="sui-icon-check" aria-hidden="true"></span>
									<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this notice', 'snapshot' ); ?></span>
								</button>
							</div>
						</div>
					</div>

					<div role="alert" id="snapshot-test-connection-error-dropbox" class="sui-notice sui-notice-error" aria-live="assertive">
						<div class="sui-notice-content">
							<div class="sui-notice-message">
								<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
								<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
									<p><?php esc_html_e( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory and if you run into further issues, you can contact our Support team for help.', 'snapshot' ); ?></p>
								<?php } else { ?>
									<?php /* translators: %s - Link for support */ ?>
									<p><?php echo wp_kses_post( sprintf( __( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory and if you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?></p>
								<?php } ?>
							</div>
							<div class="sui-notice-actions">
								<button class="sui-button-icon hide-notice">
									<span class="sui-icon-check" aria-hidden="true"></span>
									<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this notice', 'snapshot' ); ?></span>
								</button>
							</div>
						</div>
					</div>

					<span class="sui-label"><?php echo esc_html( __( 'Connected Dropbox Account', 'snapshot' ) ); ?></span>
					<table class="sui-table" style=" margin-top: 0px; ">
						<tbody>
							<tr class="snapshot-configured-account snapshot-configured-account--dropbox">
								<td class="snapshot-configured-account-email"></td>
							</tr>
						</tbody>
					</table>

					<form method="post" id="snapshot-edit-dropbox-connection">
						<input type="hidden" name="tpd_action" value="update_destination">
						<input type="hidden" name="tpd_id">
						<input type="hidden" name="tpd_type">
						<input type="hidden" name="tpd_email">
						<input type="hidden" name="tpd_accesskey">
						<input type="hidden" name="tpd_secretkey">

						<div class="sui-form-field">
							<label for="edit-dropbox-connection-name" id="label-edit-dropbox-connection-name" class="sui-label">
								<?php echo esc_html( __( 'Destination Name', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?>
							</label>

							<input
								placeholder="<?php esc_attr_e( 'Place Destination Name here', 'snapshot' ); ?>"
								id="edit-dropbox-connection-name"
								class="sui-form-control"
								name="tpd_name"
								aria-labelledby="label-edit-dropbox-connection-name"
							/>
							<span id="error-edit-dropbox-connection-name" class="sui-error-message" style="display: none; text-align: right;" role="alert"></span>
						</div>

						<div class="sui-form-field">
							<label for="edit-dropbox-connection-path" id="label-edit-dropbox-connection-path" class="sui-label">
								<?php echo esc_html( __( 'Directory', 'snapshot' ) ); ?>
								<span> (<?php esc_html_e( 'Optional', 'snapshot' ); ?>) </span>
							</label>

							<input
								placeholder="<?php esc_attr_e( 'Place directory name here', 'snapshot' ); ?>"
								id="edit-dropbox-connection-path"
								class="sui-form-control"
								name="tpd_path"
								aria-labelledby="label-edit-dropbox-connection-path"
							/>
							<span id="error-edit-dropbox-connection-path" class="sui-error-message" style="display: none; text-align: right;" role="alert"></span>
						</div>

						<div class="sui-form-field">
							<label for="edit-dropbox-connection-limit" id="label-edit-dropbox-connection-limit" class="sui-label">
								<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?>
							</label>

							<input
								type="number"
								min="1"
								id="edit-dropbox-connection-limit"
								class="sui-form-control sui-input-sm"
								name="tpd_limit"
								aria-labelledby="label-edit-dropbox-connection-limit"
								aria-describedby="error-edit-dropbox-connection-limit description-edit-dropbox-connection-limit"
								value=""
							/>

							<span id="error-edit-dropbox-connection-limit" class="sui-error-message" style="display: none;" role="alert"></span>
							<span id="description-edit-dropbox-connection-limit" class="sui-description"><?php echo esc_html_e( 'Set the number of backups you want to store before removing the older ones. It must be greater than 0.', 'snapshot' ); ?></span>
						</div>

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
					<button class="sui-button sui-button-ghost sui-button-icon-right snapshot-edit-test-connection" data-type="dropbox" data-nonce="<?php echo esc_attr( wp_create_nonce( 'snapshot_dropbox_connection' ) ); ?>">
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