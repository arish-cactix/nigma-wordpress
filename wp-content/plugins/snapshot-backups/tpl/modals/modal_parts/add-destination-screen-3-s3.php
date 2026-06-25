<?php // phpcs:ignore
/**
 * Third screen of Add Destination modal - S3.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-3-s3" data-modal-size="md">
	<div class="sui-box">

		<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

			<figure class="sui-box-logo" aria-hidden="true">
				<img
					src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws.png' ) ); ?>"
					srcset="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws@2x.png' ) ); ?> 2x"
				/>
			</figure>

			<button class="sui-button-icon sui-button-float--right" data-modal-close>
				<span class="sui-icon-close sui-md" aria-hidden="true"></span>
			</button>

			<h3 class="sui-box-title sui-lg"><?php echo esc_html( __( 'Connect S3', 'snapshot' ) ); ?></h3>
			<?php /* translators: %s - span class */ ?>
			<span class="sui-description"><?php echo wp_kses_post( __( 'Choose the bucket where you want the backups to be stored.', 'snapshot' ) ); ?></span>

			<button class="sui-button-icon sui-button-float--left" data-modal-slide="snapshot-add-destination-dialog-slide-2-s3">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">

			<div role="alert" id="snapshot-wrong-s3-details" class="sui-notice sui-notice-error" aria-live="assertive" style="display:none;">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php /* translators: %s - Link for support */ ?>
						<p><?php echo esc_html( __( 'The testing results have failed. We were unable to authorize your account and access the bucket. Please check your access credentials and bucket/folder path again.', 'snapshot' ) ); ?></p>

						<button role="button" class="sui-button" data-modal-slide="snapshot-add-destination-dialog-slide-2-s3" style=" padding: 5px 14px; margin-left: 26px; ">
							<?php echo esc_html( __( 'Check credentials', 'snapshot' ) ); ?>
						</button>

					</div>

				</div>

			</div>

			<div role="alert" id="snapshot-correct-s3-details" class="sui-notice sui-notice-success" aria-live="assertive" style="display:none;">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php /* translators: %s - Link for support */ ?>
						<p><?php echo esc_html( __( 'The testing results were successful. Your account has been verified and we successfully accessed the bucket. You’re good to proceed with the current settings. Click "Next" to continue.', 'snapshot' ) ); ?></p>

					</div>

				</div>

			</div>

			<div role="alert" id="snapshot-duplicate-s3-details" class="sui-notice sui-notice-error" aria-live="assertive">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact support for help.', 'snapshot' ); ?></p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different folder or create a new one. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?></p>
						<?php } ?>

					</div>

				</div>

			</div>

			<div role="alert" id="snapshot-duplicate-s3-bucket-details" class="sui-notice sui-notice-error" aria-live="assertive">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different bucket or create a new folder. If you run into further issues, you can contact support for help.', 'snapshot' ); ?></p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'You\'re trying to save a destination that already exists. If you want to create a new destination with the same credentials, please choose a different bucket or create a new folder. If you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), 'https://wpmudev.com/hub2/support#get-support' ) ); ?></p>
						<?php } ?>

					</div>

				</div>

			</div>

			<form method="post" id="snapshot-add-s3-info">
				<input type="hidden" name="tpd_action" value="">
				<input type="hidden" name="tpd_accesskey" value="">
				<input type="hidden" name="tpd_secretkey" value="">
				<input type="hidden" name="tpd_region" value="">
				<input type="hidden" name="tpd_save" value="0">
				<input type="hidden" name="tpd_type" value="">
				<input type="hidden" name="tpd_endpoint" value="">

				<div class="sui-form-field">
					<label for="s3-tpd-bucket" id="label-s3-details-bucket" class="sui-label">
						<?php echo esc_html( __( 'Bucket Name', 'snapshot' ) ); ?><span style="margin-left: 3px;">*
					</label>
					<input
						id="s3-tpd-bucket"
						class="sui-form-control"
						name="tpd_bucket"
						aria-labelledby="label-s3-details-bucket"
						aria-describedby="error-s3-details-bucket description-s3-details-bucket"
						readonly
						value="" />

					<span
						id="error-s3-details-bucket"
						class="sui-error-message"
						style="display: none; text-align:right;" role="alert">
					</span>
				</div>

				<div class="sui-form-field">
					<label for="s3-details-directory" id="label-s3-details-directory" class="sui-label">
						<?php echo esc_html( __( 'Directory Folder Path (Optional)', 'snapshot' ) ); ?>
					</label>

					<input
						placeholder="E.g. /foldername"
						id="s3-details-directory"
						class="sui-form-control"
						name="tpd_directory"
						aria-labelledby="label-s3-details-directory"
						aria-describedby="error-s3-details-directory description-s3-details-directory"
					/>

					<span id="error-s3-details-directory" class="sui-error-message" style="display: none; text-align:right;" role="alert"></span>
					<span id="description-s3-details-directory" class="sui-description"><?php echo wp_kses_post( __( 'You can use the directory path to store your backups in the folder. Examples of correct directory paths are <strong>/folder</strong> or <strong>/folder/subfolder</strong>. If the folder directory doesn’t exist, we will automatically create it. If the directory is provided, backups will be stored inside the bucket folder.', 'snapshot' ) ); ?></span>
				</div>

				<div class="sui-form-field">
					<label for="s3-details-limit" id="label-s3-details-limit" class="sui-label">
						<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?><span style="margin-left: 3px;">*</span>
					</label>

					<input
						type="number"
						min="1"
						id="s3-details-limit"
						class="sui-form-control sui-input-sm"
						name="tpd_limit"
						aria-labelledby="label-s3-details-limit"
						aria-describedby="error-s3-details-limit description-s3-details-limit"
						value="30"
					/>

					<span id="error-s3-details-limit" class="sui-error-message" style="display: none;" role="alert"></span>
					<span id="description-s3-details-limit" class="sui-description"><?php echo esc_html( 'Set the number of exported backups you want to store in the third-party destination before removing the older ones. It must be greater than 0.' ); ?></span>
				</div>
			</form>

		</div>

		<div class="sui-box-footer sui-lg sui-content-separated">
			<div class="sui-flex-child-right">
				<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-2-s3" >
					<span class="sui-icon-arrow-left" aria-hidden="true"></span>
					<?php echo esc_html( 'Back' ); ?>
				</button>
			</div>

			<div class="sui-actions-right">
				<button class="sui-button sui-button-ghost" id="snapshot-test-s3-connection-path" >
					<span class="sui-button-text-default">
						<?php echo esc_html( 'Test Connection' ); ?>
					</span>
					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						<?php echo esc_html( 'Testing...' ); ?>
					</span>
				</button>

				<button class="sui-button sui-button-icon-right snapshot-next-destination-screen" id="snapshot-submit-s3-connection-details" >
				<?php echo esc_html( 'Next' ); ?>
					<span class="sui-icon-arrow-right" aria-hidden="true"></span>
				</button>
			</div>
		</div>

	</div>
</div>