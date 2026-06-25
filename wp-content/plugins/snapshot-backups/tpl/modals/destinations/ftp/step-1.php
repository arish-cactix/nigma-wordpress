<?php // phpcs:ignore
/**
 * Second screen of Add Destination modal - FTP.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Task\Backup\Fail;
use WPMUDEV\Snapshot4\Helper\Settings;
use WPMUDEV\Snapshot4\Model\Env;

?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-2-ftp" data-modal-size="md">
	<div class="sui-box">

		<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

			<figure class="sui-box-logo" aria-hidden="true">
				<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-ftp-header.svg' ) ); ?>" />
			</figure>

			<button class="sui-button-icon sui-button-float--right" data-modal-close>
				<span class="sui-icon-close sui-md" aria-hidden="true"></span>
			</button>

			<h3 class="sui-box-title sui-lg"><?php echo esc_html( __( 'Connect FTP/SFTP', 'snapshot' ) ); ?></h3>
			<span class="sui-description">
				<?php echo wp_kses_post( __( 'To connect with FTP, you need to enter your FTP account credentials below.', 'snapshot' ) ); ?>
				<?php
				if ( Env::is_wpmu_hosting() ) {
					/* translators: %s - WPMU hosted ftp/sftp url. */
					echo wp_kses_post( sprintf( __( 'You can get your credentials <a target="_blank" href="%s">here</a>.', 'snapshot' ), esc_url( Env::get_wpmu_hosted_sftp_url() ) ) );
				}
				?>
			</span>

			<button class="sui-button-icon sui-button-float--left"
				data-modal-slide="snapshot-add-destination-dialog-slide-1">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">
			<!-- Alerts -->
			<div role="alert" id="snapshot-duplicate-ftp-details__test" class="sui-notice sui-notice-red"
				aria-live="assertive" style="display: none;">
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

			<div role="alert" id="error-ftp-destination-incorrect-creds" class="sui-notice sui-notice-red"
				aria-live="assertive" style="display: none;">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
						<p><?php esc_html_e( 'Connection failed. Please double-check username and password. If you run into further issues, you can contact our Support team for help.', 'snapshot' ); ?>
						</p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
						<p><?php echo wp_kses_post( sprintf( __( 'Connection failed. Please double-check username and password. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) ); ?>
						</p>
						<?php } ?>
					</div>
				</div>
			</div>

			<div role="alert" id="error-ftp-destination-incorrect-creds__test" class="sui-notice sui-notice-red"
				aria-live="assertive" style="display: none;">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
						<p><?php echo wp_kses_post( __( 'Test Connection failed. Please double-check <span id="connection_failed_group_text"> username and password </span>. If you run into further issues, you can contact our Support team for help.', 'snapshot' ) ); ?>
						</p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
						<p><?php echo wp_kses_post( sprintf( __( 'Test Connection failed. Please double-check <span id="connection_failed_group_text"> username and password </span>. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), Fail::URL_CONTACT_SUPPORT ) ); ?>
						</p>
						<?php } ?>
					</div>
				</div>

			</div>

			<div role="alert" id="ftp-destination-test__success" class="sui-notice sui-notice-green"
				aria-live="assertive" style="display: none;">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
						<p><?php esc_html_e( 'The testing results were successful. We are able to connect to your destination. You\'re good to proceed with the current settings. Click "Next" to continue.', 'snapshot' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="sui-tabs sui-side-tabs">
				<form id="ftp-connection-details" method="POST">
					<div class="sui-flex justify-content-center">
						<div class="sui-button--group snapshot-ftp-type" data-callback="change_port_number">
							<div class="sui-button--group__item selected">
								<input type="radio" class="sui-hidden" name="tpd_type" value="ftp" checked />
								<?php esc_html_e( 'FTP', 'snapshot' ); ?>
							</div>
							<div class="sui-button--group__item">
								<input type="radio" class="sui-hidden" name="tpd_type" value="sftp" />
								<?php esc_html_e( 'SFTP', 'snapshot' ); ?>
							</div>
						</div>
					</div>

					<div class="sui-row mt-25">
						<div class="sui-col">
							<div class="sui-form-field">
								<label class="sui-label" for="ftp-host" id="destination-ftp-host">
									<?php esc_html_e( 'Host', 'snapshot' ); ?><span><?php echo esc_html( '*' ); ?>
								</label>
								<input class="sui-form-control" name="ftp-host" id="ftp-host" value=""
									aria-describedby="destination-ftp-host" aria-labelledby="error-destination-ftp-host"
									placeholder="<?php esc_attr_e( 'Add host name', 'snapshot' ); ?>">
								<span id="error-destination-ftp-host" class="sui-error-message" style="display: none;"
									role="alert"></span>
							</div>
						</div>

						<div class="sui-col">
							<div class="sui-form-field">
								<label class="sui-label" for="ftp-port" id="destination-ftp-port">
									<?php esc_html_e( 'Port', 'snapshot' ); ?><span><?php echo esc_html( '*' ); ?>
								</label>
								<input class="sui-form-control" name="ftp-port" id="ftp-port" value="21"
									aria-describedby="destination-ftp-port" aria-labelledby="error-destination-ftp-port"
									placeholder="<?php esc_attr_e( 'Port number', 'snapshot' ); ?>">
								<span id="error-destination-ftp-port" class="sui-error-message" style="display: none;"
									role="alert"></span>
							</div>
						</div>
					</div>

					<div class="sui-form-field">
						<label class="sui-label" for="ftp-user" id="destination-ftp-user">
							<?php esc_html_e( 'User', 'snapshot' ); ?><span><?php echo esc_html( '*' ); ?>
						</label>
						<input class="sui-form-control" name="tpd_accesskey" id="ftp-user" value=""
							aria-describedby="destination-ftp-user" aria-labelledby="error-destination-ftp-user"
							placeholder="<?php esc_attr_e( 'FTP username', 'snapshot' ); ?>">
						<span id="error-destination-ftp-user" class="sui-error-message" style="display: none;"
							role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label class="sui-label" for="ftp-password" id="destination-ftp-password">
							<?php esc_html_e( 'Password', 'snapshot' ); ?><span><?php echo esc_html( '*' ); ?>
						</label>
						<div class="sui-with-button sui-with-button-icon">
							<input type="password" class="sui-form-control" name="tpd_secretkey" id="ftp-password"
								value="" aria-describedby="destination-ftp-password"
								aria-labelledby="error-destination-ftp-password"
								placeholder="<?php esc_attr_e( 'Password', 'snapshot' ); ?>">
							<button type="button" class="sui-button-icon">
								<span aria-hidden="true" class="sui-icon-eye"></span>
								<span
									class="sui-password-text sui-screen-reader-text"><?php esc_html_e( 'Show Password', 'snapshot' ); ?></span>
								<span
									class="sui-password-text sui-screen-reader-text sui-hidden"><?php esc_html_e( 'Hide Password', 'snapshot' ); ?></span>
							</button>
						</div>
						<span id="error-destination-ftp-password" class="sui-error-message" style="display: none;"
							role="alert"></span>
					</div>

					<div class="sui-form-field">
						<label class="sui-label" for="ftp-directory" id="destination-ftp-directory">
							<?php esc_html_e( 'Directory Path', 'snapshot' ); ?><span><?php echo esc_html( '*' ); ?>
						</label>
						<input type="text" class="sui-form-control" name="tpd_path" id="ftp-directory" value=""
							placeholder="/home/user/public_html" aria-describedby="destination-ftp-directory"
							aria-labelledby="error-destination-ftp-directory">
						<span id="error-destination-ftp-directory" class="sui-error-message" style="display: none;"
							role="alert"></span>
						<span id="description-ftp-directory" class="sui-description" role="alert">
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
					</div>

					<div class="sui-form-field">
						<label for="ftp-details-limit" id="label-ftp-details-limit" class="sui-label">
							<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?></span>
						</label>

						<input type="number" min="1" id="ftp-details-limit" class="sui-form-control sui-input-sm"
							name="tpd_limit" aria-labelledby="label-ftp-details-limit"
							aria-describedby="error-ftp-details-limit description-ftp-details-limit" value="5" />
						<span id="error-ftp-details-limit" class="sui-error-message" style="display: none;"
							role="alert"></span>
						<span id="description-ftp-details-limit"
							class="sui-description"><?php echo esc_html( 'Set the number of backups you want to store before removing the older ones. It must be greater than 0.' ); ?></span>
					</div>

					<div class="sui-row">
						<div class="sui-col">
							<div class="sui-form-field">
								<label class="sui-label" for="ftp-mode" id="destination-ftp-mode">
									<?php esc_html_e( 'Use Passive Mode', 'snapshot' ); ?>
									<span class="sui-tooltip sui-tooltip-constrained" style="--tooltip-width: 300px;"
										data-tooltip="<?php esc_attr_e( 'In passive mode, data connections are initiated by the client, rather than by the server. It may be needed if the client is behind a firewall. Passive mode is off by default.', 'snapshot' ); ?>">
										<span class="sui-icon-info" aria-hidden="true"></span>
									</span>
								</label>

								<div class="sui-button--group snapshot-ftp--passive__mode">
									<div class="sui-button--group__item">
										<input type="radio" class="sui-hidden" name="ftp-passive-mode" value="on" />
										<?php esc_html_e( 'On', 'snapshot' ); ?>
									</div>
									<div class="sui-button--group__item selected">
										<input type="radio" class="sui-hidden" name="ftp-passive-mode" value="off"
											checked />
										<?php esc_html_e( 'Off', 'snapshot' ); ?>
									</div>
								</div>
							</div>
						</div>
						<div class="sui-col">
							<div class="sui-form-field sui-width-110">
								<label class="sui-label" for="ftp-timeout" id="destination-ftp-timeout">
									<?php esc_html_e( 'Server Timeout', 'snapshot' ); ?>
									<span class="sui-tooltip sui-tooltip-constrained" style="--tooltip-width: 300px;"
										data-tooltip="<?php esc_attr_e( 'The default timeout for FTP connections is 90 seconds. Sometimes this timeout needs to be longer for slower connections to busy servers.', 'snapshot' ); ?>">
										<span class="sui-icon-info" aria-hidden="true"></span>
									</span>
								</label>

								<input type="text" class="sui-form-control" name="ftp-timeout" id="ftp-timeout"
									value="90" aria-describedby="destination-ftp-timeout">
							</div>
						</div>
					</div>
					<input type="hidden" name="tpd_action" value="test_connection_final">
					<input type="hidden" name="tpd_save" value="0">
				</form>
			</div>
		</div>

		<div class="sui-box-footer sui-lg sui-content-separated">
			<div class="sui-flex-child-right">
				<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-1">
					<span class="sui-icon-arrow-left" aria-hidden="true"></span>
					<?php esc_html_e( 'Back', 'snapshot' ); ?>
				</button>
			</div>

			<div class="sui-actions-right">
				<button id="snapshot-test-connection__ftp" class="sui-button sui-button-icon-right sui-button-blue"
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

				<button
					class="sui-button--disabled sui-button sui-button-icon-right snapshot-ftp-destination--next sui-tooltip sui-tooltip-constrained"
					style="--tooltip-width: 220px;"
					data-tooltip="<?php esc_attr_e( 'Please test the connection first using the "Test Connection" button.', 'snapshot' ); ?>"
					data-slide-to="snapshot-add-destination-dialog-slide-3-ftp">
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