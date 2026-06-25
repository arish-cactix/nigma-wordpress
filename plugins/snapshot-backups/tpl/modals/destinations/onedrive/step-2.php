<?php // phpcs:ignore
/**
 * Third screen of Add Destination modal - OneDrive.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;
?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-3-onedrive" data-modal-size="md">
	<div class="sui-box">

		<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60">

			<figure class="sui-box-logo" aria-hidden="true">
				<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-onedrive-header.svg' ) ); ?>" />
			</figure>

			<button class="sui-button-icon sui-button-float--right" data-modal-close>
				<span class="sui-icon-close sui-md" aria-hidden="true"></span>
			</button>

			<h3 class="sui-box-title sui-lg">
				<?php echo esc_html( __( 'Connect OneDrive', 'snapshot' ) ); ?>
			</h3>
			<span class="sui-description">
				<?php echo wp_kses_post( __( 'Create a OneDrive directory to store your backups in.', 'snapshot' ) ); ?>
			</span>

			<button class="sui-button-icon sui-button-float--left" data-modal-slide="snapshot-add-destination-dialog-slide-2-onedrive">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">

			<div role="alert" id="snapshot-wrong-onedrive-details" class="sui-notice sui-notice-error" aria-live="assertive">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory and if you run into further issues, you can contact our Support team for help.', 'snapshot' ); ?></p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory and if you run into further issues, you can contact our <a href="%s" target="_blank">Support team</a> for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?></p>
						<?php } ?>

					</div>

				</div>

			</div>

			<div role="alert" id="snapshot-duplicate-onedrive-details" class="sui-notice sui-notice-error" aria-live="assertive">
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

			<div role="alert" id="snapshot-correct-onedrive-details" class="sui-notice sui-notice-success" aria-live="assertive">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<p>
							<?php esc_html_e( 'The testing results were successful. Your account has been verified and we successfully accessed the directory. You\'re good to proceed with the current settings. Click "Next" to continue.', 'snapshot' ); ?>
						</p>
					</div>

				</div>

			</div>

			<form method="post" id="snapshot-add-onedrive-info">
				<input type="hidden" name="tpd_action" value="test_connection_final">
				<input type="hidden" name="tpd_retoken_onedrive" value="">
				<input type="hidden" name="tpd_acctoken_onedrive" value="">
				<input type="hidden" name="tpd_email_onedrive" value="">
				<input type="hidden" name="tpd_save" value="0">
				<input type="hidden" name="tpd_type" value="onedrive">

				<span class="sui-label"><?php esc_html_e( 'Connected OneDrive Account', 'snapshot' ); ?></span>
				<table class="sui-table" style=" margin-top: 0px; ">
					<tbody>
						<tr class="snapshot-configured-account snapshot-configured-account--onedrive">
							<td class="snapshot-configured-account-email snapshot-configured-account--onedrive__email"></td>
						</tr>
					</tbody>
				</table>

				<div class="sui-form-field">
					<label for="onedrive-path" id="label-onedrive-path" class="sui-label">
						<?php esc_html_e( 'Directory path', 'snapshot' ); ?><span> (<?php echo esc_html__( 'Optional', 'snapshot' ); ?></span>)
					</label>

					<input
						type="text"
						placeholder=""
						name="tpd_path"
						class="sui-form-control"
						aria-labelledby="label-onedrive-path"
						aria-describedby="error-onedrive-path description-onedrive-path-details"
						>

						<span id="error-onedrive-path" class="sui-error-message" role="alert" style="display: none; text-align: right;"></span>
						<span id="description-onedrive-path-details" class="sui-description">
							<?php echo wp_kses_post( __( 'Enter the directory path to where you want to store your backups. Examples of correct directory path are <strong>folder</strong> or <strong>folder/sub_folder</strong>. If you leave this field empty, Snapshot will create a new directory to store the backups.', 'snapshot' ) ); ?>
						</span>
				</div>

				<div class="sui-form-field create-directory--toggle sui-hidden">
					<label for="create-onedrive-dir" class="sui-checkbox sui-checkbox-stacked">
						<input
							type="checkbox"
							id="create-onedrive-dir"
							aria-labelledby="create-onedrive-dir-label"
							name="onedrive_dir_creation_flag"
							value="1"
						/>
						<span aria-hidden="true"></span>
						<span id="create-onedrive-dir-label"><?php esc_html_e( 'Let Snapshot create the above directory path if it doesn\'t exist.', 'snapshot' ); ?></span>
						<span id="error-onedrive-crate-directory" class="sui-error-message" aria-hidden="true" style="display: none; text-align: right;"></span>
					</label>
				</div>

				<div class="sui-form-field">
					<label for="onedrive-details-limit" id="label-onedrive-details-limit" class="sui-label">
						<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?></span>
					</label>

					<input
						type="number"
						min="1"
						id="onedrive-details-limit"
						class="sui-form-control sui-input-sm"
						name="tpd_limit"
						aria-labelledby="label-onedrive-details-limit"
						aria-describedby="error-onedrive-details-limit description-onedrive-details-limit"
						value="30"
					/>

					<span id="error-onedrive-details-limit" class="sui-error-message" style="display: none;" role="alert"></span>
					<span id="description-onedrive-details-limit" class="sui-description"><?php echo esc_html( 'Set the number of backups you want to store before removing the older ones. It must be greater than 0.' ); ?></span>
				</div>
			</form>

		</div>

		<div class="sui-box-footer sui-lg sui-content-separated">
			<div class="sui-flex-child-right">
				<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-2-onedrive" >
					<span class="sui-icon-arrow-left" aria-hidden="true"></span>
					<?php esc_html_e( 'Back', 'snapshot' ); ?>
				</button>
			</div>

			<div class="sui-actions-right">
				<button class="sui-button sui-button-ghost" id="snapshot-test-onedrive-connection-path" >
					<span class="sui-button-text-default">
						<?php esc_html_e( 'Test Connection', 'snapshot' ); ?>
					</span>
					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						<?php esc_html_e( 'Testing...', 'snapshot' ); ?>
					</span>
				</button>

				<button class="sui-button sui-button-icon-right snapshot-next-destination-screen" id="snapshot-submit-onedrive-connection-details">
					<?php esc_html_e( 'Next', 'snapshot' ); ?>
					<span class="sui-icon-arrow-right" aria-hidden="true"></span>
				</button>
			</div>
		</div>

	</div>
</div>