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
	<div role="dialog" id="modal-destination-ftp-edit" class="sui-modal-content" aria-modal="true"
		aria-labelledby="modal-destination-ftp-edit-title" aria-describedby="modal-destination-ftp-edit-description">
		<div class="sui-box">

			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

				<figure class="sui-box-logo" aria-hidden="true">
					<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-ftp-header.svg' ) ); ?>" />
				</figure>

				<button class="sui-button-icon sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
				</button>

				<h3 class="sui-box-title sui-lg sui-hidden snapshot-title--ftp">
					<?php echo esc_html( __( 'Configure FTP', 'snapshot' ) ); ?>
				</h3>
				<h3 class="sui-box-title sui-lg sui-hidden snapshot-title--sftp">
					<?php echo esc_html( __( 'Configure SFTP', 'snapshot' ) ); ?>
				</h3>

			</div>

			<div class="sui-box-body">

				<div role="alert" class="sui-notice sui-notice-success" aria-live="assertive"
					id="notice-edit-ftp-destination-success">
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

				<div role="alert" class="sui-notice sui-notice-error" aria-live="assertive"
					id="notice-edit-ftp-destination-error">
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

				<div role="alert" id="notice-edit-ftp-duplicate-destination-error" class="sui-notice sui-notice-error"
					aria-live="assertive">
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

				<div role="alert" id="snapshot-test-connection-success-ftp" class="sui-notice sui-notice-success"
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

				<div role="alert" id="snapshot-test-connection-error-ftp" class="sui-notice sui-notice-error"
					aria-live="assertive">
					<div class="sui-notice-content">
						<div class="sui-notice-message">
							<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
							<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory and if you run into further issues, you can contact our Support team for help.', 'snapshot' ); ?>
							</p>
							<?php } else { ?>
								<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory and if you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?>
							</p>
							<?php } ?>
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

				<span class="sui-label"><?php echo esc_html( __( 'Connected FTP Account', 'snapshot' ) ); ?></span>
				<table class="sui-table" style=" margin-top: 0px; ">
					<tbody>
						<tr class="snapshot-configured-account snapshot-configured-account--ftp">
							<td class="snapshot-configured-account-email"></td>
						</tr>
					</tbody>
				</table>

				<form method="post" id="snapshot-edit-ftp-connection">
					<input type="hidden" name="tpd_action" value="update_destination">
					<input type="hidden" name="tpd_id">
					<input type="hidden" name="tpd_type">
					<input type="hidden" name="tpd_accesskey">
					<input type="hidden" name="tpd_secretkey">
					<input type="hidden" name="ftp-host">
					<input type="hidden" name="ftp-port">

					<div class="sui-form-field">
						<label for="edit-ftp-connection-name" id="label-edit-ftp-connection-name" class="sui-label">
							<?php echo esc_html( __( 'Destination name', 'snapshot' ) ); ?><span>
						</label>

						<input placeholder="<?php esc_attr_e( 'Place Destination Name here', 'snapshot' ); ?>"
							id="edit-ftp-connection-name" class="sui-form-control" name="tpd_name"
							aria-labelledby="label-edit-ftp-connection-name" />
						<span id="error-edit-ftp-connection-name" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label class="sui-label" for="edit-ftp-directory" id="label-edit-ftp-connection-path">
							<?php esc_html_e( 'Directory name', 'snapshot' ); ?><span><?php echo esc_html( '*' ); ?>
						</label>
						<input type="text" class="sui-form-control" name="tpd_path" id="edit-ftp-directory" value=""
							aria-describedby="error-edit-ftp-connection-path"
							aria-labelledby="label-edit-ftp-connection-path">
						<span id="description-ftp-directory-edit" class="sui-description" role="alert">
							<?php
									echo wp_kses(
										__(
											'Please enter the full directory path, e.g. <strong>/home/user/public_ftp/</strong>. Note: the path must already exist on your FTP/SFTP server.',
											'snapshot'
										),
										array(
											'strong' => array(),
										)
									);
									?>
						</span>
						<span id="error-edit-ftp-connection-path" class="sui-error-message"
							style="display: none; text-align: right;" role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label for="edit-ftp-details-limit" id="label-edit-ftp-details-limit" class="sui-label">
							<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?></span>
						</label>

						<input type="number" min="1" id="edit-ftp-details-limit" class="sui-form-control sui-input-sm"
							name="tpd_limit" aria-labelledby="label-edit-ftp-details-limit"
							aria-describedby="error-edit-ftp-details-limit description-edit-ftp-details-limit"
							value="" />

						<span id="error-edit-ftp-details-limit" class="sui-error-message" style="display: none;"
							role="alert"></span>
						<span id="description-edit-ftp-details-limit"
							class="sui-description"><?php echo esc_html( 'Set the number of backups you want to store before removing the older ones. It must be greater than 0.' ); ?></span>
					</div>

					<div class="sui-row">
						<div class="sui-col snapshot-ftp--mode">
							<div class="sui-form-field">
								<label class="sui-label" for="ftp-mode" id="destination-ftp-mode">
									<?php esc_html_e( 'Use Passive Mode', 'snapshot' ); ?>
									<span class="sui-tooltip sui-tooltip-constrained" style="--tooltip-width: 300px;"
										data-tooltip="<?php esc_attr_e( 'In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind a firewall. Passive mode is off by default.', 'snapshot' ); ?>">
										<span class="sui-icon-info" aria-hidden="true"></span>
									</span>
								</label>

								<div class="sui-button--group snapshot-ftp--passive__mode">
									<div class="sui-button--group__item selected">
										<input type="radio" class="sui-hidden" name="ftp-passive-mode" value="on" />
										<?php esc_html_e( 'On', 'snapshot' ); ?>
									</div>
									<div class="sui-button--group__item">
										<input type="radio" class="sui-hidden" name="ftp-passive-mode" value="off" />
										<?php esc_html_e( 'Off', 'snapshot' ); ?>
									</div>
								</div>
							</div>
						</div>
						<div class="sui-col">
							<div class="sui-form-field sui-width-110">
								<label class="sui-label" for="edit-ftp-timeout" id="edit-destination-ftp-timeout">
									<?php esc_html_e( 'Server Timeout', 'snapshot' ); ?>
									<span class="sui-tooltip sui-tooltip-constrained" style="--tooltip-width: 300px;"
										data-tooltip="<?php esc_attr_e( 'The default timeout for FTP connections is 90 seconds. Sometimes this timeout needs to be longer for slower connections to busy servers.', 'snapshot' ); ?>">
										<span class="sui-icon-info" aria-hidden="true"></span>
									</span>
								</label>

								<input type="text" class="sui-form-control" name="ftp-timeout" id="edit-ftp-timeout"
									value="90" aria-describedby="edit-destination-ftp-timeout">
							</div>
						</div>
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
					<button class="sui-button sui-button-ghost sui-button-icon-right snapshot-edit-test-connection"
						data-type="ftp"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'snapshot_ftp_connection' ) ); ?>">
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