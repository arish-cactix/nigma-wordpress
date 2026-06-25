<?php // phpcs:ignore
/**
 * Third screen of Add Destination modal - GD.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-4-gd" data-modal-size="md">
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
			<?php /* translators: %s - span class */ ?>
			<span class="sui-description"><?php echo wp_kses_post( __( 'Lastly, give the destination a name so you can easily identify it.', 'snapshot' ) ); ?></span>

			<button class="sui-button-icon sui-button-float--left" data-modal-slide="snapshot-add-destination-dialog-slide-3-gd">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">

			<div role="alert" id="snapshot-gd-save-failure" class="sui-notice sui-notice-error" aria-live="assertive" style="display:none;">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php /* translators: %s - Link for support */ ?>
						<p><?php echo esc_html( __( 'We were unable to save the destination, as an error occurred while setting up your account. Please recheck your configurations and your Directory ID to complete the setup.', 'snapshot' ) ); ?></p>

						<button role="button" class="sui-button" data-modal-slide="snapshot-add-destination-dialog-slide-3-gd" style=" padding: 5px 14px; margin-left: 26px; ">
							<?php echo esc_html( __( 'Recheck setup', 'snapshot' ) ); ?>
						</button>

					</div>

				</div>

			</div>

			<div role="alert" id="snapshot-duplicate-gd-save-failure" class="sui-notice sui-notice-error" aria-live="assertive">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different directory. If you run into further issues, you can contact Support for help.', 'snapshot' ); ?></p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?></p>
						<?php } ?>

						<button role="button" class="sui-button" data-modal-slide="snapshot-add-destination-dialog-slide-3-gd" style=" padding: 5px 14px; margin-left: 26px; ">
							<?php echo esc_html( __( 'Recheck setup', 'snapshot' ) ); ?>
						</button>

					</div>

				</div>

			</div>

			<form method="post" id="snapshot-save-gd">
				<input type="hidden" name="tpd_action" value="test_connection_final">
				<input type="hidden" name="tpd_acctoken_gdrive" value="">
				<input type="hidden" name="tpd_retoken_gdrive" value="">
				<input type="hidden" name="tpd_path" value="">
				<input type="hidden" name="tpd_limit" value="">
				<input type="hidden" name="tpd_name" value="">
				<input type="hidden" name="tpd_save" value="1">
				<input type="hidden" name="tpd_type" value="gdrive">
				<input type="hidden" name="tpd_email_gdrive" value="">

				<div class="sui-form-field">
					<label for="gd-save-name" id="label-gd-save-name" class="sui-label">
						<?php echo esc_html( __( 'Destination Name', 'snapshot' ) ); ?><span style="margin-left: 3px; "><?php echo esc_html( '*' ); ?></span>
					</label>

					<input
						value="Google Drive"
						id="gd-save-name"
						name="tpd_name"
						class="sui-form-control"
						aria-labelledby="label-gd-save-name"
					/>

					<span id="error-gd-save-name" class="sui-error-message" style="display: none;" role="alert"></span>
				</div>
			</form>
		</div>

		<div class="sui-box-footer sui-lg sui-content-separated">

			<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-3-gd" >
				<span class="sui-icon-arrow-left" aria-hidden="true"></span>
				<?php esc_html_e( 'Back' ); ?>
			</button>

			<button class="sui-button sui-button-blue" id="snapshot-submit-save-gd" >
				<span class="sui-button-text-default">
					<span class="sui-icon-check" aria-hidden="true"></span>
					<?php esc_html_e( 'Save Destination' ); ?>
				</span>
				<span class="sui-button-text-onload">
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					<?php esc_html_e( 'Loading...' ); ?>
				</span>
			</button>

		</div>

	</div>
</div>