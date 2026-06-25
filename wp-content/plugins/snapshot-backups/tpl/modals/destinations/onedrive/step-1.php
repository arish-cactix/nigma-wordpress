<?php // phpcs:ignore
/**
 * Second screen of Add Destination modal - OneDrive.
 *
 * @package snapshot
 */
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-2-onedrive" data-modal-size="md">
	<div class="sui-box">

		<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

			<figure class="sui-box-logo" aria-hidden="true">
				<img
					src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-onedrive-header.svg' ) ); ?>" />
			</figure>

			<button class="sui-button-icon sui-button-float--right" data-modal-close>
				<span class="sui-icon-close sui-md" aria-hidden="true"></span>
			</button>

			<h3 class="sui-box-title sui-lg"><?php echo esc_html( __( 'Connect OneDrive', 'snapshot' ) ); ?></h3>
			<span class="sui-description">
				<?php esc_html_e( 'Easily connect with OneDrive to authorize Snapshot and store your backups in their directory.', 'snapshot' ); ?>
			</span>

			<button class="sui-button-icon sui-button-float--left"
				data-modal-slide="snapshot-add-destination-dialog-slide-1">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back', 'snapshot' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">

			<div role="alert" id="snapshot-wrong-onedrive-creds" class="sui-notice sui-notice-error"
				aria-live="assertive" style="display: none;">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
						<p><?php esc_html_e( 'It appears the authorization process went wrong. Please try again by clicking the "Authenticate" button and make sure you authorize Snapshot to access your OneDrive. If you run into further issues, you can contact our Support team for help.', 'snapshot' ); ?>
						</p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
						<p><?php echo wp_kses_post( sprintf( __( 'It appears the authorization process went wrong. Please try again by clicking the "Authenticate" button and make sure you authorize Snapshot to access your OneDrive. If you run into further issues, you can <a href="%s" target="_blank">contact our Support</a> team for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?>
						</p>
						<?php } ?>
					</div>
				</div>
			</div>

			<div role="alert" id="snapshot-correct-onedrive-creds" class="sui-notice sui-notice-success"
				aria-live="assertive" style="display: none;">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
						<p><?php echo wp_kses_post( __( 'Snapshot has been successfully authorized in OneDrive.', 'snapshot' ) ); ?>
						</p>
					</div>
				</div>
			</div>

			<div id="snapshot-onedrive-authorization">
				<h4><?php echo esc_html( __( 'OneDrive authorization', 'snapshot' ) ); ?></h4>

				<div class="sui-border-frame">
					<form method="post" id="snapshot-test-onedrive-connection">
						<input type="hidden" id="_wpnonce-snapshot_onedrive_connection"
							name="_wpnonce-snapshot_onedrive_connection" value="">
						<input type="hidden" name="tpd_action" value="generate_tokens">
						<input type="hidden" name="tpd_type" value="onedrive">
						<input type="hidden" name="tpd_auth_code" value="">
						<input type="hidden" name="tpd_save" value="0">
					</form>

					<p class="sui-description">
						<?php echo wp_kses_post( __( 'Connect with OneDrive integration by authenticating it using the button below. Note that you\'ll be taken to the OneDrive website to grant access to Snapshot and then redirected back.', 'snapshot' ) ); ?>
					</p>

					<a type="button" href="<?php echo esc_url( $auth_url ); ?>"
						class="sui-button sui-button-lg snapshot-connect-onedrive">
						<span aria-hidden="true" class="sui-icon-onedrive-connect"></span>
						<?php echo esc_html( __( 'Authenticate', 'snapshot' ) ); ?>
					</a>
				</div>
			</div>

		</div>

		<div class="sui-box-footer sui-flatten sui-lg sui-content-separated">

			<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-1">
				<span class="sui-icon-arrow-left" aria-hidden="true"></span>
				<?php esc_html_e( 'Back', 'snapshot' ); ?>
			</button>

			<button class="sui-button sui-button-icon-right snapshot-next-destination-screen" style="display: none;"
				id="snapshot-submit-onedrive-generate-tokens">
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