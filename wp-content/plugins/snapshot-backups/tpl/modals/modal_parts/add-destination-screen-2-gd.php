<?php // phpcs:ignore
/**
 * Second screen of Add Destination modal - Google Drive.
 *
 * @package snapshot
 */
use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-2-gd" data-modal-size="md">
	<div class="sui-box">

		<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

			<figure class="sui-box-logo" aria-hidden="true">
				<img
					src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-gd.png' ) ); ?>"
					srcset="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-gd.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/header-logo-gd@2x.png' ) ); ?> 2x"
				/>
			</figure>

			<button class="sui-button-icon sui-button-float--right" data-modal-close>
				<span class="sui-icon-close sui-md" aria-hidden="true"></span>
			</button>

			<h3 class="sui-box-title sui-lg"><?php echo esc_html( __( 'Connect Google Drive', 'snapshot' ) ); ?></h3>
			<?php /* translators: %s - WPMU DEV link */ ?>
			<span class="sui-description"><?php echo wp_kses_post( __( 'Easily connect with Google to authorize Snapshot for Google Drive and store your backups in their directory.', 'snapshot' ) ); ?></span>

			<button class="sui-button-icon sui-button-float--left" data-modal-slide="snapshot-add-destination-dialog-slide-1">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">

			<div role="alert" id="snapshot-wrong-gd-creds" class="sui-notice sui-notice-error" aria-live="assertive" style="display:none;">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'It appears the authorization process went wrong. Please try again by clicking the "Connect with Google" button and make sure you authorize Snapshot to access your Google Drive. If you run into further issues, you can contact support for help.', 'snapshot' ); ?></p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'It appears the authorization process went wrong. Please try again by clicking the "Connect with Google" button and make sure you authorize Snapshot to access your Google Drive. If you run into further issues, you can <a href="%s" target="_blank">contact our Support</a> team for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?></p>
						<?php } ?>

					</div>

				</div>

			</div>

			<div role="alert" id="snapshot-correct-gd-creds" class="sui-notice sui-notice-success" aria-live="assertive" style="display:none;">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php /* translators: %s - Link for support */ ?>
						<p><?php echo wp_kses_post( __( 'Snapshot has been successfully authorized in Google Drive.', 'snapshot' ) ); ?></p>

					</div>

				</div>

			</div>

			<div id="snapshot-gd-authorization">
				<h4><?php echo esc_html( __( 'Google Drive authorization', 'snapshot' ) ); ?></h4>

				<div class="sui-border-frame">

					<form method="post" id="snapshot-test-gd-connection">
						<input type="hidden" id="_wpnonce-snapshot_gd_connection" name="_wpnonce-snapshot_gd_connection" value="">
						<input type="hidden" name="tpd_action" value="generate_tokens">
						<input type="hidden" name="tpd_type" value="gdrive">
						<input type="hidden" name="tpd_auth_code" value="">
						<input type="hidden" name="tpd_save" value="0">
						<input type="hidden" name="tpd_id" value="">
						<input type="hidden" name="tpd_reauth" value="">
					</form>

					<?php /* translators: %s - Privacy Policy link */ ?>
					<p class="sui-description"><?php echo wp_kses_post( sprintf( __( 'Connect with Google to authorize Snapshot to access your Google Drive account. Please read the <a href="%s" target="_blank">privacy policy</a> concerning the use of our Google Drive authorisation.', 'snapshot' ), 'https://wpmudev.com/docs/privacy/our-plugins/#snapshot-privacy-policy' ) ); ?></p>

					<a type="button" href="<?php echo esc_url( $auth_url ); ?>" class="sui-button sui-button-lg snapshot-connect-google"><span aria-hidden="true" class="sui-icon-google-connect"></span> <?php echo esc_html( __( 'Connect with Google', 'snapshot' ) ); ?> </a>

				</div>
			</div>

		</div>

		<div class="sui-box-footer sui-flatten sui-lg sui-content-separated">

			<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-1" >
				<span class="sui-icon-arrow-left" aria-hidden="true"></span>
				<?php esc_html_e( 'Back' ); ?>
			</button>

			<button class="sui-button sui-button-icon-right snapshot-next-destination-screen" style="display:none;" id="snapshot-submit-gd-generate-tokens">
				<span class="sui-button-text-default">
					<?php esc_html_e( 'Next' ); ?>
					<span class="sui-icon-arrow-right" aria-hidden="true"></span>
				</span>

				<span class="sui-button-text-onload">
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					<?php esc_html_e( 'Connecting...' ); ?>
				</span>
			</button>
		</div>

	</div>
</div>