<?php // phpcs:ignore
/**
 * Final screen of Add Destination modal - FTP.
 *
 * @package snapshot
 */

/**
 * Assets helper instance
 *
 * @var $assets \WPMUDEV\Snapshot4\Helpers\Assets
 */

use WPMUDEV\Snapshot4\Helper\Settings;
?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-3-ftp" data-modal-size="md">
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
				<?php esc_html_e( 'Lastly, give your FTP destination a name so you can easily identify it.', 'snapshot' ); ?>
			</span>

			<button class="sui-button-icon sui-button-float--left snapshot-slide-modal-hide-notice"
				data-prev="snapshot-add-destination-dialog-slide-2-ftp" data-parent=".sui-modal-slide">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">
			<!-- Alerts -->
			<div role="alert" id="error-ftp-destination-exists" class="sui-notice sui-notice-red" aria-live="assertive"
				style="display: none;">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
						<p>
							<?php esc_html_e( 'We were unable to save the destination, as an error occurred while setting up your account. Please recheck your configurations to complete the setup.', 'snapshot' ); ?>
						</p>
						<div>
							<button type="button" class="sui-button snapshot-slide-modal-hide-notice"
								data-prev="snapshot-add-destination-dialog-slide-2-ftp" data-parent=".sui-modal-slide">
								<?php esc_html_e( 'Recheck Setup', 'snapshot' ); ?>
							</button>
						</div>
					</div>

				</div>
			</div>

			<div role="alert" id="snapshot-add-duplicate-ftp-details" class="sui-notice sui-notice-error"
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
						<div>
							<button type="button" class="sui-button snapshot-slide-modal-hide-notice"
								data-prev="snapshot-add-destination-dialog-slide-2-ftp" data-parent=".sui-modal-slide">
								<?php esc_html_e( 'Recheck Setup', 'snapshot' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Form -->
			<form method="post" id="snapshot-ftp-destination-form-final">
				<div class="sui-form-field">
					<label for="ftp-name" id="ftp-destination-name" class="sui-label">
						<?php esc_html_e( 'Destination Name', 'snapshot' ); ?>
					</label>
					<input class="sui-form-control" name="tpd_name" id="ftp-name" aria-labelledby="ftp-name"
						aria-describedby="error-ftp-destination-name" placeholder="FTP Backups">
					<span id="error-ftp-destination-name" class="sui-error-message"
						style="display: none; text-align: right;"></span>
				</div>
				<input name="tpd_accesskey" type="hidden">
				<input name="tpd_secretkey" type="hidden">
				<input name="tpd_type" type="hidden">
				<input name="tpd_path" type="hidden">
				<input name="tpd_limit" type="hidden">
				<input name="ftp-host" type="hidden">
				<input name="ftp-port" type="hidden">
				<input name="ftp-timeout" type="hidden">
				<input name="ftp-passive-mode" type="hidden">
				<input name="tpd_action" value="test_connection_final" type="hidden">
				<input name="tpd_save" value="0" type="hidden">
			</form>
		</div>

		<div class="sui-box-footer sui-lg sui-content-separated">
			<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-2-ftp">
				<span class="sui-icon-arrow-left" aria-hidden="true"></span>
				<?php esc_html_e( 'Back', 'snapshot' ); ?>
			</button>

			<button class="sui-button sui-button-blue snapshot-ftp-destination--save"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'snapshot_ftp_connection' ) ); ?>">
				<span class="sui-button-text-default">
					<?php esc_html_e( 'Save Destination', 'snapshot' ); ?>
				</span>

				<span class="sui-button-text-onload">
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					<?php esc_html_e( 'Loading...', 'snapshot' ); ?>
				</span>
			</button>

		</div>

	</div>
</div>