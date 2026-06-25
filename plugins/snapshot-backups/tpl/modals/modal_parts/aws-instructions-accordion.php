<?php // phpcs:ignore
/**
 * Accordion with instructions on how to get AWS credentials.
 *
 * @package snapshot
 */

?>
<div class="sui-accordion sui-accordion-flushed snapshot-aws-credentials-howto">

	<div class="sui-accordion-item">

		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title" style="flex: auto;"><span aria-hidden="true" class="sui-icon-warning-alert" style=" font-size: 12px; "></span>
			<?php esc_html_e( 'How to get Amazon S3 credentials?', 'snapshot' ); ?>
			</div>

			<div>
				<button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
			</div>
		</div>

		<div class="sui-accordion-item-body">
			<div class="sui-box">
				<div class="sui-box-body">
					<?php /* translators: %s - PHP version */ ?>
					<p class="sui-description"><?php esc_html_e( 'Follow these instructions to retrieve the Access Key ID and Secret Access Key.', 'snapshot' ); ?></p>
					<label class="sui-label" style=" margin: 30px 0 35px; "><?php esc_html_e( 'Choose your account type', 'snapshot' ); ?></label>

					<div class="sui-side-tabs sui-tabs sui-tabs-flushed">

						<div data-tabs>
							<div class="active"><?php esc_html_e( 'Root User', 'snapshot' ); ?></div>
							<div><?php esc_html_e( 'IAM User', 'snapshot' ); ?></div>
						</div>

						<div data-panes>
							<div class="sui-tab-boxed active">
								<ol style=" margin-left: 0px; list-style-position: inside; ">
									<?php /* translators: %s - Link for AWS docs */ ?>
									<li><?php echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Sign in</a> to the AWS Management Console as the root user.', 'snapshot' ), 'https://console.aws.amazon.com/' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'In the navigation bar on the upper right, choose your account name or number and then choose <strong>My Security Credentials</strong>.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'Expand the <strong>Access keys</strong> (access key ID and secret access key) section.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'Choose <strong>Create New Access Key</strong>. If you already have two access keys, this button is disabled.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'When prompted, choose <strong>Show Access Key</strong> or <strong>Download Key File</strong>. This is your only opportunity to save your secret access key.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'After you\'ve saved your secret access key in a secure location, chose Close.', 'snapshot' ) ); ?></li>
								</ol>
							</div>
							<div class="sui-tab-boxed">
							<ol style=" margin-left: 0px; list-style-position: inside; ">
									<?php /* translators: %s - Link for AWS docs */ ?>
									<li><?php echo wp_kses_post( sprintf( __( '<a href="%s" target="_blank">Sign in</a> to the AWS Management Console as an IAM user.', 'snapshot' ), 'https://console.aws.amazon.com/' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'In the navigation bar on the upper right, choose your user name and then choose <strong>My Security Credentials</strong>.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'Choose <strong>AWS IAM</strong> credentials, Create access key. If you already have two access keys, the console displays a "Limited exceeded" error.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'When prompted, choose <strong>Download .csv file</strong> or <strong>Show secret access key</strong>. This is your only opportunity to save your secret access key.', 'snapshot' ) ); ?></li>
									<li><?php echo wp_kses_post( __( 'After you\'ve saved your secret access key in a secure location, chose Close.', 'snapshot' ) ); ?></li>
								</ol>
							</div>
						</div>

					</div>

					<?php /* translators: %s - Link for AWS docs */ ?>
					<p style="text-align: right; margin-bottom: 0;"><?php echo wp_kses_post( sprintf( __( 'Source: <a href="%s" target="_blank">AWS Documentation</a>', 'snapshot' ), 'https://docs.aws.amazon.com/general/latest/gr/aws-sec-cred-types.html' ) ); ?></p>
				</div>

			</div>
		</div>

	</div>
</div>