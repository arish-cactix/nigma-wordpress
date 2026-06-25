<?php // phpcs:ignore
/**
 * Third screen of Add Destination modal - Google Drive.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-3-gd" data-modal-size="md">
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
			<span class="sui-description"><?php echo wp_kses_post( __( 'Create a Google Drive folder to store your backups in.', 'snapshot' ) ); ?></span>

			<button class="sui-button-icon sui-button-float--left" data-modal-slide="snapshot-add-destination-dialog-slide-2-gd">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">

			<div role="alert" id="snapshot-wrong-gd-details" class="sui-notice sui-notice-error" aria-live="assertive" style="display:none;">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
							<p><?php esc_html_e( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory ID and if you run into further issues, you can contact support for help.', 'snapshot' ); ?></p>
						<?php } else { ?>
							<?php /* translators: %s - Link for support */ ?>
							<p><?php echo wp_kses_post( sprintf( __( 'The testing results have failed. We were unable to access the directory. Please double-check your Directory ID and if you run into further issues, you can <a href="%s" target="_blank">contact our Support team</a> for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?></p>
						<?php } ?>

					</div>

				</div>

			</div>

			<div role="alert" id="snapshot-duplicate-gd-details" class="sui-notice sui-notice-error" aria-live="assertive">

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

			<div role="alert" id="snapshot-correct-gd-details" class="sui-notice sui-notice-success" aria-live="assertive" style="display:none;">

				<div class="sui-notice-content">

					<div class="sui-notice-message">

						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

						<?php /* translators: %s - Link for support */ ?>
						<p><?php echo esc_html( __( 'The testing results were successful. Your account has been verified and we successfully accessed the folder. You’re good to proceed with the current settings. Click “Next” to continue.', 'snapshot' ) ); ?></p>

					</div>

				</div>

			</div>

			<form method="post" id="snapshot-add-gd-info">
				<input type="hidden" name="tpd_action" value="test_connection_final">
				<input type="hidden" name="tpd_retoken_gdrive" value="">
				<input type="hidden" name="tpd_acctoken_gdrive" value="">
				<input type="hidden" name="tpd_save" value="0">
				<input type="hidden" name="tpd_type" value="gdrive">
				<input type="hidden" name="tpd_email_gdrive" value="">
				<input type="hidden" name="tpd_id" value="">
				<input type="hidden" name="tpd_reauth" value="">

				<span class="sui-label"><?php echo esc_html( __( 'Connected Google Drive Account', 'snapshot' ) ); ?></span>
				<table class="sui-table" style=" margin-top: 0px; ">
					<tbody>
						<tr class="snapshot-configured-gd-account">
							<td class="snapshot-configured-gd-account-email"></td>
						</tr>
					</tbody>
				</table>

				<div class="sui-form-field">
					<label for="gd-details-directory" id="label-gd-details-directory" class="sui-label">
						<?php echo esc_html( __( 'Directory ID', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?></span>
					</label>

					<input
						placeholder="Place Directory ID here"
						id="gd-details-directory"
						class="sui-form-control"
						name="tpd_path"
						aria-labelledby="label-gd-details-directory"
						aria-describedby="error-gd-details-directory description-gd-details-directory"
					/>

					<span id="error-gd-details-directory" class="sui-error-message" style="display: none; text-align:right;" role="alert"></span>
					<span id="description-gd-details-directory" class="sui-description"></span>
				</div>

				<div class="sui-form-field">
					<label for="gd-details-limit" id="label-gd-details-limit" class="sui-label">
						<?php echo esc_html( __( 'Backup Storage Limit', 'snapshot' ) ); ?><span><?php echo esc_html( '*' ); ?></span>
					</label>

					<input
						type="number"
						min="1"
						id="gd-details-limit"
						class="sui-form-control sui-input-sm"
						name="tpd_limit"
						aria-labelledby="label-gd-details-limit"
						aria-describedby="error-gd-details-limit description-gd-details-limit"
						value="30"
					/>

					<span id="error-gd-details-limit" class="sui-error-message" style="display: none;" role="alert"></span>
					<span id="description-gd-details-limit" class="sui-description"><?php echo esc_html( 'Set the number of backups you want to store before removing the older ones. It must be greater than 0.' ); ?></span>
				</div>
			</form>

		</div>

		<?php
		$this->render(
			'modals/modal_parts/gd-instructions-accordion',
			array()
		);
		?>

		<div class="sui-box-footer sui-lg sui-content-separated">
			<div class="sui-flex-child-right">
				<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-2-gd" >
					<span class="sui-icon-arrow-left" aria-hidden="true"></span>
					<?php echo esc_html( 'Back' ); ?>
				</button>
			</div>

			<div class="sui-actions-right">
				<button class="sui-button sui-button-ghost" id="snapshot-test-gd-connection-path" >
					<span class="sui-button-text-default">
						<?php echo esc_html( 'Test Connection' ); ?>
					</span>
					<span class="sui-button-text-onload">
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
						<?php echo esc_html( 'Testing...' ); ?>
					</span>
				</button>

				<button class="sui-button sui-button-icon-right snapshot-next-destination-screen" id="snapshot-submit-gd-connection-details" >
				<?php echo esc_html( 'Next' ); ?>
					<span class="sui-icon-arrow-right" aria-hidden="true"></span>
				</button>
			</div>
		</div>

	</div>
</div>