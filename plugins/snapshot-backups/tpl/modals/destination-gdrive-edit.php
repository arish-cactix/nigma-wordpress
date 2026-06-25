<?php // phpcs:ignore
/**
 * Modal for destination edit.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper;
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;

$assets = new Helper\Assets();

wp_nonce_field( 'snapshot_update_destination', '_wpnonce-snapshot-update-destination' );
?>
<div class="sui-modal sui-modal-md">
	<div role="dialog" id="modal-destination-gdrive-edit" class="sui-modal-content" aria-modal="true"
		aria-labelledby="modal-destination-gdrive-edit-title"
		aria-describedby="modal-destination-gdrive-edit-description">
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

				<figure class="sui-box-logo" aria-hidden="true">
					<img src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-gd.png' ) ); ?>"
						srcset="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-gd.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/header-logo-gd@2x.png' ) ); ?> 2x" />
				</figure>

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>

				<h3 class="sui-box-title sui-lg">
					<?php echo wp_kses_post( __( 'Configure Google Drive', 'snapshot' ) ); ?></h3>

			</div>

			<div class="sui-box-body">
				<div role="alert" id="snapshot-permission-error-gdrive" class="sui-notice sui-notice-error"
					aria-live="assertive" style="display:none;">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'We are unable to process this action at the moment. Please try again later.', 'snapshot' ); ?>
							</p>
						</div>
					</div>
				</div>

				<div role="alert" id="snapshot-unknown-error" class="sui-notice sui-notice-error" aria-live="assertive"
					style="display:none;">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p class="snapshot-display--message"></p>
						</div>
					</div>
				</div>

				<div role="alert" id="snapshot-wrong-gd-details" class="sui-notice sui-notice-error"
					aria-live="assertive" style="display:none;">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory ID and if you run into further issues, you can contact support for help.', 'snapshot' ); ?>
							</p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory ID and if you run into further issues, you can <a href="%s" target="_blank">contact our Support team</a> for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?>
							</p>
							<?php } ?>
						</div>
					</div>
				</div>

				<div role="alert" class="sui-notice sui-notice-success" aria-live="assertive"
					id="notice-edit-gdrive-destination-success">
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

				<div role="alert" id="snapshot-test-connection-success-gdrive" class="sui-notice sui-notice-success"
					aria-live="assertive">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'The testing results were successful. Your account has been verified and we successfully accessed the directory.', 'snapshot' ); ?>
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

				<div role="alert" id="snapshot-test-connection-error-gdrive" class="sui-notice sui-notice-error"
					aria-live="assertive">
					<div class="sui-notice-content">
						<div class="sui-notice-message">

							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory ID and if you run into further issues, you can contact our Support team for help.', 'snapshot' ); ?>
							</p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory ID and if you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
							</p>
							<?php } ?>
						</div>
					</div>
				</div>

				<div role="alert" class="sui-notice sui-notice-error" aria-live="assertive"
					id="notice-edit-gdrive-destination-error">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<p><?php esc_html_e( 'Error occurred while updating the Destination. Please double-check your Directory ID is correct and try again.', 'snapshot' ); ?>
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

				<div role="alert" id="notice-edit-gdrive-duplicate-destination-error"
					class="sui-notice sui-notice-error" aria-live="assertive">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different directory. If you run into further issues, you can contact Support for help.', 'snapshot' ); ?>
							</p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
							</p>
							<?php } ?>
						</div>
					</div>
				</div>

				<span
					class="sui-label"><?php echo esc_html( __( 'Connected Google Drive Account', 'snapshot' ) ); ?></span>

				<table class="sui-table" style=" margin-top: 0px; ">
					<tbody>
						<tr class="snapshot-configured-account snapshot-configured-account--gdrive">
							<td class="snapshot-configured-account-email"></td>
						</tr>
					</tbody>
				</table>

				<form method="post" id="snapshot-edit-gdrive-connection">
					<input type="hidden" name="tpd_action" value="update_destination">
					<input type="hidden" name="tpd_id">
					<input type="hidden" name="tpd_type">
					<input type="hidden" name="tpd_email">
					<input type="hidden" name="tpd_accesskey">
					<input type="hidden" name="tpd_secretkey">

					<div class="sui-form-field">
						<label for="edit-gdrive-connection-name" id="label-edit-gdrive-connection-name"
							class="sui-label">
							<?php echo esc_html( __( 'Destination Name', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?>
						</label>

						<input placeholder="<?php esc_attr_e( 'Place Destination Name here', 'snapshot' ); ?>"
							id="edit-gdrive-connection-name" class="sui-form-control" name="tpd_name"
							aria-labelledby="label-edit-gdrive-connection-name" />
						<span id="error-edit-gdrive-connection-name" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-gdrive-connection-path" id="label-edit-gdrive-connection-path"
							class="sui-label">
							<?php echo esc_html( __( 'Directory ID', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?></span>
						</label>

						<input placeholder="<?php esc_attr_e( 'Place Directory ID here', 'snapshot' ); ?>"
							id="edit-gdrive-connection-path" class="sui-form-control" name="tpd_path"
							aria-labelledby="label-edit-gdrive-connection-path" />
						<span id="error-edit-gdrive-connection-path" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-gdrive-connection-limit" id="label-edit-gdrive-connection-limit"
							class="sui-label">
							<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?>
						</label>

						<input type="number" min="1" id="edit-gdrive-connection-limit"
							class="sui-form-control sui-input-sm" name="tpd_limit"
							aria-labelledby="label-edit-gdrive-connection-limit"
							aria-describedby="error-edit-gdrive-connection-limit description-edit-gdrive-connection-limit"
							value="" />

						<span id="error-edit-gdrive-connection-limit" class="sui-error-message" style="display: none;"
							role="alert"></span>
						<span id="description-edit-gdrive-connection-limit"
							class="sui-description"><?php echo esc_html_e( 'Set the number of backups you want to store before removing the older ones. It must be greater than 0.', 'snapshot' ); ?></span>
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
					<button class="sui-button sui-button-icon-right sui-button-ghost snapshot-edit-test-connection"
						data-type="gdrive"
						data-nonce="<?php echo esc_html( wp_create_nonce( 'snapshot_gd_connection' ) ); ?>">
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