<?php // phpcs:ignore
/**
 * First screen of Add Destination modal.
 *
 * @package snapshot
 */

?>
<div class="sui-modal-slide sui-active sui-loaded" id="snapshot-add-destination-dialog-slide-1" data-modal-size="md">
	<div class="sui-box">

		<div class="sui-box-header sui-flatten sui-content-center header-logo-center">

			<div class="sui-box-banner banner-logo-center" role="banner" aria-hidden="true">
				<img
					src="<?php echo esc_attr( $assets->get_asset( 'img/modal-icon-add-destination.png' ) ); ?>"
				/>
			</div>
			<button class="sui-button-icon sui-button-float--right" data-modal-close>
				<span class="sui-icon-close sui-md" aria-hidden="true"></span>
			</button>

			<h3 class="sui-box-title sui-lg"><?php echo esc_html( 'Add destination' ); ?></h3>
			<span class="sui-description" style=" padding-left: 15px; padding-right: 15px; "><?php echo esc_html( 'Select the destination where you want to export a full copy of each Snapshot backup.' ); ?></span>

		</div>

		<div class="sui-box-selectors sui-box-selectors-col-2 snapshot-destination-selectors">
			<ul>
				<li>
					<label for="s3-destination" class="sui-box-selector">
						<input type="radio" name="snapshot-selected-destination-type" id="s3-destination" value="s3" />
						<span>
							<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-aws-small.svg' ) ); ?>" style="margin-right: 10px;" />
							<?php echo esc_html( 'Amazon S3' ); ?>
						</span>
					</label>
				</li>

				<li>
					<label for="gd-destination" class="sui-box-selector">
						<input type="radio" name="snapshot-selected-destination-type" id="gd-destination" value="gd" />
						<span>
							<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-gd-small.svg' ) ); ?>" style="margin-right: 10px;" />
							<?php echo esc_html( 'Google Drive' ); ?>
						</span>
					</label>
				</li>

				<li>
					<label for="dropbox-destination" class="sui-box-selector">
						<input type="radio" name="snapshot-selected-destination-type" id="dropbox-destination" value="dropbox" />
						<span>
							<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-dropbox-small.svg' ) ); ?>" style="margin-right: 10px;" />
							<?php esc_html_e( 'Dropbox', 'snapshot' ); ?>
						</span>
					</label>
				</li>

				<li>
					<label for="ftp-destination" class="sui-box-selector">
						<input type="radio" name="snapshot-selected-destination-type" id="ftp-destination" value="ftp" />
						<span>
							<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-ftp-small.svg' ) ); ?>" style="margin-right: 10px;" />
							<?php esc_html_e( 'FTP/SFTP', 'snapshot' ); ?>
						</span>
					</label>
				</li>

				<li>
					<label for="onedrive-destination" class="sui-box-selector">
						<input type="radio" name="snapshot-selected-destination-type" id="onedrive-destination" value="onedrive" />
						<span>
							<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-onedrive-small.svg' ) ); ?>" style="margin-right: 10px;" />
							<?php esc_html_e( 'OneDrive', 'snapshot' ); ?>
						</span>
					</label>
				</li>

				<li>
					<label for="azure-destination" class="sui-box-selector">
						<input type="radio" name="snapshot-selected-destination-type" id="azure-destination" value="azure" />
						<span>
							<img src="<?php echo esc_attr( $assets->get_asset( 'img/destination-logo-azure-small.svg' ) ); ?>" style="margin-right: 10px;" />
							<?php esc_html_e( 'Azure', 'snapshot' ); ?>
						</span>
					</label>
				</li>
			</ul>
		</div>

		<div class="sui-box-footer sui-content-right sui-flatten">
			<button class="sui-button sui-button-icon-right snapshot-next-destination-screen" disabled data-modal-slide="snapshot-add-destination-dialog-slide-2-s3" data-modalslide="snapshot-add-destination-dialog-slide-2-s3" data-modal-slide-intro="next">
				<?php esc_html_e( 'Next', 'snapshot' ); ?>
				<span class="sui-icon-arrow-right" aria-hidden="true"></span>
			</button>
		</div>

	</div>
</div>