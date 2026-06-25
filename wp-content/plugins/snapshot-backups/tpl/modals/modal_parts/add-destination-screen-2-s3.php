<?php // phpcs:ignore
/**
 * Second screen of Add Destination modal - S3.
 *
 * @package snapshot
 */

use WPMUDEV\Snapshot4\Task;
use WPMUDEV\Snapshot4\Helper\Settings;

?>
<div class="sui-modal-slide sui-loaded" id="snapshot-add-destination-dialog-slide-2-s3" data-modal-size="md">
	<div class="sui-box">

		<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--60 sui-spacing-bottom--30">

			<figure class="sui-box-logo" aria-hidden="true">
				<img src="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws.png' ) ); ?>"
					srcset="<?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws.png' ) ); ?> 1x, <?php echo esc_attr( $assets->get_asset( 'img/header-logo-aws@2x.png' ) ); ?> 2x" />
			</figure>

			<button class="sui-button-icon sui-button-float--right" data-modal-close>
				<span class="sui-icon-close sui-md" aria-hidden="true"></span>
			</button>

			<h3 class="sui-box-title sui-lg"><?php echo esc_html( __( 'Connect S3', 'snapshot' ) ); ?></h3>

			<span class="sui-description">
			<?php
				echo Settings::get_branding_hide_doc_link()
				? esc_html( __( 'Connect with Amazon S3 and store your backups in their directory, or choose one of the available S3 Compatible providers and store the backups there.', 'snapshot' ) )
				: wp_kses_post(
					sprintf(
						/* translators: %s - WPMU DEV link */
						__( 'Connect with Amazon S3 and store your backups in their directory, or choose one of the available <a href="%s" target="_blank">S3 Compatible providers</a> and store the backups there.', 'snapshot' ),
						'https://wpmudev.com/docs/wpmu-dev-plugins/snapshot-4-0/#s3-compatible-storage'
					)
				);
				?>
			</span>

			<button class="sui-button-icon sui-button-float--left"
				data-modal-slide="snapshot-add-destination-dialog-slide-1">
				<span class="sui-icon-chevron-left sui-md" aria-hidden="true"></span>
				<span class="sui-screen-reader-text"><?php esc_html_e( 'Back', 'snapshot' ); ?></span>
			</button>

		</div>

		<div class="sui-box-body">

			<div class="sui-side-tabs sui-tabs sui-tabs-flushed snapshot-s3-selection">
				<div data-tabs>
					<div class="active snapshot-aws-tab"><?php esc_html_e( 'Amazon S3', 'snapshot' ); ?></div>
					<div class="snapshot-s3-tab"><?php esc_html_e( 'S3 Compatible Storage', 'snapshot' ); ?></div>
				</div>

				<div data-panes="s3">
					<div class="sui-tab-boxed snapshot-destination-formbox snapshot-amazon-s3-tab active">
						<div class="box-content snapshot-amazon-s3">

							<span class="sui-description" data-type='aws'>
							<?php
								echo wp_kses_post(
									sprintf(
										/* translators: %s - Class name to expand instructions */
										__( 'Unsure how to get your Amazon S3 credentials? <span class="%s">Follow the instructions</span> below.', 'snapshot' ),
										'snapshot-expand-instructions-link'
									)
								);
								?>
							</span>

							<div role="alert" id="snapshot-wrong-s3-creds" class="sui-notice sui-notice-error"
								aria-live="assertive" style="display:none;">

								<div class="sui-notice-content">

									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
										<p><?php esc_html_e( 'It appears the authorization credentials you used were invalid. Please enter your credentials again or follow the instructions below to find them. If you run into further issues, you can contact support for help.', 'snapshot' ); ?>
										</p>
										<?php } else { ?>
											<?php /* translators: %s - Link for support */ ?>
										<p><?php echo wp_kses_post( sprintf( __( 'It appears the authorization credentials you used were invalid. Please enter your credentials again or follow the instructions below to find them. If you run into further issues, you can <a href="%s" target="_blank">contact our Support</a> team for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?>
										</p>
										<?php } ?>

									</div>

								</div>
							</div>

							<div role="alert" id="snapshot-region-no-buckets" class="sui-notice sui-notice-warning"
								aria-live="assertive" style="display:none;">

								<div class="sui-notice-content">

									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php /* translators: %1$s - Name of selected region, %2$s - Link for AWS console */ ?>
										<p><?php echo wp_kses_post( sprintf( __( 'You don\'t have an available bucket in the <strong class="snapshot-selected-region-no-buckets">%1$s</strong> region. In order to continue, you will need to <a href="%2$s" target="_blank" >create a bucket</a> with that region, or choose another region where you have available buckets.', 'snapshot' ), 'selected_region', 'https://console.aws.amazon.com/s3/' ) ); ?>
										</p>

									</div>

								</div>

							</div>

							<form method="post" id="snapshot-test-s3-connection">
								<input type="hidden" name="tpd_action" value="load_buckets">
								<input type="hidden" name="tpd_type" value="aws">

								<div class="sui-form-field snapshot-select2-regions">
									<label for="s3-connection-access-key-id" id="label-s3-connection-access-key-id"
										class="sui-label">
										<span
											class="s3-connection-access-key-id-label"><?php echo esc_html( __( 'AWS Access Key ID', 'snapshot' ) ); ?></span><span
											style="margin-left: 3px; ">*</span>
									</label>

									<input
										placeholder="<?php echo esc_attr__( 'Place Access Key ID here', 'snapshot' ); ?>"
										id="s3-connection-access-key-id" class="sui-form-control" name="tpd_accesskey"
										aria-labelledby="label-s3-connection-access-key-id" />
									<span id="error-s3-connection-access-key-id" class="sui-error-message"
										style="display: none; text-align: right;" role="alert"></span>

								</div>

								<div class="sui-form-field">
									<label for="s3-connection-secret-access-key"
										id="label-s3-connection-secret-access-key" class="sui-label">
										<span
											class="s3-connection-secret-access-key-label"><?php echo esc_html( __( 'AWS Secret Access Key', 'snapshot' ) ); ?></span><span
											style=" margin-left: 3px; ">*</span>
									</label>

									<input
										placeholder="<?php echo esc_attr__( 'Place Secret Access Key here', 'snapshot' ); ?>"
										id="s3-connection-secret-access-key" class="sui-form-control"
										name="tpd_secretkey" aria-labelledby="label-s3-connection-secret-access-key" />
									<span id="error-s3-connection-secret-access-key" class="sui-error-message"
										style="display: none; text-align: right;" role="alert"></span>
								</div>

								<div class="sui-form-field">
									<label for="s3-connection-bucket-name"
										id="label-s3-connection-bucket-name" class="sui-label">
										<span class="s3-connection-bucket-name-label">
											<?php echo esc_html( __( 'Bucket name', 'snapshot' ) ); ?>
										</span>
										<span style=" margin-left: 3px; ">*</span>
									</label>

									<input
										placeholder="<?php echo esc_attr__( 'Enter bucket name', 'snapshot' ); ?>"
										id="s3-connection-bucket-name" class="sui-form-control"
										name="tpd_bucket" aria-labelledby="label-s3-connection-bucket-name" />
									<span id="error-s3-connection-bucket-name" class="sui-error-message"
										style="display: none; text-align: right;" role="alert"></span>
								</div>

								<div class="sui-form-field">
									<label for="s3-connection-region" id="label-s3-connection-region" class="sui-label">
										<span
											class="s3-connection-region-label"><?php echo esc_html( __( 'AWS Region', 'snapshot' ) ); ?></span><span
											style="margin-left: 3px; ">*</span>
									</label>

									<select id="s3-connection-region" class="sui-select"
										aria-labelledby="label-s3-connection-region" name="tpd_region">

										<option></option>
										<option value="us-east-2">
											<?php echo esc_html( __( 'US East (Ohio)', 'snapshot' ) ); ?></option>
										<option value="us-east-1">
											<?php echo esc_html( __( 'US East (N. Virginia)', 'snapshot' ) ); ?>
										</option>
										<option value="us-west-1">
											<?php echo esc_html( __( 'US West (N. California)', 'snapshot' ) ); ?>
										</option>
										<option value="us-west-2">
											<?php echo esc_html( __( 'US West (Oregon)', 'snapshot' ) ); ?></option>
										<option value="af-south-1">
											<?php echo esc_html( __( 'Africa (Cape Town)', 'snapshot' ) ); ?></option>
										<option value="ap-east-1">
											<?php echo esc_html( __( 'Asia Pacific (Hong Kong)', 'snapshot' ) ); ?>
										</option>
										<option value="ap-south-1">
											<?php echo esc_html( __( 'Asia Pacific (Mumbai)', 'snapshot' ) ); ?>
										</option>
										<option value="ap-northeast-2">
											<?php echo esc_html( __( 'Asia Pacific (Seoul)', 'snapshot' ) ); ?></option>
										<option value="ap-southeast-1">
											<?php echo esc_html( __( 'Asia Pacific (Singapore)', 'snapshot' ) ); ?>
										</option>
										<option value="ap-southeast-2">
											<?php echo esc_html( __( 'Asia Pacific (Sydney)', 'snapshot' ) ); ?>
										</option>
										<option value="ap-northeast-1">
											<?php echo esc_html( __( 'Asia Pacific (Tokyo)', 'snapshot' ) ); ?></option>
										<option value="ca-central-1">
											<?php echo esc_html( __( 'Canada (Central)', 'snapshot' ) ); ?></option>
										<option value="cn-northwest-1">
											<?php echo esc_html( __( 'China (Ningxia)', 'snapshot' ) ); ?></option>
										<option value="eu-central-1">
											<?php echo esc_html( __( 'Europe (Frankfurt)', 'snapshot' ) ); ?></option>
										<option value="eu-west-1">
											<?php echo esc_html( __( 'Europe (Ireland)', 'snapshot' ) ); ?></option>
										<option value="eu-west-2">
											<?php echo esc_html( __( 'Europe (London)', 'snapshot' ) ); ?></option>
										<option value="eu-south-1">
											<?php echo esc_html( __( 'Europe (Milan)', 'snapshot' ) ); ?></option>
										<option value="eu-west-3">
											<?php echo esc_html( __( 'Europe (Paris)', 'snapshot' ) ); ?></option>
										<option value="eu-north-1">
											<?php echo esc_html( __( 'Europe (Stockholm)', 'snapshot' ) ); ?></option>
										<option value="sa-east-1">
											<?php echo esc_html( __( 'South America (Sao Paulo)', 'snapshot' ) ); ?>
										</option>
										<option value="me-south-1">
											<?php echo esc_html( __( 'Middle East (Bahrain)', 'snapshot' ) ); ?>
										</option>

									</select>
									<span id="error-s3-connection-region" class="sui-error-message"
										style="display: none;  text-align: right;" role="alert"></span>

								</div>
							</form>

						</div>

						<?php
						$this->render(
							'modals/modal_parts/aws-instructions-accordion',
							array()
						);
						?>

					</div>

					<div class="sui-tab-boxed snapshot-destination-formbox snapshot-s3-compatible-tab">
						<div class="box-content snapshot-s3-compatible">

							<div role="alert" id="snapshot-wrong-s3-compatible-creds"
								class="sui-notice sui-notice-error" aria-live="assertive" style="display:none;">

								<div class="sui-notice-content">

									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php if ( Settings::get_branding_hide_doc_link() ) { ?>
										<p><?php esc_html_e( 'It appears the authorization credentials you used were invalid. Follow the instructions below for guidance and add the credentials again. If you run into further issues, you can contact support for help. ', 'snapshot' ); ?>
										</p>
										<?php } else { ?>
											<?php /* translators: %s - Link for support */ ?>
										<p><?php echo wp_kses_post( sprintf( __( 'It appears the authorization credentials you used were invalid. Follow the instructions below for guidance and add the credentials again. If you run into further issues, you can <a href="%s" target="_blank">contact our Support</a> team for help.', 'snapshot' ), Task\Backup\Fail::URL_CONTACT_SUPPORT ) ); ?>
										</p>
										<?php } ?>

									</div>

								</div>

							</div>

							<div role="alert" id="snapshot-region-no-buckets-s3-compatible"
								class="sui-notice sui-notice-warning" aria-live="assertive" style="display:none;">

								<div class="sui-notice-content">

									<div class="sui-notice-message">

										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>

										<?php /* translators: %1$s - Name of selected region, %2$s - Link for AWS console */ ?>
										<p><?php echo wp_kses_post( sprintf( __( 'You don\'t have an available bucket in the <strong class="snapshot-selected-region-no-buckets">%1$s</strong> region. In order to continue, you will need to <a href="%2$s" target="_blank" class="snapshot-s3-compatible-login-link" >create a bucket</a> with that region, or choose another region where you have available buckets.', 'snapshot' ), 'selected_region', '#' ) ); ?>
										</p>

									</div>

								</div>

							</div>

							<form method="post" id="snapshot-test-s3-compatible-connection">
								<input type="hidden" name="tpd_action" value="load_buckets">

								<div class="sui-form-field s3-compatible-providers">
									<label for="s3-compatible-connection-provider"
										id="label-s3-compatible-connection-provider" class="sui-label">
										<?php echo esc_html( __( 'Choose provider', 'snapshot' ) ); ?><span
											style="margin-left: 3px; ">*</span>
										<a href="<?php echo Settings::get_branding_hide_doc_link() ? '#' : 'https://wpmudev.com/docs/wpmu-dev-plugins/snapshot-4-0/#s3-compatible-storage'; ?>"
											target="_blank"
											class="sui-label-link"><?php echo esc_html( __( 'Guide to find the credentials', 'snapshot' ) ); ?></a>
									</label>

									<select id="s3-compatible-connection-provider" name="tpd_type" class="sui-select"
										aria-labelledby="label-s3-connection-provider">

										<option></option>
										<option value="backblaze">
											<?php echo esc_html( __( 'Backblaze', 'snapshot' ) ); ?></option>
										<option value="googlecloud">
											<?php echo esc_html( __( 'Google Cloud', 'snapshot' ) ); ?></option>
										<option value="digitalocean">
											<?php echo esc_html( __( 'DigitalOcean Spaces', 'snapshot' ) ); ?></option>
										<option value="wasabi"><?php echo esc_html( __( 'Wasabi', 'snapshot' ) ); ?>
										</option>
										<option value="s3_other"><?php echo esc_html( __( 'Other', 'snapshot' ) ); ?>
										</option>

									</select>
									<span id="error-s3-compatible-connection-provider" class="sui-error-message"
										style="display: none;  text-align: right;" role="alert"></span>

								</div>

								<div class="s3-compatible-creds" style="display: none;">
									<div class="sui-form-field s3-compatible-bucket-name">
										<label for="s3-compatible-connection-bucket-name"
											id="label-s3-compatible-connection-bucket-name" class="sui-label">
											<span
												class="s3-compatible-connection-bucket-name-label"><?php echo esc_html( __( 'Bucket Name', 'snapshot' ) ); ?></span><span
												style=" margin-left: 3px; ">*</span>
										</label>

										<div class="snapshot-s3-compatible-key">
											<input
												placeholder="<?php echo esc_attr__( 'Place Bucket name here', 'snapshot' ); ?>"
												id="s3-compatible-connection-bucket-name" class="sui-form-control"
												name="tpd_bucketname"
												aria-labelledby="label-s3-compatible-connection-bucket-name" />
											<span class="sui-icon-profile-male" aria-hidden="true"></span>
										</div>
										<span id="error-s3-compatible-connection-bucket-name" class="sui-error-message"
											style="display: none; text-align: right;" role="alert"></span>
									</div>
									<div class="sui-form-field s3-compatible-accessKey">
										<label for="s3-compatible-connection-access-key-id"
											id="label-s3-compatible-connection-access-key-id" class="sui-label">
											<span
												class="s3-compatible-connection-access-key-id-label"><?php echo esc_html( __( 'key ID', 'snapshot' ) ); ?></span><span
												style=" margin-left: 3px; ">*</span>
										</label>

										<div class="snapshot-s3-compatible-key">
											<input
												placeholder="<?php echo esc_attr__( 'Place keyID here', 'snapshot' ); ?>"
												id="s3-compatible-connection-access-key-id" class="sui-form-control"
												name="tpd_accesskey"
												aria-labelledby="label-s3-compatible-connection-access-key-id" />
											<span class="sui-icon-profile-male" aria-hidden="true"></span>
										</div>
										<span id="error-s3-compatible-connection-access-key-id"
											class="sui-error-message" style="display: none; text-align: right;"
											role="alert"></span>
									</div>

									<div class="sui-form-field s3-compatible-secretKey">
										<label for="s3-compatible-connection-secret-access-key"
											id="label-s3-compatible-connection-secret-access-key" class="sui-label">
											<span
												class="s3-compatible-connection-secret-access-key-label"><?php echo esc_html( __( 'Application Key', 'snapshot' ) ); ?></span><span
												style=" margin-left: 3px; ">*</span>
										</label>

										<div class="snapshot-s3-compatible-key">
											<input
												placeholder="<?php echo esc_attr__( 'Place applicationKey here', 'snapshot' ); ?>"
												id="s3-compatible-connection-secret-access-key" class="sui-form-control"
												name="tpd_secretkey"
												aria-labelledby="label-s3-compatible-connection-secret-access-key" />
											<span class="sui-icon-key" aria-hidden="true"></span>
										</div>
										<span id="error-s3-compatible-connection-secret-access-key"
											class="sui-error-message" style="display: none; text-align: right;"
											role="alert"></span>
									</div>

									<div class="sui-form-field s3-compatible-region">
										<label for="s3-compatible-connection-region"
											id="label-s3-compatible-connection-region" class="sui-label">
											<span
												class="s3-compatible-connection-region-label"><?php echo esc_html( __( 'Region', 'snapshot' ) ); ?></span><span
												style=" margin-left: 3px; ">*</span>
										</label>

										<input
											placeholder="<?php echo esc_attr__( 'Place Region here', 'snapshot' ); ?>"
											id="s3-compatible-connection-region" class="sui-form-control"
											name="tpd_region" aria-labelledby="label-s3-compatible-connection-region" />
										<span id="error-s3-compatible-connection-region" class="sui-error-message"
											style="display: none; text-align: right;" role="alert"></span>
									</div>

									<div class="snapshot-s3-compatible--additional_fields"></div>

								</div>

							</form>

						</div>

						<?php
							$this->render(
								'modals/modal_parts/backblaze-instructions-accordion',
								array()
							);
							$this->render(
								'modals/modal_parts/google-cloud-instructions-accordion',
								array()
							);
							$this->render(
								'modals/modal_parts/do-spaces-instructions-accordion',
								array()
							);
							$this->render(
								'modals/modal_parts/wasabi-instructions-accordion',
								array()
							);
							?>
					</div>

				</div>
			</div>
		</div>

		<div class="sui-box-footer sui-flatten sui-lg sui-content-separated">

			<button class="sui-button sui-button-ghost" data-modal-slide="snapshot-add-destination-dialog-slide-1">
				<span class="sui-icon-arrow-left" aria-hidden="true"></span>
				<?php esc_html_e( 'Back', 'snapshot' ); ?>
			</button>

			<button class="sui-button sui-button-icon-right snapshot-next-destination-screen"
				id="snapshot-submit-s3-connection-test">
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

<script type="text/html" id="snapshot-s3-compatible-destination--other">
	<div class="sui-form-field s3-compatible-endpoint">
		<label for="s3-compatible-connection-endpoint"
			id="label-s3-compatible-connection-endpoint" class="sui-label">
			<span
				class="s3-compatible-connection-endpoint-label"><?php echo esc_html( __( 'Endpoint', 'snapshot' ) ); ?></span><span
				style=" margin-left: 3px; ">*</span>
		</label>

		<input
			placeholder="<?php echo esc_attr__( 'Place Endpoint here', 'snapshot' ); ?>"
			id="s3-compatible-connection-endpoint" class="sui-form-control"
			name="tpd_endpoint" aria-labelledby="label-s3-compatible-connection-endpoint"  />
		<span id="error-s3-compatible-connection-endpoint" class="sui-error-message"
			style="display: none; text-align: right;" role="alert"></span>
	</div>
</script>